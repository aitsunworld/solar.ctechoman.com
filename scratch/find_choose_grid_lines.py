with open('theme-v3.css', 'r', encoding='utf-8', errors='ignore') as f:
    for i, line in enumerate(f, 1):
        if '.choose-us-grid' in line:
            print(f"Line {i}: {line.strip()}")
