<?php
header('Content-Type: application/json');
include('database/dbconfig.php');

$id_fase = isset($_GET['id_fase']) ? (int)$_GET['id_fase'] : 0;

if ($id_fase === 0) {
    echo json_encode(['error' => 'ID de fase no proporcionado']);
    exit;
}

$query = "SELECT id, nota_final FROM rutinas WHERE id_fase = $id_fase";
$res = mysqli_query($connection, $query);

$scores = [];
while ($row = mysqli_fetch_assoc($res)) {
    $scores[] = [
        'id' => (int)$row['id'],
        'nota_final' => (float)$row['nota_final']
    ];
}

echo json_encode(['scores' => $scores]);
