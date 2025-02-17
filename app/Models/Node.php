<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $table = 'nodes';
    protected $primaryKey = 'inode';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'activity',
        'durasi',
        'prioritas',
        'total_price',
        'bobot_rencana',
        'bobot_realisasi',
        'bab_idbab'
    ];

    public function bab()
    {
        return $this->belongsTo(Bab::class, 'bab_idbab', 'idbab');
    }

    public function corePredecessors()
    {
        return $this->hasMany(Predecessor::class, 'node_core', 'inode');
    }

    public function cabangPredecessors()
    {
        return $this->hasMany(Predecessor::class, 'node_cabang', 'inode');
    }


    public function predecessorsCabang()
    {
        return $this->hasMany(Predecessor::class, 'node_cabang', 'id');
    }
}
