<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'idea_url', 'organization_id', 'architecture_json', 'schema_json', 'status'];

    protected $casts = [
        'architecture_json' => 'array',
        'schema_json' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function aiRequests()
    {
        return $this->hasMany(AIRequest::class);
    }

    public function generatedFiles()
    {
        return $this->hasMany(GeneratedFile::class);
    }
}
