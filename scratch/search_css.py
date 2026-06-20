with open('theme-v3.css', 'r', encoding='utf-8', errors='ignore') as f:
    for i, line in enumerate(f, 1):
        if 'testimonial' in line.lower() or 'process-step' in line.lower() or 'flow-step' in line.lower():
            print(f"{i}: {line.strip()}")
