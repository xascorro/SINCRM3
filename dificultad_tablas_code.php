<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('security.php');

// Solo Administrador (1) y Secretario (2) pueden realizar modificaciones
$id_rol_user = (int)($_SESSION['id_rol'] ?? 0);
$puede_editar = in_array($id_rol_user, [1, 2, 3]);

if (!$puede_editar) {
    $_SESSION['estado'] = 'No tienes permisos para modificar las tablas de dificultad.';
    header('Location: dificultad_tablas.php');
    exit();
}

$tablas_permitidas = [
    'dificultad_hibridos'   => 'Híbridos',
    'dificultad_acrobacias' => 'Acrobacias Equipos',
    'dificultad_acropair'   => 'Acrobacias Parejas',
    'dificultad_tre'        => 'Elementos Técnicos (TRE)',
    'dificultad_basemark'   => 'Base Mark'
];

$tabla_post = $_POST['tabla'] ?? $_GET['tabla'] ?? 'dificultad_hibridos';
if (!array_key_exists($tabla_post, $tablas_permitidas)) {
    $tabla_post = 'dificultad_hibridos';
}

$version_curr = $_POST['version_curr'] ?? $_GET['version_curr'] ?? '2627';

// -----------------------------------------------------------------------------
// 1. ELIMINAR ELEMENTO
// -----------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_del = intval($_GET['id']);
    $q_del = "DELETE FROM $tabla_post WHERE id = $id_del";
    if (mysqli_query($connection, $q_del)) {
        write_log("Elemento #$id_del eliminado de $tabla_post", "WARNING");
        $_SESSION['correcto'] = 'Elemento eliminado correctamente.';
    } else {
        $_SESSION['estado'] = 'Error al eliminar el elemento: ' . mysqli_error($connection);
    }
    header("Location: dificultad_tablas.php?tabla=$tabla_post&version=$version_curr");
    exit();
}

// -----------------------------------------------------------------------------
// 2. CREAR NUEVO ELEMENTO
// -----------------------------------------------------------------------------
if (isset($_POST['btn_guardar_nuevo'])) {
    $codigo = mysqli_real_escape_string($connection, trim($_POST['codigo'] ?? ''));
    $valor = floatval(str_replace(',', '.', $_POST['valor'] ?? '0'));
    $agrupar = mysqli_real_escape_string($connection, trim($_POST['agrupar'] ?? ''));
    $subagrupar = mysqli_real_escape_string($connection, trim($_POST['subagrupar'] ?? ''));
    $descripcion = mysqli_real_escape_string($connection, trim($_POST['descripcion'] ?? ''));
    $version = mysqli_real_escape_string($connection, trim($_POST['version'] ?? '2627'));
    $temporada = mysqli_real_escape_string($connection, trim($_POST['temporada'] ?? '2026-2027'));
    $orden_select = intval($_POST['orden_select'] ?? 0);

    if (empty($codigo)) {
        $_SESSION['estado'] = 'El código del elemento es obligatorio.';
        header("Location: dificultad_tablas.php?tabla=$tabla_post&version=$version");
        exit();
    }

    // Verificar columnas existentes en la tabla
    $cols_check = [];
    $res_cols = mysqli_query($connection, "SHOW COLUMNS FROM $tabla_post");
    while ($c = mysqli_fetch_assoc($res_cols)) {
        $cols_check[] = $c['Field'];
    }

    $fields = ['codigo', 'valor', 'agrupar', 'descripcion', 'version'];
    $values = ["'$codigo'", "'$valor'", "'$agrupar'", "'$descripcion'", "'$version'"];

    if (in_array('temporada', $cols_check)) {
        $fields[] = 'temporada';
        $values[] = "'$temporada'";
    }
    if (in_array('subagrupar', $cols_check)) {
        $fields[] = 'subagrupar';
        $values[] = "'$subagrupar'";
    }
    if (in_array('orden_select', $cols_check)) {
        $fields[] = 'orden_select';
        $values[] = "'$orden_select'";
    }

    $q_ins = "INSERT INTO $tabla_post (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
    if (mysqli_query($connection, $q_ins)) {
        write_log("Nuevo elemento $codigo ($valor) creado en $tabla_post (v$version)", "SUCCESS");
        $_SESSION['correcto'] = "Elemento '$codigo' creado con éxito.";
    } else {
        $_SESSION['estado'] = 'Error al registrar el elemento: ' . mysqli_error($connection);
    }

    header("Location: dificultad_tablas.php?tabla=$tabla_post&version=$version");
    exit();
}

// -----------------------------------------------------------------------------
// 3. ACTUALIZAR ELEMENTO EXISTENTE
// -----------------------------------------------------------------------------
if (isset($_POST['btn_actualizar'])) {
    $id_edit = intval($_POST['id_edit'] ?? 0);
    $codigo = mysqli_real_escape_string($connection, trim($_POST['codigo'] ?? ''));
    $valor = floatval(str_replace(',', '.', $_POST['valor'] ?? '0'));
    $agrupar = mysqli_real_escape_string($connection, trim($_POST['agrupar'] ?? ''));
    $subagrupar = mysqli_real_escape_string($connection, trim($_POST['subagrupar'] ?? ''));
    $descripcion = mysqli_real_escape_string($connection, trim($_POST['descripcion'] ?? ''));
    $version = mysqli_real_escape_string($connection, trim($_POST['version'] ?? '2627'));
    $temporada = mysqli_real_escape_string($connection, trim($_POST['temporada'] ?? '2026-2027'));
    $orden_select = intval($_POST['orden_select'] ?? 0);

    if ($id_edit <= 0 || empty($codigo)) {
        $_SESSION['estado'] = 'Datos inválidos para actualizar el elemento.';
        header("Location: dificultad_tablas.php?tabla=$tabla_post&version=$version");
        exit();
    }

    // Verificar columnas existentes
    $cols_check = [];
    $res_cols = mysqli_query($connection, "SHOW COLUMNS FROM $tabla_post");
    while ($c = mysqli_fetch_assoc($res_cols)) {
        $cols_check[] = $c['Field'];
    }

    $set_parts = [
        "codigo = '$codigo'",
        "valor = '$valor'",
        "agrupar = '$agrupar'",
        "descripcion = '$descripcion'",
        "version = '$version'"
    ];

    if (in_array('temporada', $cols_check)) {
        $set_parts[] = "temporada = '$temporada'";
    }
    if (in_array('subagrupar', $cols_check)) {
        $set_parts[] = "subagrupar = '$subagrupar'";
    }
    if (in_array('orden_select', $cols_check)) {
        $set_parts[] = "orden_select = '$orden_select'";
    }

    $q_upd = "UPDATE $tabla_post SET " . implode(', ', $set_parts) . " WHERE id = $id_edit";
    if (mysqli_query($connection, $q_upd)) {
        write_log("Elemento #$id_edit ($codigo) modificado en $tabla_post (v$version)", "INFO");
        $_SESSION['correcto'] = "Elemento '$codigo' actualizado correctamente.";
    } else {
        $_SESSION['estado'] = 'Error al actualizar el elemento: ' . mysqli_error($connection);
    }

    header("Location: dificultad_tablas.php?tabla=$tabla_post&version=$version");
    exit();
}

// -----------------------------------------------------------------------------
// 4. CLONAR TEMPORADA A NUEVA VERSIÓN (Solo Admin)
// -----------------------------------------------------------------------------
if (isset($_POST['btn_clonar_temporada']) && $id_rol_user === 1) {
    $v_origen = mysqli_real_escape_string($connection, trim($_POST['version_origen'] ?? ''));
    $v_destino = mysqli_real_escape_string($connection, trim($_POST['version_destino'] ?? ''));
    $temp_destino = mysqli_real_escape_string($connection, trim($_POST['temporada_destino'] ?? ''));
    $tablas_a_clonar = $_POST['tablas_clonar'] ?? array_keys($tablas_permitidas);

    if (empty($v_origen) || empty($v_destino) || empty($temp_destino)) {
        $_SESSION['estado'] = 'Debes especificar la versión origen, versión destino y nombre de temporada.';
        header("Location: dificultad_tablas.php?tabla=$tabla_post&version=$version_curr");
        exit();
    }

    $total_clonados = 0;

    foreach ($tablas_a_clonar as $t) {
        if (!array_key_exists($t, $tablas_permitidas)) continue;

        // Comprobar si ya existen registros en destino para evitar duplicar por error
        $q_chk = mysqli_query($connection, "SELECT COUNT(*) as cnt FROM $t WHERE version = '$v_destino'");
        $cnt_existente = intval(mysqli_fetch_assoc($q_chk)['cnt'] ?? 0);
        if ($cnt_existente > 0) {
            continue; // Ya existe en esa tabla
        }

        // Obtener columnas excepto id, creado, modificado
        $cols = [];
        $res_c = mysqli_query($connection, "SHOW COLUMNS FROM $t");
        while ($col = mysqli_fetch_assoc($res_c)) {
            $f = $col['Field'];
            if (!in_array($f, ['id', 'creado', 'modificado', 'version', 'temporada'])) {
                $cols[] = $f;
            }
        }

        $cols_str = implode(', ', $cols);
        $q_clone = "INSERT INTO $t ($cols_str, version, temporada) 
                    SELECT $cols_str, '$v_destino', '$temp_destino' 
                    FROM $t WHERE version = '$v_origen'";
        if (mysqli_query($connection, $q_clone)) {
            $total_clonados += mysqli_affected_rows($connection);
        }
    }

    write_log("Clonación de temporada v$v_origen -> v$v_destino ($total_clonados filas)", "SUCCESS");
    $_SESSION['correcto'] = "Temporada clonada con éxito. Se han duplicado $total_clonados elementos hacia la versión v$v_destino ($temp_destino).";
    header("Location: dificultad_tablas.php?tabla=$tabla_post&version=$v_destino");
    exit();
}

header("Location: dificultad_tablas.php");
exit();
