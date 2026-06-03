<?php
include('security.php');

$id_rutina = $_SESSION['id_rutina_usuario'] ?? 0;
$id_competicion = $_SESSION['id_competicion_usuario'] ?? 0;

if ($id_rutina == 0) {
    header('Location: rutinas.php');
    exit();
}

include('includes/header.php');
include('includes/navbar.php');

// Fetch Routine Details
$query = "SELECT rutinas.id, rutinas.nombre as nombre_rutina, rutinas.id_fase, rutinas.id_club, 
                 clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, 
                 categorias.nombre as nombre_categoria, modalidades.numero_participantes, 
                 modalidades.numero_reservas 
          FROM rutinas, fases, modalidades, categorias, clubes 
          WHERE rutinas.id=$id_rutina 
          AND rutinas.id_fase = fases.id 
          AND fases.id_modalidad = modalidades.id 
          AND fases.id_categoria = categorias.id 
          AND rutinas.id_club = clubes.id 
          AND fases.id_competicion = ".$id_competicion;

$res_rutina = mysqli_query($connection, $query);
$data = mysqli_fetch_assoc($res_rutina);

if(!$data) {
    header('Location: rutinas.php');
    exit();
}

$nombre_modalidad = $data['nombre_modalidad'];
$nombre_categoria = $data['nombre_categoria'];
$nombre_club = $data['nombre_club'];
$nombre_rutina = $data['nombre_rutina'];
$numero_participantes = $data['numero_participantes'];
$numero_reservas = $data['numero_reservas'];
$id_club_rutina = $data['id_club'];
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-users text-lg"></i></span>
                    Participantes
                </h1>
                <p class="text-slate-500 font-medium"><?php echo $nombre_modalidad." ".$nombre_categoria." - ". $nombre_club.' '.$nombre_rutina; ?></p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="saveAllParticipants()" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> ASIGNAR TODO
                </button>
                <a href="rutinas.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-chevron-left text-xs"></i> Volver
                </a>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <div id="statusMessage" class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-100 flex items-center gap-3 text-blue-700 font-medium transition-all">
            <i class="fas fa-info-circle"></i>
            <span id="statusText">Selecciona las nadadoras para los puestos disponibles.</span>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-10">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-id-badge"></i></span>
                    Titulares (<?php echo $numero_participantes; ?>)
                </h3>
            </div>
            
            <div class="p-8 space-y-4" id="titulares-container">
                <?php for ($x=0; $x<$numero_participantes; $x++): 
                    $query_p = "SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina AND reserva='no' LIMIT $x,1";
                    $participante = mysqli_fetch_assoc(mysqli_query($connection, $query_p));
                    $id_nadadora = $participante['id_nadadora'] ?? 0;
                    $id_registro = $participante['id'] ?? 0;
                ?>
                    <form action="rutinas_participantes_code.php" method="POST" onsubmit="return validateIndividual(this)" class="participant-form grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-4 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-blue-200 transition-colors">
                        <div class="md:col-span-2">
                            <span class="px-3 py-1 bg-blue-600 text-white text-[10px] font-black uppercase rounded-lg tracking-widest">TITULAR <?php echo $x+1; ?></span>
                        </div>
                        <div class="md:col-span-8">
                            <?php 
                            ob_start();
                            include('./includes/nadadoras_select_option.php');
                            $select_html = ob_get_clean();
                            echo str_replace('<select', '<select name="id_nadadora" class="v3-select-fix select-nadadora"', preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html));
                            ?>
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-2">
                            <input type="hidden" name="id" value="<?php echo $id_registro; ?>">
                            <input type="hidden" name="reserva" value="no">
                            <?php if ($id_nadadora > 0): ?>
                                <button type="submit" name="update_btn" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Actualizar">
                                    <i class="fas fa-save text-xs"></i>
                                </button>
                                <button type="submit" name="delete_btn" class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Quitar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="save_btn" class="w-full py-2.5 bg-blue-600 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">
                                    Asignar
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endfor; ?>
            </div>
        </div>

        <?php if($numero_reservas > 0): ?>
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-amber-50/30">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="fas fa-id-badge"></i></span>
                    Reservas (<?php echo $numero_reservas; ?>)
                </h3>
            </div>
            
            <div class="p-8 space-y-4" id="reservas-container">
                <?php for ($x=0; $x<$numero_reservas; $x++): 
                    $query_p = "SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina AND reserva='si' LIMIT $x,1";
                    $participante = mysqli_fetch_assoc(mysqli_query($connection, $query_p));
                    $id_nadadora = $participante['id_nadadora'] ?? 0;
                    $id_registro = $participante['id'] ?? 0;
                ?>
                    <form action="rutinas_participantes_code.php" method="POST" onsubmit="return validateIndividual(this)" class="participant-form grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-4 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-amber-200 transition-colors">
                        <div class="md:col-span-2">
                            <span class="px-3 py-1 bg-amber-500 text-white text-[10px] font-black uppercase rounded-lg tracking-widest">RESERVA <?php echo $x+1; ?></span>
                        </div>
                        <div class="md:col-span-8">
                            <?php 
                            ob_start();
                            include('./includes/nadadoras_select_option.php');
                            $select_html = ob_get_clean();
                            echo str_replace('<select', '<select name="id_nadadora" class="v3-select-fix select-nadadora"', preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html));
                            ?>
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-2">
                            <input type="hidden" name="id" value="<?php echo $id_registro; ?>">
                            <input type="hidden" name="reserva" value="si">
                            <?php if ($id_nadadora > 0): ?>
                                <button type="submit" name="update_btn" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Actualizar">
                                    <i class="fas fa-save text-xs"></i>
                                </button>
                                <button type="submit" name="delete_btn" class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Quitar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="save_btn" class="w-full py-2.5 bg-amber-500 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">
                                    Asignar
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<!-- Formulario Oculto para Guardado Masivo -->
<form id="bulkAssignForm" action="rutinas_participantes_code.php" method="POST" style="display:none;">
    <input type="hidden" name="bulk_save" value="1">
    <div id="bulkInputs"></div>
</form>

<script>
function updateStatus(type, message) {
    const container = document.getElementById('statusMessage');
    const text = document.getElementById('statusText');
    const icon = container.querySelector('i');

    container.className = 'mb-6 p-4 rounded-2xl flex items-center gap-3 font-medium transition-all border';
    
    if (type === 'error') {
        container.classList.add('bg-red-50', 'border-red-200', 'text-red-700');
        icon.className = 'fas fa-exclamation-circle';
    } else if (type === 'success') {
        container.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-700');
        icon.className = 'fas fa-check-circle';
    } else {
        container.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-700');
        icon.className = 'fas fa-info-circle';
    }
    text.textContent = message;
}

function validateIndividual(form) {
    const selects = document.querySelectorAll('.select-nadadora');
    const currentSelect = form.querySelector('.select-nadadora');
    const val = currentSelect.value.trim();
    
    if (val === '' || val === ' ') {
        updateStatus('error', 'Por favor, selecciona una nadadora.');
        return false;
    }

    let duplicateFound = false;
    const swimmerIds = [];

    // Reset visuales inicial
    selects.forEach(s => {
        s.style.cssText = ''; 
    });

    selects.forEach((select) => {
        const sVal = select.value.trim();
        if (sVal !== '' && sVal !== ' ') {
            if (swimmerIds.includes(sVal)) {
                duplicateFound = true;
                // Marcar todos los que tengan este ID
                selects.forEach(s => {
                    if (s.value.trim() === sVal) {
                        s.style.setProperty('border-color', '#ef4444', 'important');
                        s.style.setProperty('background-color', '#fef2f2', 'important');
                        s.style.setProperty('box-shadow', '0 0 0 3px rgba(239, 68, 68, 0.4)', 'important');
                    }
                });
            } else {
                swimmerIds.push(sVal);
            }
        }
    });

    if (duplicateFound) {
        updateStatus('error', 'Error: Se han detectado nadadoras duplicadas. No se puede realizar la asignación.');
        Swal.fire({
            icon: 'error',
            title: 'Validación Fallida',
            text: 'Esta nadadora ya está seleccionada en otro puesto de la rutina.',
            confirmButtonColor: '#3b82f6'
        });
        return false;
    }

    return true;
}

function saveAllParticipants() {
    const selects = document.querySelectorAll('.select-nadadora');
    const bulkInputs = document.getElementById('bulkInputs');
    bulkInputs.innerHTML = ''; 
    
    let duplicateFound = false;
    const swimmerIds = [];
    let count = 0;

    // Reset visuales inicial
    selects.forEach(s => {
        s.style.cssText = ''; 
        s.classList.remove('!border-red-500', '!bg-red-50', '!ring-2', '!ring-red-500');
    });

    selects.forEach((select, index) => {
        const val = select.value.trim();
        const form = select.closest('.participant-form');
        const idRegistro = form.querySelector('input[name="id"]').value;
        const reserva = form.querySelector('input[name="reserva"]').value;

        if (val !== '' && val !== ' ') {
            if (swimmerIds.includes(val)) {
                duplicateFound = true;
                // Marcar todos los que tengan este ID
                selects.forEach(s => {
                    if (s.value.trim() === val) {
                        s.style.setProperty('border-color', '#ef4444', 'important');
                        s.style.setProperty('background-color', '#fef2f2', 'important');
                        s.style.setProperty('box-shadow', '0 0 0 3px rgba(239, 68, 68, 0.4)', 'important');
                    }
                });
            } else {
                swimmerIds.push(val);
                count++;
                bulkInputs.innerHTML += `<input type="hidden" name="participants[${index}][id_nadadora]" value="${val}">`;
                bulkInputs.innerHTML += `<input type="hidden" name="participants[${index}][id_registro]" value="${idRegistro}">`;
                bulkInputs.innerHTML += `<input type="hidden" name="participants[${index}][reserva]" value="${reserva}">`;
            }
        }
    });

    if (duplicateFound) {
        updateStatus('error', 'Error: Se han detectado nadadoras duplicadas en la misma rutina.');
        Swal.fire({
            icon: 'error',
            title: 'Validación Fallida',
            text: 'Una nadadora no puede ser asignada varias veces. Revisa los campos marcados.',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    if (count === 0) {
        updateStatus('info', 'No has seleccionado ninguna nadadora para asignar.');
        return;
    }

    updateStatus('success', `Procesando asignación de ${count} nadadoras...`);
    document.getElementById('bulkAssignForm').submit();
}
</script>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>
