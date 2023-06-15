create database agenda_digital;
-- drop database agenda_digital;

create table agenda_digital.pacientes (
  id int auto_increment primary key,
  nombre varchar(255),
  direccion varchar(255),
  telefono varchar(10),
  fecha_creacion datetime,
  cita_automatica boolean
);

create table agenda_digital.citas_automaticas (
  id int auto_increment primary key,
  fecha_creacion datetime,
  paciente_id int,
  foreign key (paciente_id) references pacientes(id)
);

INSERT INTO agenda_digital.pacientes (nombre, direccion, telefono, fecha_creacion, cita_automatica) VALUES
('Henry', 'Calle 1', '1234567890', '2023-06-14 08:00:00', false),
('Maria', 'Calle 3', '4561237890', '2023-06-14 14:00:00', false),
('David', 'Calle 5', '6547893210', '2023-06-15 08:30:00', false);