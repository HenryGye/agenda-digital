<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pacientes;
use App\Models\CitasAutomaticas;
use Carbon\Carbon;

class AgendaDigitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pacientes = Pacientes::orderBy('fecha_creacion', 'asc')->get();

        return view('listaPacientes', [
            'pacientes' => $pacientes
        ]);
    }

    public function crearCita() {
        $fechas = $this->obtenerFechasCitas();

        return view('agenda', [
            'fechasExistentes' => $fechas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fechaCreacion = \DateTime::createFromFormat('d/m/Y H:i:s', $request->fechaCreacion)->format('Y-m-d H:i:s');
        
        if ($this->validarFechaCreacion($fechaCreacion)) {
            $paciente = new Pacientes;
            $paciente->nombre = $request->nombre;
            $paciente->direccion = $request->direccion;
            $paciente->telefono = $request->telefono;
            $paciente->fecha_creacion = $fechaCreacion;
            $paciente->cita_automatica = false;
            $paciente->save();
            return $paciente->id;
        }

        return response()->json(['error' => true, 'mensaje' => 'Fecha de cita no puede ser menor que la fecha actual'], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $paciente = Pacientes::where('id', $id)->first();
        $citasAutomaticas = $paciente->citasAutomaticas()->orderBy('fecha_creacion', 'asc')->get();
        $fechas = $this->obtenerFechasCitas();

        if ($paciente) {
            return view('agenda', [
                'paciente' => $paciente,
                'citasAutomaticas' => $citasAutomaticas,
                'fechasExistentes' => $fechas
            ]);
        }

        return redirect()->action([AgendaDigitalController::class, 'crearCita']);
    }

    public function guardarCitasAutomaticas(Request $request) {
        $arrayFechas = array();
        $id = (int)$request->id;
        $citasAutomaticas = (int)$request->cantidadCitas;
        $diasDiferencia = (int)$request->diasDiferencia;
        $fechaCreacion = new \DateTime($request->fechaCreacion);
        $fechaSumada = $fechaCreacion->modify('+' . $diasDiferencia . ' days')->format('Y-m-d H:i:s');

        try {
            $paciente = Pacientes::where('id', $id)->first();
            $paciente->cita_automatica = true;
            $paciente->save();

            for ($i=0; $i < $citasAutomaticas ; $i++) { 
                $existeFecha = CitasAutomaticas::where('fecha_creacion', $fechaSumada)->first();
                
                // valida si existe fecha
                if ($existeFecha) $fechaSumada = (new \DateTime($fechaSumada))->modify('+1 days')->format('Y-m-d H:i:s');
    
                // obtengo dia semana
                $diaSemana = date("l", strtotime($fechaSumada));
    
                // valida si cae fin de semana
                if ($diaSemana === "Saturday") $fechaSumada = (new \DateTime($fechaSumada))->modify('+2 days')->format('Y-m-d H:i:s');
                if ($diaSemana === "Sunday") $fechaSumada = (new \DateTime($fechaSumada))->modify('+1 days')->format('Y-m-d H:i:s');
                
                // valida si existe horario
                $existeHorario = CitasAutomaticas::where('fecha_creacion', $fechaSumada)->first();
    
                if ($existeHorario) $fechaSumada = (new \DateTime($fechaSumada))->modify('+1 hours')->format('Y-m-d H:i:s');
    
                // registro citas automaticas
                $citas = new CitasAutomaticas;
                $citas->paciente_id = $id;
                $citas->fecha_creacion = $fechaSumada;
                $citas->save();

                $fechaSumada = (new \DateTime($fechaSumada))->modify('+' . $diasDiferencia . ' days')->format('Y-m-d H:i:s');
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => true, 'mensaje' => 'Ha ocurrido un error. Intente más tarde.'], 500);
        }

        return $paciente->id;
    }

    function validarFechaCreacion($fechaCreacion) {
        if (date('Y-m-d H:i:s', strtotime($fechaCreacion)) > date('Y-m-d H:i:s')) return true; else return false;
    }

    function obtenerFechasCitas() {
        $merge = array();
        $fechasExistentes = Pacientes::distinct()->pluck('fecha_creacion')->toArray();
        $fechasExistentesCitasAutomaticas = CitasAutomaticas::distinct()->pluck('fecha_creacion')->toArray();
        $mergedArray = array_unique(array_merge($fechasExistentes, $fechasExistentesCitasAutomaticas));
        return $mergedArray;
    }
}
