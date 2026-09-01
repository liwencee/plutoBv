#!/usr/bin/env python3
"""Local preview server for the Plutobv site.

The site uses extensionless URLs (/about rather than /about.html), which on
the live server is handled by mod_rewrite rules in .htaccess. A plain
`python -m http.server` knows nothing about those rules, so every internal
link would 404 locally. This server applies the same resolution order, so
what you see locally matches what Hostinger serves.

It does NOT run the PHP form handlers under backend/ - those need a real PHP
server. Submitting a form here will fail at the network request, which is
expected.

Usage:
    python scripts/serve.py [port]        # default 8421
"""

import http.server
import os
import socketserver
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PORT = int(sys.argv[1]) if len(sys.argv) > 1 else 8421


class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=ROOT, **kwargs)

    def translate_path(self, path):
        resolved = super().translate_path(path)
        if os.path.isfile(resolved):
            return resolved

        # Mirror the .htaccess order: <path>.html, then <path>/index.html.
        # The .html check comes first because "services" is both services.html
        # and a services/ directory, and the page should win.
        for candidate in (resolved.rstrip(os.sep) + ".html",
                          os.path.join(resolved, "index.html")):
            if os.path.isfile(candidate):
                return candidate
        return resolved

    def end_headers(self):
        # No caching locally, so edits show up on refresh instead of being
        # masked by a stale copy.
        self.send_header("Cache-Control", "no-store")
        super().end_headers()

    def log_message(self, fmt, *args):
        sys.stderr.write("%s %s\n" % (self.address_string(), fmt % args))


if __name__ == "__main__":
    socketserver.TCPServer.allow_reuse_address = True
    with socketserver.TCPServer(("", PORT), Handler) as httpd:
        print(f"Plutobv preview: http://localhost:{PORT}/")
        print("Extensionless URLs resolve the same way they do on Hostinger.")
        print("Ctrl+C to stop.")
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print("\nstopped")
