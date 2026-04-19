<?php require_once 'php/auth.php'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Bookmarks — NewsHub</title>
  <meta name="description" content="View and manage your saved news article bookmarks." />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/animations.css" />
  <script src="js/theme.js"></script>
</head>
<body>

<nav class="navbar" id="main-navbar">
  <div class="container">
    <a href="index.php" class="nav-logo">
      <div class="nav-logo-icon">📰</div>
      <span class="nav-logo-text">NewsHub</span>
    </a>

    <ul class="nav-links" role="list">
      <li><a href="index.php">Home</a></li>
      <li><a href="bookmarks.php" class="active">Bookmarks</a></li>
      <li><a href="preferences.php">Preferences</a></li>
    </ul>

    <div class="nav-actions">
      <button class="theme-btn" id="theme-toggle"
              onclick="ThemeManager.toggle(this)"
              aria-label="Toggle dark mode">🌙</button>

      <a href="php/logout.php" class="btn btn-outline" id="logout-btn"
         style="font-size:0.82rem; padding:0.4rem 0.9rem;"
         title="Sign out">🚪 Logout</a>

      <button class="hamburger" id="hamburger"
              aria-label="Toggle mobile menu"
              aria-expanded="false"
              aria-controls="mobile-nav">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<div class="nav-mobile" id="mobile-nav">
  <a href="index.php">🏠 Home</a>
  <a href="bookmarks.php" class="active">🔖 Bookmarks</a>
  <a href="preferences.php">⚙️ Preferences</a>
  <a href="php/logout.php">🚪 Logout</a>
</div>

<header class="page-hero">
  <div class="container">
    <h1>🔖 My Bookmarks</h1>
    <p id="bm-count-text">Loading your saved articles…</p>
  </div>
</header>

<main class="news-section">
  <div class="container">

    <div class="bookmark-toolbar" id="bm-toolbar" style="display:none;">
      <h2 class="section-title" id="bm-section-title">
        <span class="dot"></span> Saved Articles
      </h2>
      <button class="btn btn-danger" id="bm-clear-btn">🗑️ Clear All</button>
    </div>

    <div class="news-grid" id="news-grid" role="list" aria-live="polite" aria-label="Bookmarked articles"></div>

  </div>
</main>

<footer class="footer">
  <div class="container">
    <p class="footer-text">
      📰 <strong>NewsHub</strong> — Web Technologies Lab Project<br>
      Powered by <a href="https://newsapi.org" target="_blank" rel="noopener">NewsAPI</a>
      &amp; built with HTML, CSS, JavaScript &amp; PHP
    </p>
  </div>
</footer>

<script src="js/bookmarks.js"></script>
<script src="js/preferences.js"></script>

<script>
(function () {
  'use strict';

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
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
    if (diff < 60)    return `${diff}s ago`;
    if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
  }

  const CATEGORY_EMOJI = {
    technology: '💻', sports: '⚽', business: '📈',
    entertainment: '🎬', health: '❤️', science: '🔬', general: '🌐'
  };

  function buildCard(article) {
    const { title='', description='', url='#', urlToImage='',
            publishedAt='', source={}, category='' } = article;
    const sourceName  = source.name || 'Unknown';
    const timeAgo     = formatTimeAgo(publishedAt);
    const emoji       = CATEGORY_EMOJI[category] || '📰';
    const articleData = encodeURIComponent(JSON.stringify(article));

    const imgHtml = urlToImage
      ? `<img src="${escHtml(urlToImage)}" alt="${escHtml(title)}" loading="lazy"
              onerror="this.parentElement.innerHTML='<div class=\\'card-img-fallback\\'>${emoji}</div>'">`
      : `<div class="card-img-fallback">${emoji}</div>`;

    return `
      <article class="card" data-url="${escHtml(url)}">
        <div class="card-img-wrap">
          ${imgHtml}
          ${category ? `<span class="card-category-pill">${emoji} ${category}</span>` : ''}
          <button class="card-bookmark-btn bookmarked"
                  aria-label="Remove bookmark"
                  data-article="${escHtml(articleData)}"
                  title="Remove bookmark">🔖</button>
        </div>
        <div class="card-body">
          <h3><a href="${escHtml(url)}" target="_blank" rel="noopener">${escHtml(title)}</a></h3>
          ${description ? `<p>${escHtml(description)}</p>` : ''}
          <div class="card-meta">
            <span class="card-source">${escHtml(sourceName)}</span>
            <time datetime="${escHtml(publishedAt)}">${timeAgo}</time>
          </div>
        </div>
      </article>
    `;
  }

  function render() {
    const grid         = document.getElementById('news-grid');
    const countText    = document.getElementById('bm-count-text');
    const toolbar      = document.getElementById('bm-toolbar');
    const sectionTitle = document.getElementById('bm-section-title');

    if (!grid) return;

    const articles = Bookmarks.list();

    if (articles.length === 0) {
      if (toolbar) toolbar.style.display = 'none';
      if (countText) countText.textContent = 'You have no saved bookmarks yet.';
      grid.innerHTML = `
        <div class="state-box">
          <div class="state-icon">🔭</div>
          <h3>No bookmarks yet</h3>
          <p>Browse the news feed and tap 🏷️ to save articles here.</p>
          <a href="index.php" class="btn btn-primary" style="margin-top:1rem;">Browse News</a>
        </div>`;
      return;
    }

    if (toolbar)      toolbar.style.display = 'flex';
    if (countText)    countText.textContent  = `You have ${articles.length} saved article${articles.length !== 1 ? 's' : ''}.`;
    if (sectionTitle) sectionTitle.innerHTML = `<span class="dot"></span> ${articles.length} Saved Article${articles.length !== 1 ? 's' : ''}`;

    grid.innerHTML = articles.map(buildCard).join('');

    grid.querySelectorAll('.card-bookmark-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        let article;
        try { article = JSON.parse(decodeURIComponent(btn.dataset.article)); } catch { return; }

        Bookmarks.remove(article);
        const card = btn.closest('.card');
        if (card) {
          card.style.transition = 'opacity 0.3s, transform 0.3s';
          card.style.opacity    = '0';
          card.style.transform  = 'scale(0.95)';
          setTimeout(() => { card.remove(); render(); }, 320);
        }
        showToast('🗑️ Bookmark removed');
      });
    });
  }

  function showToast(msg) {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const t = document.createElement('div');
    t.className   = 'toast';
    t.textContent = msg;
    container.appendChild(t);
    setTimeout(() => {
      t.classList.add('removing');
      t.addEventListener('animationend', () => t.remove(), { once: true });
    }, 2500);
  }

  function initNavbar() {
    const navbar    = document.querySelector('.navbar');
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobile-nav');
    window.addEventListener('scroll', () => {
      if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });
    if (hamburger && mobileNav) {
      hamburger.addEventListener('click', () => mobileNav.classList.toggle('open'));
    }
  }

  function initClearBtn() {
    const btn = document.getElementById('bm-clear-btn');
    if (!btn) return;
    btn.addEventListener('click', () => {
      if (confirm('Remove all bookmarks? This cannot be undone.')) {
        Bookmarks.clearAll();
        render();
        showToast('🗑️ All bookmarks cleared');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initClearBtn();
    render();
  });
})();
</script>
</body>
</html>
