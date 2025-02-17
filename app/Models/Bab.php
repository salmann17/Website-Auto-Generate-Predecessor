<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bab extends Model
{
    use HasFactory;

    protected $table = 'babs';
    protected $primaryKey = 'idbab';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'activity',
        'project_idproject'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_idproject', 'idproject');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class, 'bab_idbab', 'idbab');
    }

}
