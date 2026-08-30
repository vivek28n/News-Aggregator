# 📰 NewsHub — News Aggregator

<div align="center">

![NewsHub Banner](https://img.shields.io/badge/NewsHub-News%20Aggregator-7c3aed?style=for-the-badge&logo=rss&logoColor=white)

[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=flat&logo=xampp&logoColor=white)](https://www.apachefriends.org/)

**A lightweight, database-free News Aggregator built with core web technologies.**  
Live news · Category filters · Bookmarks · Secure login · Dark/Light mode

[Features](#-features) · [Tech Stack](#-tech-stack) · [Getting Started](#-getting-started) · [Project Structure](#-project-structure) · [Screenshots](#-screenshots) · [Future Work](#-future-work)

</div>

---

## 📖 About

**NewsHub** is a web-based News Aggregator application developed as part of the **Web Technologies (BCSE-0455)** course at **NIET Greater Noida**.

The project fetches live news articles from the [NewsAPI.org](https://newsapi.org) API and presents them in a clean, modern interface. Users can filter news by category, search for specific topics, bookmark articles, and set their preferred categories — all without any database or paid infrastructure.

> Built entirely with core web technologies — **no frameworks, no database, no paid hosting required.**

---

## ✨ Features

| Feature | Description |
|---|---|
| 🔐 Secure Login | PHP session-based authentication with access control on all pages |
| 📰 Live News | Real-time articles fetched from NewsAPI.org |
| 🗂️ Category Filter | Filter by Technology, Sports, Business, Entertainment, Health, Science |
| 🔍 Keyword Search | Search any topic using the search bar |
| 🔖 Bookmarks | Save articles for later — stored in browser's localStorage |
| ⚙️ Preferences | Select preferred categories — feed auto-updates on login |
| 🌗 Dark / Light Mode | Toggle between themes — preference saved in localStorage |
| 📱 Responsive UI | CSS Grid auto-fit layout — works on mobile, tablet, desktop |
| 🚪 Secure Logout | PHP session_destroy() on logout — full access control |

---

## 🛠️ Tech Stack

### Frontend
- **HTML5** — Semantic page structure
- **CSS3** — Styling, CSS Variables for theming, CSS Grid for responsive layout
- **Vanilla JavaScript** — DOM manipulation, Fetch API calls, localStorage management

### Backend
- **PHP 8+** — Server-side scripting, session handling, API proxy
- **Apache (XAMPP)** — Local web server

### External
- **NewsAPI.org** — Live news data source (REST API)

### Tools
- **VS Code** — Code editor
- **XAMPP** — Local server environment
- **Google Chrome** — Testing

---

## ⚙️ Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) installed (Apache + PHP)
- Internet connection (for NewsAPI)
- A free API key from [newsapi.org](https://newsapi.org/register)

### Installation

**1. Clone the repository**
```bash
git clone https://github.com/vivek28n/News-Aggregator.git
```

**2. Move to XAMPP's htdocs folder**
```bash
# Windows
C:\xampp\htdocs\

# Mac/Linux
/opt/lampp/htdocs/
```
Copy the `News-Aggregator` folder here. Rename it to `newshub` (optional).

**3. Add your API Key**

Open `php/config.php` and replace the placeholder:
```php
<?php
$config = [
    'api_key' => 'YOUR_NEWSAPI_KEY_HERE'   // <-- paste your key here
];
```

**4. Start XAMPP**
- Open XAMPP Control Panel
- Click **Start** next to **Apache**

**5. Open in Browser**
```
http://localhost/newshub/login.php
```

### Demo Credentials
```
Email    : nigamvivek2805@gmail.com
Password : 12345678
```

---

## 📁 Project Structure

```
newshub/
│
├── login.php               # Login page (public)
├── index.php               # Home / News feed (protected)
├── bookmarks.php           # Saved articles page (protected)
├── preferences.php         # Category preferences page (protected)
│
├── php/
│   ├── config.php          # API key configuration
│   ├── login_handler.php   # Credential validation + session creation
│   ├── fetch_news.php      # NewsAPI proxy (GET handler)
│   └── logout.php          # Session destroy + redirect
│
├── js/
│   ├── main.js             # News fetching + card rendering
│   ├── bookmarks.js        # localStorage read/write/delete
│   └── theme.js            # Dark/light mode toggle
│
└── css/
    └── style.css           # All styling (CSS variables, grid, responsive)
```

---

## 🔄 How It Works

```
User Opens Browser
        ↓
   Visit login.php
        ↓
 Enter Email & Password
        ↓
  PHP validates credentials
        ↓
   ┌──[Valid?]──┐
  YES           NO
   ↓             ↓
Session      Show Error
Created      Message
   ↓
index.php loads
        ↓
PHP fetch_news.php → NewsAPI.org
        ↓
JavaScript renders news cards
        ↓
User filters / searches / bookmarks
        ↓
Bookmarks → localStorage (na_bookmarks)
        ↓
Preferences → localStorage (na_prefs)
        ↓
Logout → session_destroy() → login.php
```

---

## 🔐 Authentication Flow

- Login form sends POST data to `php/login_handler.php`
- PHP compares credentials with hardcoded values
- On success: `$_SESSION['logged_in'] = true` → redirect to `index.php`
- On failure: redirect to `login.php?error=1` → error message shown
- Every protected page checks session at the top:
```php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}
```

---

## 🌐 API Integration

NewsHub uses a **PHP proxy** pattern instead of calling NewsAPI directly from JavaScript.

**Why proxy?**
- Prevents API key exposure in browser DevTools
- Avoids CORS (Cross-Origin Resource Sharing) errors
- Centralizes error handling

**Endpoint used:**
```
GET https://newsapi.org/v2/top-headlines
    ?category={category}
    &q={searchQuery}
    &apiKey={YOUR_KEY}
    &pageSize=20
    &language=en
```

---

## 💾 Storage — localStorage

No database is used. All user-specific data is stored in the browser:

| Key | Data | Used For |
|---|---|---|
| `na_bookmarks` | JSON array of articles | Bookmarks page |
| `na_prefs` | JSON array of category strings | Personalized feed |
| `theme` | `"dark"` or `"light"` | Theme persistence |

---

## 📋 System Requirements

### Software
| Software | Purpose |
|---|---|
| XAMPP (Apache) | Local web server to run PHP |
| PHP 8+ | Backend scripting |
| HTML5, CSS3 | Frontend structure and styling |
| Vanilla JavaScript | Interactivity and API calls |
| NewsAPI.org | Live news data |
| VS Code | Development |
| Chrome / Any Browser | Testing |

### Hardware (Minimum)
| Component | Requirement |
|---|---|
| Processor | Intel Core i3 or equivalent |
| RAM | 4 GB |
| Storage | 100 MB free space |
| Internet | Required (for NewsAPI) |
| Display | 1280 × 720 or higher |

---

## ✅ Test Cases

All 8 test cases passed during manual black-box testing.

| TC | Feature | Input | Result |
|---|---|---|---|
| TC-01 | Login — correct credentials | nigamvivek2805@gmail.com / 12345678 | ✅ PASS |
| TC-02 | Login — wrong credentials | wrong@mail.com / 0000 | ✅ PASS |
| TC-03 | Login — empty fields | No input | ✅ PASS |
| TC-04 | Direct URL access (no login) | Visit index.php | ✅ PASS |
| TC-05 | Category filter | Click "Technology" tab | ✅ PASS |
| TC-06 | Keyword search | Type "India" | ✅ PASS |
| TC-07 | Bookmark article | Click bookmark icon | ✅ PASS |
| TC-08 | View bookmarks | Go to Bookmarks page | ✅ PASS |

---

## 🚀 Future Work

- [ ] **MySQL Database** — Server-side storage for user accounts, bookmarks, and preferences
- [ ] **User Registration** — Multi-user support with unique credentials
- [ ] **Live Deployment** — Host on InfinityFree / 000webhost (no XAMPP needed)
- [ ] **Push Notifications** — Browser alerts for breaking news in preferred categories
- [ ] **Infinite Scroll** — Load more articles on scroll
- [ ] **Social Sharing** — Share articles directly to WhatsApp, Twitter
- [ ] **Password Hashing** — bcrypt for secure credential storage

---


**Supervisor:** Dr. Atul Pratap Singh (Assistant Professor, CSE-AI, NIET)  
**HOD:** Dr. Anand Kumar Gupta (Professor & HOD, CSE-AI, NIET)

---

## 🏫 Institution

**Department of Computer Science & Engineering — Artificial Intelligence**  
School of Computer Science in Emerging Technologies  
**Noida Institute of Engineering and Technology (NIET)**  
Greater Noida, Uttar Pradesh  
Affiliated to Dr. A.P.J. Abdul Kalam Technical University, Lucknow

**Course:** Web Technologies (BCSE-0455) | **Year:** May 2026

---

## 📚 References

1. NewsAPI.org — [newsapi.org/docs](https://newsapi.org/docs)
2. PHP Manual — [php.net/manual/en](https://www.php.net/manual/en/)
3. PHP Sessions — [php.net/manual/en/book.session.php](https://www.php.net/manual/en/book.session.php)
4. MDN — localStorage API — [developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage)
5. MDN — Fetch API — [developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)
6. XAMPP — [apachefriends.org](https://www.apachefriends.org/)
7. Google Fonts (Inter) — [fonts.google.com](https://fonts.google.com/specimen/Inter)
8. Visual Studio Code — [code.visualstudio.com](https://code.visualstudio.com/)

---

## 📄 License

This project was developed for academic purposes at NIET Greater Noida.  
Feel free to use it as a reference for learning web technologies.

---

<div align="center">
Made with ❤️ by Vivek Nigam & Yash Gupta · NIET Greater Noida · 2026
</div>
