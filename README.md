# Plutobv Website

Static HTML/CSS/JS site for Plutobv, a UK healthcare-staffing agency, with a small
PHP form backend. No build step — deploy by uploading this folder's contents.

## Local development

**Don't open the `.html` files directly by double-clicking them or pasting a
`file://` path into the address bar.** Every page loads its CSS/JS/images via
root-relative paths (`/assets/css/base.css`, not `assets/css/base.css`) so the
exact same header/footer markup works at any folder depth and matches how the
site is served once deployed. A `file://` URL has no concept of a site root,
so a root-relative path resolves to the root of your entire hard drive —
nothing loads, and the page renders as unstyled text. This is expected
`file://` behavior, not a bug in the site.

Use the included preview server:

```bash
python scripts/serve.py
```

then open `http://localhost:8421/`.

Use this rather than a plain `python -m http.server`. The site uses
extensionless URLs (`/about`, not `/about.html`), which the live server
handles through rewrite rules in `.htaccess`. A plain static server knows
nothing about those rules, so every internal link would 404. `serve.py`
applies the same resolution order, so local matches live.

The PHP backend under `backend/` still needs a PHP-capable server (see
Deployment below), so submitting a form locally will fail at the network
request. That is expected; every page's HTML, CSS and JS renders correctly.

## URLs

Pages are served without the `.html` extension:

| Address | File on disk |
|---|---|
| `/` | `index.html` |
| `/about` | `about.html` |
| `/services` | `services/index.html` |
| `/services/companionship-care` | `services/companionship-care.html` |
| `/news` | `news/index.html` |
| `/news/wellbeing-in-home-care` | `news/wellbeing-in-home-care.html` |

Requests for the old `.html` address are 301-redirected to the clean one, so
any link already shared keeps working and search engines see a single
canonical URL per page.

Three details worth knowing if you edit the rewrite rules:

- **A section landing page must live at `section/index.html`, never at
  `section.html` beside a `section/` directory.** When both existed,
  Apache's `mod_dir` appended a trailing slash to `/services` before the
  rewrite could serve `services.html`, and the page 404'd on the live site.
  Both the services and news overviews were moved into their directories
  for exactly this reason.
- The `.html` to clean-URL redirect matches on `THE_REQUEST` (the original
  request line) rather than the current URI. That is what stops it looping,
  since the internal rewrite never alters `THE_REQUEST`.
- The rewrite tests `%{REQUEST_FILENAME}.html`, not
  `%{DOCUMENT_ROOT}/$1.html`. The `DOCUMENT_ROOT` form does not resolve
  under LiteSpeed, which is what Hostinger runs, and fails silently.

When adding a page, link to it without the extension (`href="/new-page"`).
`node scripts/check-links.js` understands this and will still catch typos.

## Placeholders to replace before going live

- Coverage area: search for `[Your Coverage Area]` (on `areas-we-support.html`)
- Testimonial quotes: search for `[Client testimonial goes here]`

Address (Flat 2, 4 West Street Walk, Northampton NN1 5BS, United Kingdom) and
phone (07932 790842) are now live across the site. The phone is wired as a
`tel:` link in the topbar and footer of every page.

## Prerequisites

- PHP 7.4 or later, with the `fileinfo` extension enabled (Hostinger enables
  it by default). If `apply.html` or `timesheet.html` uploads always fail
  with "Unable to verify file type. Please try again later.", check this
  first — `validate_upload()` in `backend/lib/helpers.php` refuses to accept
  a file if `finfo_open` isn't available.

## Deployment option A - Git (recommended)

hPanel can pull straight from GitHub, which means deploying is `git push`
and nothing else.

1. In hPanel, open your website and go to **Advanced → Git**.
2. Click **Connect with GitHub** and authorise the Hostinger GitHub app for
   the `liwencee/plutoBv` repository.
3. Set the branch to `main` and leave the root directory as `public_html`.
4. Deploy. After that, every push to `main` triggers an automatic
   deployment via webhook; the **Redeploy** button on the Git page forces
   one by hand.

There is no build step, so what is committed is what gets served.

**The catch, and why the `.htaccess` matters.** Unlike an FTP upload, where
you choose which folders to send, a Git deploy puts the *entire repository*
into `public_html` - including `.git`, `docs/`, `scripts/`, and `README.md`.
An exposed `.git` directory is genuinely dangerous: anyone who finds it can
reconstruct the full source history. The root `.htaccess` therefore denies
HTTP access to all of it (dotfiles and dot-directories, `docs/`, `scripts/`,
`images/`, `.superpowers/`, `*.md`, `LICENSE`, and `*.test.php`).

So: `.htaccess` is not optional with this deployment method. After the first
deploy, check these all return 403 or 404 rather than content:

- `https://plutobv.co.uk/.git/config`
- `https://plutobv.co.uk/README.md`
- `https://plutobv.co.uk/docs/`
- `https://plutobv.co.uk/backend/lib/helpers.php`

If any of them returns real content, the `.htaccess` is not being applied -
stop and fix that before sharing the site.

## Deployment option B - File Manager or FTP

1. In hPanel, open **File Manager** (or connect via FTP/SFTP with the
   credentials from hPanel → Files → FTP Accounts).
2. Upload everything in this folder **except** `docs/`, `scripts/`, `.git/`,
   `.gitignore`, `.superpowers/`, `README.md`, and `LICENSE` into
   `public_html/` (or your domain's document root if using an add-on
   domain). No build step — upload the files as they are. `.superpowers/`
   holds internal build artifacts (task briefs, implementer/reviewer
   reports, the SDD ledger) and must not end up on the live site.
   `README.md` and `LICENSE` are project documentation, not site content —
   `README.md` in particular must never be publicly reachable, since its
   Security notes section documents internal details (the honeypot
   mechanism, the lack of rate-limiting, backend file paths) that shouldn't
   be handed to anyone probing the live site.
3. Hostinger's File Manager (and many FTP clients) hide dotfiles by
   default. Before or while uploading, enable **Show Hidden Files** in File
   Manager (or the equivalent setting in your FTP client), and specifically
   confirm that both `.htaccess` (project root) and `backend/lib/.htaccess`
   were uploaded. Missing the root `.htaccess` means no HTTPS redirect, no
   security headers, and no custom 404 page; missing `backend/lib/.htaccess`
   means `backend/lib/helpers.php` becomes publicly readable, since that's
   the file that denies direct access to the `backend/lib/` folder.
4. Confirm `plutobv.co.uk` (and `www.plutobv.co.uk`) point at this hosting
   account in hPanel → Domains, and that a free SSL certificate is issued and
   active under hPanel → SSL (Hostinger issues these automatically for
   domains pointed at it, usually within a few minutes to hours).
5. Photography is in place. All 15 images referenced by the site exist under
   `assets/images/`, resized and compressed for the web (1600px wide for
   heroes, 1200px for cards; roughly 1.4MB for the whole set). The
   full-resolution originals live in `images/` at the project root, which is
   git-ignored and must NOT be uploaded - it is around 85MB of source files
   that the site never serves.

   To swap a photo later: drop the new original into `images/`, then re-run
   the resize step (Pillow is the only dependency) and overwrite the file of
   the same name in `assets/images/`. Keep the filenames identical so no HTML
   needs editing. If the new photo shows something different from the old
   one, update that image's `alt` text too - the alt text on each page
   describes the specific photo currently in that slot.
6. Replace every placeholder before telling anyone the site is live:
   - `[Your Coverage Area]` on `areas-we-support.html` - replace with the
     towns or counties you actually place staff in
   - `"[Client testimonial goes here]"` — replace with real client quotes, or
     remove the testimonial section from `index.html` if you don't have any
     yet
7. Post-deploy checklist:
   - Visit the live domain over `https://` and confirm it loads with no
     browser mixed-content warnings.
   - Visit `http://plutobv.co.uk` (no `s`) and confirm it redirects to
     `https://plutobv.co.uk`.
   - Visit `https://plutobv.co.uk/backend/lib/helpers.php` directly and
     confirm it returns a 403, not the file's contents.
   - Submit each of the three forms (contact, apply with a small test PDF,
     timesheet with a small test image) for real, and confirm the email
     arrives at `admin@plutobv.co.uk`. If it doesn't arrive within a few
     minutes, check hPanel's mail logs — Hostinger shared hosting supports
     PHP's `mail()` out of the box, but some setups deliver more reliably
     through SMTP.
   - If `mail()` delivery is unreliable, switch `send_notification_email()`
     in `backend/lib/helpers.php` to send via SMTP instead (Hostinger
     provides SMTP credentials in hPanel → Emails), for example with
     PHPMailer — this is the one place a small library is worth adding later.
   - As an extra sanity check, if SSH access is available on your hosting
     plan, run `php backend/lib/helpers.test.php` over SSH to verify the
     backend helper functions behave as expected on the live server.

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
- No rate-limiting or throttling on form submissions beyond the
  honeypot/timing/referer checks above — there's no database or session
  layer on this site, so there's no cheap place to add real throttling. This
  is a known limitation for a simple brochure site. If spam/abuse becomes a
  real problem after launch, consider Hostinger-level mail protections or
  adding a lightweight file/IP-based throttle to `backend/lib/helpers.php`.
- Security headers and a Content-Security-Policy are set in `.htaccess`.
  If you add any third-party script or embed (analytics, a booking widget,
  etc.) later, you'll need to widen the CSP to allow it.
