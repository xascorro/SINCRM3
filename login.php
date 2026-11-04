<?php
@session_start();
// Si el usuario ya está logueado, redirigir al dashboard
if(isset($_SESSION['id_usario'])) {
    header("Location: index.php");
    exit();
}
// Solo destruimos la sesión si venimos de un logout explícito, 
// de lo contrario perdemos los mensajes de confirmación (Email Verificado, etc.)
if(isset($_GET['logout_btn'])) {
    session_destroy();
    session_start();
}
require_once 'lib/my_functions.php';
$version = getVersion();
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0" name="viewport"/>
    <title>SINCRM | Acceso al Sistema</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Icons suite -->
    <link rel="icon" type="image/png" sizes="32x32" href="pwa-icons/32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="pwa-icons/16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="pwa-icons/180x180.png">

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('service-worker.js')
                    .then(reg => console.log('Service Worker registrado', reg.scope))
                    .catch(err => console.error('Error al registrar Service Worker', err));
            });
        }
    </script>

    <!-- Tipografía Lexend y FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/83d95dbe8d.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "brand-blue": "#2563eb",
                        "brand-cyan": "#0284c7"
                    },
                    fontFamily: { 
                        lexend: ["Lexend", "sans-serif"] 
                    }
                },
            },
        }
    </script>
    <style>
        :root {
            --spring-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
            --spring-smooth: cubic-bezier(0.16, 1, 0.3, 1);
        }
        body { 
            font-family: 'Lexend', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
        .tap-press {
            transition: transform 0.2s var(--spring-bounce), box-shadow 0.2s var(--spring-smooth);
        }
        .tap-press:active {
            transform: scale(0.98);
        }
        .input-glow {
            transition: border-color 0.2s var(--spring-smooth), box-shadow 0.2s var(--spring-smooth), background-color 0.2s;
        }
        .input-glow:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12), 0 2px 4px rgba(0, 0, 0, 0.04) !important;
            background-color: #ffffff !important;
        }
        @keyframes pulseBreathing {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.8; }
        }
        .pulse-breathing {
            animation: pulseBreathing 2.4s ease-in-out infinite;
        }
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(6px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        .animate-fade-in { 
            animation: fadeIn 0.5s var(--spring-smooth) both; 
        }
        
        /* SweetAlert2 Custom Styling */
        .swal2-popup-v3 {
            border-radius: 2rem !important;
            padding: 2.25rem !important;
            font-family: 'Lexend', sans-serif !important;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.15) !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
        }
        .swal2-title-v3 {
            font-size: 0.8125rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.18em !important;
            font-weight: 800 !important;
            color: #64748b !important;
            margin-bottom: 0.5rem !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-full antialiased selection:bg-blue-600 selection:text-white">

<main class="min-h-screen w-full flex flex-col lg:flex-row relative bg-slate-50">
    
    <!-- ========================================================================= -->
    <!-- PANEL IZQUIERDO: HERO VISUAL LUMINOSO (PC Y TABLETS HORIZONTAL)           -->
    <!-- ========================================================================= -->
    <section class="hidden lg:flex lg:w-1/2 xl:w-[52%] relative overflow-hidden flex-col justify-between p-10 xl:p-14 bg-gradient-to-br from-blue-700 via-sky-600 to-cyan-600 text-white shadow-2xl">
        
        <!-- Textura de Natación Artística y Luces Ambientales -->
        <div class="absolute inset-0 z-0">
            <img alt="Natación Sincronizada" class="w-full h-full object-cover opacity-20 mix-blend-overlay scale-105" src="img/bg_sincro_login.jpg"/>
            <div class="absolute inset-0 bg-gradient-to-t from-blue-900/40 via-transparent to-blue-900/10"></div>
            <!-- Destellos de luz suave -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-cyan-300/25 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-400/25 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Cabecera Superior Izquierda -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white p-2 shadow-lg shadow-blue-900/20 flex items-center justify-center border border-white/40 tap-press">
                    <img src="img/logo_sincrm4.png" class="w-full h-full object-contain mix-blend-multiply" alt="Logo SINCRM 4">
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-1.5 leading-none">
                        SINCRM <span class="text-cyan-200 font-extrabold">4</span>
                    </h2>
                    <span class="text-[10px] font-bold text-sky-100/90 tracking-wider">Natación Artística</span>
                </div>
            </div>
            <div class="px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/25 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-300 pulse-breathing"></span>
                <span class="text-[10px] font-bold text-white uppercase tracking-widest tabular-nums">Sistema Activo</span>
            </div>
        </div>

        <!-- Tarjeta Central de Marca (Glassmorphism Claro) -->
        <div class="relative z-10 my-auto py-8">
            <div class="rounded-3xl p-8 xl:p-11 bg-white/15 backdrop-blur-xl border border-white/25 shadow-2xl relative overflow-hidden animate-fade-in text-white">
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 border border-white/30 text-white text-[10px] font-bold uppercase tracking-wider mb-5">
                    <i class="fas fa-water text-xs text-cyan-200"></i> Natación Artística FNRM & AQUA
                </div>

                <h1 class="text-4xl xl:text-5xl font-black text-white tracking-tight leading-[1.15] mb-4">
                    Donde el arte se une a la <span class="text-cyan-200">precisión técnica</span>.
                </h1>

                <p class="text-sm xl:text-base text-sky-100 font-light leading-relaxed mb-8 max-w-lg">
                    Gestión integral de inscripciones, composición de Coach Cards, auditoría BIAS y control en tiempo real de competiciones.
                </p>

                <!-- Características Destacadas -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-6 border-t border-white/20">
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <i class="fas fa-shield-halved text-cyan-200 text-sm"></i>
                        <span class="text-xs font-bold text-white">Normativa AQUA</span>
                        <span class="text-[10px] text-sky-100/80 font-normal leading-tight">Dificultad, TRE y DDs oficiales.</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <i class="fas fa-chart-line text-cyan-200 text-sm"></i>
                        <span class="text-xs font-bold text-white">BIAS Analizer</span>
                        <span class="text-[10px] text-sky-100/80 font-normal leading-tight">Auditoría estadística de jueces.</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <i class="fas fa-mobile-screen-button text-cyan-200 text-sm"></i>
                        <span class="text-xs font-bold text-white">PWA Móvil</span>
                        <span class="text-[10px] text-sky-100/80 font-normal leading-tight">Ágil y ligera a pie de piscina.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie de Información Izquierdo -->
        <div class="relative z-10 flex items-center justify-between text-[11px] font-bold text-sky-100/90 pt-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-300 text-xs"></i>
                <span class="tracking-wider">Temporada 2026 / 2027</span>
            </div>
            <div class="tabular-nums uppercase tracking-widest text-[10px] text-white/80 font-bold">
                Versión <?php echo htmlspecialchars($version); ?>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- PANEL DERECHO: FORMULARIO DE ACCESO (MÓVIL, TABLET Y ESCRITORIO)          -->
    <!-- ========================================================================= -->
    <section class="w-full lg:w-1/2 xl:w-[48%] flex flex-col justify-between p-6 sm:p-10 md:p-14 lg:p-14 xl:p-16 bg-white z-20 min-h-screen">
        
        <!-- Header Móvil & Tablet (< lg): Logo limpio sin recuadro oscuro -->
        <div class="lg:hidden w-full max-w-md mx-auto pt-2 pb-6 text-center animate-fade-in">
            <img src="img/logo_sincrm4.png" class="h-20 w-auto object-contain mx-auto mb-2 mix-blend-multiply drop-shadow-sm" alt="Logo SINCRM 4">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center justify-center gap-1.5">
                SINCRM <span class="text-blue-600">4</span>
            </h1>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Gestión de Natación Artística</p>
        </div>

        <!-- Contenedor Central del Formulario -->
        <div class="w-full max-w-md mx-auto my-auto animate-fade-in">
            
            <!-- Título & Subtítulo (Desktop) -->
            <div class="hidden lg:block mb-8 text-left">
                <span class="inline-block px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-widest mb-3 border border-blue-100">
                    Acceso
                </span>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Bienvenido</h2>
                <p class="text-slate-400 font-medium text-sm mt-1">Introduce tus credenciales para acceder al panel.</p>
            </div>

            <!-- Contenedor de Alertas -->
            <div id="alert-container" class="mb-4">
                <?php include('includes/alertas_v4.php'); ?>
            </div>

            <!-- Formulario de Login -->
            <form id="loginForm" action="login_code.php" method="POST" class="space-y-5">
                
                <!-- Campo: Email -->
                <div class="space-y-1.5">
                    <label for="inputEmail" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-1 flex items-center gap-1.5">
                        <i class="fas fa-at text-slate-400 text-xs"></i>
                        <span>Correo Electrónico</span>
                    </label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                            <i class="fas fa-envelope text-sm"></i>
                        </span>
                        <input type="email" 
                               id="inputEmail"
                               name="email" 
                               required 
                               autocomplete="username" 
                               placeholder="tu-correo@sincrm.com" 
                               class="w-full pl-11 pr-4 py-3.5 sm:py-4 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 input-glow">
                    </div>
                </div>

                <!-- Campo: Contraseña con Toggle de Visibilidad -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center px-1">
                        <label for="inputPassword" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-key text-slate-400 text-xs"></i>
                            <span>Contraseña</span>
                        </label>
                        <a href="forgot_password.php" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 hover:underline tracking-tight transition-colors">
                            ¿Olvidaste la clave?
                        </a>
                    </div>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input type="password" 
                               id="inputPassword"
                               name="password" 
                               required 
                               autocomplete="current-password" 
                               placeholder="••••••••••••" 
                               class="w-full pl-11 pr-12 py-3.5 sm:py-4 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 input-glow">
                        <button type="button" 
                                id="togglePasswordBtn" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-all tap-press focus:outline-none" 
                                title="Mostrar / ocultar contraseña"
                                aria-label="Mostrar u ocultar contraseña">
                            <i id="togglePasswordIcon" class="fas fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Recordar Sesión -->
                <div class="flex items-center justify-between pt-1 px-1">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none group">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember" 
                               class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20 transition-all cursor-pointer">
                        <span class="text-xs font-medium text-slate-600 group-hover:text-slate-900 transition-colors">Mantener sesión iniciada</span>
                    </label>
                    <span class="text-[10px] font-bold uppercase text-slate-400 hidden sm:inline tracking-wider">30 días</span>
                </div>

                <!-- Botón de Envío Luminoso -->
                <div class="pt-2">
                    <button id="submitBtn" 
                            name="login_btn" 
                            type="submit" 
                            class="w-full py-4 px-6 bg-gradient-to-r from-blue-600 via-sky-600 to-blue-700 hover:from-blue-700 hover:to-sky-700 text-white font-bold uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/35 tap-press transition-all flex items-center justify-center gap-3 group">
                        <span id="btnText">Entrar al Sistema</span>
                        <i id="btnIcon" class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                        <div id="loader" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-white/20 border-t-white"></div>
                    </button>
                </div>
            </form>

            <!-- Registro de Nuevos Usuarios -->
            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-xs font-medium text-slate-500">
                    ¿Aún no tienes cuenta? 
                    <a href="register.php" class="text-blue-600 font-bold hover:text-blue-700 transition-colors ml-1 hover:underline">
                        Crear una cuenta
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer Inferior -->
        <div class="w-full max-w-md mx-auto pt-6 text-center text-slate-400">
            <div class="flex items-center justify-center gap-5 text-[11px] font-semibold text-slate-500 mb-3">
                <a href="help/index.html" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                    <i class="fas fa-circle-question text-[10px]"></i> Ayuda
                </a>
                <span class="text-slate-200">&bull;</span>
                <a href="soporte.php" class="hover:text-blue-600 transition-colors">Soporte</a>
                <span class="text-slate-200">&bull;</span>
                <a href="privacidad.php" class="hover:text-blue-600 transition-colors">Privacidad</a>
            </div>
            <p class="text-[10px] font-medium text-slate-400 tracking-tight flex items-center justify-center gap-1">
                <span>SINCRM 4</span>
                <span>&bull;</span>
                <span>Desarrollado con</span>
                <i class="fas fa-heart text-red-500 text-[9px] animate-pulse"></i>
                <span>por Pedro Díaz</span>
            </p>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery/jquery.min.js"></script>
<script>
// Toggle de visibilidad de contraseña
function togglePasswordVisibility() {
    const input = document.getElementById('inputPassword');
    const icon = document.getElementById('togglePasswordIcon');
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash', 'text-blue-600');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash', 'text-blue-600');
        icon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Mostrar alertas de sesión mediante SweetAlert2 si existen
    <?php if(isset($_SESSION['correcto'])): ?>
        Swal.fire({
            icon: 'success',
            title: '<span class="swal2-title-v3">¡Éxito!</span>',
            html: '<?php echo addslashes($_SESSION['correcto']); unset($_SESSION['correcto']); ?>',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'CONTINUAR',
            customClass: { popup: 'swal2-popup-v3' }
        });
    <?php endif; ?>

    <?php if(isset($_SESSION['estado'])): ?>
        Swal.fire({
            icon: 'error',
            title: '<span class="swal2-title-v3 text-red-500">Aviso</span>',
            html: '<?php echo addslashes($_SESSION['estado']); unset($_SESSION['estado']); ?>',
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'ENTENDIDO',
            customClass: { popup: 'swal2-popup-v3' }
        });
    <?php endif; ?>

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#submitBtn');
        const btnText = $('#btnText');
        const btnIcon = $('#btnIcon');
        const loader = $('#loader');

        submitBtn.prop('disabled', true).addClass('opacity-90 pointer-events-none');
        btnText.text('Verificando acceso...');
        btnIcon.addClass('hidden');
        loader.removeClass('hidden');

        $.ajax({
            type: 'POST',
            url: 'login_code.php',
            data: form.serialize() + '&login_btn=1',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const user = response.user || {};
                    const avatar = user.avatar;
                    const clubLogo = user.club_logo || response.icon;
                    const initials = user.initials || 'US';
                    const userName = user.name || (response.message ? response.message.replace(/^Bienvenido\s*/i, '') : 'Usuario');
                    const userRole = user.role || '';
                    const clubName = user.club || '';

                    // Cascada de identidad visual: 1. Avatar -> 2. Logo Club -> 3. Iniciales
                    let visualHtml = '';
                    if (avatar) {
                        visualHtml = `
                            <div class="relative inline-block mx-auto mb-4">
                                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full p-1 bg-gradient-to-tr from-blue-600 via-sky-400 to-cyan-300 shadow-xl shadow-blue-500/25">
                                    <img src="${avatar}" class="w-full h-full object-cover rounded-full bg-white" alt="${userName}">
                                </div>
                                ${clubLogo ? `
                                    <div class="absolute -bottom-1 -right-1 w-9 h-9 rounded-full bg-white p-1 shadow-md border border-slate-100 flex items-center justify-center ring-2 ring-white">
                                        <img src="${clubLogo}" class="w-full h-full object-contain" alt="Club">
                                    </div>` : ''}
                            </div>
                        `;
                    } else if (clubLogo) {
                        visualHtml = `
                            <div class="relative inline-block mx-auto mb-4">
                                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-slate-50 p-4 shadow-xl shadow-slate-900/5 ring-4 ring-blue-500/10 flex items-center justify-center border border-slate-100">
                                    <img src="${clubLogo}" class="max-w-full max-h-full object-contain drop-shadow-sm" alt="Club">
                                </div>
                            </div>
                        `;
                    } else {
                        visualHtml = `
                            <div class="relative inline-block mx-auto mb-4">
                                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-tr from-blue-600 via-sky-500 to-cyan-400 text-white font-black text-2xl sm:text-3xl flex items-center justify-center shadow-xl shadow-blue-500/25 ring-4 ring-blue-500/15 tracking-wider select-none">
                                    ${initials}
                                </div>
                            </div>
                        `;
                    }

                    const modalHtml = `
                        <div class="font-lexend text-center pt-1">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-[10px] font-bold uppercase tracking-wider mb-4">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Acceso Autorizado</span>
                            </div>

                            ${visualHtml}

                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">¡Hola de nuevo!</p>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight mb-2">${userName}</h3>

                            <div class="flex items-center justify-center flex-wrap gap-1.5 mb-5">
                                ${userRole ? `<span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100 flex items-center gap-1"><i class="fas fa-id-badge text-[10px]"></i> ${userRole}</span>` : ''}
                                ${clubName ? `<span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold flex items-center gap-1"><i class="fas fa-shield text-[10px] text-slate-400"></i> ${clubName}</span>` : ''}
                            </div>

                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden mb-3">
                                <div id="swalProgress" class="bg-gradient-to-r from-blue-600 to-cyan-500 h-full w-0 transition-all duration-[2000ms] ease-out rounded-full"></div>
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium flex items-center justify-center gap-2">
                                <i class="fas fa-circle-notch fa-spin text-blue-600 text-[10px]"></i>
                                <span>Cargando tu panel de control...</span>
                            </p>
                        </div>
                    `;

                    Swal.fire({
                        html: modalHtml,
                        timer: 2300,
                        showConfirmButton: false,
                        background: '#ffffff',
                        customClass: { popup: 'swal2-popup-v3' },
                        didOpen: () => {
                            setTimeout(() => {
                                const bar = document.getElementById('swalProgress');
                                if (bar) bar.style.width = '100%';
                            }, 50);
                        }
                    }).then(() => { 
                        window.location.href = response.redirect; 
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="swal2-title-v3 text-red-500">Error de Acceso</span>',
                        html: response.message,
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'REINTENTAR',
                        customClass: { popup: 'swal2-popup-v3' }
                    });
                    submitBtn.prop('disabled', false).removeClass('opacity-90 pointer-events-none');
                    btnText.text('Entrar al Sistema');
                    btnIcon.removeClass('hidden');
                    loader.addClass('hidden');
                }
            },
            error: function() {
                Swal.fire({ 
                    icon: 'error', 
                    title: '<span class="swal2-title-v3 text-red-500">Error</span>', 
                    text: 'El servidor no responde. Por favor, revisa tu conexión e inténtalo de nuevo.', 
                    confirmButtonColor: '#2563eb',
                    customClass: { popup: 'swal2-popup-v3' } 
                });
                submitBtn.prop('disabled', false).removeClass('opacity-90 pointer-events-none');
                btnText.text('Entrar al Sistema');
                btnIcon.removeClass('hidden');
                loader.addClass('hidden');
            }
        });
    });
});

function reenviarVerificacion(email) {
    Swal.fire({
        title: '<span class="swal2-title-v3">Enviando...</span>',
        text: 'Estamos procesando tu solicitud de verificación.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); },
        customClass: { popup: 'swal2-popup-v3' }
    });

    $.ajax({
        type: 'POST',
        url: 'login_code.php',
        data: { reenviar_verificacion: 1, email: email },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '<span class="swal2-title-v3">Email Enviado</span>',
                    html: response.message,
                    confirmButtonColor: '#2563eb',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '<span class="swal2-title-v3 text-red-500">Error</span>',
                    html: response.message,
                    confirmButtonColor: '#2563eb',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            }
        },
        error: function() {
            Swal.fire({ 
                icon: 'error', 
                title: '<span class="swal2-title-v3 text-red-500">Error</span>', 
                text: 'El servidor no responde. Inténtalo más tarde.',
                confirmButtonColor: '#2563eb',
                customClass: { popup: 'swal2-popup-v3' } 
            });
        }
    });
}
</script>
</body>
</html>
