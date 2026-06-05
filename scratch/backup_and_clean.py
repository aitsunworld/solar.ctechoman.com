import shutil
import subprocess

shutil.copy2("index.php", "scratch/index_backup.php")
shutil.copy2("script.js", "scratch/script_backup.js")
print("Backed up index.php and script.js to scratch/")

res1 = subprocess.run(["git", "checkout", "--", "index.php", "script.js"], capture_output=True, text=True)
print("Restored original files from Git:", res1.returncode)
