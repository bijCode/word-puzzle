<?php
namespace Tests\Unit;

use App\Models\Puzzle;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordPuzzleTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Puzzle $puzzle;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->student = User::factory()->create();
        $this->puzzle = Puzzle::create(['letters' => 'abcdefgh']);
    }

    public function test_puzzle_generation()
    {
        $response = $this->actingAs($this->student)
            ->get('/api/puzzle');
            
        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'letters']);
    }

    public function test_valid_word_submission()
    {
        $response = $this->actingAs($this->student)
            ->postJson("/api/puzzle/{$this->puzzle->id}/submit", [
                'word' => 'bad'
            ]);
            
        $response->assertStatus(200)
            ->assertJson([
                'submission' => [
                    'word' => 'bad',
                    'score' => 3,
                ],
                'remaining_letters' => 'cefgh',
            ]);
    }

    public function test_invalid_word_submission()
    {
        $response = $this->actingAs($this->student)
            ->postJson("/api/puzzle/{$this->puzzle->id}/submit", [
                'word' => 'xyz'
            ]);
            
        $response->assertStatus(422);
    }

    public function test_duplicate_letter_usage()
    {
        $this->actingAs($this->student)
            ->postJson("/api/puzzle/{$this->puzzle->id}/submit", [
                'word' => 'bad'
            ]);
            
        $response = $this->actingAs($this->student)
            ->postJson("/api/puzzle/{$this->puzzle->id}/submit", [
                'word' => 'bed'
            ]);
            
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['word']);
    }

    public function test_leaderboard_returns_top_scores()
    {
        $users = User::factory()->count(3)->create();
        
        // Create submissions with different scores
        $this->createSubmission($users[0], 'aced', 4);
        $this->createSubmission($users[1], 'face', 4);
        $this->createSubmission($users[2], 'bad', 3);
        
        $response = $this->actingAs($this->student)
            ->get('/api/leaderboard/'.$this->puzzle->id);
            
        $response->assertStatus(200)
            ->assertJsonCount(3) // Should only show unique words
            ->assertJsonFragment(['word' => 'Aced', 'score' => 4])
            ->assertJsonFragment(['word' => 'Face', 'score' => 4])
            ->assertJsonFragment(['word' => 'Bad', 'score' => 3]);
    }

    public function test_end_game_returns_correct_info()
    {
        $this->actingAs($this->student)
            ->postJson("/api/puzzle/{$this->puzzle->id}/submit", [
                'word' => 'bad'
            ]);
            
        $response = $this->actingAs($this->student)
            ->postJson("/api/puzzle/{$this->puzzle->id}/end");
            
        $response->assertStatus(200)
            ->assertJson([
                'final_score' => 3,
                'remaining_letters' => 'cefgh',
            ]);
    }

    private function createSubmission(User $user, string $word, int $score): void
    {
        Submission::create([
            'puzzle_id' => $this->puzzle->id,
            'student_id' => $user->id,
            'word' => $word,
            'score' => $score,
            'remaining_letters' => [],
        ]);
    }
}
