<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedFile extends Model
{
    use HasFactory;

    protected $table = 'generated_files';

    protected $fillable = ['project_id', 'file_path', 'content', 'file_type'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
