/* -------------------------------------------------------------------------- */
/*                              Config (Browser Safe)                         */
/* -------------------------------------------------------------------------- */

(function () {
  const CONFIG = {
    isNavbarVerticalCollapsed: false,
    theme: 'light',
    isRTL: false,
    isFluid: false,
    navbarStyle: 'transparent',
    navbarPosition: 'vertical'
  };

  // Initialize localStorage with defaults if not set
  Object.keys(CONFIG).forEach(key => {
    if (localStorage.getItem(key) === null) {
      localStorage.setItem(key, CONFIG[key]);
    }
  });

  // Apply collapsed navbar setting
  if (!!JSON.parse(localStorage.getItem('isNavbarVerticalCollapsed'))) {
    document.documentElement.classList.add('navbar-vertical-collapsed');
  }

  // Apply theme (light, dark, or auto)
  const theme = localStorage.getItem('theme');
  if (theme === 'dark') {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
  } else if (theme === 'auto') {
    document.documentElement.setAttribute(
      'data-bs-theme',
      window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    );
  }

  // ✅ Expose globally for other scripts (theme-control.js, theme-dashboard.js, etc.)
  window.CONFIG = CONFIG;
})();
