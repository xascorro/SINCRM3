//envio formulario para actualizar puntuaciones despues de eliminar una penalizaición
$( document ).ready(function() {
	//recargo la pagina si reload=si
	var urlParams = new URLSearchParams(window.location.search);
	var reload = urlParams.get('reload');
	if(reload == 'si'){
		setTimeout( function () {
        	$('#save_btn').click();
    	}, 100);
	}
});

//coloreo input activo para destacarlo
var estilo_input = '';
$('input.form-control').focus(function() {
	estilo_input = $(this).attr('style');
    $(this).css({'background-color':'#fef3c7', 'border-color':'#f59e0b'}); // Tailwind amber-100/amber-500
    if(this.value == '0.0' || this.value == '0')
    	this.value = '';
    
    // Seleccionar texto al entrar para facilitar sobreescritura
    this.select();
});

//cuando un input tipo numero pierde el foco, saco media si esta vacio y es juez-media y ajusto colores,
$('input.form-control').blur(function() {
    $(this).attr("style",estilo_input);
    $(this).css({'background-color':'', 'color':'', 'border-color':''});

    var val = this.value.trim();
    if(val === '') return;

    // 1. Normalizar: Cambiar comas por puntos
    val = val.replace(/,/g, '.');

    // 2. Lógica de corrección inteligente de decimales
    var num = parseFloat(val);
    if (!isNaN(num)) {
        // Caso: 35 -> 3.5 (Solo si es mayor que 10, asumiendo error de tecleo sin punto)
        if (num > 10.0) {
            num = num / 10.0;
        }
        
        // Redondear al cuarto más cercano (0.25)
        if (!Number.isInteger(num / 0.25)) {
            num = Math.round(num / 0.25) * 0.25;
            $(this).css({'background-color':'#dbeafe', 'color':'#1d4ed8'}); // Tailwind blue-100
        } else {
            $(this).css({'background-color':'#d1fae5', 'color':'#047857'}); // Tailwind emerald-100
        }
        
        // Forzar formato con un decimal mínimo (1 -> 1.0)
        this.value = num.toFixed(2).replace(/\.00$/, '.0');
    }

    // 3. Lógica de Media automática para Juez 108
	if($(this).hasClass('juez-media') && (this.value == '' || this.value == '0.0') ){
		console.log('Entro a sacar media');
		var blurId = this.id;
        var prefix = '';
        var suffix = '';

        // Detectar si es Elemento o IA
        if(blurId.startsWith('notaE')) {
            // Ejemplo: notaE1J5 -> Elemento 1, Juez 5
            var match = blurId.match(/notaE(\d+)J(\d+)/);
            if(match) {
                var elNum = match[1];
                var n1 = parseFloat($('#notaE'+elNum+'J1').val()) || 0;
                var n2 = parseFloat($('#notaE'+elNum+'J2').val()) || 0;
                var n3 = parseFloat($('#notaE'+elNum+'J3').val()) || 0;
                var n4 = parseFloat($('#notaE'+elNum+'J4').val()) || 0;
                var media = (n1+n2+n3+n4)/4;
                media = Math.round(media/0.25) * 0.25;
                this.value = media.toFixed(2).replace(/\.00$/, '.0');
            }
        } else {
            // Ejemplo: notaChoMuJ5
            var match = blurId.match(/nota([a-zA-Z]+)J(\d+)/);
            if(match) {
                var type = match[1];
                var n1 = parseFloat($('#nota'+type+'J1').val()) || 0;
                var n2 = parseFloat($('#nota'+type+'J2').val()) || 0;
                var n3 = parseFloat($('#nota'+type+'J3').val()) || 0;
                var n4 = parseFloat($('#nota'+type+'J4').val()) || 0;
                var media = (n1+n2+n3+n4)/4;
                media = Math.round(media/0.25) * 0.25;
                this.value = media.toFixed(2).replace(/\.00$/, '.0');
            }
        }
		$(this).css({'background-color':'#fef3c7', 'color':'#b45309'}); // Tailwind amber-100
	}
});

// Navegación inteligente con Enter (respetando tabindex vertical)
$('input.form-control').bind('keydown', function (e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault(); 
        var currentTabIndex = parseInt($(this).attr('tabindex')) || 0;
        
        if (currentTabIndex > 0) {
            var nextElement = null;
            var nextTabIndex = Infinity;
            
            // Buscar el elemento con el tabindex inmediatamente superior
            $('[tabindex]').each(function() {
                var ti = parseInt($(this).attr('tabindex'));
                if (ti > currentTabIndex && ti < nextTabIndex && !$(this).prop('disabled') && $(this).is(':visible')) {
                    nextTabIndex = ti;
                    nextElement = this;
                }
            });
            
            if (nextElement) {
                $(nextElement).focus();
            } else {
                $('#save_btn').focus();
            }
        }
    }
});
