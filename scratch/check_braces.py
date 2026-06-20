with open('theme-v3.css', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

open_braces = 0
close_braces = 0
errors = []
for i, char in enumerate(content, 1):
    if char == '{':
        open_braces += 1
    elif char == '}':
        close_braces += 1
        if close_braces > open_braces:
            errors.append(f"Extra closing brace at char {i}")
            close_braces -= 1  # reset to avoid cascade

print("Open braces count:", open_braces)
print("Close braces count:", close_braces)
if open_braces != close_braces:
    print("WARNING: Braces are unbalanced!")
else:
    print("Braces are balanced.")
if errors:
    print("Errors:")
    for err in errors:
        print(err)
