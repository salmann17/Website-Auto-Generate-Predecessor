<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Predecessor extends Model
{
    use HasFactory;

    protected $table = 'predecessors';
    protected $fillable = [
        'node_core',
        'node_cabang',
    ];

    public function nodeCore()
    {
        return $this->belongsTo(Node::class, 'node_core', 'id');
    }

    public function nodeCabang()
    {
        return $this->belongsTo(Node::class, 'node_cabang', 'id');
    }
}
