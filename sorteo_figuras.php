<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_comp = $_SESSION['id_competicion_activa'];

// Paleta de colores para cortes
$v3_colors = [
    1 => ["border" => "border-l-emerald-500", "badge" => "bg-emerald-500 text-white border-emerald-600", "bg" => "bg-emerald-50/20"],
    2 => ["border" => "border-l-blue-500", "badge" => "bg-blue-500 text-white border-blue-600", "bg" => "bg-blue-50/20"],
    3 => ["border" => "border-l-amber-500", "badge" => "bg-amber-500 text-white border-amber-600", "bg" => "bg-amber-50/20"],
    4 => ["border" => "border-l-red-500", "badge" => "bg-red-500 text-white border-red-600", "bg" => "bg-red-50/20"],
    5 => ["border" => "border-l-purple-500", "badge" => "bg-purple-500 text-white border-purple-600", "bg" => "bg-purple-50/20"],
    6 => ["border" => "border-l-pink-500", "badge" => "bg-pink-500 text-white border-pink-600", "bg" => "bg-pink-50/20"],
    7 => ["border" => "border-l-indigo-500", "badge" => "bg-indigo-500 text-white border-indigo-600", "bg" => "bg-indigo-50/20"]
];
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fa-solid fa-shapes text-lg"></i></span>
                    Sorteo de Figuras
                </h1>
                <p class="text-slate-500 font-medium font-lexend">Distribución técnica por cortes · Competición #<?php echo $id_comp; ?></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="toggleAddSorteoPanel()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-random text-xs"></i> Sortear
                </button>
                <button onclick="toggleAnularPanel()" class="px-5 py-3 bg-white border border-red-100 text-red-500 font-bold text-xs uppercase tracking-widest rounded-2xl hover:bg-red-50 transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-trash-can text-xs"></i> Anular
                </button>
                <a href="./informes/informe_figuras_orden_salida_cortes.php?titulo=Orden de salida" target="_blank" class="px-5 py-3 bg-emerald-600 text-white font-bold text-xs uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-500/10">
                    <i class="fas fa-file-pdf text-xs"></i> PDF Oficial
                </a>
            </div>
        </div>

        <!-- Alertas -->
        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-emerald-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['estado'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-red-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <i class="fas fa-circle-exclamation text-red-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Panel Nuevo Sorteo (Colapsable) -->
        <div id="addSorteoPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-dice text-blue-600"></i> Configurar Sorteo por Categoría</h2>
                <form action="sorteo_figuras_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-6 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Categoría a sortear</label>
                        <?php 
                        ob_start();
                        include('includes/categorias_competicion_select_option.php');
                        $select_html = ob_get_clean();
                        $select_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html);
                        $select_html = str_replace("name='categoria'", "name='id_categoria'", $select_html);
                        $select_html = str_replace("class='form-control'", "class='v3-select-fix'", $select_html);
                        echo $select_html;
                        ?>
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Método de Redondeo</label>
                        <select name="redondeo" class="v3-select-fix">
                            <option value="ceil">Hacia arriba (Ceil)</option>
                            <option value="floor">Hacia abajo (Floor)</option>
                        </select>
                    </div>
                    <div class="md:col-span-3 flex items-end">
                        <input type="hidden" name="save_btn" value="1">
                        <button type="submit" class="w-full py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">Ejecutar Sorteo</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel Anular (Colapsable) -->
        <div id="anularPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-red-50 rounded-[2.5rem] p-8 border border-red-100 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center text-red-500 shadow-sm border border-red-100"><i class="fas fa-trash-can text-2xl"></i></div>
                    <div><h3 class="text-lg font-black text-red-800 leading-tight">Limpiar sorteos de figuras</h3><p class="text-sm text-red-600 font-medium">Se eliminarán todos los órdenes de salida para la sesión de figuras.</p></div>
                </div>
                <form action="sorteo_figuras_code.php" method="POST" class="flex gap-4">
                    <button type="submit" name="delete_btn" class="px-10 py-3 bg-red-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl">Confirmar Borrado</button>
                </form>
            </div>
        </div>

        <!-- LISTADO DINÁMICO -->
        <div class="space-y-20">
            <?php
            $q_cat = "SELECT DISTINCT f.id_categoria, c.nombre 
                      FROM fases f, categorias c 
                      WHERE f.id_categoria = c.id AND f.id_competicion = '$id_comp'
                      ORDER BY c.edad_minima ASC";
            $res_cats = mysqli_query($connection, $q_cat);
            
            while ($row_cat = mysqli_fetch_assoc($res_cats)):
                $id_cat = $row_cat['id_categoria'];
                
                // Mapeo Dinámico de Fases
                $q_map = "SELECT id FROM fases WHERE id_categoria = '$id_cat' AND id_competicion = '$id_comp' ORDER BY orden ASC";
                $res_map = mysqli_query($connection, $q_map);
                $map_fases = [];
                $pos = 1;
                while($mf = mysqli_fetch_assoc($res_map)) {
                    $map_fases[$mf['id']] = $pos++;
                }
                $num_cortes = count($map_fases);
            ?>
            <div>
                <div class="flex items-center justify-between mb-8 border-b border-slate-200 pb-4">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase italic"><?php echo $row_cat['nombre'];?></h2>
                    <div class="flex flex-wrap gap-2">
                        <?php for($c=1; $c <= $num_cortes; $c++): 
                            $c_style = $v3_colors[$c] ?? $v3_colors[1];
                        ?>
                            <span class="px-3 py-1 <?php echo $c_style['badge']; ?> text-[9px] font-black rounded-lg uppercase">C<?php echo $c; ?></span>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 relative">
                    <?php
                    reset($map_fases);
                    $id_fase_base = key($map_fases);

                    if($id_fase_base):
                        $q_nad = "SELECT i.orden, i.id_nadadora, n.nombre, n.apellidos, n.año_nacimiento as año, cl.nombre_corto as club 
                                  FROM inscripciones_figuras i 
                                  JOIN nadadoras n ON i.id_nadadora = n.id 
                                  JOIN clubes cl ON n.club = cl.id 
                                  WHERE i.id_fase = '$id_fase_base' 
                                  ORDER BY i.orden ASC";
                        $res_nad = mysqli_query($connection, $q_nad);
                        
                        while ($row = mysqli_fetch_assoc($res_nad)):
                            $id_nadadora = $row['id_nadadora'];
                            
                            $q_chk = "SELECT id_fase FROM inscripciones_figuras 
                                      WHERE id_nadadora = '$id_nadadora' AND orden = 1 
                                      AND id_fase IN (".implode(',', array_keys($map_fases)).")";
                            $res_chk = mysqli_query($connection, $q_chk);
                            
                            $accent_color = "border-l-slate-200";
                            $badge_color = "hidden";
                            $bg_color = "bg-white";
                            $corte_label = "";
                            
                            if(mysqli_num_rows($res_chk) > 0) {
                                $id_fase_corte = mysqli_fetch_assoc($res_chk)['id_fase'];
                                $pos_corte = $map_fases[$id_fase_corte];
                                $c_style = $v3_colors[$pos_corte] ?? $v3_colors[1];
                                $accent_color = $c_style['border'];
                                $badge_color = $c_style['badge'];
                                $bg_color = $c_style['bg'];
                                $corte_label = "C".$pos_corte;
                            }
                    ?>
                    <div class="<?php echo $bg_color; ?> rounded-xl p-4 border border-slate-100 border-l-[6px] <?php echo $accent_color; ?> flex items-center gap-6 hover:shadow-md transition-all group">
                        <div class="w-12 text-center flex-shrink-0">
                            <?php 
                                $orden_display = $row['orden'];
                                $text_size = "text-xs";
                                if($row['orden'] <= -1 && $row['orden'] >= -9) {
                                    $orden_display = "PS";
                                    $text_size = "text-[10px]";
                                } else if($row['orden'] <= -10) {
                                    $orden_display = "E";
                                }
                            ?>
                            <span class="<?php echo $text_size; ?> font-black text-slate-800"><?php echo $orden_display; ?></span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-black text-slate-700 leading-tight"><?php echo $row['apellidos'].', '.$row['nombre']; ?></p>
                            <div class="flex items-center gap-4 mt-0.5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $row['club']; ?></span>
                                <span class="text-[9px] font-medium text-slate-300 italic"><?php echo $row['año']; ?></span>
                            </div>
                        </div>
                        <?php if($corte_label): ?>
                        <div class="flex-shrink-0 px-3 py-1 rounded-lg border <?php echo $badge_color; ?> shadow-sm">
                            <span class="text-[10px] font-black uppercase tracking-widest"><?php echo $corte_label; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<!-- Modal de Animación Mágica (v3.0 Refined) -->
<div id="animacionModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/90 backdrop-blur-xl transition-all duration-500 opacity-0">
    <div class="text-center">
        <div class="relative inline-block mb-12">
            <!-- Partículas rotando (CSS puro) -->
            <div class="absolute inset-0 animate-spin-slow">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-blue-400 rounded-full blur-sm"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-emerald-400 rounded-full blur-sm"></div>
            </div>
            <i id="iconoAnimacion" class="fa-solid fa-wand-magic-sparkles text-7xl text-white drop-shadow-[0_0_20px_rgba(255,255,255,0.5)]"></i>
        </div>
        <h2 id="animationText" class="text-3xl font-black text-white tracking-tighter mb-4">Invocando el azar...</h2>
        <p class="text-slate-400 font-medium uppercase tracking-[0.3em] text-xs">Sistema de Sorteo SINCRM v3.0</p>
    </div>
</div>

<script>
function toggleAddSorteoPanel() { document.getElementById('addSorteoPanel')?.classList.toggle('hidden'); }
function toggleAnularPanel() { document.getElementById('anularPanel')?.classList.toggle('hidden'); }

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="sorteo_figuras_code.php"]');
    const modal = document.getElementById('animacionModal');
    const text = document.getElementById('animationText');
    const icon = document.getElementById('iconoAnimacion');

    if(form && !form.closest('#anularPanel')) {
        form.addEventListener('submit', function(e) {
            const catSelect = document.querySelector('select[name="id_categoria"]');
            const isAll = catSelect.value === '0';
            
            if(!isAll) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Confirmar Sorteo?',
                    text: "Se generará un nuevo orden para esta categoría. Si ya existía uno, será sobreescrito.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Sí, Sortear',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        popup: 'rounded-[2rem]',
                        confirmButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest',
                        cancelButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        ejecutarAnimacion(form);
                    }
                });
            } else {
                e.preventDefault();
                ejecutarAnimacion(form);
            }
        });

        function ejecutarAnimacion(formElement) {
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('opacity-100'), 10);
            
            const frases = [
                { t: "Calculando trayectorias hidrodinámicas...", c: "text-cyan-400", i: "fa-water" },
                { t: "Mezclando el bombo de cristal...", c: "text-purple-400", i: "fa-gem" },
                { t: "Esparciendo polvos de aleatoriedad...", c: "text-pink-400", i: "fa-wand-sparkles" },
                { t: "Convenciendo a los jueces de que el azar existe...", c: "text-amber-400", i: "fa-gavel" },
                { t: "Ordenando el caos con estilo...", c: "text-blue-500", i: "fa-layer-group" },
                { t: "¡Sorteo finalizado con éxito!", c: "text-emerald-400", i: "fa-trophy" }
            ];

            let step = 0;
            const interval = setInterval(() => {
                if(step < frases.length) {
                    text.textContent = frases[step].t;
                    icon.className = `fa-solid ${frases[step].i} text-7xl ${frases[step].c} transition-all duration-500`;
                    step++;
                } else {
                    clearInterval(interval);
                    formElement.submit();
                }
            }, 800);
        }
    }
});
</script>

<style>
@keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.animate-spin-slow { animation: spin-slow 3s linear infinite; }
</style>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
