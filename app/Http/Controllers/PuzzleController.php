<?php
namespace App\Http\Controllers;

use App\Services\PuzzleService;
use Illuminate\Http\Request;

class PuzzleController extends Controller
{
    public function __construct(private PuzzleService $puzzleService)
    {
    }

    public function generate(Request $request,$length=Null)
    {
        $request->validate([
            'length' => 'nullable|integer|min:6|max:20|numeric',
        ]);
        if($request->length){
            $puzzle = $this->puzzleService->generatePuzzle($request->length);
        }else{
            $puzzle = $this->puzzleService->generatePuzzle();
        }
        return response()->json($puzzle);
    }
}