<?php
include('security.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SINCRM 3 - Full Tailwind Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    <script src="https://kit.fontawesome.com/83d95dbe8d.js" crossorigin="anonymous"></script>
    <style>
        body { font-family: 'Lexend', sans-serif; }
        .sidebar-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .submenu-closed { max-height: 0; overflow: hidden; opacity: 0; }
        .submenu-open { max-height: 500px; opacity: 1; margin-bottom: 10px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">

    <div class="flex min-h-screen">
        <!-- SIDEBAR PURA TAILWIND (SIN BOOTSTRAP) -->
        <aside id="sidebar" class="sidebar-transition w-72 bg-[#001629] text-white flex-shrink-0 flex flex-col shadow-2xl z-50">
            <!-- Brand -->
            <div class="p-8 flex items-center gap-4">
                <div class="w-10 h-10 bg-cyan-500 rounded-xl flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <img src="img/logo_sincrm3.png" class="w-7 brightness-0 invert" alt="Logo">
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black tracking-tighter leading-none">SINCRM <span class="text-cyan-400">3</span></span>
                    <span class="text-[9px] font-bold text-cyan-500/60 uppercase tracking-widest mt-1">Beta v3.0</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 space-y-2 mt-4">
                <a href="index.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl bg-cyan-500/10 text-cyan-400 font-bold border border-cyan-500/20 transition-all hover:scale-[1.02]">
                    <i class="fas fa-house-chimney w-5"></i> <span class="sidebar-text">Dashboard</span>
                </a>

                <div class="pt-6 pb-2 px-5 text-[10px] font-black uppercase tracking-widest text-slate-500 sidebar-text">Administración</div>
                
                <a href="mantenimiento.php" class="flex items-center gap-3 px-5 py-4 rounded-2xl hover:bg-white/5 transition-all text-slate-300 hover:text-white group">
                    <i class="fa-solid fa-screwdriver-wrench w-5 opacity-60 group-hover:opacity-100"></i> <span class="sidebar-text">Mantenimiento</span>
                </a>

                <!-- Dropdown: Gestión Usuarios -->
                <div class="relative">
                    <button onclick="toggleSubmenu('sub-usuarios')" class="w-full flex items-center justify-between px-5 py-4 rounded-2xl hover:bg-white/5 transition-all text-slate-300 hover:text-white group">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-shield w-5 opacity-60 group-hover:opacity-100"></i> <span class="sidebar-text">Gestión Usuarios</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-300 sidebar-text" id="arrow-sub-usuarios"></i>
                    </button>
                    <div id="sub-usuarios" class="submenu-closed sidebar-transition ml-4 space-y-1">
                        <a href="usuarios.php" class="flex items-center gap-3 px-10 py-2.5 text-sm text-slate-400 hover:text-cyan-400 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span> Usuarios
                        </a>
                        <a href="roles.php" class="flex items-center gap-3 px-10 py-2.5 text-sm text-slate-400 hover:text-cyan-400 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span> Roles
                        </a>
                    </div>
                </div>

                <div class="pt-6 pb-2 px-5 text-[10px] font-black uppercase tracking-widest text-slate-500 sidebar-text">Entidades y Atletas</div>

                <!-- Dropdown: Datos Maestros -->
                <div class="relative">
                    <button onclick="toggleSubmenu('sub-datos')" class="w-full flex items-center justify-between px-5 py-4 rounded-2xl hover:bg-white/5 transition-all text-slate-300 hover:text-white group text-left">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-database w-5 opacity-60 group-hover:opacity-100"></i> <span class="sidebar-text">Datos Maestros</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-300 sidebar-text" id="arrow-sub-datos"></i>
                    </button>
                    <div id="sub-datos" class="submenu-closed sidebar-transition ml-4 space-y-1">
                        <a href="competiciones.php" class="flex items-center gap-3 px-10 py-2.5 text-sm text-slate-400 hover:text-cyan-400 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span> Competiciones
                        </a>
                        <a href="clubes.php" class="flex items-center gap-3 px-10 py-2.5 text-sm text-slate-400 hover:text-cyan-400 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span> Clubs
                        </a>
                        <a href="nadadoras.php" class="flex items-center gap-3 px-10 py-2.5 text-sm text-slate-400 hover:text-cyan-400 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span> Nadadoras
                        </a>
                        <a href="jueces.php" class="flex items-center gap-3 px-10 py-2.5 text-sm text-slate-400 hover:text-cyan-400 transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span> Jueces
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Footer Sidebar -->
            <div class="p-6 mt-auto">
                <button onclick="toggleSidebar()" class="w-full flex items-center justify-center p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition-all border border-white/5 group">
                    <i class="fas fa-angles-left sidebar-transition" id="sidebar-toggle-icon"></i>
                    <span class="sidebar-text ml-3 text-sm font-bold opacity-60 group-hover:opacity-100">Contraer menú</span>
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 flex flex-col max-h-screen overflow-y-auto">
            <!-- Topbar Mockup -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-10 sticky top-0 z-40">
                <div class="flex items-center gap-6">
                    <button onclick="toggleSidebar()" class="lg:hidden p-3 rounded-xl hover:bg-slate-100 transition-colors">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-0.5">Sistema de Gestión</p>
                        <h2 class="font-bold text-slate-800 text-lg leading-tight">Informes y Documentación</h2>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-black text-[#001629] leading-none mb-1"><?php echo $_SESSION['username'];?></p>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-widest"><?php echo $_SESSION['rol'];?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white font-black shadow-lg shadow-cyan-500/20 border-2 border-white">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-10 max-w-6xl mx-auto w-full">
                <div class="bg-white rounded-[2.5rem] p-12 shadow-sm border border-slate-200 relative overflow-hidden">
                    <!-- Background Accent -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-cyan-500/5 rounded-full -mr-32 -mt-32 blur-3xl text-primary"></div>
                    
                    <div class="relative">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-cyan-50 text-cyan-600 text-xs font-black uppercase tracking-widest mb-6">Test Area</span>
                        <h1 class="text-5xl font-black text-[#001629] mb-6 tracking-tighter">Diagnóstico <span class="text-cyan-500">Sin Bootstrap</span></h1>
                        <p class="text-slate-500 text-lg max-w-2xl mb-12">Esta página utiliza <strong>únicamente Tailwind CSS</strong> para la Sidebar. Si los submenús aquí funcionan bien, significa que debemos migrar la Sidebar real a este sistema y decir adiós a los conflictos de Bootstrap.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group p-8 rounded-[2rem] bg-slate-50 border border-slate-200 hover:border-cyan-500 hover:bg-white hover:shadow-2xl hover:shadow-cyan-500/10 transition-all cursor-pointer">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-users-line text-2xl text-cyan-600"></i>
                                </div>
                                <h3 class="font-black text-xl mb-2 text-[#001629]">Inscripciones numéricas</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">Genera automáticamente el listado de nadadoras inscritas por club y categoría.</p>
                            </div>
                            <div class="group p-8 rounded-[2rem] bg-slate-50 border border-slate-200 hover:border-cyan-500 hover:bg-white hover:shadow-2xl hover:shadow-cyan-500/10 transition-all cursor-pointer">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-list-ol text-2xl text-cyan-600"></i>
                                </div>
                                <h3 class="font-black text-xl mb-2 text-[#001629]">Orden de salida</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">Crea el documento oficial de competición con el sorteo técnico aplicado.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const arrow = document.getElementById('arrow-' + id);
            
            // Cerrar otros submenús (acordeón)
            document.querySelectorAll('.submenu-open').forEach(el => {
                if(el.id !== id) {
                    el.classList.remove('submenu-open');
                    el.classList.add('submenu-closed');
                    const otherArrow = document.getElementById('arrow-' + el.id);
                    if(otherArrow) otherArrow.classList.remove('rotate-180');
                }
            });

            if (submenu.classList.contains('submenu-closed')) {
                submenu.classList.remove('submenu-closed');
                submenu.classList.add('submenu-open');
                arrow.classList.add('rotate-180');
            } else {
                submenu.classList.remove('submenu-open');
                submenu.classList.add('submenu-closed');
                arrow.classList.remove('rotate-180');
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const icon = document.getElementById('sidebar-toggle-icon');
            const texts = document.querySelectorAll('.sidebar-text');
            
            if (sidebar.classList.contains('w-72')) {
                sidebar.classList.remove('w-72');
                sidebar.classList.add('w-24');
                icon.classList.remove('fa-angles-left');
                icon.classList.add('fa-angles-right');
                texts.forEach(t => t.classList.add('hidden'));
                // Cerrar submenús abiertos al contraer
                document.querySelectorAll('.submenu-open').forEach(el => {
                    el.classList.remove('submenu-open');
                    el.classList.add('submenu-closed');
                });
            } else {
                sidebar.classList.add('w-72');
                sidebar.classList.remove('w-24');
                icon.classList.add('fa-angles-left');
                icon.classList.remove('fa-angles-right');
                texts.forEach(t => t.classList.remove('hidden'));
            }
        }
    </script>
</body>
</html>