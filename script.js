document.addEventListener('DOMContentLoaded', function () {
    const scrollStorageKey = 'ube-scroll-position';

    function createQuickNavigation() {
        if (!document.querySelector('.site-header')) return;

        const pathname = window.location.pathname;
        const cartLink = document.querySelector('.cart-link');
        const cartPage = document.querySelector('.cart-page');
        const isAuthenticated = Boolean(cartLink || cartPage);
        const cartCount = cartLink?.querySelector('.cart-count')?.textContent?.trim() || '';
        const quickNav = document.createElement('nav');

        quickNav.className = 'quick-nav';
        quickNav.setAttribute('aria-label', 'Acceso rápido');
        quickNav.innerHTML = `
            <a href="index.php" class="${pathname.endsWith('/index.php') || pathname.endsWith('/pwapractica4/') ? 'active' : ''}">
                <span aria-hidden="true">⌂</span><small>Inicio</small>
            </a>
            <a href="productos.php" class="${pathname.endsWith('/productos.php') ? 'active' : ''}">
                <span aria-hidden="true">▦</span><small>Productos</small>
            </a>
            <a href="${isAuthenticated ? 'carrito.php' : 'login.php'}" class="${pathname.endsWith('/carrito.php') ? 'active' : ''}">
                <span aria-hidden="true">${isAuthenticated ? '◫' : '○'}</span>
                <small>${isAuthenticated ? 'Carrito' : 'Entrar'}</small>
                ${cartCount ? `<b>${cartCount}</b>` : ''}
            </a>
            <button type="button" data-scroll-top aria-label="Volver arriba">
                <span aria-hidden="true">↑</span><small>Arriba</small>
            </button>
        `;

        document.body.appendChild(quickNav);

        function updateQuickNavigation() {
            const visible = window.scrollY > 430;
            quickNav.classList.toggle('quick-nav-visible', visible);
            document.body.classList.toggle('quick-nav-active', visible);
        }

        quickNav.querySelector('[data-scroll-top]').addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        window.addEventListener('scroll', updateQuickNavigation, { passive: true });
        updateQuickNavigation();
    }

    createQuickNavigation();

    function saveScrollPosition() {
        sessionStorage.setItem(scrollStorageKey, JSON.stringify({
            path: window.location.pathname,
            y: window.scrollY,
            createdAt: Date.now()
        }));
    }

    try {
        const savedScroll = JSON.parse(sessionStorage.getItem(scrollStorageKey) || 'null');
        if (
            savedScroll &&
            savedScroll.path === window.location.pathname &&
            Date.now() - savedScroll.createdAt < 10000
        ) {
            sessionStorage.removeItem(scrollStorageKey);
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    window.scrollTo({ top: savedScroll.y, behavior: 'instant' });
                });
            });
        }
    } catch (error) {
        sessionStorage.removeItem(scrollStorageKey);
    }

    const searchInput = document.getElementById('productSearch');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    const feedback = document.getElementById('searchFeedback');
    const emptyResults = document.getElementById('noSearchResults');

    if (searchInput) {
        function filterProducts() {
            const query = searchInput.value.trim().toLocaleLowerCase('es');
            let visible = 0;

            cards.forEach(function (card) {
                const content = (card.dataset.search || '').toLocaleLowerCase('es');
                const matches = content.includes(query);
                card.hidden = !matches;
                if (matches) visible++;
            });

            if (feedback) {
                feedback.textContent = query !== ''
                    ? `${visible} de ${cards.length} producto(s)`
                    : `${cards.length} producto(s)`;
            }
            if (emptyResults) emptyResults.hidden = visible !== 0;
        }

        searchInput.addEventListener('input', filterProducts);
    }

    document.querySelectorAll('[data-toast]').forEach(function (toast) {
        const closeButton = toast.querySelector('[data-toast-close]');
        let dismissed = false;

        function dismissToast() {
            if (dismissed) return;
            dismissed = true;
            toast.classList.add('toast-hiding');
            window.setTimeout(function () {
                toast.remove();
            }, 260);
        }

        closeButton?.addEventListener('click', dismissToast);
        window.setTimeout(dismissToast, 4200);
    });

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('.add-cart-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.submitting === 'true') return;

            const cartTarget = document.querySelector('.cart-link');
            const productImage = form.closest('.product-card')?.querySelector('.product-image img');
            const submitButton = event.submitter || form.querySelector('button[type="submit"]');

            if (reduceMotion || !cartTarget || !productImage || typeof productImage.animate !== 'function') {
                saveScrollPosition();
                return;
            }

            event.preventDefault();
            saveScrollPosition();
            form.dataset.submitting = 'true';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('is-loading');
            }

            const imageRect = productImage.getBoundingClientRect();
            const targetRect = cartTarget.getBoundingClientRect();
            const flyingImage = productImage.cloneNode(true);

            flyingImage.className = 'flying-cart-image';
            flyingImage.style.left = `${imageRect.left}px`;
            flyingImage.style.top = `${imageRect.top}px`;
            flyingImage.style.width = `${imageRect.width}px`;
            flyingImage.style.height = `${imageRect.height}px`;
            document.body.appendChild(flyingImage);

            const cartIsVisible = targetRect.bottom > 0 && targetRect.top < window.innerHeight;
            const targetX = cartIsVisible ? targetRect.left + targetRect.width / 2 : window.innerWidth - 34;
            const targetY = cartIsVisible ? targetRect.top + targetRect.height / 2 : 24;
            const moveX = targetX - (imageRect.left + imageRect.width / 2);
            const moveY = targetY - (imageRect.top + imageRect.height / 2);

            const animation = flyingImage.animate([
                { transform: 'translate(0, 0) scale(1)', opacity: 1 },
                { transform: `translate(${moveX * .55}px, ${moveY * .35}px) scale(.55)`, opacity: .9, offset: .55 },
                { transform: `translate(${moveX}px, ${moveY}px) scale(.12)`, opacity: .15 }
            ], {
                duration: 620,
                easing: 'cubic-bezier(.35,.8,.3,1)',
                fill: 'forwards'
            });

            animation.finished
                .catch(function () {})
                .finally(function () {
                    flyingImage.remove();
                    cartTarget.classList.add('cart-bump');
                    form.requestSubmit(submitButton);
                });
        });
    });

    document.querySelectorAll('.cart-page form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (event.submitter?.name !== 'finalizar_compra') {
                saveScrollPosition();
            }
        });
    });

    const currentUrl = new URL(window.location.href);
    if (currentUrl.searchParams.has('carrito') || currentUrl.searchParams.has('estado')) {
        currentUrl.searchParams.delete('carrito');
        currentUrl.searchParams.delete('estado');
        window.history.replaceState({}, '', currentUrl.pathname + currentUrl.search + currentUrl.hash);
    }
});
