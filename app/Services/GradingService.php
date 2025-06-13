<?php
namespace App\Services;

use App\Models\Puzzle;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Helpers\DictionaryHelper;
use Illuminate\Support\Facades\Cache;

class GradingService
{
    private DictionaryHelper $dictionaryHelper;

    public function __construct(DictionaryHelper $dictionaryHelper)
    {
        $this->dictionaryHelper = $dictionaryHelper;
    }

    public function gradeSubmission(
        Puzzle $puzzle,
        User $student,
        string $word
    ): Submission {
        $this->validateInput($word);
        
        $word = strtolower(trim($word));
        
        if (!$this->dictionaryHelper->isValidWord($word)) {
            throw new \InvalidArgumentException('Invalid English word');
        }
        if (!$this->getCurrentRemainingLetters($puzzle, $student)) {
            $remainingLetters = $puzzle->letters;
        }else{
            $remainingLetters = $this->getCurrentRemainingLetters($puzzle, $student);
        }
        $remainingLetters = $this->calculateRemainingLetters($word, $remainingLetters);
        $score = strlen($word);
        return Submission::create([
            'puzzle_id' => $puzzle->id,
            'student_id' => $student->id,
            'word' => $word,
            'score' => $score,
            'remaining_letters' => $remainingLetters,
        ]);
    }

    private function validateInput(string $word): void
    {
        $validator = Validator::make(['word' => $word], [
            'word' => 'required|string|min:2|max:20|alpha',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function calculateRemainingLetters(string $word, string $originalLetters): array
    {
        $wordLetters = str_split($word);
        $availableLetters = str_split($originalLetters);
        
        foreach ($wordLetters as $char) {
            $key = array_search($char, $availableLetters);
            if ($key === false) {
                throw new \InvalidArgumentException(
                    "Letter '$char' not available or too many '$char' in puzzle"
                );
            }
            unset($availableLetters[$key]);
        }
        
        return array_values($availableLetters);
    }

    public function getCurrentRemainingLetters(Puzzle $puzzle, User $student): string
    {
        $lastSubmission = Submission::where('puzzle_id', $puzzle->id)
            ->where('student_id', $student->id)
            ->latest()
            ->first();
        if(isset($lastSubmission) && empty($lastSubmission->remaining_letters)){
             throw new \InvalidArgumentException(
                    "Already completed the puzzle with no char left"
                );
        }
        return $lastSubmission 
            ? implode('', $lastSubmission->remaining_letters)
            : $puzzle->letters;
    }
   
}