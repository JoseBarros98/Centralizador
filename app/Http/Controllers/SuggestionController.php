<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscription;
use Illuminate\Support\Facades\DB;

class SuggestionController extends Controller
{
    /**
     * Obtener sugerencias de residencia basadas en registros previos
     */
    public function getResidenceSuggestions(Request $request)
    {
        $query = $request->query('query');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }
        
        $suggestions = Inscription::select('residence')
            ->where('residence', 'like', "%{$query}%")
            ->distinct()
            ->orderBy('residence')
            ->limit(10)
            ->pluck('residence')
            ->toArray();
            
        return response()->json($suggestions);
    }
    
    /**
     * Obtener sugerencias de profesión basadas en registros previos
     */
    public function getProfessionSuggestions(Request $request)
    {
        $query = $request->query('query');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }
        
        $suggestions = Inscription::select('profession')
            ->where('profession', 'like', "%{$query}%")
            ->distinct()
            ->orderBy('profession')
            ->limit(10)
            ->pluck('profession')
            ->toArray();
            
        return response()->json($suggestions);
    }
    
    /**
     * Verificar si un CI existe y devolver los datos personales
     */
    public function checkCI($ci)
    {
        $inscription = Inscription::where('ci', $ci)->latest()->first();
        
        if ($inscription) {
            return response()->json([
                'exists' => true,
                'message' => "CI encontrado: {$inscription->first_name} {$inscription->paternal_surname} {$inscription->maternal_surname}",
                'data' => [
                    'first_name' => $inscription->first_name,
                    'paternal_surname' => $inscription->paternal_surname,
                    'maternal_surname' => $inscription->maternal_surname,
                    'phone' => $inscription->phone,
                    'gender' => $inscription->gender,
                    'profession' => $inscription->profession,
                    'residence' => $inscription->residence
                ]
            ]);
        }
        
        return response()->json([
            'exists' => false
        ]);
    }
}
