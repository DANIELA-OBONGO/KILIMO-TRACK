# KilimoTrack — Icons + Navbar Package

Everything below is in this one folder. No other files are needed.

## 1. Files in this package

| File | What it's for |
|---|---|
| `favicon.ico` | Browser tab icon (all sizes in one file) |
| `favicon-16x16.png`, `favicon-32x32.png` | Backup favicon sizes |
| `apple-touch-icon.png` | iOS "Add to Home Screen" icon |
| `android-chrome-192x192.png`, `android-chrome-512x512.png` | Android / PWA home screen icons |
| `icon-192-maskable.png`, `icon-512-maskable.png` | Android adaptive icons (padded so the shape mask doesn't clip your logo) |
| `site.webmanifest` | Tells browsers/phones about your app + icons (for "Add to Home Screen") |
| `navbar.css` | Styling for the top navbar (logo far left, links right, mobile menu) |
| `index.html` | Example page showing the navbar + hero section wired up correctly |
| `hero-farm.jpg` | Placeholder banner image — replace with a real farm photo |

## 2. Where everything goes

Put **all of these files** in the root of your web app (the same folder as your `index.html`, or your `public/` folder if you're using Firebase Hosting):

```
your-app/
├── favicon.ico
├── favicon-16x16.png
├── favicon-32x32.png
├── apple-touch-icon.png
├── android-chrome-192x192.png
├── android-chrome-512x512.png
├── icon-192-maskable.png
├── icon-512-maskable.png
├── site.webmanifest
├── navbar.css
├── hero-farm.jpg          ← replace with your real photo
├── index.html             ← your homepage
├── dashboard.html          ← your other pages
├── market.html
└── ...
```

## 3. What to paste into EVERY page's `<head>`

Open each HTML page you have (`index.html`, `dashboard.html`, `market.html`, etc.) and paste this inside the `<head>...</head>` section:

```html
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#1f5c2e">
<link rel="stylesheet" href="navbar.css">
```

This is what makes the icon show up in the browser tab, on phone home screens, and loads the navbar styling.

## 4. What to paste into EVERY page's `<body>` (top of the page)

Paste this right after `<body>` opens, on every page — this is the logo-top-left navbar, same as the DigiFarm example you showed me:

```html
<nav class="kt-navbar">
  <a href="index.html" class="kt-navbar__brand">
    <img src="apple-touch-icon.png" alt="KilimoTrack logo">
    <div class="kt-navbar__brand-text">
      KilimoTrack
      <span>Smart Farming</span>
    </div>
  </a>

  <ul class="kt-navbar__links" id="kt-nav-links">
    <li><a href="index.html">Home</a></li>
    <li><a href="dashboard.html">Dashboard</a></li>
    <li><a href="market.html">Market</a></li>
    <li><a href="insights.html">Insights</a></li>
    <li><a href="about.html">About</a></li>
  </ul>

  <button class="kt-navbar__toggle" id="kt-nav-toggle" aria-label="Toggle menu">&#9776;</button>
</nav>

<script>
  document.getElementById('kt-nav-toggle').addEventListener('click', function () {
    document.getElementById('kt-nav-links').classList.toggle('open');
  });
</script>
```

**On each page**, add `class="active"` to whichever link matches that page, e.g. on `dashboard.html` you'd write `<a href="dashboard.html" class="active">Dashboard</a>`.

## 5. Full working example

`index.html` in this folder is a complete example with all of the above already wired up correctly — open it in a browser to see it working, then copy the same head/navbar pattern into your other pages.

## 6. If you're deploying with Firebase Hosting

Just make sure every file in this package sits inside your `public/` folder before running:
```
firebase deploy
```
No extra configuration needed — Firebase will serve them all from the root paths used above.
