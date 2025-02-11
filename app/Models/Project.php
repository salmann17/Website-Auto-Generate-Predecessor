<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'alamat',
        'deskripsi',
        
    ];

    public function nodes()
    {
        return $this->hasMany(Node::class, 'project_idproject', 'id');
    }
}
