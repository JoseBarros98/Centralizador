<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->middleware(['permission:system.backup']);
        $this->backupDir = storage_path('app/backups');

        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    public function index()
    {
        $files = collect(File::exists($this->backupDir) ? File::files($this->backupDir) : [])
            ->map(fn($f) => [
                'name' => $f->getFilename(),
                'size' => $f->getSize(),
                'date' => \Carbon\Carbon::createFromTimestamp($f->getMTime()),
                'type' => str_contains($f->getFilename(), '_db_') ? 'database' : 'full',
            ])
            ->sortByDesc('date')
            ->values();

        return view('backup.index', compact('files'));
    }

    public function createDatabase()
    {
        set_time_limit(300);

        $filename = 'backup_db_' . now()->format('Y_m_d_His') . '.sql';
        $path     = $this->backupDir . '/' . $filename;

        File::put($path, $this->generateSqlDump());

        return back()->with('success', "Backup de base de datos creado: {$filename}");
    }

    public function createFull()
    {
        set_time_limit(600);

        $filename = 'backup_full_' . now()->format('Y_m_d_His') . '.zip';
        $path     = $this->backupDir . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }

        $zip->addFromString('database.sql', $this->generateSqlDump());
        $this->addDirectoryToZip($zip, storage_path('app'), 'storage/app', ['backups']);

        $zip->close();

        return back()->with('success', "Backup completo creado: {$filename}");
    }

    public function download(string $filename)
    {
        $path = $this->backupDir . '/' . basename($filename);

        abort_unless(File::exists($path), 404, 'Archivo no encontrado.');

        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        $path = $this->backupDir . '/' . basename($filename);

        if (File::exists($path)) {
            File::delete($path);
        }

        return back()->with('success', 'Backup eliminado.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => [
                'required', 'file',
                'max:307200', // 300 MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['sql', 'zip'])) {
                        $fail('El archivo debe ser .sql o .zip');
                    }
                },
            ],
        ]);

        set_time_limit(600);

        $file = $request->file('backup_file');
        $ext  = strtolower($file->getClientOriginalExtension());

        if ($ext === 'sql') {
            $this->restoreDatabase($file->getPathname());
            return back()->with('success', 'Base de datos restaurada correctamente.');
        }

        if ($ext === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($file->getPathname()) !== true) {
                return back()->with('error', 'No se pudo abrir el archivo ZIP.');
            }

            $tempDir = storage_path('app/temp_restore_' . time());
            $zip->extractTo($tempDir);
            $zip->close();

            $sqlPath = $tempDir . '/database.sql';
            if (File::exists($sqlPath)) {
                $this->restoreDatabase($sqlPath);
            }

            $storageSrc = $tempDir . '/storage/app';
            if (File::exists($storageSrc)) {
                File::copyDirectory($storageSrc, storage_path('app'));
            }

            File::deleteDirectory($tempDir);

            return back()->with('success', 'Backup completo restaurado correctamente.');
        }

        return back()->with('error', 'Tipo de archivo no soportado.');
    }

    private function generateSqlDump(): string
    {
        $pdo    = DB::connection()->getPdo();
        $dbName = config('database.connections.' . config('database.default') . '.database');

        $sql  = "-- Backup generado el " . now()->toDateTimeString() . "\n";
        $sql .= "-- Base de datos: {$dbName}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createResult = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql    = array_values($createResult)[1];

            $sql .= "-- Tabla: {$table}\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $columns = '`' . implode('`, `', array_keys($rows[0])) . '`';

                foreach ($rows as $row) {
                    $values = array_map(
                        fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                        array_values($row)
                    );
                    $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    private function restoreDatabase(string $sqlPath): void
    {
        $sql = File::get($sqlPath);
        DB::unprepared($sql);
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipDir, array $exclude = []): void
    {
        if (!File::exists($dir)) return;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relativePath = substr($file->getPathname(), strlen($dir) + 1);
            $parts        = explode(DIRECTORY_SEPARATOR, $relativePath);

            if (!empty($exclude) && in_array($parts[0], $exclude)) continue;

            $zipPath = $zipDir . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $file->isDir()
                ? $zip->addEmptyDir($zipPath)
                : $zip->addFile($file->getPathname(), $zipPath);
        }
    }
}
