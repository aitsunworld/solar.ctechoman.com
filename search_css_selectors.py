# search_css_selectors.py
import re

with open("style.css", "r", encoding="utf-8") as f:
    lines = f.readlines()

occurrences = []
for idx, line in enumerate(lines):
    line_num = idx + 1
    if "appliance-inputs-container" in line or "appliance-grid" in line or "calc-form" in line:
        occurrences.append((line_num, line.strip()))

print(f"Found {len(occurrences)} occurrences in style.css:")
for ln, txt in occurrences:
    print(f"Line {ln}: {txt}")
