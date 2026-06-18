import fs from 'fs';
import path from 'path';

const swPath = path.resolve('public/sw.js');
try {
    let content = fs.readFileSync(swPath, 'utf8');
    const version = 'delni-public-' + Date.now().toString(36);
    content = content.replace(/const CACHE_VERSION = '[^']*';/, `const CACHE_VERSION = '${version}';`);
    fs.writeFileSync(swPath, content, 'utf8');
    console.log(`[PWA] Updated sw.js CACHE_VERSION to: ${version}`);
} catch (error) {
    console.error('[PWA] Failed to update sw.js version:', error.message);
    process.exit(1);
}
