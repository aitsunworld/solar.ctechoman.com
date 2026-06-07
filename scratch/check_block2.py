with open('style.css', 'r', encoding='utf-8') as f:
    content = f.read()

second_dashboard_str = ".discovery-dashboard {\n  display: flex;\n  flex-direction: column;\n  gap: 1.5rem;\n  width: 100%;\n}"
idx = content.find(second_dashboard_str)
print(f"Index: {idx}")
if idx != -1:
    print("Next 400 characters:")
    print(repr(content[idx:idx+400]))
