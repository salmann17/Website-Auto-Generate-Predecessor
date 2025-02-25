<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activity';             
    protected $primaryKey = 'idactivity';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'activity',
        'idproject',
        'durasi'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'idproject', 'idproject');
    }

    public function subActivities()
    {
        return $this->hasMany(SubActivity::class, 'idactivity', 'idactivity');
    }

}
