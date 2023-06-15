<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitasAutomaticas extends Model
{
    protected $table = 'citas_automaticas';
    protected $fillable = ['fecha_creacion', 'paciente_id'];
    public $timestamps = false;

    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }
}
