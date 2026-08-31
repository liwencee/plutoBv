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

1. In hPanel, open **File Manager** (or connect via FTP/SFTP with the
   credentials from hPanel → Files → FTP Accounts).
2. Upload everything in this folder **except** `docs/`, `scripts/`, `.git/`,
   and `.gitignore` into `public_html/` (or your domain's document root if
   using an add-on domain). No build step — upload the files as they are.
3. Confirm `plutobv.co.uk` (and `www.plutobv.co.uk`) point at this hosting
   account in hPanel → Domains, and that a free SSL certificate is issued and
   active under hPanel → SSL (Hostinger issues these automatically for
   domains pointed at it, usually within a few minutes to hours).
4. Photography is still pending. 12 image files are referenced across the
   site but don't exist yet under `assets/images/` — generation was blocked
   on an external credits issue during the build:
   - `hero-team.jpg`, `hero-slide-2.jpg`, `hero-slide-3.jpg`
   - `about-team.jpg`
   - `staffing-solutions.jpg`
   - `service-live-in-care.jpg`, `service-domiciliary-care.jpg`,
     `service-companionship-care.jpg`, `service-autism-support.jpg`
   - `news-01.jpg`, `news-02.jpg`, `news-03.jpg`

   The site is otherwise complete and functional, but these will show as
   broken images until generated and added to `assets/images/`.
5. Replace every placeholder before telling anyone the site is live:
   - `[Your Street Address]` — search across all files
   - `[Your Phone Number]` — search across all files
   - `"[Client testimonial goes here]"` — replace with real client quotes, or
     remove the testimonial section from `index.html` if you don't have any
     yet
6. Post-deploy checklist:
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
- No rate-limiting or throttling on form submissions beyond the
  honeypot/timing/referer checks above — there's no database or session
  layer on this site, so there's no cheap place to add real throttling. This
  is a known limitation for a simple brochure site. If spam/abuse becomes a
  real problem after launch, consider Hostinger-level mail protections or
  adding a lightweight file/IP-based throttle to `backend/lib/helpers.php`.
- Security headers and a Content-Security-Policy are set in `.htaccess`.
  If you add any third-party script or embed (analytics, a booking widget,
  etc.) later, you'll need to widen the CSP to allow it.
