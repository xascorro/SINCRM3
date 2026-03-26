//traducir dataTable, configurar clase no-sort, cambiar paginación por defecto
$('#dataTable').DataTable({
	language: {
		"decimal": "",
		"emptyTable": "No hay información",
		"info": "Mostrando desde _START_ a _END_ de _TOTAL_ resultados",
		"infoEmpty": "Mostrando 0 to 0 of 0 resultados",
		"infoFiltered": "(Filtrado de _MAX_ total entradas)",
		"infoPostFix": "",
		"thousands": ",",
		"lengthMenu": "Mostrar _MENU_ resultados",
		"loadingRecords": "Cargando...",
		"processing": "Procesando...",
		"search": "<i class='fa-solid fa-2x fa-magnifying-glass'></i>",
		"zeroRecords": "Sin resultados encontrados",
		"paginate": {
			"first": "<i class='fa-solid fa-angles-left'></i>",
			"last": "<i class='fa-solid fa-angles-right'></i>",
			"next": "<i class='fa-solid fa-chevron-right'></i>",
			"previous": "<i class='fa-solid fa-chevron-left'></i>"
		}

	},
	"pageLength": 200,
	"lengthMenu": [10, 20, 50, 100, 200, 500],
	"order": [],
	"columnDefs": [{
		"targets": 'no-sort',
		"orderable": false
    }]
});


//comenar con navbar cerrado
$('#accordionSidebar').toggleClass('toggled');

// Lazy loading para imágenes
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    images.forEach(img => imageObserver.observe(img));
});

// Toggle dark mode
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
}

// Cargar preferencia de dark mode
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}

// Optimizar DataTables para móviles
$('#dataTable').DataTable().on('draw', function() {
    if (window.innerWidth < 768) {
        $('.table-responsive').addClass('table-responsive-sm');
    }
});
