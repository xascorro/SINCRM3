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
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SINCRM 4 | Acceso</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f172a">
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
        
        /* SweetAlert2 Custom Styling */
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
                <h1 class="text-5xl font-black text-white tracking-tighter italic">SINCRM <span class="text-blue-400">4</span></h1>
                <p class="text-xl text-blue-50 font-light leading-relaxed">Donde el arte se une a la precisión. La gestión definitiva para natación artística.</p>
            </div>
        </div>
        <div class="absolute bottom-10 left-10 z-10 flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
            <p class="text-[10px] font-black text-white/60 uppercase tracking-[0.3em]"><?php echo $version; ?> stable</p>
        </div>
    </section>

    <!-- PANEL DERECHO: FORMULARIO -->
    <section class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-16 bg-white lg:rounded-l-[3rem] shadow-2xl z-20">
        <div class="w-full max-w-md animate-fade-in">
            <div class="lg:hidden text-center mb-10">
                <img src="img/logo_sincrm4.png" class="w-16 mx-auto mb-4" alt="Logo">
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter italic">SINCRM <span class="text-blue-500">4</span></h1>
            </div>

            <div class="mb-10 text-center lg:text-left">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2 uppercase italic tracking-tighter">Bienvenido</h2>
                <p class="text-slate-400 font-medium italic text-sm">Introduce tus datos para acceder al panel.</p>
            </div>

            <div id="alert-container">
                <?php include('includes/alertas_v4.php'); ?>
            </div>

            <form id="loginForm" action="login_code.php" method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Email de Usuario</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-envelope text-sm"></i></span>
                        <input type="email" name="email" required autocomplete="username" placeholder="ejemplo@sincrm.com" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-8 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest italic">Contraseña</label>
                        <a href="forgot_password.php" class="text-[10px] font-black text-blue-500 hover:underline tracking-tighter">¿Olvidaste la clave?</a>
                    </div>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-lock text-sm"></i></span>
                        <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-[1.5rem] text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-8 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <div class="flex items-center gap-3 px-2">
                    <input type="checkbox" name="remember" id="remember" class="w-5 h-5 rounded-lg border-slate-200 text-blue-600 focus:ring-blue-500/20">
                    <label for="remember" class="text-xs font-bold text-slate-400 italic">Mantener sesión iniciada</label>
                </div>

                <button id="submitBtn" name="login_btn" type="submit" class="w-full py-5 px-6 bg-slate-900 text-white font-black uppercase text-xs tracking-[0.3em] rounded-[1.5rem] shadow-2xl hover:bg-blue-600 active:scale-95 transition-all flex items-center justify-center gap-4 group">
                    <span id="btnText">Entrar al Sistema</span>
                    <i id="btnIcon" class="fas fa-arrow-right-long text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    <div id="loader" class="hidden animate-spin rounded-full h-5 w-5 border-2 border-white/20 border-t-white"></div>
                </button>
            </form>

            <div class="mt-12 pt-8 border-t border-slate-100 text-center">
                <p class="text-sm font-medium text-slate-400 italic">
                    ¿Aún no tienes acceso? 
                    <a href="register.php" class="text-slate-800 font-black hover:text-blue-600 transition-colors ml-1 not-italic border-b-2 border-slate-100">Crea una cuenta</a>
                </p>
            </div>

            <div class="mt-12 flex flex-col items-center gap-6">
                <div class="flex items-center justify-center gap-6 opacity-30 hover:opacity-100 transition-all">
                    <a href="help/index.html" class="text-[10px] font-black uppercase tracking-widest text-slate-900 no-underline"><i class="fas fa-circle-question mr-1"></i>Ayuda</a>
                    <a href="soporte.php" class="text-[10px] font-black uppercase tracking-widest text-slate-900 no-underline">Soporte</a>
                    <a href="privacidad.php" class="text-[10px] font-black uppercase tracking-widest text-slate-900 no-underline">Privacidad</a>
                </div>
                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-tighter italic">
                    Made with <i class="fas fa-heart text-red-500/50 mx-1"></i> by Pedro Díaz
                </p>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Mostrar alertas de sesión mediante SweetAlert2 si existen
    <?php if(isset($_SESSION['correcto'])): ?>
        Swal.fire({
            icon: 'success',
            title: '<span class="swal2-title-v3">¡Éxito!</span>',
            html: '<?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?>',
            confirmButtonColor: '#0f172a',
            customClass: { popup: 'swal2-popup-v3' }
        });
    <?php endif; ?>

    <?php if(isset($_SESSION['estado'])): ?>
        Swal.fire({
            icon: 'error',
            title: '<span class="swal2-title-v3 text-red-500">Aviso</span>',
            html: '<?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?>',
            confirmButtonColor: '#0f172a',
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

        submitBtn.prop('disabled', true).addClass('bg-blue-600');
        btnText.text('Verificando...');
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
                                <p class="text-3xl font-black text-slate-800 tracking-tighter italic leading-none mb-2">${response.message}</p>
                                <p class="text-sm text-slate-400 font-medium italic">Sincronizando con tu club...</p>
                            </div>`,
                        timer: 2500,
                        showConfirmButton: false,
                        background: '#ffffff',
                        customClass: { popup: 'swal2-popup-v3' }
                    }).then(() => { window.location.href = response.redirect; });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="swal2-title-v3 text-red-500">Error de Acceso</span>',
                        html: response.message,
                        confirmButtonColor: '#0f172a',
                        confirmButtonText: 'REINTENTAR',
                        customClass: { popup: 'swal2-popup-v3' }
                    });
                    submitBtn.prop('disabled', false).removeClass('bg-blue-600');
                    btnText.text('Entrar al Sistema');
                    btnIcon.removeClass('hidden');
                    loader.addClass('hidden');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Servidor no responde.', customClass: { popup: 'swal2-popup-v3' } });
                submitBtn.prop('disabled', false).removeClass('bg-blue-600');
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
        text: 'Estamos procesando tu solicitud.',
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
                    confirmButtonColor: '#0f172a',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '<span class="swal2-title-v3 text-red-500">Error</span>',
                    html: response.message,
                    confirmButtonColor: '#0f172a',
                    customClass: { popup: 'swal2-popup-v3' }
                });
            }
        },
        error: function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Servidor no responde.', customClass: { popup: 'swal2-popup-v3' } });
        }
    });
}
</script>
</body>
</html>
