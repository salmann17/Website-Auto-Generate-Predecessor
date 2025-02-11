<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $table = 'nodes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'activity',
        'durasi',
        'prioritas',
        'project_idproject',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_idproject', 'id');
    }

    public function predecessorsCore()
    {
        return $this->hasMany(Predecessor::class, 'node_core', 'id');
    }

    public function predecessorsCabang()
    {
        return $this->hasMany(Predecessor::class, 'node_cabang', 'id');
    }
}
