with open('style.css', 'r', encoding='utf-8') as f:
    lines = f.readlines()

def clean_print(s):
    # Keep only ASCII printable characters
    cleaned = "".join(c if ord(c) < 128 else f"\\u{ord(c):04x}" for c in s)
    print(cleaned)

occurrences = []
for idx, line in enumerate(lines):
    if '.discovery-dashboard {' in line:
        occurrences.append(idx + 1)

print(f"Occurrences of '.discovery-dashboard {{': {occurrences}")

for lineno in occurrences:
    print(f"\n--- Occurrence at line {lineno} ---")
    start = max(1, lineno - 2)
    end = min(len(lines), lineno + 15)
    for l in range(start, end + 1):
        clean_print(f"{l}: {lines[l-1].strip()}")
