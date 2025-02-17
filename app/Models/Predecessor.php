<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Predecessor extends Model
{
    use HasFactory;

    protected $table = 'predecessor';
    protected $primaryKey = 'id'; 
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'node_core',
        'node_cabang'
    ];

    public function coreNode()
    {
        return $this->belongsTo(Node::class, 'node_core', 'idnode');
    }

    public function cabangNode()
    {
        return $this->belongsTo(Node::class, 'node_cabang', 'idnode');
    }
}
