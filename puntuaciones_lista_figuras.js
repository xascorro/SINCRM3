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
