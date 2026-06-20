import urllib.request

url = 'https://solar.ctechoman.com/theme-v2.css'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as response:
        print("Status:", response.status)
        content = response.read()
        print("Content length:", len(content))
except Exception as e:
    print("Error:", e)
