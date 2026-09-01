<?php
/**
 * Template for backend/lib/config.php.
 *
 * COPY THIS FILE to config.php in the same directory and put the real secret
 * in the copy. config.php is git-ignored; this template is not.
 *
 * Why the split: this repository is public on GitHub. A secret committed here
 * would be readable by anyone, and would stay in the git history even after
 * being deleted, so it would have to be regenerated rather than removed.
 *
 * Deploying it: because config.php is git-ignored, a Git deployment will not
 * create it on the server. Upload it once by hand through hPanel's File
 * Manager into backend/lib/, or set RECAPTCHA_SECRET as an environment
 * variable in hPanel instead - the code checks the environment first and
 * falls back to this file.
 *
 * It is safe from the web either way: backend/lib/.htaccess denies all HTTP
 * access to this directory, so config.php is never served even though it
 * sits under public_html.
 */

return [
    // From https://www.google.com/recaptcha/admin - the SECRET key, not the
    // site key. The site key is public and lives in the HTML.
    'recaptcha_secret' => '',

    // Minimum seconds a real person takes to fill a form. Submissions faster
    // than this are treated as automated. Raise it if spam still gets through.
    'min_fill_seconds' => 3,
];
