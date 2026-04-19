<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NewsHub — Your Personalized News Feed</title>
  <meta name="description" content="Stay informed with live news from top sources across technology, sports, business, entertainment, health, and science." />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/animations.css" />
  <script src="js/theme.js"></script>
</head>
<body>

<nav class="navbar" id="main-navbar" aria-label="Main Navigation">
  <div class="container">

    <a href="index.php" class="nav-logo" aria-label="NewsHub Home">
      <div class="nav-logo-icon">📰</div>
      <span class="nav-logo-text">NewsHub</span>
    </a>

    <ul class="nav-links" role="list">
      <li><a href="index.php"       class="active" id="nav-home">Home</a></li>
      <li><a href="bookmarks.php"   id="nav-bookmarks">Bookmarks</a></li>
      <li><a href="preferences.php" id="nav-prefs">Preferences</a></li>
    </ul>

    <div class="nav-search-wrap">
      <span class="search-icon" aria-hidden="true">🔍</span>
      <input type="search"
             id="nav-search"
             placeholder="Quick search…"
             aria-label="Search articles" />
    </div>

    <div class="nav-actions">
      <button class="theme-btn" id="theme-toggle"
              onclick="ThemeManager.toggle(this)"
              aria-label="Toggle dark mode"
              title="Toggle dark mode">🌙</button>

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

<div class="nav-mobile" id="mobile-nav" role="dialog" aria-label="Mobile navigation">
  <a href="index.php"       class="active">🏠 Home</a>
  <a href="bookmarks.php">🔖 Bookmarks</a>
  <a href="preferences.php">⚙️ Preferences</a>
  <a href="php/logout.php">🚪 Logout</a>
</div>

<section class="hero" id="hero" aria-labelledby="hero-heading">
  <div class="container">

    <div class="hero-badge">✨ Live News • Updated Every Hour</div>

    <h1 id="hero-heading">Stay Informed,<br>Stay Ahead.</h1>

    <p>Get personalized news from top sources across technology, sports,
       business, entertainment, health, and science — all in one place.</p>

    <div class="hero-search" role="search">
      <input type="search"
             id="hero-search"
             placeholder="Search for news articles…"
             aria-label="Search news articles" />
      <button class="hero-search-btn" id="hero-search-btn" type="button">Search</button>
    </div>

  </div>
</section>

<div class="tabs-section" id="tabs-section" aria-label="News Categories">
  <div class="container">
    <div class="tabs-scroll">
      <div class="tabs" role="tablist" aria-label="News categories">
        <button class="tab-btn active" data-category="all"           role="tab" aria-selected="true"  id="tab-all">           <span class="tab-emoji">🌐</span> All News</button>
        <button class="tab-btn"        data-category="technology"    role="tab" aria-selected="false" id="tab-technology">    <span class="tab-emoji">💻</span> Technology</button>
        <button class="tab-btn"        data-category="sports"        role="tab" aria-selected="false" id="tab-sports">        <span class="tab-emoji">⚽</span> Sports</button>
        <button class="tab-btn"        data-category="business"      role="tab" aria-selected="false" id="tab-business">      <span class="tab-emoji">📈</span> Business</button>
        <button class="tab-btn"        data-category="entertainment" role="tab" aria-selected="false" id="tab-entertainment"> <span class="tab-emoji">🎬</span> Entertainment</button>
        <button class="tab-btn"        data-category="health"        role="tab" aria-selected="false" id="tab-health">        <span class="tab-emoji">❤️</span> Health</button>
        <button class="tab-btn"        data-category="science"       role="tab" aria-selected="false" id="tab-science">       <span class="tab-emoji">🔬</span> Science</button>
      </div>
    </div>
  </div>
</div>

<main class="news-section" id="main-content">
  <div class="container">

    <div class="section-header">
      <h2 class="section-title" id="section-title">
        <span class="dot"></span> Loading…
      </h2>
      <span class="demo-badge" id="demo-badge" title="Showing demo data — no API key configured">
        📦 Demo Data
      </span>
    </div>

    <div class="news-grid" id="news-grid" role="list" aria-live="polite" aria-label="News articles"></div>

  </div>
</main>

<footer class="footer" aria-label="Footer">
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
<script src="js/main.js"></script>

</body>
</html>
