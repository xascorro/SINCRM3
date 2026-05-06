<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Obtener estadísticas reales para la competición activa
$id_competicion = $_SESSION['id_competicion_usuario'] ?? 0;
$total_nadadoras = 0;
$total_rutinas = 0;
$fases_puntuadas = 0;
$total_fases = 0;
$porcentaje_fases = 0;

if ($id_competicion > 0) {
    // 1. Contar nadadoras únicas en esta competición
    $q1 = "SELECT COUNT(DISTINCT id_nadadora) as total FROM rutinas_participantes rp 
           JOIN rutinas r ON rp.id_rutina = r.id 
           WHERE r.id_competicion = '$id_competicion'";
    $res1 = mysqli_query($connection, $q1);
    $total_nadadoras = mysqli_fetch_assoc($res1)['total'] ?? 0;

    // 2. Contar total de rutinas
    $q2 = "SELECT COUNT(*) as total FROM rutinas WHERE id_competicion = '$id_competicion'";
    $res2 = mysqli_query($connection, $q2);
    $total_rutinas = mysqli_fetch_assoc($res2)['total'] ?? 0;

    // 3. Contar fases totales configuradas
    $q_total = "SELECT COUNT(*) as total FROM fases WHERE id_competicion = '$id_competicion'";
    $res_total = mysqli_query($connection, $q_total);
    $total_fases = mysqli_fetch_assoc($res_total)['total'] ?? 0;

    // 4. Contar fases con al menos una puntuación registrada
    $q3 = "SELECT COUNT(DISTINCT id_fase) as total FROM puntuaciones_jueces pj 
           JOIN rutinas r ON pj.id_rutina = r.id 
           WHERE r.id_competicion = '$id_competicion'";
    $res3 = mysqli_query($connection, $q3);
    $fases_puntuadas = mysqli_fetch_assoc($res3)['total'] ?? 0;

    $porcentaje_fases = ($total_fases > 0) ? round(($fases_puntuadas / $total_fases) * 100) : 0;
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc]">
    
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-file-pdf text-lg"></i></span>
                    Informes
                </h1>
                <p class="text-slate-500 font-medium">Gestión de resultados y documentación oficial.</p>
            </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            
            <!-- Inscripciones -->
            <a href="informes/informe_preinscripciones.php?titulo=Nadadoras%20inscritas" target="_blank" class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-blue-600 transition-all hover:shadow-xl hover:-translate-y-1 group">
                <div class="flex justify-between items-center mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-users-line text-xl"></i>
                    </div>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest bg-slate-50 px-3 py-1 rounded-full border border-slate-100">Participación</span>
                </div>
                <h3 class="text-3xl font-black text-slate-800 mb-1 leading-none"><?php echo $total_nadadoras; ?></h3>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tight mb-6">Atletas Registradas</p>
                <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-blue-600 text-[10px] font-black uppercase tracking-widest transition-colors group-hover:text-blue-700">
                    <span>Descargar Listado</span>
                    <i class="fas fa-arrow-right animate-pulse"></i>
                </div>
            </a>

            <!-- Rutinas -->
            <a href="./informes/inscripciones_numericas_rutinas.php?titulo=Orden%20de%20salida" target="_blank" class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-purple-500 transition-all hover:shadow-xl hover:-translate-y-1 group">
                <div class="flex justify-between items-center mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-list-ol text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest bg-slate-50 px-3 py-1 rounded-full border border-slate-100">Competición</span>
                </div>
                <h3 class="text-3xl font-black text-slate-800 mb-1 leading-none"><?php echo $total_rutinas; ?></h3>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tight mb-6">Rutinas en Sorteo</p>
                <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-purple-600 text-[10px] font-black uppercase tracking-widest transition-colors group-hover:text-purple-700">
                    <span>Ver Orden Salida</span>
                    <i class="fas fa-arrow-right animate-pulse"></i>
                </div>
            </a>

            <!-- Fases (Con Gráfico Circular) -->
            <a href="./informes/informe_figuras_resultados_categorias.php?titulo=Resultados" target="_blank" class="bg-white p-7 rounded-[2rem] shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 transition-all hover:shadow-xl hover:-translate-y-1 group relative overflow-hidden">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm mb-6">
                            <i class="fas fa-medal text-xl"></i>
                        </div>
                        <h3 class="text-3xl font-black text-slate-800 mb-1 leading-none">
                            <?php echo $fases_puntuadas; ?> <span class="text-lg text-slate-300 font-bold italic">/ <?php echo $total_fases; ?></span>
                        </h3>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tight">Fases Puntuadas</p>
                    </div>
                    
                    <div class="relative w-20 h-20 shrink-0">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-100" />
                            <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="8" fill="transparent" 
                                    stroke-dasharray="213.6" 
                                    stroke-dashoffset="<?php echo 213.6 - (213.6 * $porcentaje_fases / 100); ?>" 
                                    class="text-emerald-500 transition-all duration-1000 ease-out" 
                                    stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-[11px] font-black text-emerald-600">
                            <?php echo $porcentaje_fases; ?>%
                        </div>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-emerald-600 text-[10px] font-black uppercase tracking-widest transition-colors group-hover:text-emerald-700">
                    <span>Ver Resultados</span>
                    <i class="fas fa-arrow-right animate-pulse"></i>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
            <!-- COLUMNA IZQUIERDA: RUTINAS -->
            <div class="lg:col-span-7 space-y-8">
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                        <i class="fa-solid fa-water text-blue-600"></i> Documentación Rutinas
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="informes/inscripciones_numericas_rutinas.php?titulo=Rutinas" target="_blank" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-blue-200 transition-all flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-sm transition-colors"><i class="fas fa-clipboard-list text-lg"></i></div>
                            <span class="text-sm font-bold text-slate-700">Inscripciones Numéricas</span>
                        </a>
                        <a href="informes/informe_preinscripciones.php?titulo=Nadadoras%20inscritas" target="_blank" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-blue-200 transition-all flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-sm transition-colors"><i class="fas fa-person-swimming text-lg"></i></div>
                            <span class="text-sm font-bold text-slate-700">Nadadoras Inscritas</span>
                        </a>
                        <a href="./informes/inscripciones_numericas_rutinas.php?titulo=Orden%20de%20salida" target="_blank" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-blue-200 transition-all flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-sm transition-colors"><i class="fas fa-sort-amount-down text-lg"></i></div>
                            <span class="text-sm font-bold text-slate-700">Orden de Salida</span>
                        </a>
                    </div>

                    <div class="mt-10 pt-8 border-t border-slate-100">
                        <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-6 px-1">Hojas de Puntuación PDF</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <a href="informes/hojas_puntuacion_rutinas.php" target="_blank" class="flex flex-col items-center gap-3 p-4 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                                <i class="fas fa-file-pdf text-red-500 text-2xl group-hover:scale-110 transition-transform"></i>
                                <span class="text-[9px] font-black uppercase text-slate-500">Ejecución</span>
                            </a>
                            <a href="informes/hojas_puntuacion_rutinas_sincronizacion.php" target="_blank" class="flex flex-col items-center gap-3 p-4 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                                <i class="fas fa-file-pdf text-red-500 text-2xl group-hover:scale-110 transition-transform"></i>
                                <span class="text-[9px] font-black uppercase text-slate-500">Sincro</span>
                            </a>
                            <a href="informes/hojas_puntuacion_rutinas_ia.php" target="_blank" class="flex flex-col items-center gap-3 p-4 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                                <i class="fas fa-file-pdf text-red-500 text-2xl group-hover:scale-110 transition-transform"></i>
                                <span class="text-[9px] font-black uppercase text-slate-500">Artística</span>
                            </a>
                            <a href="./informes/informe_puntuaciones.php?titulo=Árbitros&hoja_tecnica=si&id_fase=0" target="_blank" class="flex flex-col items-center gap-3 p-4 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                                <i class="fas fa-gavel text-slate-400 text-2xl group-hover:scale-110 transition-transform"></i>
                                <span class="text-[9px] font-black uppercase text-slate-500">Árbitros</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: FIGURAS -->
            <div class="lg:col-span-5 space-y-8">
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-t-[6px] border-t-emerald-500 h-full">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                        <i class="fa-solid fa-shapes text-emerald-500"></i> Sección Figuras
                    </h2>
                    
                    <div class="space-y-3">
                        <a href="informes/informe_figuras.php?titulo=Inscripciones" target="_blank" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-sm"><i class="fas fa-signature"></i></div>
                                <span class="text-sm font-bold text-slate-700">Inscripciones Figuras</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="informes/informe_figuras.php?titulo=orden" target="_blank" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-sm"><i class="fas fa-list-ol"></i></div>
                                <span class="text-sm font-bold text-slate-700">Orden de Actuación</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="./informes/informe_figuras_resultados_categorias.php?titulo=Resultados por categorias&hoja_tecnica=si" target="_blank" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-sm"><i class="fas fa-trophy text-lg"></i></div>
                                <span class="text-sm font-bold text-slate-700">Resultados Finales</span>
                            </div>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

</div> <!-- Cierre Wrapper -->

<?php include('includes/scripts.php'); ?>
<?php include('includes/footer.php'); ?>
