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
