(function () {
  'use strict';

  const STORAGE_KEY = 'na_theme';

  const saved      = localStorage.getItem(STORAGE_KEY);
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const isDark     = saved ? saved === 'dark' : prefersDark;

  if (isDark) {
    document.documentElement.setAttribute('data-theme', 'dark');
  }

  window.ThemeManager = {
    toggle(btn) {
      const current = document.documentElement.getAttribute('data-theme');
      const next    = current === 'dark' ? 'light' : 'dark';

      document.documentElement.setAttribute('data-theme', next);
      localStorage.setItem(STORAGE_KEY, next);

      if (btn) {
        btn.classList.remove('spin-once');
        void btn.offsetWidth;   // reflow to restart animation
        btn.classList.add('spin-once');
        btn.addEventListener('animationend', () => btn.classList.remove('spin-once'), { once: true });
      }

      ThemeManager.updateIcon();
    },

    updateIcon() {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.textContent = isDark ? '☀️' : '🌙';
        btn.setAttribute('title', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
        btn.setAttribute('aria-label', btn.getAttribute('title'));
      });
    },

    init() {
      ThemeManager.updateIcon();
    }
  };

  document.addEventListener('DOMContentLoaded', ThemeManager.init);
})();
