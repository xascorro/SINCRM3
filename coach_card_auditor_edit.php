<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>
<?php

// 1. LÓGICA DE ACTUALIZACIÓN (Al enviar el formulario)
if(isset($_POST['update_btn'])){
    $id_regla = $_POST['edit_id'];
    $max_tre = $_POST['max_tre'];
    $max_hibridos = $_POST['max_hibridos'];
    $max_acrobacias = $_POST['max_acrobacias'];
    $req_hibrido_creativo = $_POST['req_hibrido_creativo'];
    
    // Si la apnea está vacía, la guardamos como NULL en SQL
    $max_apnea = !empty($_POST['max_apnea']) ? $_POST['max_apnea'] : 'NULL';

    $query_update = "UPDATE reglas_competicion SET 
                        max_tre = '$max_tre', 
                        max_hibridos = '$max_hibridos', 
                        max_acrobacias = '$max_acrobacias', 
                        req_hibrido_creativo = '$req_hibrido_creativo', 
                        max_apnea = $max_apnea 
                     WHERE id_regla = '$id_regla'";
                     
    $query_update_run = mysqli_query($connection, $query_update);

    if($query_update_run){
        $_SESSION['correcto'] = 'Los límites del Apéndice III se han modificado correctamente.';
    } else {
        $_SESSION['estado'] = 'Error al actualizar: ' . mysqli_error($connection);
    }
}
?>

<div class="container-fluid mt-4">
    <?php include('includes/alertas_v4.php'); ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-edit"></i> Editar Reglas de Competición (Apéndice III)</h6>
        </div>
        <div class="card-body">

            <?php
            // 2. LÓGICA DE VISUALIZACIÓN (Al llegar a la página desde el botón "Editar")
            // Compatible con el envío por POST (como en tu fases_edit.php) o por GET
            if(isset($_POST['edit_btn']) || isset($_GET['id'])) {
                
                $id = isset($_POST['edit_id']) ? $_POST['edit_id'] : $_GET['id'];
                
                // Hacemos el JOIN para poder mostrar el nombre de la Categoría y Modalidad
                $query = "SELECT r.*, c.nombre as categoria_nombre, m.nombre as modalidad_nombre 
                          FROM reglas_competicion r
                          JOIN categorias c ON r.id_categoria = c.id
                          JOIN modalidades m ON r.id_modalidad = m.id
                          WHERE r.id_regla = '$id'";
                          
                $query_run = mysqli_query($connection, $query);

                if(mysqli_num_rows($query_run) > 0) {
                    $row = mysqli_fetch_assoc($query_run);
                    ?>
                    
                    <form action="coach_card_auditor_edit.php" method="POST">
                        <input type="hidden" name="edit_id" value="<?php echo $row['id_regla']; ?>">

                        <!-- Información de Solo Lectura -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="text-muted text-uppercase fw-bold" style="font-size: 0.8rem;">Categoría</label>
                                <h4><span class="badge bg-secondary"><?php echo $row['categoria_nombre']; ?></span></h4>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted text-uppercase fw-bold" style="font-size: 0.8rem;">Modalidad</label>
                                <h4><span class="badge bg-secondary"><?php echo $row['modalidad_nombre']; ?></span></h4>
                            </div>
                        </div>

                        <hr>

                        <!-- Campos Editables -->
                        <div class="row mb-3">
                            <div class="col-md-3 form-group">
                                <label class="fw-bold">Max. Híbridos Libres</label>
                                <input type="number" name="max_hibridos" value="<?php echo $row['max_hibridos']; ?>" class="form-control text-center" min="0">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="fw-bold">Max. Acrobacias</label>
                                <input type="number" name="max_acrobacias" value="<?php echo $row['max_acrobacias']; ?>" class="form-control text-center" min="0">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="fw-bold">Max. Elem. Técnicos (TRE)</label>
                                <input type="number" name="max_tre" value="<?php echo $row['max_tre']; ?>" class="form-control text-center" min="0">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="fw-bold">Max. Apnea (Segundos)</label>
                                <input type="number" step="0.01" name="max_apnea" value="<?php echo $row['max_apnea']; ?>" class="form-control text-center" placeholder="Ej: 18.99">
                                <small class="text-muted">Dejar vacío si no aplica</small>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 form-group">
                                <label class="fw-bold">¿Híbrido Creativo Obligatorio?</label>
                                <select name="req_hibrido_creativo" class="form-control">
                                    <option value="1" <?php if($row['req_hibrido_creativo'] == 1) echo 'selected'; ?>>Sí (Obligatorio)</option>
                                    <option value="0" <?php if($row['req_hibrido_creativo'] == 0) echo 'selected'; ?>>No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <!-- Cambia 'admin_reglas.php' por el nombre de tu tabla principal si es distinto -->
                                <a href="admin_reglas.php" class="btn btn-danger">Cancelar</a>
                                <button type="submit" name="update_btn" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php
                } else {
                    echo "<h5>No se ha encontrado la regla seleccionada.</h5>";
                }
            } else {
                echo '<div class="alert alert-warning">No se ha recibido ningún ID para editar. Vuelve al listado.</div>';
            }
            ?>
        </div>
    </div>
</div>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>