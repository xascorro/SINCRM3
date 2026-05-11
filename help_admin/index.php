<?php
include('../security.php');
if($_SESSION['id_rol'] != 1) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SINCRM 4 | Ayuda Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/83d95dbe8d.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Lexend', sans-serif; }
        .image-placeholder {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 3rem;
            margin: 2rem 0;
            color: #94a3b8;
            transition: all 0.3s ease;
        }
        .image-placeholder:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #3b82f6;
        }
        section { scroll-margin-top: 100px; }
        .dm-card {
            background: white;
            padding: 2.5rem;
            border-radius: 2rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 3rem;
        }
        .step-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            background: #f1f5f9;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="../img/logo_sincrm4.png" class="w-8" alt="Logo">
                <span class="font-black text-xl italic tracking-tighter text-white uppercase tracking-tighter">Admin <span class="text-blue-400">Knowledge Base</span></span>
            </div>
            <div class="flex items-center gap-4">
                <a href="../index.php" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition-colors">Dashboard</a>
                <a href="../usuarios.php" class="text-xs font-black uppercase tracking-widest bg-white text-slate-900 px-6 py-2.5 rounded-xl hover:bg-blue-400 hover:text-white transition-all shadow-lg">Gestión Usuarios</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12 flex flex-col lg:flex-row gap-12">
        
        <!-- SIDEBAR INDEX -->
        <aside class="lg:w-72 flex-shrink-0">
            <div class="sticky top-28 space-y-8">
                <div>
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 px-4 italic">Guía Técnica</h3>
                    <nav class="flex flex-col gap-1">
                        <a href="#seguridad" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all">
                            <i class="fas fa-shield-halved w-5"></i> Seguridad
                        </a>
                        <a href="#datos-maestros" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-slate-900 bg-white shadow-sm border border-slate-100 transition-all">
                            <i class="fas fa-database w-5 text-blue-500"></i> Datos Maestros
                        </a>
                        <div class="pl-12 flex flex-col gap-3 mt-4 border-l-2 border-slate-200 ml-6">
                            <a href="#dm-competiciones" class="text-[11px] font-black text-slate-500 hover:text-blue-600 uppercase tracking-wider">Competiciones</a>
                            <a href="#dm-federaciones" class="text-[11px] font-black text-slate-500 hover:text-blue-600 uppercase tracking-wider">Federaciones</a>
                            <a href="#dm-clubes" class="text-[11px] font-black text-slate-500 hover:text-blue-600 uppercase tracking-wider">Clubes</a>
                            <a href="#dm-nadadoras" class="text-[11px] font-black text-slate-500 hover:text-blue-600 uppercase tracking-wider">Nadadoras</a>
                            <a href="#dm-categorias" class="text-[11px] font-black text-slate-500 hover:text-blue-600 uppercase tracking-wider">Categorías</a>
                            <a href="#dm-jueces" class="text-[11px] font-black text-slate-500 hover:text-blue-600 uppercase tracking-wider">Jueces</a>
                            <a href="#dm-figuras" class="text-[11px] font-black text-slate-500 hover:text-blue-600 uppercase tracking-wider">Figuras</a>
                        </div>
                        <a href="#operativa" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all">
                            <i class="fas fa-flag-checkered w-5"></i> Gestión Operativa
                        </a>
                        <a href="#mantenimiento" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all">
                            <i class="fas fa-microchip w-5"></i> Mantenimiento v4
                        </a>
                    </nav>
                </div>

                <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bolt text-8xl"></i>
                    </div>
                    <h4 class="text-xs font-black uppercase tracking-widest text-blue-400 mb-3 italic">Tip de Admin</h4>
                    <p class="text-[11px] font-medium leading-relaxed text-slate-300 relative z-10">
                        Si un cambio no se refleja, recuerda vaciar la caché del navegador o usar una pestaña de incógnito.
                    </p>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 space-y-32 pb-32">
            
            <!-- SECCIÓN: SEGURIDAD -->
            <section id="seguridad">
                <header class="mb-12">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 mb-2 block italic">Módulo 01</span>
                    <h1 class="text-5xl font-black tracking-tight text-slate-900 uppercase italic leading-none tracking-tighter">Seguridad</h1>
                    <p class="mt-4 text-slate-500 text-lg font-medium">Gestión de usuarios y políticas de acceso al sistema.</p>
                </header>
                
                <div class="dm-card border-l-8 border-l-emerald-500">
                    <h2 class="text-2xl font-black mb-6 flex items-center gap-3">
                        <i class="fas fa-user-plus text-emerald-500"></i> Altas de Invitados
                    </h2>
                    <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                        <p>El flujo de alta externa coloca a los nuevos usuarios en el estamento <strong>Invitado (Rol 6)</strong>. Esto es un "limbo" de seguridad donde no pueden ver ningún dato sensible.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <span class="step-pill">Paso A</span>
                                <p class="text-sm font-bold text-slate-800">Verificación de Identidad</p>
                                <p class="text-xs mt-2">Revisa el comentario del registro (Club/Federación) y confirma que el email sea institucional o conocido.</p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <span class="step-pill">Paso B</span>
                                <p class="text-sm font-bold text-slate-800">Asignación de Rol</p>
                                <p class="text-xs mt-2">Al cambiar de Rol 6 a otro (Juez, Club, etc.), el sistema activará los menús correspondientes en su próxima sesión.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN: DATOS MAESTROS -->
            <section id="datos-ma-master">
                <header class="mb-12" id="datos-maestros">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 mb-2 block italic">Módulo 02</span>
                    <h1 class="text-5xl font-black tracking-tight text-slate-900 uppercase italic leading-none tracking-tighter">Datos Maestros</h1>
                    <p class="mt-4 text-slate-500 text-lg font-medium">Diccionario técnico y censo oficial de la plataforma.</p>
                </header>

                <div class="space-y-12">
                    
                    <!-- COMPETICIONES -->
                    <article id="dm-competiciones" class="dm-card">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter uppercase leading-none">Competiciones</h3>
                                <p class="text-sm text-slate-400 font-bold mt-2 italic">Corazón Operativo del Sistema</p>
                            </div>
                            <i class="fas fa-trophy text-4xl text-amber-400 opacity-20"></i>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium space-y-6">
                            <p>En este módulo se definen los parámetros de cada evento. La configuración aquí determinará qué pueden hacer los clubes.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <h4 class="text-blue-600 font-black uppercase text-xs tracking-widest"><i class="fas fa-toggle-on mr-2"></i>Activación Global</h4>
                                    <p class="text-sm text-slate-500">Solo una competición puede tener el estado <strong>ACTIVO: SÍ</strong>. Esta será la competición que se cargue por defecto al entrar en el sistema. Los usuarios pueden cambiarla, pero esta manda en el Dashboard.</p>
                                    
                                    <h4 class="text-blue-600 font-black uppercase text-xs tracking-widest"><i class="fas fa-calendar-alt mr-2"></i>Gestión de Plazos</h4>
                                    <p class="text-sm text-slate-500">Puedes bloquear las inscripciones independientemente para <strong>Figuras</strong> y <strong>Rutinas</strong>. El sistema mostrará avisos en rojo cuando el plazo haya expirado.</p>
                                </div>
                                <div class="space-y-4">
                                    <h4 class="text-blue-600 font-black uppercase text-xs tracking-widest"><i class="fas fa-file-invoice mr-2"></i>Headers & Footers</h4>
                                    <p class="text-sm text-slate-500">Selecciona las imágenes de cabecera y pie para los informes PDF. Las imágenes se almacenan en <code>images/</code> y se gestionan desde la galería integrada.</p>
                                    
                                    <h4 class="text-blue-600 font-black uppercase text-xs tracking-widest"><i class="fas fa-chart-pie mr-2"></i>KPI de Distribución</h4>
                                    <p class="text-sm text-slate-500">La barra tricolor muestra el porcentaje real de inscripciones: <span class="text-pink-500">Figuras</span>, <span class="text-blue-500">Rutinas</span> y <span class="text-emerald-500">Pases de Nivel</span>.</p>
                                </div>
                            </div>

                            <div class="image-placeholder">
                                <i class="fas fa-image text-4xl mb-3 opacity-20"></i>
                                <p class="font-bold uppercase text-[10px] tracking-widest text-slate-400">Captura: Formulario de Competición v4</p>
                                <p class="text-xs italic mt-2 text-center">(Muestra la edición de colores, plazos y selector de galerías)</p>
                            </div>
                        </div>
                    </article>

                    <!-- FEDERACIONES -->
                    <article id="dm-federaciones" class="dm-card">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter uppercase leading-none">Federaciones</h3>
                                <p class="text-sm text-slate-400 font-bold mt-2 italic">Organismos Rectores</p>
                            </div>
                            <i class="fas fa-landmark text-4xl text-slate-200"></i>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            <p>Permite centralizar los datos de las diferentes federaciones (Nacional, Autonómicas). Sus logos se utilizan principalmente en los listados de clasificación oficial y certificados de participación.</p>
                            <p class="text-sm bg-blue-50 p-6 rounded-2xl border-l-4 border-blue-500 mt-6">
                                <strong>Technical Note:</strong> Al subir un logo de federación, el sistema limpia el archivo anterior para evitar saturar el almacenamiento del servidor.
                            </p>
                        </div>
                    </article>

                    <!-- CLUBES -->
                    <article id="dm-clubes" class="dm-card">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter uppercase leading-none">Clubes</h3>
                                <p class="text-sm text-slate-400 font-bold mt-2 italic">Censo de Entidades</p>
                            </div>
                            <i class="fas fa-shield-halved text-4xl text-blue-200"></i>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            <p>Registro de todos los clubes participantes. Este módulo es crítico para el filtrado de datos por rol.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                                <div class="bg-slate-50 p-6 rounded-2xl">
                                    <h4 class="font-black text-xs uppercase tracking-widest mb-2 italic">Vinculación de Usuarios</h4>
                                    <p class="text-xs leading-relaxed">Para que un usuario tipo <strong>Club</strong> solo vea a sus nadadoras, debes asegurarte de que el campo <code>club</code> en su perfil de usuario coincida exactamente con el <strong>ID</strong> del club registrado aquí.</p>
                                </div>
                                <div class="bg-slate-50 p-6 rounded-2xl">
                                    <h4 class="font-black text-xs uppercase tracking-widest mb-2 italic">Identidad Visual</h4>
                                    <p class="text-xs leading-relaxed">Sube el escudo del club en formato transparente (PNG). Este escudo aparecerá automáticamente en las tarjetas de las nadadoras y en el listado de "Mi Equipo".</p>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- NADADORAS -->
                    <article id="dm-nadadoras" class="dm-card">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter uppercase leading-none">Nadadoras</h3>
                                <p class="text-sm text-slate-400 font-bold mt-2 italic">Base de Datos de Atletas</p>
                            </div>
                            <i class="fas fa-person-swimming text-4xl text-cyan-200"></i>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            <p>El censo central de deportistas. La precisión de estos datos es vital para el cumplimiento de normativas de edad.</p>
                            
                            <div class="mt-8 space-y-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 flex-shrink-0 flex items-center justify-center text-white font-black text-xs italic">1</div>
                                    <div>
                                        <p class="font-black text-slate-800 italic uppercase text-xs tracking-widest">Cálculo de Categoría</p>
                                        <p class="text-sm text-slate-500">El sistema NO requiere asignar una categoría manualmente. Al introducir el <strong>Año de Nacimiento</strong>, el sistema lo cruza con los parámetros del módulo <em>Categorías</em> para determinar si es Alevín, Infantil, etc.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 flex-shrink-0 flex items-center justify-center text-white font-black text-xs italic">2</div>
                                    <div>
                                        <p class="font-black text-slate-800 italic uppercase text-xs tracking-widest">Gestión de Bajas</p>
                                        <p class="text-sm text-slate-500">Usa el switch <strong>Activo / Baja</strong>. Las nadadoras en estado de baja no aparecerán en los selectores de inscripción de los clubes, evitando errores humanos.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="image-placeholder">
                                <i class="fas fa-image text-4xl mb-3 opacity-20"></i>
                                <p class="font-bold uppercase text-[10px] tracking-widest text-slate-400">Captura: Tabla de Nadadoras</p>
                                <p class="text-xs italic mt-2 text-center">(Muestra la foto de la atleta, su club y el badge de estado)</p>
                            </div>
                        </div>
                    </article>

                    <!-- CATEGORÍAS -->
                    <article id="dm-categorias" class="dm-card">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter uppercase leading-none">Categorías</h3>
                                <p class="text-sm text-slate-400 font-bold mt-2 italic">Reglas de Clasificación</p>
                            </div>
                            <i class="fas fa-layer-group text-4xl text-purple-200"></i>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            <p>Aquí se definen las "jaulas" de edad. Es fundamental actualizar estos rangos cada inicio de temporada (ej. Septiembre).</p>
                            <div class="bg-white p-6 rounded-2xl border-2 border-slate-100 shadow-inner mt-6">
                                <p class="text-sm italic"><i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i><strong>Importante:</strong> Si un año de nacimiento queda fuera de todos los rangos definidos, la nadadora aparecerá como "Sin Categoría" y no podrá ser inscrita en fases oficiales.</p>
                            </div>
                        </div>
                    </article>

                    <!-- JUECES -->
                    <article id="dm-jueces" class="dm-card">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter uppercase leading-none">Jueces</h3>
                                <p class="text-sm text-slate-400 font-bold mt-2 italic">Personal Arbitral</p>
                            </div>
                            <i class="fas fa-gavel text-4xl text-slate-900 opacity-20"></i>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            <p>Gestión del censo de jueces. Este módulo conecta con el <strong>BIAS Analizer</strong> para medir la precisión de las puntuaciones.</p>
                            <div class="bg-slate-900 text-blue-100 p-8 rounded-[2rem] mt-8 shadow-2xl">
                                <h4 class="text-blue-400 font-black uppercase text-xs tracking-[0.2em] mb-4 italic">El Vínculo Crítico</h4>
                                <p class="text-xs leading-relaxed opacity-80 mb-4">Para que la auditoría de un juez funcione (Hall of Fame, Radar Charts), debes seguir estos pasos:</p>
                                <ul class="text-[11px] space-y-2 opacity-90 list-disc pl-4">
                                    <li>Crea el Juez en este módulo de <strong>Datos Maestros</strong>.</li>
                                    <li>Anota su ID de registro.</li>
                                    <li>Ve a <strong>Seguridad > Usuarios</strong> y busca al usuario correspondiente.</li>
                                    <li>En el campo <code>ID Juez v3</code>, introduce el ID del registro maestro.</li>
                                </ul>
                                <p class="text-[10px] mt-6 font-black uppercase text-blue-500 tracking-widest italic">Sin este vínculo, el sistema no podrá cruzar los datos de puntuación.</p>
                            </div>
                        </div>
                    </article>

                    <!-- FIGURAS -->
                    <article id="dm-figuras" class="dm-card">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-3xl font-black text-slate-900 italic tracking-tighter uppercase leading-none">Figuras</h3>
                                <p class="text-sm text-slate-400 font-bold mt-2 italic">Catálogo Técnico Oficial</p>
                            </div>
                            <i class="fas fa-shapes text-4xl text-pink-200"></i>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            <p>Listado de todas las figuras aprobadas por la normativa vigente. Cada figura tiene un <strong>Coeficiente de Dificultad (DD)</strong> que es multiplicador directo de la nota del juez.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                                <div class="border-l-4 border-pink-500 pl-6">
                                    <h4 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-2 italic">Número y Nombre</h4>
                                    <p class="text-xs">Usa el código oficial (ej. 101, 301f) para facilitar las búsquedas durante el sorteo.</p>
                                </div>
                                <div class="border-l-4 border-pink-500 pl-6">
                                    <h4 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-2 italic">Grupos Técnicos</h4>
                                    <p class="text-xs">Clasifica las figuras por grupos para automatizar la selección durante el sorteo oficial de la competición.</p>
                                </div>
                            </div>
                        </div>
                    </article>

                </div>
            </section>

            <!-- SECCIÓN: GESTIÓN OPERATIVA -->
            <section id="operativa">
                <header class="mb-12">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 mb-2 block italic">Módulo 03</span>
                    <h1 class="text-5xl font-black tracking-tight text-slate-900 uppercase italic leading-none tracking-tighter">Operativa</h1>
                    <p class="mt-4 text-slate-500 text-lg font-medium">Procesos de competición, sorteos y paneles arbitrales.</p>
                </header>
                
                <div class="space-y-12">
                    <div class="dm-card">
                        <h2 class="text-2xl font-black mb-6 flex items-center gap-3">
                            <i class="fas fa-random text-blue-500"></i> Sorteos de Orden de Salida
                        </h2>
                        <p class="text-sm text-slate-600 leading-relaxed font-medium">
                            El sistema genera automáticamente el orden de actuación. En <strong>Figuras</strong>, el sorteo es aleatorio puro. En <strong>Rutinas</strong>, se puede configurar por grupos de nivel o sorteo abierto. 
                            Recuerda que una vez realizado el sorteo, no se pueden añadir más nadadoras sin resetear el orden (o añadirlas manualmente al final).
                        </p>
                    </div>

                    <div class="dm-card">
                        <h2 class="text-2xl font-black mb-6 flex items-center gap-3">
                            <i class="fas fa-users-gear text-blue-500"></i> Configuración de Paneles
                        </h2>
                        <p class="text-sm text-slate-600 leading-relaxed font-medium">
                            Desde el módulo de <strong>Paneles de Jueces</strong>, asignas qué juez puntúa qué elemento o impresión artística. 
                            La v4 permite guardar individualmente por juez, lo que evita pérdidas de datos si falla la conexión de un solo dispositivo.
                        </p>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN: MANTENIMIENTO -->
            <section id="mantenimiento">
                <header class="mb-12">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 mb-2 block italic">Módulo 04</span>
                    <h1 class="text-5xl font-black tracking-tight text-slate-900 uppercase italic leading-none tracking-tighter">Mantenimiento</h1>
                    <p class="mt-4 text-slate-500 text-lg font-medium">Herramientas críticas de diagnóstico y salud del servidor.</p>
                </header>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                        <h3 class="text-xl font-black text-slate-800 uppercase italic mb-4">Sistema de Logs</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Accede a <code>log.php</code> para ver la actividad del sistema en tiempo real. Clasificación por colores: 
                            <span class="text-emerald-500 font-bold">SUCCESS</span>, 
                            <span class="text-red-500 font-bold">ERROR</span>, 
                            <span class="text-amber-500 font-bold">WARNING</span>.
                        </p>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                        <h3 class="text-xl font-black text-slate-800 uppercase italic mb-4">Diagnóstico</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Permite verificar la versión de PHP, límites de memoria y estado de las extensiones críticas (MySQLi, GD). 
                            Fundamental antes de iniciar una competición oficial.
                        </p>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <footer class="bg-slate-900 text-white py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <div class="flex items-center justify-center gap-4 mb-10">
                <img src="../img/logo_sincrm4.png" class="w-12 brightness-0 invert" alt="Logo">
                <div class="text-left">
                    <span class="font-black text-3xl italic tracking-tighter uppercase leading-none block">Sincrm <span class="text-blue-400">4</span></span>
                    <span class="text-[9px] font-black uppercase tracking-[0.4em] text-slate-500">Official Documentation</span>
                </div>
            </div>
            <div class="flex flex-wrap justify-center gap-8 mb-12 opacity-50 hover:opacity-100 transition-all">
                <a href="#" class="text-xs font-black uppercase tracking-widest no-underline text-white">Seguridad</a>
                <a href="#" class="text-xs font-black uppercase tracking-widest no-underline text-white">Privacidad</a>
                <a href="#" class="text-xs font-black uppercase tracking-widest no-underline text-white">Soporte Técnico</a>
            </div>
            <p class="text-slate-600 text-[9px] italic font-medium">
                © 2026 SINCRM 4 | Diseñado para la precisión en Natación Artística.
            </p>
        </div>
    </footer>

</body>
</html>
