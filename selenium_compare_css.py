# selenium_compare_css.py
import urllib.request
import hashlib
import sys
import difflib

if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def compare_css():
    prod_css_url = "https://solar.ctechoman.com/style.css?v=4.0"
    local_css_path = "style.css"
    
    print("Fetching production CSS...")
    try:
        req = urllib.request.Request(
            prod_css_url, 
            headers={'User-Agent': 'Mozilla/5.0'}
        )
        with urllib.request.urlopen(req) as response:
            prod_content = response.read()
    except Exception as e:
        print(f"Error fetching production CSS: {e}")
        return
        
    print("Reading local CSS...")
    try:
        with open(local_css_path, "rb") as f:
            local_content = f.read()
    except Exception as e:
        print(f"Error reading local CSS: {e}")
        return
        
    prod_hash = hashlib.md5(prod_content).hexdigest()
    local_hash = hashlib.md5(local_content).hexdigest()
    
    prod_text = prod_content.decode('utf-8', errors='ignore')
    local_text = local_content.decode('utf-8', errors='ignore')
    
    prod_lines = prod_text.splitlines()
    local_lines = local_text.splitlines()
    
    print(f"Production CSS Hash: {prod_hash} (lines={len(prod_lines)})")
    print(f"Local CSS Hash:      {local_hash} (lines={len(local_lines)})")
    
    if prod_hash == local_hash:
        print("Files are identical.")
        return
        
    print("Files are different! Generating diff...")
    
    # Calculate diff
    diff = list(difflib.unified_diff(
        prod_lines, 
        local_lines, 
        fromfile='production_style.css', 
        tofile='local_style.css',
        lineterm=''
    ))
    
    report = []
    report.append("# STYLESHEET DIFFERENCE REPORT")
    report.append(f"\n- **Production style.css hash**: `{prod_hash}` (lines: {len(prod_lines)})")
    report.append(f"\n- **Local style.css hash**: `{local_hash}` (lines: {len(local_lines)})")
    report.append("\n## Line Diff (Production vs Local)")
    report.append("```diff")
    # Show first 150 lines of diff
    report.append("\n".join(diff[:150]))
    if len(diff) > 150:
        report.append(f"\n... [Truncated {len(diff) - 150} lines of diff]")
    report.append("```")
    
    with open("selenium_css_diff.md", "w", encoding="utf-8") as f:
        f.write("\n".join(report))
        
    print("Diff report written successfully as selenium_css_diff.md.")

if __name__ == "__main__":
    compare_css()
