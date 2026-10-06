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


## 📌 Nota de Sesión
Esta sesión concluye tras la modernización total del flujo de acceso y el Dashboard principal. Se ha garantizado la compatibilidad de Tailwind con el motor de Bootstrap original desactivando el Preflight.

## Reglas de Trabajo para AGY
1. **Directorio Raíz**: Toda consulta, análisis o modificación debe tomar como referencia el directorio raíz `/var/www/html/sincrm`.
2. **Preservar Credenciales**: Nunca exponer ni modificar contraseñas o claves en `database/dbconfig.php` ni en `~/.ssh/`.
3. **Compatibilidad y Permisos**: Respetar los permisos de servidor web (`www-data`) y asegurar compatibilidad PHP / MySQL sin romper código legacy ni migraciones v4.
4. **Auto-resumen de sesión por desconexiones**:
   - Al finalizar un trabajo o bloque importante en este directorio, crea/actualiza el archivo `.last_session_summary.txt` en la raíz del proyecto (`/var/www/html/sincrm/.last_session_summary.txt`) con un resumen muy breve de los cambios realizados, archivos modificados y estado del proyecto. Esto permitirá al usuario ver de un vistazo qué se hizo si se le corta la conexión SSH en el móvil al entrar con `agy-sincrm`.


---

## 🎨 Directrices de Diseño y Estética Visual (Sistema Emil Kowalski + Impeccable + Rauno Freiberg)

Para cualquier desarrollo o mejora en la interfaz web de SINCRM v4 (Dashboards, tablas de clientes, expedientes, facturación, modal forms):

### 1. Físicas de Muelles y Microinteracciones Elásticas (Spring Physics)
- **Transiciones fluidas**: Usar curvas de muelles naturales para animaciones y aperturas (`--spring-bounce: cubic-bezier(0.34, 1.56, 0.64, 1)` y `--spring-smooth: cubic-bezier(0.16, 1, 0.3, 1)`). Evitar transiciones lineales o `ease-in-out` planas.
- **Feedback táctil elástico**: Todo botón de acción, fila interactiva, pill de estado o selector debe responder al clic o toque con un micro-escalado reactivo (`.tap-press:active { transform: scale(0.97); }` o `active:scale-[0.98]`).
- **Elevación reactiva elástica**: En tarjetas de resumen, gráficos de facturación y paneles KPI (`.card-hover:hover { transform: translateY(-2px); }`).

### 2. Tipografía y Estabilidad Numérica Obligatoria (`tabular-nums`)
- **Estabilidad numérica obligatoria**: En cualquier importe monetario (`€`), cifras de ventas, porcentajes de IVA/IRPF, CIF/NIF, números de teléfono, fechas de vencimiento y contadores de clientes, es **obligatorio** usar números tabulares (`font-variant-numeric: tabular-nums;` o clase `tabular-nums`).
- Evita temblores numéricos, desalineaciones de columnas en listados contables o saltos visuales al ordenar tablas.

### 3. Sombras Multicapa Refinadas (Layered Ambient Shadows) y Bordes Hairline
- **Profundidad limpia**: No usar sombras negras duras o difuminados opacos que ensucien la interfaz empresarial.
- Emplear sombras multicapa compuestas sutiles combinadas con tinte ambiental y bordes *hairline* ultra-finos (`1px solid rgba(0, 0, 0, 0.06)` en modo claro o `1px solid rgba(255, 255, 255, 0.08)` en modo oscuro) para delimitar tarjetas, modales y tablas con máxima nitidez.

### 4. Componentes Móviles y Ergonomía Táctil
- **Modales en Bottom-Sheet**: En resoluciones móviles, los formularios de alta rápida, filtros y selectores deben desplegarse preferentemente desde la parte inferior (*Bottom-Sheet*) con tirador táctil (*drag handle*), esquinas superiores redondeadas y deslizamiento suave.
- **Micro-respiración orgánica (*Breathing Pulse*)**: Alertas críticas de cobro, tareas pendientes o estados de sync deben usar pulsaciones suaves elásticas (`pulse-breathing`) en vez de parpadeos agresivos.
- **Jerarquía visual limpia**: Espaciado generoso (múltiplos de 4/8px), separación clara entre métricas primarias destacadas y etiquetas secundarias atenuadas.

### 5. Controles Táctiles y Contraste (Zero-Gray Controls)
- **Cero inputs o selectores en gris plano**: Prohibido usar selectores `<select>` o cajas de texto en gris opaco plano.
- **Sustitución por controles segmentados**: Usar conmutadores táctiles, chips de filtro y desplegables con contraste nítido, fondos claros suaves (`#f8fafc`), bordes definidos (`#cbd5e1`), texto oscuro de alto contraste (`#0f172a`), y halo de foco de acento (`#2563eb` / `#4f46e5`).
