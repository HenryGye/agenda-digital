<h4 class="text-start">Citas automáticas</h4>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Secuencia</th>
      <th scope="col">Fecha</th>
      <th scope="col">Hora</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($citas as $c => $cita)
      <tr>
        <td>Cita Automática {{ $c + 1 }}</td>
        <td>{{ Carbon\Carbon::parse($cita->fecha_creacion)->format('d/m/Y') }}</td>
        <td>{{ Carbon\Carbon::parse($cita->fecha_creacion)->format('H:i:s') }}</td>
      </tr>
    @endforeach
  </tbody>
</table>