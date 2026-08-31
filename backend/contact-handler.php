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
