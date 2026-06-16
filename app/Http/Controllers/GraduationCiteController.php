<?php

namespace App\Http\Controllers;

use App\Models\GraduationCite;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GraduationCiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:graduation_cite.view')->only(['index', 'show']);
        $this->middleware('permission:graduation_cite.create')->only(['create', 'store', 'import']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('graduation_cite.edit') || $user->can('graduation_cite.edit_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['edit', 'update']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('graduation_cite.delete') || $user->can('graduation_cite.delete_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = GraduationCite::with(['participants', 'creator'])
            ->withCount('participants');

        if ($request->filled('cite_number')) {
            $query->where('cite_number', 'like', '%' . $request->cite_number . '%');
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('participant')) {
            $search = $request->participant;

            $query->whereHas('participants', function ($participantQuery) use ($search) {
                $participantQuery->where('ci', 'like', '%' . $search . '%')
                    ->orWhere('full_name', 'like', '%' . $search . '%');
            });
        }

        $graduationCites = $query->orderByDesc('cite_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('graduation-cites.index', compact('graduationCites'));
    }

    public function create()
    {
        return view('graduation-cites.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $participantIds = array_unique($validated['participant_ids']);
        $amounts = $request->input('participant_amounts', []);
        $totalAmount = array_sum(array_map('floatval', array_intersect_key($amounts, array_flip($participantIds))));

        $graduationCite = GraduationCite::create([
            'cite_number'            => $validated['cite_number'],
            'cite_date'              => $validated['cite_date'],
            'payment_type'           => $validated['payment_type'],
            'amount_per_participant' => $validated['amount_per_participant'],
            'total_amount'           => $totalAmount,
            'observations'           => $validated['observations'] ?? null,
            'created_by'             => Auth::id(),
            'updated_by'             => Auth::id(),
        ]);

        $this->syncParticipants($graduationCite, $participantIds, $amounts);

        return redirect()
            ->route('graduation-cites.show', $graduationCite)
            ->with('success', 'CITE de titulación creado correctamente.');
    }

    public function show(Request $request, GraduationCite $graduationCite)
    {
        $graduationCite->load(['creator', 'updater']);

        $search = $request->filled('search') ? trim($request->search) : null;

        $participantsQuery = $graduationCite->participants()
            ->withPivot(['participant_full_name', 'participant_ci', 'participant_program', 'amount'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('graduation_cite_inscription.participant_full_name', 'like', "%{$search}%")
                          ->orWhere('graduation_cite_inscription.participant_ci', 'like', "%{$search}%");
                });
            })
            ->orderBy('graduation_cite_inscription.participant_full_name');

        $participants = $participantsQuery->paginate(15)->withQueryString();
        $totalCount   = $graduationCite->participants()->count();

        return view('graduation-cites.show', compact('graduationCite', 'participants', 'totalCount', 'search'));
    }

    public function edit(GraduationCite $graduationCite)
    {
        $this->authorizeAccess($graduationCite, 'edit');
        $graduationCite->load('participants.programs');

        return view('graduation-cites.edit', compact('graduationCite'));
    }

    public function update(Request $request, GraduationCite $graduationCite)
    {
        $this->authorizeAccess($graduationCite, 'edit');
        $validated = $this->validateRequest($request, $graduationCite);
        $participantIds = array_unique($validated['participant_ids']);
        $amounts = $request->input('participant_amounts', []);
        $totalAmount = array_sum(array_map('floatval', array_intersect_key($amounts, array_flip($participantIds))));

        $graduationCite->update([
            'cite_number'            => $validated['cite_number'],
            'cite_date'              => $validated['cite_date'],
            'payment_type'           => $validated['payment_type'],
            'amount_per_participant' => $validated['amount_per_participant'],
            'total_amount'           => $totalAmount,
            'observations'           => $validated['observations'] ?? null,
            'updated_by'             => Auth::id(),
        ]);

        $this->syncParticipants($graduationCite, $participantIds, $amounts);

        return redirect()
            ->route('graduation-cites.show', $graduationCite)
            ->with('success', 'CITE de titulación actualizado correctamente.');
    }

    public function destroy(GraduationCite $graduationCite)
    {
        $this->authorizeAccess($graduationCite, 'delete');
        $graduationCite->delete();

        return redirect()
            ->route('graduation-cites.index')
            ->with('success', 'CITE de titulación eliminado correctamente.');
    }

    public function searchParticipants(Request $request)
    {
        abort_unless(
            Auth::user()?->can('graduation_cite.view')
                || Auth::user()?->can('graduation_cite.create')
                || Auth::user()?->can('graduation_cite.edit'),
            403
        );

        $validated = $request->validate([
            'q' => 'required|string|min:2|max:255',
        ]);

        $search = trim($validated['q']);

        $participants = Inscription::with('programs:id,name')
            ->where(function ($query) use ($search) {
                $query->where('ci', 'like', '%' . $search . '%')
                    ->orWhere('full_name', 'like', '%' . $search . '%');
            })
            ->where(function ($query) {
                $query->whereNull('external_inscription_status')
                    ->orWhere('external_inscription_status', '!=', 'Preinscrito');
            })
            ->orderBy('full_name')
            ->limit(20)
            ->get()
            ->map(function (Inscription $inscription) {
                $programNames = $inscription->programs
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->implode(', ');

                return [
                    'id' => $inscription->id,
                    'full_name' => $inscription->getFullName(),
                    'ci' => $inscription->ci,
                    'program' => $programNames !== '' ? $programNames : 'Sin programa',
                ];
            })
            ->values();

        return response()->json($participants);
    }

    protected function validateRequest(Request $request, ?GraduationCite $graduationCite = null): array
    {
        return $request->validate([
            'cite_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('graduation_cites', 'cite_number')
                    ->where('payment_type', $request->input('payment_type'))
                    ->ignore($graduationCite),
            ],
            'cite_date' => 'required|date',
            'payment_type' => 'required|in:inscripcion,matricula,colegiatura,certificacion',
            'amount_per_participant' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:2000',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'integer|exists:inscriptions,id',
        ], [
            'participant_ids.required' => 'Debes agregar al menos un participante al CITE.',
            'participant_ids.min' => 'Debes agregar al menos un participante al CITE.',
        ]);
    }

    protected function syncParticipants(GraduationCite $graduationCite, array $participantIds, array $amounts = [], array $programOverrides = []): array
    {
        $participants = Inscription::with('programs:id,name')
            ->whereIn('id', $participantIds)
            ->get()
            ->keyBy('id');

        $syncData    = [];
        $notFoundIds = [];

        foreach (array_unique($participantIds) as $participantId) {
            $participant = $participants->get($participantId);

            if (!$participant) {
                $notFoundIds[] = $participantId;
                continue;
            }

            // Usar programa del Excel si está disponible; si no, cargar desde BD
            if (isset($programOverrides[$participantId]) && $programOverrides[$participantId] !== '') {
                $programNames = $programOverrides[$participantId];
            } else {
                $programNames = $participant->programs
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->implode(', ');
                $programNames = $programNames !== '' ? $programNames : 'Sin programa';
            }

            $syncData[$participant->id] = [
                'participant_full_name' => $participant->getFullName(),
                'participant_ci'        => $participant->ci,
                'participant_program'   => $programNames,
                'amount'                => (float) ($amounts[$participantId] ?? 0),
            ];
        }

        $graduationCite->participants()->sync($syncData);

        return $notFoundIds;
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'excel_file.required' => 'Debes seleccionar un archivo Excel.',
            'excel_file.mimes'    => 'El archivo debe ser .xlsx o .xls.',
        ]);

        $monthMap = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12,
        ];

        $paymentTypeMap = [
            'INSCRIPCION'  => 'inscripcion',
            'INSCRIPCIÓN'  => 'inscripcion',
            'MATRICULA'    => 'matricula',
            'MATRÍCULA'    => 'matricula',
            'COLEGIATURA'  => 'colegiatura',
            'CERTIFICACION' => 'certificacion',
            'CERTIFICACIÓN' => 'certificacion',
        ];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('excel_file')->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return back()->withErrors(['excel_file' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
        }

        // Detectar fila de cabecera buscando la celda "PARTICIPANTE"
        $headerRow  = null;
        $headerIndex = null;
        foreach ($rows as $i => $row) {
            foreach ($row as $cell) {
                if (strtoupper(trim((string) $cell)) === 'PARTICIPANTE') {
                    $headerRow   = array_map(fn($c) => strtoupper(trim((string) $c)), $row);
                    $headerIndex = $i;
                    break 2;
                }
            }
        }

        if (!$headerRow) {
            return back()->withErrors(['excel_file' => 'No se encontró la fila de cabecera con columna "PARTICIPANTE".']);
        }

        $colCI           = array_search('C.I', $headerRow);
        $colParticipante = array_search('PARTICIPANTE', $headerRow);
        $colCite         = array_search('CITE', $headerRow);

        // Columnas de tipo de pago — buscar por nombre parcial
        $colPayments = [];
        foreach ($headerRow as $idx => $name) {
            $key = $paymentTypeMap[$name] ?? null;
            if ($key) {
                $colPayments[$key] = $idx;
            }
        }

        if ($colCI === false || $colCite === false || empty($colPayments)) {
            return back()->withErrors(['excel_file' => 'El archivo no tiene las columnas esperadas (C.I, CITE, e INSCRIPCION/MATRICULA/COLEGIATURA/CERTIFICACION).']);
        }

        // Columnas de programa: cualquier columna que no sea especial, de pago, ni de numeración
        $specialCols     = array_filter([$colCI, $colParticipante, $colCite], fn($v) => $v !== false);
        $paymentCols     = array_values($colPayments);
        $numberingHeaders = ['N°', 'N', 'N.°', 'NO', 'NO.', 'NUM', 'NUM.', 'NUMERO', 'NÚMERO', '#'];
        $colPrograms = [];
        foreach ($headerRow as $idx => $name) {
            if ($name === '') continue;
            if (in_array($idx, $specialCols) || in_array($idx, $paymentCols)) continue;
            if (in_array($name, $numberingHeaders)) continue;
            $colPrograms[] = $idx;
        }

        $shortMonths = [
            'ENE' => 1, 'FEB' => 2, 'MAR' => 3, 'ABR' => 4,
            'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AGO' => 8,
            'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DIC' => 12,
        ];

        // Agrupar filas por CITE + concepto de pago (clave compuesta).
        // El nombre del CITE solo aparece en la primera fila (fusionadas/vacías) → carry-forward.
        // Un mismo CITE puede tener filas de distintos conceptos (inscripcion, matricula, etc.).
        $groups      = [];  // ['CITE 027|matricula' => ['cite_number'=>…, 'payment_type'=>…, 'rows'=>[…]]]
        $lastCite    = '';
        $skippedRows = []; // filas descartadas por no tener monto en ninguna columna
        foreach ($rows as $i => $row) {
            if ($i <= $headerIndex) continue;

            $citeRaw = trim((string) ($row[$colCite] ?? ''));
            if ($citeRaw !== '') {
                $lastCite = $citeRaw;
            } else {
                $citeRaw = $lastCite;
            }
            if ($citeRaw === '') continue;

            // Normalizar CI: quitar espacios, caracteres no imprimibles, tratar como string
            $ciRaw = preg_replace('/\s+/', '', (string) ($row[$colCI] ?? ''));
            if ($ciRaw === '') continue;

            $nameRaw = trim((string) ($row[$colParticipante] ?? ''));

            // Programa de esta fila (columnas que no son especiales ni de pago)
            $programParts = [];
            foreach ($colPrograms as $col) {
                $val = trim((string) ($row[$col] ?? ''));
                if ($val !== '') $programParts[] = $val;
            }
            $programRaw = implode(', ', $programParts);

            // Detectar todos los tipos de pago con monto válido para esta fila
            $rowPayments = [];
            foreach ($colPayments as $type => $col) {
                $val = trim((string) ($row[$col] ?? ''));
                if ($val !== '' && is_numeric($val) && (float) $val > 0) {
                    $rowPayments[$type] = (float) $val;
                }
            }

            if (empty($rowPayments)) {
                // Fila con CI pero sin monto en ningún concepto → reportar
                $skippedRows[] = "Fila sin monto: CI {$ciRaw} ({$nameRaw}) en CITE \"{$citeRaw}\"";
                continue;
            }

            // Agregar la fila a cada grupo de pago que tenga monto, con su monto específico
            foreach ($rowPayments as $paymentType => $amount) {
                $groupKey = $citeRaw . '|||' . $paymentType;

                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'cite_number'  => $citeRaw,
                        'payment_type' => $paymentType,
                        'rows'         => [],
                    ];
                }

                $groups[$groupKey]['rows'][] = [
                    'ci'      => $ciRaw,
                    'name'    => $nameRaw,
                    'amount'  => $amount,
                    'program' => $programRaw,
                ];
            }
        }

        $created  = 0;
        $skipped  = 0;
        $errors   = array_merge([], $skippedRows); // incluir filas sin monto desde el inicio

        foreach ($groups as $group) {
            $citeNumber  = $group['cite_number'];
            $paymentType = $group['payment_type'];

            // Omitir si ya existe la combinación cite_number + payment_type
            if (GraduationCite::where('cite_number', $citeNumber)->where('payment_type', $paymentType)->exists()) {
                $skipped++;
                continue;
            }

            // Inferir fecha del nombre del CITE
            $citeDate  = null;
            $upperCite = strtoupper($citeNumber);

            if (preg_match('/\b(' . implode('|', array_keys($monthMap)) . ')\s+(\d{4})\b/', $upperCite, $m)) {
                $citeDate = \Carbon\Carbon::createFromDate((int) $m[2], $monthMap[$m[1]], 1)->toDateString();
            }
            if (!$citeDate && preg_match('/\b(' . implode('|', array_keys($shortMonths)) . ')\D*(\d{4})\b/', $upperCite, $m)) {
                $citeDate = \Carbon\Carbon::createFromDate((int) $m[2], $shortMonths[$m[1]], 1)->toDateString();
            }
            if (!$citeDate) {
                $citeDate = now()->toDateString();
            }

            // Buscar participantes por CI (normalizado) y mapear montos y programas
            $participantIds = [];
            $amounts        = [];
            $programs       = [];
            $notFoundCIs    = [];
            foreach ($group['rows'] as $row) {
                // Buscar con CI exacto y también sin ceros a la izquierda (Excel a veces los elimina)
                $inscription = Inscription::where('ci', $row['ci'])
                    ->orWhere('ci', ltrim($row['ci'], '0'))
                    ->first();

                if ($inscription) {
                    if (in_array($inscription->id, $participantIds)) {
                        // Mismo participante con varios programas → acumular monto y programa
                        $amounts[$inscription->id] += $row['amount'];
                        if (!empty($row['program'])) {
                            $existing = $programs[$inscription->id] ?? '';
                            if ($existing === '') {
                                $programs[$inscription->id] = $row['program'];
                            } elseif (!str_contains($existing, $row['program'])) {
                                $programs[$inscription->id] = $existing . ', ' . $row['program'];
                            }
                        }
                    } else {
                        $participantIds[]           = $inscription->id;
                        $amounts[$inscription->id]  = $row['amount'];
                        $programs[$inscription->id] = $row['program'] ?? '';
                    }
                } else {
                    $notFoundCIs[] = $row['ci'] . ' (' . $row['name'] . ')';
                }
            }

            if (!empty($notFoundCIs)) {
                $errors[] = "CITE \"{$citeNumber}\" [{$paymentType}]: CIs no encontrados → " . implode(', ', $notFoundCIs);
            }

            if (empty($participantIds)) {
                $errors[] = "CITE \"{$citeNumber}\" [{$paymentType}]: ningún participante encontrado, no se creó.";
                $skipped++;
                continue;
            }

            $participantIds = array_unique($participantIds);
            $totalAmount    = array_sum($amounts);
            $avgAmount      = $totalAmount / count($participantIds);

            $cite = GraduationCite::create([
                'cite_number'            => $citeNumber,
                'cite_date'              => $citeDate,
                'payment_type'           => $paymentType,
                'amount_per_participant' => $avgAmount,
                'total_amount'           => $totalAmount,
                'created_by'             => Auth::id(),
                'updated_by'             => Auth::id(),
            ]);

            $notFoundIds = $this->syncParticipants($cite, $participantIds, $amounts, $programs);
            if (!empty($notFoundIds)) {
                $errors[] = "CITE \"{$citeNumber}\" [{$paymentType}]: " . count($notFoundIds) . " inscripción(es) con ID " . implode(', ', $notFoundIds) . " no se pudo(n) vincular al CITE (revisar BD).";
            }
            $created++;
        }

        $message = "Importación completada: {$created} CITEs creados, {$skipped} omitidos.";

        return redirect()->route('graduation-cites.index')
            ->with('import_success', $message)
            ->with('import_errors', $errors);
    }

    private function authorizeAccess(GraduationCite $graduationCite, string $ability): void
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);
        $isOwner = (int) $graduationCite->created_by === (int) $user->id;
        if ($ability === 'edit'   && $user->can('graduation_cite.edit'))               return;
        if ($ability === 'edit'   && $user->can('graduation_cite.edit_own')   && $isOwner) return;
        if ($ability === 'delete' && $user->can('graduation_cite.delete'))             return;
        if ($ability === 'delete' && $user->can('graduation_cite.delete_own') && $isOwner) return;
        abort(403, 'Solo puedes ' . ($ability === 'edit' ? 'editar' : 'eliminar') . ' CITEs que tú creaste.');
    }
}