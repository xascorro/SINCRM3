////deshabilito todo si la fase esta puntuada o los inputs de las rutinas en baja
//$( document ).ready(function() {
//	if ($('#puntuada').attr('puntuada') == 'si'){
//		$('#editable-sample').find('input').each(function() {
//		 	$(this).prop('disabled',true);
//		});
//		$('#editable-sample').find('a').each(function() {
//		 	$(this).hide();
//		});
//		$('.penalizaciones').hide();
//		$('.bajas').hide();
//	} else{
//		$('#editable-sample').find('baja').each(function() {
//			 var id_rutina = $(this).attr('id');
//			 $('#'+id_rutina).find('input').each(function() {
//				 $(this).prop('disabled',true);
//		 });
//
//		});
//	}
//});
//
////despuntuo y desbloqueo la fase
//$("#puntuada").live('click', function() {
//	if(prompt('Introduzca la contraseña para desbloquear la fase','')=='...'){
//		var id_fase = $(this).attr('id_fase');
//		$.post('./puntuaciones_fases_despuntuar.php',{id_fase:id_fase},
//		   	function(){
//				location.reload(true);
//		});
//	} else{
//		$('.alert').show();
//	}
//});
//
////puntuo y bloqueo la fase
//$("#puntuar_fase").live('click', function() {
//		var id_fase = $(this).attr('id_fase');
//		$.post('./puntuaciones_fases_puntuar.php',{id_fase:id_fase},
//		   	function(){
//				location.reload(true);
//		});
//});

//coloreo input activo para destacarlo
var estilo_input = '';
$('input[type="number"]').focus(function() {
	estilo_input = $(this).attr('style');
    $(this).css({'background-color':'#FEFAE0'});
    if(this.value == '0.0')
    	this.value = '';
});
//vuelto input a aspecto original cuando pierde el foco
$('input[type="number"]').blur(function() {
    $(this).attr("style",estilo_input);
    $(this).css({'background-color':'', 'color':''});
    if(this.value == '')
    	this.value = '0';
    else if(this.value > 100)
        this.value = this.value/100;
//    else if(this.value > 10)
//        this.value = this.value/10;
    if (!Number.isInteger(this.value/0.25)){
        $(this).css({'background-color':'#FFCDBA', 'color':'black'});
        this.value = Math.round( this.value/0.25 , 0) * 0.25;

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

////guarda una fila
//$(".guarda_puntuacion").live('click', function() {
//	var id_fase = $(this).attr('fase');
//	var id_rutina =  $(this).parent().parent().attr('id');
//	var id_figura =  $(this).parent().parent().attr('id_figura');
//	var nota = 0.0;
//	var id_nota_media = "";
//	var nota_media = 0.0000;
//	var nota_calculada = 0.0000;
//	var nota_menor = 10.0;
//	var nota_mayor = 0.0;
//	var nota_final = 0.000;
//	var id_nota_menor = "";
//	var id_nota_mayor = "";
//	//obtengo las celdas de la fila desde la que se ha llamado
//	$('#'+id_rutina).find('th').each(function() {
//		//obtengo el input de la celda
//		$(this).find('input').each(function() {
//			//obtengo valores a guardar
//			var id_panel_jueces = $(this).attr('panel_juez');
//			var id_rutina = $(this).attr('id_rutina');
//			nota = $(this).attr('value');
//			nota = nota.replace(',', '.');
//            if (nota > 10)
//				nota = nota/10;
//			nota = parseFloat(nota).toFixed(1);
//
//			//envio notas de puntuaciones_jueces por post, una consulta por nota y juez	 y desabilito el input
//			$.ajax({
//		     type:'POST',
//		      url:'./puntuaciones_jueces_guarda.php',
//		      data:'id_rutina='+id_rutina+'&id_panel_jueces='+id_panel_jueces+'&nota='+nota,
//		   });
//		   $(this).attr('disabled','disabled');
//		   //obtengo nota del panel, nota menor y nota mayor (se restaran despues)
//		   if(nota < nota_menor){
//			   nota_menor = nota;
//			   id_nota_menor = $(this).attr('id');
//		   }
//		   if(nota >= nota_mayor){
//			   nota_mayor = nota;
//			   id_nota_mayor = $(this).attr('id');
//		   }
//		   nota_media = parseFloat(nota_media) + parseFloat(nota);
//		   console.log(nota);
//		});
//		//calculo la nota media del panel restando nota mayor y menor
//		if($(this).attr('class') == 'nota_media'){
//			var panel = $(this).attr('panel');
//		    id_nota_media = $(this).attr('id');
//		    peso = $(this).attr('peso');
//		    numero_jueces = $(this).attr('numero_jueces');
//		    //calculo la nota calculada del panel
////            nota_menor = '0.0';
////            nota_mayor = '0.0';
//		    nota_media = (nota_media - nota_mayor - nota_menor)/(numero_jueces-2);
////		    nota_media = (nota_media)/(numero_jueces);
//		    nota_calculada = nota_media*parseInt(peso)/10;
//		    nota_media = parseFloat(nota_media).toFixed(4);
//		    nota_calculada = parseFloat(nota_calculada).toFixed(4);
//		    $('#'+id_nota_media).html(nota_media+'<br>'+nota_calculada);
//		    console.log('nota_calculada:'+nota_calculada+" nota_mayor:"+nota_mayor+" nota_menor:"+nota_menor);
//		    //saco su valor calculado y lo sumo a la nota final
//		    nota_final = parseFloat(nota_calculada) + parseFloat(nota_final);
//		    nota_final = parseFloat(nota_final).toFixed(4);
//		    //cambio atributos de inputs con valores menor y mayor
//		    $("#"+id_nota_menor).css({'text-decoration':'underline'});
//		    $("#"+id_nota_mayor).css({'text-decoration':'overline'});
//		    //guardo en DB cual es la nota mayor y menor (para informes)
//		    $.post('./puntuaciones_jueces_menor_mayor.php',{id_rutina:id_rutina,id_nota_menor:id_nota_menor,id_nota_mayor:id_nota_mayor,panel:panel,id_fase:id_fase},
//		      function(){
//		 	});
//		    //envio nota media y calculada de paneles por post, una consulta por panel
//			$.post('./puntuaciones_paneles_guarda.php',{id_rutina:id_rutina,id_panel:panel,nota_media:nota_media,nota_calculada:nota_calculada},
//				function(){
//					   console.log("peso:"+peso + " nota_calculada:"+nota_calculada + " nota_final:"+nota_final);
//
//				});
//		   		    //reseteo valores
//		    nota = 0.0;
//		    nota_media = 0.0000;
//		    nota_mayor = 0.0;
//		    nota_menor = 10.0;
//	    }
//	    if($(this).attr('class') == 'nota_final'){
//		    $(this).html(nota_final);
//		    //guardo nota final
//		    actualizar_nota_final_guarda(id_rutina,nota_final);
//		}
//	});
//	$(this).html("<i class='fa fa-lock'></i>");
//});
//
//
////añado penalizaciones
//$("#penaliza_rutina").live('click', function() {
//	var id_rutina = $('#rutina_penalizada').val();
//	var id_penalizacion = $('#rutina_penalizacion').val();
//	var id_fase = $(this).attr('id_fase');
//	$.post('./penalizacion_rutina_guarda.php',{id_rutina:id_rutina,id_penalizacion:id_penalizacion},
//      function(){
//		//actualizo la celda de puntuaciones con las penalizaciones
//		$("#pr_"+id_rutina).load("penalizacion_rutinas_lee.php", "id_rutina="+id_rutina);
//		//refresco por completo la seccion de penalizaciones
//		$('.penalizaciones-body').load('penalizaciones_rutinas_listar.php', "id_fase="+id_fase);
//		//actualizo nota_final
//		actualizar_nota_final(id_rutina);
// 	});
//});
//
//
//
//
//
////borro penalizacion
//$(".penalizacion_borrar").live('click', function() {
//	var id_rutina = $(this).attr('id_rutina');
//	var id_penalizacion = $(this).attr('id_penalizacion');
//	//borro por ajax la penalizacion de la rutina en la db
//	$.post('./penalizacion_rutina_borra.php',{id_rutina:id_rutina,id_penalizacion:id_penalizacion},
//		function(){
//			//oculto la fila de la tabla que la mostraba
//			$('#p'+id_penalizacion).hide();
//			//actualizo la celda de puntuaciones con las penalizaciones
//			$("#pr_"+id_rutina).load("penalizacion_rutinas_lee.php", "id_rutina="+id_rutina);
//			//actualizo nota_final
//			actualizar_nota_final(id_rutina);
//    });
//});
//
////añado baja
//$("#baja_rutina").live('click', function() {
//	var id_fase = $(this).attr('id_fase');
//	var id_rutina = $('#rutina_baja').val();
//	$.post('./baja_rutina_guarda.php',{id_rutina:id_rutina},
//	    function(){
//	    	$('.bajas-body').load('bajas_rutinas_listar.php', "id_fase="+id_fase);
//	    });
//		$('#'+id_rutina).find('input').each(function() {
//			$(this).prop('disabled', true);
//	});
//});
//
////borro baja
//$(".baja_borrar").live('click', function() {
//	var id_rutina = $(this).attr('id_rutina');
//	$.post('./baja_rutina_borra.php',{id_rutina:id_rutina},
//	   	function(){
//			//oculto la fila de la tabla que la mostraba
//			$('#b'+id_rutina).hide();
//			//habilito inputs
//			$('#'+id_rutina).find('input').each(function() {
//			$(this).prop('disabled', false);
//			});
//
//	});
//});
//
////actualiza la nota final en la DB
//function actualizar_nota_final(id_rutina){
//	   $("#nf_"+id_rutina).load("puntuaciones_actualiza_nota_final.php", "id_rutina="+id_rutina, function(){
//	   	   var nota_final = $("#nf_"+id_rutina).html();
//	   	   $.post('./puntuaciones_rutinas_guarda.php',{id_rutina:id_rutina,nota_final:nota_final});
//	   	   console.log(nota_final);
//	   });
//}
////actualiza la nota final en la DB
//function actualizar_nota_final_guarda(id_rutina, nota_final){
//	   $("#nf_"+id_rutina).load("puntuaciones_actualiza_nota_final.php", "id_rutina="+id_rutina, function(){
//	   	   $.post('./puntuaciones_rutinas_guarda.php',{id_rutina:id_rutina,nota_final:nota_final}, function(){
//		   	 $("#nf_"+id_rutina).load("puntuaciones_actualiza_nota_final.php", "id_rutina="+id_rutina);
//	   	   });
//	   	   console.log(nota_final);
//	   });
//}
