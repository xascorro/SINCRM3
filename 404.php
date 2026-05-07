<?php
require_once 'lib/my_functions.php';
$version = getVersion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>¡Splash! - 404 SINCRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/83d95dbe8d.js" crossorigin="anonymous"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { lexend: ["Lexend", "sans-serif"] }
                },
            },
        }
    </script>
    <style>
        @keyframes swim {
            0%, 100% { transform: translateY(0) rotate(12deg); }
            50% { transform: translateY(-20px) rotate(12deg); }
        }
        .animate-swim { animation: swim 3s ease-in-out infinite; }
    </style>
</head>
<body class="bg-[#f8fafc] font-lexend overflow-hidden">
    <main class="fixed inset-0 w-full h-full flex items-center justify-center bg-white p-6 overflow-hidden">
        <!-- Efectos de agua envolventes -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[60%] h-[60%] bg-blue-100 rounded-full blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[70%] h-[70%] bg-purple-50 rounded-full blur-[150px] animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="max-w-3xl w-full flex flex-col items-center justify-between relative z-10 min-h-screen py-16">
            <!-- Espaciador superior para centrar el contenido principal -->
            <div class="flex-1 flex flex-col items-center justify-center w-full">
                <!-- Ilustración 404 Gigante -->
                <div class="relative inline-block mb-8">
                    <div class="text-[15rem] md:text-[20rem] font-black text-slate-200 leading-none select-none tracking-tighter opacity-100">404</div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-32 h-32 md:w-40 md:h-40 flex items-center justify-center animate-swim">
                            <i class="fas fa-swimmer text-7xl md:text-8xl text-blue-600 drop-shadow-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="relative -mt-20 md:-mt-32">
                    <h1 class="text-4xl md:text-6xl font-black text-slate-800 uppercase italic tracking-tighter mb-6 leading-tight text-center">
                        ¡Splash!<br><span class="text-blue-600">Apnea Fallida</span>
                    </h1>
                    
                    <div class="max-w-xl mx-auto space-y-6 mb-12 text-center">
                        <p class="text-lg md:text-xl text-slate-500 font-medium leading-relaxed">
                            Has bajado tanto a por la página que te has quedado sin aire. Esta ruta no existe en nuestro reglamento técnico.
                        </p>
                        <div class="flex flex-col items-center gap-4">
                            <div class="px-6 py-2 bg-red-50 text-red-500 text-xs font-black rounded-full border border-red-100 uppercase tracking-[0.2em] italic shadow-sm">
                                Puntuación Final: 0.0 (Cero por figura no declarada)
                            </div>
                            <p class="text-xs text-slate-400 italic">"Los jueces te están mirando con cara de pocos amigos"</p>
                        </div>
                    </div>

                    <!-- Acciones Principales -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="index.php" class="w-full sm:w-auto px-12 py-5 bg-slate-900 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-2xl hover:bg-black hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3 group">
                            <i class="fas fa-house group-hover:-translate-y-1 transition-transform"></i>
                            Volver a la Superficie
                        </a>
                        <button onclick="history.back()" class="w-full sm:w-auto px-10 py-5 bg-white text-slate-400 font-black uppercase text-xs tracking-widest rounded-2xl border border-slate-200 hover:bg-slate-50 hover:text-slate-600 transition-all shadow-sm">
                            Reintentar Buceo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer decorativo (con más aire y contraste) -->
            <div class="mt-16 pb-8 opacity-80 w-full text-center">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.5em] whitespace-nowrap">SINCRM <?php echo $version; ?> · Underwater Error Engine · Stable</p>
            </div>
        </div>
    </main>

    <!-- Burbujas decorativas animadas -->
    <div class="fixed bottom-0 left-0 w-full h-32 pointer-events-none opacity-20">
        <div class="absolute bottom-10 left-10 w-4 h-4 bg-blue-200 rounded-full animate-bounce"></div>
        <div class="absolute bottom-20 left-1/4 w-2 h-2 bg-blue-300 rounded-full animate-bounce" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-5 right-1/3 w-6 h-6 bg-blue-100 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
    </div>
</body>
</html>