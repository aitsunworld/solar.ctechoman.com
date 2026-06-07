import re

with open('style.css', 'r', encoding='utf-8') as f:
    content = f.read()

pattern = re.compile(r'\.progress-bar-wrapper\s*\{[^}]*\}')
for match in pattern.finditer(content):
    print(f"Match found at index {match.start()} to {match.end()}:")
    print(repr(match.group(0)))
    print("-" * 40)
