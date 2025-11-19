#!/usr/bin/env python3
"""
Verify MongoDB Atlas IP whitelist configuration
Tests connection and provides information about network access
"""

import sys
import os
import socket
from pathlib import Path

# Add parent directory to path to import app modules
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.mongodb_service import MongoDBService
from app.config import MONGODB_URI

# Colors
GREEN = '\033[0;32m'
RED = '\033[0;31m'
YELLOW = '\033[1;33m'
BLUE = '\033[0;34m'
CYAN = '\033[0;36m'
NC = '\033[0m'

def get_public_ip():
    """Get public IP address"""
    try:
        # Try multiple services
        import urllib.request
        services = [
            'https://api.ipify.org',
            'https://icanhazip.com',
            'https://ifconfig.me/ip'
        ]
        
        for service in services:
            try:
                with urllib.request.urlopen(service, timeout=5) as response:
                    return response.read().decode('utf-8').strip()
            except:
                continue
        
        return None
    except:
        return None

def verify_connection():
    """Verify MongoDB Atlas connection"""
    print(f"{BLUE}{'='*60}{NC}")
    print(f"{BLUE}🔍 MongoDB Atlas IP Whitelist Verification{NC}")
    print(f"{BLUE}{'='*60}{NC}\n")
    
    # Get current IP
    print(f"{CYAN}📡 Detecting current IP address...{NC}")
    current_ip = get_public_ip()
    
    if current_ip:
        print(f"{GREEN}✅ Current Public IP: {current_ip}{NC}")
    else:
        print(f"{YELLOW}⚠️  Could not detect public IP automatically{NC}")
        print(f"{CYAN}💡 EC2 Public IP (from config): 13.48.57.194{NC}")
        current_ip = "13.48.57.194"
    
    print(f"\n{CYAN}🔌 Testing MongoDB Atlas connection...{NC}")
    
    try:
        is_connected = MongoDBService.is_connected()
        
        if is_connected:
            print(f"{GREEN}✅ MongoDB Atlas connection successful!{NC}\n")
            print(f"{GREEN}✅ IP whitelist configured correctly{NC}")
            print(f"{CYAN}💡 Your IP ({current_ip}) is allowed to connect{NC}\n")
            
            print(f"{BLUE}📋 Next Steps:{NC}")
            print(f"  1. ✅ Connection working - IP whitelist OK")
            print(f"  2. 💡 For production, ensure EC2 IP (13.48.57.194) is whitelisted")
            print(f"  3. 💡 For development, ensure your current IP is whitelisted")
            
            return True
        else:
            print(f"{RED}❌ MongoDB Atlas connection failed!{NC}\n")
            print(f"{YELLOW}⚠️  Possible causes:{NC}")
            print(f"  1. IP not whitelisted in MongoDB Atlas")
            print(f"  2. Network connectivity issues")
            print(f"  3. Incorrect credentials")
            print(f"\n{CYAN}💡 Solution:{NC}")
            print(f"  - MongoDB Atlas Dashboard → Network Access")
            print(f"  - Add IP address: {current_ip}")
            print(f"  - Or add EC2 IP: 13.48.57.194")
            
            return False
            
    except Exception as e:
        print(f"{RED}❌ Error: {e}{NC}\n")
        print(f"{YELLOW}⚠️  Check:{NC}")
        print(f"  1. MongoDB Atlas Network Access settings")
        print(f"  2. Connection string in .env")
        print(f"  3. Network connectivity")
        
        return False

if __name__ == "__main__":
    success = verify_connection()
    sys.exit(0 if success else 1)

