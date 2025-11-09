(function() {

    const getItemFromStore = key => localStorage.getItem(key);
    const setItemToStore = (key, value) => localStorage.setItem(key, value);
    const getSystemTheme = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const getData = (el, key) => el.dataset[key];

    const applyTheme = value => {
        if (value === 'auto') {
            document.documentElement.setAttribute('data-bs-theme', getSystemTheme());
        } else {
            document.documentElement.setAttribute('data-bs-theme', value);
        }
    };

    const initialDomSetup = element => {
        if (!element) return;

        element.querySelectorAll('[data-theme-control]').forEach(el => {
            const control = el.dataset.themeControl;
            const localStorageValue = getItemFromStore(control);

            if (control === 'theme') {
                if (!localStorageValue) return;
                if (el.type === 'checkbox') {
                    el.checked = localStorageValue === 'dark';
                } else if (el.type === 'radio') {
                    el.checked = localStorageValue === el.value;
                } else {
                    el.classList.toggle('active', localStorageValue === el.value);
                }
            } else if (el.type === 'select-one' && control === 'navbarPosition') {
                el.value = localStorageValue;
            }
        });

        // Apply saved theme immediately
        applyTheme(getItemFromStore('theme') || 'light');
    };

    const changeTheme = value => {
        applyTheme(value);
        setItemToStore('theme', value);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        initialDomSetup(body);

        const navbarVertical = document.querySelector('.navbar-vertical');

        body.addEventListener('click', e => {
            const target = e.target.closest('[data-theme-control]');
            if (!target) return;

            const control = target.dataset.themeControl;
            let value;

            if (control === 'theme') {
                if (target.type === 'checkbox') {
                    // Determine theme from checkbox: auto is only when not checked
                    value = target.dataset.auto === 'true' ? 'auto' : (target.checked ? 'dark' : 'light');
                } else if (target.type === 'radio') {
                    value = target.value;
                } else {
                    value = target.value;
                }
                changeTheme(value);
            }

            if (control === 'navbarStyle' && navbarVertical) {
                navbarVertical.classList.remove('navbar-card', 'navbar-inverted', 'navbar-vibrant');
                if (target.value !== 'transparent') navbarVertical.classList.add(`navbar-${target.value}`);
                setItemToStore('navbarStyle', target.value);
            }

            if (control === 'reset') {
                Object.keys(window.CONFIG).forEach(key => {
                    setItemToStore(key, window.CONFIG[key]);
                });
                window.location.reload();
            }
        });

        body.addEventListener('change', e => {
            const target = e.target.closest('[data-theme-control="navbarPosition"]');
            if (!target) return;
            setItemToStore('navbarPosition', target.value);

            const pageUrl = target.selectedOptions[0].dataset.pageUrl;
            if (pageUrl) window.location.href = pageUrl;
        });
    });

})();
