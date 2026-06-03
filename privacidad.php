<?php
session_start();
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SINCRM - Política de Privacidad</title>
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Google Fonts: Lexend -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
        <span class="font-headline-xl text-primary tracking-tighter">SINCRM</span>
        <h1 class="text-2xl font-bold text-primary-container mt-2">Política de Privacidad</h1>
        <div class="w-24 h-1 oceanic-gradient mt-4 rounded-full"></div>
    </div>

    <!-- Content Card -->
    <div class="bg-surface-container-lowest p-8 md:p-12 rounded-xl shadow-xl border border-outline-variant/30 space-y-8">
        
        <section>
            <h2 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">account_balance</span>
                Responsable del Tratamiento
            </h2>
            <p class="text-on-surface-variant leading-relaxed">
                La <strong>Federación de Natación de la Región de Murcia (FNRM)</strong>, con domicilio en 
                <span class="bg-red-500 text-white px-1 px-2 rounded">DIRECCIÓN FÍSICA DE LA FEDERACIÓN</span>, 
                es la responsable del tratamiento de los datos personales recogidos a través de la plataforma SINCRM.
                <br><br>
                <strong>Email de contacto:</strong> info@fnrm.es
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">database</span>
                Datos que Recopilamos
            </h2>
            <ul class="list-disc pl-6 space-y-2 text-on-surface-variant">
                <li><strong>Usuarios (Managers/Jueces):</strong> Nombre, apellidos, correo electrónico, teléfono y vinculación con club o federación.</li>
                <li><strong>Participantes (Nadadoras):</strong> Nombre, apellidos, año de nacimiento y club deportivo.</li>
                <li><strong>Datos Técnicos:</strong> Dirección IP, registros de acceso (logs) y cookies técnicas de sesión.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">assignment_turned_in</span>
                Finalidad del Tratamiento
            </h2>
            <p class="text-on-surface-variant leading-relaxed">
                Los datos se tratan exclusivamente para:
            </p>
            <ul class="list-disc pl-6 mt-2 space-y-2 text-on-surface-variant">
                <li>Gestionar las inscripciones en competiciones oficiales y escolares.</li>
                <li>Realizar los sorteos de orden de salida y paneles de jueces.</li>
                <li>Calcular puntuaciones y generar resultados deportivos.</li>
                <li>Garantizar la seguridad y auditoría de la plataforma mediante el registro de actividad.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">security</span>
                Protección de Menores
            </h2>
            <p class="text-on-surface-variant leading-relaxed">
                SINCRM gestiona datos de menores de edad (nadadoras). La FNRM asume que los clubes que introducen estos datos cuentan con la 
                <strong>autorización expresa</strong> de los padres o tutores legales para el tratamiento de los datos en el ámbito deportivo y para la publicación de sus nombres en los resultados públicos de las competiciones.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary">policy</span>
                Tus Derechos
            </h2>
            <p class="text-on-surface-variant leading-relaxed">
                Puedes ejercer tus derechos de acceso, rectificación, supresión y portabilidad de tus datos, o la limitación u oposición a su tratamiento, enviando un correo electrónico a <strong>info@fnrm.es</strong> adjuntando copia de un documento identificativo oficial.
            </p>
        </section>

        <section class="bg-surface-container-low p-6 rounded-lg border-l-4 border-secondary">
            <h2 class="text-lg font-bold text-primary mb-2">Nota sobre Cookies</h2>
            <p class="text-sm text-on-surface-variant">
                Esta plataforma utiliza únicamente cookies técnicas necesarias para mantener la sesión del usuario. No se utilizan cookies de seguimiento ni de terceros para fines publicitarios.
            </p>
        </section>

        <!-- Botón de retorno -->
        <div class="pt-8 border-t border-outline-variant/30 flex justify-center">
            <a href="login.php" class="oceanic-gradient text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:opacity-90 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">arrow_back</span>
                Volver al Acceso
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-12 flex flex-col items-center gap-4 text-center text-outline-variant text-sm">
        <div class="flex gap-6">
            <a class="font-label-sm text-outline hover:text-secondary transition-colors" href="soporte.php">Soporte</a>
            <a class="font-label-sm text-outline hover:text-secondary transition-colors" href="privacidad.php">Privacidad</a>
        </div>
        <p class="flex items-center justify-center gap-1">
            Made with <span class="material-symbols-outlined fill-icon text-[14px] text-error">favorite</span> by Pedro Díaz
        </p>
        <p class="mt-2 text-xs">© <?php echo date('Y'); ?> Federación de Natación de la Región de Murcia</p>
    </footer>
</main>
</body>
</html>
