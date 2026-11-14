////envio formulario para actualizar puntuaciones despues de eliminar una penalizaición
//$( document ).ready(function() {
//	//recargo la pagina si reload=si
//	var reload = window.location.href.match(/(?<=reload=)(.*?)[^&]+/)[0];
//	console.log('reload:'+reload);
//	if(reload == 'si'){
//		setTimeout( function () {
//        	$('#save_btn').click();
//    	}, 100);
//	}
//});

//coloreo input activo para destacarlo
var estilo_input = '';
$('input[type="number"]').focus(function() {
	estilo_input = $(this).attr('style');
    $(this).css({'background-color':'#FEFAE0'});
    if(this.value == '0.0')
    	this.value = '';
});
//cuando un input tipo numero pierde el foco, saco media si esta vacio y es table-warning y ajusto colores,
$('input[type="number"]').blur(function() {
    $(this).attr("style",estilo_input);
    $(this).css({'background-color':'', 'color':''});
	if($(this).hasClass('table-warning') && this.value == '' ){
		console.log('Entro a sacar media');
		var blurId = this.id;
		var blurValue = this.value;
		var blurParentId = $(this).parent().parent().attr('id');
		// Seleccionamos los inputs dentro de la fila con el ID específico
		var inputs = $("#" + blurParentId + " input[type='number'].form-control.form-control-sm");
		// Creamos un array para almacenar los valores de los inputs
		var valores = [];
		inputs.each(function() {
			if($(this).val() != '')
		  		valores.push($(this).val());
		});
		// Calculamos la suma de todos los valores
		var suma = 0;
		for (var i = 0; i < valores.length; i++) {
		  suma += parseFloat(valores[i]);
			console.log(suma);
		}
		// Calculamos la media y la redondeamos a 1 decimal
		var media = suma / valores.length;
		media = media.toFixed(1);
		console.log('parentId:'+blurParentId+'suma:'+suma+'media:'+media);
		this.value = media;

		$(this).css({'background-color':'#FEFAE0', 'color':'black'});
	}else{
		if(this.value == '')
			this.value = '0.0';
		else if(this.value > 100)
			this.value = this.value/100;
	   else if(this.value > 10)
			this.value = this.value/10;
		if (!Number.isInteger(this.value/0.1)){
			$(this).css({'background-color':'#bee5eb', 'color':'black'});
			console.log(this.value);
			var nota = parseFloat(this.value);
			console.log('parseFloat(nota):'+nota);
			nota = nota.toFixed(1);
			this.value = nota;
			console.log('nota formateada:'+nota);

		} else{
			   $(this).css({'background-color':'#c3e6cb', 'color':'black'});
		}
	}
});

//incremento tab index
 $('input[type="number"]').bind('keyup', function (e) {
     var keyCode = e.keyCode || e.which;
//     console.log($(this).attr('value'));
     //if(keyCode === 13 || ($(this).attr('value') >= 10 && keyCode >= 96 && keyCode <= 105)){
     if(keyCode === 13 ){
         //e.preventDefault();
         $('input, select, textarea,a')
         [$('input,select,textarea,a').index(this)+1].focus();


     }
 });

// Exactamente 4 decimales (con ceros a la derecha)
function puntuacionesFmtHasta4(val) {
	var n = Number(val);
	if (!isFinite(n)) {
		return '';
	}
	return n.toFixed(4);
}

function puntuacionesFlashCelda(td) {
	if (!td) {
		return;
	}
	td.classList.remove('puntuacion-celda-flash');
	void td.offsetWidth;
	td.classList.add('puntuacion-celda-flash');
	td.addEventListener('animationend', function onEnd() {
		td.removeEventListener('animationend', onEnd);
		window.setTimeout(function () {
			td.classList.remove('puntuacion-celda-flash');
		}, 350);
	});
}

// Guardar notas por fila (fetch nativo: no depende del submit ni del orden del DOM)
function puntuacionesListaFigurasGuardarRow(btn) {
	var formId = btn.getAttribute('data-form-id');
	var form = formId ? document.getElementById(formId) : null;
	if (!form || !form.classList.contains('notas')) {
		return;
	}
	var tr = form.closest('tr');
	if (!tr) {
		return;
	}
	btn.disabled = true;
	var fd = new FormData(form);
	fd.append('puntuar_btn', '1');
	fetch(form.getAttribute('action'), {
		method: 'POST',
		body: fd,
		credentials: 'same-origin',
		headers: { 'X-Requested-With': 'XMLHttpRequest' },
	})
		.then(function (r) {
			return r.json().then(function (data) {
				return { ok: r.ok, data: data };
			});
		})
		.then(function (out) {
			var res = out.data;
			if (!res || !res.ok) {
				alert((res && res.message) ? res.message : 'Error al guardar');
				return;
			}
			var sumTd = tr.querySelector('.js-sum-s');
			var tTd = tr.querySelector('.js-nota-total');
			var mTd = tr.querySelector('.js-nota-media');
			var fTd = tr.querySelector('.js-nota-final');
			if (sumTd) {
				sumTd.textContent = res.sumatorio;
			}
			if (tTd) {
				tTd.textContent = res.nota_total;
			}
			if (mTd) {
				mTd.textContent = puntuacionesFmtHasta4(res.nota_media);
				puntuacionesFlashCelda(mTd);
			}
			if (fTd) {
				fTd.textContent = puntuacionesFmtHasta4(res.nota_final);
				puntuacionesFlashCelda(fTd);
			}
			actualizarContadorProgresoDesdeFilas();
			if (res.notas_juez) {
				Object.keys(res.notas_juez).forEach(function (num) {
					var inp = tr.querySelector('input[name="nota[' + num + '][nota]"]');
					if (inp) {
						inp.value = res.notas_juez[num];
					}
				});
			}
		})
		.catch(function () {
			alert('Error de red o respuesta no valida');
		})
		.then(function () {
			btn.disabled = false;
		});
}

document.addEventListener('click', function (e) {
	var btn = e.target.closest && e.target.closest('button.btn-puntuar-fila');
	if (!btn || !btn.getAttribute('data-form-id')) {
		return;
	}
	e.preventDefault();
	puntuacionesListaFigurasGuardarRow(btn);
});

// =========================================================================
// GESTIÓN DE PENALIZACIONES DE FIGURAS (MODAL & AJAX)
// =========================================================================
var currentInscripcionId = 0;
var currentSwimmerName = '';
var currentClubName = '';

window.abrirModalPenalizaciones = function (idInscripcion, nombre, club) {
	currentInscripcionId = idInscripcion;
	currentSwimmerName = nombre;
	currentClubName = club;
	var elName = document.getElementById('modalPenNadadora');
	if (elName) elName.textContent = nombre + ' (' + club + ')';
	var sel = document.getElementById('selectPenalizacion');
	if (sel) sel.value = '0';
	var modal = document.getElementById('modalPenalizaciones');
	if (modal) modal.classList.remove('hidden');
	cargarPenalizacionesModal(idInscripcion);
};

window.cerrarModalPenalizaciones = function () {
	var modal = document.getElementById('modalPenalizaciones');
	if (modal) modal.classList.add('hidden');
	currentInscripcionId = 0;
};

// Cerrar con Escape o clic fuera
document.addEventListener('keydown', function (e) {
	if (e.key === 'Escape' || e.keyCode === 27) {
		cerrarModalPenalizaciones();
	}
});

document.addEventListener('click', function (e) {
	var modal = document.getElementById('modalPenalizaciones');
	if (modal && !modal.classList.contains('hidden') && e.target === modal) {
		cerrarModalPenalizaciones();
	}
});

function cargarPenalizacionesModal(idInscripcion) {
	var listContainer = document.getElementById('modalPenLista');
	if (!listContainer) return;
	listContainer.innerHTML = '<div class="p-4 text-center text-xs font-bold text-slate-400"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando...</div>';

	fetch('puntuaciones_lista_figuras_code.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: new URLSearchParams({
			action: 'get_penalizaciones',
			id_inscripcion_figuras: idInscripcion
		})
	})
	.then(function (r) { return r.json(); })
	.then(function (data) {
		if (!data.ok) {
			listContainer.innerHTML = '<div class="p-3 bg-red-50 text-red-600 text-xs font-bold rounded-xl">' + (data.message || 'Error') + '</div>';
			return;
		}
		renderPenalizacionesList(data.items || [], data.total_puntos || 0);
		actualizarBadgeFila(idInscripcion, data.items || [], data.total_puntos || 0);
	})
	.catch(function () {
		listContainer.innerHTML = '<div class="p-3 bg-red-50 text-red-600 text-xs font-bold rounded-xl">Error de conexión al cargar</div>';
	});
}

function renderPenalizacionesList(items, totalPuntos) {
	var listContainer = document.getElementById('modalPenLista');
	if (!listContainer) return;
	if (!items || items.length === 0) {
		listContainer.innerHTML = '<div class="p-4 bg-slate-50 border border-slate-100 rounded-xl text-center text-xs font-medium text-slate-400 italic">No hay penalizaciones registradas para esta figura.</div>';
		return;
	}
	var html = '';
	items.forEach(function (item) {
		var pts = parseFloat(item.puntos);
		var ptsStr = (pts > 0 ? '-' + pts : pts) + ' pts';
		html += '<div class="p-3 bg-rose-50/70 border border-rose-100 rounded-xl flex items-center justify-between gap-3 animate-fade-in">' +
			'<div class="flex-1 min-w-0">' +
				'<div class="flex items-center gap-2 mb-0.5">' +
					'<span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[9px] font-black rounded uppercase">' + item.codigo + '</span>' +
					'<span class="text-[9px] font-bold text-slate-400 uppercase italic">' + (item.tipo || 'Figuras') + '</span>' +
				'</div>' +
				'<p class="text-xs font-bold text-slate-700 truncate" title="' + item.resumen + '">' + item.resumen + '</p>' +
			'</div>' +
			'<div class="flex items-center gap-3 shrink-0">' +
				'<span class="text-xs font-black text-rose-600 tabular-nums">' + ptsStr + '</span>' +
				'<button type="button" onclick="eliminarPenalizacionModal(' + item.id_pr + ')" class="w-7 h-7 rounded-lg bg-white border border-rose-200 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-2xs cursor-pointer" title="Eliminar penalización">' +
					'<i class="fas fa-trash-alt text-[10px]"></i>' +
				'</button>' +
			'</div>' +
		'</div>';
	});
	listContainer.innerHTML = html;
}

window.guardarPenalizacionModal = function () {
	var select = document.getElementById('selectPenalizacion');
	var idPen = parseInt(select.value, 10);
	if (!idPen || idPen <= 0) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({ icon: 'warning', text: 'Por favor selecciona una penalización del catálogo', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
		} else {
			alert('Por favor selecciona una penalización');
		}
		return;
	}
	var btn = document.getElementById('btnAplicarPenalizacion');
	btn.disabled = true;
	btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Aplicando...';

	fetch('puntuaciones_lista_figuras_code.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: new URLSearchParams({
			action: 'add_penalizacion',
			id_inscripcion_figuras: currentInscripcionId,
			id_penalizacion: idPen
		})
	})
	.then(function (r) { return r.json(); })
	.then(function (data) {
		btn.disabled = false;
		btn.innerHTML = '<i class="fas fa-plus text-xs"></i> Aplicar Penalización';
		if (data.ok) {
			select.value = '0';
			renderPenalizacionesList(data.items || [], data.total_puntos || 0);
			actualizarBadgeFila(currentInscripcionId, data.items || [], data.total_puntos || 0);
			if (typeof Swal !== 'undefined') {
				Swal.fire({ icon: 'success', text: 'Penalización aplicada correctamente', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
			}
		} else {
			if (typeof Swal !== 'undefined') {
				Swal.fire({ icon: 'error', text: data.message || 'Error al aplicar', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
			} else {
				alert(data.message || 'Error al aplicar');
			}
		}
	})
	.catch(function () {
		btn.disabled = false;
		btn.innerHTML = '<i class="fas fa-plus text-xs"></i> Aplicar Penalización';
		if (typeof Swal !== 'undefined') {
			Swal.fire({ icon: 'error', text: 'Error de red al aplicar penalización', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
		} else {
			alert('Error de red');
		}
	});
};

window.eliminarPenalizacionModal = function (idPr) {
	if (!confirm('¿Seguro que deseas eliminar esta penalización?')) return;
	fetch('puntuaciones_lista_figuras_code.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: new URLSearchParams({
			action: 'del_penalizacion',
			id_pr: idPr,
			id_inscripcion_figuras: currentInscripcionId
		})
	})
	.then(function (r) { return r.json(); })
	.then(function (data) {
		if (data.ok) {
			renderPenalizacionesList(data.items || [], data.total_puntos || 0);
			actualizarBadgeFila(currentInscripcionId, data.items || [], data.total_puntos || 0);
			if (typeof Swal !== 'undefined') {
				Swal.fire({ icon: 'info', text: 'Penalización eliminada', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
			}
		} else {
			if (typeof Swal !== 'undefined') {
				Swal.fire({ icon: 'error', text: data.message || 'Error al eliminar', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
			} else {
				alert(data.message || 'Error al eliminar');
			}
		}
	});
};

function actualizarBadgeFila(idInscripcion, items, totalPuntos) {
	var container = document.getElementById('pen_badge_container_' + idInscripcion);
	if (!container) return;
	var count = items ? items.length : 0;
	var escSwimmer = (currentSwimmerName || '').replace(/'/g, "\\'");
	var escClub = (currentClubName || '').replace(/'/g, "\\'");
	if (count > 0) {
		var pts = parseFloat(totalPuntos);
		var ptsStr = (pts > 0 ? '-' + pts : pts) + ' pts';
		container.innerHTML =
			'<button type="button" ' +
				'onclick="abrirModalPenalizaciones(' + idInscripcion + ', \'' + escSwimmer + '\', \'' + escClub + '\')" ' +
				'class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 hover:border-rose-300 rounded-md text-[10px] font-black transition-all active:scale-95 shadow-2xs cursor-pointer" ' +
				'title="Ver o editar penalizaciones">' +
				'<i class="fas fa-gavel text-rose-600 text-[10px]"></i>' +
				'<span>' + count + ' Pen (' + ptsStr + ')</span>' +
			'</button>';
	} else {
		container.innerHTML =
			'<button type="button" ' +
				'onclick="abrirModalPenalizaciones(' + idInscripcion + ', \'' + escSwimmer + '\', \'' + escClub + '\')" ' +
				'class="inline-flex items-center gap-1 px-1.5 py-0.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 rounded-md text-[10px] font-bold transition-all active:scale-95 cursor-pointer" ' +
				'title="Añadir penalización">' +
				'<i class="fas fa-gavel text-[9px]"></i> + Pen' +
			'</button>';
	}
}

// ============================================================
// CONTADOR DINÁMICO DE PROGRESO DE PUNTUACIONES
// ============================================================
function actualizarContadorProgresoDesdeFilas() {
	var todasFilas = document.querySelectorAll('tbody tr[id^="fila_nadadora_"]');
	if (!todasFilas || todasFilas.length === 0) return;

	var puntuadas = 0;
	todasFilas.forEach(function(row) {
		var finalEl = row.querySelector('.js-nota-final');
		var val = finalEl ? finalEl.textContent.trim() : '';
		if (val !== '' && val !== '-') {
			puntuadas++;
		}
	});

	var total = todasFilas.length;
	var pendientes = Math.max(0, total - puntuadas);
	var btn = $('#btn_cerrar_fase');
	btn.attr('data-total', total);
	btn.attr('data-puntuadas', puntuadas);
	btn.attr('data-pendientes', pendientes);
	$('#text_fase_progreso').text(puntuadas + ' / ' + total + ' puntuadas');
}

// ============================================================
// GESTIÓN DINÁMICA DE CIERRE / REAPERTURA DE FASE
// ============================================================
$(document).on('click', '#btn_cerrar_fase', function() {
	var btn = $(this);
	var idFase = btn.attr('data-id-fase');
	var puntuada = btn.attr('data-puntuada'); // 'si' o 'no'
	var pendientes = parseInt(btn.attr('data-pendientes')) || 0;
	var total = parseInt(btn.attr('data-total')) || 0;
	var puntuadas = parseInt(btn.attr('data-puntuadas')) || 0;

	if (puntuada === 'si') {
		// La fase ya está cerrada -> Preguntar si desea reabrirla
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: '¿Reabrir esta fase?',
				html: '<p class="text-sm text-slate-600">La fase está marcada como <b>Terminada</b>. Si la reabres, podrás seguir modificando o guardando puntuaciones.</p>',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: '<i class="fas fa-lock-open mr-1"></i> Sí, reabrir fase',
				cancelButtonText: 'Mantener cerrada',
				confirmButtonColor: '#2563eb',
				cancelButtonColor: '#64748b'
			}).then(function(res) {
				if (res.isConfirmed) {
					ejecutarCambioEstadoFase(idFase, 'no');
				}
			});
		} else {
			if (confirm('La fase está terminada. ¿Deseas reabrirla para edición?')) {
				ejecutarCambioEstadoFase(idFase, 'no');
			}
		}
	} else {
		// La fase está en curso -> Preguntar si desea cerrarla
		var warningHtml = '';
		if (pendientes > 0) {
			warningHtml = '<div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-bold mb-3"><i class="fas fa-exclamation-triangle mr-1"></i> Hay <b>' + pendientes + '</b> nadadora(s) sin puntuar de un total de ' + total + '.</div>';
		} else {
			warningHtml = '<div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-bold mb-3"><i class="fas fa-check-circle mr-1"></i> Todas las nadadoras (<b>' + puntuadas + '/' + total + '</b>) están puntuadas.</div>';
		}

		if (typeof Swal !== 'undefined') {
			Swal.fire({
				title: '¿Cerrar y finalizar fase?',
				html: warningHtml + '<p class="text-sm text-slate-600">Al cerrar la fase quedará registrada como <b>Terminada</b> en la competición y se actualizarán las clasificaciones.</p>',
				icon: pendientes > 0 ? 'warning' : 'question',
				showCancelButton: true,
				confirmButtonText: '<i class="fas fa-flag-checkered mr-1"></i> Sí, finalizar fase',
				cancelButtonText: 'Cancelar',
				confirmButtonColor: '#059669',
				cancelButtonColor: '#64748b'
			}).then(function(res) {
				if (res.isConfirmed) {
					ejecutarCambioEstadoFase(idFase, 'si');
				}
			});
		} else {
			if (confirm('¿Deseas cerrar y finalizar esta fase?')) {
				ejecutarCambioEstadoFase(idFase, 'si');
			}
		}
	}
});

function ejecutarCambioEstadoFase(idFase, nuevoEstado) {
	var btn = $('#btn_cerrar_fase');
	var originalHtml = btn.html();
	btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

	$.ajax({
		url: 'puntuaciones_lista_figuras_code.php',
		type: 'POST',
		dataType: 'json',
		data: {
			action: 'cerrar_fase',
			id_fase: idFase,
			estado: nuevoEstado
		},
		success: function(data) {
			btn.prop('disabled', false);
			if (data && data.ok) {
				var est = data.puntuada; // 'si' o 'no'
				btn.attr('data-puntuada', est);
				if (typeof data.pendientes !== 'undefined') {
					btn.attr('data-pendientes', data.pendientes);
				}
				if (typeof data.total !== 'undefined') {
					btn.attr('data-total', data.total);
				}
				if (typeof data.puntuadas !== 'undefined') {
					btn.attr('data-puntuadas', data.puntuadas);
				}

				actualizarUIFase(est, data.total, data.puntuadas);

				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: est === 'si' ? 'success' : 'info',
						text: data.message || (est === 'si' ? 'Fase cerrada con éxito' : 'Fase reabierta'),
						toast: true,
						position: 'top-end',
						timer: 2500,
						showConfirmButton: false
					});
				}
			} else {
				btn.html(originalHtml);
				var msg = (data && data.message) ? data.message : 'Error al actualizar fase';
				if (typeof Swal !== 'undefined') {
					Swal.fire({ icon: 'error', text: msg, toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
				} else {
					alert(msg);
				}
			}
		},
		error: function() {
			btn.prop('disabled', false).html(originalHtml);
			if (typeof Swal !== 'undefined') {
				Swal.fire({ icon: 'error', text: 'Error de conexión con el servidor', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
			} else {
				alert('Error de conexión con el servidor');
			}
		}
	});
}

function actualizarUIFase(estado, total, puntuadas) {
	var btn = $('#btn_cerrar_fase');
	var badgeStatus = $('#badge_fase_status');
	var iconStatus = $('#icon_fase_status');
	var textStatus = $('#text_fase_status');
	var textProgreso = $('#text_fase_progreso');

	if (estado === 'si') {
		// Terminada
		btn.removeClass('bg-blue-600 hover:bg-blue-500 shadow-blue-500/20')
		   .addClass('bg-emerald-600 hover:bg-emerald-500 shadow-emerald-500/20')
		   .html('<i id="btn_cerrar_icon" class="fas fa-check-circle"></i> <span id="btn_cerrar_text">Fase Terminada</span>');

		badgeStatus.removeClass('bg-amber-50 text-amber-700 border-amber-200')
		           .addClass('bg-emerald-50 text-emerald-700 border-emerald-200');
		iconStatus.removeClass('fa-clock text-amber-500').addClass('fa-circle-check text-emerald-500');
		textStatus.text('Fase Terminada');
	} else {
		// En curso
		btn.removeClass('bg-emerald-600 hover:bg-emerald-500 shadow-emerald-500/20')
		   .addClass('bg-blue-600 hover:bg-blue-500 shadow-blue-500/20')
		   .html('<i id="btn_cerrar_icon" class="fas fa-flag-checkered"></i> <span id="btn_cerrar_text">Cerrar Fase</span>');

		badgeStatus.removeClass('bg-emerald-50 text-emerald-700 border-emerald-200')
		           .addClass('bg-amber-50 text-amber-700 border-amber-200');
		iconStatus.removeClass('fa-circle-check text-emerald-500').addClass('fa-clock text-amber-500');
		textStatus.text('Fase En Curso');
	}

	if (typeof total !== 'undefined' && typeof puntuadas !== 'undefined') {
		textProgreso.text(puntuadas + ' / ' + total + ' puntuadas');
	}
}
