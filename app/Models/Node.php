<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $table = 'nodes';
    protected $primaryKey = 'idnode';
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
        'id_sub_activity'
    ];

    public function subActivity()
    {
        return $this->belongsTo(SubActivity::class, 'id_sub_activity', 'idsub_activity');
    }

    public function predecessors()
    {
        return $this->hasMany(Predecessor::class, 'node_core', 'idnode');
    }

    public function cabangPredecessors()
    {
        return $this->hasMany(Predecessor::class, 'node_cabang', 'idnode');
    }
}
