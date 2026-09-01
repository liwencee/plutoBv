// Run with: node scripts/check-links.js
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const htmlFiles = [];

function walk(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name === '.git' || entry.name === 'scripts') continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full);
    else if (entry.name.endsWith('.html')) htmlFiles.push(full);
  }
}
walk(root);

const linkPattern = /(?:href|src|action)="(\/[^"]*)"/g;
let brokenCount = 0;

// <source srcset> inside <picture>. Worth checking separately: a broken
// WebP path here does not show up as a visibly broken image, because the
// browser quietly falls back to the JPEG. It would just silently ship the
// larger file forever.
const srcsetPattern = /srcset="([^"]+)"/g;

for (const file of htmlFiles) {
  const content = fs.readFileSync(file, 'utf8');
  let match;
  while ((match = linkPattern.exec(content))) {
    const link = match[1].split('#')[0].split('?')[0];
    if (link === '' || link.startsWith('/backend/')) continue; // PHP endpoints, tested separately
    const targetPath = path.join(root, link);
    if (!fs.existsSync(targetPath)) {
      console.log(`BROKEN: ${path.relative(root, file)} -> ${link}`);
      brokenCount++;
    }
  }

  while ((match = srcsetPattern.exec(content))) {
    for (const candidate of match[1].split(',')) {
      const url = candidate.trim().split(/\s+/)[0];
      if (!url.startsWith('/')) continue;
      const targetPath = path.join(root, url.split('?')[0]);
      if (!fs.existsSync(targetPath)) {
        console.log(`BROKEN srcset: ${path.relative(root, file)} -> ${url}`);
        brokenCount++;
      }
    }
  }
}

console.log(brokenCount === 0 ? 'All internal links resolve.' : `${brokenCount} broken link(s) found.`);
process.exit(brokenCount === 0 ? 0 : 1);
