document.addEventListener('DOMContentLoaded', function() {
    // Función para confirmar eliminación
    function confirmDelete(e) {
        if (!confirm("¿Estás seguro de que quieres eliminar este registro?")) {
            e.preventDefault();
        }
    }

    // Asociamos la función a todos los botones de eliminación
    const deleteButtons = document.querySelectorAll('.delete-button');
    deleteButtons.forEach(button => {
        button.addEventListener('click', confirmDelete);
    });
});
