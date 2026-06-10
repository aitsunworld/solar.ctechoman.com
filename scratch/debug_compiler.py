import re
import sys

if sys.version_info >= (3, 7):
    sys.stdout.reconfigure(encoding='utf-8')

workspace = r"c:\Users\Dell\Documents\GitHub\solar.ctechoman.com"

# Parse arrays
def parse_php_array(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    pairs = re.findall(r"'([^']+)'\s*=>\s*(?:'([^']*)'|\$c\['([^']+)'\]|\s*require[^;]+|\s*\[[^\]]+\])", content)
    result = {}
    for match in pairs:
        key = match[0]
        val = match[1] or match[2] or ""
        result[key] = val
    matches = re.finditer(r"'([^']+)'\s*=>\s*'([^']*)'", content)
    for m in matches:
        result[m.group(1)] = m.group(2)
    return result

constants = parse_php_array(workspace + "\\constants.php")
lang = parse_php_array(workspace + "\\lang\\en.php")

lang['cta_hotline'] = f"hotline: ( {constants.get('phone_2', '+968 92315949')} )"
lang['foot_copy'] = f"&copy; 2026 {constants.get('address_1')}.<br>Powering the Future."
lang['foot_loc_desc'] = f"{constants.get('address_1')}<br>{constants.get('address_2')}<br>{constants.get('address_country')}<br>{constants.get('timings')}"
lang['foot_mail_desc'] = f'<a href="mailto:{constants.get("email")}">{constants.get("email")}</a><br>Fax No : {constants.get("fax")}'
lang['foot_call_desc'] = f'<a href="tel:{constants.get("phone_1_clean")}">{constants.get("phone_1")}</a><br><a href="tel:{constants.get("phone_2_clean")}">{constants.get("phone_2")}'

with open(workspace + "\\index.php", "r", encoding="utf-8") as f:
    html = f.read()

print("Initial length:", len(html))

# Helper to check content around calibration
def check_status(step_name):
    pos = html.find("calibration-status-message")
    if pos != -1:
        print(f"[{step_name}] Substring around calibration:")
        print(html[pos-100:pos+300])
    else:
        print(f"[{step_name}] 'calibration-status-message' NOT FOUND!")

check_status("Initial")

html = html.replace('<?= $active_lang ?>', 'en')
html = html.replace('<?= $dir ?>', 'ltr')
check_status("After lang/dir replace")

pattern_if_else = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'en'\s*\):\s*\?>(.*?)<\?php\s+else:\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
html = pattern_if_else.sub(r"\1", html)
check_status("After if_else en")

pattern_if_else_ar = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'ar'\s*\):\s*\?>(.*?)<\?php\s+else:\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
html = pattern_if_else_ar.sub(r"\2", html)
check_status("After if_else ar")

pattern_if_en = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'en'\s*\):\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
html = pattern_if_en.sub(r"\1", html)
check_status("After if_en")

pattern_if_ar = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'ar'\s*\):\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
html = pattern_if_ar.sub(r"", html)
check_status("After if_ar")

pattern_ternary = re.compile(r"<\?=\s*\$active_lang\s*===\s*'ar'\s*\?\s*'([^']*)'\s*:\s*'([^']*)'\s*\?>")
html = pattern_ternary.sub(r"\2", html)
check_status("After pattern_ternary")

def replace_lang(match):
    key = match.group(1)
    return lang.get(key, f"Translation missing: {key}")
html = re.sub(r"<\?=\s*\$lang\['([^']+)'\]\s*\?>", replace_lang, html)
check_status("After replace_lang")

def replace_const(match):
    key = match.group(1)
    return constants.get(key, f"Constant missing: {key}")
html = re.sub(r"<\?=\s*\$constants\['([^']+)'\]\s*\?>", replace_const, html)
check_status("After replace_const")

# Slide & Nav loops
slides_html = "SLIDES_PLACEHOLDER"
nav_html = "NAV_PLACEHOLDER"
slide_loop_pattern = re.compile(r'<\?php\s+\$slide_images\s*=\s*\[.*?foreach\s*\(\s*\$lang\s*\[\s*\'slides\'\s*\]\s*as\s*\$index\s*=>\s*\$slide\s*\):\s*\?>.*?<\?php\s+endforeach;\s*\?>', re.DOTALL)
html = slide_loop_pattern.sub(slides_html, html)
nav_loop_pattern = re.compile(r'<\?php\s+foreach\s*\(\s*\$lang\s*\[\s*\'slides\'\s*\]\s*as\s*\$index\s*=>\s*\$slide\s*\):\s*\?>.*?<\?php\s+endforeach;\s*\?>', re.DOTALL)
html = nav_loop_pattern.sub(nav_html, html)
check_status("After loops")

# Strip remaining php blocks
html = re.sub(r"<\?php.*?\?>", "", html, flags=re.DOTALL)
check_status("After php strip")
