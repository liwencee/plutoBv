<?php
declare(strict_types=1);

require __DIR__ . '/lib/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$redirectUrl = '/thank-you';

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
    'admin@plutobv.co.uk',
    "Job application: {$position} — {$name}",
    $body,
    $email,
    $_FILES['cv']
);

if (!$sent) {
    respond(false, 'Something went wrong sending your application. Please email admin@plutobv.co.uk directly.', $redirectUrl, 502);
}

respond(true, "Thanks — we've received your application. We'll be in touch soon.", $redirectUrl);
