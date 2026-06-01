# search_prop_types.py
import sys

# Ensure UTF-8 output on Windows terminal
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

with open("script.js", "r", encoding="utf-8") as f:
    lines = f.readlines()

for idx, line in enumerate(lines):
    line_num = idx + 1
    if any(pt in line for pt in ["residential", "commercial", "industrial", "selectedPropType"]):
        print(f"Line {line_num}: {line.strip()}")
