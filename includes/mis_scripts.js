$(document).ready(function() {
    // Traducir DataTable y configurar opciones por defecto
    if ($('#dataTable').length) {
        $('#dataTable').DataTable({
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ resultados",
                "infoEmpty": "Mostrando 0 a 0 de 0 resultados",
                "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ resultados",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "<i class='fa-solid fa-magnifying-glass opacity-50'></i>",
                "searchPlaceholder": "Buscar...",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "<i class='fa-solid fa-angles-left'></i>",
                    "last": "<i class='fa-solid fa-angles-right'></i>",
                    "next": "<i class='fa-solid fa-chevron-right'></i>",
                    "previous": "<i class='fa-solid fa-chevron-left'></i>"
                }
            },
            "pageLength": 50,
            "lengthMenu": [10, 25, 50, 100, 200, 500],
            "order": [],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false
            }],
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        });
    }

    // El toggle manual del sidebar se ha eliminado para evitar conflictos con sb-admin-2.js
});
