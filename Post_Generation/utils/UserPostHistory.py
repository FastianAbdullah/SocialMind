from typing import Dict, List
from datetime import datetime

class UserPostHistory:
    def __init__(self):
        self.post_history = []

    def add_post(self, platform: str, post_data: Dict):
        """Add a new post to history"""
        post_entry = {
            'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
            'platform': platform,
            'data': post_data
        }
        self.post_history.append(post_entry)

    def get_history(self) -> List[Dict]:
        """Get all posts from history"""
        return self.post_history