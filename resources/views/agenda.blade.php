@extends('welcome')

@section('content')
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
      <div class="toast-header">
        <strong class="me-auto" id="mensaje"></strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
  
  <form class="row g-2">
    <h4>Datos paciente</h4>
    @csrf
    <div class="col-md">
      <input type="text" class="form-control" placeholder="Nombre" id="nombre" maxLength="100" @if(isset($paciente) && !empty($paciente)) value="{{ $paciente->nombre }}" readonly @endif>
    </div>
    <div class="col-md">
      <input type="text" class="form-control" placeholder="Dirección" id="direccion" maxLength="100" @if(isset($paciente) && !empty($paciente)) value="{{ $paciente->direccion }}" readonly @endif>
    </div>
    <div class="col-md">
      <input type="text" class="form-control" placeholder="Teléfono" id="telefono" maxLength="10" @if(isset($paciente) && !empty($paciente)) value="{{ $paciente->telefono }}" readonly @endif>
    </div>
  </form>
  @if(isset($paciente) && !empty($paciente))
    <div class="row align-items-center my-4">
      <div class="col-auto">
        <h4>Fecha cita creada</h4>
      </div>
      <div class="col-auto">
        <span>{{ Carbon\Carbon::parse($paciente->fecha_creacion)->format('d/m/Y') }}</span>
      </div>
    </div>
    @if(!$paciente->cita_automatica)
      <form class="row g-2 my-4">
        <h4>Crear citas automáticas</h4>
        <div class="col-md">
          <input type="text" class="form-control" placeholder="Cantidad de citas" id="cantidad-citas" maxLength="2">
        </div>
        <div class="col-md">
          <input type="text" class="form-control" placeholder="Días de diferencia" id="dias-diferencia" maxLength="2">
        </div>
      </form>
    @else
      @include('listaCitasAutomaticas', ['citas' => $citasAutomaticas])
    @endif
  @endif
  
  @if(!isset($paciente) && empty($paciente) || $paciente->cita_automatica)
    <div id='calendar' class="my-5"></div>
  @endif

  <div class="row text-end mb-5">
    <div class="col-md">
      <button class="btn btn-secondary" onclick="window.location='{{ route('index') }}'" id="btnRegresar">Regresar</button>
    </div>
    @if(!isset($paciente) && empty($paciente) || !$paciente->cita_automatica)
      <div class="col-auto">
        <button class="btn btn-primary" 
          id="{{ isset($paciente) && !empty($paciente) ? 'btnAgendarAutomatica' : 'btnAgendar'}}">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" id="spinner" hidden></span>
          {{ isset($paciente) && !empty($paciente) ? 'Agendar cita automática' : 'Agendar cita'}}
        </button>
      </div>
    @endif
  </div>
@endsection

@section('script')
  <script>
    $(document).ready(function() {
      // calendario
      var fechaInicio = '';
      var selectedEvent = null;
      var calendarEl = document.getElementById('calendar');
      if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'timeGridWeek',
          selectable: true,
          allDaySlot: false,
          slotMinTime: '06:00:00',
          slotMaxTime: '18:00:00',
          locale: 'es',
          hiddenDays: [0, 6],
          slotLabelFormat: {
            hour: 'numeric',
            minute: '2-digit',
            hour12: false
          },
          events: [
            @foreach($fechasExistentes as $fecha) {
              title: 'No disponible',
              start: '{{ $fecha }}',
              editable: false,
            },
            @endforeach
          ],
          selectAllow: function(info) {
            var today = new Date().setHours(0, 0, 0, 0);
            return info.start >= today;
          },
          select: function(info) {
            if (selectedEvent) selectedEvent.remove();

            selectedEvent = calendar.addEvent({
              title: 'Horario seleccionado',
              start: info.start,
              end: info.end,
              classNames: ['selected'],
              overlap: false
            });

            fechaInicio = info.start.toLocaleString().replace(',', '');
            calendar.unselect();
          },
        });
        calendar.render();
      }

      // eventos
      $('#nombre').on('keypress paste', function(e) { validarRegex(e, /[a-zA-Z]/); });
      $('#direccion').on('keypress paste', function(e) { validarRegex(e, /[a-zA-Z0-9@,._ ]/); });
      $('#telefono').on('keypress paste', function(e) { validarRegex(e, /[0-9]/); });
      $('#cantidad-citas, #dias-diferencia').on('keypress paste', function(e) { validarRegex(e, /[1-9]/); });

      $('#btnAgendar').on('click', function(e) {
        var nombre = $('#nombre').val();
        var direccion = $('#direccion').val();
        var telefono = $('#telefono').val();

        if (!nombre || !direccion || !telefono || !fechaInicio) {
          mostrarToast('Faltan ingresar datos');
        } else {
          mostrarSpinner($('#btnAgendar'), true);
          $.ajax({
            url: '{{ route('guardarCita') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {nombre:nombre, direccion:direccion, telefono:telefono, fechaCreacion:fechaInicio},
            dataType: 'json',
            success: function(response) {
              $(this).prop('disabled', true);
              mostrarToast('Registro existoso.');
              window.location='{{ route('mostrarCita', ['id' => ':id']) }}'.replace(':id', response);
            },
            error: function(xhr, status, error) {
              mostrarSpinner($('#btnAgendar'), false);
              if (xhr.status === 400) {
                mostrarToast(xhr.responseJSON.mensaje);
              } else {
                mostrarToast('Ha ocurrido un error. Intente más tarde.');
              }
            }
          });
        }
      });

      $('#btnAgendarAutomatica').on('click', function(e) {
        var id = '@if(isset($paciente) && !empty($paciente)) {{ $paciente->id }} @endif';
        var fechaCreacion = '@if(isset($paciente) && !empty($paciente)) {{ $paciente->fecha_creacion }} @endif';
        var cantidadCitas = $('#cantidad-citas').val();
        var diasDiferencia = $('#dias-diferencia').val();

        if (!cantidadCitas || !diasDiferencia) {
          mostrarToast('Faltan ingresar datos');
        } else {
          mostrarSpinner($('#btnAgendarAutomatica'), true);
          $.ajax({
            url: '{{ route('guardarCitasAutomaticas') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {cantidadCitas:cantidadCitas, diasDiferencia:diasDiferencia, fechaCreacion:fechaCreacion, id:id},
            dataType: 'json',
            success: function(response) {
              $(this).prop('disabled', true);
              mostrarToast('Registro existoso.');
              window.location='{{ route('mostrarCita', ['id' => ':id']) }}'.replace(':id', response);
            },
            error: function(xhr, status, error) {
              mostrarSpinner($('#btnAgendarAutomatica'), false);
              if (xhr.status === 400) {
                mostrarToast(xhr.responseJSON.mensaje);
              } else {
                mostrarToast('Ha ocurrido un error. Intente más tarde.');
              }
            }
          });
        }
      });
      
      //metodos
      function validarRegex(e, r) {
        var inputChar = String.fromCharCode(e.charCode);
        if (!r.test(inputChar)) e.preventDefault();
      }

      function mostrarToast(mensaje) {
        $('#mensaje').text(mensaje);
        const toastLiveExample = document.getElementById('liveToast')
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample);
        toastBootstrap.show();
        window.scrollTo({top: 0, behavior: 'smooth'});
      }

      function mostrarSpinner(el, b) {
        $('#spinner').prop('hidden', !b);
        $('#btnRegresar').prop('disabled', b);
        $(el).prop('disabled', b);
      }
    });
  </script>
@endsection
