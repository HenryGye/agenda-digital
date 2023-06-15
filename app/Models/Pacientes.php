<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pacientes extends Model
{
    protected $table = 'pacientes';
    protected $fillable = ['nombre', 'direccion', 'telefono', 'fecha_creacion', 'cita_automatica'];
    public $timestamps = false;

    public function citasAutomaticas()
    {
        return $this->hasMany(CitasAutomaticas::class, 'paciente_id');
    }
}
