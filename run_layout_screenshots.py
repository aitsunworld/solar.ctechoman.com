import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select

def take_screenshots():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--hide-scrollbars")
    
    driver = webdriver.Chrome(options=options)
    
    cwd = os.getcwd()
    file_path = os.path.join(cwd, "preview.html")
    url = "file://" + file_path.replace("\\", "/")
    
    # Target directory for artifacts
    artifact_dir = r"C:\Users\Sagar\.gemini\antigravity-ide\brain\a6d54b4f-6860-4fb1-8499-2a6a2b6ba3ca"
    os.makedirs(artifact_dir, exist_ok=True)
    
    viewports = {
        "desktop_1920x1080": (1920, 1080),
        "desktop_1440x900": (1440, 900),
        "desktop_1366x768": (1366, 768),
        "mobile_390x844": (390, 844),
        "mobile_430x932": (430, 932)
    }
    
    try:
        for name, size in viewports.items():
            print(f"Setting viewport to {size[0]}x{size[1]} for {name}...")
            driver.set_window_size(size[0], size[1])
            driver.get(url)
            time.sleep(2) # wait for page to render
            
            # Save screenshot
            screenshot_path = os.path.join(artifact_dir, f"{name}.png")
            driver.save_screenshot(screenshot_path)
            print(f"Saved screenshot to {screenshot_path}")
            
    finally:
        driver.quit()

if __name__ == "__main__":
    take_screenshots()
