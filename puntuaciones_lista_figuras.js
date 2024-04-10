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
	if($(this).attr("class") == 'form-control form-control-sm table-warning' && this.value == '' ){
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
