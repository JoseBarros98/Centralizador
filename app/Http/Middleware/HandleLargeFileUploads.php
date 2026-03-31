<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleLargeFileUploads
{
    /**
     * Handle an incoming request for large file uploads.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('HandleLargeFileUploads middleware executed for: ' . $request->path());
        
        // Configurar timeouts más altos para uploads de archivos grandes
        if ($request->hasFile('file') || $request->hasFile('files') || $request->hasFile('document_files')) {
            Log::info('Large file upload detected, extending limits');
            
            // Extender límites para archivos grandes
            ini_set('max_execution_time', 600); // 10 minutos
            ini_set('max_input_time', 600);     // 10 minutos
            ini_set('memory_limit', '1024M');   // 1GB de memoria
            
            // Configurar timeouts de conexión
            ini_set('default_socket_timeout', 600);
            
            Log::info('PHP limits extended', [
                'max_execution_time' => ini_get('max_execution_time'),
                'max_input_time' => ini_get('max_input_time'),
                'memory_limit' => ini_get('memory_limit'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]);
        }

        return $next($request);
    }
}