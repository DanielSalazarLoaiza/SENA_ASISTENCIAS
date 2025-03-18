// Ejemplo: Validación de formularios
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                alert('Por favor, complete todos los campos requeridos.');
            }
        });
    });
});