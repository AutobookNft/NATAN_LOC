"""
Adaptive rate limiter for polite scraping.

Implements intelligent rate limiting that adapts to server responses.
"""

import time
import asyncio
from datetime import datetime, timedelta
from dataclasses import dataclass
import logging

logger = logging.getLogger(__name__)


@dataclass
class RateLimitConfig:
    """Configuration for rate limiting."""
    min_delay: float        # Minimum seconds between requests
    max_delay: float        # Maximum delay if server is slow
    burst_size: int         # Max requests in a burst
    burst_delay: float      # Delay after burst
    daily_limit: int = None # Optional daily request limit


class AdaptiveRateLimiter:
    """
    Rate limiter that adapts to server conditions.
    
    Features:
    - Adaptive delays based on response time and errors
    - Burst limiting
    - Daily request limits (optional)
    - Exponential backoff on errors
    """
    
    # Preset configurations for different scenarios
    PRESETS = {
        'pa_gentle': RateLimitConfig(
            min_delay=2.0,
            max_delay=10.0,
            burst_size=5,
            burst_delay=10.0,
            daily_limit=10000
        ),
        'pa_moderate': RateLimitConfig(
            min_delay=1.0,
            max_delay=5.0,
            burst_size=10,
            burst_delay=5.0,
            daily_limit=50000
        ),
        'pa_aggressive': RateLimitConfig(
            min_delay=0.5,
            max_delay=3.0,
            burst_size=20,
            burst_delay=3.0,
            daily_limit=None
        ),
        'api_endpoint': RateLimitConfig(
            min_delay=0.1,
            max_delay=1.0,
            burst_size=50,
            burst_delay=1.0,
            daily_limit=None
        ),
    }
    
    def __init__(self, config_name: str = 'pa_moderate'):
        """
        Initialize rate limiter with preset configuration.
        
        Args:
            config_name: One of 'pa_gentle', 'pa_moderate', 'pa_aggressive', 'api_endpoint'
        """
        if config_name not in self.PRESETS:
            raise ValueError(f"Unknown preset: {config_name}. Use one of {list(self.PRESETS.keys())}")
        
        self.config = self.PRESETS[config_name]
        self.current_delay = self.config.min_delay
        self.request_count = 0
        self.burst_count = 0
        self.last_request_time = None
        self.daily_count = 0
        self.daily_reset_time = datetime.now() + timedelta(days=1)
        self.error_count = 0
        
        logger.info(f"Initialized AdaptiveRateLimiter with preset: {config_name}")
        logger.info(f"  Min delay: {self.config.min_delay}s")
        logger.info(f"  Max delay: {self.config.max_delay}s")
        logger.info(f"  Burst size: {self.config.burst_size}")
    
    async def wait(self):
        """
        Wait before the next request (async version).
        
        Handles:
        - Daily limit resets
        - Burst limiting
        - Adaptive delay
        """
        # Reset daily counter if needed
        if datetime.now() > self.daily_reset_time:
            self.daily_count = 0
            self.daily_reset_time = datetime.now() + timedelta(days=1)
            logger.info("Daily request count reset")
        
        # Check daily limit
        if self.config.daily_limit and self.daily_count >= self.config.daily_limit:
            wait_seconds = (self.daily_reset_time - datetime.now()).total_seconds()
            logger.warning(
                f"Daily limit ({self.config.daily_limit}) reached! "
                f"Waiting {wait_seconds:.0f}s until reset..."
            )
            await asyncio.sleep(wait_seconds)
            self.daily_count = 0
        
        # Burst management
        if self.burst_count >= self.config.burst_size:
            logger.info(f"Burst limit reached. Cooling down for {self.config.burst_delay}s...")
            await asyncio.sleep(self.config.burst_delay)
            self.burst_count = 0
        
        # Standard adaptive wait
        if self.last_request_time:
            elapsed = time.time() - self.last_request_time
            sleep_time = max(0, self.current_delay - elapsed)
            if sleep_time > 0:
                await asyncio.sleep(sleep_time)
        
        # Update counters
        self.last_request_time = time.time()
        self.request_count += 1
        self.burst_count += 1
        self.daily_count += 1
    
    def wait_sync(self):
        """
        Synchronous version of wait() for non-async code.
        """
        # Similar logic but with time.sleep instead of asyncio.sleep
        if datetime.now() > self.daily_reset_time:
            self.daily_count = 0
            self.daily_reset_time = datetime.now() + timedelta(days=1)
        
        if self.config.daily_limit and self.daily_count >= self.config.daily_limit:
            wait_seconds = (self.daily_reset_time - datetime.now()).total_seconds()
            logger.warning(f"Daily limit reached! Waiting {wait_seconds:.0f}s...")
            time.sleep(wait_seconds)
            self.daily_count = 0
        
        if self.burst_count >= self.config.burst_size:
            logger.info(f"Burst limit reached. Cooling down for {self.config.burst_delay}s...")
            time.sleep(self.config.burst_delay)
            self.burst_count = 0
        
        if self.last_request_time:
            elapsed = time.time() - self.last_request_time
            sleep_time = max(0, self.current_delay - elapsed)
            if sleep_time > 0:
                time.sleep(sleep_time)
        
        self.last_request_time = time.time()
        self.request_count += 1
        self.burst_count += 1
        self.daily_count += 1
    
    def adjust_for_response(self, response_time: float, status_code: int):
        """
        Adjust delay based on server response.
        
        Args:
            response_time: Time taken for request (seconds)
            status_code: HTTP status code
        """
        if status_code == 429:  # Too Many Requests
            self.current_delay = min(self.current_delay * 3, self.config.max_delay)
            self.error_count += 1
            logger.warning(f"⚠️ Rate limit hit (429)! Slowing down to {self.current_delay:.2f}s")
        
        elif status_code >= 500:  # Server errors
            self.current_delay = min(self.current_delay * 2, self.config.max_delay)
            self.error_count += 1
            logger.warning(f"⚠️ Server error ({status_code})! Slowing down to {self.current_delay:.2f}s")
        
        elif response_time > 5.0:  # Slow server
            self.current_delay = min(self.current_delay * 1.5, self.config.max_delay)
            logger.info(f"🐌 Slow server response ({response_time:.2f}s). Adjusting to {self.current_delay:.2f}s")
        
        elif status_code == 200 and response_time < 1.0:  # Fast and healthy
            # Gradually reduce delay if everything is OK
            if self.error_count == 0:
                self.current_delay = max(
                    self.current_delay * 0.95,
                    self.config.min_delay
                )
    
    def get_stats(self) -> dict:
        """Get statistics about rate limiting."""
        elapsed = time.time() - self.last_request_time if self.last_request_time else 0
        
        return {
            'total_requests': self.request_count,
            'daily_requests': self.daily_count,
            'current_delay': self.current_delay,
            'error_count': self.error_count,
            'avg_requests_per_second': self.request_count / elapsed if elapsed > 0 else 0,
            'burst_count': self.burst_count,
        }
    
    def reset(self):
        """Reset all counters (useful for testing)."""
        self.request_count = 0
        self.burst_count = 0
        self.daily_count = 0
        self.error_count = 0
        self.current_delay = self.config.min_delay
        self.last_request_time = None
        logger.info("Rate limiter reset")
