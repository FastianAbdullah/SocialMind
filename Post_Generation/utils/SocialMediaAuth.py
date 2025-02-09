import streamlit as st
import requests
from typing import Optional
from flask import jsonify

#Class to Get Permission for Posting on Behalf of User.
class SocialMediaAuth:
    def __init__(self,
                 app_id: str, # App ID for the  Facebook / Instagram Social Media Platform.
                 app_secret: str, # App Secret for the Facebook / Instagram Social Media Platform.
                 app_id_linkedin: str, # App ID for the LinkedIn Social Media Platform.
                 app_secret_linkedin: str, # App Secret for the LinkedIn Social Media Platform.
                 redirect_uri: str): # Redirect URI for the Facebook / Instagram / LinkedIn Social Media Platform.
        
        self.app_id = app_id
        self.app_secret = app_secret
        self.app_id_linkedin = app_id_linkedin
        self.app_secret_linkedin = app_secret_linkedin

        self.app_secret = app_secret
        self.redirect_uri = redirect_uri

        self.platform = None

    def generate_auth_url(self, platform: str) -> str:
        """Generate authentication URL for social media platforms"""
        self.platform = platform
        if platform == 'linkedin':
            params = {
                "response_type": "code",
                "client_id": self.app_id_linkedin,
                "redirect_uri": self.redirect_uri,
                "scope": "openid profile w_member_social email"
            }
            base_url = "https://www.linkedin.com/oauth/v2/authorization"
            return f"{base_url}?{'&'.join(f'{k}={v}' for k, v in params.items())}"
        else:
            permissions = {
                'facebook': "pages_read_engagement,pages_manage_posts,instagram_basic,instagram_content_publish",
                'instagram': "instagram_basic,instagram_content_publish"
            }
            return f"https://www.facebook.com/v20.0/dialog/oauth?client_id={self.app_id}&redirect_uri={self.redirect_uri}&scope={permissions[platform]}"

    def get_access_token(self, code: str) -> Optional[dict]:
        """Exchange authorization code for access token"""
        try:
            if self.platform == 'linkedin':

                token_url = "https://www.linkedin.com/oauth/v2/accessToken"
                data = {
                    "grant_type": "authorization_code",
                    "code": code,
                    "redirect_uri": self.redirect_uri,
                    "client_id": self.app_id_linkedin,
                    "client_secret": self.app_secret_linkedin
                }
                response = requests.post(token_url, data=data)

            else:
                token_url = "https://graph.facebook.com/v20.0/oauth/access_token"
                params = {
                    "client_id": self.app_id,
                    "redirect_uri": self.redirect_uri,
                    "client_secret": self.app_secret,
                    "code": code
                }
                response = requests.get(token_url, params=params)

            response.raise_for_status()
            access_token = response.json().get('access_token')
            
            if access_token:
                return {
                    'access_token': access_token,
                    'redirect_url': f'http://127.0.0.1:8000/{self.platform}/callback?token={access_token}'
                }
            

            return None
            
        except Exception as e:
            print(f"Error getting access token: {str(e)}")
            return None
