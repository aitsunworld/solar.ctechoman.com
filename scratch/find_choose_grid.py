with open('theme-v3.css', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

import re
matches = list(re.finditer(r'\.choose-us-grid', content))
print("Found", len(matches), "matches for '.choose-us-grid':")
for m in matches:
    start = max(0, m.start() - 50)
    end = min(len(content), m.end() + 200)
    print(f"Match at index {m.start()}:")
    print(content[start:end])
    print("-" * 55)
