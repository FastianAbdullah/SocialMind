from typing import List,Dict,Optional,Tuple
from collections import Counter
import requests
import re
import streamlit as st

#Class To handle Instagram Operations.
class InstagramManager:
    def __init__(self, access_token: str):
        self.access_token = access_token

    def get_accounts(self) -> List[Dict]:
        """Get list of Instagram business accounts"""
        # Remove 'Bearer ' prefix if present
        token = self.access_token.replace('Bearer ', '').strip()
        
        # First get Facebook Pages
        pages_url = "https://graph.facebook.com/v20.0/me/accounts"
        params = {
            "access_token": token,
            "fields": "name,id,access_token,instagram_business_account{id,name,username}"
        }
        
        print(pages_url)
        print(params)   
        
        try:
            # Test token first
            test_url = "https://graph.facebook.com/v20.0/me"
            test_params = {"access_token": token}
            test_response = requests.get(test_url, params=test_params)
            test_data = test_response.json()
            
            print(f"Token test response: {test_data}")  # Debug print
            
            # Get Facebook Pages
            pages_response = requests.get(pages_url, params=params)
            pages_data = pages_response.json()
            
            self.debug_response = {
                'token_test': test_data,
                'status_code': pages_response.status_code,
                'raw_response': pages_data,
                'url': pages_response.url,
                'used_token': token[:10] + '...'  # Show first 10 chars of token
            }
            
            if 'error' in pages_data:
                raise Exception(pages_data['error'].get('message', 'Unknown error'))
            
            if 'data' not in pages_data:
                raise Exception(f"Unexpected response format: {pages_data}")
            
            accounts = []
            for page in pages_data.get('data', []):
                print(f"Processing page: {page}")  # Debug print
                if 'instagram_business_account' in page:
                    ig_account = page['instagram_business_account']
                    accounts.append({
                        'page_id': page['id'],
                        'page_name': page['name'],
                        'page_access_token': page['access_token'],
                        'instagram_account_id': ig_account['id'],
                        'instagram_username': ig_account.get('username', 'Unknown'),
                        'instagram_name': ig_account.get('name', 'Unknown')
                    })
            
            return accounts
            
        except requests.exceptions.RequestException as e:
            error_message = str(e)
            if hasattr(e, 'response') and e.response is not None:
                try:
                    error_data = e.response.json()
                    error_message = error_data.get('error', {}).get('message', str(e))
                    print(f"Error data: {error_data}")  # Debug print
                except:
                    pass
            raise Exception(f"Error getting Instagram accounts: {error_message}")

    def post_content(self, ig_user_id: str, image_url: str, caption: str) -> Optional[Dict]:
        """Post image with caption to Instagram business account"""
        media_url = f'https://graph.facebook.com/v20.0/{ig_user_id}/media'
        media_params = {
            'image_url': image_url,
            'caption': caption,
            'access_token': self.access_token
        }
        try:
            response = requests.post(media_url, params=media_params)
            response.raise_for_status()
            result = response.json()
         
            
            if 'id' in result:
                creation_id = result['id']
                publish_url = f'https://graph.facebook.com/v20.0/{ig_user_id}/media_publish'
                publish_params = {
                    'creation_id': creation_id,
                    'access_token': self.access_token
                }
                publish_response = requests.post(publish_url, params=publish_params)
                publish_response.raise_for_status()
                return publish_response.json()
            return result
        except Exception as e:
            st.error(f"Error posting to Instagram: {str(e)}")
            return None

    def get_trending_hashtags(self, ig_user_id: str, seed_hashtag: str) -> List[Tuple[str, int]]:
        """Get trending hashtags based on a seed hashtag"""
        hashtag_search_url = "https://graph.facebook.com/v20.0/ig_hashtag_search"
        params = {
            "user_id": ig_user_id,
            "q": seed_hashtag.strip('#'),
            "access_token": self.access_token
        }
        
        try:
            response = requests.get(hashtag_search_url, params=params)
            response.raise_for_status()
            hashtag_data = response.json()
            
            if 'data' in hashtag_data and hashtag_data['data']:
                hashtag_id = hashtag_data['data'][0]['id']
                
                media_url = f"https://graph.facebook.com/v20.0/{hashtag_id}/recent_media"
                media_params = {
                    "user_id": ig_user_id,
                    "fields": "caption,like_count,comments_count",
                    "access_token": self.access_token,
                    "limit": 50
                }
                
                media_response = requests.get(media_url, params=media_params)
                media_response.raise_for_status()
                media_data = media_response.json()
                
                all_hashtags = []
                if 'data' in media_data:
                    for post in media_data['data']:
                        if 'caption' in post:
                            hashtags = re.findall(r'#\w+', post['caption'])
                            weight = 1 + (post.get('like_count', 0) + post.get('comments_count', 0)) / 100
                            all_hashtags.extend([hashtag for hashtag in hashtags] * int(weight))
                
                hashtag_counts = Counter(all_hashtags)
                if f"#{seed_hashtag.strip('#')}" in hashtag_counts:
                    del hashtag_counts[f"#{seed_hashtag.strip('#')}"]
                
                return hashtag_counts.most_common(15)
                
        except Exception as e:
            st.error(f"Error fetching trending hashtags: {str(e)}")
            return []
