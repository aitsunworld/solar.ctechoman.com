import re
import sys

if sys.version_info >= (3, 7):
    sys.stdout.reconfigure(encoding='utf-8')

workspace = r"c:\Users\Dell\Documents\GitHub\solar.ctechoman.com"

# Read index.php
with open(workspace + "\\index.php", "r", encoding="utf-8") as f:
    html = f.read()

# Let's see if the text contains calibration_warning
print("index.php contains calibration_warning:", "calibration_warning" in html)

# Let's search index.php for the range around calibration_warning
pos = html.find("calibration_warning")
if pos != -1:
    print("Around calibration_warning in index.php:")
    print(html[pos-100:pos+300])

# Let's simulate the regex replacement of lang['calibration_warning']
val = "We noticed a difference between appliance usage and your bill history. We\\"
pat = r"<\?=\s*\$lang\['calibration_warning'\]\s*\?>"
print("Pattern matches:", re.search(pat, html) is not None)

html_sub = re.sub(pat, val, html)
pos_sub = html_sub.find("We noticed a difference")
print("After sub:")
print(html_sub[pos_sub:pos_sub+400])
