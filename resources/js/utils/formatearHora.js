/**
 * Extrae HH:MM de un valor TIME (09:30:00) o datetime ISO legacy sin conversión de zona horaria.
 */
export function formatearHoraCita(hora) {
    if (!hora) {
        return 'N/A';
    }

    if (typeof hora === 'string') {
        if (/^\d{2}:\d{2}/.test(hora) && !hora.includes('T')) {
            return hora.substring(0, 5);
        }

        const matchIso = hora.match(/T(\d{2}):(\d{2})/);
        if (matchIso) {
            return `${matchIso[1]}:${matchIso[2]}`;
        }
    }

    return String(hora);
}

/**
 * Combina fecha (DATE) y hora (TIME) como Date local, sin desfase UTC.
 */
export function obtenerDateTimeCita(fecha, hora) {
    if (!fecha || !hora) {
        return null;
    }

    const fechaTexto = String(fecha).substring(0, 10);
    const horaTexto = formatearHoraCita(hora);

    if (!/^\d{4}-\d{2}-\d{2}$/.test(fechaTexto) || horaTexto === 'N/A') {
        return null;
    }

    const [anio, mes, dia] = fechaTexto.split('-').map(Number);
    const [horas, minutos] = horaTexto.split(':').map(Number);

    return new Date(anio, mes - 1, dia, horas, minutos, 0);
}

/** Minutos que faltan para la hora de cita (positivo = aún no es la hora). */
export function minutosAntesDeCita(fecha, hora, referencia = new Date()) {
    const cita = obtenerDateTimeCita(fecha, hora);
    if (!cita) {
        return 0;
    }

    return Math.max(0, Math.round((cita.getTime() - referencia.getTime()) / 60000));
}

/** true si la referencia es anterior a la hora de la cita. */
export function esAntesDeHoraCita(fecha, hora, referencia = new Date()) {
    const cita = obtenerDateTimeCita(fecha, hora);
    if (!cita) {
        return false;
    }

    return referencia.getTime() < cita.getTime();
}

/** Compara dos fichas por fecha+hora de cita. Negativo = a es antes que b. */
export function compararHoraCita(fichaA, fichaB) {
    const citaA = obtenerDateTimeCita(fichaA?.fecha, fichaA?.hora)?.getTime() ?? 0;
    const citaB = obtenerDateTimeCita(fichaB?.fecha, fichaB?.hora)?.getTime() ?? 0;

    return citaA - citaB;
}

/** true si el check-in fue antes de la hora programada de la cita. */
export function llegoAntesDeCita(fechaLlegada, fechaCita, horaCita) {
    if (!fechaLlegada) {
        return false;
    }

    const cita = obtenerDateTimeCita(fechaCita, horaCita);
    if (!cita) {
        return false;
    }

    return new Date(fechaLlegada).getTime() < cita.getTime();
}
