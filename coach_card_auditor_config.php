<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<div class="container-fluid mt-4">
	<!-- Cabecera de la sección -->
	<div class="row mb-3 align-items-center">
		<div class="col-md-8">
			<h2><i class="fas fa-clipboard-check text-primary"></i> Coach Card Auditor - Settings</h2>
			<p class="text-muted">Gestión de límites estructurales del Apéndice III para las diferentes categorías y modalidades.</p>
		</div>
		<div class="col-md-4 text-end">
			<!-- Botón para añadir nueva regla (Abre un modal o lleva a otra página) -->
			<button class="btn btn-success shadow-sm" onclick="window.location.href='editar_regla.php'">
				<i class="fas fa-plus"></i> Nueva Regla
			</button>
		</div>
	</div>

	<!-- Tabla de Reglas -->
	<div class="table-responsive shadow-sm rounded">
	<span style="position: relative; display: inline-block; width: 1.2em; text-align: center;">
  <i class="fa fa-solid fa-puzzle-piece text-primary" aria-hidden="true"></i>
  <i class="fa fa-solid fa-magnifying-glass text-warning" style="position: absolute; bottom: -4px; right: -6px; font-size: 0.65em; -webkit-text-stroke: 2px white;" aria-hidden="true"></i>
</span>
   					<span style="position: relative; display: inline-block; width: 1.2em; text-align: center;">
  <i class="fa fa-solid fa-puzzle-piece text-primary" aria-hidden="true"></i>
  <i class="fa fa-solid fa-circle-check text-success" style="position: absolute; bottom: -4px; right: -6px; font-size: 0.65em; background: white; border-radius: 50%;" aria-hidden="true"></i>
</span>
   					<span style="position: relative; display: inline-block; width: 1.2em; text-align: center;">
  <i class="fa fa-solid fa-puzzle-piece text-primary" aria-hidden="true"></i>
  <i class="fa fa-solid fa-triangle-exclamation text-danger" style="position: absolute; top: -6px; right: -8px; font-size: 0.60em; background: white; border-radius: 50%; padding: 1px;" aria-hidden="true"></i>
</span>
   					<i class="fa fa-solid fa-cubes" aria-hidden="true"></i> (Representa la estructura ya montada que se está analizando).
<i class="fa fa-solid fa-list-check" aria-hidden="true"></i> (Un listado con checks, pura auditoría).
<i class="fa fa-solid fa-microscope" aria-hidden="true"></i> (Mirando con lupa la estructura de la Coach Card).
   					<form action="coach_card_auditor.php" method="post" style="display:inline-block; margin: 0 2px;">
    <button class="btn btn-warning" type="submit" name="audit_btn" title="Auditar Normativa">
        <span style="position: relative; display: inline-block; width: 1.2em; text-align: center;">
            <i class="fa fa-solid fa-puzzle-piece text-dark" aria-hidden="true"></i>
            <i class="fa fa-solid fa-magnifying-glass text-primary" style="position: absolute; bottom: -4px; right: -6px; font-size: 0.65em; -webkit-text-stroke: 2px #ffc107;" aria-hidden="true"></i>
        </span>
        <span class="badge text-bg-secondary" style="font-size: 0.9em;"></span>
    </button>
    
    <input type="hidden" name="id_rutina" value="647">
    <input type="hidden" name="id_fase" value="354">
    <input type="hidden" name="id_competicion" value="78">
</form>
   					<form action="coach_card_auditor.php" method="post" style="display:inline-block; margin: 0 2px;">
    <button class="btn btn-warning" type="submit" name="audit_btn" title="Validar Normativa">
        <span style="position: relative; display: inline-block; width: 1.2em; text-align: center;">
            <i class="fa fa-solid fa-puzzle-piece text-dark" aria-hidden="true"></i>
            <i class="fa fa-solid fa-circle-check text-success" style="position: absolute; bottom: -4px; right: -6px; font-size: 0.65em; background: #ffc107; border-radius: 50%; padding: 1px;" aria-hidden="true"></i>
        </span>
        <span class="badge text-bg-secondary" style="font-size: 0.9em;"></span>
    </button>
    
    <input type="hidden" name="id_rutina" value="647">
    <input type="hidden" name="id_fase" value="354">
    <input type="hidden" name="id_competicion" value="78">
</form>
	<form action="coach_card_auditor.php" method="post" style="display:inline-block; margin: 0 2px;">
    <button class="btn btn-warning" type="submit" name="audit_btn" title="Auditar Normativa">
        <span style="position: relative; display: inline-block; width: 1.2em; text-align: center;">
            <i class="fa fa-solid fa-puzzle-piece text-dark" aria-hidden="true"></i>
            <i class="fa fa-solid fa-magnifying-glass text-white" style="position: absolute; bottom: -4px; right: -6px; font-size: 0.65em; -webkit-text-stroke: 2px;" aria-hidden="true"></i>
        </span>
    </button>
    
    <input type="hidden" name="id_rutina" value="647">
    <input type="hidden" name="id_fase" value="354">
    <input type="hidden" name="id_competicion" value="78">
</form>
		<table class="table table-bordered table-hover table-sm align-middle bg-white">
			<thead class="thead-dark text-center align-middle">
				<tr>
					<th scope="col" style="width: 5%;">ID</th>
					<th scope="col">Categoría</th>
					<th scope="col">Modalidad</th>
					<th scope="col" class="text-dark" style="background:#ffc107" title="Elementos Técnicos Requeridos">Max TRE</th>
					<th scope="col" class="text-white" style="background:#007bff" title="Híbridos Libres">Max Híbridos</th>
					<th scope="col" class="text-white" style="background:#0056b3" title="¿Requiere Híbrido Creativo?">H. Creativo</th>
					<th scope="col" class="text-white" style="background:#dc3545" title="Acrobacias (ACRO y ACROPAIR)">Max Acros</th>
					<th scope="col" class="text-white" style="background:#17a2b8" title="Tiempo de Apnea Permitido">Max Apnea (s)</th>
					<th scope="col" style="width: 10%;">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php
                // Consulta que cruza nuestra nueva tabla con tus tablas existentes
                $query_reglas = "
                    SELECT r.*, c.nombre as categoria_nombre, m.nombre as modalidad_nombre 
                    FROM reglas_competicion r
                    JOIN categorias c ON r.id_categoria = c.id
                    JOIN modalidades m ON r.id_modalidad = m.id
                    ORDER BY c.orden ASC, m.id ASC
                ";
                
                $query_run = mysqli_query($connection, $query_reglas);

                if(mysqli_num_rows($query_run) > 0){
                    while ($row = mysqli_fetch_assoc($query_run)) {
                        ?>
				<tr class="text-center">
					<th scope="row"><?php echo $row['id_regla']; ?></th>
					<td class="text-start fw-bold"><?php echo $row['categoria_nombre']; ?></td>
					<td class="text-start"><?php echo $row['modalidad_nombre']; ?></td>

					<!-- Columnas de datos numéricos -->
					<td class="fw-bold"><?php echo $row['max_tre']; ?></td>
					<td class="fw-bold"><?php echo $row['max_hibridos']; ?></td>
					<td>
						<?php if($row['req_hibrido_creativo'] == 1): ?>
						<span class="badge bg-success"><i class="fas fa-check"></i> Sí</span>
						<?php else: ?>
						<span class="badge bg-secondary">No</span>
						<?php endif; ?>
					</td>
					<td class="fw-bold"><?php echo $row['max_acrobacias']; ?></td>
					<td>
						<?php echo !empty($row['max_apnea']) ? $row['max_apnea'] . 's' : '<span class="text-muted">-</span>'; ?>
					</td>

					<!-- Botones de Acción -->
					<td>
						<form action="coach_card_auditor_edit.php" method="POST" style="display:inline;">
    <input type="hidden" name="edit_id" value="<?php echo $row['id_regla']; ?>">
    <button type="submit" name="edit_btn" class="btn btn-sm btn-outline-primary" title="Editar">
        <i class="fas fa-edit"></i>
    </button>
</form>
						<button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="borrarRegla(<?php echo $row['id_regla']; ?>)">
							<i class="fas fa-trash"></i>
						</button>
					</td>
				</tr>
				<?php
                    }
                } else {
                    echo '<tr><td colspan="9" class="text-center py-4">No hay reglas configuradas actualmente en el sistema.</td></tr>';
                }
                ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Script rápido para confirmación de borrado -->
<script>
	function borrarRegla(idRegla) {
		if (confirm("¿Estás seguro de que deseas eliminar la configuración de esta regla? El Coach Card Auditor dejará de evaluar esta categoría/modalidad.")) {
			// Aquí puedes poner la ruta hacia tu script PHP que hace el DELETE
			// window.location.href = "acciones_reglas.php?action=delete&id=" + idRegla;
		}
	}
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>