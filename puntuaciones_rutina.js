//envio formulario para actualizar puntuaciones despues de eliminar una penalizaición
$( document ).ready(function() {
	//recargo la pagina si reload=si
	var reload = window.location.href.match(/(?<=reload=)(.*?)[^&]+/)[0];
	console.log('reload:'+reload);
	if(reload == 'si'){
		setTimeout( function () {
        	$('#save_btn').click();
    	}, 100);
	}
});

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
	if($(this).attr("class") == 'form-control  table-warning' && this.value == '' ){
		console.log('Entro a sacar media');
		var blurId = this.id;
		var blurValue = this.value;

		console.log('id:'+blurId+' valor:'+blurValue);
		if(blurId == 'notaE1J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE1J1').val());
			var n2 = parseFloat($('#notaE1J2').val());
			var n3 = parseFloat($('#notaE1J3').val());
			var n4 = parseFloat($('#notaE1J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE1J5').val(notaMedia);
			console.log('pongo notaMedia:'+notaMedia);
		}else if (blurId == 'notaE2J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE2J1').val());
			var n2 = parseFloat($('#notaE2J2').val());
			var n3 = parseFloat($('#notaE2J3').val());
			var n4 = parseFloat($('#notaE2J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE2J5').val(notaMedia);
			$(this).css({'background-color':'#FEFAE0', 'color':'black'});
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaE3J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE3J1').val());
			var n2 = parseFloat($('#notaE3J2').val());
			var n3 = parseFloat($('#notaE3J3').val());
			var n4 = parseFloat($('#notaE3J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE3J5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaE4J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE4J1').val());
			var n2 = parseFloat($('#notaE4J2').val());
			var n3 = parseFloat($('#notaE4J3').val());
			var n4 = parseFloat($('#notaE4J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE4J5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaE5J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE5J1').val());
			var n2 = parseFloat($('#notaE5J2').val());
			var n3 = parseFloat($('#notaE5J3').val());
			var n4 = parseFloat($('#notaE5J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE5J5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaE6J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE6J1').val());
			var n2 = parseFloat($('#notaE6J2').val());
			var n3 = parseFloat($('#notaE6J3').val());
			var n4 = parseFloat($('#notaE6J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE6J5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaE7J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE7J1').val());
			var n2 = parseFloat($('#notaE7J2').val());
			var n3 = parseFloat($('#notaE7J3').val());
			var n4 = parseFloat($('#notaE7J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE7J5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaE8J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE8J1').val());
			var n2 = parseFloat($('#notaE8J2').val());
			var n3 = parseFloat($('#notaE8J3').val());
			var n4 = parseFloat($('#notaE8J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE8J5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaE9J5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaE9J1').val());
			var n2 = parseFloat($('#notaE9J2').val());
			var n3 = parseFloat($('#notaE9J3').val());
			var n4 = parseFloat($('#notaE9J4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaE9J5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaChoMuJ5'){
			console.log('ChoMu');
			var notaMedia = 0;
			var n1 = parseFloat($('#notaChoMuJ1').val());
			var n2 = parseFloat($('#notaChoMuJ2').val());
			var n3 = parseFloat($('#notaChoMuJ3').val());
			var n4 = parseFloat($('#notaChoMuJ4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaChoMuJ5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaPerformanceJ5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaPerformanceJ1').val());
			var n2 = parseFloat($('#notaPerformanceJ2').val());
			var n3 = parseFloat($('#notaPerformanceJ3').val());
			var n4 = parseFloat($('#notaPerformanceJ4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaPerformanceJ5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}else if (blurId == 'notaTransitionsJ5'){
			var notaMedia = 0;
			var n1 = parseFloat($('#notaTransitionsJ1').val());
			var n2 = parseFloat($('#notaTransitionsJ2').val());
			var n3 = parseFloat($('#notaTransitionsJ3').val());
			var n4 = parseFloat($('#notaTransitionsJ4').val());
			notaMedia = (n1+n2+n3+n4)/4;
			notaMedia = Math.round( notaMedia/0.25 , 0) * 0.25;
			$('#notaTransitionsJ5').val(notaMedia);
			console.log('notaMedia:'+notaMedia);
		}
		$(this).css({'background-color':'#FEFAE0', 'color':'black'});
	}else if (this.id != 'errores_pequenos' && this.id != 'errores_obvios' && this.id != 'errores_mayores'){
		if(this.value == '')
			this.value = '0.0';
		else if(this.value > 100)
			this.value = this.value/100;
	   else if(this.value > 10)
			this.value = this.value/10;
		if (!Number.isInteger(this.value/0.25)){
			$(this).css({'background-color':'#bee5eb', 'color':'black'});
			this.value = Math.round( this.value/0.25 , 0) * 0.25;

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
