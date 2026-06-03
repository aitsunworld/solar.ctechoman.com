# render_preview_ar.py
import re
import os

def parse_php_array(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Strip comments and PHP tags
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    content = re.sub(r'//.*', '', content)
    
    # Extract keys and values
    pairs = re.findall(r"'([^']+)'\s*=>\s*(?:'([^']*)'|\$c\['([^']+)'\]|\s*require[^;]+|\s*\[[^\]]+\])", content)
    result = {}
    for match in pairs:
        key = match[0]
        val = match[1] or match[2] or ""
        result[key] = val
    
    # Let's do a fallback regex search for standard string entries
    matches = re.finditer(r"'([^']+)'\s*=>\s*'([^']*)'", content)
    for m in matches:
        result[m.group(1)] = m.group(2)
        
    return result

def render():
    workspace = r"c:\Users\Dell\Documents\GitHub\solar.ctechoman.com"
    
    # Load constants
    constants = parse_php_array(os.path.join(workspace, "constants.php"))
    # Load lang/ar
    lang = parse_php_array(os.path.join(workspace, "lang", "ar.php"))
    
    # Re-inject phone_2 since it's referenced in lang/ar.php
    lang['cta_hotline'] = f"hotline: ( {constants.get('phone_2', '+968 92315949')} )"
    lang['foot_copy'] = f"&copy; 2026 {constants.get('address_1')}.<br>Powering the Future."
    lang['foot_loc_desc'] = f"{constants.get('address_1')}<br>{constants.get('address_2')}<br>{constants.get('address_country')}<br>{constants.get('timings')}"
    lang['foot_mail_desc'] = f'<a href="mailto:{constants.get("email")}">{constants.get("email")}</a><br>Fax No : {constants.get("fax")}'
    lang['foot_call_desc'] = f'<a href="tel:{constants.get("phone_1_clean")}">{constants.get("phone_1")}</a><br><a href="tel:{constants.get("phone_2_clean")}">{constants.get("phone_2")}'

    with open(os.path.join(workspace, "index.php"), 'r', encoding='utf-8') as f:
        html = f.read()

    # Replace lang/constants variables
    html = html.replace('<?= $active_lang ?>', 'ar')
    html = html.replace('<?= $dir ?>', 'rtl')
    
    # Conditionals: <?php if ($active_lang === 'en'): ?> ... <?php else: ?> ... <?php endif; ?>
    # Strip if blocks for Arabic
    pattern_if_else = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'en'\s*\):\s*\?>(.*?)<\?php\s+else:\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
    html = pattern_if_else.sub(r"\2", html)

    # Conditionals: <?php if ($active_lang === 'ar'): ?> ... <?php else: ?> ... <?php endif; ?>
    # Keep if blocks for Arabic
    pattern_if_else_ar = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'ar'\s*\):\s*\?>(.*?)<\?php\s+else:\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
    html = pattern_if_else_ar.sub(r"\1", html)

    # Conditionals without else
    pattern_if_en = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'en'\s*\):\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
    html = pattern_if_en.sub(r"", html)
    
    pattern_if_ar = re.compile(r"<\?php\s+if\s*\(\s*\$active_lang\s*===\s*'ar'\s*\):\s*\?>(.*?)<\?php\s+endif;\s*\?>", re.DOTALL)
    html = pattern_if_ar.sub(r"\1", html)

    # Replace inline active_lang ternary operators:
    # <?= $active_lang === 'ar' ? 'ar_val' : 'en_val' ?>
    pattern_ternary = re.compile(r"<\?=\s*\$active_lang\s*===\s*'ar'\s*\?\s*'([^']*)'\s*:\s*'([^']*)'\s*\?>")
    html = pattern_ternary.sub(r"\1", html)
    
    # Replace <?= $lang['...'] ?>
    def replace_lang(match):
        key = match.group(1)
        return lang.get(key, f"Translation missing: {key}")
    html = re.sub(r"<\?=\s*\$lang\['([^']+)'\]\s*\?>", replace_lang, html)

    # Replace <?= $constants['...'] ?>
    def replace_const(match):
        key = match.group(1)
        return constants.get(key, f"Constant missing: {key}")
    html = re.sub(r"<\?=\s*\$constants\['([^']+)'\]\s*\?>", replace_const, html)

    # Strip remaining php blocks/headers
    html = re.sub(r"<\?php.*?\?>", "", html, flags=re.DOTALL)

    preview_path = os.path.join(workspace, "preview_ar.html")
    with open(preview_path, 'w', encoding='utf-8') as f:
        f.write(html)
        
    print(f"Successfully rendered preview_ar.html with {len(html)} bytes.")

if __name__ == "__main__":
    render()
