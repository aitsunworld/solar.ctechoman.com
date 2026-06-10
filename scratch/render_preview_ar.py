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

    # Expand Arabic slider loop statically
    slides_ar = [
        {
            'badge': '⚡ خفض الفواتير',
            'title': 'خفّض فواتير الكهرباء بنسبة تصل إلى 90٪',
            'desc': 'استغل أشعة الشمس الوفيرة في سلطنة عمان لتشغيل منزلك. توقف عن دفع فواتير مرتفعة وابدأ بتوليد طاقتك الخاصة لتلغي التكاليف الشهرية.',
            'btn': 'احسب وفرك ➔',
            'img': 'lightbulb.webp'
        },
        {
            'badge': '☀️ استقلالية الطاقة',
            'title': 'ولّد طاقتك النظيفة بنفسك',
            'desc': 'كن مصدر الطاقة الخاص بك. توفر الطاقة الشمسية استقلالية تامة في الطاقة، مما يحافظ على تشغيل منزلك بكهرباء نظيفة وموثوقة.',
            'btn': 'احسب وفرك ➔',
            'img': 'solar_panel.webp'
        },
        {
            'badge': '🔒 حماية التكاليف',
            'title': 'احمِ نفسك من ارتفاع أسعار الكهرباء مستقبلاً',
            'desc': 'أسعار الكهرباء في ارتفاع مستمر. ثبّت تكلفة طاقتك اليوم واضمن الحصول على كهرباء مجانية ومستقرة لأكثر من 25 عاماً قادمة.',
            'btn': 'احسب وفرك ➔',
            'img': 'hero-commercial.webp?v=3.11'
        },
        {
            'badge': '📈 زيادة قيمة العقار',
            'title': 'زد من قيمة عقارك مع الطاقة الشمسية',
            'desc': 'تُباع المنازل الحديثة المجهزة بأنظمة الطاقة الشمسية الذكية بشكل أسرع وبسعر أعلى. الطاقة الشمسية هي أصل استثماري يدر عوائد مالية عالية.',
            'btn': 'احسب وفرك ➔',
            'img': 'hero-villa.webp'
        },
        {
            'badge': '📊 تحليل فوري',
            'title': 'تعرف على وفرك المالي المتوقع في دقائق',
            'desc': 'بدون مكالمات مبيعات مزعجة. استخدم حاسبتنا الذكية والتفاعلية لتكتشف حجم النظام الموصى به وتفاصيل وفرك المالي الفعلي.',
            'btn': 'احسب وفرك ➔',
            'img': 'lightbulb.webp'
        },
        {
            'badge': '🌱 صديق للبيئة',
            'title': 'قلل من انبعاثات الكربون وادعم الاستدامة',
            'desc': 'ادعم رؤية عمان 2040. انضم إلى التحول الأخضر، وقلل من بصمتك البيئية وازرع ما يعادل مئات الأشجار سنوياً.',
            'btn': 'احسب وفرك ➔',
            'img': 'solar_panel.webp'
        }
    ]

    slides_html = ""
    for index, slide in enumerate(slides_ar):
        lazy = "eager" if index == 0 else "lazy"
        priority = "fetchpriority=\"high\"" if index == 0 else ""
        slides_html += f"""
          <div class="hero-slide">
            <div class="slide-card-container">
              <div class="slide-card-text">
                <span class="slide-badge">{slide['badge']}</span>
                <h2>{slide['title']}</h2>
                <p>{slide['desc']}</p>
                <div class="slide-actions">
                  <a href="#calculator" class="btn btn-hero-primary">{slide['btn']}</a>
                </div>
              </div>
              <div class="slide-card-visual">
                <img src="{slide['img']}" alt="{slide['title']}" class="lightbulb-img" width="500" height="500"
                  loading="{lazy}" {priority} style="mix-blend-mode: darken;">
              </div>
            </div>
          </div>
        """

    nav_html = ""
    for index in range(len(slides_ar)):
        active_class = " active" if index == 0 else ""
        num_str = f"{index + 1:02d}"
        nav_html += f"""
          <div class="slider-item{active_class}" data-slide-index="{index}">
            <span class="slider-num">{num_str}</span>
            <div class="slider-line"></div>
          </div>
        """

    # Locate and replace the slide loop in the track
    slide_loop_pattern = re.compile(r'<\?php\s+\$slide_images\s*=\s*\[.*?foreach\s*\(\s*\$lang\s*\[\s*\'slides\'\s*\]\s*as\s*\$index\s*=>\s*\$slide\s*\):\s*\?>.*?<\?php\s+endforeach;\s*\?>', re.DOTALL)
    html = slide_loop_pattern.sub(slides_html, html)

    # Locate and replace the navigation controls loop
    nav_loop_pattern = re.compile(r'<\?php\s+foreach\s*\(\s*\$lang\s*\[\s*\'slides\'\s*\]\s*as\s*\$index\s*=>\s*\$slide\s*\):\s*\?>.*?<\?php\s+endforeach;\s*\?>', re.DOTALL)
    html = nav_loop_pattern.sub(nav_html, html)

    # Strip remaining php blocks/headers
    html = re.sub(r"<\?php.*?\?>", "", html, flags=re.DOTALL)

    preview_path = os.path.join(workspace, "preview_ar.html")
    with open(preview_path, 'w', encoding='utf-8') as f:
        f.write(html)
        
    print(f"Successfully rendered preview_ar.html with {len(html)} bytes.")

if __name__ == "__main__":
    render()
