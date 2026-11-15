#!/usr/bin/env python3
"""Stamp assets/app.js and assets/app.css with a short hash of their content.

index.html and sw.js refer to them as app.js?v=<hash> / app.css?v=<hash>. The service
worker keeps files with ?v= forever, so the stamp must change whenever the file does.
Run this after every edit to either file (it is safe to run any time)."""
import hashlib, pathlib, re

root = pathlib.Path(__file__).resolve().parent.parent
for name in ('app.js', 'app.css'):
    digest = hashlib.sha1((root / 'assets' / name).read_bytes()).hexdigest()[:8]
    for page in ('index.html', 'sw.js'):
        path = root / page
        text = path.read_text()
        updated = re.sub(rf'assets/{re.escape(name)}\?v=[0-9a-z]+', f'assets/{name}?v={digest}', text)
        if updated != text:
            path.write_text(updated)
    print(f'{name} -> {digest}')
