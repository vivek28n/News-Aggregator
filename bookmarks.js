(function () {
  'use strict';

  const STORAGE_KEY = 'na_bookmarks';

  function hashKey(str) {
    let h = 0;
    for (let i = 0; i < str.length; i++) {
      h = (Math.imul(31, h) + str.charCodeAt(i)) | 0;
    }
    return 'bm_' + Math.abs(h).toString(36);
  }

  window.Bookmarks = {
    getAll() {
      try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
      } catch {
        return {};
      }
    },

    _save(bms) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(bms));
    },

    has(article) {
      const key = hashKey(article.url || article.title);
      return !!this.getAll()[key];
    },

    add(article) {
      const bms = this.getAll();
      const key = hashKey(article.url || article.title);
      bms[key] = { ...article, _savedAt: Date.now() };
      this._save(bms);
    },

    remove(article) {
      const bms = this.getAll();
      const key = hashKey(article.url || article.title);
      delete bms[key];
      this._save(bms);
    },

    toggle(article) {
      if (this.has(article)) {
        this.remove(article);
        return false;
      } else {
        this.add(article);
        return true;
      }
    },

    list() {
      const bms = this.getAll();
      return Object.values(bms).sort((a, b) => b._savedAt - a._savedAt);
    },

    clearAll() {
      localStorage.removeItem(STORAGE_KEY);
    }
  };
})();
