<?php
namespace App\Services;

use App\Models\Puzzle;
use Illuminate\Support\Str;
use App\Helpers\DictionaryHelper;

class PuzzleService
{
    public function generatePuzzle(int $length = 12): Puzzle
    {
        do {
            $letters = $this->generateRandomLetters($length);
            $hasValidWord = $this->validatePuzzleHasWords($letters);
        } while (!$hasValidWord);

        return Puzzle::create(['letters' => $letters]);
    }

    private function generateRandomLetters(int $length): string
    {
        $vowels = ['a', 'e', 'i', 'o', 'u'];
        $consonants = array_diff(range('a', 'z'), $vowels);
        
        return collect()
            ->pad(floor($length * 0.4), null)
            ->map(fn() => $vowels[array_rand($vowels)])
            ->concat(
                collect()->pad(ceil($length * 0.6), null)
                    ->map(fn() => $consonants[array_rand($consonants)])
            )
            ->shuffle()
            ->implode('');
    }

    private function validatePuzzleHasWords(string $letters): bool
    {
        // Simple check - at least one 3-letter word can be formed
        return count($this->findPossibleWords($letters, 3)) > 0;
    }

    private function findPossibleWords(string $letters, int $minLength = 3): array
    {
        // In a real implementation, this would use a dictionary service
        // For demo purposes, we'll return some mock words
        $mockWords = ['cat', 'dog', 'fox', 'bat', 'rat'];
        
        return array_filter($mockWords, function($word) use ($letters, $minLength) {
            return strlen($word) >= $minLength && 
                   $this->canFormWord($word, $letters);
        });
    }

    private function canFormWord(string $word, string $letters): bool
    {
        $wordLetters = str_split(strtolower($word));
        $availableLetters = str_split(strtolower($letters));
        
        foreach ($wordLetters as $char) {
            $key = array_search($char, $availableLetters);
            if ($key === false) return false;
            unset($availableLetters[$key]);
        }
        
        return true;
    }
}