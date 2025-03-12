import requests
from collections import Counter
import re
import streamlit as st
from typing import List, Dict

#Class to filter Top Posts From Instagram SocialMedia Handles.
class InstagramContentAnalyzer:
    def __init__(self, access_token: str):
        self.access_token = access_token.replace('Bearer ', '').strip()

    def get_top_posts(self, ig_user_id: str, hashtag: str) -> List[Dict]:
        """Get top posts for a hashtag"""
        # First, search for the hashtag to get its ID
        hashtag_search_url = "https://graph.facebook.com/v20.0/ig_hashtag_search"
        params = {
            "user_id": ig_user_id,
            "q": hashtag.strip('#'),
            "access_token": self.access_token
        }
        
        try:
            # Get hashtag ID
            response = requests.get(hashtag_search_url, params=params)
            response.raise_for_status()
            hashtag_data = response.json()
            
            if 'data' in hashtag_data and hashtag_data['data']:
                hashtag_id = hashtag_data['data'][0]['id']
                
                # Get top media for this hashtag
                media_url = f"https://graph.facebook.com/v20.0/{hashtag_id}/top_media"
                media_params = {
                    "user_id": ig_user_id,
                    "fields": "caption,like_count,comments_count,permalink,timestamp,id",
                    "access_token": self.access_token,
                    "limit": 25  # Get top 25 posts
                }
                
                media_response = requests.get(media_url, params=media_params)
                media_response.raise_for_status()
                media_data = media_response.json()
                
                if 'data' in media_data:
                    return [{
                        'id': post.get('id', ''),
                        'caption': post.get('caption', ''),
                        'likes': post.get('like_count', 0),
                        'comments': post.get('comments_count', 0),
                        'engagement_score': post.get('like_count', 0) + post.get('comments_count', 0),
                        'permalink': post.get('permalink', ''),
                        'timestamp': post.get('timestamp', '')
                    } for post in media_data['data']]
            
            return []
            
        except Exception as e:
            print(f"Error fetching top posts: {str(e)}")
            return []

    def get_top_performing_posts(self, posts: List[Dict], num_posts: int = 2) -> List[Dict]:
        """Get top performing posts based on engagement"""
        if not posts:
            return []
            
        # Calculate engagement score for each post
        for post in posts:
            post['engagement_score'] = (
                post.get('like_count', 0) + 
                (post.get('comments_count', 0) * 2)  # Comments weighted more heavily
            )
        
        # Sort posts by engagement score
        sorted_posts = sorted(
            posts, 
            key=lambda x: x.get('engagement_score', 0), 
            reverse=True
        )
        
        # Return top posts with their captions cleaned
        top_posts = []
        for post in sorted_posts[:num_posts]:
            if post.get('caption'):
                top_posts.append({
                    'caption': post['caption'],
                    'likes': post.get('like_count', 0),
                    'comments': post.get('comments_count', 0),
                    'engagement_score': post['engagement_score'],
                    'permalink': post.get('permalink', '')
                })
        
        return top_posts

    def analyze_descriptions(self, posts: List[Dict]) -> Dict:
        """Analyze post descriptions for patterns"""
        if not posts:
            return {
                'top_performing_posts': [],
                'common_phrases': [],
                'emoji_usage': {},
                'structure_patterns': {},
                'avg_length': 0
            }

        # Sort posts by engagement score
        sorted_posts = sorted(posts, key=lambda x: x.get('engagement_score', 0), reverse=True)
        top_posts = sorted_posts[:10]  # Get top 10 posts

        # Extract captions
        captions = [post.get('caption', '') for post in posts if post.get('caption')]

        return {
            'top_performing_posts': top_posts,
            'common_phrases': self._extract_common_phrases(captions),
            'emoji_usage': self._analyze_emoji_usage(captions),
            'structure_patterns': self._analyze_structure_patterns(captions),
            'avg_length': self._calculate_avg_length(captions)
        }

    def _extract_common_phrases(self, captions: List[str]) -> List[str]:
        """Extract common phrases from captions"""
        words = Counter()
        for caption in captions:
            # Split by spaces and remove special characters
            clean_words = re.sub(r'[^\w\s]', '', caption.lower()).split()
            words.update(clean_words)
        return [word for word, count in words.most_common(10) if len(word) > 3]

    def _analyze_emoji_usage(self, captions: List[str]) -> Dict:
        """Analyze emoji usage in captions"""
        emoji_pattern = re.compile(r'[\U0001F300-\U0001F9FF]')
        emoji_counter = Counter()
        for caption in captions:
            emojis = emoji_pattern.findall(caption)
            emoji_counter.update(emojis)
        return dict(emoji_counter.most_common(10))

    def _analyze_structure_patterns(self, captions: List[str]) -> Dict:
        """Analyze structural patterns in captions"""
        total = len(captions)
        if total == 0:
            return {}

        patterns = {
            'has_emoji': sum(1 for c in captions if re.search(r'[\U0001F300-\U0001F9FF]', c)),
            'has_hashtags': sum(1 for c in captions if '#' in c),
            'has_mentions': sum(1 for c in captions if '@' in c),
            'has_call_to_action': sum(1 for c in captions if any(cta in c.lower() 
                for cta in ['click', 'tap', 'check', 'follow', 'like', 'share']))
        }

        return {k: int((v/total) * 100) for k, v in patterns.items()}

    def _calculate_avg_length(self, captions: List[str]) -> int:
        """Calculate average caption length"""
        if not captions:
            return 0
        return int(sum(len(caption) for caption in captions) / len(captions))

    def generate_description_template(self, analysis: Dict) -> str:
        """Generate a template based on the analysis"""
        patterns = analysis.get('structure_patterns', {})
        emoji_usage = analysis.get('emoji_usage', {})
        common_phrases = analysis.get('common_phrases', [])

        template_parts = []
        
        # Add emoji suggestion if commonly used
        if patterns.get('has_emoji', 0) > 50 and emoji_usage:
            template_parts.append(f"Consider using popular emojis: {' '.join(list(emoji_usage.keys())[:3])}")
        
        # Add structure suggestions
        if patterns.get('has_call_to_action', 0) > 50:
            template_parts.append("Include a call-to-action")
        
        if patterns.get('has_hashtags', 0) > 50:
            template_parts.append("Add relevant hashtags")
        
        if patterns.get('has_mentions', 0) > 30:
            template_parts.append("Consider mentioning relevant accounts")
        
        # Add common phrases if available
        if common_phrases:
            template_parts.append(f"Popular phrases: {', '.join(common_phrases[:3])}")

        return "\n".join(template_parts) if template_parts else "No specific patterns found"

    def get_top_posts_for_hashtags(self, ig_user_id: str, hashtags: List[str]) -> List[Dict]:
        """
        Get top posts for multiple hashtags and combine results
        """
        all_posts = []
        for hashtag in hashtags:
            posts = self.get_top_posts(ig_user_id, hashtag.strip('#'))
            all_posts.extend(posts)
        
        # Sort by engagement and remove duplicates
        seen_ids = set()
        unique_posts = []
        for post in sorted(all_posts, key=lambda x: x['engagement_score'], reverse=True):
            if post['id'] not in seen_ids:
                seen_ids.add(post['id'])
                unique_posts.append(post)
        
        # Return top 25 unique posts
        return unique_posts[:25]

    def  get_optimized_examples(self, posts: List[Dict], num_examples: int = 4) -> List[Dict]:
        """
        Get optimized set of example posts ensuring diversity
        """
        if not posts:
            return []
        
        # Sort by engagement score
        sorted_posts = sorted(posts, key=lambda x: x.get('engagement_score', 0), reverse=True)
        
        # Get top performing posts with different styles
        selected_posts = []
        seen_patterns = set()
        
        for post in sorted_posts:
            caption = post.get('caption', '')
            
            # Create a simple pattern signature
            pattern = (
                bool(re.search(r'[\U0001F300-\U0001F9FF]', caption)),  # has emoji
                bool(re.search(r'(check|click|tap|follow|share)', caption.lower())),  # has CTA
                len(caption) > 300,  # is long form
                bool(re.findall(r'#\w+', caption))  # has hashtags
            )
            
            if pattern not in seen_patterns and len(selected_posts) < num_examples:
                seen_patterns.add(pattern)
                selected_posts.append({
                    'caption': caption,
                    'likes': post.get('likes', 0),
                    'comments': post.get('comments', 0),
                    'engagement_score': post.get('engagement_score', 0),
                    'permalink': post.get('permalink', '')
                })
        
        return selected_posts
