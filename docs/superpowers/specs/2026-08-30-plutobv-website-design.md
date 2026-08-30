# Plutobv Website — Design Spec

Date: 2026-08-30
Status: Approved by user in chat, pending written-spec review

## 1. Requirements (SDLC: Requirements)

Build a marketing / lead-generation website for **Plutobv**, a new UK healthcare-staffing
agency (same industry as the reference site, a different business). The reference site,
`https://plutobvservices.co.uk/` (a WordPress/Elementor "modins" theme site for "Plutobv
Services"), is used **only as a structural and stylistic reference** — page layout, section
types, and site map. All written content, photography, logo, testimonials, and specific
contact details are original to Plutobv, not copied from the reference site.

Hard constraints from the user:
- Static HTML/CSS/JS frontend — no framework, no build step.
- A backend that works with a static frontend, for form handling only.
- Target host: Hostinger shared hosting (Apache + PHP, no Node/DB assumed available).
- Domain: `plutobv.co.uk`. Working contact email: `info@plutobv.co.uk`.

### Decision log (from brainstorming Q&A)

| Question | Decision |
|---|---|
| Is Plutobv the same business as the reference site? | No — separate business, same industry. Do not reuse the reference site's real content, testimonials, or photos. |
| Address / phone number to display | **Clear placeholders** (`[Your Street Address]`, `[Your Phone Number]`), not the reference site's real ones. Flagged to the user: reusing that business's actual address and phone would misdirect real callers/visitors to an unrelated company and risks a passing-off claim, given how close the two trading names already are. User's first instinct was to reuse them; after the flag, placeholders were the agreed path — real details to be swapped in by the user before launch. |
| Site scope for this build | Full site now (all pages below), not a phased MVP. |
| Visual assets | Original AI-generated photography + an original new logo (not the reference site's triangle mark). |

## 2. Site Map (SDLC: Requirements → Design)

```
/index.html                          Home
/about.html                          About Us
/services.html                       Services overview
/services/live-in-care.html
/services/domiciliary-care.html
/services/companionship-care.html
/services/autism-support.html
/areas-we-support.html
/staffing-solutions.html
/news.html                           News index
/news/<5 original articles>.html     Same themes as reference (live-in care, domiciliary
                                      care, companionship, medication safety, wellbeing) —
                                      original headlines and original writing, not the
                                      reference site's copyrighted copy.
/contact.html                        Name / email / subject / message
/apply.html                          Job application + CV upload
/timesheet.html                      Timesheet submission + file upload
/forms/application-form-print.html   Print-stylesheet page (stand-in for a PDF we don't have)
/forms/reference-form-print.html
/forms/timesheet-print.html
/thank-you.html                      Generic form-success page (no-JS fallback target)
/privacy-policy.html                 Added: any UK site collecting names/emails/CVs via
/terms.html                          forms needs one; not present in original ask but
                                      low-cost and expected.
/404.html
```

## 3. Visual Design (SDLC: Design)

- **Logo**: original SVG wordmark/mark, not the reference site's triangle icon. Built as
  inline SVG so it stays crisp and is trivial to recolor.
- **Palette**: deep trustworthy blue (primary) + a warm teal/coral accent for CTAs, warm
  off-white background, dark slate text. Same "professional healthcare" register as the
  reference without matching its exact swatches.
- **Type**: system font stack (`-apple-system, "Segoe UI", Roboto, ...`). No external font
  requests — faster, no third-party dependency, nothing to break on shared hosting.
- **Photography**: original AI-generated images (hero, section imagery), generated fresh —
  not the reference site's stock photography.
- Visual polish pass during implementation via `design-taste-frontend` / `ui-ux-pro-max` /
  `21st-ui-build`, aiming for a site that doesn't read as a templated theme clone.

## 4. Content Approach (SDLC: Design)

Two deliberate departures from the reference site's content pattern, both about not
presenting fabricated claims as fact:

- **Testimonials**: section kept (it's good for conversion) but seeded with visibly
  placeholder quotes, e.g. `"[Client testimonial goes here]" — Name, Role`, clearly meant
  to be replaced with real client quotes before launch. Not fabricated reviews presented as
  genuine.
- **Stats counters**: the reference site shows numbers like "12+ Total Top Services" / "99%
  Positive Feedback". Since Plutobv is a new business, we use value statements instead
  ("Careful vetting", "24/7 support") rather than invented performance metrics that would
  read as false claims about an operating history that doesn't exist yet.

Service categories (4, matching the reference site's categories, appropriate to the
industry): Live-in care, Domiciliary care, Companionship care, Autism support.

## 5. Architecture (SDLC: Design)

Plain static `.html` files — no templating engine, no build step. Each page carries its own
header/footer markup directly (kept consistent by hand across files during authoring, not
via PHP includes), so the deployed site is exactly what the user asked for: static HTML,
deployable by uploading the folder as-is.

```
plutobv-website/
├── index.html, about.html, services.html, ... (all pages from the site map)
├── services/*.html
├── news/*.html
├── forms/*.html
├── assets/
│   ├── css/        base.css, components.css, pages.css
│   ├── js/          main.js (nav toggle, form validation + fetch submit,
│   │                 scroll-reveal, testimonial slider, stat animation)
│   └── images/      generated photography + logo SVG
├── backend/
│   ├── contact-handler.php
│   ├── apply-handler.php
│   ├── timesheet-handler.php
│   └── lib/         shared validation / sanitization / mail helpers
├── .htaccess
├── README.md         deployment steps + list of placeholders to replace before go-live
└── docs/superpowers/specs/   (this file, and the implementation plan)
```

Project lives in its own folder (`plutobv-website/`) with its own git repo, separate from
the unrelated quotation files already in the parent `Desktop/Claude` directory.

## 6. Backend & Forms (SDLC: Design)

Three PHP handlers (Hostinger shared hosting supports PHP natively, no extra setup):

- `contact-handler.php` — name, email, subject, message → email to `info@plutobv.co.uk`.
- `apply-handler.php` — name, email, phone, position, message + CV upload.
- `timesheet-handler.php` — name/reference + file upload.

Each handler: validates required fields server-side (mirroring client-side JS validation
for UX), sanitizes input, checks uploaded files against an extension/MIME allow-list and a
size cap, and **emails the uploaded file as an attachment rather than storing it on the
server** — this removes persistent-upload attack surface (no stored-file/web-shell risk)
entirely. Sends via PHP `mail()`; README notes the upgrade path to SMTP/PHPMailer if
deliverability needs improving later. Returns JSON for the JS layer to show inline
success/error; plain `<form action>` fallback still works with JS disabled, landing on
`/thank-you.html`.

## 7. Security (SDLC: Design — the "cybersecurity pass")

- Input sanitization and output encoding throughout (no raw user input echoed into HTML
  or shell/mail headers unescaped).
- Upload hardening: extension allow-list, MIME sniffing (not just trusting the client's
  claimed type), size cap, random-not-user-controlled temp handling, no persistence to disk.
- Honeypot field + a per-form token on all three forms as basic spam/CSRF mitigation
  (no login/session system exists to protect, so this stays lightweight by design).
- `.htaccess`: security headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy,
  a basic CSP), `Options -Indexes` (no directory listing), block direct access to
  `backend/lib/`.
- Final pass before calling the build done: strip debug output, leftover comments, and any
  test/scaffold code — via the security-and-hardening skill and a security-review pass.

## 8. Testing (SDLC: Testing)

- Responsive check at mobile / tablet / desktop widths in the browser pane.
- Form testing: valid submission, missing-required-field, invalid file type, oversized
  file, honeypot triggered.
- `php -l` lint on each backend script.
- Accessibility pass: semantic landmarks/headings, alt text on all images, visible focus
  states, color-contrast check on the chosen palette.

## 9. Deployment (SDLC: Deployment)

No build step. Upload the `plutobv-website/` contents to `public_html/` on Hostinger via
File Manager or FTP/SFTP. `README.md` documents: where the placeholder address/phone/logo
live and need replacing, how to point the `plutobv.co.uk` domain at the hosting account,
and the SMTP/PHPMailer upgrade path if `mail()` deliverability is an issue.

## 10. Out of Scope (for this build)

CMS/admin panel for the blog (hand-authored static pages instead), payments/booking,
user accounts or login, a database (none needed — forms email their submissions, they
don't persist anything server-side).
