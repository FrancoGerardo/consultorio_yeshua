<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Persona;
use App\Models\Propietario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear permisos para Roles
        Permission::firstOrCreate(['name' => 'gestionar-roles']);
        Permission::firstOrCreate(['name' => 'ver-roles']);
        Permission::firstOrCreate(['name' => 'crear-roles']);
        Permission::firstOrCreate(['name' => 'editar-roles']);
        Permission::firstOrCreate(['name' => 'eliminar-roles']);
        Permission::firstOrCreate(['name' => 'mostrar-roles']);

        // Crear permisos para Permisos
        Permission::firstOrCreate(['name' => 'gestionar-permisos']);
        Permission::firstOrCreate(['name' => 'ver-permisos']);
        Permission::firstOrCreate(['name' => 'crear-permisos']);
        Permission::firstOrCreate(['name' => 'editar-permisos']);
        Permission::firstOrCreate(['name' => 'eliminar-permisos']);
        Permission::firstOrCreate(['name' => 'mostrar-permisos']);

        // Crear permisos para Salas
        Permission::firstOrCreate(['name' => 'gestionar-salas']);
        Permission::firstOrCreate(['name' => 'ver-salas']);
        Permission::firstOrCreate(['name' => 'crear-salas']);
        Permission::firstOrCreate(['name' => 'editar-salas']);
        Permission::firstOrCreate(['name' => 'eliminar-salas']);
        Permission::firstOrCreate(['name' => 'mostrar-salas']);

        // Crear permisos para Usuarios
        Permission::firstOrCreate(['name' => 'gestionar-usuarios']);
        Permission::firstOrCreate(['name' => 'ver-usuarios']);
        Permission::firstOrCreate(['name' => 'crear-usuarios']);
        Permission::firstOrCreate(['name' => 'editar-usuarios']);
        Permission::firstOrCreate(['name' => 'eliminar-usuarios']);
        Permission::firstOrCreate(['name' => 'mostrar-usuarios']);

        // Crear permisos para Especialidades
        Permission::firstOrCreate(['name' => 'gestionar-especialidades']);
        Permission::firstOrCreate(['name' => 'ver-especialidades']);
        Permission::firstOrCreate(['name' => 'crear-especialidades']);
        Permission::firstOrCreate(['name' => 'editar-especialidades']);
        Permission::firstOrCreate(['name' => 'eliminar-especialidades']);
        Permission::firstOrCreate(['name' => 'mostrar-especialidades']);

        // Crear permisos para Servicios
        Permission::firstOrCreate(['name' => 'gestionar-servicios']);
        Permission::firstOrCreate(['name' => 'ver-servicios']);
        Permission::firstOrCreate(['name' => 'crear-servicios']);
        Permission::firstOrCreate(['name' => 'editar-servicios']);
        Permission::firstOrCreate(['name' => 'eliminar-servicios']);
        Permission::firstOrCreate(['name' => 'mostrar-servicios']);

        // Crear permisos para Fichas
        Permission::firstOrCreate(['name' => 'gestionar-fichas']);
        Permission::firstOrCreate(['name' => 'ver-fichas']);
        Permission::firstOrCreate(['name' => 'crear-fichas']);
        Permission::firstOrCreate(['name' => 'editar-fichas']);
        Permission::firstOrCreate(['name' => 'eliminar-fichas']);
        Permission::firstOrCreate(['name' => 'mostrar-fichas']);

        // Crear permisos para Seguimientos
        Permission::firstOrCreate(['name' => 'gestionar-seguimientos']);
        Permission::firstOrCreate(['name' => 'ver-seguimientos']);
        Permission::firstOrCreate(['name' => 'crear-seguimientos']);
        Permission::firstOrCreate(['name' => 'editar-seguimientos']);
        Permission::firstOrCreate(['name' => 'eliminar-seguimientos']);
        Permission::firstOrCreate(['name' => 'mostrar-seguimientos']);

        // Crear permisos para Historiales Clínicos
        Permission::firstOrCreate(['name' => 'gestionar-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'ver-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'crear-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'editar-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'eliminar-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'mostrar-historiales-clinicos']);

        // Crear permisos para Reportes
        Permission::firstOrCreate(['name' => 'gestionar-reportes']);
        Permission::firstOrCreate(['name' => 'ver-reportes']);
        Permission::firstOrCreate(['name' => 'crear-reportes']);
        Permission::firstOrCreate(['name' => 'editar-reportes']);
        Permission::firstOrCreate(['name' => 'eliminar-reportes']);
        Permission::firstOrCreate(['name' => 'mostrar-reportes']);

        // Crear rol Administrador
        $rolAdministrador = Role::firstOrCreate(['name' => 'Administrador']);

        // Asignar TODOS los permisos al rol Administrador
        $rolAdministrador->syncPermissions(Permission::all());

        // Crear rol Cliente
        $rolCliente = Role::firstOrCreate(['name' => 'Cliente']);
        
        // Asignar permisos básicos al rol Cliente
        $permisosCliente = Permission::whereIn('name', [
            'ver-fichas',
            'crear-fichas',
            'mostrar-fichas',
            'ver-historiales-clinicos',
            'mostrar-historiales-clinicos',
        ])->get();
        
        $rolCliente->syncPermissions($permisosCliente);

        // Crear rol Medico
        $rolMedico = Role::firstOrCreate(['name' => 'Medico']);
        
        // Asignar permisos al rol Medico
        $permisosMedico = Permission::whereIn('name', [
            'ver-fichas',
            'editar-fichas',
            'mostrar-fichas',
            'gestionar-seguimientos',
            'ver-seguimientos',
            'crear-seguimientos',
            'editar-seguimientos',
            'mostrar-seguimientos',
            'ver-historiales-clinicos',
            'editar-historiales-clinicos',
            'mostrar-historiales-clinicos',
        ])->get();
        
        $rolMedico->syncPermissions($permisosMedico);

        // Crear rol Secretaria
        $rolSecretaria = Role::firstOrCreate(['name' => 'Secretaria']);
        
        // Asignar permisos al rol Secretaria
        $permisosSecretaria = Permission::whereIn('name', [
            'gestionar-fichas',
            'ver-fichas',
            'crear-fichas',
            'editar-fichas',
            'mostrar-fichas',
            'ver-usuarios',
            'crear-usuarios',
            'editar-usuarios',
            'mostrar-usuarios',
            'ver-historiales-clinicos',
            'mostrar-historiales-clinicos',
        ])->get();
        
        $rolSecretaria->syncPermissions($permisosSecretaria);

        // Crear Persona para el administrador
        $personaId = Str::uuid()->toString();
        $persona = Persona::firstOrCreate(
            ['dni' => '00000000'],
            [
                'id' => $personaId,
                'nombre' => 'Administrador',
                'apellidos' => 'Sistema',
                'telefono' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
            ]
        );

        // Crear Usuario administrador
        $usuarioId = Str::uuid()->toString();
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'id' => $usuarioId,
                'persona_id' => $persona->id,
                'password_hash' => Hash::make('123456789'),
                'tipo_usuario' => 'PROPIETARIO',
                'estado' => true,
                'fecha_registro' => now(),
            ]
        );

        // Crear Propietario para el administrador
        Propietario::firstOrCreate(
            ['usuario_id' => $admin->id],
            [
                'nivel_acceso' => 'TOTAL',
            ]
        );

        // Asignar rol Administrador al usuario
        if (!$admin->hasRole('Administrador')) {
            $admin->assignRole('Administrador');
        }

        // ==================== USUARIO CLIENTE ====================
        $personaClienteId = Str::uuid()->toString();
        $personaCliente = Persona::firstOrCreate(
            ['dni' => '11111111'],
            [
                'id' => $personaClienteId,
                'nombre' => 'Cliente',
                'apellidos' => 'Prueba',
                'telefono' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
            ]
        );

        $clienteUsuarioId = Str::uuid()->toString();
        $usuarioCliente = User::firstOrCreate(
            ['email' => 'cliente@gmail.com'],
            [
                'id' => $clienteUsuarioId,
                'persona_id' => $personaCliente->id,
                'password_hash' => Hash::make('123456789'),
                'tipo_usuario' => 'CLIENTE',
                'estado' => true,
                'fecha_registro' => now(),
            ]
        );

        \App\Models\Cliente::firstOrCreate(['usuario_id' => $usuarioCliente->id]);

        if (!$usuarioCliente->hasRole('Cliente')) {
            $usuarioCliente->assignRole('Cliente');
        }

        // ==================== USUARIO SECRETARIA ====================
        $personaSecretariaId = Str::uuid()->toString();
        $personaSecretaria = Persona::firstOrCreate(
            ['dni' => '22222222'],
            [
                'id' => $personaSecretariaId,
                'nombre' => 'Secretaria',
                'apellidos' => 'Prueba',
                'telefono' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
            ]
        );

        $secretariaUsuarioId = Str::uuid()->toString();
        $usuarioSecretaria = User::firstOrCreate(
            ['email' => 'secretaria@gmail.com'],
            [
                'id' => $secretariaUsuarioId,
                'persona_id' => $personaSecretaria->id,
                'password_hash' => Hash::make('123456789'),
                'tipo_usuario' => 'SECRETARIA',
                'estado' => true,
                'fecha_registro' => now(),
            ]
        );

        \App\Models\Secretaria::firstOrCreate(['usuario_id' => $usuarioSecretaria->id]);

        if (!$usuarioSecretaria->hasRole('Secretaria')) {
            $usuarioSecretaria->assignRole('Secretaria');
        }

        // ==================== USUARIO MEDICO ====================
        $personaMedicoId = Str::uuid()->toString();
        $personaMedico = Persona::firstOrCreate(
            ['dni' => '33333333'],
            [
                'id' => $personaMedicoId,
                'nombre' => 'Medico',
                'apellidos' => 'Prueba',
                'telefono' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
            ]
        );

        $medicoUsuarioId = Str::uuid()->toString();
        $usuarioMedico = User::firstOrCreate(
            ['email' => 'medico@gmail.com'],
            [
                'id' => $medicoUsuarioId,
                'persona_id' => $personaMedico->id,
                'password_hash' => Hash::make('123456789'),
                'tipo_usuario' => 'MEDICO',
                'estado' => true,
                'fecha_registro' => now(),
            ]
        );

        \App\Models\Medico::firstOrCreate([
            'usuario_id' => $usuarioMedico->id,
        ]);

        if (!$usuarioMedico->hasRole('Medico')) {
            $usuarioMedico->assignRole('Medico');
        }

        // ==================== ESPECIALIDADES ====================
        $especialidades = [
            ['nombre' => 'Medicina General', 'descripcion' => 'Atención médica integral y preventiva', 'estado' => 'ACTIVA'],
            ['nombre' => 'Pediatría', 'descripcion' => 'Atención médica para niños y adolescentes', 'estado' => 'ACTIVA'],
            ['nombre' => 'Cardiología', 'descripcion' => 'Especialidad del corazón y sistema circulatorio', 'estado' => 'ACTIVA'],
            ['nombre' => 'Dermatología', 'descripcion' => 'Tratamiento de enfermedades de la piel', 'estado' => 'ACTIVA'],
            ['nombre' => 'Ginecología', 'descripcion' => 'Salud reproductiva de la mujer', 'estado' => 'ACTIVA'],
            ['nombre' => 'Traumatología', 'descripcion' => 'Lesiones del sistema músculo-esquelético', 'estado' => 'ACTIVA'],
            ['nombre' => 'Oftalmología', 'descripcion' => 'Enfermedades y cirugía de los ojos', 'estado' => 'ACTIVA'],
            ['nombre' => 'Otorrinolaringología', 'descripcion' => 'Oídos, nariz y garganta', 'estado' => 'ACTIVA'],
            ['nombre' => 'Neurología', 'descripcion' => 'Sistema nervioso y enfermedades cerebrales', 'estado' => 'ACTIVA'],
            ['nombre' => 'Psiquiatría', 'descripcion' => 'Salud mental y trastornos psicológicos', 'estado' => 'ACTIVA'],
            ['nombre' => 'Urología', 'descripcion' => 'Sistema urinario y reproductivo masculino', 'estado' => 'ACTIVA'],
            ['nombre' => 'Gastroenterología', 'descripcion' => 'Sistema digestivo y enfermedades intestinales', 'estado' => 'ACTIVA'],
            ['nombre' => 'Endocrinología', 'descripcion' => 'Hormonas y glándulas endocrinas', 'estado' => 'ACTIVA'],
            ['nombre' => 'Neumología', 'descripcion' => 'Enfermedades respiratorias y pulmonares', 'estado' => 'ACTIVA'],
            ['nombre' => 'Reumatología', 'descripcion' => 'Enfermedades articulares y autoinmunes', 'estado' => 'ACTIVA'],
            ['nombre' => 'Oncología', 'descripcion' => 'Diagnóstico y tratamiento del cáncer', 'estado' => 'ACTIVA'],
            ['nombre' => 'Nefrología', 'descripcion' => 'Enfermedades renales y diálisis', 'estado' => 'ACTIVA'],
            ['nombre' => 'Hematología', 'descripcion' => 'Enfermedades de la sangre', 'estado' => 'ACTIVA'],
            ['nombre' => 'Cirugía General', 'descripcion' => 'Procedimientos quirúrgicos generales', 'estado' => 'ACTIVA'],
            ['nombre' => 'Cirugía Plástica', 'descripcion' => 'Cirugía reconstructiva y estética', 'estado' => 'ACTIVA'],
            ['nombre' => 'Anestesiología', 'descripcion' => 'Manejo del dolor y anestesia', 'estado' => 'ACTIVA'],
            ['nombre' => 'Radiología', 'descripcion' => 'Diagnóstico por imágenes médicas', 'estado' => 'ACTIVA'],
            ['nombre' => 'Medicina Interna', 'descripcion' => 'Diagnóstico y tratamiento de adultos', 'estado' => 'ACTIVA'],
            ['nombre' => 'Geriatría', 'descripcion' => 'Atención médica para adultos mayores', 'estado' => 'ACTIVA'],
            ['nombre' => 'Alergología', 'descripcion' => 'Alergias y enfermedades inmunológicas', 'estado' => 'ACTIVA'],
            ['nombre' => 'Infectología', 'descripcion' => 'Enfermedades infecciosas', 'estado' => 'ACTIVA'],
            ['nombre' => 'Nutrición', 'descripcion' => 'Alimentación y dietética', 'estado' => 'ACTIVA'],
            ['nombre' => 'Fisioterapia', 'descripcion' => 'Rehabilitación física', 'estado' => 'ACTIVA'],
            ['nombre' => 'Odontología', 'descripcion' => 'Salud bucal y dental', 'estado' => 'ACTIVA'],
            ['nombre' => 'Medicina del Deporte', 'descripcion' => 'Lesiones y rendimiento deportivo', 'estado' => 'ACTIVA'],
        ];

        foreach ($especialidades as $esp) {
            \App\Models\Especialidad::firstOrCreate(
                ['nombre' => $esp['nombre']],
                [
                    'id' => Str::uuid()->toString(),
                    'descripcion' => $esp['descripcion'],
                    'estado' => $esp['estado'],
                ]
            );
        }

        // ==================== SALAS ====================
        $salas = [
            ['numero' => '101', 'categoria' => 'CONSULTORIO', 'capacidad' => 2, 'equipamiento' => 'Camilla, escritorio, silla, computadora, tensiómetro'],
            ['numero' => '102', 'categoria' => 'CONSULTORIO', 'capacidad' => 2, 'equipamiento' => 'Camilla, escritorio, silla, computadora, estetoscopio'],
            ['numero' => '103', 'categoria' => 'CONSULTORIO', 'capacidad' => 2, 'equipamiento' => 'Camilla, escritorio, silla, computadora, otoscopio'],
            ['numero' => '201', 'categoria' => 'LABORATORIO', 'capacidad' => 4, 'equipamiento' => 'Microscopio, centrífuga, refrigerador, analizador automático'],
            ['numero' => '202', 'categoria' => 'RAYOS_X', 'capacidad' => 3, 'equipamiento' => 'Equipo de rayos X digital, delantales de plomo, sistema PACS'],
            ['numero' => '301', 'categoria' => 'QUIROFANO', 'capacidad' => 6, 'equipamiento' => 'Mesa quirúrgica, lámpara cialítica, monitor de signos vitales, anestesia'],
            ['numero' => '302', 'categoria' => 'QUIROFANO', 'capacidad' => 6, 'equipamiento' => 'Mesa quirúrgica, lámpara cialítica, electrobisturí, desfibrilador'],
            ['numero' => '401', 'categoria' => 'EMERGENCIA', 'capacidad' => 5, 'equipamiento' => 'Camilla de emergencia, desfibrilador, monitor multiparamétrico, ventilador'],
            ['numero' => '501', 'categoria' => 'UCI', 'capacidad' => 8, 'equipamiento' => 'Camas especiales, ventiladores mecánicos, monitores avanzados, bomba de infusión'],
            ['numero' => '104', 'categoria' => 'CONSULTORIO', 'capacidad' => 2, 'equipamiento' => 'Camilla, escritorio, electrocardiografo, nebulizador'],
        ];

        foreach ($salas as $sala) {
            \App\Models\Sala::firstOrCreate(
                ['numero' => $sala['numero']],
                [
                    'id' => Str::uuid()->toString(),
                    'categoria' => $sala['categoria'],
                    'capacidad' => $sala['capacidad'],
                    'equipamiento' => $sala['equipamiento'],
                    'estado' => 'DISPONIBLE',
                ]
            );
        }

        // ==================== CLIENTES ADICIONALES ====================
        for ($i = 1; $i <= 10; $i++) {
            $dni = str_pad(10000000 + $i, 8, '0', STR_PAD_LEFT);
            
            $personaClienteId = Str::uuid()->toString();
            $personaCliente = Persona::firstOrCreate(
                ['dni' => $dni],
                [
                    'id' => $personaClienteId,
                    'nombre' => "Cliente{$i}",
                    'apellidos' => "Apellido{$i}",
                    'telefono' => "7000000{$i}",
                    'direccion' => "Calle {$i} #123",
                    'fecha_nacimiento' => '1990-01-01',
                ]
            );

            $clienteUsuarioId = Str::uuid()->toString();
            $usuarioCliente = User::firstOrCreate(
                ['email' => "cliente{$i}@gmail.com"],
                [
                    'id' => $clienteUsuarioId,
                    'persona_id' => $personaCliente->id,
                    'password_hash' => Hash::make('123456789'),
                    'tipo_usuario' => 'CLIENTE',
                    'estado' => true,
                    'fecha_registro' => now(),
                ]
            );

            \App\Models\Cliente::firstOrCreate(['usuario_id' => $usuarioCliente->id]);

            if (!$usuarioCliente->hasRole('Cliente')) {
                $usuarioCliente->assignRole('Cliente');
            }
        }

        // ==================== MÉDICOS ADICIONALES CON ESPECIALIDADES Y HORARIOS ====================
        
        // Mapeo de médicos a especialidades (nombres más descriptivos)
        $medicosConfig = [
            1 => ['nombre' => 'Juan', 'apellidos' => 'Pérez García', 'especialidades' => ['Medicina General'], 'horario' => 'completo'],
            2 => ['nombre' => 'María', 'apellidos' => 'López Rodríguez', 'especialidades' => ['Pediatría'], 'horario' => 'manana'],
            3 => ['nombre' => 'Carlos', 'apellidos' => 'Gómez Fernández', 'especialidades' => ['Cardiología'], 'horario' => 'tarde'],
            4 => ['nombre' => 'Ana', 'apellidos' => 'Martínez Silva', 'especialidades' => ['Ginecología'], 'horario' => 'completo'],
            5 => ['nombre' => 'Luis', 'apellidos' => 'Sánchez Morales', 'especialidades' => ['Traumatología'], 'horario' => 'completo'],
            6 => ['nombre' => 'Elena', 'apellidos' => 'Ramírez Torres', 'especialidades' => ['Dermatología'], 'horario' => 'manana'],
            7 => ['nombre' => 'Diego', 'apellidos' => 'Flores Vargas', 'especialidades' => ['Oftalmología'], 'horario' => 'tarde'],
            8 => ['nombre' => 'Patricia', 'apellidos' => 'Cruz Mendoza', 'especialidades' => ['Neurología'], 'horario' => 'completo'],
            9 => ['nombre' => 'Roberto', 'apellidos' => 'Jiménez Castro', 'especialidades' => ['Urología'], 'horario' => 'manana'],
            10 => ['nombre' => 'Laura', 'apellidos' => 'Herrera Rojas', 'especialidades' => ['Endocrinología'], 'horario' => 'tarde'],
        ];

        foreach ($medicosConfig as $i => $config) {
            $dni = str_pad(40000000 + $i, 8, '0', STR_PAD_LEFT);
            
            $personaMedicoId = Str::uuid()->toString();
            $personaMedico = Persona::firstOrCreate(
                ['dni' => $dni],
                [
                    'id' => $personaMedicoId,
                    'nombre' => "Dr. {$config['nombre']}",
                    'apellidos' => $config['apellidos'],
                    'telefono' => "7100000{$i}",
                    'direccion' => "Av. Médica {$i} #456",
                    'fecha_nacimiento' => '1980-01-01',
                ]
            );

            $medicoUsuarioId = Str::uuid()->toString();
            $usuarioMedico = User::firstOrCreate(
                ['email' => "medico{$i}@gmail.com"],
                [
                    'id' => $medicoUsuarioId,
                    'persona_id' => $personaMedico->id,
                    'password_hash' => Hash::make('123456789'),
                    'tipo_usuario' => 'MEDICO',
                    'estado' => true,
                    'fecha_registro' => now(),
                ]
            );

            $medico = \App\Models\Medico::firstOrCreate([
                'usuario_id' => $usuarioMedico->id,
            ]);

            if (!$usuarioMedico->hasRole('Medico')) {
                $usuarioMedico->assignRole('Medico');
            }

            // Asignar especialidades al médico
            foreach ($config['especialidades'] as $nombreEsp) {
                $especialidad = \App\Models\Especialidad::where('nombre', $nombreEsp)->first();
                if ($especialidad) {
                    // Verificar si ya existe la relación
                    $existeRelacion = \Illuminate\Support\Facades\DB::table('medico_especialidad')
                        ->where('medico_id', $medico->usuario_id)
                        ->where('especialidad_id', $especialidad->id)
                        ->exists();

                    if (!$existeRelacion) {
                        \Illuminate\Support\Facades\DB::table('medico_especialidad')->insert([
                            'id' => Str::uuid()->toString(),
                            'medico_id' => $medico->usuario_id,
                            'especialidad_id' => $especialidad->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Crear horarios de atención según tipo
            $diasSemana = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];
            
            switch ($config['horario']) {
                case 'completo': // 8:00-12:00 y 14:00-18:00
                    foreach ($diasSemana as $dia) {
                        // Turno mañana
                        \App\Models\HorarioMedico::firstOrCreate(
                            [
                                'medico_id' => $medico->usuario_id,
                                'dia_semana' => $dia,
                                'hora_inicio' => '08:00:00',
                            ],
                            [
                                'id' => Str::uuid()->toString(),
                                'hora_fin' => '12:00:00',
                                'activo' => true,
                            ]
                        );
                        // Turno tarde
                        \App\Models\HorarioMedico::firstOrCreate(
                            [
                                'medico_id' => $medico->usuario_id,
                                'dia_semana' => $dia,
                                'hora_inicio' => '14:00:00',
                            ],
                            [
                                'id' => Str::uuid()->toString(),
                                'hora_fin' => '18:00:00',
                                'activo' => true,
                            ]
                        );
                    }
                    break;

                case 'manana': // Solo 8:00-13:00
                    foreach ($diasSemana as $dia) {
                        \App\Models\HorarioMedico::firstOrCreate(
                            [
                                'medico_id' => $medico->usuario_id,
                                'dia_semana' => $dia,
                                'hora_inicio' => '08:00:00',
                            ],
                            [
                                'id' => Str::uuid()->toString(),
                                'hora_fin' => '13:00:00',
                                'activo' => true,
                            ]
                        );
                    }
                    break;

                case 'tarde': // Solo 14:00-19:00
                    foreach ($diasSemana as $dia) {
                        \App\Models\HorarioMedico::firstOrCreate(
                            [
                                'medico_id' => $medico->usuario_id,
                                'dia_semana' => $dia,
                                'hora_inicio' => '14:00:00',
                            ],
                            [
                                'id' => Str::uuid()->toString(),
                                'hora_fin' => '19:00:00',
                                'activo' => true,
                            ]
                        );
                    }
                    break;
            }
        }

        // ==================== SERVICIOS (DESPUÉS DE CREAR MÉDICOS) ====================
        $serviciosPorEspecialidad = [
            'Medicina General' => [
                ['nombre' => 'Consulta General', 'costo' => 1.50, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Control de Salud', 'costo' => 1.20, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Pediatría' => [
                ['nombre' => 'Consulta Pediátrica', 'costo' => 1.80, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Control de Niño Sano', 'costo' => 1.50, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Cardiología' => [
                ['nombre' => 'Consulta Cardiológica', 'costo' => 2.00, 'duracion' => 45, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Electrocardiograma', 'costo' => 1.70, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Ginecología' => [
                ['nombre' => 'Consulta Ginecológica', 'costo' => 1.90, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Control Prenatal', 'costo' => 1.60, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Traumatología' => [
                ['nombre' => 'Consulta Traumatológica', 'costo' => 1.80, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Infiltración', 'costo' => 2.00, 'duracion' => 45, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Dermatología' => [
                ['nombre' => 'Consulta Dermatológica', 'costo' => 1.60, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Crioterapia', 'costo' => 1.90, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Oftalmología' => [
                ['nombre' => 'Consulta Oftalmológica', 'costo' => 1.70, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Examen de Vista', 'costo' => 1.40, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Neurología' => [
                ['nombre' => 'Consulta Neurológica', 'costo' => 2.00, 'duracion' => 45, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Electroencefalograma', 'costo' => 1.90, 'duracion' => 60, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Urología' => [
                ['nombre' => 'Consulta Urológica', 'costo' => 1.80, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Ecografía Prostática', 'costo' => 1.50, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
            'Endocrinología' => [
                ['nombre' => 'Consulta Endocrinológica', 'costo' => 1.90, 'duracion' => 45, 'categoria' => 'ESPECIALIDAD'],
                ['nombre' => 'Control de Diabetes', 'costo' => 1.60, 'duracion' => 30, 'categoria' => 'ESPECIALIDAD'],
            ],
        ];

        echo "\n🔄 Asignando servicios a médicos...\n";

        foreach ($serviciosPorEspecialidad as $nombreEsp => $servicios) {
            $especialidad = \App\Models\Especialidad::where('nombre', $nombreEsp)->first();
            
            if ($especialidad) {
                foreach ($servicios as $srv) {
                    // Crear el servicio
                    $servicio = \App\Models\Servicio::firstOrCreate(
                        [
                            'nombre' => $srv['nombre'],
                            'especialidad_id' => $especialidad->id,
                        ],
                        [
                            'id' => Str::uuid()->toString(),
                            'descripcion' => "Servicio de {$srv['nombre']} en {$nombreEsp}",
                            'categoria' => $srv['categoria'],
                            'costo' => $srv['costo'],
                            'duracion_minutos' => $srv['duracion'],
                            'estado' => true,
                        ]
                    );

                    // Obtener TODOS los médicos que tienen esta especialidad
                    $medicos = \App\Models\Medico::whereHas('especialidades', function($q) use ($especialidad) {
                        $q->where('especialidades.id', $especialidad->id);
                    })->get();

                    echo "   ✓ Servicio: {$srv['nombre']} ({$nombreEsp}) - Médicos encontrados: {$medicos->count()}\n";

                    // Asignar el servicio a cada médico
                    foreach ($medicos as $medico) {
                        $existeRelacion = \Illuminate\Support\Facades\DB::table('medico_servicios')
                            ->where('medico_id', $medico->usuario_id)
                            ->where('servicio_id', $servicio->id)
                            ->exists();

                        if (!$existeRelacion) {
                            \Illuminate\Support\Facades\DB::table('medico_servicios')->insert([
                                'id' => Str::uuid()->toString(),
                                'medico_id' => $medico->usuario_id,
                                'servicio_id' => $servicio->id,
                                'activo' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            echo "     → Asignado a médico ID: {$medico->usuario_id}\n";
                        }
                    }
                }
            }
        }

        echo "\n✅ Servicios asignados correctamente a todos los médicos\n\n";

        // Llamar al seeder de items de menú
        $this->call(ItemMenuSeeder::class);

        // Llamar al seeder de configuración de pagos
        $this->call(ConfiguracionPagoSeeder::class);
    }
}
