# 📰 NewsHub — News Aggregator

**College Web Technologies Lab Project**
Built with: HTML5 · CSS3 · Vanilla JavaScript · PHP

---

## 🚀 How to Run (XAMPP)

### Step 1 — Install XAMPP
Download from [https://www.apachefriends.org](https://www.apachefriends.org) and install.

### Step 2 — Copy the Project
Copy the `news-aggregator/` folder into your XAMPP root:
```
C:\xampp\htdocs\news-aggregator\
```

### Step 3 — Start Apache
Open the **XAMPP Control Panel** and click **Start** next to **Apache**.

### Step 4 — Open in Browser
Visit: [http://localhost/news-aggregator/](http://localhost/news-aggregator/)

> **Note:** The project runs with **demo data by default** (no API key needed).

---

## 🔑 Adding a Real NewsAPI Key (Optional)

1. Sign up at [https://newsapi.org](https://newsapi.org) (free plan: 100 req/day)
2. Open `php/config.php`
3. Replace:
   ```php
   define('NEWSAPI_KEY', 'YOUR_NEWSAPI_KEY_HERE');
   ```
   with your actual key:
   ```php
   define('NEWSAPI_KEY', 'abc123yourkeyhere');
   ```
4. Refresh the browser — live news will appear.

---

## 📁 Project Structure

```
news-aggregator/
├── index.php           ← Home page (news feed + search)
├── bookmarks.php       ← Saved articles page
├── preferences.php     ← Category preference selection
│
├── css/
│   ├── style.css       ← Design system (tokens, layout, components)
│   └── animations.css  ← Keyframe animations & transitions
│
├── js/
│   ├── main.js         ← Core: fetch, render, search, tabs
│   ├── theme.js        ← Dark/Light mode toggle
│   ├── bookmarks.js    ← Bookmark CRUD (localStorage)
│   └── preferences.js  ← Category preference manager (localStorage)
│
└── php/
    ├── config.php      ← API key + constants
    └── fetch_news.php  ← NewsAPI proxy + demo fallback
```

---

## ✨ Features

| Feature | Description |
|---|---|
| 🌐 Live News | Fetches from NewsAPI via PHP proxy |
| 📦 Demo Mode | Realistic fallback data when no API key is set |
| 🗂️ Categories | Technology, Sports, Business, Entertainment, Health, Science |
| 🔍 Search | Debounced keyword search — no page reload |
| 🔖 Bookmarks | Save/remove articles (localStorage) |
| ⚙️ Preferences | Choose your favourite categories |
| 🌙 Dark Mode | Toggle with persistence |
| 📱 Responsive | Works on mobile, tablet, and desktop |
| ⚡ Skeleton UI | Loading placeholders before data arrives |
| 🎨 Animations | Smooth card entrances, hover effects, toasts |

---

## 🛠️ Technologies Used

- **PHP** — Backend API proxy, demo data generation
- **HTML5** — Semantic structure, accessibility (ARIA)
- **CSS3** — Custom properties (design tokens), Flexbox, Grid, animations
- **Vanilla JavaScript** — Fetch API, DOM manipulation, localStorage

*No frameworks. No database. No Node.js. Pure web technologies.*

---

## 📌 Notes for Viva

- The PHP file `fetch_news.php` acts as a **middleman** between the browser and NewsAPI — this keeps the API key hidden from the client.
- `localStorage` is used like a simple client-side database for bookmarks and preferences.
- The search is **debounced** — it waits 400ms after the user stops typing before making a request, to avoid flooding the API.
- The UI auto-applies the OS's preferred color scheme (dark/light) on first load.
