with open('theme-v3.css', 'r', encoding='utf-8', errors='ignore') as f:
    for i, line in enumerate(f, 1):
        if '@media' in line:
            print(f"{i}: {line.strip()}")
