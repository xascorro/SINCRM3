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
    <meta name="theme-color" content="#070b14">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
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
                        "primary": "#070b14",
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
        .glass-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .glass-badge {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
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
            50% { transform: scale(1.18); opacity: 0.75; }
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
            border-radius: 2.5rem !important;
            padding: 2.5rem !important;
            font-family: 'Lexend', sans-serif !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            border: 1px solid #e2e8f0 !important;
        }
        .swal2-title-v3 {
            font-size: 0.8125rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.18em !important;
            font-weight: 900 !important;
            color: #64748b !important;
            margin-bottom: 0.5rem !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-800 h-full antialiased selection:bg-blue-600 selection:text-white">

<main class="min-h-screen w-full flex flex-col lg:flex-row relative overflow-hidden bg-slate-950">
    
    <!-- ========================================================================= -->
    <!-- PANEL IZQUIERDO: HERO VISUAL (PC Y TABLETS EN HORIZONTAL)                 -->
    <!-- ========================================================================= -->
    <section class="hidden lg:flex lg:w-1/2 xl:w-[54%] relative overflow-hidden flex-col justify-between p-10 xl:p-14 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        
        <!-- Fondos Ambientales y Textura Acuática -->
        <div class="absolute inset-0 z-0">
            <img alt="Natación Sincronizada" class="w-full h-full object-cover opacity-25 scale-105 filter blur-[0.5px]" src="img/bg_sincro_login.jpg"/>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-transparent to-slate-950"></div>
            <!-- Luces Ambientales de Acento -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Cabecera Superior Izquierda -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white p-2 shadow-xl shadow-blue-500/10 flex items-center justify-center border border-white/20 tap-press">
                    <img src="img/logo_sincrm4.png" class="w-full h-full object-contain" alt="Logo SINCRM 4">
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-1.5 leading-none">
                        SINCRM <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400 font-extrabold">4</span>
                    </h2>
                    <span class="text-[9px] font-black uppercase tracking-[0.25em] text-slate-400">Plataforma Técnica Oficial</span>
                </div>
            </div>
            <div class="glass-badge px-3.5 py-1.5 rounded-full flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-breathing"></span>
                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest tabular-nums">Sistema Operativo</span>
            </div>
        </div>

        <!-- Tarjeta Central de Marca (Glassmorphism) -->
        <div class="relative z-10 my-auto py-8">
            <div class="glass-card rounded-[2.5rem] p-8 xl:p-12 shadow-2xl relative overflow-hidden animate-fade-in">
                <!-- Resplandor Interior -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full blur-2xl -mr-20 -mt-20 pointer-events-none"></div>
                
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/10 border border-blue-400/20 text-blue-300 text-[10px] font-black uppercase tracking-wider mb-5">
                    <i class="fas fa-water text-xs text-cyan-400"></i> Natación Artística FNRM & AQUA
                </div>

                <h1 class="text-4xl xl:text-5xl font-black text-white tracking-tight leading-[1.15] mb-4">
                    Donde el arte se une a la <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-300 to-blue-200">precisión técnica</span>.
                </h1>

                <p class="text-sm xl:text-base text-slate-300 font-light leading-relaxed mb-8 max-w-lg">
                    Gestión integral de inscripciones, composición de Coach Cards, auditoría de sesgos (BIAS) y control en tiempo real de competiciones federadas.
                </p>

                <!-- Características Destacadas -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-6 border-t border-white/10">
                    <div class="p-3.5 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex flex-col gap-1.5">
                        <i class="fas fa-shield-halved text-blue-400 text-sm"></i>
                        <span class="text-xs font-black text-white">Normativa AQUA</span>
                        <span class="text-[10px] text-slate-400 font-medium leading-tight">Dificultad, TRE y sumatorio de DDs oficial.</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex flex-col gap-1.5">
                        <i class="fas fa-chart-line text-cyan-400 text-sm"></i>
                        <span class="text-xs font-black text-white">BIAS Analizer</span>
                        <span class="text-[10px] text-slate-400 font-medium leading-tight">Auditoría estadística y precisión de jueces.</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex flex-col gap-1.5">
                        <i class="fas fa-mobile-screen-button text-purple-400 text-sm"></i>
                        <span class="text-xs font-black text-white">PWA Móvil</span>
                        <span class="text-[10px] text-slate-400 font-medium leading-tight">Instalable, ligera y rápida a pie de piscina.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie de Información Izquierdo -->
        <div class="relative z-10 flex items-center justify-between text-[11px] font-bold text-slate-400 pt-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-400 text-xs"></i>
                <span class="uppercase tracking-wider">Temporada Oficial 2026 / 2027</span>
            </div>
            <div class="tabular-nums uppercase tracking-widest text-[10px] text-slate-500 font-black">
                Versión <?php echo htmlspecialchars($version); ?>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- PANEL DERECHO: FORMULARIO DE ACCESO (MÓVIL, TABLET Y ESCRITORIO)          -->
    <!-- ========================================================================= -->
    <section class="w-full lg:w-1/2 xl:w-[46%] flex flex-col justify-between p-6 sm:p-10 md:p-14 lg:p-14 xl:p-16 bg-white lg:rounded-l-[3.5rem] shadow-2xl z-20 min-h-screen">
        
        <!-- Header Móvil & Tablet (< lg) -->
        <div class="lg:hidden w-full max-w-md mx-auto pt-4 pb-8 text-center animate-fade-in">
            <div class="inline-flex items-center justify-center p-3.5 bg-gradient-to-tr from-slate-900 to-slate-800 rounded-3xl shadow-xl shadow-blue-500/10 mb-3 border border-slate-700/50 tap-press">
                <img src="img/logo_sincrm4.png" class="w-12 h-12 object-contain" alt="Logo SINCRM 4">
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center justify-center gap-1.5">
                SINCRM <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">4</span>
            </h1>
            <p class="text-xs text-slate-400 font-medium">Acceso para Clubes, Jueces y Administradores</p>
        </div>

        <!-- Contenedor Central del Formulario -->
        <div class="w-full max-w-md mx-auto my-auto animate-fade-in">
            
            <!-- Título & Subtítulo (Desktop) -->
            <div class="hidden lg:block mb-8 text-left">
                <span class="inline-block px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest mb-3 border border-blue-100">
                    Acceso Seguro
                </span>
                <h2 class="text-3xl xl:text-4xl font-black text-slate-900 tracking-tight">Bienvenido</h2>
                <p class="text-slate-400 font-medium text-sm mt-1">Introduce tus credenciales para acceder al panel técnico.</p>
            </div>

            <!-- Contenedor de Alertas -->
            <div id="alert-container" class="mb-4">
                <?php include('includes/alertas_v4.php'); ?>
            </div>

            <!-- Formulario de Login -->
            <form id="loginForm" action="login_code.php" method="POST" class="space-y-5">
                
                <!-- Campo: Email -->
                <div class="space-y-1.5">
                    <label for="inputEmail" class="text-[10px] font-black uppercase tracking-wider text-slate-500 px-1 flex items-center gap-1.5">
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
                               class="w-full pl-11 pr-4 py-3.5 sm:py-4 bg-slate-50/70 border border-slate-200 rounded-2xl text-xs sm:text-sm font-bold text-slate-800 placeholder:text-slate-400/80 input-glow">
                    </div>
                </div>

                <!-- Campo: Contraseña con Toggle Ojo -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center px-1">
                        <label for="inputPassword" class="text-[10px] font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-key text-slate-400 text-xs"></i>
                            <span>Contraseña</span>
                        </label>
                        <a href="forgot_password.php" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 hover:underline tracking-tight transition-colors">
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
                               class="w-full pl-11 pr-12 py-3.5 sm:py-4 bg-slate-50/70 border border-slate-200 rounded-2xl text-xs sm:text-sm font-bold text-slate-800 placeholder:text-slate-400/80 input-glow">
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
                        <span class="text-xs font-semibold text-slate-600 group-hover:text-slate-900 transition-colors">Mantener sesión iniciada</span>
                    </label>
                    <span class="text-[10px] font-black uppercase text-slate-400/80 hidden sm:inline tracking-widest">30 DÍAS</span>
                </div>

                <!-- Botón de Envío -->
                <div class="pt-2">
                    <button id="submitBtn" 
                            name="login_btn" 
                            type="submit" 
                            class="w-full py-4 sm:py-4.5 px-6 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-xl shadow-slate-900/15 hover:shadow-blue-500/25 hover:from-blue-600 hover:to-blue-700 tap-press transition-all flex items-center justify-center gap-3 group border border-slate-800">
                        <span id="btnText">Entrar al Sistema</span>
                        <i id="btnIcon" class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                        <div id="loader" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-white/20 border-t-white"></div>
                    </button>
                </div>
            </form>

            <!-- Registro de Nuevos Usuarios -->
            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-xs font-medium text-slate-500">
                    ¿Aún no tienes acceso oficial? 
                    <a href="register.php" class="text-blue-600 font-black hover:text-blue-700 transition-colors ml-1 hover:underline">
                        Crear una cuenta
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer Inferior -->
        <div class="w-full max-w-md mx-auto pt-6 text-center text-slate-400">
            <div class="flex items-center justify-center gap-5 text-[11px] font-bold text-slate-500 mb-3">
                <a href="help/index.html" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                    <i class="fas fa-circle-question text-[10px]"></i> Ayuda
                </a>
                <span class="text-slate-200">&bull;</span>
                <a href="soporte.php" class="hover:text-blue-600 transition-colors">Soporte</a>
                <span class="text-slate-200">&bull;</span>
                <a href="privacidad.php" class="hover:text-blue-600 transition-colors">Privacidad</a>
            </div>
            <p class="text-[10px] font-semibold text-slate-400 tracking-tight flex items-center justify-center gap-1">
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
            confirmButtonColor: '#070b14',
            confirmButtonText: 'CONTINUAR',
            customClass: { popup: 'swal2-popup-v3' }
        });
    <?php endif; ?>

    <?php if(isset($_SESSION['estado'])): ?>
        Swal.fire({
            icon: 'error',
            title: '<span class="swal2-title-v3 text-red-500">Aviso</span>',
            html: '<?php echo addslashes($_SESSION['estado']); unset($_SESSION['estado']); ?>',
            confirmButtonColor: '#070b14',
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
                    let customIcon = '';
                    if(response.icon) {
                        customIcon = `<div class="flex justify-center mb-6"><div class="w-24 h-24 rounded-3xl bg-slate-50 p-4 shadow-inner flex items-center justify-center border border-slate-100"><img src="${response.icon}" class="max-w-full max-h-full object-contain" alt="Logo Club"></div></div>`;
                    }

                    Swal.fire({
                        icon: response.icon ? undefined : 'success',
                        title: '<span class="swal2-title-v3">Acceso Concedido</span>',
                        html: `
                            <div class="font-lexend text-center">
                                ${customIcon}
                                <p class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight leading-tight mb-2">${response.message}</p>
                                <p class="text-xs sm:text-sm text-slate-400 font-medium">Sincronizando con tu club...</p>
                            </div>`,
                        timer: 2200,
                        showConfirmButton: false,
                        background: '#ffffff',
                        customClass: { popup: 'swal2-popup-v3' }
                    }).then(() => { 
                        window.location.href = response.redirect; 
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="swal2-title-v3 text-red-500">Error de Acceso</span>',
                        html: response.message,
                        confirmButtonColor: '#070b14',
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
                    confirmButtonColor: '#070b14',
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
                    confirmButtonColor: '#070b14',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '<span class="swal2-title-v3 text-red-500">Error</span>',
                    html: response.message,
                    confirmButtonColor: '#070b14',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            }
        },
        error: function() {
            Swal.fire({ 
                icon: 'error', 
                title: '<span class="swal2-title-v3 text-red-500">Error</span>', 
                text: 'El servidor no responde. Inténtalo más tarde.',
                confirmButtonColor: '#070b14',
                customClass: { popup: 'swal2-popup-v3' } 
            });
        }
    });
}
</script>
</body>
</html>
