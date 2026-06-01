import json

with open(r'C:\Users\Sagar\.gemini\antigravity-ide\brain\d55f15e8-2f39-4c59-9aa4-5cebbbcebcfd\.system_generated\logs\transcript.jsonl', 'r', encoding='utf-8') as f:
    for line in f:
        try:
            data = json.loads(line)
            if data.get('type') == 'USER_INPUT':
                print(f"--- {data.get('created_at')} ---")
                print(data.get('content'))
                print("\n" + "="*50 + "\n")
        except:
            pass
