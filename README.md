# prueba tecnica sistema agenda digital
* Realizado en laravel 10 y PHP 8.1

* Jquery y FullCalendar js

### instalacion
* clona el proyecto del repositorio

* crear la base de datos agenda_digital ejecutando el script adjunto en el repositorio scripts_bd.sql que ya contiene las tablas y registros directos

* instalar dependencias del proyecto
  - composer install

* crear archivo .env y configurar segun crea conveniente

* generar key
  - php artisan key:generate

* no se usa migraciones, una vez creada la base de datos con las tablas, crear los modelos correspondienetes