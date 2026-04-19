<?php require_once 'php/auth.php'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Preferences — NewsHub</title>
  <meta name="description" content="Customize your NewsHub feed by selecting your favourite news categories." />

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
      <li><a href="bookmarks.php">Bookmarks</a></li>
      <li><a href="preferences.php" class="active">Preferences</a></li>
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
  <a href="bookmarks.php">🔖 Bookmarks</a>
  <a href="preferences.php" class="active">⚙️ Preferences</a>
  <a href="php/logout.php">🚪 Logout</a>
</div>

<header class="page-hero">
  <div class="container">
    <h1>⚙️ Preferences</h1>
    <p>Select the categories you care about — your feed will remember your choices.</p>
  </div>
</header>

<main class="news-section">
  <div class="container">

    <div class="section-header">
      <h2 class="section-title"><span class="dot"></span> Choose Your Categories</h2>
      <p style="font-size:0.85rem; color:var(--text-muted);">Select one or more</p>
    </div>

    <div class="prefs-grid" id="prefs-grid" role="group" aria-label="Category selection"></div>

    <div class="prefs-actions">
      <button class="btn btn-primary" id="save-prefs-btn">💾 Save Preferences</button>
      <button class="btn btn-outline" id="reset-prefs-btn">↩️ Reset to Default</button>
      <a href="index.php" class="btn btn-outline">🏠 Back to Feed</a>
    </div>

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

<script src="js/preferences.js"></script>

<script>
(function () {
  'use strict';

  const CATEGORIES = [
    { id: 'technology',    emoji: '💻', name: 'Technology',    desc: 'AI, gadgets, software & innovation' },
    { id: 'sports',        emoji: '⚽', name: 'Sports',        desc: 'Football, cricket, tennis & more' },
    { id: 'business',      emoji: '📈', name: 'Business',      desc: 'Markets, finance & economy' },
    { id: 'entertainment', emoji: '🎬', name: 'Entertainment', desc: 'Movies, music, TV & celebrities' },
    { id: 'health',        emoji: '❤️', name: 'Health',        desc: 'Wellness, medicine & fitness' },
    { id: 'science',       emoji: '🔬', name: 'Science',       desc: 'Space, research & discoveries' },
  ];

  let selected = new Set(Preferences.get());

  function render() {
    const grid = document.getElementById('prefs-grid');
    if (!grid) return;

    grid.innerHTML = CATEGORIES.map(cat => {
      const isSelected = selected.has(cat.id);
      return `
        <div class="pref-card ${isSelected ? 'selected' : ''}"
             role="checkbox"
             aria-checked="${isSelected}"
             tabindex="0"
             data-category="${cat.id}"
             id="pref-card-${cat.id}">
          <div class="pref-emoji">${cat.emoji}</div>
          <div class="pref-name">${cat.name}</div>
          <div class="pref-desc">${cat.desc}</div>
          <div class="pref-check">✓</div>
        </div>
      `;
    }).join('');

    grid.querySelectorAll('.pref-card').forEach(card => {
      function toggle() {
        const cat = card.dataset.category;
        if (selected.has(cat)) {
          selected.delete(cat);
          card.classList.remove('selected');
          card.setAttribute('aria-checked', 'false');
        } else {
          selected.add(cat);
          card.classList.add('selected');
          card.setAttribute('aria-checked', 'true');
        }
      }
      card.addEventListener('click', toggle);
      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
      });
    });
  }

  function showToast(msg, type = 'neutral') {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const icons = { success: '✅', danger: '❌', neutral: 'ℹ️' };
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = `<span class="toast-icon">${icons[type]}</span> ${msg}`;
    container.appendChild(t);
    setTimeout(() => {
      t.classList.add('removing');
      t.addEventListener('animationend', () => t.remove(), { once: true });
    }, 2800);
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

  function initActions() {
    const saveBtn  = document.getElementById('save-prefs-btn');
    const resetBtn = document.getElementById('reset-prefs-btn');

    if (saveBtn) {
      saveBtn.addEventListener('click', () => {
        if (selected.size === 0) {
          showToast('⚠️ Please select at least one category.', 'danger');
          return;
        }
        Preferences.save(Array.from(selected));
        showToast('✅ Preferences saved! Your feed will update.', 'success');
        setTimeout(() => { window.location.href = 'index.php'; }, 1500);
      });
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        selected = new Set(['technology', 'sports', 'business']);
        render();
        showToast('↩️ Reset to default categories.', 'neutral');
      });
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    render();
    initActions();
  });
})();
</script>
</body>
</html>
