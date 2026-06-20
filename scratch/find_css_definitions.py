import os
import re

for root, dirs, files in os.walk('.'):
    if '.git' in root or 'node_modules' in root:
        continue
    for file in files:
        if file.endswith(('.css', '.html', '.php')):
            path = os.path.join(root, file)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    matches = list(re.finditer(r'\.testimonials-grid|\.testimonial-card', content))
                    if matches:
                        print(f"Found match in {path}:")
                        for m in matches:
                            start = max(0, m.start() - 100)
                            end = min(len(content), m.end() + 200)
                            print(f"  Context: {content[start:end].replace('\n', ' ')}")
            except Exception as e:
                pass
