<?php
namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\Submission;
use App\Models\User;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class SubmissionController extends Controller
{
    public function __construct(private GradingService $gradingService)
    {
        $this->middleware('auth:sanctum');
    }

    public function submit(Request $request, Puzzle $puzzle)
    {
        $request->validate([
            'word' => 'required|string|min:2|max:20|alpha',
        ]);
        try {
            $submission = $this->gradingService->gradeSubmission(
                $puzzle,
                $request->user(),
                $request->word
            );
            
            return response()->json([
                'submission' => $submission,
                'remaining_letters' => implode('', $submission->remaining_letters),
                'current_score' => $this->getCurrentScore($puzzle, $request->user()),
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['word' => [$e->getMessage()]]
            ], 422);
        }
        // $submission = $this->gradingService->gradeSubmission(
        //     $puzzle,
        //     $request->user(),
        //     $request->word
        // );
        // if(empty($submission->remaining_letters)){
        //      return $this->endGame($puzzle,$request);
        // }
        // return response()->json([
        //     'submission' => $submission,
        //     'remaining_letters' => implode('', $submission->remaining_letters),
        //     'current_score' => $this->getCurrentScore($puzzle, $request->user()),
        // ]);
    }

    public function puzzleLeaderboard(Puzzle $puzzle)
    {
        $leaderboard = Submission::where('puzzle_id', $puzzle->id)
            ->select('word', 'score')
            ->with('student') 
            ->groupBy('word', 'score')
            ->orderByDesc('score')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'word' => ucfirst($item->word),
                    'score' => $item->score,
                ];
            });

        return response()->json([
            'puzzle_id' => $puzzle->id,
            'puzzle_letters' => $puzzle->letters,
            'leaderboard' => $leaderboard
        ]);
    }

    public function endGame(Puzzle $puzzle, Request $request)
    {
        $user = $request->user();
        $score = $this->getCurrentScore($puzzle, $user);
        $remainingLetters = $this->gradingService->getCurrentRemainingLetters($puzzle, $user);

        return response()->json([
            'final_score' => $score,
            'remaining_letters' => $remainingLetters,
            'message' => 'Game ended successfully',
        ]);
    }

    private function getCurrentScore(Puzzle $puzzle, User $user): int
    {
        return Submission::where('puzzle_id', $puzzle->id)
            ->where('student_id', $user->id)
            ->sum('score');
    }
}