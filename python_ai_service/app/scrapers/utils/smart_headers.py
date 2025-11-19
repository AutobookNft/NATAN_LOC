"""
Smart headers generation for realistic HTTP requests.

Provides realistic, rotating User-Agents and headers to avoid detection.
"""

import random
from urllib.parse import urlparse
from typing import Dict
import json
from pathlib import Path


class SmartHeaders:
    """Generate realistic headers for HTTP requests."""
    
    # Realistic User-Agent strings (desktop browsers)
    USER_AGENTS = [
        # Chrome on Linux
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        # Chrome on Windows
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        # Chrome on macOS
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        # Firefox on Linux
        'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
        # Firefox on Windows
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        # Firefox on macOS
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:121.0) Gecko/20100101 Firefox/121.0',
    ]
    
    @staticmethod
    def get_realistic_headers(url: str, referer: bool = True) -> Dict[str, str]:
        """
        Generate realistic headers for a URL.
        
        Args:
            url: Target URL
            referer: If True, add Referer header (simulates internal navigation)
            
        Returns:
            Dictionary of HTTP headers
        """
        domain = urlparse(url).netloc
        
        headers = {
            'User-Agent': random.choice(SmartHeaders.USER_AGENTS),
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language': 'it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding': 'gzip, deflate, br',
            'DNT': '1',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
            'Sec-Fetch-Dest': 'document',
            'Sec-Fetch-Mode': 'navigate',
            'Sec-Fetch-Site': 'none',
            'Sec-Fetch-User': '?1',
            'Cache-Control': 'max-age=0',
        }
        
        # Add referer to simulate internal navigation
        if referer:
            headers['Referer'] = f'https://{domain}/'
        
        return headers
    
    @staticmethod
    def get_natan_headers() -> Dict[str, str]:
        """
        Get headers that identify as NatanBot (for transparency).
        
        Use this when you want to be clearly identified as a bot.
        Better for ethical scraping of PA sites.
        """
        return {
            'User-Agent': 'NatanBot/1.0 (Scraper Albi Pretori PA; +https://natan.it/bot; contact@natan.it)',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language': 'it-IT,it;q=0.9',
            'Accept-Encoding': 'gzip, deflate',
            'Connection': 'keep-alive',
        }


class SessionManager:
    """
    Manage persistent sessions with cookie storage.
    
    Useful for sites that require session cookies.
    """
    
    def __init__(self, cookies_file: str = '.scraper_cookies.json'):
        """
        Initialize session manager.
        
        Args:
            cookies_file: Path to store cookies persistently
        """
        self.cookies_file = Path(cookies_file)
        self.cookies = {}
        self.load_cookies()
    
    def load_cookies(self):
        """Load cookies from file."""
        if self.cookies_file.exists():
            try:
                with open(self.cookies_file, 'r') as f:
                    self.cookies = json.load(f)
            except Exception:
                self.cookies = {}
    
    def save_cookies(self):
        """Save cookies to file."""
        try:
            with open(self.cookies_file, 'w') as f:
                json.dump(self.cookies, f, indent=2)
        except Exception:
            pass
    
    def get_cookies(self, domain: str = None) -> Dict[str, str]:
        """
        Get cookies for a domain.
        
        Args:
            domain: Optional domain filter
            
        Returns:
            Dictionary of cookies
        """
        if domain:
            return {k: v for k, v in self.cookies.items() if domain in k}
        return self.cookies
    
    def set_cookies(self, cookies: Dict[str, str], domain: str = None):
        """
        Set cookies for a domain.
        
        Args:
            cookies: Dictionary of cookies to set
            domain: Optional domain prefix
        """
        if domain:
            for key, value in cookies.items():
                self.cookies[f"{domain}_{key}"] = value
        else:
            self.cookies.update(cookies)
        
        self.save_cookies()
    
    def clear_cookies(self):
        """Clear all cookies."""
        self.cookies = {}
        self.save_cookies()
