  // Se obtiene una referencia al campo de entrada de búsqueda
  const searchInput = document.getElementById('searchInput');

  // Se obtiene una referencia a la tabla y a las filas de datos
  const dataTable = document.getElementById('dataTable');
  const dataRows = dataTable.getElementsByTagName('tr');

  // Se agrega un evento de escucha al campo de entrada de búsqueda
  searchInput.addEventListener('input', function() {
    const searchText = searchInput.value.toLowerCase();

    // Se itera sobre las filas de datos y se muestra u oculta cada fila según el texto de búsqueda
    for (let i = 0; i < dataRows.length; i++) {
      const rowData = dataRows[i].innerText.toLowerCase();

      if (rowData.includes(searchText)) {
        dataRows[i].style.display = '';
      } else {
        dataRows[i].style.display = 'none';
      }
    }
  });
