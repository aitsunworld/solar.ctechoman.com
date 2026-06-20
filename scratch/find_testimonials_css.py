import os

target = 'testimonials-grid'
for root, dirs, files in os.walk('.'):
    if '.git' in root or 'node_modules' in root:
        continue
    for file in files:
        if file.endswith(('.css', '.html', '.php', '.js')):
            path = os.path.join(root, file)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    if target in content:
                        print(f"Found '{target}' in {path}")
            except Exception as e:
                pass
