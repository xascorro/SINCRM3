<?php
session_start();
require_once 'lib/my_functions.php';
$version = getVersion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SINCRM | Recuperar Contraseña</title>
    
    <link rel="manifest" href="manifest.json">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/83d95dbe8d.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#0f172a",
                        "secondary": "#3b82f6",
                    },
                    fontFamily: { lexend: ["Lexend", "sans-serif"] }
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Lexend', sans-serif; }
        .glass-overlay {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .animate-fade-in { animation: fadeIn 0.8s ease-out both; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .swal2-popup-v3 {
            border-radius: 3.5rem !important;
            padding: 3rem !important;
            font-family: 'Lexend', sans-serif !important;
        }
        .swal2-title-v3 {
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.2em !important;
            font-weight: 900 !important;
            color: #94a3b8 !important;
            margin-bottom: 0.5rem !important;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
<main class="flex min-h-screen">
    
    <!-- PANEL IZQUIERDO: VISUAL -->
    <section class="hidden lg:flex lg:w-1/2 relative overflow-hidden items-center justify-center bg-slate-900">
        <img alt="Natación Sincronizada" class="absolute inset-0 w-full h-full object-cover opacity-70" src="img/bg_sincro_login.jpg"/>
        <div class="relative z-10 glass-overlay p-12 rounded-[2.5rem] max-w-lg mx-8 animate-fade-in">
            <div class="flex flex-col gap-4">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl">
                    <img src="img/logo_sincrm4.png" class="w-10" alt="Logo">
                </div>
                <h1 class="text-5xl font-black text-white tracking-tighter italic">RECUPERAR <span class="text-blue-400">ACCESO</span></h1>
                <p class="text-xl text-blue-50 font-light leading-relaxed">No te preocupes, nos pasa a todos. Sigue los pasos para restablecer tu contraseña de forma segura.</p>
            </div>
        </div>
        <div class="absolute bottom-10 left-10 z-10 flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
            <p class="text-[10px] font-black text-white/60 uppercase tracking-[0.3em]"><?php echo $version; ?> stable</p>
        </div>
    </section>

    <!-- PANEL DERECHO: FORMULARIO MULTI-PASO -->
    <section class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-16 bg-white lg:rounded-l-[3rem] shadow-2xl z-20">
        <div class="w-full max-w-md animate-fade-in">
            
            <div id="step1">
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2 uppercase italic tracking-tighter">Paso 1</h2>
                    <p class="text-slate-400 font-medium italic text-sm">Introduce tu email para enviarte un código.</p>
                </div>
                <form id="requestOtpForm" class="space-y-6">
                    <input type="hidden" name="action" value="request_otp">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Email de Usuario</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-envelope text-sm"></i></span>
                            <input type="email" name="email" id="email_step1" required placeholder="ejemplo@sincrm.com" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-8 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-5 px-6 bg-slate-900 text-white font-black uppercase text-xs tracking-[0.3em] rounded-[1.5rem] shadow-2xl hover:bg-blue-600 active:scale-95 transition-all flex items-center justify-center gap-4 group">
                        <span>Enviar Código</span>
                        <i class="fas fa-paper-plane text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
            </div>

            <div id="step2" class="hidden">
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2 uppercase italic tracking-tighter">Paso 2</h2>
                    <p class="text-slate-400 font-medium italic text-sm">Introduce el código de 6 dígitos que hemos enviado a tu email.</p>
                </div>
                <form id="verifyOtpForm" class="space-y-6">
                    <input type="hidden" name="action" value="verify_otp">
                    <input type="hidden" name="email" id="email_step2">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Código de Verificación</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-key text-sm"></i></span>
                            <input type="text" name="otp" maxlength="6" required placeholder="000000" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-3xl font-black text-center tracking-[0.5em] text-slate-700 placeholder:text-slate-200 focus:bg-white focus:ring-8 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-5 px-6 bg-slate-900 text-white font-black uppercase text-xs tracking-[0.3em] rounded-[1.5rem] shadow-2xl hover:bg-blue-600 active:scale-95 transition-all flex items-center justify-center gap-4 group">
                        <span>Verificar Código</span>
                        <i class="fas fa-check-double text-[10px]"></i>
                    </button>
                    <button type="button" onclick="location.reload()" class="w-full text-center text-[10px] font-black uppercase text-slate-400 tracking-widest hover:text-slate-600 transition-colors">Volver a empezar</button>
                </form>
            </div>

            <div id="step3" class="hidden">
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2 uppercase italic tracking-tighter">Paso 3</h2>
                    <p class="text-slate-400 font-medium italic text-sm">Crea tu nueva contraseña de acceso.</p>
                </div>
                <form id="resetPasswordForm" class="space-y-6">
                    <input type="hidden" name="action" value="reset_password">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Nueva Contraseña</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-lock text-sm"></i></span>
                            <input type="password" name="password" id="password" required placeholder="••••••••" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-black text-slate-700 focus:bg-white focus:ring-8 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Repetir Contraseña</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-lock text-sm"></i></span>
                            <input type="password" name="password_r" required placeholder="••••••••" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-black text-slate-700 focus:bg-white focus:ring-8 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-5 px-6 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.3em] rounded-[1.5rem] shadow-2xl hover:bg-slate-900 active:scale-95 transition-all flex items-center justify-center gap-4 group">
                        <span>Actualizar Contraseña</span>
                        <i class="fas fa-save text-[10px]"></i>
                    </button>
                </form>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-100 text-center">
                <p class="text-sm font-medium text-slate-400 italic">
                    ¿Recordaste tu contraseña? 
                    <a href="login.php" class="text-slate-800 font-black hover:text-blue-600 transition-colors ml-1 not-italic border-b-2 border-slate-100">Volver al login</a>
                </p>
            </div>

        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // PASO 1: Solicitar OTP
    $('#requestOtpForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#email_step1').val();
        
        Swal.fire({
            title: '<span class="swal2-title-v3">Procesando</span>',
            html: '<div class="font-lexend text-sm text-slate-400">Enviando código de verificación...</div>',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); },
            customClass: { popup: 'swal2-popup-v3' }
        });

        $.ajax({
            type: 'POST',
            url: 'forgot_password_code.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '<span class="swal2-title-v3 text-blue-500">¡Código Enviado!</span>',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: { popup: 'swal2-popup-v3' }
                    });
                    $('#email_step2').val(email);
                    $('#step1').fadeOut(300, function() { $('#step2').fadeIn(300); });
                } else {
                    Swal.fire({ icon: 'error', title: '<span class="swal2-title-v3 text-red-500">Error</span>', text: response.message, customClass: { popup: 'swal2-popup-v3' } });
                }
            }
        });
    });

    // PASO 2: Verificar OTP
    $('#verifyOtpForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'forgot_password_code.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#step2').fadeOut(300, function() { $('#step3').fadeIn(300); });
                } else {
                    Swal.fire({ icon: 'error', title: '<span class="swal2-title-v3 text-red-500">Error</span>', text: response.message, customClass: { popup: 'swal2-popup-v3' } });
                }
            }
        });
    });

    // PASO 3: Restablecer Contraseña
    $('#resetPasswordForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'forgot_password_code.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '<span class="swal2-title-v3 text-blue-500">¡Éxito!</span>',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: false,
                        customClass: { popup: 'swal2-popup-v3' }
                    }).then(() => { window.location.href = 'login.php'; });
                } else {
                    Swal.fire({ icon: 'error', title: '<span class="swal2-title-v3 text-red-500">Error</span>', text: response.message, customClass: { popup: 'swal2-popup-v3' } });
                }
            }
        });
    });
});
</script>
</body>
</html>