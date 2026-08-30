# Plutobv Website Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the full Plutobv static HTML/CSS/JS website (all pages from the site map) with a small PHP form backend, ready to upload as-is to Hostinger shared hosting.

**Architecture:** Plain static `.html` files, no build step, no templating engine. A shared design system (CSS custom properties, component CSS, one JS file) is established first in Tasks 2–5, then every page task reuses it. Three PHP scripts under `backend/` handle the three forms (contact, apply, timesheet) by validating input and emailing it — no database, no persisted uploads.

**Tech Stack:** HTML5, vanilla CSS (custom properties, flexbox/grid), vanilla JS (no framework, no bundler), PHP 8 (`mail()`), Apache `.htaccess`.

## Global Constraints

- Static HTML/CSS/JS frontend only — no framework, no build step, no Node dependency at runtime or deploy time.
- Backend is PHP only (Hostinger shared hosting: Apache + PHP, no assumed database, no assumed Node).
- Domain: `plutobv.co.uk`. Working contact address used everywhere in code/content: `info@plutobv.co.uk`.
- Business address and phone number are **placeholders** everywhere they appear: use the exact literal strings `[Your Street Address]` and `[Your Phone Number]` — do not invent real-looking values, and do not reuse plutobvservices.co.uk's real address/phone.
- No content, photography, or testimonial text is copied from `https://plutobvservices.co.uk/` — original copy only, same themes/structure.
- Testimonials are visibly placeholder quotes (`"[Client testimonial goes here]" — Name, Role`), never fabricated reviews presented as genuine.
- No invented performance statistics (no "X years", "X clients served" claims) — use value statements instead.
- Every page shares the exact header/footer markup and CSS classes established in Task 4 — content pages in later tasks specify copy and section order, not repeated boilerplate, and must reuse those exact class names.
- All internal links and asset references use root-relative paths (e.g. `/assets/css/base.css`, `/index.html`, `/services/live-in-care.html`), never relative (`../`) paths — this lets the exact same header/footer HTML work unmodified on pages at any folder depth, and matches deployment at the Hostinger domain root (`public_html/`).
- No fabricated social-media links: the reference site's social icons pointed at unrelated demo accounts, so this build omits a social-links block entirely rather than shipping fake or dead links. Can be added later once real profiles exist.
- No placeholder value is ever wired up as if it were live (e.g. a fake phone number must never be wrapped in a working `tel:` link) — placeholders render as plain text until replaced.
- Every uploaded file (CV, timesheet) is emailed as an attachment and never written to persistent disk storage on the server.
- PHP CLI is not available in the local dev environment: backend tasks lint with `php -l` when possible, otherwise are verified by careful manual review and a post-deploy checklist in the README (Task 25).

---

### Task 1: Project scaffold

**Files:**
- Create: `plutobv-website/.gitignore`
- Create: `plutobv-website/README.md`

**Interfaces:**
- Produces: repo-level ignore rules and a README that later tasks (20, 22) extend — do not remove existing headings when editing it later.

- [ ] **Step 1: Write `.gitignore`**

```gitignore
.DS_Store
Thumbs.db
*.log
.vscode/
.idea/
```

- [ ] **Step 2: Write the README skeleton**

```markdown
# Plutobv Website

Static HTML/CSS/JS site for Plutobv, a UK healthcare-staffing agency, with a small
PHP form backend. No build step — deploy by uploading this folder's contents.

## Local development

Open any `.html` file directly in a browser, or serve the folder with any static
file server. The PHP backend under `backend/` needs a PHP-capable host (see
Deployment below) — it will not run from a plain static file server.

## Placeholders to replace before going live

- Business address: search for `[Your Street Address]`
- Phone number: search for `[Your Phone Number]`
- Testimonial quotes: search for `[Client testimonial goes here]`

## Deployment (Hostinger shared hosting)

(Filled in by Task 25.)

## Security notes

(Filled in by Task 25.)
```

- [ ] **Step 3: Verify the directory structure matches the plan**

Run: `ls -la plutobv-website` and confirm `assets/`, `backend/`, `services/`,
`news/`, `forms/`, `docs/` all exist (created during design).
Expected: all five directories present.

- [ ] **Step 4: Commit**

```bash
git add .gitignore README.md
git commit -m "chore: add project scaffold (.gitignore, README skeleton)"
```

---

### Task 2: Design tokens & base CSS

**Files:**
- Create: `plutobv-website/assets/css/base.css`

**Interfaces:**
- Produces: CSS custom properties (`--color-*`, `--font-sans`, `--space-*`,
  `--radius-*`, `--shadow-*`, `--max-width`, `--header-height`) and base element
  styles that every later CSS/HTML task consumes by name. Also produces layout
  utility classes `.container`, `.section`, `.visually-hidden`, `.skip-link`.

- [ ] **Step 1: Write `assets/css/base.css`**

```css
/* ---- Design tokens ---- */
:root {
  --color-primary: #14406B;
  --color-primary-dark: #0D2C4A;
  --color-primary-light: #E8EEF4;
  /* Accent is a dark teal, not a brighter one, specifically so white button
     text on --color-accent clears WCAG AA (4.5:1) — verified at 6.43:1 with
     Node's luminance formula; a brighter teal (e.g. #2FA79A) measured only
     2.95:1 and was rejected for this reason. */
  --color-accent: #156A61;
  --color-accent-dark: #0F5A52;
  --color-bg: #F7F8FA;
  --color-surface: #FFFFFF;
  --color-text: #1E2530;
  --color-text-muted: #5B6472;
  --color-border: #E2E6EA;

  --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
    "Helvetica Neue", Arial, sans-serif;

  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 24px;
  --space-6: 32px;
  --space-7: 48px;
  --space-8: 64px;
  --space-9: 96px;

  --radius-sm: 6px;
  --radius-md: 12px;
  --radius-lg: 20px;

  --shadow-sm: 0 1px 2px rgba(20, 64, 107, 0.08);
  --shadow-md: 0 4px 16px rgba(20, 64, 107, 0.12);

  --max-width: 1180px;
  --header-height: 84px;
}

/* ---- Reset ---- */
*, *::before, *::after { box-sizing: border-box; }
html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }
body, h1, h2, h3, h4, p, figure, blockquote, dl, dd { margin: 0; }
ul[class], ol[class] { margin: 0; padding: 0; list-style: none; }
img, picture, svg { display: block; max-width: 100%; }
button, input, textarea, select { font: inherit; color: inherit; }
a { color: inherit; text-decoration: none; }

/* ---- Base typography ---- */
body {
  font-family: var(--font-sans);
  background: var(--color-bg);
  color: var(--color-text);
  line-height: 1.55;
  font-size: 16px;
}

h1, h2, h3, h4 {
  font-weight: 700;
  line-height: 1.2;
  color: var(--color-primary-dark);
}

h1 { font-size: clamp(2rem, 1.5rem + 2vw, 3.25rem); }
h2 { font-size: clamp(1.6rem, 1.3rem + 1.2vw, 2.25rem); }
h3 { font-size: 1.25rem; }
h4 { font-size: 1.05rem; }

p { color: var(--color-text); }
.text-muted { color: var(--color-text-muted); }

a:focus-visible, button:focus-visible, input:focus-visible,
textarea:focus-visible, select:focus-visible {
  outline: 3px solid var(--color-accent);
  outline-offset: 2px;
}

/* ---- Layout utilities ---- */
.container {
  max-width: var(--max-width);
  margin-inline: auto;
  padding-inline: var(--space-5);
}

.section {
  padding-block: var(--space-8);
}

.section--tight {
  padding-block: var(--space-6);
}

.visually-hidden {
  position: absolute;
  width: 1px; height: 1px;
  padding: 0; margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.skip-link {
  position: absolute;
  left: var(--space-3);
  top: -100px;
  background: var(--color-primary);
  color: #fff;
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-sm);
  z-index: 1000;
  transition: top 0.2s ease;
}

.skip-link:focus {
  top: var(--space-3);
}

@media (max-width: 640px) {
  .section { padding-block: var(--space-7); }
}
```

- [ ] **Step 2: Verify the file loads without a syntax error**

Run: `node -e "require('fs').readFileSync('plutobv-website/assets/css/base.css','utf8')"` (only checks the file is readable UTF-8, not CSS validity — CSS has no local linter in this stack)
Expected: no output, exit code 0.

This file has no visual output by itself; it's verified visually in Task 6 (Home
page), the first page to consume it.

- [ ] **Step 3: Commit**

```bash
git add assets/css/base.css
git commit -m "feat: add design tokens and base CSS"
```

---

### Task 3: Logo mark and favicon

**Files:**
- Create: `plutobv-website/assets/images/favicon.svg`

**Interfaces:**
- Produces: the inline logo SVG snippet below (embedded literally into the header
  in Task 4 — not loaded as an external file, so it can use `var(--color-*)` and
  recolor with the page's CSS) and the standalone favicon file.

- [ ] **Step 1: Write the standalone favicon**

`assets/images/favicon.svg`:

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <rect width="64" height="64" rx="18" fill="#14406B"/>
  <circle cx="50" cy="14" r="6" fill="#156A61"/>
  <text x="32" y="44" text-anchor="middle" font-family="Arial, sans-serif" font-weight="800" font-size="32" fill="#ffffff">P</text>
</svg>
```

- [ ] **Step 2: Record the inline logo mark snippet for Task 4**

This exact markup is pasted into the header in Task 4 (it is not a separate file):

```html
<svg class="brand__mark" viewBox="0 0 64 64" width="44" height="44" role="img" aria-hidden="true" focusable="false">
  <rect width="64" height="64" rx="18" fill="var(--color-primary)"></rect>
  <circle cx="50" cy="14" r="6" fill="var(--color-accent)"></circle>
  <text x="32" y="44" text-anchor="middle" font-family="var(--font-sans)" font-weight="800" font-size="32" fill="#ffffff">P</text>
</svg>
```

- [ ] **Step 3: Verify the favicon is valid XML**

Run: `node -e "new (require('util').TextDecoder)().decode(require('fs').readFileSync('plutobv-website/assets/images/favicon.svg'))"`
Expected: no output, exit code 0 (confirms the file is readable; visually confirmed once a page references it in Task 6).

- [ ] **Step 4: Commit**

```bash
git add assets/images/favicon.svg
git commit -m "feat: add logo mark and favicon"
```

---

### Task 4: Shared header and footer partial + nav toggle JS

**Files:**
- Create: `plutobv-website/assets/js/main.js`

**Interfaces:**
- Consumes: `.brand__mark` SVG snippet from Task 3; CSS tokens from Task 2 (styled
  in Task 5).
- Produces: the exact header and footer HTML blocks below, which every page task
  from Task 6 onward pastes in verbatim (only the `aria-current="page"` attribute
  moves to match whichever nav link matches the current page, and the relevant
  submenu item if the current page is a service page). Produces `main.js`'s
  navigation section: reads `.nav-toggle`, `#primary-nav`, `#year` by those exact
  IDs/classes — later tasks (13, 14) append more code to this same file below the
  `/* ---- Navigation ---- */` block, never replacing it.

- [ ] **Step 1: Write the header block (reference copy for every page task)**

```html
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
  <div class="topbar">
    <div class="container topbar__inner">
      <ul class="topbar__contact">
        <li>[Your Street Address]</li>
        <li><a href="mailto:info@plutobv.co.uk">info@plutobv.co.uk</a></li>
        <li>[Your Phone Number]</li>
      </ul>
    </div>
  </div>
  <div class="container navbar">
    <a class="brand" href="/index.html" aria-label="Plutobv home">
      <svg class="brand__mark" viewBox="0 0 64 64" width="44" height="44" role="img" aria-hidden="true" focusable="false">
        <rect width="64" height="64" rx="18" fill="var(--color-primary)"></rect>
        <circle cx="50" cy="14" r="6" fill="var(--color-accent)"></circle>
        <text x="32" y="44" text-anchor="middle" font-family="var(--font-sans)" font-weight="800" font-size="32" fill="#ffffff">P</text>
      </svg>
      <span class="brand__text">
        <span class="brand__name">Plutobv</span>
        <span class="brand__tagline">Health &amp; Care Staffing</span>
      </span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
      <span class="visually-hidden">Menu</span>
      <span class="nav-toggle__bar" aria-hidden="true"></span>
    </button>

    <nav id="primary-nav" class="primary-nav" aria-label="Primary">
      <ul>
        <li><a href="/index.html">Home</a></li>
        <li><a href="/about.html">About Us</a></li>
        <li><a href="/areas-we-support.html">Areas We Support</a></li>
        <li><a href="/staffing-solutions.html">Staffing Solutions</a></li>
        <li class="has-submenu">
          <a href="/services.html">Services</a>
          <ul class="submenu">
            <li><a href="/services/live-in-care.html">Live-in Care</a></li>
            <li><a href="/services/domiciliary-care.html">Domiciliary Care</a></li>
            <li><a href="/services/companionship-care.html">Companionship Care</a></li>
            <li><a href="/services/autism-support.html">Autism Support</a></li>
          </ul>
        </li>
        <li><a href="/news.html">News</a></li>
        <li><a href="/contact.html" class="btn btn--primary btn--sm">Contact Us</a></li>
      </ul>
    </nav>
  </div>
</header>
```

- [ ] **Step 2: Write the footer block (reference copy for every page task)**

```html
<footer class="site-footer">
  <div class="container footer__grid">
    <div class="footer__col">
      <span class="brand__name">Plutobv</span>
      <p class="text-muted">
        Plutobv supplies vetted, trained health and social care staff to local
        authorities, the NHS, and private care providers, so the people in their
        care are never without support.
      </p>
    </div>
    <div class="footer__col">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="/about.html">About Us</a></li>
        <li><a href="/services.html">Services</a></li>
        <li><a href="/staffing-solutions.html">Staffing Solutions</a></li>
        <li><a href="/news.html">News</a></li>
        <li><a href="/contact.html">Contact Us</a></li>
      </ul>
    </div>
    <div class="footer__col">
      <h4>Work With Us</h4>
      <ul>
        <li><a href="/apply.html">Apply Now</a></li>
        <li><a href="/forms/application-form-print.html">Print Application Form</a></li>
        <li><a href="/forms/reference-form-print.html">Print Reference Form</a></li>
        <li><a href="/timesheet.html">Submit Timesheet</a></li>
        <li><a href="/forms/timesheet-print.html">Print Timesheet</a></li>
      </ul>
    </div>
    <div class="footer__col">
      <h4>Contact</h4>
      <ul>
        <li>[Your Street Address]</li>
        <li><a href="mailto:info@plutobv.co.uk">info@plutobv.co.uk</a></li>
        <li>[Your Phone Number]</li>
      </ul>
    </div>
  </div>
  <div class="container footer__bottom">
    <p>&copy; <span id="year"></span> Plutobv. All rights reserved.</p>
    <ul class="footer__legal">
      <li><a href="/privacy-policy.html">Privacy Policy</a></li>
      <li><a href="/terms.html">Terms</a></li>
    </ul>
  </div>
</footer>
```

- [ ] **Step 3: Write the navigation section of `assets/js/main.js`**

```js
/* ---- Navigation ---- */
(function () {
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('primary-nav');

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var expanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!expanded));
      nav.classList.toggle('is-open', !expanded);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });
  }

  var yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();
})();
```

- [ ] **Step 4: Verify the JS has no syntax errors**

Run: `node --check plutobv-website/assets/js/main.js`
Expected: no output, exit code 0.

This block's behavior (menu open/close, footer year) is visually verified once a
real page uses it, starting Task 6.

- [ ] **Step 5: Commit**

```bash
git add assets/js/main.js
git commit -m "feat: add shared header/footer markup and nav toggle script"
```

---

### Task 5: Component CSS (header, nav, footer, buttons, cards, forms, badges)

**Files:**
- Create: `plutobv-website/assets/css/components.css`

**Interfaces:**
- Consumes: tokens from Task 2 (`--color-*`, `--space-*`, `--radius-*`, `--shadow-*`,
  `--max-width`, `--header-height`), class names introduced in Task 4's header/footer.
- Produces: `.btn` (`--primary`, `--outline`, `--sm`, `--lg` modifiers), `.card`
  (`.service-card`, `.testimonial-card`, `.news-card`, `.stat-card`), `.badge`,
  and `.form`/`.form-field`/`.form-success`/`.form-error` — every page task from
  Task 6 onward uses these exact class names rather than inventing new ones.

- [ ] **Step 1: Write `assets/css/components.css`**

```css
/* ---- Topbar & header ---- */
.topbar {
  background: var(--color-primary-dark);
  color: #fff;
  font-size: 0.85rem;
}

.topbar__inner { padding-block: var(--space-2); }

.topbar__contact {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-5);
}

.topbar__contact a { color: #fff; }
.topbar__contact a:hover { text-decoration: underline; }

.site-header {
  background: var(--color-surface);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 100;
}

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: var(--header-height);
  gap: var(--space-5);
}

.brand {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.brand__text { display: flex; flex-direction: column; line-height: 1.2; }
.brand__name { font-weight: 800; font-size: 1.25rem; color: var(--color-primary-dark); }
.brand__tagline { font-size: 0.7rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--color-text-muted); }

/* ---- Nav toggle (mobile) ---- */
.nav-toggle {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: 0;
  padding: var(--space-2);
  cursor: pointer;
}

.nav-toggle__bar,
.nav-toggle__bar::before,
.nav-toggle__bar::after {
  content: "";
  display: block;
  width: 24px;
  height: 2px;
  background: var(--color-primary-dark);
  transition: transform 0.2s ease;
}

.nav-toggle__bar::before { transform: translateY(-7px); }
.nav-toggle__bar::after { transform: translateY(5px); }

/* ---- Primary nav ---- */
.primary-nav ul {
  display: flex;
  align-items: center;
  gap: var(--space-5);
}

.primary-nav a {
  font-weight: 600;
  color: var(--color-text);
  padding: var(--space-2) 0;
}

.primary-nav a:hover { color: var(--color-primary); }
.primary-nav a[aria-current="page"] { color: var(--color-primary); border-bottom: 2px solid var(--color-accent); }

.has-submenu { position: relative; }
.submenu {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  background: var(--color-surface);
  box-shadow: var(--shadow-md);
  border-radius: var(--radius-md);
  padding: var(--space-2);
  min-width: 220px;
  flex-direction: column;
  gap: 0;
}

.has-submenu:hover .submenu,
.has-submenu:focus-within .submenu {
  display: flex;
}

.submenu a { padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm); }
.submenu a:hover { background: var(--color-primary-light); }

@media (max-width: 900px) {
  .nav-toggle { display: flex; }

  .primary-nav {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--color-surface);
    box-shadow: var(--shadow-md);
    padding: var(--space-4);
  }

  .primary-nav.is-open { display: block; }
  .primary-nav ul { flex-direction: column; align-items: stretch; gap: var(--space-2); }
  .has-submenu:hover .submenu, .has-submenu:focus-within .submenu { display: none; }
  .submenu { position: static; box-shadow: none; display: block; padding-left: var(--space-4); }
}

/* ---- Buttons ---- */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-md);
  font-weight: 700;
  border: 2px solid transparent;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.btn--primary { background: var(--color-accent); color: #fff; }
.btn--primary:hover { background: var(--color-accent-dark); box-shadow: var(--shadow-md); }

.btn--outline { background: transparent; border-color: var(--color-surface); color: var(--color-surface); }
.btn--outline:hover { background: rgba(255,255,255,0.12); }

.btn--dark-outline { background: transparent; border-color: var(--color-primary); color: var(--color-primary); }
.btn--dark-outline:hover { background: var(--color-primary-light); }

.btn--sm { padding: var(--space-2) var(--space-4); font-size: 0.85rem; }
.btn--lg { padding: var(--space-4) var(--space-6); font-size: 1.05rem; }
.btn:active { transform: translateY(1px); }

/* ---- Badge ---- */
.badge {
  display: inline-block;
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  padding: var(--space-1) var(--space-3);
  border-radius: 999px;
}

/* ---- Cards ---- */
.card {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--space-5);
}

.service-card {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--space-6);
  border-top: 4px solid var(--color-accent);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.service-card:hover { box-shadow: var(--shadow-md); transform: translateY(-4px); }
.service-card__icon { width: 48px; height: 48px; margin-bottom: var(--space-4); color: var(--color-accent); }

.testimonial-card {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  padding: var(--space-6);
}

.testimonial-card__quote { font-size: 1.05rem; font-style: italic; color: var(--color-text); }
.testimonial-card__author { margin-top: var(--space-4); font-weight: 700; color: var(--color-primary-dark); }
.testimonial-card__role { color: var(--color-text-muted); font-size: 0.85rem; }

.news-card {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.news-card__image { aspect-ratio: 4 / 3; object-fit: cover; width: 100%; }
.news-card__body { padding: var(--space-5); display: flex; flex-direction: column; gap: var(--space-2); flex: 1; }
.news-card__date { color: var(--color-text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; }
.news-card__title { font-size: 1.1rem; }
.news-card__link { margin-top: auto; font-weight: 700; color: var(--color-accent-dark); }

.stat-card { text-align: center; }
.stat-card__value { font-size: 2.5rem; font-weight: 800; color: var(--color-accent); }
.stat-card__label { color: var(--color-text-muted); font-weight: 600; }

/* ---- Forms ---- */
.form { display: flex; flex-direction: column; gap: var(--space-5); max-width: 640px; }
.form-field { display: flex; flex-direction: column; gap: var(--space-2); }
.form-field label { font-weight: 700; font-size: 0.9rem; }

.form-field input,
.form-field textarea,
.form-field select {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: var(--space-3);
  background: var(--color-surface);
}

.form-field textarea { min-height: 140px; resize: vertical; }
.form-field--error input,
.form-field--error textarea { border-color: #C0392B; }
.form-field__error { color: #C0392B; font-size: 0.85rem; }

.form-field--honeypot { position: absolute; left: -9999px; top: -9999px; }

.form-success, .form-error {
  border-radius: var(--radius-md);
  padding: var(--space-4);
  font-weight: 600;
}

.form-success { background: #E4F5EE; color: #1E6B4C; }
.form-error { background: #FBEAEA; color: #A3231A; }

/* ---- Footer ---- */
.site-footer {
  background: var(--color-primary-dark);
  color: #fff;
  margin-top: var(--space-9);
}

.footer__grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--space-6);
  padding-block: var(--space-8);
}

.footer__col h4 { color: #fff; margin-bottom: var(--space-3); }
.footer__col ul { display: flex; flex-direction: column; gap: var(--space-2); }
.footer__col a { color: rgba(255,255,255,0.85); }
.footer__col a:hover { color: #fff; text-decoration: underline; }

.footer__bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--space-3);
  padding-block: var(--space-4);
  border-top: 1px solid rgba(255,255,255,0.15);
  font-size: 0.85rem;
}

.footer__legal { display: flex; gap: var(--space-4); }

@media (max-width: 900px) {
  .footer__grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 560px) {
  .footer__grid { grid-template-columns: 1fr; }
}
```

- [ ] **Step 2: Verify the file has no syntax error**

Run: `node -e "require('fs').readFileSync('plutobv-website/assets/css/components.css','utf8')"`
Expected: no output, exit code 0.

Visual verification happens in Task 6, the first page to use these classes.

- [ ] **Step 3: Commit**

```bash
git add assets/css/components.css
git commit -m "feat: add component CSS (header, nav, footer, buttons, cards, forms)"
```

---

### Task 6: Generate original photography

**Files:**
- Create: `plutobv-website/assets/images/hero-team.jpg`
- Create: `plutobv-website/assets/images/about-team.jpg`
- Create: `plutobv-website/assets/images/staffing-solutions.jpg`
- Create: `plutobv-website/assets/images/service-live-in-care.jpg`
- Create: `plutobv-website/assets/images/service-domiciliary-care.jpg`
- Create: `plutobv-website/assets/images/service-companionship-care.jpg`
- Create: `plutobv-website/assets/images/service-autism-support.jpg`
- Create: `plutobv-website/assets/images/news-01.jpg`
- Create: `plutobv-website/assets/images/news-02.jpg`
- Create: `plutobv-website/assets/images/news-03.jpg`

**Interfaces:**
- Produces: the 10 image files above, at these exact paths — every page task from
  Task 7 onward references these exact filenames. None of these are copied from
  plutobvservices.co.uk; all are freshly generated, and none depict an identifiable
  real person.

Use the image-generation tool available in this environment (e.g. `generate_image` /
`generate_image_batch`) for each of the 10 prompts below, saving each result to its
exact path. Keep a consistent warm, natural, editorial-photography style (not
stock-photo-stiff, not illustration) across all 10 so they read as one shoot.

- [ ] **Step 1: Generate `hero-team.jpg`**

Prompt: "Warm, candid editorial photograph of a friendly home care support worker
in navy scrubs sitting and chatting with an elderly woman in a bright, comfortable
UK living room, natural daylight, genuine smiles, shallow depth of field,
documentary style, no visible text or logos"
Size: landscape, at least 1600x1000.

- [ ] **Step 2: Generate `about-team.jpg`**

Prompt: "Small diverse team of five care and support workers in navy-blue polos
standing together in a bright modern office breakout space, relaxed genuine
expressions, natural light, documentary editorial photography style, no visible
text or logos"
Size: landscape, at least 1600x1000.

- [ ] **Step 3: Generate `staffing-solutions.jpg`**

Prompt: "Recruiter and care worker reviewing a shift schedule together on a tablet
in a bright modern office, warm natural light, documentary editorial photography
style, professional but approachable, no visible text or logos"
Size: landscape, at least 1600x1000.

- [ ] **Step 4: Generate `service-live-in-care.jpg`**

Prompt: "Live-in carer helping an elderly man prepare tea in a cosy home kitchen,
warm afternoon light, genuine candid interaction, documentary editorial
photography style, no visible text or logos"
Size: landscape, at least 1200x900.

- [ ] **Step 5: Generate `service-domiciliary-care.jpg`**

Prompt: "Care worker arriving at an elderly client's front door with a warm smile,
UK suburban house, daytime, documentary editorial photography style, no visible
text or logos"
Size: landscape, at least 1200x900.

- [ ] **Step 6: Generate `service-companionship-care.jpg`**

Prompt: "Support worker and elderly woman laughing together over a board game at a
kitchen table, warm natural light, genuine candid moment, documentary editorial
photography style, no visible text or logos"
Size: landscape, at least 1200x900.

- [ ] **Step 7: Generate `service-autism-support.jpg`**

Prompt: "Support worker and a young autistic adult doing a calm sensory-friendly
art activity together at a table, soft natural light, warm and patient mood,
documentary editorial photography style, no visible text or logos"
Size: landscape, at least 1200x900.

- [ ] **Step 8: Generate `news-01.jpg`, `news-02.jpg`, `news-03.jpg`**

Prompt for all three (vary composition slightly across the three): "Close-up,
warm editorial photograph representing home health care — hands, a cup of tea, a
care worker's notes, or a quiet home interior detail, soft natural light,
documentary photography style, no visible text or logos"
Size: landscape, at least 1200x900.

- [ ] **Step 9: Verify all 10 files exist**

Run: `ls -la plutobv-website/assets/images/`
Expected: all 10 filenames listed above are present, alongside `favicon.svg` from
Task 3.

- [ ] **Step 10: Commit**

```bash
git add assets/images/
git commit -m "feat: add original generated photography"
```

---

### Task 7: Home page

**Files:**
- Create: `plutobv-website/index.html`
- Create: `plutobv-website/assets/css/pages.css`

**Interfaces:**
- Consumes: header/footer from Task 4, classes from Task 5, images from Task 6.
- Produces: `assets/css/pages.css`'s first section styles (`.hero`, `.feature-grid`,
  `.section-head`, `.testimonial-track`, `.process-list`, `.value-band`,
  `.news-grid`, `.cta-band`) — every later page task appends to this same file
  rather than duplicating these rules, and reuses these exact class names for the
  same kind of section.

- [ ] **Step 1: Write `index.html`**

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Plutobv | Health &amp; Social Care Staffing</title>
  <meta name="description" content="Plutobv supplies vetted, trained health and social care staff to local authorities, the NHS, and private care providers across the UK.">
  <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
  <link rel="stylesheet" href="/assets/css/base.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/pages.css">
</head>
<body>
  <!-- HEADER: shared block from Task 4, Home link marked current -->
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="site-header">
    <div class="topbar">
      <div class="container topbar__inner">
        <ul class="topbar__contact">
          <li>[Your Street Address]</li>
          <li><a href="mailto:info@plutobv.co.uk">info@plutobv.co.uk</a></li>
          <li>[Your Phone Number]</li>
        </ul>
      </div>
    </div>
    <div class="container navbar">
      <a class="brand" href="/index.html" aria-label="Plutobv home">
        <svg class="brand__mark" viewBox="0 0 64 64" width="44" height="44" role="img" aria-hidden="true" focusable="false">
          <rect width="64" height="64" rx="18" fill="var(--color-primary)"></rect>
          <circle cx="50" cy="14" r="6" fill="var(--color-accent)"></circle>
          <text x="32" y="44" text-anchor="middle" font-family="var(--font-sans)" font-weight="800" font-size="32" fill="#ffffff">P</text>
        </svg>
        <span class="brand__text">
          <span class="brand__name">Plutobv</span>
          <span class="brand__tagline">Health &amp; Care Staffing</span>
        </span>
      </a>
      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">
        <span class="visually-hidden">Menu</span>
        <span class="nav-toggle__bar" aria-hidden="true"></span>
      </button>
      <nav id="primary-nav" class="primary-nav" aria-label="Primary">
        <ul>
          <li><a href="/index.html" aria-current="page">Home</a></li>
          <li><a href="/about.html">About Us</a></li>
          <li><a href="/areas-we-support.html">Areas We Support</a></li>
          <li><a href="/staffing-solutions.html">Staffing Solutions</a></li>
          <li class="has-submenu">
            <a href="/services.html">Services</a>
            <ul class="submenu">
              <li><a href="/services/live-in-care.html">Live-in Care</a></li>
              <li><a href="/services/domiciliary-care.html">Domiciliary Care</a></li>
              <li><a href="/services/companionship-care.html">Companionship Care</a></li>
              <li><a href="/services/autism-support.html">Autism Support</a></li>
            </ul>
          </li>
          <li><a href="/news.html">News</a></li>
          <li><a href="/contact.html" class="btn btn--primary btn--sm">Contact Us</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main id="main">
    <!-- Hero -->
    <section class="hero section">
      <div class="container hero__grid">
        <div class="hero__content">
          <span class="badge">Compassionate, Reliable Care Staff</span>
          <h1>Health and social care staffing you can rely on</h1>
          <p>
            Plutobv recruits, vets, and places health care assistants and support
            workers across the public and private sector — supplying local
            authorities, the NHS, and private residential homes with staff who
            show up ready to care.
          </p>
          <div class="hero__actions">
            <a class="btn btn--primary btn--lg" href="/contact.html">Get In Touch</a>
            <a class="btn btn--dark-outline btn--lg" href="/services.html">Our Services</a>
          </div>
        </div>
        <div class="hero__media">
          <img src="/assets/images/hero-team.jpg" alt="A Plutobv home care worker sitting and chatting with an elderly client in her living room" width="720" height="480" loading="eager">
        </div>
      </div>
    </section>

    <!-- How we do it -->
    <section class="section section--tight">
      <div class="container">
        <div class="section-head">
          <span class="badge">How We Do It</span>
          <h2>Supercharging your care workforce</h2>
        </div>
        <div class="feature-grid">
          <div class="feature">
            <h3>Client-first matching</h3>
            <p class="text-muted">We start with your needs and requirements, then match staff to the specific people and settings they'll be working with.</p>
          </div>
          <div class="feature">
            <h3>Properly trained staff</h3>
            <p class="text-muted">Every care and support worker we place completes structured training before their first shift, not just an induction chat.</p>
          </div>
          <div class="feature">
            <h3>Flexible, reliable cover</h3>
            <p class="text-muted">Skilled, friendly staff willing to work flexible hours, so you're covered when you need it — not just during office hours.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Services -->
    <section class="section" style="background: var(--color-surface);">
      <div class="container">
        <div class="section-head">
          <span class="badge">What We're Offering</span>
          <h2>Care staffing across every setting</h2>
        </div>
        <div class="card-grid card-grid--4">
          <a class="service-card" href="/services/live-in-care.html">
            <h3>Live-in Care</h3>
            <p class="text-muted">Round-the-clock support in the comfort of your own home, so you can keep living life on your terms.</p>
          </a>
          <a class="service-card" href="/services/domiciliary-care.html">
            <h3>Domiciliary Care</h3>
            <p class="text-muted">Visiting support with daily tasks and personal care, helping people stay independent in their own homes.</p>
          </a>
          <a class="service-card" href="/services/companionship-care.html">
            <h3>Companionship Care</h3>
            <p class="text-muted">Regular company and conversation for people who'd otherwise spend long stretches of time alone.</p>
          </a>
          <a class="service-card" href="/services/autism-support.html">
            <h3>Autism Support</h3>
            <p class="text-muted">Support workers experienced with Autism Spectrum Disorders, complex needs, and behaviours that challenge.</p>
          </a>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="section section--tight">
      <div class="container">
        <div class="section-head">
          <span class="badge">Our Testimonials</span>
          <h2>What care providers say about working with us</h2>
        </div>
        <div class="testimonial-track">
          <div class="testimonial-card">
            <p class="testimonial-card__quote">"[Client testimonial goes here]"</p>
            <p class="testimonial-card__author">Name</p>
            <p class="testimonial-card__role">Role, Organisation</p>
          </div>
          <div class="testimonial-card">
            <p class="testimonial-card__quote">"[Client testimonial goes here]"</p>
            <p class="testimonial-card__author">Name</p>
            <p class="testimonial-card__role">Role, Organisation</p>
          </div>
          <div class="testimonial-card">
            <p class="testimonial-card__quote">"[Client testimonial goes here]"</p>
            <p class="testimonial-card__author">Name</p>
            <p class="testimonial-card__role">Role, Organisation</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Work process -->
    <section class="section" style="background: var(--color-surface);">
      <div class="container">
        <div class="section-head">
          <span class="badge">Work Process</span>
          <h2>How it works</h2>
        </div>
        <ul class="process-list">
          <li>Stringent vetting and screening, including DBS checks and UK right-to-work verification.</li>
          <li>One point of contact covering a full range of patient conditions and care needs.</li>
          <li>Coverage across social care disciplines, including NHS, care homes, and supported living.</li>
          <li>A care-standards-compliant service from first enquiry to placement.</li>
          <li>Experience managing TUPE transfers from an outgoing supplier's staff.</li>
          <li>A dedicated account manager for fast responses when plans change.</li>
          <li>Straightforward invoicing and review documentation, on your schedule.</li>
        </ul>
      </div>
    </section>

    <!-- Value band -->
    <section class="section section--tight value-band">
      <div class="container card-grid card-grid--3">
        <div class="stat-card">
          <p class="stat-card__value">DBS-Checked</p>
          <p class="stat-card__label">Every member of staff is vetted before they're ever placed.</p>
        </div>
        <div class="stat-card">
          <p class="stat-card__value">Fully Trained</p>
          <p class="stat-card__label">Structured training completed before the first shift, every time.</p>
        </div>
        <div class="stat-card">
          <p class="stat-card__value">24/7 Support</p>
          <p class="stat-card__label">A dedicated account manager, not a call queue.</p>
        </div>
      </div>
    </section>

    <!-- News teaser -->
    <section class="section">
      <div class="container">
        <div class="section-head">
          <span class="badge">Recent News</span>
          <h2>Latest from Plutobv</h2>
        </div>
        <div class="card-grid card-grid--3">
          <article class="news-card">
            <img class="news-card__image" src="/assets/images/news-01.jpg" alt="" loading="lazy">
            <div class="news-card__body">
              <span class="news-card__date">News</span>
              <h3 class="news-card__title">What Live-in Care Really Looks Like Day to Day</h3>
              <a class="news-card__link" href="/news/live-in-care-day-to-day.html">Read More</a>
            </div>
          </article>
          <article class="news-card">
            <img class="news-card__image" src="/assets/images/news-02.jpg" alt="" loading="lazy">
            <div class="news-card__body">
              <span class="news-card__date">News</span>
              <h3 class="news-card__title">Domiciliary Care: Support That Fits Around Your Life</h3>
              <a class="news-card__link" href="/news/domiciliary-care-fits-your-life.html">Read More</a>
            </div>
          </article>
          <article class="news-card">
            <img class="news-card__image" src="/assets/images/news-03.jpg" alt="" loading="lazy">
            <div class="news-card__body">
              <span class="news-card__date">News</span>
              <h3 class="news-card__title">Why Companionship Care Matters More Than We Think</h3>
              <a class="news-card__link" href="/news/why-companionship-care-matters.html">Read More</a>
            </div>
          </article>
        </div>
        <p style="text-align:center; margin-top: var(--space-6);">
          <a class="btn btn--dark-outline" href="/news.html">View All News</a>
        </p>
      </div>
    </section>

    <!-- CTA band -->
    <section class="cta-band">
      <div class="container cta-band__inner">
        <h2>Need reliable care staff, or looking for care work?</h2>
        <div class="hero__actions">
          <a class="btn btn--primary btn--lg" href="/contact.html">Contact Us</a>
          <a class="btn btn--outline btn--lg" href="/apply.html">Apply Now</a>
        </div>
      </div>
    </section>
  </main>

  <!-- FOOTER: shared block from Task 4 -->
  <footer class="site-footer">
    <div class="container footer__grid">
      <div class="footer__col">
        <span class="brand__name">Plutobv</span>
        <p class="text-muted">
          Plutobv supplies vetted, trained health and social care staff to local
          authorities, the NHS, and private care providers, so the people in their
          care are never without support.
        </p>
      </div>
      <div class="footer__col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="/about.html">About Us</a></li>
          <li><a href="/services.html">Services</a></li>
          <li><a href="/staffing-solutions.html">Staffing Solutions</a></li>
          <li><a href="/news.html">News</a></li>
          <li><a href="/contact.html">Contact Us</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4>Work With Us</h4>
        <ul>
          <li><a href="/apply.html">Apply Now</a></li>
          <li><a href="/forms/application-form-print.html">Print Application Form</a></li>
          <li><a href="/forms/reference-form-print.html">Print Reference Form</a></li>
          <li><a href="/timesheet.html">Submit Timesheet</a></li>
          <li><a href="/forms/timesheet-print.html">Print Timesheet</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4>Contact</h4>
        <ul>
          <li>[Your Street Address]</li>
          <li><a href="mailto:info@plutobv.co.uk">info@plutobv.co.uk</a></li>
          <li>[Your Phone Number]</li>
        </ul>
      </div>
    </div>
    <div class="container footer__bottom">
      <p>&copy; <span id="year"></span> Plutobv. All rights reserved.</p>
      <ul class="footer__legal">
        <li><a href="/privacy-policy.html">Privacy Policy</a></li>
        <li><a href="/terms.html">Terms</a></li>
      </ul>
    </div>
  </footer>

  <script src="/assets/js/main.js"></script>
</body>
</html>
```

- [ ] **Step 2: Write `assets/css/pages.css`**

```css
/* ---- Hero ---- */
.hero__grid {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: var(--space-8);
  align-items: center;
  padding-block: var(--space-8);
}

.hero__content { display: flex; flex-direction: column; gap: var(--space-4); align-items: flex-start; }
.hero__actions { display: flex; gap: var(--space-4); flex-wrap: wrap; margin-top: var(--space-3); }
.hero__media img { border-radius: var(--radius-lg); box-shadow: var(--shadow-md); width: 100%; height: auto; }

@media (max-width: 900px) {
  .hero__grid { grid-template-columns: 1fr; }
  .hero__media { order: -1; }
}

/* ---- Section head ---- */
.section-head { max-width: 640px; margin-bottom: var(--space-7); display: flex; flex-direction: column; gap: var(--space-3); }

/* ---- Feature grid (How We Do It) ---- */
.feature-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-6);
}

@media (max-width: 760px) {
  .feature-grid { grid-template-columns: 1fr; }
}

/* ---- Card grid (services / stats / news) ---- */
.card-grid { display: grid; gap: var(--space-6); }
.card-grid--3 { grid-template-columns: repeat(3, 1fr); }
.card-grid--4 { grid-template-columns: repeat(4, 1fr); }

@media (max-width: 900px) {
  .card-grid--4 { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
  .card-grid--3, .card-grid--4 { grid-template-columns: 1fr; }
}

/* ---- Testimonials ---- */
.testimonial-track {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-5);
}

@media (max-width: 900px) {
  .testimonial-track { grid-template-columns: 1fr; }
}

/* ---- Work process ---- */
.process-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-4) var(--space-6);
  counter-reset: process;
}

.process-list li {
  position: relative;
  padding-left: var(--space-7);
  color: var(--color-text);
}

.process-list li::before {
  counter-increment: process;
  content: counter(process);
  position: absolute;
  left: 0;
  top: 0;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--color-accent);
  color: #fff;
  font-weight: 700;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 760px) {
  .process-list { grid-template-columns: 1fr; }
}

/* ---- Value band ---- */
.value-band { background: var(--color-primary-light); }

/* ---- CTA band ---- */
.cta-band {
  background: var(--color-primary);
  color: #fff;
  padding-block: var(--space-8);
}

.cta-band__inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--space-5);
}

.cta-band h2 { color: #fff; margin: 0; max-width: 520px; }
```

- [ ] **Step 3: Open the page in the browser and check it renders**

Open `plutobv-website/index.html` directly in the browser pane (or serve the
folder with a static file server) and confirm: header topbar/nav/logo render,
mobile menu toggles below 900px width, hero image displays, all four service
cards, testimonial cards, the 7-item process list, the value band, and the three
news cards all render, footer year shows the current year, and no console errors
are logged.
Expected: page matches the section order above with no layout breakage at
375px, 768px, and 1280px widths.

- [ ] **Step 4: Commit**

```bash
git add index.html assets/css/pages.css
git commit -m "feat: add home page"
```

---

### Task 8: About page

**Files:**
- Create: `plutobv-website/about.html`
- Modify: `plutobv-website/assets/css/pages.css` (append `.about-hero`, `.value-grid`)

**Interfaces:**
- Consumes: header/footer pattern from Task 7 (same markup, `About Us` link gets
  `aria-current="page"` instead of `Home`), `.feature-grid`/`.card-grid` classes
  from Task 7, `about-team.jpg` from Task 6.

- [ ] **Step 1: Write `about.html`**

Use the exact `<head>` pattern from Task 7 with:
- `<title>About Us | Plutobv</title>`
- `<meta name="description" content="Plutobv recruits, trains, and places vetted health and social care staff. Learn about our approach to staffing the people who care for others.">`

Use the exact header markup from Task 7, with `aria-current="page"` moved to the
`About Us` link instead of `Home`. Use the exact footer markup from Task 7
unchanged. Between them, `<main id="main">` contains:

```html
<section class="section about-hero">
  <div class="container hero__grid">
    <div class="hero__content">
      <span class="badge">About Plutobv</span>
      <h1>Staffing built around the people being cared for</h1>
      <p>
        Plutobv recruits and places health care assistants and support workers
        across the public and private sector. We work with local authorities,
        the NHS, and private residential homes to supply staff who are properly
        vetted, properly trained, and genuinely suited to the people they'll be
        supporting.
      </p>
      <p class="text-muted">
        We started Plutobv because care staffing shouldn't feel like a numbers
        game. Every placement starts with understanding the person being cared
        for, not just filling a shift.
      </p>
    </div>
    <div class="hero__media">
      <img src="/assets/images/about-team.jpg" alt="A small team of Plutobv care and support workers standing together in a bright office" width="720" height="480" loading="eager">
    </div>
  </div>
</section>

<section class="section section--tight" style="background: var(--color-surface);">
  <div class="container">
    <div class="section-head">
      <span class="badge">What We Value</span>
      <h2>How we work</h2>
    </div>
    <div class="value-grid feature-grid">
      <div class="feature">
        <h3>Careful vetting</h3>
        <p class="text-muted">Every member of staff is DBS-checked and right-to-work verified before they're ever placed with a client.</p>
      </div>
      <div class="feature">
        <h3>Real training</h3>
        <p class="text-muted">Structured training completed before the first shift — not a induction chat on the way out the door.</p>
      </div>
      <div class="feature">
        <h3>One point of contact</h3>
        <p class="text-muted">A dedicated account manager who knows your setting, not a rotating call centre queue.</p>
      </div>
      <div class="feature">
        <h3>Compliance-first</h3>
        <p class="text-muted">Care-standards-compliant processes, TUPE experience, and documentation that holds up to scrutiny.</p>
      </div>
    </div>
  </div>
</section>

<section class="cta-band">
  <div class="container cta-band__inner">
    <h2>Want to work with us, or work for us?</h2>
    <div class="hero__actions">
      <a class="btn btn--primary btn--lg" href="/contact.html">Contact Us</a>
      <a class="btn btn--outline btn--lg" href="/apply.html">Apply Now</a>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Append to `assets/css/pages.css`**

```css
/* ---- About page ---- */
.about-hero { padding-block: var(--space-8); }
.value-grid.feature-grid { grid-template-columns: repeat(4, 1fr); }

@media (max-width: 900px) {
  .value-grid.feature-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 560px) {
  .value-grid.feature-grid { grid-template-columns: 1fr; }
}
```

- [ ] **Step 3: Open `about.html` in the browser and check it renders**

Confirm the hero grid, four value cards, and CTA band render correctly at 375px,
768px, and 1280px, nav shows "About Us" as the current page, and no console
errors are logged.
Expected: matches the section order above, no layout breakage.

- [ ] **Step 4: Commit**

```bash
git add about.html assets/css/pages.css
git commit -m "feat: add about page"
```

---

### Task 9: Services overview + 4 service detail pages

**Files:**
- Create: `plutobv-website/services.html`
- Create: `plutobv-website/services/live-in-care.html`
- Create: `plutobv-website/services/domiciliary-care.html`
- Create: `plutobv-website/services/companionship-care.html`
- Create: `plutobv-website/services/autism-support.html`
- Modify: `plutobv-website/assets/css/pages.css` (append `.detail-list`)

**Interfaces:**
- Consumes: header/footer pattern (Services link gets `aria-current="page"` on the
  overview page; on detail pages, the `Services` submenu item's own `<a>` gets
  `aria-current="page"` instead), `.hero__grid`/`.card-grid`/`.service-card`
  classes, and `service-*.jpg` images from Task 6. All four detail pages live one
  folder deeper (`/services/…`) but every link/asset path stays root-relative
  (`/assets/...`, `/index.html`, etc.) exactly as in Task 4/7 — no `../` paths.

- [ ] **Step 1: Write `services.html`**

Same `<head>`/header/footer pattern as Task 8, with `<title>Our Services | Plutobv</title>`,
meta description `"Live-in care, domiciliary care, companionship care, and autism
support — vetted, trained staff placed by Plutobv."`, and `Services` marked
`aria-current="page"`. `<main id="main">`:

```html
<section class="section">
  <div class="container section-head" style="max-width: 720px;">
    <span class="badge">Our Services</span>
    <h1>Care staffing across every setting</h1>
    <p>
      Every placement starts with the person being cared for. Choose a service
      below to see what's included, or get in touch and we'll help you work out
      what fits.
    </p>
  </div>
  <div class="container card-grid card-grid--4">
    <a class="service-card" href="/services/live-in-care.html">
      <h3>Live-in Care</h3>
      <p class="text-muted">Round-the-clock support in the comfort of your own home.</p>
    </a>
    <a class="service-card" href="/services/domiciliary-care.html">
      <h3>Domiciliary Care</h3>
      <p class="text-muted">Visiting support with daily tasks and personal care.</p>
    </a>
    <a class="service-card" href="/services/companionship-care.html">
      <h3>Companionship Care</h3>
      <p class="text-muted">Regular company and conversation, on a consistent schedule.</p>
    </a>
    <a class="service-card" href="/services/autism-support.html">
      <h3>Autism Support</h3>
      <p class="text-muted">Support workers experienced with Autism Spectrum Disorders.</p>
    </a>
  </div>
</section>

<section class="cta-band">
  <div class="container cta-band__inner">
    <h2>Not sure which service fits?</h2>
    <div class="hero__actions">
      <a class="btn btn--primary btn--lg" href="/contact.html">Talk To Us</a>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Write `services/live-in-care.html`**

Same `<head>`/header/footer pattern, `<title>Live-in Care | Plutobv</title>`, meta
description `"Round-the-clock live-in care from Plutobv, delivered in the comfort
of your own home."`, `Services` submenu link marked `aria-current="page"`.
`<main id="main">`:

```html
<section class="section about-hero">
  <div class="container hero__grid">
    <div class="hero__content">
      <span class="badge">Live-in Care</span>
      <h1>Live-in care that keeps people at home</h1>
      <p>
        Round-the-clock support in the comfort of your own home, from a carer
        who gets to know you, not just your file.
      </p>
    </div>
    <div class="hero__media">
      <img src="/assets/images/service-live-in-care.jpg" alt="A live-in carer helping an elderly man prepare tea in his kitchen" width="720" height="480" loading="eager">
    </div>
  </div>
</section>
<section class="section section--tight" style="background: var(--color-surface);">
  <div class="container">
    <div class="section-head">
      <h2>What's included</h2>
    </div>
    <ul class="detail-list">
      <li>Overnight support and a consistent, familiar carer</li>
      <li>Help with personal care and medication prompts</li>
      <li>Meal preparation around your routine and preferences</li>
      <li>Support keeping up hobbies, routines, and social contact</li>
      <li>Regular check-ins with family and your care coordinator</li>
    </ul>
    <div class="section-head" style="margin-top: var(--space-7);">
      <h2>Who it's for</h2>
      <p class="text-muted">
        People who want to remain in their own home rather than move into
        residential care — including those recovering from a hospital stay,
        living with dementia, or managing a long-term condition.
      </p>
    </div>
  </div>
</section>
<section class="cta-band">
  <div class="container cta-band__inner">
    <h2>Ready to talk about live-in care?</h2>
    <div class="hero__actions">
      <a class="btn btn--primary btn--lg" href="/contact.html">Contact Us</a>
    </div>
  </div>
</section>
```

- [ ] **Step 3: Write `services/domiciliary-care.html`**

Same pattern. `<title>Domiciliary Care | Plutobv</title>`, meta description
`"Visiting domiciliary care from Plutobv, scheduled around your day."`. Hero:
badge "Domiciliary Care", h1 "Visiting care that fits around your day", intro
"Support with the tasks that matter, delivered on a schedule that works for you —
from a short daily visit to several calls a day.", image
`/assets/images/service-domiciliary-care.jpg` alt "A care worker arriving at a
client's front door". What's included: "Personal care visits", "Help with
washing and dressing", "Medication support", "Light household tasks and meal
preparation", "Mobility support and companionship during visits". Who it's for:
"People who need regular support but want to stay independent in their own home,
and don't need overnight care." CTA heading: "Ready to talk about domiciliary
care?"

- [ ] **Step 4: Write `services/companionship-care.html`**

Same pattern. `<title>Companionship Care | Plutobv</title>`, meta description
`"Companionship care from Plutobv — regular, reliable company for people at risk
of isolation."`. Hero: badge "Companionship Care", h1 "Because no one should have
no one", intro "Regular company and conversation for people who would otherwise
spend long stretches of time alone.", image
`/assets/images/service-companionship-care.jpg` alt "A support worker and an
elderly woman laughing together over a board game". What's included: "Regular
social visits", "Accompanying clients to appointments or on errands", "Shared
hobbies and activities", "Help staying connected with family and friends", "A
consistent, familiar face, not a rotating roster". Who it's for: "People living
alone, recently bereaved, or at risk of isolation, where company matters as much
as physical care." CTA heading: "Ready to talk about companionship care?"

- [ ] **Step 5: Write `services/autism-support.html`**

Same pattern. `<title>Autism Support | Plutobv</title>`, meta description
`"Autism support staffing from Plutobv — experienced support workers for complex
needs."`. Hero: badge "Autism Support", h1 "Support workers who understand
autism", intro "Experienced support for individuals with a diagnosis of Autism
Spectrum Disorder, complex needs, and behaviours that challenge.", image
`/assets/images/service-autism-support.jpg` alt "A support worker and a young
autistic adult doing an art activity together". What's included: "Support
workers trained in autism-specific approaches", "Consistent routines and
predictable visits", "Positive behaviour support", "Support at home, in
education, or in the community", "Close coordination with family and other
professionals". Who it's for: "Individuals with ASD and their families who need
support that's genuinely tailored to how they communicate and what helps them
feel secure." CTA heading: "Ready to talk about autism support?"

- [ ] **Step 6: Append to `assets/css/pages.css`**

```css
/* ---- Service detail pages ---- */
.detail-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 640px;
}

.detail-list li {
  padding-left: var(--space-6);
  position: relative;
}

.detail-list li::before {
  content: "";
  position: absolute;
  left: 0;
  top: 0.5em;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--color-accent);
}
```

- [ ] **Step 7: Open all 5 pages in the browser and check them render**

Confirm each page's hero, list, and CTA band render at 375px, 768px, and 1280px,
that the `Services` nav item (and, on detail pages, the correct submenu item)
shows as current, that all internal links between the 5 pages resolve (no 404s),
and no console errors are logged.
Expected: consistent layout across all 5 pages, all links work.

- [ ] **Step 8: Commit**

```bash
git add services.html services/ assets/css/pages.css
git commit -m "feat: add services overview and 4 service detail pages"
```

---

### Task 10: Areas We Support + Staffing Solutions pages

**Files:**
- Create: `plutobv-website/areas-we-support.html`
- Create: `plutobv-website/staffing-solutions.html`

**Interfaces:**
- Consumes: header/footer pattern, `.hero__grid`/`.feature-grid`/`.detail-list`/
  `.cta-band` classes, `about-team.jpg` and `staffing-solutions.jpg` from Task 6.

Note on content honesty: rather than listing specific real towns/counties Plutobv
"covers" (which would be an unverifiable, possibly false claim for a new
business), Areas We Support is written around the *kinds* of settings covered
and asks the visitor to check availability directly — consistent with the
Global Constraint against invented factual claims.

- [ ] **Step 1: Write `areas-we-support.html`**

Same `<head>`/header/footer pattern as Task 8. `<title>Areas We Support | Plutobv</title>`,
meta description `"The settings Plutobv places care staff into, and how to check
coverage in your area."`, `Areas We Support` marked `aria-current="page"`.
`<main id="main">`:

```html
<section class="section about-hero">
  <div class="container hero__grid">
    <div class="hero__content">
      <span class="badge">Areas We Support</span>
      <h1>Wherever care happens, we can place staff</h1>
      <p>
        We place care and support staff into private homes, residential and
        nursing homes, supported living settings, and NHS and local authority
        services. Coverage depends on staff availability in your area —
        currently placing across <strong>[Your Coverage Area]</strong>.
      </p>
      <div class="hero__actions">
        <a class="btn btn--primary btn--lg" href="/contact.html">Check Availability</a>
      </div>
    </div>
    <div class="hero__media">
      <img src="/assets/images/about-team.jpg" alt="A small team of Plutobv care and support workers standing together in a bright office" width="720" height="480" loading="eager">
    </div>
  </div>
</section>
<section class="section section--tight" style="background: var(--color-surface);">
  <div class="container">
    <div class="section-head">
      <span class="badge">Settings We Cover</span>
      <h2>The places we place staff</h2>
    </div>
    <div class="feature-grid">
      <div class="feature">
        <h3>Private homes</h3>
        <p class="text-muted">Live-in and visiting care delivered directly in a client's own home.</p>
      </div>
      <div class="feature">
        <h3>Residential &amp; nursing homes</h3>
        <p class="text-muted">Shift cover and longer-term placements for care home teams.</p>
      </div>
      <div class="feature">
        <h3>Supported living</h3>
        <p class="text-muted">Consistent staff for supported living settings, including autism and complex needs support.</p>
      </div>
      <div class="feature">
        <h3>NHS &amp; local authority</h3>
        <p class="text-muted">Compliant, documented staffing for public sector care services.</p>
      </div>
    </div>
  </div>
</section>
<section class="cta-band">
  <div class="container cta-band__inner">
    <h2>Not sure if we cover your area?</h2>
    <div class="hero__actions">
      <a class="btn btn--primary btn--lg" href="/contact.html">Ask Us</a>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Write `staffing-solutions.html`**

Same `<head>`/header/footer pattern. `<title>Staffing Solutions | Plutobv</title>`,
meta description `"Temporary, permanent, and emergency care staffing solutions
for care homes, the NHS, and local authorities."`, `Staffing Solutions` marked
`aria-current="page"`. `<main id="main">`:

```html
<section class="section about-hero">
  <div class="container hero__grid">
    <div class="hero__content">
      <span class="badge">Staffing Solutions</span>
      <h1>Staffing solutions built for care providers</h1>
      <p>
        Whether you need one shift covered tomorrow or an ongoing staffing
        partnership, Plutobv supplies vetted, trained health and social care
        staff to local authorities, the NHS, and private care providers.
      </p>
      <div class="hero__actions">
        <a class="btn btn--primary btn--lg" href="/contact.html">Discuss Your Needs</a>
      </div>
    </div>
    <div class="hero__media">
      <img src="/assets/images/staffing-solutions.jpg" alt="A recruiter and a care worker reviewing a shift schedule together on a tablet" width="720" height="480" loading="eager">
    </div>
  </div>
</section>
<section class="section section--tight" style="background: var(--color-surface);">
  <div class="container">
    <div class="section-head">
      <h2>What we offer care providers</h2>
    </div>
    <ul class="detail-list">
      <li>Temporary staffing for short-notice or one-off shift cover</li>
      <li>Permanent placement recruitment for hard-to-fill roles</li>
      <li>Emergency cover for unplanned absences</li>
      <li>Managed staffing for ongoing, block-booked contracts</li>
      <li>TUPE transfer management when staff move between suppliers</li>
      <li>Compliance documentation and care-standards-aligned processes</li>
    </ul>
  </div>
</section>
<section class="cta-band">
  <div class="container cta-band__inner">
    <h2>Let's talk about your staffing needs</h2>
    <div class="hero__actions">
      <a class="btn btn--primary btn--lg" href="/contact.html">Contact Us</a>
    </div>
  </div>
</section>
```

- [ ] **Step 3: Open both pages in the browser and check them render**

Confirm both pages' hero, feature grid / detail list, and CTA band render at
375px, 768px, and 1280px, the correct nav item shows as current on each, and no
console errors are logged.
Expected: consistent layout with the pages from Tasks 7–9, no broken links.

- [ ] **Step 4: Commit**

```bash
git add areas-we-support.html staffing-solutions.html
git commit -m "feat: add areas-we-support and staffing-solutions pages"
```

---

### Task 11: Contact, Apply, and Timesheet page UI (markup only, no backend wiring yet)

**Files:**
- Create: `plutobv-website/contact.html`
- Create: `plutobv-website/apply.html`
- Create: `plutobv-website/timesheet.html`
- Modify: `plutobv-website/assets/css/pages.css` (append `.page-hero`, `.form-page`)

**Interfaces:**
- Consumes: header/footer pattern, `.form`/`.form-field`/`.form-field--honeypot`/
  `.form-success`/`.form-error` classes from Task 5.
- Produces: the anti-abuse field pattern every form on the site uses — a visually
  hidden honeypot input named `website`, and a hidden `form_started` input that
  Task 16's JS fills with the load timestamp. This replaces a traditional CSRF
  token: these forms are anonymous and session-less, so there's no session to
  hijack — honeypot + timing + Task 17's same-origin Referer check cover the
  actual risk (spam/bot abuse) without machinery that protects nothing here.
  Tasks 18–20's PHP handlers consume both fields by these exact names. Each
  form's `action` points at its Task 18–20 handler; each includes a hidden
  `redirect` field pointing at `/thank-you.html`
  for the no-JS fallback path.

- [ ] **Step 1: Write `contact.html`**

Same `<head>`/header/footer pattern. `<title>Contact Us | Plutobv</title>`, meta
description `"Get in touch with Plutobv about care staffing or care work."`,
`Contact Us` marked `aria-current="page"`. `<main id="main">`:

```html
<section class="section page-hero">
  <div class="container section-head">
    <span class="badge">Contact Us</span>
    <h1>Let's talk</h1>
    <p>Whether you need staff or you're looking for care work, send us a message and we'll get back to you.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container form-page">
    <form class="form" id="contact-form" action="/backend/contact-handler.php" method="POST" novalidate>
      <div class="form-field" data-field="name">
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" required autocomplete="name">
      </div>
      <div class="form-field" data-field="email">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required autocomplete="email">
      </div>
      <div class="form-field" data-field="subject">
        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" required>
      </div>
      <div class="form-field" data-field="message">
        <label for="message">Message</label>
        <textarea id="message" name="message" required></textarea>
      </div>
      <div class="form-field form-field--honeypot" aria-hidden="true">
        <label for="website">Website</label>
        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" name="form_started" value="">
      <input type="hidden" name="redirect" value="/thank-you.html">
      <div class="form-status" role="status" aria-live="polite"></div>
      <button class="btn btn--primary btn--lg" type="submit">Send Message</button>
    </form>
  </div>
</section>
```

- [ ] **Step 2: Write `apply.html`**

Same `<head>`/header/footer pattern. `<title>Apply Now | Plutobv</title>`, meta
description `"Apply to join the Plutobv care and support staff team."`. No nav
item corresponds directly to this page, so no `aria-current` change is needed.
`<main id="main">`:

```html
<section class="section page-hero">
  <div class="container section-head">
    <span class="badge">Apply Now</span>
    <h1>Join the Plutobv team</h1>
    <p>Tell us about yourself and attach your CV — we'll be in touch about current opportunities.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container form-page">
    <form class="form" id="apply-form" action="/backend/apply-handler.php" method="POST" enctype="multipart/form-data" novalidate>
      <div class="form-field" data-field="name">
        <label for="apply-name">Full name</label>
        <input type="text" id="apply-name" name="name" required autocomplete="name">
      </div>
      <div class="form-field" data-field="email">
        <label for="apply-email">Email address</label>
        <input type="email" id="apply-email" name="email" required autocomplete="email">
      </div>
      <div class="form-field" data-field="phone">
        <label for="apply-phone">Phone number</label>
        <input type="tel" id="apply-phone" name="phone" required autocomplete="tel">
      </div>
      <div class="form-field" data-field="position">
        <label for="apply-position">Role you're applying for</label>
        <select id="apply-position" name="position" required>
          <option value="">Select a role</option>
          <option value="Live-in Carer">Live-in Carer</option>
          <option value="Domiciliary Care Worker">Domiciliary Care Worker</option>
          <option value="Support Worker">Support Worker</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="form-field" data-field="message">
        <label for="apply-message">Tell us about your experience</label>
        <textarea id="apply-message" name="message" required></textarea>
      </div>
      <div class="form-field" data-field="cv">
        <label for="apply-cv">CV (PDF or Word, max 5MB)</label>
        <input type="file" id="apply-cv" name="cv" accept=".pdf,.doc,.docx" required>
      </div>
      <div class="form-field form-field--honeypot" aria-hidden="true">
        <label for="apply-website">Website</label>
        <input type="text" id="apply-website" name="website" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" name="form_started" value="">
      <input type="hidden" name="redirect" value="/thank-you.html">
      <div class="form-status" role="status" aria-live="polite"></div>
      <button class="btn btn--primary btn--lg" type="submit">Submit Application</button>
    </form>
  </div>
</section>
```

- [ ] **Step 3: Write `timesheet.html`**

Same `<head>`/header/footer pattern. `<title>Submit Timesheet | Plutobv</title>`,
meta description `"Submit your timesheet to Plutobv."`. `<main id="main">`:

```html
<section class="section page-hero">
  <div class="container section-head">
    <span class="badge">Timesheets</span>
    <h1>Submit your timesheet</h1>
    <p>Upload your completed timesheet and we'll process it. Need a blank copy first? <a href="/forms/timesheet-print.html">Print one here</a>.</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container form-page">
    <form class="form" id="timesheet-form" action="/backend/timesheet-handler.php" method="POST" enctype="multipart/form-data" novalidate>
      <div class="form-field" data-field="name">
        <label for="ts-name">Full name</label>
        <input type="text" id="ts-name" name="name" required autocomplete="name">
      </div>
      <div class="form-field" data-field="reference">
        <label for="ts-reference">Staff reference / employee ID</label>
        <input type="text" id="ts-reference" name="reference" required>
      </div>
      <div class="form-field" data-field="week_ending">
        <label for="ts-week-ending">Week ending</label>
        <input type="date" id="ts-week-ending" name="week_ending" required>
      </div>
      <div class="form-field" data-field="timesheet">
        <label for="ts-file">Timesheet file (PDF, JPG, or PNG, max 5MB)</label>
        <input type="file" id="ts-file" name="timesheet" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>
      <div class="form-field" data-field="notes">
        <label for="ts-notes">Notes (optional)</label>
        <textarea id="ts-notes" name="notes"></textarea>
      </div>
      <div class="form-field form-field--honeypot" aria-hidden="true">
        <label for="ts-website">Website</label>
        <input type="text" id="ts-website" name="website" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" name="form_started" value="">
      <input type="hidden" name="redirect" value="/thank-you.html">
      <div class="form-status" role="status" aria-live="polite"></div>
      <button class="btn btn--primary btn--lg" type="submit">Submit Timesheet</button>
    </form>
  </div>
</section>
```

- [ ] **Step 4: Append to `assets/css/pages.css`**

```css
/* ---- Form pages ---- */
.page-hero { padding-block: var(--space-7) var(--space-5); }
.form-page { max-width: 640px; }
.form-status:empty { display: none; }
```

- [ ] **Step 5: Open all three pages and check the markup renders**

Confirm all fields, labels, and the submit button render correctly, the honeypot
field is visually hidden but present in the DOM, file inputs accept the right
extensions, and tabbing through each form skips the honeypot field (its
`tabindex="-1"`). Forms will not yet submit successfully (`Task 16` wires JS,
`Tasks 18–20` build the handlers) — confirm only markup/layout at this step.
Expected: three visually consistent, accessible forms; no console errors.

- [ ] **Step 6: Commit**

```bash
git add contact.html apply.html timesheet.html assets/css/pages.css
git commit -m "feat: add contact, apply, and timesheet form pages"
```

---

### Task 12: Printable paper forms (stand-ins for PDFs we don't have)

**Files:**
- Create: `plutobv-website/forms/application-form-print.html`
- Create: `plutobv-website/forms/reference-form-print.html`
- Create: `plutobv-website/forms/timesheet-print.html`
- Create: `plutobv-website/assets/css/print.css`

**Interfaces:**
- Consumes: header/footer pattern (unchanged, no nav item marked current — these
  pages aren't in the primary nav).
- Produces: `.print-form`, `.print-field`, `.print-table` classes and a
  `[data-action="print"]` button hook that Task 15's JS wires to `window.print()`.

These are hand-fill paper forms (label + blank line, or a blank table), not data
inputs — visitors print them, fill them by hand, and either post them in or scan
them back via the Apply/Timesheet upload pages.

- [ ] **Step 1: Write `forms/application-form-print.html`**

Same `<head>`/header/footer pattern, plus `<link rel="stylesheet" href="/assets/css/print.css">`
after the other stylesheets. `<title>Application Form | Plutobv</title>`, meta
description `"Printable job application form for Plutobv."`. `<main id="main">`:

```html
<section class="section page-hero no-print">
  <div class="container section-head">
    <span class="badge">Printable Form</span>
    <h1>Application Form</h1>
    <p>Print this form, fill it in by hand, and post it to us — or fill in our <a href="/apply.html">online application</a> instead.</p>
    <button type="button" class="btn btn--primary" data-action="print">Print This Form</button>
  </div>
</section>
<section class="section section--tight">
  <div class="container print-form">
    <h2>Plutobv &mdash; Job Application Form</h2>
    <div class="print-field"><span>Full name</span></div>
    <div class="print-field"><span>Address</span></div>
    <div class="print-field"><span>Phone</span></div>
    <div class="print-field"><span>Email</span></div>
    <div class="print-field"><span>National Insurance number</span></div>
    <div class="print-field"><span>Position applied for</span></div>
    <div class="print-field"><span>Availability (full-time / part-time / flexible)</span></div>
    <div class="print-field"><span>Do you have the right to work in the UK? (yes / no)</span></div>

    <h3>Employment history</h3>
    <table class="print-table">
      <thead><tr><th>Employer</th><th>Position</th><th>Dates</th><th>Reason for leaving</th></tr></thead>
      <tbody>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
      </tbody>
    </table>

    <h3>Education &amp; qualifications</h3>
    <table class="print-table">
      <thead><tr><th>Institution / Awarding body</th><th>Qualification</th><th>Year</th></tr></thead>
      <tbody>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
      </tbody>
    </table>

    <p class="text-muted" style="margin-top: var(--space-6);">
      I declare that the information given on this form is true and complete to
      the best of my knowledge.
    </p>
    <div class="print-field print-field--half"><span>Signature</span></div>
    <div class="print-field print-field--half"><span>Date</span></div>
  </div>
</section>
```

- [ ] **Step 2: Write `forms/reference-form-print.html`**

Same pattern. `<title>Reference Form | Plutobv</title>`, meta description
`"Printable employment reference form for Plutobv."`. Page hero heading
"Reference Form", intro "For a referee to fill in on behalf of a Plutobv
applicant.", print button as above. `.print-form` content:

```html
<h2>Plutobv &mdash; Reference Request Form</h2>
<div class="print-field"><span>Applicant's name</span></div>
<div class="print-field"><span>Referee's name</span></div>
<div class="print-field"><span>Referee's relationship to applicant</span></div>
<div class="print-field"><span>Referee's organisation</span></div>
<div class="print-field"><span>Referee's address</span></div>
<div class="print-field"><span>Referee's phone / email</span></div>
<div class="print-field"><span>How long have you known the applicant, and in what capacity?</span></div>
<div class="print-field print-field--tall"><span>Please comment on the applicant's reliability, honesty, and suitability for care work</span></div>
<div class="print-field"><span>Would you employ this person again? (yes / no, and why)</span></div>
<div class="print-field print-field--half"><span>Signature</span></div>
<div class="print-field print-field--half"><span>Date</span></div>
```

- [ ] **Step 3: Write `forms/timesheet-print.html`**

Same pattern. `<title>Timesheet | Plutobv</title>`, meta description `"Printable
weekly timesheet for Plutobv staff."`. Page hero heading "Timesheet", intro
"Print, fill in, and either post this in or upload a photo/scan via our <a
href=\"/timesheet.html\">timesheet submission page</a>.", print button as above.
`.print-form` content:

```html
<h2>Plutobv &mdash; Weekly Timesheet</h2>
<div class="print-field print-field--half"><span>Staff name</span></div>
<div class="print-field print-field--half"><span>Staff reference / employee ID</span></div>
<div class="print-field print-field--half"><span>Week ending</span></div>
<div class="print-field print-field--half"><span>Client / site name</span></div>

<table class="print-table">
  <thead><tr><th>Day</th><th>Time in</th><th>Time out</th><th>Break</th><th>Total hours</th></tr></thead>
  <tbody>
    <tr><td>Monday</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Tuesday</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Wednesday</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Thursday</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Friday</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Saturday</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Sunday</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
  </tbody>
</table>

<div class="print-field"><span>Total hours for week</span></div>
<div class="print-field print-field--half"><span>Staff signature / date</span></div>
<div class="print-field print-field--half"><span>Authorised by / date</span></div>
```

- [ ] **Step 4: Write `assets/css/print.css`**

```css
.print-form {
  max-width: 760px;
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.print-form h2 { margin-bottom: var(--space-2); }
.print-form h3 { margin-top: var(--space-4); }

.print-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.print-field span { font-weight: 700; font-size: 0.9rem; color: var(--color-text-muted); }
.print-field::after { content: ""; border-bottom: 1px solid var(--color-border); }
.print-field--tall::after { border-bottom: none; height: 80px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); }
.print-field--half { display: inline-flex; }

.print-table { width: 100%; border-collapse: collapse; }
.print-table th, .print-table td {
  border: 1px solid var(--color-border);
  padding: var(--space-2) var(--space-3);
  text-align: left;
  height: 2.2em;
}

@media print {
  .site-header, .site-footer, .no-print { display: none !important; }
  body { background: #fff; }
  .print-form { max-width: none; }
  a[href]::after { content: ""; }
}
```

- [ ] **Step 5: Open all three pages, check screen layout, and check print preview**

Confirm each renders correctly on screen, and that the browser's print preview
(or `@media print` emulation) hides the header/footer/print button and shows a
clean, single-column form.
Expected: three consistent printable forms; print preview hides site chrome.

- [ ] **Step 6: Commit**

```bash
git add forms/ assets/css/print.css
git commit -m "feat: add printable application, reference, and timesheet forms"
```

---

### Task 13: News index + 5 original articles

**Files:**
- Create: `plutobv-website/news.html`
- Create: `plutobv-website/news/live-in-care-day-to-day.html`
- Create: `plutobv-website/news/domiciliary-care-fits-your-life.html`
- Create: `plutobv-website/news/why-companionship-care-matters.html`
- Create: `plutobv-website/news/medication-safety-simple-and-consistent.html`
- Create: `plutobv-website/news/wellbeing-in-home-care.html`
- Modify: `plutobv-website/assets/css/pages.css` (append `.article`)

**Interfaces:**
- Consumes: header/footer pattern, `.news-card`/`.card-grid` classes, `news-01/02/03.jpg`
  from Task 6. `News` nav link marked `aria-current="page"` on all 6 pages (there's
  no separate submenu for news, unlike Services).
- All five articles are original writing on the same themes as the reference
  site's blog teasers — no copied sentences.

- [ ] **Step 1: Write `news.html`**

Same `<head>`/header/footer pattern. `<title>News | Plutobv</title>`, meta
description `"News and articles from Plutobv on live-in care, domiciliary care,
companionship, and home care best practice."`, `News` marked `aria-current="page"`.
`<main id="main">`:

```html
<section class="section page-hero">
  <div class="container section-head">
    <span class="badge">News</span>
    <h1>Latest from Plutobv</h1>
  </div>
</section>
<section class="section section--tight">
  <div class="container card-grid card-grid--3">
    <article class="news-card">
      <img class="news-card__image" src="/assets/images/news-01.jpg" alt="" loading="lazy">
      <div class="news-card__body">
        <span class="news-card__date">30 Aug 2026</span>
        <h3 class="news-card__title">What Live-in Care Really Looks Like Day to Day</h3>
        <a class="news-card__link" href="/news/live-in-care-day-to-day.html">Read More</a>
      </div>
    </article>
    <article class="news-card">
      <img class="news-card__image" src="/assets/images/news-02.jpg" alt="" loading="lazy">
      <div class="news-card__body">
        <span class="news-card__date">20 Aug 2026</span>
        <h3 class="news-card__title">Domiciliary Care: Support That Fits Around Your Life</h3>
        <a class="news-card__link" href="/news/domiciliary-care-fits-your-life.html">Read More</a>
      </div>
    </article>
    <article class="news-card">
      <img class="news-card__image" src="/assets/images/news-03.jpg" alt="" loading="lazy">
      <div class="news-card__body">
        <span class="news-card__date">5 Aug 2026</span>
        <h3 class="news-card__title">Why Companionship Care Matters More Than We Think</h3>
        <a class="news-card__link" href="/news/why-companionship-care-matters.html">Read More</a>
      </div>
    </article>
    <article class="news-card">
      <img class="news-card__image" src="/assets/images/news-01.jpg" alt="" loading="lazy">
      <div class="news-card__body">
        <span class="news-card__date">22 Jul 2026</span>
        <h3 class="news-card__title">Medication Safety: How We Keep It Simple and Consistent</h3>
        <a class="news-card__link" href="/news/medication-safety-simple-and-consistent.html">Read More</a>
      </div>
    </article>
    <article class="news-card">
      <img class="news-card__image" src="/assets/images/news-02.jpg" alt="" loading="lazy">
      <div class="news-card__body">
        <span class="news-card__date">10 Jul 2026</span>
        <h3 class="news-card__title">Small Connections, Big Impact: Wellbeing in Home Care</h3>
        <a class="news-card__link" href="/news/wellbeing-in-home-care.html">Read More</a>
      </div>
    </article>
  </div>
</section>
```

- [ ] **Step 2: Write `news/live-in-care-day-to-day.html`**

Same `<head>`/header/footer pattern, `News` marked `aria-current="page"`.
`<title>What Live-in Care Really Looks Like Day to Day | Plutobv</title>`, meta
description `"A look at what a typical day involves for a live-in carer and the
person they support."`. `<main id="main">`:

```html
<section class="section page-hero">
  <div class="container section-head">
    <span class="badge">30 Aug 2026</span>
    <h1>What Live-in Care Really Looks Like Day to Day</h1>
  </div>
</section>
<section class="section section--tight">
  <div class="container article">
    <img src="/assets/images/news-01.jpg" alt="" style="border-radius: var(--radius-lg); margin-bottom: var(--space-6);">
    <p>
      Live-in care sounds simple on paper: a carer moves in, and someone always
      has support on hand. In practice, what makes it work is much less about
      any single task and much more about rhythm — building a day that still
      feels like the client's own, just with help woven through it.
    </p>
    <p>
      A typical day starts with whatever the client's normal morning looks like:
      help getting up and dressed if it's needed, breakfast made the way they
      like it, and medication given on schedule. From there, the shape of the
      day is set by the person, not the carer — errands, appointments, hobbies,
      or simply time in the garden.
    </p>
    <p>
      What tends to surprise families is how much of live-in care is
      companionship rather than hands-on tasks: shared meals, conversation, and
      just having someone else in the house. That's often what makes the
      difference between "coping at home" and actually enjoying it.
    </p>
    <p>
      If you're weighing up live-in care for a family member, our
      <a href="/services/live-in-care.html">live-in care page</a> covers what's
      included, or you can <a href="/contact.html">get in touch</a> to talk it
      through.
    </p>
  </div>
</section>
```

- [ ] **Step 3: Write `news/domiciliary-care-fits-your-life.html`**

Same pattern. Badge "20 Aug 2026". `<title>Domiciliary Care: Support That Fits
Around Your Life | Plutobv</title>`, meta description `"How domiciliary care
visits work, and how to decide how many visits a day you actually need."`.
Article body (4 paragraphs):

```html
<p>
  Domiciliary care is built around visits rather than a live-in presence — a
  carer arrives for a set window, helps with specific tasks, and moves on to
  their next client. For a lot of people, that's exactly the right amount of
  support: enough help to stay safe and independent, without someone living in
  the spare room.
</p>
<p>
  Visits can be as short as thirty minutes for a medication check, or long
  enough to cover washing, dressing, and breakfast. Most clients start with one
  or two visits a day and adjust from there — it's rarely the right call to
  guess the number of visits up front and never revisit it.
</p>
<p>
  The tasks covered are practical: personal care, mobility support, light
  housework, meal preparation, and picking up prescriptions. Just as important
  is consistency — seeing a familiar face rather than a different carer every
  visit makes a real difference to how comfortable someone feels accepting
  help.
</p>
<p>
  Our <a href="/services/domiciliary-care.html">domiciliary care page</a> has
  more detail on what's included, or <a href="/contact.html">contact us</a> to
  talk through a visit schedule that fits.
</p>
```

- [ ] **Step 4: Write `news/why-companionship-care-matters.html`**

Same pattern. Badge "5 Aug 2026". `<title>Why Companionship Care Matters More
Than We Think | Plutobv</title>`, meta description `"Isolation is a health risk
in its own right — why companionship care belongs alongside physical care."`.
Article body (4 paragraphs):

```html
<p>
  It's easy to think of care in terms of tasks — washing, medication, meals —
  and treat company as a nice-to-have on top. In practice, isolation is a risk
  in its own right. Long stretches without conversation or company are linked
  to worse physical health outcomes, not just low mood.
</p>
<p>
  Companionship care is deliberately built around time, not tasks. A visit
  might mean a walk, a shared crossword, help writing letters, or just tea and
  conversation. There's no checklist to work through — the point is the
  company itself.
</p>
<p>
  It's often most valuable for people living alone after bereavement, or
  those whose family live too far away for regular visits. A consistent,
  familiar companion, seen on a predictable schedule, does more for wellbeing
  than an occasional visit from someone new each time.
</p>
<p>
  Read more on our <a href="/services/companionship-care.html">companionship
  care page</a>, or <a href="/contact.html">get in touch</a> if you think it
  might help someone you know.
</p>
```

- [ ] **Step 5: Write `news/medication-safety-simple-and-consistent.html`**

Same pattern. Badge "22 Jul 2026". `<title>Medication Safety: How We Keep It
Simple and Consistent | Plutobv</title>`, meta description `"How Plutobv care
staff approach medication support safely and consistently."`. Article body (4
paragraphs):

```html
<p>
  Medication mistakes are one of the most preventable risks in home care, and
  usually come down to inconsistency rather than any single error — a missed
  dose here, a mistimed one there. The fix isn't complicated: consistency,
  clear records, and staff who are trained to follow the same process every
  time.
</p>
<p>
  Every Plutobv care worker follows the same basic routine for medication
  support: confirm what's due against the client's medication list, prompt or
  assist as agreed with the client and their GP or pharmacist, and record what
  was given and when — every visit, without exception.
</p>
<p>
  We deliberately don't ask staff to make judgment calls about dosages or
  changes to medication. Anything outside the agreed plan gets raised with the
  client's family or healthcare provider rather than handled informally in the
  moment.
</p>
<p>
  It's a simple approach on purpose — the goal is that medication support
  looks the same whichever member of staff is on shift. If you have questions
  about how we handle medication for a specific care plan,
  <a href="/contact.html">get in touch</a>.
</p>
```

- [ ] **Step 6: Write `news/wellbeing-in-home-care.html`**

Same pattern. Badge "10 Jul 2026". `<title>Small Connections, Big Impact:
Wellbeing in Home Care | Plutobv</title>`, meta description `"Why the small,
everyday moments in home care visits matter as much as the practical tasks."`.
Article body (4 paragraphs):

```html
<p>
  Ask most care workers what they think actually matters most in a visit, and
  it's rarely the task list. It's the small moments around it — noticing
  someone seems quieter than usual, remembering how they take their tea,
  asking about a grandchild by name.
</p>
<p>
  Those details aren't small to the person receiving care. They're often the
  difference between a visit that feels transactional and one that feels like
  being looked after by someone who actually knows you.
</p>
<p>
  It's part of why we try to keep the same care worker with the same client
  wherever possible, rather than rotating staff for convenience. Familiarity
  is part of the care, not a bonus on top of it.
</p>
<p>
  If wellbeing and consistency matter as much to you as the practical side of
  care, that's exactly what we aim to deliver — <a href="/contact.html">get in
  touch</a> to talk about what that could look like.
</p>
```

- [ ] **Step 7: Append to `assets/css/pages.css`**

```css
/* ---- Article pages ---- */
.article {
  max-width: 720px;
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  font-size: 1.05rem;
}

.article img { width: 100%; height: auto; }
```

- [ ] **Step 8: Open all 6 pages and check they render, and check every link**

Confirm each article and the index render correctly at 375px, 768px, and 1280px,
`News` shows as the current nav item on all 6, every "Read More" link on the
index goes to the matching article, and every in-article link (to services or
contact) resolves. No console errors.
Expected: 6 consistent pages, no broken links.

- [ ] **Step 9: Commit**

```bash
git add news.html news/ assets/css/pages.css
git commit -m "feat: add news index and 5 original articles"
```

---

### Task 14: Privacy Policy, Terms, Thank-you, and 404 pages

**Files:**
- Create: `plutobv-website/privacy-policy.html`
- Create: `plutobv-website/terms.html`
- Create: `plutobv-website/thank-you.html`
- Create: `plutobv-website/404.html`
- Modify: `plutobv-website/assets/css/pages.css` (append `.legal`, `.status-page`)

**Interfaces:**
- Consumes: header/footer pattern, `.article` class from Task 13 for legal-page
  typography.
- The Privacy Policy's data-handling description must stay accurate to what the
  backend (Tasks 17–20) actually does — no persisted file storage, forms email
  their contents to `info@plutobv.co.uk`, no cookies or analytics anywhere on the
  site.

- [ ] **Step 1: Write `privacy-policy.html`**

Same `<head>`/header/footer pattern (no nav item marked current).
`<title>Privacy Policy | Plutobv</title>`, meta description `"How Plutobv
collects, uses, and protects the personal information submitted through this
site."`. `<main id="main">`:

```html
<section class="section page-hero">
  <div class="container section-head">
    <span class="badge">Legal</span>
    <h1>Privacy Policy</h1>
    <p class="text-muted">Last updated: 30 August 2026</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container article legal">
    <h2>Who we are</h2>
    <p>This policy covers personal information submitted through plutobv.co.uk. For any question about it, contact us at <a href="mailto:info@plutobv.co.uk">info@plutobv.co.uk</a>.</p>

    <h2>What we collect</h2>
    <p>We only collect what you submit through our forms:</p>
    <ul class="detail-list">
      <li><strong>Contact form:</strong> name, email address, subject, and message.</li>
      <li><strong>Apply Now form:</strong> name, email address, phone number, role applied for, your message, and your CV file.</li>
      <li><strong>Timesheet form:</strong> name, staff reference, week ending date, your uploaded timesheet file, and any notes.</li>
    </ul>

    <h2>Why we collect it</h2>
    <p>To respond to enquiries, to process job applications, and to process timesheet submissions from our staff. We do not use this information for marketing, and we do not sell or share it with third parties.</p>

    <h2>How it's stored</h2>
    <p>Form submissions, including uploaded CV and timesheet files, are sent directly by email to our team and are not stored in a database or kept in permanent file storage on our website. Uploaded files exist only as email attachments.</p>

    <h2>Cookies and tracking</h2>
    <p>This website does not use cookies or any analytics or tracking scripts.</p>

    <h2>Your rights</h2>
    <p>Under UK data protection law, you can ask us what personal information we hold about you, ask us to correct it, or ask us to delete it. Contact <a href="mailto:info@plutobv.co.uk">info@plutobv.co.uk</a> to make a request.</p>

    <h2>Changes to this policy</h2>
    <p>If this policy changes, the "last updated" date at the top of this page will change too.</p>
  </div>
</section>
```

- [ ] **Step 2: Write `terms.html`**

Same pattern. `<title>Terms | Plutobv</title>`, meta description `"Terms of use
for the Plutobv website."`. `<main id="main">`:

```html
<section class="section page-hero">
  <div class="container section-head">
    <span class="badge">Legal</span>
    <h1>Terms of Use</h1>
    <p class="text-muted">Last updated: 30 August 2026</p>
  </div>
</section>
<section class="section section--tight">
  <div class="container article legal">
    <h2>Using this site</h2>
    <p>This website is provided for information about Plutobv's care staffing services. You may browse it and use its forms to contact us, apply for a role, or submit a timesheet.</p>

    <h2>Accuracy of information</h2>
    <p>We try to keep the information on this site accurate and up to date, but service details, availability, and coverage may change. Please confirm current details with us directly before relying on them.</p>

    <h2>External links</h2>
    <p>Where this site links to external websites, we aren't responsible for their content or availability.</p>

    <h2>Intellectual property</h2>
    <p>The text, images, and design on this site belong to Plutobv unless otherwise stated, and may not be reused without permission.</p>

    <h2>Governing law</h2>
    <p>These terms are governed by the law of England and Wales.</p>

    <h2>Contact</h2>
    <p>Questions about these terms can be sent to <a href="mailto:info@plutobv.co.uk">info@plutobv.co.uk</a>.</p>
  </div>
</section>
```

- [ ] **Step 3: Write `thank-you.html`**

Same pattern. `<title>Thank You | Plutobv</title>`, meta description `"Your
submission was received."`, add `<meta name="robots" content="noindex">` (this
page shouldn't appear in search results). `<main id="main">`:

```html
<section class="section status-page">
  <div class="container section-head" style="text-align:center; align-items:center; margin-inline:auto;">
    <span class="badge">Thank You</span>
    <h1>We've received your submission</h1>
    <p>Thanks for getting in touch with Plutobv — we'll be in touch soon.</p>
    <a class="btn btn--primary btn--lg" href="/index.html">Back To Home</a>
  </div>
</section>
```

- [ ] **Step 4: Write `404.html`**

Same pattern. `<title>Page Not Found | Plutobv</title>`, meta description `"The
page you're looking for doesn't exist."`, `<meta name="robots" content="noindex">`.
`<main id="main">`:

```html
<section class="section status-page">
  <div class="container section-head" style="text-align:center; align-items:center; margin-inline:auto;">
    <span class="badge">404</span>
    <h1>We couldn't find that page</h1>
    <p>The page you were looking for may have moved or no longer exists.</p>
    <div class="hero__actions" style="justify-content:center;">
      <a class="btn btn--primary btn--lg" href="/index.html">Back To Home</a>
      <a class="btn btn--dark-outline btn--lg" href="/contact.html">Contact Us</a>
    </div>
  </div>
</section>
```

- [ ] **Step 5: Append to `assets/css/pages.css`**

```css
/* ---- Legal & status pages ---- */
.legal h2 { margin-top: var(--space-6); }
.legal h2:first-child { margin-top: 0; }
.status-page { padding-block: var(--space-9); }
```

- [ ] **Step 6: Open all four pages and check they render**

Confirm all four render correctly at 375px, 768px, and 1280px, and that
`thank-you.html` / `404.html` show no nav item as current (expected — they're
outside the primary nav) with no console errors.
Expected: consistent layout, correct `noindex` meta tag present on thank-you and
404 only.

- [ ] **Step 7: Commit**

```bash
git add privacy-policy.html terms.html thank-you.html 404.html assets/css/pages.css
git commit -m "feat: add privacy policy, terms, thank-you, and 404 pages"
```

---

### Task 15: Scroll-reveal animation and print-button wiring

**Files:**
- Modify: `plutobv-website/assets/js/main.js` (append)
- Modify: `plutobv-website/assets/css/pages.css` (append `.reveal`)

**Interfaces:**
- Consumes: `[data-action="print"]` buttons from Task 12; `.section` class present
  on every page.
- Note on scope: the original design mentioned a testimonial slider, but with
  only 3 placeholder testimonials the responsive `.testimonial-track` grid built
  in Task 7 already displays all of them cleanly at every breakpoint — a slider
  would add interaction and accessibility cost for no visible benefit at this
  content volume, so it's dropped in favor of this simpler scroll-reveal touch.

- [ ] **Step 1: Append to `assets/js/main.js`**

```js
/* ---- Print buttons ---- */
document.querySelectorAll('[data-action="print"]').forEach(function (btn) {
  btn.addEventListener('click', function () { window.print(); });
});

/* ---- Scroll reveal ---- */
(function () {
  var targets = document.querySelectorAll('.section');
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!('IntersectionObserver' in window) || reduceMotion) return;

  targets.forEach(function (el) { el.classList.add('reveal'); });

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('reveal--visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  targets.forEach(function (el) { observer.observe(el); });
})();
```

- [ ] **Step 2: Append to `assets/css/pages.css`**

```css
/* ---- Scroll reveal ---- */
.reveal { opacity: 0; transform: translateY(16px); transition: opacity 0.5s ease, transform 0.5s ease; }
.reveal--visible { opacity: 1; transform: none; }
```

- [ ] **Step 3: Verify in the browser**

Run: `node --check plutobv-website/assets/js/main.js` (syntax check), then open
`index.html`, scroll down, and confirm each section fades/slides in once, the
"Print This Form" button on the three `forms/*-print.html` pages opens the
browser's print dialog, and with the OS/browser "reduce motion" setting enabled
sections appear immediately with no animation.
Expected: no syntax errors; sections animate in once each; print buttons work;
reduced-motion respected.

- [ ] **Step 4: Commit**

```bash
git add assets/js/main.js assets/css/pages.css
git commit -m "feat: add scroll-reveal animation and print button wiring"
```

---

### Task 16: Form validation and fetch-based submission

**Files:**
- Modify: `plutobv-website/assets/js/main.js` (append)

**Interfaces:**
- Consumes: the three forms from Task 11 (`#contact-form`, `#apply-form`,
  `#timesheet-form`), their `.form-field[data-field]` wrappers, `form_started`/
  `website` hidden fields, and `.form-status` region.
- Produces: client-side validation (required fields, email format, file
  presence/size) that mirrors the server-side checks Tasks 18–20 implement, so a
  user rarely hits a round trip just to learn a field was missing. Submits via
  `fetch` with `Accept: application/json`, expecting `{ success: boolean,
  message: string }` back — this exact response shape is what Tasks 18–20's PHP
  handlers must return.
- This task only reaches Task 18–20's endpoints once those exist; until then,
  submissions will fail at the network request, which is expected and re-tested
  in Task 23 once the backend exists.

- [ ] **Step 1: Append to `assets/js/main.js`**

```js
/* ---- Form validation + submission ---- */
(function () {
  var forms = document.querySelectorAll('form.form[id]');

  forms.forEach(function (form) {
    var startedInput = form.querySelector('input[name="form_started"]');
    if (startedInput) startedInput.value = String(Date.now());

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      clearErrors(form);
      if (validateForm(form)) submitForm(form);
    });
  });

  function clearErrors(form) {
    form.querySelectorAll('.form-field--error').forEach(function (field) {
      field.classList.remove('form-field--error');
      var msg = field.querySelector('.form-field__error');
      if (msg) msg.remove();
    });
    var status = form.querySelector('.form-status');
    if (status) { status.textContent = ''; status.className = 'form-status'; }
  }

  function showFieldError(field, message) {
    field.classList.add('form-field--error');
    var msg = document.createElement('p');
    msg.className = 'form-field__error';
    msg.textContent = message;
    field.appendChild(msg);
  }

  function validateForm(form) {
    var valid = true;

    form.querySelectorAll('.form-field[data-field]').forEach(function (field) {
      var input = field.querySelector('input, textarea, select');
      if (!input || !input.hasAttribute('required')) return;

      if (input.type === 'file') {
        var file = input.files[0];
        if (!file) {
          showFieldError(field, 'Please choose a file.');
          valid = false;
        } else if (file.size > 5 * 1024 * 1024) {
          showFieldError(field, 'File must be 5MB or smaller.');
          valid = false;
        }
        return;
      }

      if (!input.value.trim()) {
        showFieldError(field, 'This field is required.');
        valid = false;
        return;
      }

      if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
        showFieldError(field, 'Enter a valid email address.');
        valid = false;
      }
    });

    return valid;
  }

  function submitForm(form) {
    var status = form.querySelector('.form-status');
    var submitBtn = form.querySelector('button[type="submit"]');
    var formData = new FormData(form);

    if (submitBtn) submitBtn.disabled = true;

    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: { 'Accept': 'application/json' }
    })
      .then(function (response) {
        return response.json().then(function (data) {
          return { ok: response.ok, data: data };
        });
      })
      .then(function (result) {
        if (result.ok && result.data.success) {
          form.reset();
          if (status) {
            status.textContent = result.data.message || "Thanks — we've received your submission.";
            status.className = 'form-status form-success';
          }
        } else if (status) {
          status.textContent = (result.data && result.data.message) || 'Something went wrong. Please try again.';
          status.className = 'form-status form-error';
        }
      })
      .catch(function () {
        if (status) {
          status.textContent = 'Something went wrong sending this. Please try again, or email info@plutobv.co.uk directly.';
          status.className = 'form-status form-error';
        }
      })
      .finally(function () {
        if (submitBtn) submitBtn.disabled = false;
      });
  }
})();
```

- [ ] **Step 2: Verify syntax and client-side validation**

Run: `node --check plutobv-website/assets/js/main.js`
Expected: no syntax errors.

Then open `contact.html`, `apply.html`, and `timesheet.html` in the browser:
submit each form empty and confirm every required field shows "This field is
required." beneath it and focus/labeling stays intact; enter an invalid email
and confirm the email-format error shows; on `apply.html`/`timesheet.html`,
attach an oversized/wrong-type file and confirm the file error shows. Submitting
a fully valid form will fail at the network request (404/connection error) until
Tasks 18–20 exist — confirm only that the `.form-error` message appears in that
case, not a silent failure.
Expected: all client-side validation messages appear correctly; a valid
submission surfaces a visible (expected, temporary) error rather than doing
nothing.

- [ ] **Step 3: Commit**

```bash
git add assets/js/main.js
git commit -m "feat: add client-side form validation and fetch submission"
```

---

### Task 17: Backend shared helpers (validation, sanitization, anti-abuse, mail)

**Files:**
- Create: `plutobv-website/backend/lib/helpers.php`
- Test: `plutobv-website/backend/lib/helpers.test.php`

**Interfaces:**
- Produces: `sanitize_text()`, `strip_header_injection()`, `is_valid_email()`,
  `is_honeypot_triggered()`, `is_submitted_too_fast()`, `is_referer_allowed()`,
  `wants_json()`, `send_json()`, `send_redirect()`, `respond()`,
  `validate_upload()`, `send_notification_email()` — Tasks 18–20's handlers call
  these exact function names and rely on the exact `[bool, string]` return shape
  of `validate_upload()`.

- [ ] **Step 1: Write the failing test first**

`backend/lib/helpers.test.php`:

```php
<?php
// Run with: php backend/lib/helpers.test.php
require __DIR__ . '/helpers.php';

$failures = 0;

function check(string $label, bool $condition): void
{
    global $failures;
    if ($condition) {
        echo "PASS: {$label}\n";
    } else {
        echo "FAIL: {$label}\n";
        $failures++;
    }
}

check('sanitize_text trims and strips tags', sanitize_text('  <b>hi</b>  ') === 'hi');
check('is_valid_email accepts a valid address', is_valid_email('a@example.com') === true);
check('is_valid_email rejects an invalid address', is_valid_email('not-an-email') === false);
check('is_honeypot_triggered detects a filled honeypot', is_honeypot_triggered(['website' => 'spam']) === true);
check('is_honeypot_triggered passes an empty honeypot', is_honeypot_triggered(['website' => '']) === false);
check('is_submitted_too_fast flags an instant submission', is_submitted_too_fast(['form_started' => (string)(time() * 1000)]) === true);
check('is_submitted_too_fast allows a real-world submission', is_submitted_too_fast(['form_started' => (string)((time() - 10) * 1000)]) === false);
check('is_submitted_too_fast skips the check with no JS', is_submitted_too_fast([]) === false);
check('strip_header_injection removes newlines', strip_header_injection("Subject\r\nBcc: x@example.com") === 'SubjectBcc: x@example.com');

echo "\n" . ($failures === 0 ? "All checks passed." : "{$failures} check(s) failed.") . "\n";
exit($failures === 0 ? 0 : 1);
```

- [ ] **Step 2: Run it to confirm it fails**

Run: `php backend/lib/helpers.test.php`
Expected: a fatal error — `helpers.php` doesn't exist yet.
(PHP CLI isn't installed in this local environment — if `php` isn't found, skip
running this now and rely on careful manual review of Step 3 against Step 1's
assertions; run this for real on first deploy, or once PHP CLI is available
locally.)

- [ ] **Step 3: Write `backend/lib/helpers.php`**

```php
<?php
declare(strict_types=1);

function sanitize_text(string $value, int $maxLength = 2000): string
{
    $value = trim($value);
    $value = strip_tags($value);
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
}

function strip_header_injection(string $value): string
{
    return str_replace(["\r", "\n"], '', $value);
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_honeypot_triggered(array $post): bool
{
    return trim((string)($post['website'] ?? '')) !== '';
}

function is_submitted_too_fast(array $post, int $minSeconds = 3): bool
{
    $startedMs = (int)($post['form_started'] ?? 0);
    if ($startedMs <= 0) {
        return false; // no-JS submission: skip, rely on honeypot + referer instead
    }
    $elapsedSeconds = (time() * 1000 - $startedMs) / 1000;
    return $elapsedSeconds < $minSeconds;
}

function is_referer_allowed(): bool
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer === '') {
        return true; // absent referer is common (privacy settings) — don't block on absence
    }
    $refererHost = parse_url($referer, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    return $refererHost === $currentHost;
}

function wants_json(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return strpos($accept, 'application/json') !== false;
}

function send_json(bool $success, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function send_redirect(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}

function respond(bool $success, string $message, string $redirectUrl, int $httpCode = 200): void
{
    if (wants_json()) {
        send_json($success, $message, $httpCode);
    }
    send_redirect($redirectUrl . ($success ? '' : '?error=1'));
}

/**
 * @param array{name:string,type:string,tmp_name:string,error:int,size:int}|null $file
 * @param string[] $allowedExtensions
 * @param string[] $allowedMimeTypes
 * @return array{0: bool, 1: string}
 */
function validate_upload(?array $file, array $allowedExtensions, array $allowedMimeTypes, int $maxBytes): array
{
    if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [false, 'Please attach a file.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'There was a problem uploading your file.'];
    }
    if ($file['size'] > $maxBytes) {
        return [false, 'File must be ' . (int)($maxBytes / 1024 / 1024) . 'MB or smaller.'];
    }

    $extension = strtolower((string)pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return [false, 'That file type is not accepted.'];
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowedMimeTypes, true)) {
            return [false, 'That file type is not accepted.'];
        }
    }

    return [true, ''];
}

/**
 * @param array{name:string,tmp_name:string}|null $attachment
 * @return array{0: string, 1: string} [contentType, body]
 */
function build_mime_email(string $textBody, ?array $attachment): array
{
    if ($attachment === null) {
        return ['text/plain; charset=UTF-8', $textBody];
    }

    $boundary = 'plutobv-' . bin2hex(random_bytes(12));
    $fileContent = file_get_contents($attachment['tmp_name']);
    $encoded = chunk_split(base64_encode((string)$fileContent));
    $safeName = str_replace(['"', "\r", "\n"], '', $attachment['name']);

    $body = "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $textBody . "\r\n\r\n"
        . "--{$boundary}\r\n"
        . "Content-Type: application/octet-stream; name=\"{$safeName}\"\r\n"
        . "Content-Transfer-Encoding: base64\r\n"
        . "Content-Disposition: attachment; filename=\"{$safeName}\"\r\n\r\n"
        . $encoded . "\r\n"
        . "--{$boundary}--";

    return ["multipart/mixed; boundary=\"{$boundary}\"", $body];
}

/**
 * @param array{name:string,tmp_name:string}|null $attachment
 */
function send_notification_email(string $to, string $subject, string $textBody, string $replyTo, ?array $attachment = null): bool
{
    [$contentType, $body] = build_mime_email($textBody, $attachment);

    $headers = "From: Plutobv Website <info@plutobv.co.uk>\r\n"
        . 'Reply-To: ' . strip_header_injection($replyTo) . "\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: {$contentType}\r\n";

    return mail($to, strip_header_injection($subject), $body, $headers);
}
```

- [ ] **Step 4: Run the test again to confirm it passes**

Run: `php backend/lib/helpers.test.php`
Expected: 9 `PASS:` lines and `All checks passed.`, exit code 0. If PHP CLI isn't
available locally, manually trace each `check(...)` call in Step 1 against the
implementation in Step 3 and confirm each would evaluate true; re-run for real
on first deploy (Task 25's checklist covers this).

- [ ] **Step 5: Commit**

```bash
git add backend/lib/helpers.php backend/lib/helpers.test.php
git commit -m "feat: add backend validation, sanitization, and mail helpers"
```

---

### Task 18: Contact form handler

**Files:**
- Create: `plutobv-website/backend/contact-handler.php`

**Interfaces:**
- Consumes: `sanitize_text()`, `is_valid_email()`, `is_honeypot_triggered()`,
  `is_submitted_too_fast()`, `is_referer_allowed()`, `respond()`,
  `send_notification_email()` from Task 17. Reads POST fields `name`, `email`,
  `subject`, `message`, `website` (honeypot), `form_started` from `contact.html`
  (Task 11).
- Produces: a JSON `{success, message}` response (when `Accept: application/json`
  is sent, as Task 16's `fetch` does) or a 303 redirect to `/thank-you.html`
  otherwise — this exact behavior is the contract Task 16 already codes against.

- [ ] **Step 1: Write `backend/contact-handler.php`**

```php
<?php
declare(strict_types=1);

require __DIR__ . '/lib/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$redirectUrl = '/thank-you.html';

if (is_honeypot_triggered($_POST)) {
    // Pretend success so automated submissions don't learn the honeypot was hit.
    respond(true, "Thanks — we've received your message.", $redirectUrl);
}

if (is_submitted_too_fast($_POST) || !is_referer_allowed()) {
    respond(false, 'Something went wrong. Please try again.', $redirectUrl, 400);
}

$name = sanitize_text((string)($_POST['name'] ?? ''), 200);
$email = trim((string)($_POST['email'] ?? ''));
$subject = sanitize_text((string)($_POST['subject'] ?? ''), 200);
$message = sanitize_text((string)($_POST['message'] ?? ''), 5000);

if ($name === '' || $subject === '' || $message === '' || !is_valid_email($email)) {
    respond(false, 'Please fill in every field with a valid email address.', $redirectUrl, 422);
}

$body = "New contact form submission from plutobv.co.uk\n\n"
    . "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Subject: {$subject}\n\n"
    . "Message:\n{$message}\n";

$sent = send_notification_email('info@plutobv.co.uk', "Contact form: {$subject}", $body, $email);

if (!$sent) {
    respond(false, 'Something went wrong sending your message. Please email info@plutobv.co.uk directly.', $redirectUrl, 502);
}

respond(true, "Thanks — we've received your message. We'll be in touch soon.", $redirectUrl);
```

- [ ] **Step 2: Lint it**

Run: `php -l backend/contact-handler.php`
Expected: `No syntax errors detected`. If PHP CLI isn't available locally, review
the file manually against Step 1 and defer this exact command to first deploy.

- [ ] **Step 3: Test the request/response contract**

If a local PHP server is available (`php -S localhost:8000 -t plutobv-website`),
run these from another terminal; otherwise run them against the Hostinger URL
once deployed (Task 25):

```bash
curl -s -X POST http://localhost:8000/backend/contact-handler.php \
  -H "Accept: application/json" \
  -F "name=" -F "email=bad" -F "subject=" -F "message=" \
  -F "website=" -F "form_started=0"
```
Expected: HTTP 422, `{"success":false,"message":"Please fill in every field with a valid email address."}`.

```bash
curl -s -X POST http://localhost:8000/backend/contact-handler.php \
  -H "Accept: application/json" \
  -F "name=Jane Smith" -F "email=jane@example.com" -F "subject=Enquiry" \
  -F "message=Hello there" -F "website=i-am-a-bot" -F "form_started=0"
```
Expected: HTTP 200, `{"success":true,...}` — the honeypot silently discards
without an error, exactly like a normal success.

```bash
curl -s -X POST http://localhost:8000/backend/contact-handler.php \
  -H "Accept: application/json" \
  -F "name=Jane Smith" -F "email=jane@example.com" -F "subject=Enquiry" \
  -F "message=Hello there" -F "website=" -F "form_started=$(( $(date +%s%3N) - 5000 ))"
```
Expected: HTTP 200, `{"success":true,"message":"Thanks — we've received your message. We'll be in touch soon."}`.
Actual email delivery depends on the server's `mail()` configuration — on a local
PHP built-in server this will likely log a warning rather than deliver; real
delivery is confirmed post-deploy in Task 25's checklist.

- [ ] **Step 4: Commit**

```bash
git add backend/contact-handler.php
git commit -m "feat: add contact form backend handler"
```

---

### Task 19: Job application handler (with CV upload)

**Files:**
- Create: `plutobv-website/backend/apply-handler.php`

**Interfaces:**
- Consumes: helpers from Task 17, including `validate_upload()`. Reads POST
  fields `name`, `email`, `phone`, `position`, `message`, `website`,
  `form_started`, and file field `cv` from `apply.html` (Task 11).
- Produces: the same `{success, message}` / redirect contract as Task 18.

- [ ] **Step 1: Write `backend/apply-handler.php`**

```php
<?php
declare(strict_types=1);

require __DIR__ . '/lib/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$redirectUrl = '/thank-you.html';

if (is_honeypot_triggered($_POST)) {
    respond(true, "Thanks — we've received your application.", $redirectUrl);
}

if (is_submitted_too_fast($_POST) || !is_referer_allowed()) {
    respond(false, 'Something went wrong. Please try again.', $redirectUrl, 400);
}

$name = sanitize_text((string)($_POST['name'] ?? ''), 200);
$email = trim((string)($_POST['email'] ?? ''));
$phone = sanitize_text((string)($_POST['phone'] ?? ''), 40);
$position = sanitize_text((string)($_POST['position'] ?? ''), 100);
$message = sanitize_text((string)($_POST['message'] ?? ''), 5000);

if ($name === '' || $phone === '' || $position === '' || $message === '' || !is_valid_email($email)) {
    respond(false, 'Please fill in every field with a valid email address.', $redirectUrl, 422);
}

[$fileValid, $fileError] = validate_upload(
    $_FILES['cv'] ?? null,
    ['pdf', 'doc', 'docx'],
    ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    5 * 1024 * 1024
);

if (!$fileValid) {
    respond(false, $fileError, $redirectUrl, 422);
}

$body = "New job application from plutobv.co.uk\n\n"
    . "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Phone: {$phone}\n"
    . "Role: {$position}\n\n"
    . "Message:\n{$message}\n";

$sent = send_notification_email(
    'info@plutobv.co.uk',
    "Job application: {$position} — {$name}",
    $body,
    $email,
    $_FILES['cv']
);

if (!$sent) {
    respond(false, 'Something went wrong sending your application. Please email info@plutobv.co.uk directly.', $redirectUrl, 502);
}

respond(true, "Thanks — we've received your application. We'll be in touch soon.", $redirectUrl);
```

- [ ] **Step 2: Lint it**

Run: `php -l backend/apply-handler.php`
Expected: `No syntax errors detected` (or manual review if PHP CLI is unavailable
locally, as in Task 18).

- [ ] **Step 3: Test the request/response contract**

```bash
printf '%%PDF-1.4 test' > /tmp/test-cv.pdf
curl -s -X POST http://localhost:8000/backend/apply-handler.php \
  -H "Accept: application/json" \
  -F "name=Jane Smith" -F "email=jane@example.com" -F "phone=07000000000" \
  -F "position=Support Worker" -F "message=I have 3 years experience." \
  -F "website=" -F "form_started=$(( $(date +%s%3N) - 5000 ))" \
  -F "cv=@/tmp/test-cv.pdf;type=application/pdf"
```
Expected: HTTP 200, `{"success":true,...}`.

```bash
printf 'not a real pdf' > /tmp/test-cv.exe
curl -s -X POST http://localhost:8000/backend/apply-handler.php \
  -H "Accept: application/json" \
  -F "name=Jane Smith" -F "email=jane@example.com" -F "phone=07000000000" \
  -F "position=Support Worker" -F "message=Test" \
  -F "website=" -F "form_started=$(( $(date +%s%3N) - 5000 ))" \
  -F "cv=@/tmp/test-cv.exe;type=application/x-msdownload"
```
Expected: HTTP 422, `{"success":false,"message":"That file type is not accepted."}`.

- [ ] **Step 4: Commit**

```bash
git add backend/apply-handler.php
git commit -m "feat: add job application backend handler"
```

---

### Task 20: Timesheet handler (with file upload)

**Files:**
- Create: `plutobv-website/backend/timesheet-handler.php`

**Interfaces:**
- Consumes: helpers from Task 17. Reads POST fields `name`, `reference`,
  `week_ending`, `notes`, `website`, `form_started`, and file field `timesheet`
  from `timesheet.html` (Task 11).
- Produces: the same `{success, message}` / redirect contract as Task 18.

- [ ] **Step 1: Write `backend/timesheet-handler.php`**

```php
<?php
declare(strict_types=1);

require __DIR__ . '/lib/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$redirectUrl = '/thank-you.html';

if (is_honeypot_triggered($_POST)) {
    respond(true, "Thanks — we've received your timesheet.", $redirectUrl);
}

if (is_submitted_too_fast($_POST) || !is_referer_allowed()) {
    respond(false, 'Something went wrong. Please try again.', $redirectUrl, 400);
}

$name = sanitize_text((string)($_POST['name'] ?? ''), 200);
$reference = sanitize_text((string)($_POST['reference'] ?? ''), 100);
$weekEnding = sanitize_text((string)($_POST['week_ending'] ?? ''), 20);
$notes = sanitize_text((string)($_POST['notes'] ?? ''), 2000);

if ($name === '' || $reference === '' || $weekEnding === '') {
    respond(false, 'Please fill in your name, reference, and week ending date.', $redirectUrl, 422);
}

[$fileValid, $fileError] = validate_upload(
    $_FILES['timesheet'] ?? null,
    ['pdf', 'jpg', 'jpeg', 'png'],
    ['application/pdf', 'image/jpeg', 'image/png'],
    5 * 1024 * 1024
);

if (!$fileValid) {
    respond(false, $fileError, $redirectUrl, 422);
}

$body = "New timesheet submission from plutobv.co.uk\n\n"
    . "Name: {$name}\n"
    . "Staff reference: {$reference}\n"
    . "Week ending: {$weekEnding}\n\n"
    . "Notes:\n" . ($notes !== '' ? $notes : '(none)') . "\n";

$sent = send_notification_email(
    'info@plutobv.co.uk',
    "Timesheet: {$name} — week ending {$weekEnding}",
    $body,
    'info@plutobv.co.uk',
    $_FILES['timesheet']
);

if (!$sent) {
    respond(false, 'Something went wrong sending your timesheet. Please email info@plutobv.co.uk directly.', $redirectUrl, 502);
}

respond(true, "Thanks — we've received your timesheet.", $redirectUrl);
```

- [ ] **Step 2: Lint it**

Run: `php -l backend/timesheet-handler.php`
Expected: `No syntax errors detected` (or manual review if unavailable locally).

- [ ] **Step 3: Test the request/response contract**

```bash
printf 'test image bytes' > /tmp/test-ts.png
curl -s -X POST http://localhost:8000/backend/timesheet-handler.php \
  -H "Accept: application/json" \
  -F "name=Jane Smith" -F "reference=EMP-001" -F "week_ending=2026-08-24" \
  -F "notes=" -F "website=" -F "form_started=$(( $(date +%s%3N) - 5000 ))" \
  -F "timesheet=@/tmp/test-ts.png;type=image/png"
```
Expected: HTTP 200, `{"success":true,...}`.

```bash
curl -s -X POST http://localhost:8000/backend/timesheet-handler.php \
  -H "Accept: application/json" \
  -F "name=" -F "reference=" -F "week_ending=" \
  -F "website=" -F "form_started=$(( $(date +%s%3N) - 5000 ))"
```
Expected: HTTP 422, `{"success":false,"message":"Please fill in your name, reference, and week ending date."}`.

- [ ] **Step 4: Commit**

```bash
git add backend/timesheet-handler.php
git commit -m "feat: add timesheet backend handler"
```

---

### Task 21: `.htaccess` hardening

**Files:**
- Create: `plutobv-website/.htaccess`
- Create: `plutobv-website/backend/lib/.htaccess`

**Interfaces:**
- Produces: HTTPS/canonical-host redirects, security headers, directory-listing
  lockdown, and a full deny on `backend/lib/` (which is only ever `require`d by
  PHP, never requested directly).

- [ ] **Step 1: Write `plutobv-website/.htaccess`**

```apache
ErrorDocument 404 /404.html

<IfModule mod_rewrite.c>
  RewriteEngine On

  RewriteCond %{HTTPS} off
  RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

  RewriteCond %{HTTP_HOST} ^www\.plutobv\.co\.uk$ [NC]
  RewriteRule ^ https://plutobv.co.uk%{REQUEST_URI} [L,R=301]
</IfModule>

<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
  Header always set Content-Security-Policy "default-src 'self'; img-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; form-action 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'"
  Header always set Strict-Transport-Security "max-age=15552000; includeSubDomains"
</IfModule>

Options -Indexes
```

- [ ] **Step 2: Write `plutobv-website/backend/lib/.htaccess`**

```apache
Require all denied
```

- [ ] **Step 3: Verify locally where possible**

If a local Apache-compatible server isn't available, verify by careful reading:
confirm the `RewriteRule` lines match the exact domain `plutobv.co.uk` from the
spec, and that `backend/lib/.htaccess` sits inside the same folder as
`helpers.php` and `helpers.test.php` from Task 17. Full verification (headers
present, `backend/lib/helpers.php` returns 403, HTTP redirects to HTTPS) happens
in Task 25's post-deploy checklist once this is live on Hostinger, which runs
Apache and honors `.htaccess` by default.

- [ ] **Step 4: Commit**

```bash
git add .htaccess backend/lib/.htaccess
git commit -m "feat: add .htaccess security headers and access rules"
```

---

### Task 22: Security review pass

**Files:**
- Modify: any file under `plutobv-website/backend/`, `plutobv-website/assets/js/main.js`, or the three form pages, as findings require.

**Interfaces:**
- Consumes: the full backend and form-handling code produced by Tasks 11, 16–21.

- [ ] **Step 1: Run a security review against the backend and form-handling code**

Invoke the `agent-skills:security-and-hardening` skill (or dispatch a
`security-auditor` agent) scoped to: `plutobv-website/backend/**`,
`plutobv-website/assets/js/main.js`, `plutobv-website/contact.html`,
`plutobv-website/apply.html`, `plutobv-website/timesheet.html`, and
`plutobv-website/.htaccess`. Ask it to specifically check: PHP mail-header
injection, file-upload handling (extension/MIME/size checks, no persistence to
disk), input sanitization/output encoding, the honeypot/timing/referer anti-abuse
logic, the CSP/security headers in `.htaccess`, and any leftover debug output,
stray comments, or test/scaffold code that shouldn't ship (e.g. anything left
over from Task 18–20's curl-testing process).

- [ ] **Step 2: Fix every finding it raises**

Apply fixes directly in the affected files. If a finding is a false positive or
doesn't apply to this codebase (for example, a generic "add CSRF tokens"
suggestion that ignores that these forms are anonymous and session-less), note
why in the commit message rather than silently ignoring it.

- [ ] **Step 3: Re-run Tasks 18–20's curl tests**

Re-run every `curl` command from Task 18 Step 3, Task 19 Step 3, and Task 20
Step 3.
Expected: identical results to when those tasks were first completed — no
regression from the fixes applied in Step 2.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "fix: apply security review findings to backend and forms"
```

(If Step 2 found nothing to fix, skip this commit — don't create an empty one.)

---

### Task 23: Design polish pass

**Files:**
- Modify: any `.html` file or file under `plutobv-website/assets/css/`, as findings require.

**Interfaces:**
- Consumes: the fully assembled site from Tasks 6–15 (every page and the shared
  design system exist by this point, which is why this pass sits here rather
  than earlier).

- [ ] **Step 1: Review the assembled site against anti-templated-design and UI/UX standards**

Invoke the `design-taste-frontend` skill (this was explicitly requested for this
project) against the built site — `index.html`, `about.html`, one service detail
page, `contact.html`, and `news.html` are enough to represent every page pattern
in use. Ask it specifically whether the site reads as templated/generic rather
than deliberate, and to check spacing rhythm, typographic hierarchy, and
whether the accent color and imagery feel cohesive across pages. Follow up with
the `ui-ux-pro-max` and `21st-ui-build` skills for any further concrete UI
polish or componentization suggestions.

- [ ] **Step 2: Apply the fixes that improve the site**

Apply fixes directly to the CSS/HTML. Skip suggestions that would reintroduce
scope already deliberately cut (e.g. don't re-add a testimonial slider — see
Task 15's note) — note in the commit message why anything was skipped.

- [ ] **Step 3: Re-check the pages touched**

Re-open every page modified in Step 2 in the browser at 375px, 768px, and
1280px and confirm no layout regression, and re-run the contrast check for any
color token that changed:

```bash
node -e '
function luminance(hex) {
  const [r,g,b] = [0,2,4].map(i => parseInt(hex.slice(i,i+2),16)/255);
  const lin = c => c <= 0.03928 ? c/12.92 : Math.pow((c+0.055)/1.055, 2.4);
  const [R,G,B] = [r,g,b].map(lin);
  return 0.2126*R + 0.7152*G + 0.0722*B;
}
function contrast(a,b) {
  const [L1,L2] = [luminance(a), luminance(b)];
  const [hi,lo] = L1>L2 ? [L1,L2] : [L2,L1];
  return (hi+0.05)/(lo+0.05);
}
console.log(contrast(process.argv[1], process.argv[2]).toFixed(2));
' "<new-hex-if-changed>" ffffff
```
Expected: no visual regressions; any changed color-on-color pairing still ≥ 4.5
for normal text (or ≥ 3.0 if it's only ever used at large-text sizes).

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "polish: apply design review fixes"
```

(If Step 2 found nothing to change, skip this commit.)

---

### Task 24: Site-wide QA — links, alt text, responsive, and accessibility

**Files:**
- Create: `plutobv-website/scripts/check-links.js`
- Create: `plutobv-website/scripts/check-alt-text.js`

**Interfaces:**
- Consumes: every `.html` file produced by Tasks 6–14 and 23.
- These two scripts are dev tooling, not part of the deployed site — Task 25's
  README explicitly excludes `scripts/` (and `docs/`, `.git`, `.gitignore`) from
  what gets uploaded to Hostinger.

- [ ] **Step 1: Write and run the internal-link checker**

`scripts/check-links.js`:

```js
// Run with: node scripts/check-links.js
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const htmlFiles = [];

function walk(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name === '.git' || entry.name === 'scripts') continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full);
    else if (entry.name.endsWith('.html')) htmlFiles.push(full);
  }
}
walk(root);

const linkPattern = /(?:href|src|action)="(\/[^"]*)"/g;
let brokenCount = 0;

for (const file of htmlFiles) {
  const content = fs.readFileSync(file, 'utf8');
  let match;
  while ((match = linkPattern.exec(content))) {
    const link = match[1].split('#')[0].split('?')[0];
    if (link === '' || link.startsWith('/backend/')) continue; // PHP endpoints, tested separately
    const targetPath = path.join(root, link);
    if (!fs.existsSync(targetPath)) {
      console.log(`BROKEN: ${path.relative(root, file)} -> ${link}`);
      brokenCount++;
    }
  }
}

console.log(brokenCount === 0 ? 'All internal links resolve.' : `${brokenCount} broken link(s) found.`);
process.exit(brokenCount === 0 ? 0 : 1);
```

Run: `node scripts/check-links.js`
Expected: `All internal links resolve.`, exit code 0. If it reports any `BROKEN:`
line, fix that file's link/asset path and re-run before continuing.

- [ ] **Step 2: Write and run the alt-text checker**

`scripts/check-alt-text.js`:

```js
// Run with: node scripts/check-alt-text.js
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const htmlFiles = [];

function walk(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name === '.git' || entry.name === 'scripts') continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full);
    else if (entry.name.endsWith('.html')) htmlFiles.push(full);
  }
}
walk(root);

const imgPattern = /<img\b[^>]*>/g;
let missing = 0;

for (const file of htmlFiles) {
  const content = fs.readFileSync(file, 'utf8');
  let match;
  while ((match = imgPattern.exec(content))) {
    if (!/\balt\s*=/.test(match[0])) {
      console.log(`MISSING ALT: ${path.relative(root, file)} -> ${match[0].slice(0, 80)}`);
      missing++;
    }
  }
}

console.log(missing === 0 ? 'Every <img> has an alt attribute.' : `${missing} <img> tag(s) missing alt.`);
process.exit(missing === 0 ? 0 : 1);
```

Run: `node scripts/check-alt-text.js`
Expected: `Every <img> has an alt attribute.`, exit code 0. Fix any reported tag
(decorative images should get `alt=""`, content images a real description) and
re-run before continuing.

- [ ] **Step 3: Responsive spot-check**

In the browser, at 375px, 768px, and 1280px widths, open one page of each
template type and confirm no horizontal scroll, no overlapping text, and the
mobile nav toggle works below 900px: `index.html`, `about.html`,
`services/live-in-care.html`, `areas-we-support.html`, `contact.html`,
`apply.html`, `forms/application-form-print.html`, `news.html`,
`news/live-in-care-day-to-day.html`, `privacy-policy.html`, `thank-you.html`,
`404.html`.
Expected: all 12 render cleanly at all 3 widths.

- [ ] **Step 4: Accessibility spot-check**

On the same 12 pages, confirm: one `<h1>` per page and no skipped heading
levels, the skip-link is the first focusable element and jumps focus to
`<main id="main">`, every form field's `<label for>` matches its input `id`,
and tabbing through `contact.html` never lands on the honeypot field.
Expected: no violations found; fix and re-check any that are.

- [ ] **Step 5: Commit**

```bash
git add scripts/
git commit -m "test: add link and alt-text checker scripts"
```

---

### Task 25: Finalize README and deploy

**Files:**
- Modify: `plutobv-website/README.md`

**Interfaces:**
- Consumes: the finished site from every prior task — this is the last task in
  the plan.

- [ ] **Step 1: Replace the README's Deployment and Security notes sections**

Replace the `## Deployment (Hostinger shared hosting)` and `## Security notes`
sections (currently `(Filled in by Task 25.)` placeholders from Task 1) with:

```markdown
## Deployment (Hostinger shared hosting)

1. In hPanel, open **File Manager** (or connect via FTP/SFTP with the
   credentials from hPanel → Files → FTP Accounts).
2. Upload everything in this folder **except** `docs/`, `scripts/`, `.git/`,
   and `.gitignore` into `public_html/` (or your domain's document root if
   using an add-on domain). No build step — upload the files as they are.
3. Confirm `plutobv.co.uk` (and `www.plutobv.co.uk`) point at this hosting
   account in hPanel → Domains, and that a free SSL certificate is issued and
   active under hPanel → SSL (Hostinger issues these automatically for
   domains pointed at it, usually within a few minutes to hours).
4. Replace every placeholder before telling anyone the site is live:
   - `[Your Street Address]` — search across all files
   - `[Your Phone Number]` — search across all files
   - `"[Client testimonial goes here]"` — replace with real client quotes, or
     remove the testimonial section from `index.html` if you don't have any
     yet
5. Post-deploy checklist:
   - Visit the live domain over `https://` and confirm it loads with no
     browser mixed-content warnings.
   - Visit `http://plutobv.co.uk` (no `s`) and confirm it redirects to
     `https://plutobv.co.uk`.
   - Visit `https://plutobv.co.uk/backend/lib/helpers.php` directly and
     confirm it returns a 403, not the file's contents.
   - Submit each of the three forms (contact, apply with a small test PDF,
     timesheet with a small test image) for real, and confirm the email
     arrives at `info@plutobv.co.uk`. If it doesn't arrive within a few
     minutes, check hPanel's mail logs — Hostinger shared hosting supports
     PHP's `mail()` out of the box, but some setups deliver more reliably
     through SMTP.
   - If `mail()` delivery is unreliable, switch `send_notification_email()`
     in `backend/lib/helpers.php` to send via SMTP instead (Hostinger
     provides SMTP credentials in hPanel → Emails), for example with
     PHPMailer — this is the one place a small library is worth adding later.

## Security notes

- Uploaded files (CVs, timesheets) are emailed as attachments and never
  written to disk on the server — there is no uploads folder to secure.
- `backend/lib/` is fully denied via its own `.htaccess` — it's only ever
  loaded by PHP `require`, never requested directly.
- All three form handlers validate required fields, verify uploaded file
  extensions and real MIME types (not just the filename), cap file size at
  5MB, and strip characters that could enable email header injection.
- Basic spam/abuse mitigation: a honeypot field, a minimum-fill-time check,
  and a same-origin Referer check — deliberately lightweight, since these are
  anonymous, session-less forms with nothing for real CSRF protection to
  protect.
- Security headers and a Content-Security-Policy are set in `.htaccess`.
  If you add any third-party script or embed (analytics, a booking widget,
  etc.) later, you'll need to widen the CSP to allow it.
```

- [ ] **Step 2: Read through the whole README once**

Confirm every placeholder mentioned in it (`[Your Street Address]`,
`[Your Phone Number]`, testimonial text) still matches what's actually in the
HTML files, and that the file-exclusion list in Step 3 of the Deployment
section matches the real top-level folders (`docs/`, `scripts/`, `.git`,
`.gitignore`).
Expected: no drift between the README and the actual project structure.

- [ ] **Step 3: Commit**

```bash
git add README.md
git commit -m "docs: finalize deployment and security notes"
```

- [ ] **Step 4: Final check**

Run: `git log --oneline` and `git status`
Expected: a clean working tree, and a commit history that reads as one task per
commit from Task 1 through this one.

