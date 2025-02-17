<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubActivity extends Model
{
    use HasFactory;

    protected $table = 'sub_activity';         
    protected $primaryKey = 'idsub_activity';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'activity',
        'idactivity'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'idactivity', 'idactivity');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class, 'id_sub_activity', 'idsub_activity');
    }
}
