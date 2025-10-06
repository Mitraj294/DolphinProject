const fs = require('fs');
const path = require('path');

const src = path.resolve(__dirname, '..', 'node_modules', 'tinymce');
const dest = path.resolve(__dirname, '..', 'public', 'tinymce');

function copyRecursiveSync(srcDir, destDir) {
  if (!fs.existsSync(srcDir)) {
    console.error(`Source not found: ${srcDir}`);
    process.exit(1);
  }
  if (!fs.existsSync(destDir)) {
    fs.mkdirSync(destDir, { recursive: true });
  }
  const entries = fs.readdirSync(srcDir, { withFileTypes: true });
  for (const entry of entries) {
    const srcPath = path.join(srcDir, entry.name);
    const destPath = path.join(destDir, entry.name);
    if (entry.isDirectory()) {
      copyRecursiveSync(srcPath, destPath);
    } else if (entry.isFile()) {
      fs.copyFileSync(srcPath, destPath);
    } else {
      console.warn(`Skipping unsupported entry: ${srcPath}`);
    }
  }
}

try {
  console.log(`Copying TinyMCE from ${src} -> ${dest} ...`);
  copyRecursiveSync(src, dest);
  console.log('TinyMCE copy complete.');
} catch (err) {
  console.error('Failed to copy TinyMCE assets:', err);
  process.exit(1);
}