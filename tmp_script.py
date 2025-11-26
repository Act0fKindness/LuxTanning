import json
import subprocess

res = subprocess.run(['node', '-v'], capture_output=True, text=True)
print(res.stdout)
