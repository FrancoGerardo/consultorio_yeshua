# 📊 INSTRUCCIONES DE PRUEBA - SISTEMA DE REPORTES

## ✅ IMPLEMENTACIÓN COMPLETADA

Se han implementado **exitosamente** las Fases 1 y 2 del sistema de reportes:

### **FASE 1: Generación de Reportes** ✅
- ✅ Reporte de Citas por Fecha
- ✅ Reporte de Ingresos
- ✅ Reporte de Pacientes por Médico
- ✅ Exportación PDF (con DomPDF)
- ✅ Exportación Excel (con Laravel Excel)

### **FASE 2: Automatización y Email** ✅
- ✅ Sistema de Jobs en segundo plano
- ✅ Envío automático por email
- ✅ Comando para reportes periódicos
- ✅ Programación de reportes automáticos

---

## 🚀 PASOS PARA PROBAR

### **1. CONFIGURAR EMAIL (Opcional para Fase 2)**

Si desea probar el envío por email, configure en su `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=su_email@gmail.com
MAIL_PASSWORD=su_contraseña_de_aplicación
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=su_email@gmail.com
MAIL_FROM_NAME="Consultorio Medico Yeshua"
```

**Nota:** Para Gmail, debe generar una "Contraseña de Aplicación" en la configuración de seguridad.

---

### **2. INICIAR EL SISTEMA DE COLAS (Para Fase 2)**

Abra una **nueva terminal** y ejecute:

```bash
cd "C:\Users\Franco-PC\Documents\UAGRM 2 - 2025\Tecno - Web\CLINICA\clinica_santiago_apostol_v1"
php artisan queue:work
```

**⚠️ IMPORTANTE:** Mantenga esta terminal abierta mientras prueba los reportes programados.

---

### **3. CREAR STORAGE LINK (Si no existe)**

```bash
cd "C:\Users\Franco-PC\Documents\UAGRM 2 - 2025\Tecno - Web\CLINICA\clinica_santiago_apostol_v1"
php artisan storage:link
```

---

### **4. INICIAR EL SERVIDOR**

```bash
cd "C:\Users\Franco-PC\Documents\UAGRM 2 - 2025\Tecno - Web\CLINICA\clinica_santiago_apostol_v1"
php artisan serve
```

---

### **5. ACCEDER AL SISTEMA DE REPORTES**

1. Inicie sesión como **Administrador**
2. Vaya a: **http://localhost:8000/reportes/generar**

---

## 🧪 CASOS DE PRUEBA

### **PRUEBA 1: Generación Instantánea de PDF**

1. Seleccione **"Reporte de Citas"**
2. Configure las fechas (ejemplo: último mes)
3. Seleccione un médico (opcional)
4. Haga clic en **"📄 Generar PDF"**
5. **Resultado esperado:**
   - ✅ Se descarga automáticamente el PDF
   - ✅ Aparece en "Reportes Generados Recientemente"
   - ✅ El PDF contiene estadísticas y tabla de citas

---

### **PRUEBA 2: Generación Instantánea de Excel**

1. Seleccione **"Reporte de Ingresos"**
2. Configure las fechas
3. Seleccione método de pago (opcional)
4. Haga clic en **"📊 Generar Excel"**
5. **Resultado esperado:**
   - ✅ Se descarga automáticamente el archivo Excel
   - ✅ El Excel contiene los pagos en formato tabular
   - ✅ Se puede abrir con Microsoft Excel o LibreOffice

---

### **PRUEBA 3: Programar Reporte con Email**

**⚠️ Requiere:** Queue Worker activo y email configurado

1. Seleccione **"Reporte de Pacientes por Médico"**
2. Configure las fechas
3. **Marque la casilla:** ✅ Enviar reporte por correo electrónico
4. Haga clic en **"⏰ Programar PDF"**
5. **Resultado esperado:**
   - ✅ Mensaje: "Reporte programado exitosamente"
   - ✅ En la terminal del queue worker aparece el procesamiento
   - ✅ Después de unos segundos, aparece en "Reportes Generados"
   - ✅ Se recibe un email con el PDF adjunto

---

### **PRUEBA 4: Comando de Reportes Periódicos**

Ejecute en una terminal:

```bash
cd "C:\Users\Franco-PC\Documents\UAGRM 2 - 2025\Tecno - Web\CLINICA\clinica_santiago_apostol_v1"
php artisan reportes:periodicos diario --email
```

**Tipos disponibles:**
- `diario` - Reportes del día actual
- `semanal` - Reportes de la semana actual
- `mensual` - Reportes del mes actual

**Resultado esperado:**
- ✅ Se programan múltiples reportes (citas, ingresos, pacientes) en PDF y Excel
- ✅ Se procesan en la cola
- ✅ Se envían por email a todos los administradores

---

### **PRUEBA 5: Descargar y Eliminar Reportes**

1. En la sección **"Reportes Generados Recientemente"**
2. Haga clic en **"Descargar"** en cualquier reporte
3. **Resultado esperado:**
   - ✅ Se descarga el archivo correctamente
4. Haga clic en **"Eliminar"**
5. **Resultado esperado:**
   - ✅ Se elimina el reporte de la lista
   - ✅ El archivo físico también se elimina

---

## 📁 ARCHIVOS CREADOS/MODIFICADOS

### **Backend (PHP/Laravel):**
```
✅ database/migrations/2025_12_15_000001_create_reportes_generados_table.php
✅ app/Models/ReporteGenerado.php
✅ app/Services/ReporteService.php
✅ app/Services/ReportePDFService.php
✅ app/Services/ReporteExcelService.php
✅ app/Exports/CitasExport.php
✅ app/Exports/IngresosExport.php
✅ app/Exports/PacientesMedicoExport.php
✅ app/Http/Controllers/ReporteController.php (actualizado)
✅ app/Jobs/GenerarReporteAutomaticoJob.php
✅ app/Mail/ReporteGeneradoMail.php
✅ app/Console/Commands/GenerarReportesPeriodicos.php
✅ routes/web.php (actualizado)
```

### **Vistas (Blade):**
```
✅ resources/views/reportes/citas-pdf.blade.php
✅ resources/views/reportes/ingresos-pdf.blade.php
✅ resources/views/reportes/pacientes-medico-pdf.blade.php
✅ resources/views/emails/reporte-generado.blade.php
```

### **Frontend (Vue):**
```
✅ resources/js/Pages/Reportes/Generar.vue
```

### **Paquetes Instalados:**
```
✅ barryvdh/laravel-dompdf (PDF)
✅ maatwebsite/excel (Excel)
```

---

## 🔍 VERIFICACIÓN DE FUNCIONAMIENTO

### **Verificar que la tabla fue creada:**
```sql
SELECT * FROM reportes_generados;
```

### **Verificar Jobs procesados:**
```sql
SELECT * FROM trabajos_fallidos;
-- Debe estar vacía si todo funcionó correctamente
```

### **Ver logs en tiempo real:**
```bash
tail -f storage/logs/laravel.log
```

---

## ⚠️ SOLUCIÓN DE PROBLEMAS

### **Problema: "Class not found"**
**Solución:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### **Problema: PDFs sin estilos**
**Solución:** Los PDFs pueden tardar un poco más en generarse la primera vez. Es normal.

### **Problema: Email no se envía**
**Verificar:**
1. ✅ Queue Worker está corriendo
2. ✅ Configuración de email en `.env` es correcta
3. ✅ El usuario tiene un email válido en la base de datos

### **Problema: "Storage not found"**
**Solución:**
```bash
php artisan storage:link
```

### **Problema: Reportes no aparecen**
**Solución:**
```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## 📝 COMANDOS ÚTILES

### **Generar reporte diario automático:**
```bash
php artisan reportes:periodicos diario
```

### **Generar reporte semanal con email:**
```bash
php artisan reportes:periodicos semanal --email
```

### **Generar reporte mensual con email:**
```bash
php artisan reportes:periodicos mensual --email
```

### **Ver cola de trabajos:**
```bash
php artisan queue:work --verbose
```

### **Limpiar trabajos fallidos:**
```bash
php artisan queue:flush
```

---

## ✨ CARACTERÍSTICAS IMPLEMENTADAS

### **FASE 1:**
- ✅ Interfaz visual moderna y profesional
- ✅ Filtros dinámicos por fecha, médico, estado, método de pago
- ✅ Generación instantánea de PDF con DomPDF
- ✅ Generación instantánea de Excel con Laravel Excel
- ✅ Diseño responsive para todos los dispositivos
- ✅ Estadísticas y gráficos en los reportes PDF
- ✅ Historial de reportes generados
- ✅ Descarga directa desde el navegador

### **FASE 2:**
- ✅ Sistema de Jobs para procesamiento en segundo plano
- ✅ Envío automático de reportes por email con archivo adjunto
- ✅ Comando de consola para reportes periódicos (diario/semanal/mensual)
- ✅ Programación de reportes para generación posterior
- ✅ Cola de trabajos con Laravel Queues
- ✅ Email HTML profesional con template personalizado
- ✅ Logs detallados de generación
- ✅ Manejo de errores y reintentos automáticos

---

## 🎯 PRÓXIMOS PASOS SUGERIDOS

1. **Programar reportes automáticos en el servidor:**
   - Configurar CRON para ejecutar `reportes:periodicos` diariamente
   - Ejemplo CRON: `0 8 * * * cd /ruta/proyecto && php artisan reportes:periodicos diario --email`

2. **Agregar gráficos con Chart.js:**
   - Implementar gráficos interactivos en la interfaz web
   - Mostrar tendencias de ingresos, citas, etc.

3. **Dashboard de estadísticas:**
   - Vista resumen con KPIs principales
   - Gráficos en tiempo real

---

## 📞 SOPORTE

Si encuentra algún problema:
1. Revise los logs: `storage/logs/laravel.log`
2. Verifique la cola de trabajos fallidos
3. Asegúrese de que todas las dependencias estén instaladas

---

**¡El sistema de reportes está completamente funcional y listo para usar! 🎉**

