<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_fase = isset($_GET['id_fase']) ? (int)$_GET['id_fase'] : (isset($_POST['id_fase']) ? (int)$_POST['id_fase'] : 0);
$id_comp = $_SESSION['id_competicion_usuario'] ?? 0;

if ($id_comp == 0 && $_SESSION['id_rol'] == 1 && isset($_SESSION['id_competicion_activa'])) {
    $id_comp = $_SESSION['id_competicion_activa'];
}

// Obtener info de la fase
$query_fase = "SELECT c.nombre as categoria, m.nombre as modalidad 
               FROM fases f 
               JOIN categorias c ON f.id_categoria = c.id 
               JOIN modalidades m ON f.id_modalidad = m.id 
               WHERE f.id = $id_fase";
$res_fase = mysqli_query($connection, $query_fase);
$info_fase = mysqli_fetch_assoc($res_fase);
$nombre_modalidad = $info_fase['modalidad'] ?? 'Desconocida';
$nombre_categoria = $info_fase['categoria'] ?? 'Desconocida';
?>

<style>
    /* Estilos Teleprompter / Live Guide */
    .routine-row { transition: all 0.3s ease; }
    
    /* Rutina Puntuada: Verde suave */
    .row-scored { 
        background-color: rgba(16, 185, 129, 0.08) !important; 
    }
    .score-badge-live {
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        font-weight: 900;
        font-family: 'JetBrains Mono', monospace;
        transition: all 0.5s ease;
    }
    .score-puntuada {
        background-color: #059669;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }
    .score-pendiente {
        background-color: #f1f5f9;
        color: #94a3b8;
    }

    /* Rutina Activa (Donde estoy ahora): Resaltado fuerte */
    .row-active {
        background-color: #eff6ff !important;
        border-left: 6px solid #3b82f6 !important;
        transform: scale(1.01);
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1) !important;
        z-index: 10;
        position: relative;
    }
    .row-active .orden-cell { color: #3b82f6 !important; transform: scale(1.2); }

    /* Animación de actualización */
    @keyframes pulse-new-score {
        0% { transform: scale(1); }
        50% { transform: scale(1.15); background-color: #3b82f6; }
        100% { transform: scale(1); }
    }
    .score-updated { animation: pulse-new-score 1s ease; }

    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 20;
        background: rgba(248, 250, 252, 0.8);
        backdrop-filter: blur(12px);
    }
</style>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header Dinámico -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg shadow-emerald-500/20 animate-pulse">● LIVE GUIDE</span>
                </div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    Guion de Competición
                </h1>
                <p class="text-slate-500 font-medium text-lg uppercase tracking-tight italic"><?php echo $nombre_modalidad." ".$nombre_categoria; ?></p>
            </div>
            <div id="connection-status" class="text-[10px] font-black uppercase text-slate-400 flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
                <i class="fas fa-sync fa-spin text-blue-500"></i> Sincronizando...
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 mb-10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="liveTable">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-slate-50/50">
                            <th class="px-6 py-5 w-20 text-center">Orden</th>
                            <th class="px-6 py-5">Rutina / Club / Nadadoras</th>
                            <th class="px-6 py-5 text-center w-40">Nota Final</th>
                            <th class="px-6 py-5 text-right w-40">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $query = "SELECT r.id, r.orden, r.nombre as nombre_rutina, r.id_club, r.nota_final, r.baja, c.nombre_corto as nombre_club, m.nombre as nombre_modalidad, cat.nombre as nombre_categoria, r.id_fase, f.obsoleto 
                                  FROM rutinas r
                                  JOIN fases f ON r.id_fase = f.id
                                  JOIN modalidades m ON f.id_modalidad = m.id
                                  JOIN categorias cat ON f.id_categoria = cat.id
                                  JOIN clubes c ON r.id_club = c.id
                                  WHERE f.id = $id_fase AND f.id_competicion = $id_comp
                                  ORDER BY r.orden, r.id";

                        $query_run = mysqli_query($connection, $query);
                        while ($row = mysqli_fetch_assoc($query_run)) {
                            $q_nombres = "SELECT GROUP_CONCAT(n.nombre SEPARATOR ', ') as nombres 
                                          FROM rutinas_participantes rp
                                          JOIN nadadoras n ON n.id = rp.id_nadadora 
                                          WHERE rp.reserva = 'no' AND rp.id_rutina = " . $row['id'];
                            $nombres = mysqli_fetch_assoc(mysqli_query($connection, $q_nombres))['nombres'] ?? '';
                            
                            $isBaja = ($row['baja'] == 'si');
                            $isPuntuada = ($row['nota_final'] > 0);
                            $isPS = ($row['orden'] < 0 && $row['orden'] > -10);
                            $isExh = ($row['orden'] <= -10);
                            $isOldSystem = ($row['obsoleto'] == 'si');
                            
                            $rowClass = "routine-row ";
                            if($isBaja) $rowClass .= "bg-red-50/30 opacity-50 ";
                            elseif($isPuntuada) $rowClass .= "row-scored ";
                            
                            $orden_display = $row['orden'];
                            if ($isExh) $orden_display = 'EX';
                            elseif ($isPS) $orden_display = 'PS';
                        ?>
                        <tr class="<?php echo $rowClass; ?>" id="row-<?php echo $row['id']; ?>">
                            <td class="px-6 py-6 text-center orden-cell transition-all">
                                <span class="text-2xl font-black <?php echo $isBaja ? 'text-red-300' : 'text-slate-300'; ?>"><?php echo $orden_display; ?></span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="text-lg font-black text-slate-800 tracking-tighter uppercase"><?php echo $row['nombre_club']; ?></span>
                                    <?php if($row['nombre_rutina']): ?>
                                        <span class="text-slate-400 font-medium italic">"<?php echo $row['nombre_rutina']; ?>"</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-tight flex items-center gap-2">
                                    <i class="fas fa-users opacity-50"></i> <?php echo $nombres; ?>
                                    <?php if($isBaja): ?> <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 rounded text-[9px]">BAJA</span> <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <div id="score-<?php echo $row['id']; ?>" class="score-badge-live <?php echo $isPuntuada ? 'score-puntuada' : 'score-pendiente'; ?>">
                                    <?php echo $isPuntuada ? number_format($row['nota_final'], 4) : 'Pte.'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <?php $action_page = $isOldSystem ? 'puntuaciones_rutina_obsoleta.php' : 'puntuaciones_rutina.php'; ?>
                                <form target="_blank" action="<?php echo $action_page; ?>" method="POST" onsubmit="setActive(<?php echo $row['id']; ?>)">
                                    <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                    <input type="hidden" name="id_club" value="<?php echo $row['id_club']; ?>">
                                    <button type="submit" name="edit_btn" <?php echo $isBaja ? 'disabled' : ''; ?> class="w-12 h-12 rounded-2xl bg-white border-2 border-slate-100 text-slate-400 hover:bg-blue-600 hover:text-white hover:border-blue-600 shadow-sm transition-all flex items-center justify-center ml-auto group-hover:scale-110">
                                        <i class="fas fa-edit text-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include('includes/scripts.php'); ?>
<script>
    function setActive(id) {
        document.querySelectorAll('.routine-row').forEach(r => r.classList.remove('row-active'));
        const activeRow = document.getElementById('row-' + id);
        if(activeRow) {
            activeRow.classList.add('row-active');
            localStorage.setItem('active_routine_fase_<?php echo $id_fase; ?>', id);
        }
    }

    async function checkUpdates() {
        const statusEl = document.getElementById('connection-status');
        try {
            const response = await fetch('api_scores_fase.php?id_fase=<?php echo $id_fase; ?>');
            const data = await response.json();
            
            if (data.scores) {
                data.scores.forEach(s => {
                    const scoreDiv = document.getElementById('score-' + s.id);
                    const row = document.getElementById('row-' + s.id);
                    
                    if (scoreDiv && s.nota_final > 0) {
                        const newScore = s.nota_final.toFixed(4);
                        const currentText = scoreDiv.innerText.trim();
                        
                        if (currentText === 'Pte.' || parseFloat(currentText) !== s.nota_final) {
                            scoreDiv.innerText = newScore;
                            scoreDiv.className = 'score-badge-live score-puntuada score-updated';
                            if(!row.classList.contains('opacity-50')) {
                                row.classList.add('row-scored');
                            }
                            // Quitar clase animación después de un rato
                            setTimeout(() => scoreDiv.classList.remove('score-updated'), 2000);
                        }
                    }
                });
                statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-500"></i> Conectado';
            }
        } catch (err) {
            console.error('Error Sync:', err);
            statusEl.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500"></i> Error de Red';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const lastActive = localStorage.getItem('active_routine_fase_<?php echo $id_fase; ?>');
        if(lastActive) {
            const row = document.getElementById('row-' + lastActive);
            if(row) {
                row.classList.add('row-active');
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        setInterval(checkUpdates, 4000);
    });
</script>
<?php include('includes/footer.php'); ?>