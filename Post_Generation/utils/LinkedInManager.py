from typing import List, Dict, Optional
import requests

class LinkedInManager:
    def __init__(self, access_token: str):
        # Remove any 'Bearer ' prefix if it exists
        self.access_token = access_token.replace('Bearer ', '')
        self.base_url = "https://api.linkedin.com/v2"
        self.headers = {
            "Authorization": f"Bearer {self.access_token}",
            "Content-Type": "application/json",
            "X-Restli-Protocol-Version": "2.0.0"
        }

    def get_user_profile(self) -> Dict:
        """Get user profile information using OpenID Connect userinfo endpoint"""
        try:
            response = requests.get(
                "https://api.linkedin.com/v2/userinfo",  # OpenID Connect endpoint
                headers=self.headers
            )
            response.raise_for_status()
            profile_data = response.json()
            # Extract the sub claim which contains the LinkedIn member ID
            return {
                'id': profile_data.get('sub'),  # This is the LinkedIn member ID
                'name': profile_data.get('name'),
                'email': profile_data.get('email'),
                'picture': profile_data.get('picture')
            }
        except requests.exceptions.RequestException as e:
            print(f"LinkedIn API Error: {str(e)}")
            if hasattr(e, 'response') and hasattr(e.response, 'json'):
                print(f"Response: {e.response.json()}")
            raise Exception(f"Failed to get user profile: {str(e)}")

    def post_content(self, content: str, media_file: Optional[bytes] = None, 
                    media_type: Optional[str] = None, company_id: Optional[str] = None) -> Dict:
        """Post content to LinkedIn profile or company page"""
        try:
            # Get user URN first
            user_profile = self.get_user_profile()
            user_id = user_profile.get('id')
            if not user_id:
                raise Exception("Could not get user ID from profile")

            author = f"urn:li:organization:{company_id}" if company_id else f"urn:li:person:{user_id}"
            
            post_data = {
                "author": author,
                "lifecycleState": "PUBLISHED",
                "specificContent": {
                    "com.linkedin.ugc.ShareContent": {
                        "shareCommentary": {"text": content},
                        "shareMediaCategory": "NONE"
                    }
                },
                "visibility": {"com.linkedin.ugc.MemberNetworkVisibility": "PUBLIC"}
            }

            if media_file and media_type:
                upload_reg = self._register_upload(user_id, media_type)
                upload_url = upload_reg["value"]["uploadMechanism"]["com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"]["uploadUrl"]
                asset = upload_reg["value"]["asset"]

                if self._upload_media(upload_url, media_file):
                    post_data["specificContent"]["com.linkedin.ugc.ShareContent"].update({
                        "shareMediaCategory": media_type.upper(),
                        "media": [{
                            "status": "READY",
                            "media": asset,
                            "title": {"text": "Media Upload"},
                        }]
                    })

            response = requests.post(
                f"{self.base_url}/ugcPosts",
                json=post_data,
                headers=self.headers
            )
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            print(f"LinkedIn API Error: {str(e)}")
            if hasattr(e, 'response') and hasattr(e.response, 'json'):
                print(f"Response: {e.response.json()}")
            raise Exception(f"Failed to post content: {str(e)}")

    def _register_upload(self, owner_id: str, media_type: str = "image") -> Dict:
        """Register media upload with LinkedIn"""
        recipe = "feedshare-image" if media_type == "image" else "feedshare-video"
        
        data = {
            "registerUploadRequest": {
                "recipes": [f"urn:li:digitalmediaRecipe:{recipe}"],
                "owner": f"urn:li:person:{owner_id}",
                "serviceRelationships": [{
                    "relationshipType": "OWNER",
                    "identifier": "urn:li:userGeneratedContent"
                }]
            }
        }
        
        response = requests.post(
            f"{self.base_url}/assets?action=registerUpload",
            headers=self.headers,
            json=data
        )
        response.raise_for_status()
        return response.json()

    def _upload_media(self, upload_url: str, media_file: bytes) -> bool:
        """Upload media binary to LinkedIn"""
        response = requests.post(
            upload_url,
            headers={"Authorization": f"Bearer {self.access_token}"},
            data=media_file
        )
        return response.status_code == 201

    # Since we don't have organization_social scope, removing get_companies method
