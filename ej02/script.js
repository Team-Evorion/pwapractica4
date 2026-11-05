function abrirModal(id, nombre, precio) {
    document.getElementById('producto_id').value = id;
    document.getElementById('infoProductoSeleccionado').innerText = `Producto: ${nombre} - $${precio.toFixed(2)}`;
    document.getElementById('modalCompra').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalCompra').style.display = 'none';
}

function validarFormulario() {
    const nombre = document.getElementById('nombre').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const direccion = document.getElementById('direccion').value.trim();

    if (nombre === "" || correo === "" || direccion === "") {
        alert("Por favor, complete todos los campos del formulario.");
        return false;
    }

    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regexCorreo.test(correo)) {
        alert("Por favor, ingrese un correo electrónico válido.");
        return false;
    }

    return true;
}

window.onclick = function(event) {
    const modal = document.getElementById('modalCompra');
    if (event.target == modal) {
        cerrarModal();
    }
}