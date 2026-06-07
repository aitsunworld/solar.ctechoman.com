import re

with open('preview_ar.html', 'r', encoding='utf-8') as f:
    html = f.read()

start_match = re.search(r'<div class="[^"]*?insights-row[^"]*?">', html)
if start_match:
    start = start_match.start()
    depth = 0
    end = -1
    for i in range(start, len(html)):
        if html[i:i+4] == '<div':
            depth += 1
        elif html[i:i+6] == '</div>':
            depth -= 1
            if depth == 0:
                end = i + 6
                break
    
    if end != -1:
        with open('scratch/insights_row_ar.html', 'w', encoding='utf-8') as f_out:
            f_out.write(html[start:end])
        print("Successfully saved insights_row_ar.html.")
    else:
        print("Closing div not found in Arabic.")
else:
    print("insights-row start tag not found in Arabic.")
