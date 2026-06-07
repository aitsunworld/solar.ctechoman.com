import json

LOG_PATH = r'C:\Users\Dell\.gemini\antigravity-ide\brain\e52226d4-d4b2-4c2c-9e3f-69865bd44d80\.system_generated\logs\transcript.jsonl'

print("Printing step 141/142 content...")
with open(LOG_PATH, 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            step_idx = data.get('step_index')
            if step_idx in (141, 142):
                print(f"\n=== Step {step_idx} ===")
                print(f"Type: {data.get('type')}")
                print(f"Status: {data.get('status')}")
                # We can truncate content if it's too long
                content = data.get('content', '')
                print(content[:2000])
        except Exception as e:
            pass
