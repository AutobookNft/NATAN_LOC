#!/usr/bin/env python3
import subprocess
import sys

result = subprocess.run(
    [sys.executable, "/home/fabio/NATAN_LOC/test_retrieval.py"],
    capture_output=True,
    text=True,
    cwd="/home/fabio/NATAN_LOC"
)

print(result.stdout)
if result.stderr:
    print("STDERR:", result.stderr, file=sys.stderr)

sys.exit(result.returncode)

