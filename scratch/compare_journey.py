import sys

with open('index.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

def get_block_outline(start_idx):
    outline = []
    for idx in range(start_idx, len(lines)):
        line = lines[idx]
        trimmed = line.strip()
        if 'id="discovery-panel-' in trimmed or 'id=\'discovery-panel-\'' in trimmed or 'discovery-panel-' in trimmed:
            if 'id=' in trimmed:
                outline.append(f'Line {idx+1}: {trimmed}')
        if 'id="residential-discovery-results"' in trimmed:
            outline.append(f'Line {idx+1}: {trimmed}')
        if 'class="discovery-steps-progress' in trimmed:
            outline.append(f'Line {idx+1}: {trimmed}')
        if '</section>' in line:
            outline.append(f'Line {idx+1}: </section>')
            break
    return outline

print('--- First block outline ---')
for item in get_block_outline(191):
    print(item)

print('\n--- Second block outline ---')
for item in get_block_outline(771):
    print(item)
