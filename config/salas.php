<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Duración por defecto de una cita (minutos)
    |--------------------------------------------------------------------------
    */
    'duracion_default_minutos' => 30,

    /*
    |--------------------------------------------------------------------------
    | Tipos de sala disponibles en la clínica
    |--------------------------------------------------------------------------
    */
    'tipos' => [
        'CONSULTORIO',
        'LABORATORIO',
        'RAYOS_X',
        'QUIROFANO',
        'EMERGENCIA',
        'UCI',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo categoría de servicio → tipos de sala compatibles
    |--------------------------------------------------------------------------
    */
    'categoria_servicio_a_tipos_sala' => [
        'ESPECIALIDAD' => ['CONSULTORIO'],
        'INTERNACION' => ['UCI', 'EMERGENCIA'],
        'ENFERMERIA' => ['CONSULTORIO', 'EMERGENCIA'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Consultorio fijo por médico (usuario_id => sala_id)
    |--------------------------------------------------------------------------
    */
    'medico_sala_fija' => [
        // 'uuid-medico' => 'uuid-sala',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de sala que permiten asignación
    |--------------------------------------------------------------------------
    */
    'estados_asignables' => ['DISPONIBLE', 'OCUPADA'],

];
