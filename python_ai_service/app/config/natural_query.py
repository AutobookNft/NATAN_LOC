"""Natural language query configuration and guardrails."""
import os
from typing import List

# Rate limit config (shared with Laravel)
MAX_ATTEMPTS = int(os.getenv("NATURAL_QUERY_MAX_ATTEMPTS", "20"))
DECAY_MINUTES = int(os.getenv("NATURAL_QUERY_DECAY_MINUTES", "5"))

# Blacklisted phrases (lowercase for easier matching)
BLACKLIST: List[str] = [
    "drop database",
    "delete from",
    "truncate",
    "shutdown",
    "rm -rf",
]

