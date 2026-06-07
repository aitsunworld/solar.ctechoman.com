import json
import re

LOG_PATH = r'C:\Users\Dell\.gemini\antigravity-ide\brain\e52226d4-d4b2-4c2c-9e3f-69865bd44d80\.system_generated\logs\transcript.jsonl'

# We want to find the view_file tool output for style.css in the previous session (steps 59, 61, 63, 66)
# Let's inspect each line in transcript.jsonl and find the results
print("Reconstructing style.css from transcript...")

blocks = {}

with open(LOG_PATH, 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            step_idx = data.get('step_index')
            # Check if this is a response step containing tool output
            if data.get('type') == 'VIEW_FILE' and data.get('status') == 'DONE':
                content_text = data.get('content', '')
                if 'File Path: `file:///C:/Users/Dell/Documents/GitHub/solar.ctechoman.com/style.css`' in content_text or 'style.css' in data.get('tool_calls', [{}])[0].get('args', {}).get('AbsolutePath', ''):
                    # Extract the lines
                    lines_match = re.findall(r'^(\d+): (.*)$', content_text, re.MULTILINE)
                    if lines_match:
                        for lineno_str, line_val in lines_match:
                            blocks[int(lineno_str)] = line_val
        except Exception as e:
            pass

print(f"Total lines extracted: {len(blocks)}")
if blocks:
    print(f"Line range: {min(blocks.keys())} to {max(blocks.keys())}")
    # Write back to style_reconstructed.css
    max_line = max(blocks.keys())
    with open('style_reconstructed.css', 'w', encoding='utf-8') as out:
        for i in range(1, max_line + 1):
            line_val = blocks.get(i, "")
            out.write(line_val + "\n")
    print("reconstructed style.css written to style_reconstructed.css")
else:
    print("No lines found!")
