<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    use HasFactory;

    protected $table = 'project';              
    protected $primaryKey = 'idproject';       
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'nama',
        'activity',
        'deskripsi'
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class, 'idproject', 'idproject');
    }


}
