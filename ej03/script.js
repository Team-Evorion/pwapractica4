function abrirModal(id, nombre, precio) {
    document.getElementById('producto_id').value = id;
    document.getElementById('infoProductoSeleccionado').innerText = `Producto: ${nombre} - $${precio.toFixed(2)}`;

    const modal = document.getElementById('modalCompra');
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');

    const primerCampo = document.getElementById('nombre');
    if (primerCampo) primerCampo.focus();
}

function cerrarModal() {
    const modal = document.getElementById('modalCompra');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
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

window.addEventListener('click', function(event) {
    const modal = document.getElementById('modalCompra');
    if (event.target === modal) cerrarModal();
});

window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('modalCompra');
        if (modal && modal.style.display === 'flex') cerrarModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    const feedback = document.getElementById('searchFeedback');
    const emptyResults = document.getElementById('noSearchResults');

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLocaleLowerCase('es');
        let visible = 0;

        cards.forEach(function(card) {
            const content = (card.dataset.search || '').toLocaleLowerCase('es');
            const matches = content.includes(query);
            card.hidden = !matches;
            if (matches) visible++;
        });

        if (feedback) {
            feedback.textContent = query ? `${visible} de ${cards.length} producto(s)` : '';
        }
        if (emptyResults) emptyResults.hidden = visible !== 0;
    });
});
