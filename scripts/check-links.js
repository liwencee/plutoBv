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
}

console.log(brokenCount === 0 ? 'All internal links resolve.' : `${brokenCount} broken link(s) found.`);
process.exit(brokenCount === 0 ? 0 : 1);
