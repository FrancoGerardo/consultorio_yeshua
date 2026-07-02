# 👁️ GUÍA VISUAL - SISTEMA DE REPORTES

## 📺 QUÉ ESPERAR VER EN CADA PASO

---

## 1️⃣ PÁGINA PRINCIPAL DE GENERACIÓN

**URL:** `http://localhost:8000/reportes/generar`

### **Sección 1: Selección de Tipo de Reporte**
```
┌─────────────────────────────────────────────────────────┐
│  1. Seleccione el Tipo de Reporte                       │
│                                                          │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐         │
│  │    📅    │    │    💰    │    │   👨‍⚕️    │         │
│  │  Reporte │    │  Reporte │    │ Pacientes│         │
│  │ de Citas │    │   de     │    │    por   │         │
│  │          │    │ Ingresos │    │  Médico  │         │
│  └──────────┘    └──────────┘    └──────────┘         │
└─────────────────────────────────────────────────────────┘
```

**Visual:** 3 tarjetas con iconos grandes, al hacer clic se resalta en azul/verde/morado

---

### **Sección 2: Filtros Dinámicos**
```
┌─────────────────────────────────────────────────────────┐
│  2. Configure los Filtros                               │
│                                                          │
│  Fecha Inicio: [________] Fecha Fin: [________]        │
│                                                          │
│  Estado de Cita: [Todos los estados ▼]                 │
│                                                          │
│  Médico: [Todos los médicos ▼]                         │
└─────────────────────────────────────────────────────────┘
```

**Filtros según tipo seleccionado:**
- **Citas:** Estado, Médico
- **Ingresos:** Método de Pago
- **Pacientes por Médico:** Médico

---

### **Sección 3: Botones de Generación**
```
┌─────────────────────────────────────────────────────────┐
│  3. Genere el Reporte                                   │
│                                                          │
│  ☑ 📧 Enviar reporte por correo electrónico            │
│                                                          │
│  ┌────────────────────┐  ┌────────────────────┐       │
│  │  📄 Generar PDF    │  │  📊 Generar Excel  │       │
│  └────────────────────┘  └────────────────────┘       │
│                                                          │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━         │
│  💡 Generación en segundo plano: Para reportes         │
│     grandes, puede programarlo...                       │
│                                                          │
│  ┌────────────────────┐  ┌────────────────────┐       │
│  │ ⏰ Programar PDF   │  │ ⏰ Programar Excel │       │
│  └────────────────────┘  └────────────────────┘       │
└─────────────────────────────────────────────────────────┘
```

**Colores:**
- PDF: Botón rojo (descarga instantánea)
- Excel: Botón verde (descarga instantánea)
- Programar: Botones morados (en segundo plano)

---

### **Sección 4: Historial de Reportes**
```
┌─────────────────────────────────────────────────────────┐
│  Reportes Generados Recientemente      [🔄 Actualizar] │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│                                                          │
│  Reporte de Citas                                       │
│  15/12/2025 14:30 - Formato: PDF                       │
│  [Descargar]  [Eliminar]                               │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
│                                                          │
│  Reporte de Ingresos                                    │
│  15/12/2025 14:25 - Formato: EXCEL                     │
│  [Descargar]  [Eliminar]                               │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │
└─────────────────────────────────────────────────────────┘
```

---

## 2️⃣ CONTENIDO DE LOS REPORTES PDF

### **Reporte de Citas**
```
═══════════════════════════════════════════════════════════
         CONSULTORIO MEDICO YESHUA
              Reporte de Citas
         Generado el: 15/12/2025 14:30
═══════════════════════════════════════════════════════════

Filtros aplicados:
Desde: 01/12/2025
Hasta: 15/12/2025

┌────────────────────────────────────────────────────┐
│ Estadísticas Generales                              │
│                                                      │
│ Total de Citas: 45                                  │
│                                                      │
│ Por Estado:                                         │
│   AGENDADA: 15                                      │
│   REALIZADA: 25                                     │
│   CANCELADA: 5                                      │
└────────────────────────────────────────────────────┘

┏━━━━━━━━━━┳━━━━━━┳━━━━━━━━━━━┳━━━━━━━━━━━┓
┃  Fecha   ┃ Hora ┃ Paciente  ┃  Médico   ┃
┣━━━━━━━━━━╋━━━━━━╋━━━━━━━━━━━╋━━━━━━━━━━━┫
┃15/12/2025┃ 9:00 ┃Juan Pérez ┃Dr. García ┃
┃15/12/2025┃10:00 ┃Ana López  ┃Dr. Sánchez┃
┗━━━━━━━━━━┻━━━━━━┻━━━━━━━━━━━┻━━━━━━━━━━━┛
```

**Características visuales:**
- ✅ Encabezado con logo de la clínica
- ✅ Estadísticas resaltadas en caja de color
- ✅ Tabla con filas alternadas (gris/blanco)
- ✅ Footer con información de la clínica

---

### **Reporte de Ingresos**
```
═══════════════════════════════════════════════════════════
         CONSULTORIO MEDICO YESHUA
             Reporte de Ingresos
         Generado el: 15/12/2025 14:30
═══════════════════════════════════════════════════════════

┌────────────────────────────────────────────────────┐
│ Resumen Financiero                                  │
│                                                      │
│ TOTAL INGRESOS: Bs. 15,450.00                      │
│                                                      │
│ Total de Transacciones: 35                          │
│ Promedio por Transacción: Bs. 441.43               │
│                                                      │
│ Por Método de Pago:                                 │
│   EFECTIVO: 10 transacciones = Bs. 4,200.00        │
│   TARJETA: 15 transacciones = Bs. 7,500.00         │
│   QR: 10 transacciones = Bs. 3,750.00              │
└────────────────────────────────────────────────────┘

┏━━━━━━━━━━┳━━━━━━━━━━━┳━━━━━━━━━━┳━━━━━━━━━━┓
┃  Fecha   ┃ Paciente  ┃  Método  ┃  Monto   ┃
┣━━━━━━━━━━╋━━━━━━━━━━━╋━━━━━━━━━━╋━━━━━━━━━━┫
┃15/12/2025┃Juan Pérez ┃ TARJETA  ┃  500.00  ┃
┃14/12/2025┃Ana López  ┃ EFECTIVO ┃  300.00  ┃
┗━━━━━━━━━━┻━━━━━━━━━━━┻━━━━━━━━━━┻━━━━━━━━━━┛

Total: Bs. 15,450.00
```

**Características visuales:**
- ✅ Fondo verde claro para sección financiera
- ✅ Total destacado en grande y negrita
- ✅ Montos alineados a la derecha
- ✅ Fila de total al final de la tabla

---

### **Reporte de Pacientes por Médico**
```
═══════════════════════════════════════════════════════════
         CONSULTORIO MEDICO YESHUA
        Reporte de Pacientes por Médico
         Generado el: 15/12/2025 14:30
═══════════════════════════════════════════════════════════

┌────────────────────────────────────────────────────┐
│ Estadísticas Generales                              │
│                                                      │
│ Total de Médicos: 5                                 │
│ Total de Consultas: 125                             │
│ Promedio por Médico: 25.00 consultas               │
└────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────┐
│ Dr(a). María García                                 │
│ Especialidad: Cardiología                           │
│                                                      │
│ Total de Consultas: 30                              │
│ Pacientes Únicos: 25                                │
│                                                      │
│ ┏━━━━━━━━━━┳━━━━━━━━━━━┳━━━━━━━━━━┓              │
│ ┃  Fecha   ┃ Paciente  ┃ Servicio ┃              │
│ ┣━━━━━━━━━━╋━━━━━━━━━━━╋━━━━━━━━━━┫              │
│ ┃15/12/2025┃Juan Pérez ┃ Consulta ┃              │
│ ┗━━━━━━━━━━┻━━━━━━━━━━━┻━━━━━━━━━━┛              │
└────────────────────────────────────────────────────┘
```

**Características visuales:**
- ✅ Sección por cada médico con fondo azul
- ✅ Estadísticas individuales por médico
- ✅ Tabla de consultas dentro de cada sección
- ✅ Separación visual entre médicos

---

## 3️⃣ CONTENIDO DE LOS REPORTES EXCEL

### **Estructura Excel (todos los tipos):**
```
┌──────────────────────────────────────────────────┐
│ A          │ B       │ C        │ D       │ E   │
├──────────────────────────────────────────────────┤
│ ID         │ Fecha   │ Paciente │ Médico  │ ... │ ← ENCABEZADO (Negrita)
├──────────────────────────────────────────────────┤
│ FIC-001    │15/12/25 │Juan Pérez│García   │ ... │
│ FIC-002    │14/12/25 │Ana López │Sánchez  │ ... │
│ FIC-003    │13/12/25 │...       │...      │ ... │
└──────────────────────────────────────────────────┘
```

**Características:**
- ✅ Primera fila: Encabezados en negrita
- ✅ Formato tabular estándar
- ✅ Compatible con Excel, LibreOffice, Google Sheets
- ✅ Datos editables y filtrables
- ✅ Pestaña nombrada: "Reporte de Citas", etc.

---

## 4️⃣ EMAIL RECIBIDO

**Asunto:** 📊 Reporte Generado - Consultorio Medico Yeshua

```
┌─────────────────────────────────────────────────────┐
│  🏥 CONSULTORIO MEDICO YESHUA                        │
│     Sistema de Gestión Médica                       │
└─────────────────────────────────────────────────────┘

Su reporte ha sido generado exitosamente

Estimado usuario,

Le informamos que su reporte ha sido procesado y está 
disponible para su descarga.

┌─────────────────────────────────────────────────────┐
│ 📊 Detalles del Reporte:                            │
│                                                      │
│ Nombre: Reporte de Citas                           │
│ Tipo: Citas                                         │
│ Formato: PDF                                        │
│ Fecha de generación: 15/12/2025 14:30             │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 📅 Filtros Aplicados:                               │
│                                                      │
│ Desde: 01/12/2025                                   │
│ Hasta: 15/12/2025                                   │
└─────────────────────────────────────────────────────┘

El reporte se encuentra adjunto a este correo electrónico.

También puede descargarlo directamente desde el sistema
accediendo a la sección de reportes generados.

Atentamente,
Equipo de Consultorio Medico Yeshua

────────────────────────────────────────────────────────
Este es un correo automático, por favor no responder.
© 2025 Consultorio Medico Yeshua. Todos los derechos reservados.
```

**Archivo adjunto:** `reporte_citas_2025-12-15_143000.pdf`

---

## 5️⃣ MENSAJES DEL SISTEMA

### **Al generar reporte instantáneo:**
```
┌────────────────────────────────────┐
│  ✅ Reporte generado exitosamente │
└────────────────────────────────────┘

→ Se descarga automáticamente el archivo
→ Aparece en el historial
```

### **Al programar reporte:**
```
┌──────────────────────────────────────────────┐
│  ✅ Reporte programado exitosamente!        │
│                                               │
│  Se generará en segundo plano y estará      │
│  disponible en breve.                        │
│                                               │
│  📧 Recibirá un email cuando esté listo.    │
└──────────────────────────────────────────────┘
```

### **En el Queue Worker (Terminal):**
```
[2025-12-15 14:30:00] Processing: App\Jobs\GenerarReporteAutomaticoJob
[2025-12-15 14:30:03] Processed:  App\Jobs\GenerarReporteAutomaticoJob
```

---

## 6️⃣ ARCHIVOS GENERADOS EN STORAGE

**Ruta:** `storage/app/public/reportes/`

```
reportes/
├── reporte_citas_2025-12-15_143000.pdf
├── reporte_ingresos_2025-12-15_143015.xlsx
├── reporte_pacientes_medico_2025-12-15_143030.pdf
└── ...
```

**Nomenclatura:**
- `reporte_{tipo}_{fecha}_{hora}.{formato}`
- Tipo: citas, ingresos, pacientes_medico
- Formato: pdf, xlsx

---

## 7️⃣ RESULTADOS ESPERADOS

### **✅ GENERACIÓN INSTANTÁNEA:**
1. Clic en botón → 2-5 segundos → Descarga automática
2. Archivo funcional y completo
3. Aparece en historial inmediatamente

### **✅ GENERACIÓN PROGRAMADA:**
1. Clic en botón → Mensaje confirmación → Procesamiento en segundo plano
2. 3-10 segundos → Aparece en historial
3. Email recibido con archivo adjunto

### **✅ REPORTES PERIÓDICOS:**
1. Comando ejecutado → Mensaje de confirmación
2. Jobs encolados → Procesamiento gradual
3. Múltiples reportes generados
4. Emails enviados a administradores

---

## 🎨 PALETA DE COLORES

### **PDFs:**
- **Citas:** Azul (#2C3E50, #34495E)
- **Ingresos:** Verde (#27AE60, #D5F4E6)
- **Pacientes por Médico:** Azul claro (#3498DB, #D6EAF8)

### **Interfaz Web:**
- **PDF:** Rojo (#DC2626)
- **Excel:** Verde (#059669)
- **Programar:** Morado (#7C3AED) / Teal (#0D9488)
- **Actualizar:** Azul (#2563EB)

---

## 📊 DATOS DE EJEMPLO

### **Para probar con datos reales:**
1. Crear algunas citas en el sistema
2. Registrar pagos
3. Asignar médicos a citas
4. Luego generar reportes con esos datos

### **Rango de fechas recomendado:**
- **Desde:** 01/12/2025
- **Hasta:** 15/12/2025 (o fecha actual)

---

**¡El sistema está listo para mostrar reportes profesionales! 📊✨**

