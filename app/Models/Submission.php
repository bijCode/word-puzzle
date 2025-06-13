<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
     protected $casts = [
        'remaining_letters' => 'array', 
    ];
    protected $fillable = [
        'puzzle_id' ,'student_id','word','score','remaining_letters'
    ];
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
