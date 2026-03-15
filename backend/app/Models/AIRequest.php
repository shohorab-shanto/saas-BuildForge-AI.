<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIRequest extends Model
{
    use HasFactory;

    protected $table = 'ai_requests';

    protected $fillable = ['project_id', 'type', 'prompt', 'response_json', 'status'];

    protected $casts = [
        'response_json' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
