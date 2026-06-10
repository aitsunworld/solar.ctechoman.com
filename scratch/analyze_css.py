import re

def analyze_css(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find selectors and their line numbers
    # A simple regex to find selectors: anything before { that is not inside comments or media queries
    # Let's clean comments first
    clean_content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    
    # We want to match selectors. We can iterate line by line or character by character.
    # Let's count occurrences of curly braces and key selectors.
    lines = content.splitlines()
    selectors = {}
    current_media_query = None
    
    for idx, line in enumerate(lines):
        line_num = idx + 1
        stripped = line.strip()
        if not stripped:
            continue
        
        # Check media query
        if stripped.startswith('@media'):
            current_media_query = stripped
            continue
        
        # If line ends with '{' or has '{', it's likely a selector rule
        if '{' in stripped and not stripped.startswith('@'):
            # Extract selector
            part = stripped.split('{')[0].strip()
            # Split comma separated selectors
            sub_selectors = [s.strip() for s in part.split(',') if s.strip()]
            for sel in sub_selectors:
                if sel not in selectors:
                    selectors[sel] = []
                selectors[sel].append((line_num, current_media_query))
                
        if '}' in stripped and current_media_query and stripped == '}':
            current_media_query = None

    # Find duplicates
    print(f"Total Unique Selectors: {len(selectors)}")
    print("--- Top Duplicated Selectors ---")
    duplicates = {k: v for k, v in selectors.items() if len(v) > 1}
    sorted_duplicates = sorted(duplicates.items(), key=lambda item: len(item[1]), reverse=True)
    
    for sel, occurrences in sorted_duplicates[:40]:
        print(f"Selector '{sel}': {len(occurrences)} times")
        for line, mq in occurrences:
            mq_str = f" in {mq}" if mq else ""
            print(f"  - Line {line}{mq_str}")

if __name__ == "__main__":
    analyze_css("style.css")
