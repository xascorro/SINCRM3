<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

if (!isset($_POST['id_rutina'])) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>No se ha recibido ninguna rutina para auditar. Vuelve al listado de fases.</div></div>";
    // include 'footer.php';
    exit;
}

$id_rutina = intval($_POST['id_rutina']);

// 1. OBTENER DATOS DE LA RUTINA Y LÍMITES (Haciendo el puente por la tabla 'fases')
$q_info = "SELECT r.*, c.nombre as categoria, m.nombre as modalidad, 
                  rc.max_hibridos, rc.max_acrobacias, rc.max_tre, rc.req_hibrido_creativo 
           FROM rutinas r 
           LEFT JOIN fases f ON r.id_fase = f.id
           LEFT JOIN categorias c ON f.id_categoria = c.id 
           LEFT JOIN modalidades m ON f.id_modalidad = m.id 
           LEFT JOIN reglas_competicion rc ON f.id_categoria = rc.id_categoria AND f.id_modalidad = rc.id_modalidad
           WHERE r.id = $id_rutina";
$res_info = mysqli_query($connection, $q_info);
$rutina_info = mysqli_fetch_assoc($res_info);

// 2. EXTRAER EL DICCIONARIO DE FAMILIAS DESDE TU BASE DE DATOS
$mapa_familias = [];
$q_fam = mysqli_query($connection, "SELECT codigo, agrupar FROM dificultad_hibridos");
if($q_fam) {
    while($f = mysqli_fetch_assoc($q_fam)) {
        // Evita el error Deprecated de PHP 8.1+ usando ?? ''
        $codigo_fam = trim($f['codigo'] ?? '');
        $agrupar_fam = trim($f['agrupar'] ?? '');
        
        if ($codigo_fam !== '') {
            $mapa_familias[$codigo_fam] = $agrupar_fam;
        }
    }
}


// 3. EXTRAER Y ESTRUCTURAR TODOS LOS ELEMENTOS DE LA RUTINA
$q_elementos = "SELECT * FROM hibridos_rutina WHERE id_rutina = $id_rutina ORDER BY elemento ASC, id ASC";
$res_elementos = mysqli_query($connection, $q_elementos);

$elementos = [];
$totales = ['HYBRID' => 0, 'ACRO' => 0, 'TRE' => 0];

while ($row = mysqli_fetch_assoc($res_elementos)) {
    $num_elem = $row['elemento'];
    
    if (!isset($elementos[$num_elem])) {
        $elementos[$num_elem] = [
            'tipo_general' => 'Desconocido',
            'dificultades' => [],
            'familias' => [],
            'dd_total' => 0,
            'basemark' => 0,
            'is_creativo' => false
        ];
    }
    
    // Clasificar el tipo de elemento base
    if ($row['tipo'] == 'basemark' && !empty($row['texto'])) {
        if ($row['texto'] == 'HYBRID') {
            $elementos[$num_elem]['tipo_general'] = 'HYBRID';
            $elementos[$num_elem]['basemark'] = $row['valor'];
        } elseif (strpos($row['texto'], 'ACRO') !== false || strpos($row['texto'], 'Grupo') !== false) {
            $elementos[$num_elem]['tipo_general'] = 'ACROBATIC';
            $elementos[$num_elem]['basemark'] = $row['valor'];
            $elementos[$num_elem]['grupo_acro'] = $row['texto']; // Ej: ACRO-A, Grupo-B
        }
    }
    
    // Identificar si es un TRE
    if ($row['tipo'] == 'dd' && strpos($row['texto'], 'TRE') !== false) {
        $elementos[$num_elem]['tipo_general'] = 'TRE';
    }

    // Recolectar códigos DD para Híbridos y asociar a la Familia real de la BD
    if ($row['tipo'] == 'dd' && !empty($row['texto']) && strpos($row['texto'], 'TRE') === false) {
        $codigo = trim($row['texto'] ?? '');

        // Verificamos primero si es un Híbrido Creativo
        if ($codigo == 'ShoHY' || $codigo == 'Creativo' || strpos(strtolower($codigo), 'hy') !== false) {
            $elementos[$num_elem]['is_creativo'] = true;
        } else {
            // Guardamos el código exacto para luego comprobar la regla de "Máximo 3 técnicas idénticas"
            $elementos[$num_elem]['dificultades'][] = $codigo;
            
            // Buscamos la familia exacta en el diccionario extraído de la tabla 'dificultad_hibridos'
            $familia = isset($mapa_familias[$codigo]) ? $mapa_familias[$codigo] : 'Otra';
            
            // Ignoramos las clasificaciones que NO son familias competitivas (No se les aplica la regla de Máx 5)
            $ignorar = ['Bonus', 'Movimientos', 'Apnea', 'Creativo', 'no', ''];
            
            if (!in_array($familia, $ignorar) && $familia != 'Otra') {
                if (!isset($elementos[$num_elem]['familias'][$familia])) {
                    $elementos[$num_elem]['familias'][$familia] = 0;
                }
                // Sumamos 1 a la familia correspondiente (Ej: +1 a 'Empujes' o 'Flexibilidad')
                $elementos[$num_elem]['familias'][$familia]++;
            }
        }
    }

    // Sumar el Total de DD del elemento
    if ($row['tipo'] == 'total') {
        $elementos[$num_elem]['dd_total'] = $row['valor'];
    }
}

// 4. CONTEO FINAL PARA LA AUDITORÍA ESTRUCTURAL
foreach ($elementos as $elem) {
    if ($elem['tipo_general'] == 'HYBRID') $totales['HYBRID']++;
    if ($elem['tipo_general'] == 'ACROBATIC') $totales['ACRO']++;
    if ($elem['tipo_general'] == 'TRE') $totales['TRE']++;
}
?>

<div class="container-fluid mt-4">
    <!-- CABECERA DE INFORME -->
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <span style="position: relative; display: inline-block; width: 1.2em; text-align: center; margin-right: 10px;">
                    <i class="fa fa-solid fa-puzzle-piece text-dark" aria-hidden="true"></i>
                    <i class="fa fa-solid fa-magnifying-glass text-white" style="position: absolute; bottom: -4px; right: -6px; font-size: 0.65em; -webkit-text-stroke: 2px #007bff;" aria-hidden="true"></i>
                </span>
                Informe de Auditoría DTC
            </h4>
            <span class="badge bg-light text-dark fs-6">Rutina ID: <?php echo $id_rutina; ?></span>
        </div>
        <div class="card-body bg-light">
            <div class="row text-center">
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase">Categoría</h6>
                    <h5><strong><?php echo $rutina_info['categoria'] ?? 'No definida'; ?></strong></h5>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted text-uppercase">Modalidad</h6>
                    <h5><strong><?php echo $rutina_info['modalidad'] ?? 'No definida'; ?></strong></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: APÉNDICE III (Estructural) -->
    <h5 class="mb-3 border-bottom pb-2 text-secondary"><i class="fas fa-list-check"></i> 1. Revisión Estructural (Apéndice III)</h5>
    <div class="row mb-4">
        <!-- Tarjeta Híbridos -->
        <div class="col-md-4">
            <div class="card <?php echo ($totales['HYBRID'] > $rutina_info['max_hibridos']) ? 'border-danger' : 'border-success'; ?>">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Híbridos Libres</h6>
                    <h2 class="<?php echo ($totales['HYBRID'] > $rutina_info['max_hibridos']) ? 'text-danger' : 'text-success'; ?>">
                        <?php echo $totales['HYBRID']; ?> <small class="text-muted fs-6">/ <?php echo $rutina_info['max_hibridos'] ?? '-'; ?></small>
                    </h2>
                    <?php if($totales['HYBRID'] > $rutina_info['max_hibridos']): ?>
                        <span class="badge bg-danger">Exceso: Penalización 2 pts por elemento</span>
                    <?php else: ?>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Cumple normativa</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Tarjeta Acrobacias -->
        <div class="col-md-4">
            <div class="card <?php echo ($totales['ACRO'] > $rutina_info['max_acrobacias']) ? 'border-danger' : 'border-success'; ?>">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Acrobacias</h6>
                    <h2 class="<?php echo ($totales['ACRO'] > $rutina_info['max_acrobacias']) ? 'text-danger' : 'text-success'; ?>">
                        <?php echo $totales['ACRO']; ?> <small class="text-muted fs-6">/ <?php echo $rutina_info['max_acrobacias'] ?? '-'; ?></small>
                    </h2>
                    <?php if($totales['ACRO'] > $rutina_info['max_acrobacias']): ?>
                        <span class="badge bg-danger">Exceso: Penalización 2 pts por elemento</span>
                    <?php else: ?>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Cumple normativa</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Tarjeta TRE -->
        <div class="col-md-4">
            <div class="card <?php echo (strpos($rutina_info['modalidad'] ?? '', 'Técnic') !== false && $totales['TRE'] != $rutina_info['max_tre']) ? 'border-danger' : 'border-success'; ?>">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Elementos Técnicos (TRE)</h6>
                    <h2>
                        <?php echo $totales['TRE']; ?> <small class="text-muted fs-6">/ <?php echo $rutina_info['max_tre'] ?? '0'; ?></small>
                    </h2>
                    <?php if(strpos($rutina_info['modalidad'] ?? '', 'Técnic') !== false && $totales['TRE'] != $rutina_info['max_tre']): ?>
                        <span class="badge bg-danger">Faltan/Sobran TREs obligatorios</span>
                    <?php else: ?>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Cumple normativa</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: AUDITORÍA INTERNA POR ELEMENTO -->
    <h5 class="mb-3 border-bottom pb-2 text-secondary"><i class="fas fa-microscope"></i> 2. Análisis Interno de Elementos</h5>
    
    <div class="row">
        <?php foreach ($elementos as $num => $elem): ?>
            <div class="col-md-12 mb-3">
                <div class="card shadow-sm border-start border-4 <?php echo $elem['tipo_general'] == 'HYBRID' ? 'border-primary' : ($elem['tipo_general'] == 'ACROBATIC' ? 'border-danger' : 'border-warning'); ?>">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 fw-bold">
                            Elemento <?php echo $num; ?>: 
                            <span class="badge <?php echo $elem['tipo_general'] == 'HYBRID' ? 'bg-primary' : ($elem['tipo_general'] == 'ACROBATIC' ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                <?php echo $elem['tipo_general']; ?>
                                <?php echo isset($elem['grupo_acro']) ? ' (' . $elem['grupo_acro'] . ')' : ''; ?>
                            </span>
                            <?php if($elem['is_creativo']) echo '<span class="badge bg-info text-dark ms-2"><i class="fas fa-star"></i> Creativo</span>'; ?>
                        </h6>
                        <span class="fw-bold">DD Calculado: <?php echo number_format($elem['dd_total'], 2); ?></span>
                    </div>
                    <div class="card-body py-2">
                        
                        <?php if($elem['tipo_general'] == 'HYBRID' && !$elem['is_creativo']): ?>
                            <!-- Validaciones Híbridos Libres -->
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small fw-bold">CÓDIGOS DECLARADOS:</p>
                                    <p class="mb-2 font-monospace bg-light p-2 rounded border">
                                        <?php echo !empty($elem['dificultades']) ? implode(' - ', $elem['dificultades']) : 'Ninguno'; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small fw-bold">CHECKLIST NORMATIVA HÍBRIDOS:</p>
                                    <ul class="list-group list-group-sm list-group-flush">
                                        
                                        <!-- Regla 1: Max 5 por familia -->
                                        <?php 
                                        $error_fam = false;
                                        foreach($elem['familias'] as $fam => $cant) {
                                            if($cant > 5) {
                                                echo "<li class='list-group-item text-danger py-1 px-2 border-0'><i class='fas fa-times-circle'></i> Error: Familia <b>$fam</b> declarada $cant veces (Máx 5).</li>";
                                                $error_fam = true;
                                            }
                                        }
                                        if(!$error_fam && !empty($elem['familias'])) echo "<li class='list-group-item text-success py-1 px-2 border-0'><i class='fas fa-check-circle'></i> Límite de 5 movimientos por familia respetado.</li>";
                                        ?>

                                        <!-- Regla 2: Max 3 misma técnica -->
                                        <?php
                                        $conteo_tecnicas = array_count_values($elem['dificultades']);
                                        $error_tec = false;
                                        foreach($conteo_tecnicas as $tec => $veces) {
                                            if($veces > 3) {
                                                echo "<li class='list-group-item text-danger py-1 px-2 border-0'><i class='fas fa-times-circle'></i> Error: Técnica <b>$tec</b> repetida $veces veces (Máx 3).</li>";
                                                $error_tec = true;
                                            }
                                        }
                                        if(!$error_tec && !empty($elem['dificultades'])) echo "<li class='list-group-item text-success py-1 px-2 border-0'><i class='fas fa-check-circle'></i> Límite de 3 repeticiones por técnica respetado.</li>";
                                        ?>

                                        <!-- Regla 3: Límite Alevín (Si aplica) -->
                                        <?php if(strpos(strtolower($rutina_info['categoria'] ?? ''), 'alev') !== false): 
                                            $limite_alev = (strpos(strtolower($rutina_info['modalidad']), 'equipo') !== false || strpos(strtolower($rutina_info['modalidad']), 'combo') !== false) ? 4.0 : 5.0;
                                            if($elem['dd_total'] > $limite_alev):
                                        ?>
                                            <li class='list-group-item text-danger py-1 px-2 border-0'><i class='fas fa-shield-alt'></i> Límite Seguridad Alevín superado: DD <?php echo $elem['dd_total']; ?> (Máx permitido: <?php echo $limite_alev; ?>).</li>
                                        <?php else: ?>
                                            <li class='list-group-item text-success py-1 px-2 border-0'><i class='fas fa-check-circle'></i> Dificultad dentro del límite de seguridad Alevín (Máx <?php echo $limite_alev; ?>).</li>
                                        <?php endif; endif; ?>

                                    </ul>
                                </div>
                            </div>
                        
                        <?php elseif($elem['tipo_general'] == 'ACROBATIC'): ?>
                            <!-- Validaciones Acrobacias -->
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="list-group list-group-sm list-group-flush">
                                        <li class='list-group-item text-primary py-1 px-2 border-0'><i class='fas fa-info-circle'></i> Base Mark verificado para <?php echo $elem['grupo_acro'] ?? 'Acro'; ?>: <?php echo number_format($elem['basemark'], 2); ?></li>
                                        <li class='list-group-item text-warning text-dark py-1 px-2 border-0'><i class='fas fa-exclamation-triangle'></i> <i>Aviso DTC:</i> Verifica manualmente que la construcción/agarre o posiciones no se repitan con otra acrobacia del mismo grupo.</li>
                                        
                                        <?php if(strpos(strtolower($rutina_info['categoria'] ?? ''), 'infantil') !== false || strpos(strtolower($rutina_info['categoria'] ?? ''), 'alev') !== false): ?>
                                            <li class='list-group-item text-danger py-1 px-2 border-0'><i class='fas fa-shield-alt'></i> El DTC debe verificar los topes de seguridad de la RFEN para Menores (Ej: Alevín Grupo A Máx 2.50 DD). DD declarado: <?php echo number_format($elem['dd_total'], 2); ?>.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                        <?php elseif($elem['tipo_general'] == 'TRE'): ?>
                            <!-- Validaciones TRE -->
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="list-group list-group-sm list-group-flush">
                                        <li class='list-group-item text-success py-1 px-2 border-0'><i class='fas fa-check-circle'></i> Elemento Técnico (TRE) identificado correctamente.</li>
                                        <li class='list-group-item text-warning text-dark py-1 px-2 border-0'><i class='fas fa-exclamation-triangle'></i> <i>Aviso DTC:</i> Los TRE deben nadarse estrictamente en el orden declarado. Alterar el orden resultará en un 0.</li>
                                    </ul>
                                </div>
                            </div>

                        <?php elseif($elem['is_creativo']): ?>
                            <!-- Validaciones Híbrido Creativo -->
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="list-group list-group-sm list-group-flush">
                                        <li class='list-group-item text-success py-1 px-2 border-0'><i class='fas fa-check-circle'></i> Identificado como Híbrido Creativo (ShoHY/Creativo). Dificultad fijada.</li>
                                        <li class='list-group-item text-info text-dark py-1 px-2 border-0'><i class='fas fa-stopwatch'></i> <i>Control de Apnea:</i> Mínimo 10s (Infantil) o 6s (Alevín). No se aplican reglas de familias.</li>
                                    </ul>
                                </div>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($elementos)): ?>
            <div class="col-12"><div class="alert alert-warning text-center">No se han registrado elementos en la tabla 'hibridos_rutina' para esta rutina.</div></div>
        <?php endif; ?>
    </div>

    <!-- Botones de Acción -->
    <div class="row mt-3 mb-5">
        <div class="col-md-12 text-center">
            <button class="btn btn-secondary shadow-sm" onclick="history.back()"><i class="fas fa-arrow-left"></i> Volver Atrás</button>
            <button class="btn btn-primary shadow-sm ms-2" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / Guardar en PDF</button>
        </div>
    </div>

</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>