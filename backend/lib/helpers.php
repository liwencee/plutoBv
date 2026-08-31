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

    if (!function_exists('finfo_open')) {
        return [false, 'Unable to verify file type. Please try again later.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedMimeTypes, true)) {
        return [false, 'That file type is not accepted.'];
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

    return mail(strip_header_injection($to), strip_header_injection($subject), $body, $headers);
}
