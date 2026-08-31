// Run with: node scripts/check-alt-text.js
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

const imgPattern = /<img\b[^>]*>/g;
let missing = 0;

for (const file of htmlFiles) {
  const content = fs.readFileSync(file, 'utf8');
  let match;
  while ((match = imgPattern.exec(content))) {
    if (!/\balt\s*=/.test(match[0])) {
      console.log(`MISSING ALT: ${path.relative(root, file)} -> ${match[0].slice(0, 80)}`);
      missing++;
    }
  }
}

console.log(missing === 0 ? 'Every <img> has an alt attribute.' : `${missing} <img> tag(s) missing alt.`);
process.exit(missing === 0 ? 0 : 1);
