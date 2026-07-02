# ⚙️ CONFIGURACIÓN .env REQUERIDA

## 📋 CONFIGURACIONES CRÍTICAS PARA ELIMINACIÓN DE TABLAS

Después de eliminar las tablas, **DEBES** asegurarte de que tu archivo `.env` tenga estas configuraciones:

---

## 🔧 CONFIGURACIONES OBLIGATORIAS

Abre tu archivo `.env` y verifica/agrega estas líneas:

```env
# ============================================
# CACHE - Usar FILE driver (no database)
# ============================================
CACHE_DRIVER=file
CACHE_STORE=file

# ============================================
# SESSION - Usar FILE driver (no database)
# ============================================
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ============================================
# QUEUE - Usar DATABASE driver (necesario)
# ============================================
QUEUE_CONNECTION=database

# ============================================
# BASE DE DATOS - PostgreSQL
# ============================================
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=clinica_santiago_apostol
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña_aqui
```

---

## ✅ VERIFICAR CONFIGURACIONES

Después de actualizar tu `.env`, ejecuta:

```bash
# Limpiar cache de configuración
php artisan config:clear

# Verificar que las configs se cargaron
php artisan tinker

>>> config('cache.default')
# Debe mostrar: "file"

>>> config('session.driver')
# Debe mostrar: "file"

>>> config('queue.default')
# Debe mostrar: "database"

>>> exit
```

---

## 🚨 SI ALGO NO FUNCIONA

### **Error: "Table 'cache' not found"**
```bash
# Solución:
php artisan config:clear
php artisan cache:clear

# Verificar .env tenga:
CACHE_DRIVER=file
```

### **Error: "Table 'sessions' not found"**
```bash
# Solución:
php artisan config:clear

# Verificar .env tenga:
SESSION_DRIVER=file
```

### **Error: "Table 'personal_access_tokens' not found"**
```bash
# Solución: Esta tabla fue eliminada porque no la usas
# Si necesitas API tokens en el futuro, puedes recrearla
```

---

## 📝 NOTAS

1. **CACHE_DRIVER=file:**
   - Cache se guarda en: `storage/framework/cache/data/`
   - Funciona igual que database, solo cambia ubicación
   - Más simple para desarrollo

2. **SESSION_DRIVER=file:**
   - Sesiones se guardan en: `storage/framework/sessions/`
   - Funciona perfecto para un servidor
   - Si en futuro usas múltiples servidores, cambiar a database

3. **QUEUE_CONNECTION=database:**
   - MANTENER en database
   - Necesario para PagoFácil webhook
   - Usa tablas `jobs` y `failed_jobs` (que SÍ mantenemos)

---

## ✅ LISTO PARA PROBAR

Una vez que hayas actualizado tu `.env`, continúa con las pruebas en `INSTRUCCIONES_ELIMINACION_TABLAS.md` 🚀

