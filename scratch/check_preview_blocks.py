with open("index.php", "r", encoding="utf-8") as f:
    php_lines = f.readlines()
with open("preview.html", "r", encoding="utf-8") as f:
    html_lines = f.readlines()

# Look for standard indicators or comments
# Let's count how many times "discovery-panel-1" appears in preview.html
html_content = "".join(html_lines)
print("Count of 'discovery-panel-1' in preview.html:", html_content.count("discovery-panel-1"))
print("Count of 'discovery-panel-1' in index.php:", "".join(php_lines).count("discovery-panel-1"))

# Let's print out lines around the stepper in preview.html
stepper_line = -1
for idx, line in enumerate(html_lines):
    if "discovery-steps-progress" in line:
        stepper_line = idx
        break
print(f"Stepper in preview.html on line {stepper_line+1}:")
if stepper_line != -1:
    for i in range(max(0, stepper_line - 1), min(len(html_lines), stepper_line + 10)):
        print(f"  {i+1}: {html_lines[i].strip()}")
