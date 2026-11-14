<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

include('security.php');
include_once('./lib/my_functions.php');

@session_start();

$is_ajax = !empty($_GET['ajax']) || !empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

$id_competicion = isset($_GET['id_competicion']) ? intval($_GET['id_competicion']) : (isset($_POST['id_competicion']) ? intval($_POST['id_competicion']) : (int)($_SESSION['id_competicion_activa'] ?? 0));

if ($id_competicion <= 0) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Error: Competición no válida o no especificada.']);
        exit;
    }
    die("Error: Competición no especificada.");
}

// Iniciar buffer para suprimir ecos en el flujo
ob_start();

// 1. Limpiar resultados previos de esta competición
$query = "DELETE FROM resultados_figuras_categorias WHERE id_competicion='".$id_competicion."'";
mysqli_query($connection, $query);

$posicion = 1;

// 2. Obtener categorías presentes en las fases de la competición
$query = "SELECT * FROM fases WHERE id_competicion='$id_competicion' GROUP BY id_categoria ORDER BY orden ASC";
$fases = mysqli_query($connection, $query);

while ($fase_figuras = mysqli_fetch_array($fases)) {
    $cat_id = $fase_figuras['id_categoria'];

    // Cálculo de GD Acumulado (Soporte para Figuras y TRE)
    if ($fase_figuras['elementos_coach_card'] > 0) {
        $q_sample = "SELECT id FROM inscripciones_figuras WHERE id_fase IN (SELECT id FROM fases WHERE id_categoria='$cat_id' AND id_competicion='$id_competicion') LIMIT 1";
        $id_sample = mysqli_result(mysqli_query($connection, $q_sample), 0);
        $gd_acumulado = mysqli_result(mysqli_query($connection, "SELECT sum(valor) FROM hibridos_rutina WHERE id_rutina = '$id_sample' AND tipo = 'dd'"), 0);
    } else {
        // Blindado: Suma exclusivamente el GD de las figuras pertenecientes a ESTA competición
        $gd_acumulado = mysqli_result(mysqli_query($connection, "SELECT sum(grado_dificultad) FROM figuras WHERE id IN (SELECT id_figura FROM fases WHERE id_categoria='$cat_id' AND id_competicion='$id_competicion')"), 0);
    }

    if (!$gd_acumulado || floatval($gd_acumulado) <= 0) {
        continue;
    }

    $query_inscr = "SELECT id_nadadora, sum(nota_final) as sum_nota_final, baja, preswimmer, id 
                    FROM inscripciones_figuras 
                    WHERE id_fase IN (SELECT id FROM fases WHERE id_categoria='$cat_id' AND id_competicion='$id_competicion') 
                    GROUP BY id_nadadora";
    $resultados_nadadoras = mysqli_query($connection, $query_inscr);

    if ($resultados_nadadoras) {
        while ($resultado_nadadora = mysqli_fetch_array($resultados_nadadoras)) {
            $id_nad = (int)$resultado_nadadora['id_nadadora'];
            $anio_nadadora = mysqli_result(mysqli_query($connection, "SELECT año_nacimiento FROM nadadoras WHERE id='$id_nad'"), 0);

            $query_pen = "SELECT COALESCE(SUM(ABS(p.puntos)), 0) 
                          FROM penalizaciones p 
                          JOIN penalizaciones_rutinas pr ON pr.id_penalizacion = p.id 
                          JOIN inscripciones_figuras ifig ON pr.id_inscripcion_figuras = ifig.id 
                          WHERE ifig.id_nadadora='$id_nad' AND ifig.id_competicion='$id_competicion'";
            $puntos_penalizacion = abs(floatval(mysqli_result(mysqli_query($connection, $query_pen), 0)));

            // Fórmula Oficial RFEN: (Suma de Notas de Figuras / GD Acumulado) * 10 - Penalizaciones
            $nota_final_calculada = (($resultado_nadadora['sum_nota_final'] / $gd_acumulado) * 10) - $puntos_penalizacion;
            if ($nota_final_calculada < 0) $nota_final_calculada = 0;

            $baja_val = $resultado_nadadora['baja'] ?? 'no';
            $pres_val = $resultado_nadadora['preswimmer'] ?? 'no';

            $q_ins = "INSERT INTO resultados_figuras_categorias 
                      (id_nadadora, id_categoria, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion) 
                      VALUES ('$id_nad', '$cat_id', '$anio_nadadora', '$gd_acumulado', '$puntos_penalizacion', '".$resultado_nadadora['sum_nota_final']."', '$nota_final_calculada', '$baja_val', '$pres_val', '$id_competicion')";
            mysqli_query($connection, $q_ins);
        }
    }
}

// 3. Inserción de resultados para fases de rutinas técnicas (Junior / Absoluta) en competiciones mixtas
$q_fases_rut = "SELECT f.id, f.id_categoria, f.id_modalidad FROM fases f WHERE f.id_competicion = '$id_competicion' AND (f.id_figura IS NULL OR f.id_figura = 0) AND f.id_modalidad > 0 AND f.id_modalidad != 8";
$res_fases_rut = mysqli_query($connection, $q_fases_rut);
if ($res_fases_rut && mysqli_num_rows($res_fases_rut) > 0) {
    while ($fr = mysqli_fetch_assoc($res_fases_rut)) {
        $id_fase_rut = $fr['id'];
        $id_cat_rut = $fr['id_categoria'];
        $id_mod_rut = (int)$fr['id_modalidad'];
        
        $q_insert_rut_results = "INSERT INTO resultados_figuras_categorias 
            (id_nadadora, id_categoria, id_modalidad, id_rutina, año, gd_acumulado, puntos_penalizacion, nota_final, nota_final_calculada, baja, preswimmer, id_competicion)
            SELECT rp.id_nadadora, '$id_cat_rut', '$id_mod_rut', r.id, n.año_nacimiento, r.dd_total, 0, r.nota_final, r.nota_final, r.baja, r.preswimmer, '$id_competicion'
            FROM rutinas r
            JOIN rutinas_participantes rp ON rp.id_rutina = r.id AND rp.reserva = 'no'
            JOIN nadadoras n ON rp.id_nadadora = n.id
            WHERE r.id_fase = '$id_fase_rut' AND r.id_competicion = '$id_competicion'";
        mysqli_query($connection, $q_insert_rut_results);
    }
}

// 4. Ordenación y asignación de puestos oficiales (Normativa FNRM Regla 9: 100% nota real)
// Clasificación separada por categoría y modalidad (para rutinas técnicas Junior/Absoluta con Opción 2)
$query_cats_res = "SELECT id_categoria, COALESCE(id_modalidad, 0) AS id_mod 
                   FROM resultados_figuras_categorias 
                   WHERE id_competicion='$id_competicion' 
                   GROUP BY id_categoria, COALESCE(id_modalidad, 0) 
                   ORDER BY id_categoria, id_mod";
$categorias_res = mysqli_query($connection, $query_cats_res);

if ($categorias_res) {
    while ($grupo = mysqli_fetch_array($categorias_res)) {
        $id_cat = $grupo['id_categoria'];
        $id_mod = (int)$grupo['id_mod'];
        $filtro_mod = ($id_mod > 0) ? "AND id_modalidad = '$id_mod'" : "AND (id_modalidad IS NULL OR id_modalidad = 0)";

        $posicion = 0;
        $nota_ganador = 0;
        $nota_anterior = -1;
        
        $query_ord = "SELECT * FROM resultados_figuras_categorias 
                      WHERE id_categoria='$id_cat' AND id_competicion='$id_competicion' {$filtro_mod} 
                      ORDER BY nota_final_calculada DESC";
        $res_nad_ord = mysqli_query($connection, $query_ord);
        $idx = 0;
        
        if ($res_nad_ord) {
            while ($resultado_nadadora = mysqli_fetch_array($res_nad_ord)) {
                $nota_actual = (float)$resultado_nadadora['nota_final_calculada'];
                
                if ($idx == 0) {
                    $nota_ganador = $nota_actual;
                    $posicion = 1;
                } else {
                    if (abs($nota_actual - $nota_anterior) >= 0.0001) {
                        $posicion++;
                    }
                }
                $nota_anterior = $nota_actual;
                
                $puntos_nadadora = ($resultado_nadadora['baja'] == 'si' || $resultado_nadadora['preswimmer'] == 'si') ? 0.0000 : $nota_actual;
                $diferencia = max(0, $nota_ganador - $nota_actual);

                $query_up = "UPDATE resultados_figuras_categorias 
                             SET posicion='$posicion', diferencia='$diferencia', puntos='$puntos_nadadora' 
                             WHERE id = ".$resultado_nadadora['id'];
                mysqli_query($connection, $query_up);
                $idx++;
            }
        }
    }
}

// Retiradas / Pre-swimmers a 0 puntos
mysqli_query($connection, "UPDATE resultados_figuras_categorias SET puntos='0' WHERE (baja='si' OR preswimmer='si') AND id_competicion='$id_competicion'");

// Marcar fases de la competición como puntuadas
mysqli_query($connection, "UPDATE fases SET puntuada='si' WHERE id_competicion='$id_competicion'");

// Recalcular también la tabla histórica de figuras de forma silenciosa
@include(__DIR__ . '/puntuaciones_fases_figuras_puntuar.php');

$cnt_res = mysqli_fetch_row(mysqli_query($connection, "SELECT count(*) FROM resultados_figuras_categorias WHERE id_competicion='$id_competicion'"))[0];
$total_calculados = (int)($cnt_res ?? 0);

// Limpiar buffer de salida
ob_end_clean();

// 5. Salida JSON si es AJAX
if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => true,
        'id_competicion' => $id_competicion,
        'total_calculados' => $total_calculados,
        'message' => 'Resultados de Liga calculados con éxito según la normativa oficial RFEN.'
    ]);
    exit;
}

// 6. Salida visual si se abre directamente en navegador
include('includes/header.php');
include('includes/navbar.php');
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface font-lexend">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-12 max-w-4xl mx-auto w-full">
        <div class="bg-white rounded-3xl p-8 md:p-12 border border-slate-200 shadow-sm text-center">
            <div class="w-20 h-20 rounded-3xl bg-amber-50 border border-amber-200 text-amber-500 flex items-center justify-center mx-auto mb-6 shadow-sm">
                <i class="fas fa-trophy text-3xl"></i>
            </div>
            
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-black rounded-lg border border-emerald-200 uppercase tracking-widest inline-flex items-center gap-1.5 mb-4">
                <i class="fas fa-check-circle text-emerald-500"></i> Proceso Completado
            </span>

            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-3">
                Cálculo de Liga Oficial RFEN Finalizado
            </h1>
            <p class="text-slate-500 text-sm max-w-xl mx-auto mb-8">
                Se han computado correctamente <b class="text-slate-800"><?php echo $total_calculados; ?> resultados</b> ponderados por el Grado de Dificultad (Base 100) y asignados al Ranking Acumulado de la Liga.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="informes/informe_figuras_liga_categorias.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Clasificación General Liga" target="_blank" class="px-6 py-3.5 bg-amber-500 hover:bg-amber-600 text-white font-black uppercase text-xs tracking-wider rounded-2xl shadow-lg shadow-amber-500/20 active:scale-95 transition-all flex items-center gap-2 no-underline">
                    <i class="fas fa-file-pdf"></i> Ver Ranking de Liga (PDF)
                </a>
                <a href="informes/informe_figuras_resultados_categorias.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Resultados por categorias" target="_blank" class="px-6 py-3.5 bg-slate-800 hover:bg-slate-900 text-white font-black uppercase text-xs tracking-wider rounded-2xl shadow-lg shadow-slate-800/20 active:scale-95 transition-all flex items-center gap-2 no-underline">
                    <i class="fas fa-list-ol"></i> Ver Resultados Categorías (PDF)
                </a>
                <a href="informes_competicion.php?id_competicion=<?php echo $id_competicion; ?>" class="px-6 py-3.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-black uppercase text-xs tracking-wider rounded-2xl shadow-xs active:scale-95 transition-all flex items-center gap-2 no-underline">
                    <i class="fas fa-arrow-left"></i> Volver a Informes
                </a>
            </div>
        </div>
    </div>
</main>

<?php include('includes/scripts.php'); ?>
<?php include('includes/footer.php'); ?>
