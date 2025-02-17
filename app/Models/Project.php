<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $primaryKey = 'idproject';
    public $incrementing = true;        
    protected $keyType = 'int';

    public $timestamps = true; 

    protected $fillable = [
        'nama',
        'alamat',
        'activity',
        'deskripsi'
    ];

    public function babs()
    {
        return $this->hasMany(Bab::class, 'project_idproject', 'idproject');
    }

}
