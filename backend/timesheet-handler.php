<?php
declare(strict_types=1);

require __DIR__ . '/lib/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$redirectUrl = '/thank-you';

if (is_honeypot_triggered($_POST)) {
    respond(true, "Thanks — we've received your timesheet.", $redirectUrl);
}

if (is_submitted_too_fast($_POST) || !is_referer_allowed()) {
    respond(false, 'Something went wrong. Please try again.', $redirectUrl, 400);
}

[$captchaOk, $captchaError] = verify_recaptcha(
    (string)($_POST['g-recaptcha-response'] ?? ''),
    (string)($_SERVER['REMOTE_ADDR'] ?? '')
);

if (!$captchaOk) {
    respond(false, $captchaError, $redirectUrl, 422);
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
    'admin@plutobv.co.uk',
    "Timesheet: {$name} — week ending {$weekEnding}",
    $body,
    'admin@plutobv.co.uk',
    $_FILES['timesheet']
);

if (!$sent) {
    respond(false, 'Something went wrong sending your timesheet. Please email admin@plutobv.co.uk directly.', $redirectUrl, 502);
}

respond(true, "Thanks — we've received your timesheet.", $redirectUrl);
