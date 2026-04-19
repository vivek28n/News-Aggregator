(function () {
  'use strict';

  const STORAGE_KEY    = 'na_preferences';
  const ALL_CATEGORIES = ['technology', 'sports', 'business', 'entertainment', 'health', 'science'];
  const DEFAULT_PREFS  = ['technology', 'sports', 'business'];

  window.Preferences = {
    get() {
      try {
        const raw = JSON.parse(localStorage.getItem(STORAGE_KEY));
        if (Array.isArray(raw) && raw.length > 0) return raw;
        return DEFAULT_PREFS;
      } catch {
        return DEFAULT_PREFS;
      }
    },

    save(categories) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(categories));
    },

    hasCustom() {
      return !!localStorage.getItem(STORAGE_KEY);
    },

    allCategories() {
      return ALL_CATEGORIES;
    }
  };
})();
