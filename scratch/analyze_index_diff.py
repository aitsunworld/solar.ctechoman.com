import subprocess

res = subprocess.run(["git", "diff", "-U0", "index.php"], capture_output=True, text=True, encoding='utf-8')
added_lines = []
if res.stdout:
    for line in res.stdout.splitlines():
        if line.startswith("@@"):
            parts = line.split(" ")
            new_part = parts[2]
            if "," in new_part:
                start = int(new_part[1:].split(",")[0])
                count = int(new_part.split(",")[1])
            else:
                start = int(new_part[1:])
                count = 1
            added_lines.append((start, count))

print(f"Added blocks in index.php (count={len(added_lines)}):")
for start, count in added_lines:
    print(f"  Line {start} to {start + count - 1} ({count} lines)")
else:
    print("No output received or error occurred:", res.stderr)
