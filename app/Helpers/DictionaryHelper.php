<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DictionaryHelper
{
    

    public function isValidWord(string $word): bool
    {
        $word = strtolower($word);       
        return $this->checkWordApi($word);
    }

    private function checkWordApi(string $word): bool
    {  
        try {
            $response = Http::timeout(3)
                ->get("https://api.dictionaryapi.dev/api/v2/entries/en/{$word}");
            
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }    
    }

    private function canFormWord(string $word, string $letters): bool
    {
        $wordLetters = str_split($word);
        $availableLetters = str_split($letters);
        
        foreach ($wordLetters as $char) {
            $key = array_search($char, $availableLetters);
            if ($key === false) return false;
            unset($availableLetters[$key]);
        }
        
        return true;
    }
}