<?php
session_start();
$_SESSION['username'] = 'registrando';
$_SESSION['paginas_permitidas'] = array('register.php', 'login_code.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SINCRM 3 | Registro</title>
    
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
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
<main class="flex min-h-screen">
    
    <section class="hidden lg:flex lg:w-1/2 relative overflow-hidden items-center justify-center bg-slate-900">
        <!-- Imagen de fondo (Natación Sincronizada - Local) -->
        <img alt="Natación Artística" class="absolute inset-0 w-full h-full object-cover opacity-70" src="img/bg_sincro_register.jpg"/>
        
        <!-- Branding Flotante -->
        <div class="relative z-10 glass-overlay p-12 rounded-[2.5rem] max-w-lg mx-8 animate-fade-in text-center">
            <div class="flex flex-col gap-4 items-center">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl mb-4">
                    <img src="img/logo_sincrm3.png" class="w-10" alt="Logo">
                </div>
                <h1 class="text-4xl font-black text-white tracking-tighter italic uppercase">Únete a la Élite</h1>
                <p class="text-lg text-blue-50 font-light leading-relaxed">Forma parte de la comunidad SINCRM y digitaliza la gestión técnica de tu club con precisión profesional.</p>
            </div>
        </div>
        
        <!-- Badge inferior -->
        <div class="absolute bottom-10 left-10 z-10 flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <p class="text-[10px] font-black text-white/60 uppercase tracking-[0.3em]">Registro de nuevas entidades</p>
        </div>
    </section>

    <!-- PANEL DERECHO: FORMULARIO -->
    <section class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-12 bg-white lg:rounded-l-[3rem] shadow-2xl z-20 overflow-y-auto">
        <div class="w-full max-w-xl animate-fade-in py-10">
            
            <!-- Logo Mobile -->
            <div class="lg:hidden text-center mb-10">
                <img src="img/logo_sincrm3.png" class="w-16 mx-auto mb-4" alt="Logo">
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter italic">SINCRM <span class="text-blue-500">3</span></h1>
            </div>

            <div class="mb-10 text-center lg:text-left">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight mb-2">Crear Cuenta</h2>
                <p class="text-slate-400 font-medium italic text-sm">Solicita tu acceso rellenando el formulario técnico.</p>
            </div>

            <div id="alert-container">
                <?php
                if (isset($_SESSION['estado']) && $_SESSION['estado'] != '') {
                    echo '<div class="mb-6 p-4 rounded-2xl bg-red-50 text-red-600 text-xs font-bold border border-red-100 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i> '.$_SESSION['estado'].'</div>';
                    unset($_SESSION['estado']);
                }
                ?>
            </div>

            <form id="registerForm" action="login_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Nombre Completo -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre y Apellidos</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-user text-sm"></i></span>
                        <input type="text" name="username" required placeholder="Tu nombre oficial..." class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Correo Electrónico</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-envelope text-sm"></i></span>
                        <input type="email" name="email" required placeholder="ejemplo@correo.com" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Contraseña</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-lock text-sm"></i></span>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <!-- Password Repeat -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Repetir Clave</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-shield-halved text-sm"></i></span>
                        <input type="password" name="password_r" required placeholder="••••••••" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <!-- Teléfono -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Teléfono</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-phone text-sm"></i></span>
                        <input type="tel" name="telefono" required placeholder="600 000 000" class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <!-- Club -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Club / Entidad</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"><i class="fas fa-building text-sm"></i></span>
                        <input type="text" name="comentario" required placeholder="Nombre de tu club..." class="w-full pl-12 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-black text-slate-700 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-inner">
                    </div>
                </div>

                <!-- Submit -->
                <div class="md:col-span-2 pt-6">
                    <button id="submitBtn" name="register_btn" type="submit" class="w-full py-5 px-6 bg-slate-900 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-xl hover:bg-blue-600 active:scale-95 transition-all flex items-center justify-center gap-4 group">
                        <span id="btnText">Solicitar mi Cuenta</span>
                        <i id="btnIcon" class="fas fa-paper-plane text-[10px] group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                        <div id="loader" class="hidden animate-spin rounded-full h-5 w-5 border-2 border-white/20 border-t-white"></div>
                    </button>
                </div>
            </form>

            <div class="mt-12 pt-8 border-t border-slate-100 text-center">
                <p class="text-sm font-medium text-slate-400 italic">
                    ¿Ya tienes una cuenta activada? 
                    <a href="login.php" class="text-slate-800 font-black hover:text-blue-600 transition-colors ml-1 not-italic">Inicia sesión</a>
                </p>
                <div class="mt-8">
                    <p class="text-[10px] font-bold text-slate-300 uppercase tracking-tighter italic">
                        Made with <i class="fas fa-heart text-red-500/50 mx-1"></i> by Pedro Díaz
                    </p>
                </div>
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
                        title: '¡Solicitud Recibida!',
                        text: 'Tu cuenta está pendiente de aprobación por el administrador.',
                        confirmButtonColor: '#0f172a',
                        borderRadius: '2rem'
                    }).then(() => { window.location.href = 'login.php'; });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message, borderRadius: '2rem' });
                    submitBtn.prop('disabled', false).removeClass('bg-blue-600');
                    btnText.text('Solicitar mi Cuenta');
                    btnIcon.removeClass('hidden');
                    loader.addClass('hidden');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de red.', borderRadius: '2rem' });
                submitBtn.prop('disabled', false).removeClass('bg-blue-600');
                btnText.text('Solicitar mi Cuenta');
                btnIcon.removeClass('hidden');
                loader.addClass('hidden');
            }
        });
    });
});
</script>
</body>
</html>
