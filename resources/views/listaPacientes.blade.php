@extends('welcome')

@section('content')
<div class="d-flex justify-content-between">
  <h4 class="text-start">Listado de pacientes</h4>
  <div>
    <button type="submit" class="btn btn-primary" onclick="window.location='{{ route('crearCita') }}'">Crear cita</button>
  </div>
</div>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Nombre</th>
      <th scope="col">Dirección</th>
      <th scope="col">Teléfono</th>
      <th scope="col">Fecha cita creada</th>
      <th scope="col">Cita automática</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    @forelse ($pacientes as $paciente)
      <tr>
        <td>{{ $paciente->nombre }}</td>
        <td>{{ $paciente->direccion }}</td>
        <td>{{ $paciente->telefono }}</td>
        <td>{{ Carbon\Carbon::parse($paciente->fecha_creacion)->format('d/m/Y') }}</td>
        <td>{{ $paciente->cita_automatica ? 'SI' : 'NO' }}</td>
        <td><button type="button" class="btn btn-success btn-sm" onclick="window.location='{{ route('mostrarCita', ['id' => $paciente->id]) }}'">Ver</button></td>
      </tr>
    @empty
      <tr>
        <td>No hay pacientes</td>
      </tr>
    @endforelse
  </tbody>
</table>
@endsection