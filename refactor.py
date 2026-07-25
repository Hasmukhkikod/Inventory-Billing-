import os
import re

def main():
    api_dir = 'api'
    app_dir = 'application'

    query_pattern = re.compile(r'->query\(\s*(["\'])(.*?)\1', re.IGNORECASE | re.DOTALL)
    
    # Let's just collect all queries to see what we are dealing with
    queries = []
    
    for root_dir in [api_dir, app_dir]:
        for root, dirs, files in os.walk(root_dir):
            for file in files:
                if file.endswith('.php'):
                    path = os.path.join(root, file)
                    with open(path, 'r') as f:
                        content = f.read()
                        matches = query_pattern.findall(content)
                        for match in matches:
                            queries.append((path, match[1]))
                            
    print(f"Found {len(queries)} queries.")
    # Print sample
    for path, q in queries[:20]:
        print(f"[{path}] {q}")

if __name__ == '__main__':
    main()
