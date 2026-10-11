(() => {
    const menuButton = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.primary-navigation');

    if (menuButton && navigation) {
        menuButton.addEventListener('click', () => {
            const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
            menuButton.setAttribute('aria-expanded', String(!isOpen));
            navigation.classList.toggle('open', !isOpen);
        });

        navigation.addEventListener('click', (event) => {
            if (event.target instanceof HTMLAnchorElement) {
                menuButton.setAttribute('aria-expanded', 'false');
                navigation.classList.remove('open');
            }
        });
    }

    document.querySelectorAll('[data-directory]').forEach((directory) => {
        const input = directory.querySelector('[data-table-search]');
        const rows = [...directory.querySelectorAll('[data-table-row]')];
        const resultsLabel = directory.querySelector('[data-results-label]');
        const emptyState = directory.querySelector('[data-empty-state]');

        if (!input || rows.length === 0) return;

        const itemName = document.body.textContent.includes('Customer accounts') ? 'customers' : 'users';

        input.addEventListener('input', () => {
            const query = input.value.trim().toLocaleLowerCase();
            let visibleCount = 0;

            rows.forEach((row) => {
                const isMatch = row.textContent.toLocaleLowerCase().includes(query);
                row.hidden = !isMatch;
                if (isMatch) visibleCount += 1;
            });

            if (resultsLabel) {
                resultsLabel.textContent = query
                    ? `${visibleCount} ${itemName} matched your search`
                    : `Showing all ${rows.length} ${itemName}`;
            }

            if (emptyState) emptyState.hidden = visibleCount !== 0;
        });
    });

    const themeButton = document.querySelector('#theme-toggle');
    if (themeButton) {
        let theme = 'light';
        try { theme = localStorage.getItem('zen-supply-theme') || 'light'; } catch (_) { /* Storage can be disabled. */ }
        const applyTheme = (value) => {
            document.documentElement.dataset.theme = value;
            const dark = value === 'dark';
            themeButton.setAttribute('aria-pressed', String(dark));
            themeButton.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            const label = themeButton.querySelector('.theme-toggle-label');
            if (label) label.textContent = dark ? 'Light mode' : 'Dark mode';
        };
        applyTheme(theme);
        themeButton.addEventListener('click', () => {
            theme = theme === 'dark' ? 'light' : 'dark';
            applyTheme(theme);
            try { localStorage.setItem('zen-supply-theme', theme); } catch (_) { /* Theme remains active for this page. */ }
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.getAttribute('data-confirm') || 'Continue with this change?')) event.preventDefault();
        });
    });

    const saleForm = document.querySelector('[data-sale-form]');
    if (saleForm) {
        const productSelect = saleForm.querySelector('[data-sale-product]');
        const quantityInput = saleForm.querySelector('[data-sale-quantity]');
        const nameOutput = document.querySelector('[data-summary-name]');
        const stockOutput = document.querySelector('[data-summary-stock]');
        const priceOutput = document.querySelector('[data-summary-price]');
        const quantityOutput = document.querySelector('[data-summary-quantity]');
        const totalOutput = document.querySelector('[data-summary-total]');
        const updateSummary = () => {
            const option = productSelect?.selectedOptions[0];
            const price = Number(option?.dataset.price || 0);
            const stock = Number(option?.dataset.stock || 0);
            const quantity = Math.max(1, Number(quantityInput?.value || 1));
            if (nameOutput) nameOutput.textContent = option?.value ? option.textContent.split(' — ')[0] : 'Select a product';
            if (stockOutput) stockOutput.textContent = option?.value ? `${stock} unit(s) available` : 'Available stock will show here';
            if (priceOutput) priceOutput.textContent = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price);
            if (quantityOutput) quantityOutput.textContent = String(quantity);
            if (totalOutput) totalOutput.textContent = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price * quantity);
            if (quantityInput && option?.value) quantityInput.max = String(stock);
        };
        productSelect?.addEventListener('change', updateSummary);
        quantityInput?.addEventListener('input', updateSummary);
        updateSummary();
    }
})();
