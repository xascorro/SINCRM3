# Guía de Proyecto: SINCRM-BETA

## 🔑 Acceso al Servidor Beta
- **Host:** `beta.pedrodiaz.eu` | **Usuario:** `ubuntu` | **Puerto:** 22
- **Ruta Web:** `/var/www/html/beta`
- **Clave SSH:** `~/.ssh/beta-sincrm.key`

## 🗄️ Acceso a Base de Datos (MariaDB)
El sistema utiliza `database/dbconfig.php` para gestionar la conexión automáticamente según el entorno:

### 🌐 Entorno Beta / Producción (`beta.pedrodiaz.eu`)
- **Host:** `localhost`
- **Base de Datos:** `sincrm4`
- **Usuario:** `xas`
- **Contraseña:** `79eagle`

### 💻 Entorno Local / Desarrollo
- **Host:** `localhost`
- **Base de Datos:** `sincrm4`
- **Usuario:** `root`
- **Contraseña:** `xas`

### 🛠️ Comandos Útiles
Para conectar desde este entorno vía SSH al servidor Beta:
```bash
ssh -i ~/.ssh/beta-sincrm.key ubuntu@beta.pedrodiaz.eu "mysql -u xas -p79eagle sincrm4 -e 'CONSULTA'"
```

## 🚀 Estado del Proyecto (Refactorización v4 (Prerelease))
Hemos unificado la lógica de estados (`activo` 1/0 en lugar de `baja` si/no) y profesionalizado la interfaz (SB Admin 2 + DataTables + Logs).

### ✅ Secciones Completadas:
- **Mantenimiento**: Nueva interfaz, gestión de `.user.ini`, diagnóstico de servidor y visor de logs profesional.
- **Sistema de Logs**: Motor centralizado `write_log()` en `database/dbconfig.php`. Niveles: `SUCCESS`, `ERROR`, `WARNING`, `INFO`, `SECURITY`.
- **Visores de Log**: `log.php` (Sistema) y `log_usuario.php` (Personalizado por email/club).
- **Usuarios**: Migración a `activo` (tinyint), seguridad con `password_hash`, interfaz renovada y logs de sesión.
- **Nadadoras**: Migración técnica a `activo` (tinyint), interfaz de tabla optimizada y switch de estado en edición. Se ha aclarado que la columna `baja` en tablas operativas (`inscripciones`, `resultados`) es independiente y representa retiradas puntuales de eventos.
- **Jueces**: Migración técnica a `activo` (tinyint), limpieza de tabla y auditoría completa.
- **Clubes y Federaciones**: Sistema de subida de logos/escudos arreglado (con limpieza de archivos), interfaz unificada y logs.
- **Login y Registro (v4 (Prerelease))**: Renovación total con diseño moderno (Tailwind), AJAX e integración de SweetAlert2.
- **Dashboard (v4 (Prerelease))**: Reestructuración completa de `index.php` con grid moderno, tarjetas de resumen y gestión visual de competiciones (Próximas e Historial).
- **Sistema de Alertas Inteligente (v4 (Prerelease))**: Gestión de "Tareas Pendientes" con capacidad de **Snooze** (pausa temporal por horas/días) y **Descarte Permanente**. Los descartes no se recuperan al restablecer el panel, permitiendo una limpieza real del ruido (ej: Pases de Nivel sin BIAS).
- **Buscador Dinámico Global (v4 (Prerelease))**: Implementación de filtrado en tiempo real en las tablas y grids de: Usuarios, Nadadoras, Jueces, Clubes, Competiciones, Federaciones y Figuras.
- **Competiciones (v4 (Prerelease))**: Rediseño total de `competiciones.php` con estética vibrante ("colorinchi").
    - **KPI Técnico**: Barra tricolor con distribución real de Figuras, Rutinas y Pases de Nivel.
    - **Fichas Premium**: Inclusión de IDs, fechas en español con año y colores corporativos dinámicos.
    - **Edición 360º**: Formulario avanzado con todos los parámetros técnicos (plazos, mapas, enlaces de meet, tipos excluyentes) y galería de informes optimizada.
- **Galería Inteligente**: Nuevo selector de cabeceras/pies de página con vista `object-contain`, nombres de archivo y motor de subida directa a la carpeta `images/`.
- **Mi Equipo (v4 (Prerelease))**: Nueva sección centralizada para clubes. KPIs dinámicos (Staff/Atletas/Edad Media), directorio de usuarios vinculados y censo alfabético de nadadoras activas.
- **BIAS Analizer (v4 (Prerelease))**: Módulo de auditoría técnica avanzado bajo normativa **World Aquatics (AQUA)**.
    - **Ranking Calidad**: Hall of Fame de la temporada con podio visual y comparativa de líderes mediante **Radar Charts** y **Bubble Charts**.
    - **Perfil de Trayectoria**: Seguimiento histórico individual con evolución de posición técnica y acceso directo a auditorías pasadas.
    - **Auditoría de Evento**: Análisis micro con dispersión real de calidad y cronología de desviaciones.
    - **Motor de Bias & Severity**: Detección automática de sesgos por club (Favoritismo/Severidad) y clasificación de perfil (Severo/Generoso/Equilibrado).
    - **Precisión AQUA**: Implementación de la ficha técnica de precisión basada en el margen oficial de ±0.2 puntos.
- **Recuperación de Avisos**: Opción dinámica en el menú de usuario del TopBar para restablecer silencios temporales sin afectar a los descartes.
- **Motor de Persistencia**: Implementación de tablas `auditoria_jueces_stats` y `auditoria_jueces_puntos` que reduce el tiempo de carga de 23s a 0.003s (Optimización x7000).
- **Portal del Juez**: Acceso directo "Mi Auditoría" para usuarios con rol de Juez, permitiendo el autocontrol técnico.
- **Informes de Figuras (v4 (Prerelease))**: Modernización y unificación en `informe_figuras.php`. Ahora un solo archivo inteligente gestiona tanto el listado de **Inscripciones** (alfabético) como el de **Orden de Actuación** (con marcado de cortes técnicos), con una estética profesional rosada, cabeceras compactas y repetición automática de tablas en saltos de página.
- **Resultados por Categorías (v4 (Prerelease))**: Rediseño completo de `informe_figuras_resultados_categorias.php`. Se han optimizado los anchos de columna, mejorado el contraste de notas tachadas, implementado `nobr` para evitar cortes de filas y un sistema de consultas seguras (`safe_mysqli_result`) para evitar errores fatales.
- **Layout Global**: Modernización de `header.php`, `topbar.php` y `footer.php` (vínculos centrados y firma personalizada).
- **Navbar**: Modernizado con integración del nuevo módulo **BIAS Analizer** y organización de grupos de acceso.
- **Versionado Dinámico Global**: Integración total de `version.json` en Login, Footer y página 404. Eliminación de strings hardcoded.
- **Limpieza de Código (v4)**: Purga de librerías obsoletas (TCPDF original), carpetas SCSS legacy y archivos de prueba antiguos.
- **Categorías (v4 (Prerelease))**: Rediseño visual total. Implementación de campos `nombre_corto` y `orden`. Buscador dinámico integrado y KPIs técnicos.
- **Modo PWA (v4 (Prerelease))**: Aplicación 100% instalable en móvil.
    - **Service Worker**: Gestión de caché inteligente para carga ultra-rápida.
    - **Manifiesto**: Configuración de colores corporativos y Splash Screen.
    - **UI Mobile**: Nueva barra de navegación inferior (Bottom Nav) con botones dinámicos por rol.
    - **Resolución de Conflictos**: Solucionado el solapamiento con el alias `/icons/` de Apache mediante la nueva ruta `pwa-icons/`.

### ⏳ Pendiente:
- **TRE (Técnico)**: Ajuste del sistema de cálculo de puntuación implementado. **PENDIENTE DE REVISIÓN MANUAL POR EL USUARIO** para validar la normalización de notas basada en el sumatorio de DDs en Junior/Senior.


## 🗺️ Roadmap & TODO List (Propuestas de Mejora)

### 1. Comunicación y Automatización
- [ ] **Notificaciones Push/Bot**: Implementar avisos automáticos a jueces y clubes (Telegram/Email) sobre estados de competición.
- [ ] **Confirmación de Carga**: Envío de emails automáticos tras subida exitosa de música o cierre de inscripciones.

### 2. Visibilidad y Espectadores
- [ ] **Live Scoreboard**: Página pública ligera para seguimiento de notas en tiempo real por el público.
- [ ] **Overlays para Streaming**: Generador de capas visuales para integración en OBS (fondo croma).

### 3. Profundización Técnica (AQUA)
- [ ] **OCR Coach Cards**: Importador automático de elementos y DD desde PDFs oficiales de World Aquatics.
- [ ] **Panel Táctil de Sincro**: Interfaz específica para controladores de sincronización en tablet.

### 4. Oficialidad y Gestión
- [ ] **Firma Digital**: Implementar firma electrónica en actas finales para validez oficial inmediata.
- [ ] **Auto-Diplomas**: Generador masivo de diplomas de participación y podio por competición.

### 5. Análisis de Datos
- [ ] **Panel de Evolución**: Gráficos históricos de rendimiento por deportista a lo largo de las temporadas.
- [ ] **Benchmarking de Clubes**: Análisis comparativo de fortalezas técnicas entre clubes.

### 6. Continuidad Estética (Migración v4 Tailwind)
- [ ] **Puntuaciones Figuras**: Rediseñar `puntuaciones_lista_figuras.php` y sus subpáginas al estilo v4.
- [ ] **Wizard de Competición**: Crear un flujo guiado para la configuración inicial de eventos y fases.
- [ ] **Dashboard de Club**: Refinar la vista de "Mi Equipo" con más analíticas de plantilla.

---

## 📌 Nota de Sesión
Esta sesión concluye tras la modernización total del flujo de acceso y el Dashboard principal. Se ha garantizado la compatibilidad de Tailwind con el motor de Bootstrap original desactivando el Preflight.
