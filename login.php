<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — NewsHub</title>
  <meta name="description" content="Sign in to your NewsHub account to access your personalized news feed." />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="css/style.css" />
  <script src="js/theme.js"></script>

  <style>
    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      position: relative;
      overflow: hidden;
    }
    .login-wrapper::before {
      content: '';
      position: absolute;
      top: -200px; left: 50%;
      transform: translateX(-50%);
      width: 900px; height: 600px;
      background: radial-gradient(ellipse, var(--accent-glow) 0%, transparent 70%);
      pointer-events: none;
    }
    .login-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: 2.8rem 2.5rem;
      width: 100%;
      max-width: 420px;
      box-shadow: var(--shadow-lg);
      position: relative;
      z-index: 1;
    }
    .login-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.65rem;
      margin-bottom: 1.8rem;
      text-decoration: none;
    }
    .login-logo-icon {
      width: 44px; height: 44px;
      background: linear-gradient(135deg, var(--accent), #a855f7);
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
      box-shadow: var(--shadow-accent);
    }
    .login-logo-text {
      font-size: 1.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, var(--accent), #a855f7);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.02em;
    }
    .login-heading {
      text-align: center;
      margin-bottom: 0.4rem;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-primary);
    }
    .login-subtext {
      text-align: center;
      font-size: 0.875rem;
      color: var(--text-muted);
      margin-bottom: 2rem;
    }
    .form-group {
      margin-bottom: 1.2rem;
    }
    .form-group label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 0.45rem;
    }
    .form-group input {
      width: 100%;
      padding: 0.75rem 1rem;
      border-radius: var(--radius-md);
      border: 1.5px solid var(--border);
      background: var(--bg-secondary);
      color: var(--text-primary);
      font-size: 0.95rem;
      font-family: var(--font-sans);
      transition: border-color var(--t-fast), box-shadow var(--t-fast);
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-glow);
    }
    .form-group input::placeholder {
      color: var(--text-muted);
    }
    .login-error {
      background: rgba(239, 68, 68, 0.1);
      border: 1.5px solid rgba(239, 68, 68, 0.3);
      color: var(--danger);
      border-radius: var(--radius-md);
      padding: 0.65rem 1rem;
      font-size: 0.85rem;
      font-weight: 500;
      margin-bottom: 1.2rem;
      display: none;
      animation: fadeIn 0.25s ease both;
    }
    .login-error.visible { display: block; }
    .login-btn {
      width: 100%;
      padding: 0.85rem;
      border-radius: var(--radius-md);
      background: linear-gradient(135deg, var(--accent), #a855f7);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      border: none;
      cursor: pointer;
      box-shadow: var(--shadow-accent);
      transition: opacity var(--t-fast), transform var(--t-fast), box-shadow var(--t-fast);
      margin-top: 0.4rem;
      font-family: var(--font-sans);
    }
    .login-btn:hover {
      opacity: 0.92;
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(108, 99, 255, 0.4);
    }
    .login-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }
    .login-theme-btn {
      position: fixed;
      top: 1rem; right: 1rem;
    }
    .login-hint {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.78rem;
      color: var(--text-muted);
      padding-top: 1.2rem;
      border-top: 1px solid var(--border);
    }
    .login-hint code {
      background: var(--accent-light);
      color: var(--accent);
      padding: 0.1rem 0.45rem;
      border-radius: 4px;
      font-size: 0.82rem;
    }
  </style>
</head>
<body>

<button class="theme-btn login-theme-btn" id="theme-toggle"
        onclick="ThemeManager.toggle(this)"
        aria-label="Toggle dark mode"
        title="Toggle dark mode">🌙</button>

<div class="login-wrapper">
  <div class="login-card">

    <a href="login.php" class="login-logo" aria-label="NewsHub">
      <div class="login-logo-icon">📰</div>
      <span class="login-logo-text">NewsHub</span>
    </a>

    <h1 class="login-heading">Welcome back</h1>
    <p class="login-subtext">Sign in to access your personalized feed</p>

    <div class="login-error" id="login-error" role="alert"></div>

    <form id="login-form" novalidate>
      <div class="form-group">
        <label for="login-email">Email address</label>
        <input type="email"
               id="login-email"
               name="email"
               placeholder="nigamvivek2805@gmail.com"
               autocomplete="email"
               required />
      </div>
      <div class="form-group">
        <label for="login-password">Password</label>
        <input type="password"
               id="login-password"
               name="password"
               placeholder="••••••"
               autocomplete="current-password"
               required />
      </div>

      <button type="submit" class="login-btn" id="login-btn">Sign In</button>
    </form>

    <div class="login-hint">
      Demo credentials &nbsp;|&nbsp;
      <code>nigamvivek2805@gmail.com</code> &nbsp;/&nbsp; <code>12345678</code>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var form      = document.getElementById('login-form');
  var emailEl   = document.getElementById('login-email');
  var passEl    = document.getElementById('login-password');
  var errorEl   = document.getElementById('login-error');
  var submitBtn = document.getElementById('login-btn');

  if (!form) return;

  function showError(msg) {
    errorEl.textContent = '\u26a0\ufe0f ' + msg;
    errorEl.classList.add('visible');
  }

  function clearError() {
    errorEl.classList.remove('visible');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearError();

    var email    = emailEl.value.trim();
    var password = passEl.value.trim();

    if (!email || !password) {
      showError('Please enter both email and password.');
      return;
    }

    submitBtn.disabled    = true;
    submitBtn.textContent = 'Signing in\u2026';

    var body = 'email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password);

    fetch('php/login_handler.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    body
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.success) {
        window.location.replace('index.php');
      } else {
        showError(data.message || 'Invalid email or password.');
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Sign In';
        passEl.value = '';
        passEl.focus();
      }
    })
    .catch(function () {
      showError('Connection failed. Make sure XAMPP Apache is running.');
      submitBtn.disabled    = false;
      submitBtn.textContent = 'Sign In';
    });
  });
});
</script>
</body>
</html>
