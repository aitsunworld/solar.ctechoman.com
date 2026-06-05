with open("index.php", "r", encoding="utf-8") as f:
    lines = f.readlines()

for i, line in enumerate(lines):
    line_num = i + 1
    if "<section" in line or "</section>" in line or "id=\"residential-discovery-journey\"" in line or "id=\"residential-discovery-results\"" in line:
        print(f"Line {line_num}: {line.strip()}")
        # Print preceding 3 lines of PHP if any
        start_check = max(0, i - 4)
        for j in range(start_check, i):
            if "<?php" in lines[j] or "if" in lines[j] or "else" in lines[j] or "endif" in lines[j]:
                print(f"  [PHP Context at {j+1}]: {lines[j].strip()}")
