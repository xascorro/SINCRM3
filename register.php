<?php
@session_start();
require_once 'lib/my_functions.php';
$version = getVersion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SINCRM 4 | Registro de Usuario</title>
    
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
        <img alt="Natación Sincronizada" class="absolute inset-0 w-full h-full object-cover opacity-70" src="img/bg_sincro_register.jpg"/>
        <div class="relative z-10 glass-overlay p-12 rounded-[2.5rem] max-w-lg mx-8 animate-fade-in">
            <div class="flex flex-col gap-4">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl">
                    <img src="img/logo_sincrm4.png" class="w-10" alt="Logo">
                </div>
                <h1 class="text-5xl font-black text-white tracking-tighter italic">ÚNETE A <span class="text-blue-400">NOSOTROS</span></h1>
                <p class="text-xl text-blue-50 font-light leading-relaxed">Crea tu cuenta y empieza a gestionar tus competiciones con la tecnología más avanzada.</p>
            </div>
        </div>
        <div class="absolute bottom-10 left-10 z-10 flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
            <p class="text-[10px] font-black text-white/60 uppercase tracking-[0.3em]"><?php echo $version; ?> stable</p>
        </div>
    </section>

    <!-- PANEL DERECHO: FORMULARIO -->
    <section class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-12 bg-white lg:rounded-l-[3rem] shadow-2xl z-20 overflow-y-auto">
        <div class="w-full max-w-md animate-fade-in py-10">
            <div class="lg:hidden text-center mb-10">
                <img src="img/logo_sincrm4.png" class="w-16 mx-auto mb-4" alt="Logo">
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter italic">SINCRM <span class="text-blue-500">4</span></h1>
            </div>

            <div class="mb-8 text-center lg:text-left">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2 uppercase italic tracking-tighter">Nueva Cuenta</h2>
                <p class="text-slate-400 font-medium italic text-sm">Completa tus datos para solicitar acceso al sistema.</p>
            </div>

            <form id="registerForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Nombre Completo</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-user text-xs"></i></span>
                            <input type="text" name="username" required placeholder="Tu nombre" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-[1.2rem] text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Teléfono</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-phone text-xs"></i></span>
                            <input type="text" name="telefono" placeholder="600000000" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-[1.2rem] text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Email de Usuario</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-envelope text-xs"></i></span>
                        <input type="email" name="email" required placeholder="ejemplo@sincrm.com" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-[1.2rem] text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Contraseña</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-lock text-xs"></i></span>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-[1.2rem] text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Repetir</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-lock text-xs"></i></span>
                            <input type="password" name="password_r" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-[1.2rem] text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest italic">Comentario / Club / Cargo</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-4 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-comment text-xs"></i></span>
                        <textarea name="comentario" rows="3" placeholder="Indica tu club o cargo para facilitar la aprobación..." class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-[1.2rem] text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner"></textarea>
                    </div>
                </div>

                <button id="submitBtn" type="submit" class="w-full py-5 px-6 bg-slate-900 text-white font-black uppercase text-xs tracking-[0.3em] rounded-[1.5rem] shadow-2xl hover:bg-blue-600 active:scale-95 transition-all flex items-center justify-center gap-4 group mt-6">
                    <span id="btnText">Solicitar Registro</span>
                    <i id="btnIcon" class="fas fa-arrow-right-long text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    <div id="loader" class="hidden animate-spin rounded-full h-5 w-5 border-2 border-white/20 border-t-white"></div>
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-slate-100 text-center">
                <p class="text-sm font-medium text-slate-400 italic">
                    ¿Ya tienes una cuenta? 
                    <a href="login.php" class="text-slate-800 font-black hover:text-blue-600 transition-colors ml-1 not-italic border-b-2 border-slate-100">Inicia Sesión</a>
                </p>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#submitBtn');
        const btnText = $('#btnText');
        const btnIcon = $('#btnIcon');
        const loader = $('#loader');

        submitBtn.prop('disabled', true).addClass('bg-blue-600');
        btnText.text('Procesando...');
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
                        title: '<span class="swal2-title-v3">¡Registro Éxito!</span>',
                        html: `<div class="font-lexend"><p class="text-lg font-black text-slate-800 tracking-tighter italic leading-tight mb-4">Te hemos enviado un email de verificación.</p><p class="text-sm text-slate-400 font-medium">Por favor, confirma tu cuenta antes de que un administrador la apruebe.</p></div>`,
                        confirmButtonColor: '#0f172a',
                        customClass: { popup: 'swal2-popup-v3' }
                    }).then(() => { window.location.href = 'login.php'; });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="swal2-title-v3 text-red-500">Error en Registro</span>',
                        html: response.message,
                        confirmButtonColor: '#0f172a',
                        confirmButtonText: 'CORREGIR',
                        customClass: { popup: 'swal2-popup-v3' }
                    });
                    submitBtn.prop('disabled', false).removeClass('bg-blue-600');
                    btnText.text('Solicitar Registro');
                    btnIcon.removeClass('hidden');
                    loader.addClass('hidden');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Servidor no responde.', customClass: { popup: 'swal2-popup-v3' } });
                submitBtn.prop('disabled', false).removeClass('bg-blue-600');
                btnText.text('Solicitar Registro');
                btnIcon.removeClass('hidden');
                loader.addClass('hidden');
            }
        });
    });
});
</script>
</body>
</html>
