<?php
@session_start();
// Si el usuario ya está autenticado, redirigir al panel principal
if (isset($_SESSION['id_usario'])) {
    header("Location: index.php");
    exit();
}
require_once 'lib/my_functions.php';
$version = getVersion();
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0" name="viewport"/>
    <title>SINCRM | Registro de Usuario</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Icons suite -->
    <link rel="icon" type="image/png" sizes="32x32" href="pwa-icons/32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="pwa-icons/16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="pwa-icons/180x180.png">

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
            animation: fadeIn 0.4s var(--spring-smooth) both; 
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
    <!-- PANEL IZQUIERDO: HERO VISUAL LUMINOSO (PC Y TABLETS >= LG)                -->
    <!-- ========================================================================= -->
    <section class="hidden lg:flex lg:w-1/2 xl:w-[48%] relative overflow-hidden flex-col justify-between p-10 xl:p-14 bg-gradient-to-br from-blue-700 via-sky-600 to-cyan-600 text-white shadow-2xl">
        
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
            <a href="login.php" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-white p-2 shadow-lg shadow-blue-900/20 flex items-center justify-center border border-white/40 tap-press group-hover:scale-105 transition-transform">
                    <img src="img/logo_sincrm4.png" class="w-full h-full object-contain mix-blend-multiply" alt="Logo SINCRM 4">
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-1.5 leading-none">
                        SINCRM <span class="text-cyan-200 font-extrabold">4</span>
                    </h2>
                    <span class="text-[10px] font-bold text-sky-100/90 tracking-wider">Natación Artística</span>
                </div>
            </a>
            <div class="px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/25 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-300 pulse-breathing"></span>
                <span class="text-[10px] font-bold text-white uppercase tracking-widest tabular-nums">Registro Abierto</span>
            </div>
        </div>

        <!-- Tarjeta Central de Marca (Glassmorphism Claro) -->
        <div class="relative z-10 my-auto py-8">
            <div class="rounded-3xl p-8 xl:p-11 bg-white/15 backdrop-blur-xl border border-white/25 shadow-2xl relative overflow-hidden animate-fade-in text-white">
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 border border-white/30 text-white text-[10px] font-bold uppercase tracking-wider mb-5">
                    <i class="fas fa-user-plus text-xs text-cyan-200"></i> Nueva Cuenta de Acceso
                </div>

                <h1 class="text-3xl xl:text-4xl font-black text-white tracking-tight leading-[1.2] mb-4">
                    Crea tu cuenta en <span class="text-cyan-200">pocos segundos</span>.
                </h1>

                <p class="text-sm xl:text-base text-sky-100 font-light leading-relaxed mb-8 max-w-lg">
                    Únete a la plataforma oficial para gestionar inscripciones, rutinas, música y resultados de natación artística.
                </p>

                <!-- 3 Pasos del Proceso -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-6 border-t border-white/20">
                    <div class="p-3.5 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-cyan-200 text-xs font-black">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] tabular-nums">1</span>
                            Tus Datos
                        </div>
                        <span class="text-[11px] text-sky-100/90 font-medium leading-tight">Nombre, email, clave y tu club o función.</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-cyan-200 text-xs font-black">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] tabular-nums">2</span>
                            Verificación
                        </div>
                        <span class="text-[11px] text-sky-100/90 font-medium leading-tight">Recibes un email para confirmar tu dirección.</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-cyan-200 text-xs font-black">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] tabular-nums">3</span>
                            Activación
                        </div>
                        <span class="text-[11px] text-sky-100/90 font-medium leading-tight">Un administrador autoriza tu acceso al panel.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie de Información Izquierdo -->
        <div class="relative z-10 flex items-center justify-between text-[11px] font-bold text-sky-100/90 pt-4 border-t border-white/15">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-300 text-xs"></i>
                <span class="tracking-wider">Temporada 2026 / 2027</span>
            </div>
            <div class="tabular-nums uppercase tracking-widest text-[10px] text-white/80 font-bold">
                Versión <?php echo htmlspecialchars($version); ?> stable
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- PANEL DERECHO: FORMULARIO DE REGISTRO (MÓVIL, TABLET Y ESCRITORIO)        -->
    <!-- ========================================================================= -->
    <section class="w-full lg:w-1/2 xl:w-[52%] flex flex-col justify-between p-5 sm:p-8 md:p-12 lg:p-12 xl:p-14 bg-white z-20 min-h-screen">
        
        <!-- Header Móvil (< lg) -->
        <div class="lg:hidden flex items-center justify-between pb-4 mb-4 border-b border-slate-100 animate-fade-in">
            <a href="login.php" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-slate-900 p-1.5 flex items-center justify-center shadow-md">
                    <img src="img/logo_sincrm4.png" class="w-full h-full object-contain" alt="Logo SINCRM">
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-800 leading-none">SINCRM 4</h2>
                    <span class="text-[9px] font-bold text-blue-600 tracking-wider">Nueva Cuenta</span>
                </div>
            </a>
            <a href="login.php" class="text-xs font-bold text-slate-500 hover:text-blue-600 flex items-center gap-1.5 tap-press py-1 px-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i> Iniciar Sesión
            </a>
        </div>

        <!-- Contenedor Central del Formulario -->
        <div class="w-full max-w-xl mx-auto my-auto animate-fade-in py-2">
            
            <!-- Título & Subtítulo (Desktop) -->
            <div class="hidden lg:block mb-6 text-left">
                <span class="inline-block px-3 py-1 rounded-lg bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-widest mb-2 border border-blue-100">
                    Solicitud de Acceso
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Crea tu cuenta</h2>
                <p class="text-slate-500 font-normal text-sm mt-1">Completa tus datos para solicitar el acceso a la plataforma.</p>
            </div>

            <!-- Título & Subtítulo (Móvil) -->
            <div class="lg:hidden mb-5 text-left">
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Crea tu cuenta</h2>
                <p class="text-slate-500 font-normal text-xs mt-0.5">Rellena el formulario para solicitar tu acceso.</p>
            </div>

            <form id="registerForm" class="space-y-4">
                
                <!-- Fila 1: Nombre Completo y Teléfono -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div class="space-y-1.5">
                        <label for="inputUsername" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 px-1 flex items-center gap-1.5">
                            <i class="fas fa-user text-slate-400 text-xs"></i>
                            <span>Nombre y Apellidos</span>
                            <span class="text-rose-500 font-black">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                                <i class="fas fa-id-card text-sm"></i>
                            </span>
                            <input type="text" 
                                   id="inputUsername"
                                   name="username" 
                                   required 
                                   autocomplete="name"
                                   placeholder="Tu nombre completo" 
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 input-glow">
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label for="inputTelefono" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 px-1 flex items-center gap-1.5">
                            <i class="fas fa-phone text-slate-400 text-xs"></i>
                            <span>Teléfono</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                                <i class="fas fa-mobile-screen text-sm"></i>
                            </span>
                            <input type="tel" 
                                   id="inputTelefono"
                                   name="telefono" 
                                   autocomplete="tel"
                                   placeholder="600 000 000" 
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 tabular-nums input-glow">
                        </div>
                    </div>
                </div>

                <!-- Fila 2: Email de Usuario -->
                <div class="space-y-1.5">
                    <label for="inputEmail" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 px-1 flex items-center gap-1.5">
                        <i class="fas fa-at text-slate-400 text-xs"></i>
                        <span>Correo Electrónico</span>
                        <span class="text-rose-500 font-black">*</span>
                    </label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                            <i class="fas fa-envelope text-sm"></i>
                        </span>
                        <input type="email" 
                               id="inputEmail"
                               name="email" 
                               required 
                               autocomplete="email"
                               placeholder="tu-correo@ejemplo.com" 
                               class="w-full pl-11 pr-4 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 input-glow">
                    </div>
                    <span class="text-[11px] text-slate-400 px-1 font-normal block">Recibirás un enlace a este email para verificar tu cuenta.</span>
                </div>

                <!-- Fila 3: Contraseña y Repetir Contraseña -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div class="space-y-1.5">
                        <label for="inputPassword" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 px-1 flex items-center gap-1.5">
                            <i class="fas fa-lock text-slate-400 text-xs"></i>
                            <span>Contraseña</span>
                            <span class="text-rose-500 font-black">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                                <i class="fas fa-key text-sm"></i>
                            </span>
                            <input type="password" 
                                   id="inputPassword"
                                   name="password" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder="••••••••" 
                                   class="w-full pl-11 pr-11 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 input-glow">
                            <button type="button" 
                                    onclick="togglePasswordVisibility('inputPassword', 'eyeIcon1')"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors p-1"
                                    title="Mostrar/Ocultar contraseña"
                                    tabindex="-1">
                                <i id="eyeIcon1" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between px-1">
                            <label for="inputPasswordR" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                                <i class="fas fa-lock text-slate-400 text-xs"></i>
                                <span>Repetir Clave</span>
                                <span class="text-rose-500 font-black">*</span>
                            </label>
                            <span id="passwordMatchBadge" class="text-[10px] font-bold hidden"></span>
                        </div>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                                <i class="fas fa-check-double text-sm"></i>
                            </span>
                            <input type="password" 
                                   id="inputPasswordR"
                                   name="password_r" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder="••••••••" 
                                   class="w-full pl-11 pr-11 py-3 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 input-glow">
                            <button type="button" 
                                    onclick="togglePasswordVisibility('inputPasswordR', 'eyeIcon2')"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors p-1"
                                    title="Mostrar/Ocultar contraseña"
                                    tabindex="-1">
                                <i id="eyeIcon2" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Fila 4: Club / Cargo / Comentario -->
                <div class="space-y-1.5">
                    <label for="inputComentario" class="text-[11px] font-bold uppercase tracking-wider text-slate-600 px-1 flex items-center gap-1.5">
                        <i class="fas fa-shield-halved text-slate-400 text-xs"></i>
                        <span>Club, Cargo o Función</span>
                    </label>
                    <div class="relative group">
                        <span class="absolute left-4 top-3 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                            <i class="fas fa-building-columns text-sm"></i>
                        </span>
                        <textarea id="inputComentario"
                                  name="comentario" 
                                  rows="2" 
                                  placeholder="Ej: Entrenador de Club X, Juez Árbitro, Delegado..." 
                                  class="w-full pl-11 pr-4 py-2.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400/80 input-glow resize-none"></textarea>
                    </div>
                    <span class="text-[11px] text-slate-400 px-1 font-normal block">Ayuda al administrador a asignarte los permisos correspondientes rápidamente.</span>
                </div>

                <!-- Botón de Envío -->
                <div class="pt-2">
                    <button id="submitBtn" 
                            type="submit" 
                            class="tap-press w-full py-3.5 sm:py-4 px-6 bg-slate-900 hover:bg-blue-600 active:bg-blue-700 text-white font-bold uppercase text-xs tracking-wider rounded-2xl shadow-xl shadow-slate-900/10 hover:shadow-blue-500/20 transition-all flex items-center justify-center gap-3 group">
                        <span id="btnText">Enviar Solicitud de Registro</span>
                        <i id="btnIcon" class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                        <div id="loader" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-white/20 border-t-white"></div>
                    </button>
                </div>
            </form>

            <!-- Enlace a Login -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs sm:text-sm font-medium text-slate-500">
                    ¿Ya tienes una cuenta creada? 
                    <a href="login.php" class="text-blue-600 font-bold hover:text-blue-700 hover:underline transition-colors ml-1">
                        Inicia Sesión
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer derecho sutil -->
        <div class="w-full text-center pt-3 text-[11px] text-slate-400">
            <span>SINCRM 4 &copy; <?php echo date('Y'); ?> &bull; Sistema de Gestión Técnica</span>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery/jquery.min.js"></script>
<script>
// Alternar visibilidad de contraseña
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
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
    const inputPass = $('#inputPassword');
    const inputPassR = $('#inputPasswordR');
    const matchBadge = $('#passwordMatchBadge');

    // Validación reactiva de contraseñas
    function checkPasswordsMatch() {
        const val1 = inputPass.val();
        const val2 = inputPassR.val();

        if (val2.length === 0) {
            matchBadge.addClass('hidden').removeClass('text-emerald-600 text-rose-500');
            return;
        }

        if (val1 === val2) {
            matchBadge.removeClass('hidden text-rose-500')
                      .addClass('text-emerald-600')
                      .html('<i class="fas fa-check-circle mr-1"></i>Coinciden');
        } else {
            matchBadge.removeClass('hidden text-emerald-600')
                      .addClass('text-rose-500')
                      .html('<i class="fas fa-times-circle mr-1"></i>No coinciden');
        }
    }

    inputPass.on('input', checkPasswordsMatch);
    inputPassR.on('input', checkPasswordsMatch);

    // Envío del Formulario vía AJAX
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const pass1 = inputPass.val();
        const pass2 = inputPassR.val();

        // Comprobación cliente rápida para evitar viajes innecesarios
        if (pass1 !== pass2) {
            Swal.fire({
                icon: 'warning',
                title: '<span class="swal2-title-v3 text-amber-500">Verifica la Contraseña</span>',
                html: '<div class="font-lexend text-sm text-slate-700 pt-1">Las dos contraseñas introducidas no coinciden. Por favor, revísalas.</div>',
                confirmButtonColor: '#0f172a',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'swal2-popup-v3' }
            });
            inputPassR.focus();
            return;
        }

        const submitBtn = $('#submitBtn');
        const btnText = $('#btnText');
        const btnIcon = $('#btnIcon');
        const loader = $('#loader');

        submitBtn.prop('disabled', true).addClass('bg-blue-600');
        btnText.text('Enviando solicitud...');
        btnIcon.addClass('hidden');
        loader.removeClass('hidden');

        $.ajax({
            type: 'POST',
            url: 'login_code.php',
            data: form.serialize() + '&register_btn=1',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '<span class="swal2-title-v3 text-emerald-600">¡Registro Completado!</span>',
                        html: `
                            <div class="font-lexend text-left pt-2">
                                <p class="text-base font-black text-slate-800 tracking-tight leading-snug mb-3">
                                    Te hemos enviado un correo de verificación.
                                </p>
                                <p class="text-xs text-slate-600 font-normal leading-relaxed mb-2">
                                    Abre el enlace que acabas de recibir en tu bandeja de entrada (revisa también la carpeta de spam si tardara unos segundos).
                                </p>
                                <div class="p-3 bg-blue-50/80 border border-blue-100 rounded-xl text-[11px] text-blue-800 font-medium">
                                    <i class="fas fa-info-circle mr-1 text-blue-600"></i>
                                    Una vez verificado tu email, el administrador activará tus permisos de acceso.
                                </div>
                            </div>
                        `,
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'Ir a Iniciar Sesión',
                        customClass: { popup: 'swal2-popup-v3' }
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="swal2-title-v3 text-rose-500">No se pudo registrar</span>',
                        html: `<div class="font-lexend text-sm text-slate-700 pt-1">${response.message || 'Ha ocurrido un error inesperado al procesar el registro.'}</div>`,
                        confirmButtonColor: '#0f172a',
                        confirmButtonText: 'Corregir Datos',
                        customClass: { popup: 'swal2-popup-v3' }
                    });
                    submitBtn.prop('disabled', false).removeClass('bg-blue-600');
                    btnText.text('Enviar Solicitud de Registro');
                    btnIcon.removeClass('hidden');
                    loader.addClass('hidden');
                }
            },
            error: function() {
                Swal.fire({ 
                    icon: 'error', 
                    title: '<span class="swal2-title-v3 text-rose-500">Error de Conexión</span>', 
                    html: '<div class="font-lexend text-sm text-slate-700 pt-1">No se pudo contactar con el servidor. Por favor, comprueba tu conexión y vuelve a intentarlo.</div>', 
                    confirmButtonColor: '#0f172a',
                    confirmButtonText: 'Entendido',
                    customClass: { popup: 'swal2-popup-v3' } 
                });
                submitBtn.prop('disabled', false).removeClass('bg-blue-600');
                btnText.text('Enviar Solicitud de Registro');
                btnIcon.removeClass('hidden');
                loader.addClass('hidden');
            }
        });
    });
});
</script>
</body>
</html>
