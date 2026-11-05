<?php
@session_start();
require_once 'lib/my_functions.php';
$version = getVersion();
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0" name="viewport"/>
    <title>SINCRM | Recuperar Contraseña</title>
    
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

        /* Micro-animación de sacudida para error en OTP */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake-anim {
            animation: shake 0.4s var(--spring-bounce);
        }

        /* Micro-animación de éxito elástico */
        @keyframes successPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }
        .success-pop {
            animation: successPop 0.4s var(--spring-bounce);
        }

        /* Pulsación suave */
        @keyframes pulseBreathing {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.8; }
        }
        .pulse-breathing {
            animation: pulseBreathing 2.4s ease-in-out infinite;
        }

        .animate-fade-in { 
            animation: fadeIn 0.4s var(--spring-smooth) both; 
        }
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(6px); } 
            to { opacity: 1; transform: translateY(0); } 
        }

        /* Cajas de OTP */
        .otp-box {
            transition: all 0.25s var(--spring-bounce);
        }
        .otp-box:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            background-color: #ffffff;
            transform: translateY(-2px);
        }
        .otp-box.otp-success {
            border-color: #10b981 !important;
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18) !important;
        }
        .otp-box.otp-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.18) !important;
        }
        .otp-box.otp-loading {
            border-color: #0284c7 !important;
            background-color: #f0f9ff !important;
            animation: pulseBreathing 1.2s ease-in-out infinite;
        }
        
        /* Modal SweetAlert2 Refinado y Claro */
        .swal2-popup-v3 {
            border-radius: 2rem !important;
            padding: 2.25rem !important;
            font-family: 'Lexend', sans-serif !important;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.12) !important;
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
    <!-- PANEL IZQUIERDO: HERO VISUAL LUMINOSO (ESTILO IDENTICO A LOGIN)            -->
    <!-- ========================================================================= -->
    <section class="hidden lg:flex lg:w-1/2 xl:w-[50%] relative overflow-hidden flex-col justify-between p-10 xl:p-14 bg-gradient-to-br from-blue-700 via-sky-600 to-cyan-600 text-white shadow-2xl">
        
        <!-- Textura de Natación Artística y Luces Ambientales -->
        <div class="absolute inset-0 z-0">
            <img alt="Natación Sincronizada" class="w-full h-full object-cover opacity-20 mix-blend-overlay scale-105" src="img/bg_sincro_login.jpg"/>
            <div class="absolute inset-0 bg-gradient-to-t from-blue-900/40 via-transparent to-blue-900/10"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-cyan-300/25 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-400/25 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Cabecera Superior Izquierda -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="login.php" class="w-12 h-12 rounded-2xl bg-white p-2 shadow-lg shadow-blue-900/20 flex items-center justify-center border border-white/40 tap-press hover:scale-105 transition-transform">
                    <img src="img/logo_sincrm4.png" class="w-full h-full object-contain mix-blend-multiply" alt="Logo SINCRM 4">
                </a>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-1.5 leading-none">
                        SINCRM <span class="text-cyan-200 font-extrabold">4</span>
                    </h2>
                    <span class="text-[10px] font-bold text-sky-100/90 tracking-wider">Natación Artística</span>
                </div>
            </div>
            <div class="px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/25 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-300 pulse-breathing"></span>
                <span class="text-[10px] font-bold text-white uppercase tracking-widest tabular-nums">Recuperar Acceso</span>
            </div>
        </div>

        <!-- Tarjeta Central de Marca (Glassmorphism Claro) -->
        <div class="relative z-10 my-auto py-8">
            <div class="rounded-3xl p-8 xl:p-11 bg-white/15 backdrop-blur-xl border border-white/25 shadow-2xl relative overflow-hidden animate-fade-in text-white">
                
                <h1 class="text-4xl xl:text-5xl font-black text-white tracking-tight leading-[1.15] mb-4">
                    No te preocupes, a todos nos pasa esto <span class="text-cyan-200">de vez en cuando</span>.
                </h1>

                <p class="text-sm xl:text-base text-sky-100 font-light leading-relaxed mb-8 max-w-lg">
                    Sigue los 3 sencillos pasos para recuperar tu cuenta y volver a entrar al sistema.
                </p>

                <!-- 3 Pasos Visuales -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-6 border-t border-white/20">
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-cyan-200 text-xs font-black">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">1</span>
                            Email
                        </div>
                        <span class="text-[11px] text-sky-100/90 font-medium">Te enviamos un código.</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-cyan-200 text-xs font-black">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">2</span>
                            Código OTP
                        </div>
                        <span class="text-[11px] text-sky-100/90 font-medium">Escribe el código recibido.</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/15 flex flex-col gap-1 backdrop-blur-sm">
                        <div class="flex items-center gap-2 text-cyan-200 text-xs font-black">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">3</span>
                            Nueva Clave
                        </div>
                        <span class="text-[11px] text-sky-100/90 font-medium">Elige tu nueva contraseña.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie de Información Izquierdo -->
        <div class="relative z-10 flex items-center justify-between text-xs text-sky-100/80 pt-4 border-t border-white/15">
            <span class="flex items-center gap-1.5 font-medium text-[11px]">
                <i class="fas fa-lock text-[10px] text-cyan-200"></i> Acceso seguro
            </span>
            <span class="font-mono text-[10px] bg-white/15 px-2.5 py-1 rounded-full tabular-nums"><?php echo htmlspecialchars($version); ?> stable</span>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- PANEL DERECHO: FORMULARIO MULTI-PASO CON AUTOVALIDACIÓN                   -->
    <!-- ========================================================================= -->
    <section class="w-full lg:w-1/2 xl:w-[50%] flex flex-col justify-between p-6 sm:p-10 md:p-12 lg:p-14 bg-white relative z-20">
        
        <!-- Cabecera Superior Móvil (visible solo en pantallas pequeñas) -->
        <div class="lg:hidden flex items-center justify-between pb-6 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <a href="login.php" class="w-9 h-9 rounded-xl bg-slate-900 p-1.5 flex items-center justify-center shadow-md">
                    <img src="img/logo_sincrm4.png" class="w-full h-full object-contain" alt="Logo">
                </a>
                <div>
                    <h2 class="text-base font-black text-slate-800 leading-none">SINCRM 4</h2>
                    <span class="text-[9px] font-bold text-blue-600 tracking-wider">Recuperar Acceso</span>
                </div>
            </div>
            <a href="login.php" class="text-xs font-bold text-slate-400 hover:text-slate-700 flex items-center gap-1.5">
                <i class="fas fa-arrow-left text-[10px]"></i> Login
            </a>
        </div>

        <div class="w-full max-w-md mx-auto my-auto">
            
            <!-- STEPPER INDICATOR -->
            <div class="mb-8">
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 w-full bg-slate-100 -z-0"></div>
                    <div id="stepProgressLine" class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 w-0 bg-blue-600 transition-all duration-500 -z-0"></div>

                    <!-- Paso 1 Pill -->
                    <div id="stepPill1" class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all duration-300">
                        1
                    </div>
                    <!-- Paso 2 Pill -->
                    <div id="stepPill2" class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-400 font-bold text-xs border-2 border-white shadow-sm transition-all duration-300">
                        2
                    </div>
                    <!-- Paso 3 Pill -->
                    <div id="stepPill3" class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-400 font-bold text-xs border-2 border-white shadow-sm transition-all duration-300">
                        3
                    </div>
                </div>
                <div class="flex justify-between text-[11px] font-bold text-slate-400 mt-2 px-1">
                    <span id="stepLabel1" class="text-blue-600">Email</span>
                    <span id="stepLabel2">Código</span>
                    <span id="stepLabel3">Nueva Clave</span>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- PASO 1: SOLICITAR OTP                                         -->
            <!-- ============================================================= -->
            <div id="step1" class="animate-fade-in">
                <div class="mb-8 text-center sm:text-left">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold mb-3 border border-blue-100">
                        <i class="fas fa-envelope text-[10px]"></i> Paso 1 de 3
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">¿Has olvidado tu contraseña?</h2>
                    <p class="text-slate-500 text-sm mt-1.5">Introduce tu correo y te enviamos un código de 6 dígitos.</p>
                </div>

                <form id="requestOtpForm" class="space-y-5">
                    <input type="hidden" name="action" value="request_otp">
                    
                    <div>
                        <label for="email_step1" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                                <i class="fas fa-envelope text-sm"></i>
                            </span>
                            <input type="email" 
                                   name="email" 
                                   id="email_step1" 
                                   required 
                                   autocomplete="email"
                                   placeholder="tu@email.com" 
                                   class="input-glow w-full pl-11 pr-4 py-3.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" 
                            id="btnRequestOtp"
                            class="tap-press w-full py-4 px-6 bg-slate-900 hover:bg-blue-600 active:scale-[0.98] text-white font-bold text-sm rounded-2xl shadow-lg shadow-slate-900/10 hover:shadow-blue-500/25 transition-all flex items-center justify-center gap-2 group">
                        <span>Enviar código</span>
                        <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
            </div>

            <!-- ============================================================= -->
            <!-- PASO 2: VERIFICAR OTP (AUTOVALIDACIÓN REACTIVA)              -->
            <!-- ============================================================= -->
            <div id="step2" class="hidden animate-fade-in">
                <div class="mb-6 text-center sm:text-left">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 text-sky-700 text-[11px] font-bold mb-3 border border-sky-100">
                        Paso 2 de 3
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">Introduce el código</h2>
                    <p class="text-slate-500 text-sm mt-1.5">
                        Hemos enviado un código de 6 dígitos a <strong id="emailDisplay" class="text-slate-800 font-semibold break-all"></strong>.
                    </p>
                </div>

                <form id="verifyOtpForm" class="space-y-6">
                    <input type="hidden" name="action" value="verify_otp">
                    <input type="hidden" name="email" id="email_step2">
                    <input type="hidden" name="otp" id="fullOtpInput">

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Código de 6 Dígitos
                            </label>
                            <span id="timerBadge" class="text-[11px] font-bold text-slate-400 tabular-nums flex items-center gap-1">
                                <i class="fas fa-clock text-[10px]"></i> 15:00
                            </span>
                        </div>

                        <!-- 6 CAJAS DE ENTRADA OTP CON ANIMACION Y FEEDBACK REACTIVO -->
                        <div id="otpBoxesContainer" class="flex items-center justify-between gap-1.5 sm:gap-2.5">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black font-mono tabular-nums rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none" data-index="0" autofocus>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black font-mono tabular-nums rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none" data-index="1">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black font-mono tabular-nums rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none" data-index="2">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black font-mono tabular-nums rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none" data-index="3">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black font-mono tabular-nums rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none" data-index="4">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-black font-mono tabular-nums rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none" data-index="5">
                        </div>

                        <!-- FEEDBACK STATUS INLINE (CORRECTO O ERRÓNEO) -->
                        <div id="otpFeedback" class="min-h-[24px] mt-3 text-center text-xs font-bold transition-all"></div>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <button type="button" id="btnResendOtp" class="font-bold text-blue-600 hover:text-blue-700 disabled:text-slate-400 disabled:cursor-not-allowed transition-colors flex items-center gap-1.5">
                            <i class="fas fa-rotate-right text-[10px]"></i> <span>Reenviar código</span>
                        </button>
                        <button type="button" id="btnChangeEmail" class="font-semibold text-slate-400 hover:text-slate-600 transition-colors">
                            Cambiar de email
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============================================================= -->
            <!-- PASO 3: RESTABLECER CONTRASEÑA                                -->
            <!-- ============================================================= -->
            <div id="step3" class="hidden animate-fade-in">
                <div class="mb-8 text-center sm:text-left">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold mb-3 border border-emerald-100">
                        <i class="fas fa-check-circle text-[10px]"></i> Paso 3 de 3
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">Crea tu Nueva Contraseña</h2>
                    <p class="text-slate-500 text-sm mt-1.5">Establece una contraseña segura de al menos 6 caracteres.</p>
                </div>

                <form id="resetPasswordForm" class="space-y-5">
                    <input type="hidden" name="action" value="reset_password">

                    <div>
                        <label for="password_step3" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nueva Contraseña
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" 
                                   name="password" 
                                   id="password_step3" 
                                   required 
                                   minlength="6"
                                   placeholder="Mínimo 6 caracteres" 
                                   class="input-glow w-full pl-11 pr-12 py-3.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none">
                            <button type="button" class="toggle-pwd-btn absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" data-target="password_step3">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="password_r_step3" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Repetir Contraseña
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors pointer-events-none">
                                <i class="fas fa-lock-open text-sm"></i>
                            </span>
                            <input type="password" 
                                   name="password_r" 
                                   id="password_r_step3" 
                                   required 
                                   minlength="6"
                                   placeholder="Repite la contraseña" 
                                   class="input-glow w-full pl-11 pr-12 py-3.5 bg-slate-50/80 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none">
                            <button type="button" class="toggle-pwd-btn absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" data-target="password_r_step3">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div id="passwordMatchHint" class="text-xs font-semibold text-slate-400 flex items-center gap-1.5 min-h-[18px]">
                        <!-- feedback match -->
                    </div>

                    <button type="submit" 
                            id="btnResetPassword"
                            class="tap-press w-full py-4 px-6 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold text-sm rounded-2xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 group">
                        <i class="fas fa-check text-xs"></i>
                        <span>Guardar Nueva Contraseña</span>
                    </button>
                </form>
            </div>

            <!-- ENLACE DE RETORNO AL LOGIN -->
            <div class="mt-10 pt-6 border-t border-slate-100 text-center">
                <p class="text-xs font-medium text-slate-500">
                    ¿Te acordaste de tu contraseña? 
                    <a href="login.php" class="text-blue-600 font-bold hover:underline ml-1">Volver al login</a>
                </p>
            </div>

        </div>

        <!-- Pie Móvil Discreto -->
        <div class="pt-6 text-center lg:text-left text-[11px] text-slate-400 border-t border-slate-100 mt-auto">
            <span>&copy; <?php echo date('Y'); ?> SINCRM &middot; Natación Artística</span>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function() {
    let currentStep = 1;
    let resendCooldown = 0;
    let resendTimer = null;

    function setStep(step) {
        currentStep = step;
        if (step === 1) {
            $('#stepProgressLine').css('width', '0%');
            $('#stepPill1').removeClass('bg-slate-100 text-slate-400').addClass('bg-blue-600 text-white shadow-md shadow-blue-500/20');
            $('#stepPill2').removeClass('bg-blue-600 text-white shadow-md shadow-blue-500/20 bg-emerald-500 text-white').addClass('bg-slate-100 text-slate-400');
            $('#stepPill3').removeClass('bg-blue-600 text-white shadow-md shadow-blue-500/20 bg-emerald-500 text-white').addClass('bg-slate-100 text-slate-400');
            $('#stepLabel1').addClass('text-blue-600');
            $('#stepLabel2, #stepLabel3').removeClass('text-blue-600');
        } else if (step === 2) {
            $('#stepProgressLine').css('width', '50%');
            $('#stepPill1').removeClass('bg-blue-600 shadow-blue-500/20').addClass('bg-emerald-500 text-white').html('<i class="fas fa-check text-[10px]"></i>');
            $('#stepPill2').removeClass('bg-slate-100 text-slate-400').addClass('bg-blue-600 text-white shadow-md shadow-blue-500/20').text('2');
            $('#stepPill3').removeClass('bg-blue-600 text-white shadow-md shadow-blue-500/20 bg-emerald-500 text-white').addClass('bg-slate-100 text-slate-400');
            $('#stepLabel1').removeClass('text-blue-600').addClass('text-emerald-600');
            $('#stepLabel2').addClass('text-blue-600');
            $('#stepLabel3').removeClass('text-blue-600');
        } else if (step === 3) {
            $('#stepProgressLine').css('width', '100%');
            $('#stepPill1').addClass('bg-emerald-500 text-white').html('<i class="fas fa-check text-[10px]"></i>');
            $('#stepPill2').removeClass('bg-blue-600 shadow-blue-500/20').addClass('bg-emerald-500 text-white').html('<i class="fas fa-check text-[10px]"></i>');
            $('#stepPill3').removeClass('bg-slate-100 text-slate-400').addClass('bg-blue-600 text-white shadow-md shadow-blue-500/20').text('3');
            $('#stepLabel1, #stepLabel2').removeClass('text-blue-600').addClass('text-emerald-600');
            $('#stepLabel3').addClass('text-blue-600');
        }
    }

    // Toggle para ver contraseña
    $('.toggle-pwd-btn').on('click', function() {
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Validar coincidencia de contraseña en tiempo real
    $('#password_step3, #password_r_step3').on('input', function() {
        const p1 = $('#password_step3').val();
        const p2 = $('#password_r_step3').val();
        const hint = $('#passwordMatchHint');
        
        if (!p1 && !p2) {
            hint.html('');
            return;
        }
        if (p1.length < 6) {
            hint.html('<span class="text-amber-500"><i class="fas fa-circle-exclamation"></i> Mínimo 6 caracteres</span>');
            return;
        }
        if (p2 && p1 === p2) {
            hint.html('<span class="text-emerald-600"><i class="fas fa-circle-check"></i> Las contraseñas coinciden perfectamente</span>');
        } else if (p2 && p1 !== p2) {
            hint.html('<span class="text-rose-500"><i class="fas fa-circle-xmark"></i> Las contraseñas aún no coinciden</span>');
        } else {
            hint.html('');
        }
    });

    // Cooldown para reenvío de OTP
    function startResendCooldown(seconds = 60) {
        resendCooldown = seconds;
        $('#btnResendOtp').prop('disabled', true);
        if (resendTimer) clearInterval(resendTimer);
        resendTimer = setInterval(function() {
            resendCooldown--;
            if (resendCooldown <= 0) {
                clearInterval(resendTimer);
                $('#btnResendOtp').prop('disabled', false).html('<i class="fas fa-rotate-right text-[10px]"></i> <span>Reenviar código</span>');
            } else {
                $('#btnResendOtp').html('<i class="fas fa-clock text-[10px]"></i> <span>Reenviar en ' + resendCooldown + 's</span>');
            }
        }, 1000);
    }

    // =========================================================================
    // PASO 1: SOLICITAR OTP
    // =========================================================================
    $('#requestOtpForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#email_step1').val().trim();
        if (!email) return;

        const btn = $('#btnRequestOtp');
        const originalBtnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> <span>Enviando código...</span>');

        $.ajax({
            type: 'POST',
            url: 'forgot_password_code.php',
            data: { action: 'request_otp', email: email },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html(originalBtnHtml);
                if (response.status === 'success') {
                    $('#emailDisplay').text(email);
                    $('#email_step2').val(email);
                    setStep(2);
                    $('#step1').fadeOut(250, function() {
                        $('#step2').fadeIn(250, function() {
                            $('.otp-box').first().focus();
                        });
                    });
                    startResendCooldown(60);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="swal2-title-v3 text-rose-500">No se pudo enviar</span>',
                        text: response.message,
                        customClass: { popup: 'swal2-popup-v3' }
                    });
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalBtnHtml);
                Swal.fire({
                    icon: 'error',
                    title: '<span class="swal2-title-v3 text-rose-500">Error de Conexión</span>',
                    text: 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            }
        });
    });

    // Reenviar código desde el Paso 2
    $('#btnResendOtp').on('click', function() {
        if (resendCooldown > 0) return;
        const email = $('#email_step2').val();
        if (!email) return;

        $(this).prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> <span>Reenviando...</span>');
        $.ajax({
            type: 'POST',
            url: 'forgot_password_code.php',
            data: { action: 'request_otp', email: email },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#otpFeedback').html('<span class="text-emerald-600"><i class="fas fa-check-circle"></i> Nuevo código enviado a tu bandeja de entrada</span>');
                    startResendCooldown(60);
                    clearOtpBoxes();
                } else {
                    $('#btnResendOtp').prop('disabled', false).html('<i class="fas fa-rotate-right text-[10px]"></i> <span>Reenviar código</span>');
                    $('#otpFeedback').html('<span class="text-rose-500"><i class="fas fa-triangle-exclamation"></i> ' + response.message + '</span>');
                }
            }
        });
    });

    // Cambiar de email
    $('#btnChangeEmail').on('click', function() {
        setStep(1);
        $('#step2').fadeOut(250, function() {
            $('#step1').fadeIn(250);
            $('#email_step1').focus();
        });
    });

    // =========================================================================
    // GESTIÓN DE LAS 6 CAJAS OTP Y AUTOVALIDACIÓN
    // =========================================================================
    const otpBoxes = $('.otp-box');

    function getFullOtp() {
        let code = '';
        otpBoxes.each(function() {
            code += $(this).val();
        });
        return code;
    }

    function clearOtpBoxes() {
        otpBoxes.val('').removeClass('otp-success otp-error otp-loading');
        $('#otpBoxesContainer').removeClass('shake-anim success-pop');
        $('#otpFeedback').html('');
        otpBoxes.first().focus();
    }

    otpBoxes.on('input', function(e) {
        const index = parseInt($(this).data('index'));
        const val = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(val);

        otpBoxes.removeClass('otp-error');
        $('#otpFeedback').html('');

        if (val && index < 5) {
            otpBoxes.eq(index + 1).focus();
        }

        const fullOtp = getFullOtp();
        $('#fullOtpInput').val(fullOtp);

        // AUTOVALIDACIÓN CUANDO SE COMPLETAN LOS 6 DÍGITOS
        if (fullOtp.length === 6) {
            triggerAutoValidation(fullOtp);
        }
    });

    otpBoxes.on('keydown', function(e) {
        const index = parseInt($(this).data('index'));
        if (e.key === 'Backspace' && !$(this).val() && index > 0) {
            otpBoxes.eq(index - 1).focus().val('');
        } else if (e.key === 'ArrowLeft' && index > 0) {
            otpBoxes.eq(index - 1).focus();
        } else if (e.key === 'ArrowRight' && index < 5) {
            otpBoxes.eq(index + 1).focus();
        }
    });

    // Soporte para pegar los 6 dígitos de una vez
    otpBoxes.on('paste', function(e) {
        e.preventDefault();
        const pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
        if (pasteData.length > 0) {
            for (let i = 0; i < 6; i++) {
                if (i < pasteData.length) {
                    otpBoxes.eq(i).val(pasteData[i]);
                } else {
                    otpBoxes.eq(i).val('');
                }
            }
            const fullOtp = getFullOtp();
            $('#fullOtpInput').val(fullOtp);
            if (fullOtp.length === 6) {
                triggerAutoValidation(fullOtp);
            } else {
                otpBoxes.eq(Math.min(pasteData.length, 5)).focus();
            }
        }
    });

    // EJECUCIÓN DE LA AUTOVALIDACIÓN REACTIVA
    let isVerifying = false;
    function triggerAutoValidation(otp) {
        if (isVerifying) return;
        isVerifying = true;

        const email = $('#email_step2').val();
        
        // Estado visual: Verificando
        otpBoxes.addClass('otp-loading').prop('readonly', true);
        $('#otpFeedback').html('<span class="text-blue-600 inline-flex items-center gap-1.5"><i class="fas fa-circle-notch fa-spin text-xs"></i> Comprobando código...</span>');

        $.ajax({
            type: 'POST',
            url: 'forgot_password_code.php',
            data: { action: 'verify_otp', email: email, otp: otp },
            dataType: 'json',
            success: function(response) {
                otpBoxes.removeClass('otp-loading').prop('readonly', false);
                isVerifying = false;

                if (response.status === 'success') {
                    // Feedback visual: Verde y micro-animación de éxito
                    otpBoxes.removeClass('otp-error').addClass('otp-success');
                    $('#otpBoxesContainer').addClass('success-pop');
                    $('#otpFeedback').html('<span class="text-emerald-600 inline-flex items-center gap-1.5"><i class="fas fa-circle-check text-sm"></i> ¡Código verificado correctamente!</span>');

                    // Transición automática al Paso 3 tras 550ms
                    setTimeout(function() {
                        setStep(3);
                        $('#step2').fadeOut(300, function() {
                            $('#step3').fadeIn(300, function() {
                                $('#password_step3').focus();
                            });
                        });
                    }, 550);

                } else {
                    // Feedback visual: Rojo y sacudida
                    otpBoxes.removeClass('otp-success').addClass('otp-error');
                    $('#otpBoxesContainer').addClass('shake-anim');
                    $('#otpFeedback').html('<span class="text-rose-600 inline-flex items-center gap-1.5"><i class="fas fa-circle-xmark text-sm"></i> ' + (response.message || 'Código inválido o caducado') + '</span>');

                    // Limpiar animación tras terminar y permitir reescritura
                    setTimeout(function() {
                        $('#otpBoxesContainer').removeClass('shake-anim');
                        otpBoxes.val('').removeClass('otp-error');
                        otpBoxes.first().focus();
                    }, 900);
                }
            },
            error: function() {
                otpBoxes.removeClass('otp-loading').prop('readonly', false);
                isVerifying = false;
                otpBoxes.addClass('otp-error');
                $('#otpFeedback').html('<span class="text-rose-600 inline-flex items-center gap-1.5"><i class="fas fa-triangle-exclamation"></i> Error al conectar con el servidor</span>');
            }
        });
    }

    // =========================================================================
    // PASO 3: RESTABLECER CONTRASEÑA
    // =========================================================================
    $('#resetPasswordForm').on('submit', function(e) {
        e.preventDefault();
        const p1 = $('#password_step3').val();
        const p2 = $('#password_r_step3').val();

        if (p1.length < 6) {
            Swal.fire({
                icon: 'warning',
                title: '<span class="swal2-title-v3 text-amber-500">Contraseña corta</span>',
                text: 'La contraseña debe contener al menos 6 caracteres.',
                customClass: { popup: 'swal2-popup-v3' }
            });
            return;
        }

        if (p1 !== p2) {
            Swal.fire({
                icon: 'warning',
                title: '<span class="swal2-title-v3 text-amber-500">No coinciden</span>',
                text: 'Las contraseñas introducidas no son iguales.',
                customClass: { popup: 'swal2-popup-v3' }
            });
            return;
        }

        const btn = $('#btnResetPassword');
        const originalBtnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> <span>Actualizando...</span>');

        $.ajax({
            type: 'POST',
            url: 'forgot_password_code.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html(originalBtnHtml);
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '<span class="swal2-title-v3 text-emerald-600">¡Contraseña Cambiada!</span>',
                        text: response.message,
                        timer: 2400,
                        showConfirmButton: false,
                        customClass: { popup: 'swal2-popup-v3' }
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="swal2-title-v3 text-rose-500">Error</span>',
                        text: response.message,
                        customClass: { popup: 'swal2-popup-v3' }
                    });
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalBtnHtml);
                Swal.fire({
                    icon: 'error',
                    title: '<span class="swal2-title-v3 text-rose-500">Error</span>',
                    text: 'Error de comunicación con el servidor al actualizar la contraseña.',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            }
        });
    });
});
</script>
</body>
</html>
