import re
import sys

# Set standard output encoding to utf-8
sys.stdout.reconfigure(encoding='utf-8')

with open('index.php', 'r', encoding='utf-8', errors='ignore') as f:
    for i, line in enumerate(f, 1):
        if '<section' in line or 'id=' in line:
            print(f"{i}: {line.strip()}")
