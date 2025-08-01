<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class NameMatcher
{
    /**
     * Normaliza un nombre para comparaciones.
     *
     * @param string $name Nombre a normalizar
     * @return string Nombre normalizado
     */
    public static function normalizeName($name)
    {
        // Eliminar texto entre paréntesis
        $name = preg_replace('/$$[^)]*$$/', '', $name);
        
        // Convertir a mayúsculas
        $name = mb_strtoupper($name, 'UTF-8');
        
        // Eliminar acentos
        $name = self::removeAccents($name);
        
        // Eliminar caracteres especiales y espacios múltiples
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Eliminar palabras comunes que no aportan a la identidad
        $commonWords = ['DE', 'LA', 'LOS', 'LAS', 'DEL', 'Y', 'E', 'DA', 'DO', 'DI'];
        $parts = explode(' ', $name);
        $parts = array_filter($parts, function($part) use ($commonWords) {
            return !in_array($part, $commonWords);
        });
        
        return trim(implode(' ', $parts));
    }
    
    /**
     * Elimina acentos de un texto.
     *
     * @param string $string Texto con acentos
     * @return string Texto sin acentos
     */
    private static function removeAccents($string)
    {
        $unwanted_array = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'ñ' => 'n', 'Ñ' => 'N'
        ];
        
        return strtr($string, $unwanted_array);
    }
    
    /**
     * Extrae los apellidos de un nombre completo.
     * 
     * @param string $name Nombre completo
     * @return array Apellidos extraídos
     */
    public static function extractLastNames($name)
    {
        $parts = explode(' ', trim($name));
        
        // Si solo hay una o dos palabras, asumimos que es nombre + apellido
        if (count($parts) <= 2) {
            return [end($parts)];
        }
        
        // Para nombres más largos, asumimos que los últimos dos son apellidos
        return array_slice($parts, -2);
    }
    
    /**
     * Compara dos nombres y determina si son similares.
     * 
     * @param string $name1 Primer nombre
     * @param string $name2 Segundo nombre
     * @return bool True si los nombres son similares
     */
    public static function areSimilarNames($name1, $name2)
    {
        // Normalizar ambos nombres
        $name1 = self::normalizeName($name1);
        $name2 = self::normalizeName($name2);
        
        // Si son exactamente iguales después de normalizar
        if ($name1 === $name2) {
            return true;
        }
        
        // Dividir los nombres en partes
        $parts1 = explode(' ', $name1);
        $parts2 = explode(' ', $name2);
        
        // Extraer apellidos
        $lastNames1 = self::extractLastNames($name1);
        $lastNames2 = self::extractLastNames($name2);
        
        // Si los apellidos coinciden completamente
        $commonLastNames = array_intersect($lastNames1, $lastNames2);
        if (count($commonLastNames) == count($lastNames1) || count($commonLastNames) == count($lastNames2)) {
            return true;
        }
        
        // Caso 1: Nombre completo vs. nombre parcial (como BEATRIS NELLY TALAMÁS RIOJAS vs BEATRIS TALAMÁS)
        // Verificar si todas las partes del nombre más corto están en el nombre más largo
        if (count($parts1) != count($parts2)) {
            $shorterParts = count($parts1) < count($parts2) ? $parts1 : $parts2;
            $longerParts = count($parts1) < count($parts2) ? $parts2 : $parts1;
            
            $allPartsFound = true;
            foreach ($shorterParts as $part) {
                if (!in_array($part, $longerParts)) {
                    $allPartsFound = false;
                    break;
                }
            }
            
            if ($allPartsFound) {
                return true;
            }
        }
        
        // Caso 2: Variaciones en nombres compuestos (como CARMEN MAGALI vs CARMEN MAGALY)
        // Verificar si el primer y último nombre coinciden, y hay similitud en los nombres intermedios
        if (count($parts1) >= 2 && count($parts2) >= 2) {
            // Si el primer nombre coincide
            if ($parts1[0] === $parts2[0]) {
                // Si el último nombre coincide o está ausente en uno de ellos
                $last1 = end($parts1);
                $last2 = end($parts2);
                
                if ($last1 === $last2) {
                    return true;
                }
                
                // Si hay apellidos y al menos uno coincide
                if (count($parts1) >= 3 && count($parts2) >= 3) {
                    $apellidos1 = array_slice($parts1, -2);
                    $apellidos2 = array_slice($parts2, -2);
                    
                    $commonApellidos = array_intersect($apellidos1, $apellidos2);
                    if (count($commonApellidos) > 0) {
                        return true;
                    }
                }
                
                // Verificar similitud en nombres intermedios (para casos como MAGALI vs MAGALY)
                if (count($parts1) >= 3 && count($parts2) >= 3) {
                    $middle1 = $parts1[1];
                    $middle2 = $parts2[1];
                    
                    // Si los nombres intermedios son similares (difieren en 1-2 caracteres)
                    if (levenshtein($middle1, $middle2) <= 2) {
                        return true;
                    }
                }
            }
        }
        
        // Caso 3: Verificar si hay suficientes palabras en común
        $commonParts = array_intersect($parts1, $parts2);
        if (count($commonParts) >= 2) {
            return true;
        }
        
        // Caso 4: Para nombres muy cortos, verificar si hay al menos una palabra significativa en común
        if (count($parts1) <= 2 && count($parts2) <= 2) {
            foreach ($parts1 as $part1) {
                foreach ($parts2 as $part2) {
                    // Si la palabra es significativa (más de 4 caracteres) y son iguales
                    if (strlen($part1) > 4 && $part1 === $part2) {
                        return true;
                    }
                    
                    // O si son muy similares (difieren en 1 caracter)
                    if (strlen($part1) > 4 && levenshtein($part1, $part2) <= 1) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Genera una clave única para un nombre normalizado.
     * Útil para agrupar variaciones del mismo nombre.
     * 
     * @param string $name Nombre a procesar
     * @return string Clave única
     */
    public static function generateNameKey($name)
    {
        $normalized = self::normalizeName($name);
        $parts = explode(' ', $normalized);
        
        // Ordenar las partes para que diferentes ordenamientos del mismo nombre generen la misma clave
        sort($parts);
        
        return implode('_', $parts);
    }
    
    /**
     * Registra información detallada sobre la comparación de nombres para depuración.
     * 
     * @param string $name1 Primer nombre
     * @param string $name2 Segundo nombre
     * @return array Información de depuración
     */
    public static function getDebugInfo($name1, $name2)
    {
        $normalized1 = self::normalizeName($name1);
        $normalized2 = self::normalizeName($name2);
        
        $parts1 = explode(' ', $normalized1);
        $parts2 = explode(' ', $normalized2);
        
        $commonParts = array_intersect($parts1, $parts2);
        
        return [
            'original1' => $name1,
            'original2' => $name2,
            'normalized1' => $normalized1,
            'normalized2' => $normalized2,
            'parts1' => $parts1,
            'parts2' => $parts2,
            'commonParts' => $commonParts,
            'isSimilar' => self::areSimilarNames($name1, $name2)
        ];
    }
}
