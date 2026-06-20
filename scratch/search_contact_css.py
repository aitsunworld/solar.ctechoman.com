with open('theme-v3.css', 'r', encoding='utf-8', errors='ignore') as f:
    for i, line in enumerate(f, 1):
        if any(x in line for x in ['native-lead-form', 'lead-name', 'lead-phone', 'form-control', 'input-group', '.contact-form']):
            print(f"{i}: {line.strip()}")
