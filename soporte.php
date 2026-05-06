<?php
session_start();
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SINCRM3 - Soporte Técnico</title>
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts: Lexend -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-surface": "#091d2b",
                        "surface-container-lowest": "#ffffff",
                        "primary-container": "#002b49",
                        "inverse-surface": "#203240",
                        "on-secondary-container": "#006f66",
                        "outline": "#73777e",
                        "outline-variant": "#c3c7ce",
                        "surface-bright": "#f6f9ff",
                        "surface-container-high": "#d7ebfd",
                        "secondary-container": "#5ef6e6",
                        "on-tertiary": "#ffffff",
                        "primary": "#001629",
                        "on-primary-fixed-variant": "#274969",
                        "on-tertiary-fixed": "#171c1f",
                        "secondary-fixed": "#61f9e9",
                        "error-container": "#ffdad6",
                        "tertiary": "#111618",
                        "surface": "#f6f9ff",
                        "surface-container": "#e0f0ff",
                        "on-error-container": "#93000a",
                        "on-primary": "#ffffff",
                        "tertiary-fixed": "#dfe3e6",
                        "tertiary-container": "#252a2c",
                        "secondary-fixed-dim": "#3adccc",
                        "surface-variant": "#d1e5f7",
                        "on-error": "#ffffff",
                        "on-primary-container": "#7293b6",
                        "error": "#ba1a1a",
                        "surface-dim": "#c9dcef",
                        "on-tertiary-container": "#8c9194",
                        "on-background": "#091d2b",
                        "surface-container-low": "#ebf5ff",
                        "on-secondary": "#ffffff",
                        "on-primary-fixed": "#001d34",
                        "background": "#f6f9ff",
                        "tertiary-fixed-dim": "#c3c7ca",
                        "surface-container-highest": "#d1e5f7",
                        "on-tertiary-fixed-variant": "#43474a",
                        "surface-tint": "#406182",
                        "secondary": "#006a62",
                        "inverse-primary": "#a8caef",
                        "primary-fixed": "#cfe5ff",
                        "on-surface-variant": "#42474d",
                        "on-secondary-fixed-variant": "#005049",
                        "primary-fixed-dim": "#a8caef",
                        "on-secondary-fixed": "#00201d",
                        "inverse-on-surface": "#e6f2ff"
                    },
                    "fontFamily": {
                        "headline-md": ["Lexend"],
                        "body-md": ["Lexend"]
                    }
                },
            },
        }
    </script>
    <style>
        .oceanic-gradient {
            background: linear-gradient(135deg, #5ef6e6 0%, #006a62 100%);
        }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
        .fill-icon {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24 !important;
        }
    </style>
</head>
<body class="bg-background font-body-md text-on-background min-h-screen p-container-margin">
<main class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col items-center mb-12 text-center">
        <span class="font-headline-xl text-primary tracking-tighter">SINCRM3</span>
        <h1 class="text-2xl font-bold text-primary-container mt-2">Soporte Técnico</h1>
        <p class="text-on-surface-variant mt-2 max-w-md">¿Tienes alguna duda o incidencia? Estamos aquí para ayudarte.</p>
        <div class="w-24 h-1 oceanic-gradient mt-4 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Contact Info -->
        <div class="space-y-6">
            <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/30 shadow-sm">
                <h3 class="font-bold text-primary flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-secondary">contact_support</span>
                    Ayuda Directa
                </h3>
                <p class="text-sm text-on-surface-variant leading-relaxed">
                    Si eres administrador de un club y tienes problemas con las inscripciones, revisa primero los manuales internos.
                </p>
            </div>

            <div class="bg-primary-container p-6 rounded-xl text-white shadow-lg">
                <h3 class="font-bold flex items-center gap-2 mb-4 text-secondary-container">
                    <span class="material-symbols-outlined">mail</span>
                    Federación
                </h3>
                <p class="text-sm opacity-90 mb-2">FNRM - Natación Artística</p>
                <a href="mailto:info@fnrm.es" class="text-lg font-bold hover:underline">info@fnrm.es</a>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="md:col-span-2 bg-surface-container-lowest p-8 md:p-10 rounded-xl shadow-xl border border-outline-variant/30">
            <h2 class="text-xl font-bold text-primary mb-6">Enviar Mensaje</h2>
            
            <form id="supportForm" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="font-label-md text-on-surface" for="name">Nombre</label>
                        <input class="w-full px-4 py-3 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-secondary-container transition-all font-body-md" id="name" name="name" placeholder="Tu nombre" type="text" required/>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-md text-on-surface" for="email">Email</label>
                        <input class="w-full px-4 py-3 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-secondary-container transition-all font-body-md" id="email" name="email" placeholder="tu@email.com" type="email" required/>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="font-label-md text-on-surface" for="subject">Asunto</label>
                    <input class="w-full px-4 py-3 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-secondary-container transition-all font-body-md" id="subject" name="subject" placeholder="¿En qué podemos ayudarte?" type="text" required/>
                </div>

                <div class="space-y-2">
                    <label class="font-label-md text-on-surface" for="message">Mensaje</label>
                    <textarea class="w-full px-4 py-3 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-secondary-container transition-all font-body-md min-h-[150px]" id="message" name="message" placeholder="Describe tu incidencia..." required></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" id="submitBtn" class="w-full py-4 oceanic-gradient text-white font-bold rounded-lg shadow-lg hover:opacity-90 transition-all flex items-center justify-center gap-2">
                        <span>Enviar Consulta</span>
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Botón de retorno -->
    <div class="mt-12 flex justify-center">
        <a href="login.php" class="text-secondary font-bold flex items-center gap-2 hover:underline">
            <span class="material-symbols-outlined">arrow_back</span>
            Volver al Inicio
        </a>
    </div>

    <!-- Footer -->
    <footer class="mt-16 text-center text-outline-variant text-sm border-t border-outline-variant/20 pt-8">
        <p class="flex items-center justify-center gap-1">
            Made with <span class="material-symbols-outlined fill-icon text-[14px] text-error">favorite</span> by Pedro Díaz
        </p>
        <p class="mt-2 text-xs">© <?php echo date('Y'); ?> Federación de Natación de la Región de Murcia</p>
    </footer>
</main>

<script>
document.getElementById('supportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitBtn');
    const originalContent = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> <span>Enviando...</span>';
    
    const formData = new FormData(this);
    
    fetch('soporte_code.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Mensaje Enviado!',
                text: data.message,
                confirmButtonColor: '#006a62'
            });
            document.getElementById('supportForm').reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#ba1a1a'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error de Conexión',
            text: 'No se pudo contactar con el servidor. Inténtalo de nuevo más tarde.',
            confirmButtonColor: '#ba1a1a'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
});
</script>
</body>
</html>
