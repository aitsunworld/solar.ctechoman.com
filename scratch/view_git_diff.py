import subprocess
import sys

sys.stdout.reconfigure(encoding='utf-8')
res = subprocess.run(["git", "diff", "index.php"], capture_output=True, text=True, encoding='utf-8')
lines = res.stdout.splitlines()
for idx, line in enumerate(lines[:100]):
    print(line)
if len(lines) > 100:
    print(f"... and {len(lines)-100} more lines")
