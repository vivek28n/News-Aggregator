(function () {
  'use strict';

  const API_BASE = 'php/fetch_news.php';

  const CATEGORY_META = {
    all:           { emoji: '🌐', label: 'All News' },
    technology:    { emoji: '💻', label: 'Technology' },
    sports:        { emoji: '⚽', label: 'Sports' },
    business:      { emoji: '📈', label: 'Business' },
    entertainment: { emoji: '🎬', label: 'Entertainment' },
    health:        { emoji: '❤️', label: 'Health' },
    science:       { emoji: '🔬', label: 'Science' },
  };

  let currentCategory = 'all';
  let currentQuery    = '';
  let debounceTimer   = null;
  let isFetching      = false;

  let newsGrid, sectionTitle, demoBadge;
  let heroSearchInput, navSearchInput;

  function buildUrl() {
    const params = new URLSearchParams();

    if (currentQuery) {
      params.set('q', currentQuery);
    } else if (currentCategory && currentCategory !== 'all') {
      params.set('category', currentCategory);
    } else {
      params.set('top', '1');
    }

    return `${API_BASE}?${params.toString()}`;
  }

  async function loadNews() {
    if (isFetching) return;
    isFetching = true;

    renderSkeletons(8);

    if (currentQuery) {
      setSectionTitle(`🔍 Results for: "${currentQuery}"`);
    } else {
      const meta = CATEGORY_META[currentCategory] || CATEGORY_META.all;
      setSectionTitle(`${meta.emoji} ${meta.label}`);
    }

    setSourceBadge(null);

    try {
      const url = buildUrl();
      const res = await fetch(url, {
        method:  'GET',
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout ? AbortSignal.timeout(15000) : undefined,
      });

      if (!res.ok) {
        throw new Error(`Server returned HTTP ${res.status}`);
      }

      const data = await res.json();

      if (!data || data.status !== 'ok') {
        throw new Error('Invalid or unexpected response from server');
      }

      setSourceBadge(data.source, data.api_error);

      const articles = (data.articles || []).filter(
        (a) => a.title && a.title !== '[Removed]'
      );

      if (articles.length === 0) {
        renderEmpty();
      } else {
        renderArticles(articles);
      }

    } catch (err) {
      console.error('[NewsHub] Fetch error:', err);

      if (err.name === 'TimeoutError' || err.name === 'AbortError') {
        renderError('The request timed out. Please check your internet connection.');
      } else if (!navigator.onLine) {
        renderError('You appear to be offline. Please reconnect and try again.');
      } else {
        renderError('Could not load news. Make sure XAMPP Apache is running.');
      }

    } finally {
      isFetching = false;
    }
  }

  function setSourceBadge(source, apiError) {
    if (!demoBadge) return;

    if (source === 'demo') {
      demoBadge.textContent = '📦 Demo Data';
      const errDetail = apiError
        ? ` (${apiError.code}: ${apiError.message})`
        : ' — add your NewsAPI key in php/config.php';
      demoBadge.title    = 'Showing demo data' + errDetail;
      demoBadge.className = 'demo-badge visible';
      demoBadge.style.background = '';
    } else if (source === 'cache') {
      demoBadge.textContent  = '⚡ Cached';
      demoBadge.title        = 'Serving cached API response';
      demoBadge.className    = 'demo-badge visible';
      demoBadge.style.background = 'var(--accent, #4f8ef7)';
    } else if (source === 'newsapi') {
      demoBadge.textContent  = '🟢 Live';
      demoBadge.title        = 'Real-time data from NewsAPI';
      demoBadge.className    = 'demo-badge visible';
      demoBadge.style.background = '#16a34a';
    } else {
      demoBadge.className    = 'demo-badge';
      demoBadge.style.background = '';
    }
  }

  function renderSkeletons(count) {
    if (!newsGrid) return;
    newsGrid.innerHTML = Array.from({ length: count }, () => `
      <div class="skeleton-card">
        <div class="skeleton skeleton-img"></div>
        <div class="skeleton-body">
          <div class="skeleton skeleton-line w-80"></div>
          <div class="skeleton skeleton-line w-60"></div>
          <div class="skeleton skeleton-line w-40"></div>
        </div>
      </div>
    `).join('');
  }

  function renderArticles(articles) {
    if (!newsGrid) return;
    newsGrid.innerHTML = articles.map((article) => buildCard(article)).join('');

    newsGrid.querySelectorAll('.card-bookmark-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        handleBookmarkToggle(btn);
      });
    });
  }

  function buildCard(article) {
    const {
      title       = 'Untitled',
      description = '',
      url         = '#',
      urlToImage  = '',
      publishedAt = '',
      source      = {},
      category    = '',
    } = article;

    const sourceName   = source.name || 'Unknown Source';
    const timeAgo      = formatTimeAgo(publishedAt);
    const catLabel     = category || currentCategory;
    const catMeta      = CATEGORY_META[catLabel] || {};
    const isBookmarked = (typeof Bookmarks !== 'undefined') && Bookmarks.has(article);

    const articleData = encodeURIComponent(JSON.stringify(article));

    const imgHtml = urlToImage
      ? `<img src="${escHtml(urlToImage)}" alt="${escHtml(title)}" loading="lazy"
              onerror="this.parentElement.innerHTML='<div class=\\'card-img-fallback\\'>${catMeta.emoji || '📰'}</div>'">`
      : `<div class="card-img-fallback">${catMeta.emoji || '📰'}</div>`;

    const pillHtml = catLabel && catLabel !== 'all'
      ? `<span class="card-category-pill">${catMeta.emoji || ''} ${catLabel}</span>`
      : '';

    return `
      <article class="card">
        <div class="card-img-wrap">
          ${imgHtml}
          ${pillHtml}
          <button class="card-bookmark-btn ${isBookmarked ? 'bookmarked' : ''}"
                  aria-label="${isBookmarked ? 'Remove bookmark' : 'Save bookmark'}"
                  data-article="${escHtml(articleData)}"
                  title="${isBookmarked ? 'Remove bookmark' : 'Bookmark this article'}">
            ${isBookmarked ? '🔖' : '🏷️'}
          </button>
        </div>
        <div class="card-body">
          <h3><a href="${escHtml(url)}" target="_blank" rel="noopener noreferrer">${escHtml(title)}</a></h3>
          ${description ? `<p>${escHtml(description)}</p>` : ''}
          <div class="card-meta">
            <span class="card-source">${escHtml(sourceName)}</span>
            <time datetime="${escHtml(publishedAt)}">${timeAgo}</time>
          </div>
        </div>
      </article>
    `;
  }

  function renderEmpty() {
    if (!newsGrid) return;
    newsGrid.innerHTML = `
      <div class="state-box">
        <div class="state-icon">🔭</div>
        <h3>No articles found</h3>
        <p>Try a different search term or category.</p>
      </div>
    `;
  }

  function renderError(detail = '') {
    if (!newsGrid) return;
    newsGrid.innerHTML = `
      <div class="state-box">
        <div class="state-icon">⚠️</div>
        <h3>Could not load news</h3>
        <p>${escHtml(detail || 'Make sure XAMPP Apache is running and you have an internet connection.')}</p>
        <button class="btn-retry" onclick="window._newsRetry && window._newsRetry()">
          🔄 Try Again
        </button>
      </div>
    `;
    window._newsRetry = loadNews;
  }

  function setSectionTitle(text) {
    if (sectionTitle) {
      sectionTitle.innerHTML = `<span class="dot"></span> ${text}`;
    }
  }

  function handleBookmarkToggle(btn) {
    if (typeof Bookmarks === 'undefined') return;

    let article;
    try {
      article = JSON.parse(decodeURIComponent(btn.dataset.article));
    } catch {
      return;
    }

    const nowBookmarked = Bookmarks.toggle(article);

    btn.textContent = nowBookmarked ? '🔖' : '🏷️';
    btn.classList.toggle('bookmarked', nowBookmarked);
    btn.setAttribute('aria-label', nowBookmarked ? 'Remove bookmark' : 'Save bookmark');
    btn.title = nowBookmarked ? 'Remove bookmark' : 'Bookmark this article';

    btn.classList.remove('pop');
    void btn.offsetWidth;   // reflow to restart animation
    btn.classList.add('pop');
    btn.addEventListener('animationend', () => btn.classList.remove('pop'), { once: true });

    showToast(
      nowBookmarked ? '🔖 Article bookmarked!' : '🗑️ Bookmark removed',
      nowBookmarked ? 'success' : 'neutral'
    );
  }

  function initTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    if (!tabBtns.length) return;

    tabBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        currentQuery = '';
        if (heroSearchInput) heroSearchInput.value = '';
        if (navSearchInput)  navSearchInput.value  = '';

        tabBtns.forEach((b) => {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');

        currentCategory = btn.dataset.category || 'all';
        loadNews();
      });
    });
  }

  function initSearch() {
    const inputs = [heroSearchInput, navSearchInput].filter(Boolean);

    inputs.forEach((input) => {
      input.addEventListener('input', () => {
        const q = input.value.trim();

        inputs.forEach((i) => { if (i !== input) i.value = q; });

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          currentQuery = q;
          loadNews();
        }, 450);
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          clearTimeout(debounceTimer);
          currentQuery = input.value.trim();
          inputs.forEach((i) => { i.value = currentQuery; });
          loadNews();
        }
      });
    });

    const heroBtn = document.getElementById('hero-search-btn');
    if (heroBtn) {
      heroBtn.addEventListener('click', () => {
        clearTimeout(debounceTimer);
        currentQuery = heroSearchInput ? heroSearchInput.value.trim() : '';
        loadNews();
      });
    }
  }

  function initNavbar() {
    const navbar    = document.querySelector('.navbar');
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobile-nav');

    window.addEventListener('scroll', () => {
      if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });

    if (hamburger && mobileNav) {
      hamburger.addEventListener('click', () => {
        const open = mobileNav.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', String(open));
      });

      mobileNav.querySelectorAll('a').forEach((a) => {
        a.addEventListener('click', () => mobileNav.classList.remove('open'));
      });
    }
  }

  function showToast(message, type = 'neutral', duration = 2800) {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id        = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const icons = { success: '✅', danger: '❌', neutral: 'ℹ️' };

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span class="toast-icon">${icons[type] || icons.neutral}</span> ${escHtml(message)}`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('removing');
      toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }, duration);
  }

  window.showToast = showToast;

  function escHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatTimeAgo(iso) {
    if (!iso) return '';
    const diff = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
    if (diff < 60)    return `${diff}s ago`;
    if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
  }

  document.addEventListener('DOMContentLoaded', () => {
    newsGrid        = document.getElementById('news-grid');
    sectionTitle    = document.getElementById('section-title');
    demoBadge       = document.getElementById('demo-badge');
    heroSearchInput = document.getElementById('hero-search');
    navSearchInput  = document.getElementById('nav-search');

    initNavbar();
    initTabs();
    initSearch();

    if (typeof Preferences !== 'undefined' && Preferences.hasCustom()) {
      const prefs = Preferences.get();
      if (prefs.length > 0) {
        currentCategory = prefs[0];

        const matchingTab = document.querySelector(`.tab-btn[data-category="${currentCategory}"]`);
        if (matchingTab) {
          document.querySelectorAll('.tab-btn').forEach((b) => {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
          });
          matchingTab.classList.add('active');
          matchingTab.setAttribute('aria-selected', 'true');
        }
      }
    }

    loadNews();

    window.addEventListener('online', () => {
      showToast('🌐 Back online! Refreshing news…', 'success');
      loadNews();
    });

    window.addEventListener('offline', () => {
      showToast('📡 You are offline. Showing last loaded data.', 'danger', 4000);
    });
  });

})();
