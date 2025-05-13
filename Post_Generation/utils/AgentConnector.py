import os
import requests
import json
from typing import Dict, Any, List, Optional
from .AIAgent import AIAgent
from .MixtralClient import MixtralClient

class AgentConnector:
    """
    Connector class that bridges the AI Agent with platform-specific services.
    Uses the existing API endpoints in app.py for posting and content generation.
    """
    
    def __init__(self, base_url: str = "https://localhost:8443"):
        """
        Initialize the connector with necessary service managers.
        
        Args:
            base_url: Base URL for API requests (default: https://localhost:8443)
        """
        # Initialize the AI agent
        api_key = os.getenv("OPENAI_API_KEY")
        self.agent = AIAgent(api_key=api_key)
        
        # Initialize API settings
        self.base_url = base_url
        
        # Store access tokens for different platforms
        self.access_tokens = {}
        
        # Initialize Mixtral client (optional)
        self.mixtral_client = None
        
        # For SSL verification with self-signed certificates
        self.verify_ssl = False  # Set to True in production
        
        print("DEBUG: AgentConnector initialized")
    
    def initialize_platforms(self, instagram_token=None, facebook_token=None, linkedin_token=None):
        """
        Initialize platform connections with access tokens.
        
        Args:
            instagram_token: Access token for Instagram
            facebook_token: Access token for Facebook
            linkedin_token: Access token for LinkedIn
        """
        if instagram_token:
            self.access_tokens['instagram'] = instagram_token
            print("DEBUG: Instagram token set")
        
        if facebook_token:
            self.access_tokens['facebook'] = facebook_token
            print("DEBUG: Facebook token set")
            
        if linkedin_token:
            self.access_tokens['linkedin'] = linkedin_token
            print("DEBUG: LinkedIn token set")
    
    def initialize_mixtral(self) -> bool:
        """
        Initialize the Mixtral client for text processing.
        
        Returns:
            bool: True if initialization was successful
        """
        try:
            self.mixtral_client = MixtralClient()
            return True
        except Exception as e:
            print(f"Error initializing Mixtral client: {e}")
            return False
    
    def process_query(self, user_query: str) -> Dict[str, Any]:
        """
        Process a user query through the AI agent.
        
        Args:
            user_query: The user's query text
            
        Returns:
            Dict containing the agent's response and action plan
        """
        return self.agent.process_query(user_query)
    
    def generate_content(self, topic: str, platform: str = None, content_type: str = "general") -> Dict[str, Any]:
        """
        Generate optimized content using the existing API endpoint.
        
        Args:
            topic: The content topic
            platform: Target platform (optional)
            content_type: Type of content (post, story, etc.)
            
        Returns:
            Dict with generated content and metadata
        """
        # Check if we have an access token for the platform
        if platform and platform.lower() in self.access_tokens:
            access_token = self.access_tokens[platform.lower()]
            
            try:
                # Use the existing /content/generate-optimized endpoint
                headers = {"Authorization": access_token, "Content-Type": "application/json"}
                response = requests.post(
                    f"{self.base_url}/content/generate-optimized",
                    headers=headers,
                    json={"text": f"{content_type} about {topic} for {platform}"},
                    verify=self.verify_ssl
                )
                
                if response.status_code == 200:
                    data = response.json()
                    if data.get("status") == "success":
                        # Extract relevant data
                        optimized_content = data.get("optimized_content", "")
                        analysis = data.get("analysis", {})
                        
                        return {
                            "message": optimized_content,
                            "state": "content_generated",
                            "content": optimized_content,
                            "purpose": analysis.get("purpose", ""),
                            "hashtags": analysis.get("suggested_hashtags", []),
                            "actions": [
                                "Select specific platforms to post this content",
                                "Refine or edit this content",
                                "Schedule this content for posting"
                            ]
                        }
            except requests.RequestException as e:
                print(f"Error calling generate-optimized API: {e}")
        
        # Fallback to agent's content generation if API fails or no access token
        print("Using agent for content generation as fallback")
        query = f"Create a {content_type} about {topic}"
        if platform:
            query += f" for {platform}"
        
        # Process through the agent
        response = self.agent.process_query(query)
        
        # If we have Mixtral client initialized, enhance the content further
        if self.mixtral_client and response.get("message"):
            try:
                # Extract purpose and hashtags
                text_analysis = self.mixtral_client.process_text(response["message"])
                
                # Add purpose and hashtags to response
                response["purpose"] = text_analysis.get("purpose", "")
                response["hashtags"] = text_analysis.get("hashtags", [])
            except Exception as e:
                print(f"Error enhancing content with Mixtral: {e}")
        
        return response
    
    def suggest_posting_times(self, platform: str) -> List[Dict[str, Any]]:
        """
        Get optimal posting time suggestions for a specific platform.
        
        Args:
            platform: The target platform
            
        Returns:
            List of time suggestions with rationale
        """
        return self.agent.suggest_optimal_times(platform)
    
    def post_content(self, platforms, content, image_url=None, schedule_time=None):
        """
        Post content to one or more social media platforms
        
        Args:
            platforms: String or list of platform names (instagram, facebook, linkedin)
            content: The content to post
            image_url: Optional image URL to include with the post
            schedule_time: Optional time to schedule the post
            
        Returns:
            Dict with posting status and results
        """
        
        print(f"DEBUG AGENT_CONNECTOR: post_content called with platforms: {platforms}")
        print(f"DEBUG AGENT_CONNECTOR: Content: '{content[:50]}...'")
        print(f"DEBUG AGENT_CONNECTOR: Image URL: {image_url}")
        print(f"DEBUG AGENT_CONNECTOR: Schedule time: {schedule_time}")
        print(f"DEBUG AGENT_CONNECTOR: Available tokens: {list(self.access_tokens.keys())}")
        
        # Ensure platforms is a list
        if isinstance(platforms, str):
            platforms = [platforms]
        
        # If platforms list is empty, try to use a default platform
        if not platforms:
            print("DEBUG AGENT_CONNECTOR: No platforms specified, using default platform (Instagram)")
            # Check if we have any tokens available and use the first one
            if self.access_tokens:
                default_platform = list(self.access_tokens.keys())[0]
                platforms = [default_platform]
                print(f"DEBUG AGENT_CONNECTOR: Using default platform: {default_platform}")
            else:
                # If no tokens available, default to Instagram
                platforms = ["instagram"]
                print("DEBUG AGENT_CONNECTOR: No tokens available, defaulting to Instagram")
            
        # Convert platform names to lowercase
        normalized_platforms = [p.lower() for p in platforms]
        
        results = {}
        success_count = 0
        
        for platform in normalized_platforms:
            print(f"DEBUG AGENT_CONNECTOR: Processing platform: {platform}")
            
            try:
                if platform == 'instagram':
                    result = self._post_to_instagram(content, image_url)
                elif platform == 'facebook':
                    result = self._post_to_facebook(content, image_url)
                elif platform == 'linkedin':
                    result = self._post_to_linkedin(content, image_url)
                else:
                    print(f"DEBUG AGENT_CONNECTOR: Unknown platform: {platform}")
                    results[platform] = {
                        'status': 'error',
                        'message': f'Unknown platform: {platform}'
                    }
                    continue
                
                if result.get('status') == 'success':
                    success_count += 1
                
                results[platform] = result
                print(f"DEBUG AGENT_CONNECTOR: Platform {platform} result: {result}")
                
            except Exception as e:
                print(f"DEBUG AGENT_CONNECTOR: Error posting to {platform}: {str(e)}")
                import traceback
                traceback.print_exc()
                results[platform] = {
                    'status': 'error',
                    'message': str(e)
                }
        
        # Overall status
        overall_status = 'success' if success_count > 0 else 'error'
        
        response = {
            'status': overall_status,
            'message': f"Posted successfully to {success_count} of {len(normalized_platforms)} platforms",
            'results': results
        }
        
        print(f"DEBUG AGENT_CONNECTOR: Final response: {response}")
        return response
    
    def _post_to_instagram(self, content, image_url=None):
        """
        Post content to Instagram using the Instagram API endpoint
        
        Args:
            content: The caption for the Instagram post
            image_url: URL to the image to post (required for Instagram)
            
        Returns:
            Dict with status and result information
        """
        print(f"DEBUG AGENT_CONNECTOR: Attempting to post to Instagram")
        
        try:
            # Check if we have access token for Instagram
            if 'instagram' not in self.access_tokens:
                print(f"DEBUG AGENT_CONNECTOR: No Instagram access token")
                return {
                    'status': 'error',
                    'message': 'Instagram not authenticated. Please connect Instagram first.'
                }
            
            access_token = self.access_tokens['instagram']
            headers = {"Authorization": access_token, "Content-Type": "application/json"}
            
            # Get Instagram accounts
            account_response = requests.get(
                f"{self.base_url}/instagram/accounts",
                headers=headers,
                verify=self.verify_ssl
            )
            
            print(f"DEBUG AGENT_CONNECTOR: Instagram accounts response: {account_response.status_code}")
            
            if account_response.status_code != 200:
                return {
                    'status': 'error',
                    'message': f"Failed to retrieve Instagram accounts: {account_response.text}"
                }
            
            response_data = account_response.json()
            if response_data.get("status") != "success" or not response_data.get("accounts"):
                print(f"DEBUG AGENT_CONNECTOR: No Instagram accounts available: {response_data}")
                return {
                    'status': 'error',
                    'message': "No Instagram accounts available"
                }
            
            # Use the first Instagram account
            account = response_data["accounts"][0]
            ig_user_id = account.get("instagram_account_id")
            
            # Clean up content for posting
            cleaned_content = self._clean_content_for_posting(content)
            
            # For Instagram, an image is required
            if not image_url:
                # Fallback to a default test image for debugging
                image_url = "https://cdn.pixabay.com/photo/2014/06/03/19/38/board-361516_1280.jpg"
            
            print(f"DEBUG AGENT_CONNECTOR: Using image URL: {image_url}")
            
            # Call the Instagram post endpoint
            post_data = {
                "ig_user_id": ig_user_id,
                "filename": image_url,
                "caption": cleaned_content
            }
            print(f"DEBUG AGENT_CONNECTOR: Posting to Instagram with data: {post_data}")
            
            post_response = requests.post(
                f"{self.base_url}/instagram/post",
                headers=headers,
                json=post_data,
                verify=self.verify_ssl
            )
            
            print(f"DEBUG AGENT_CONNECTOR: Instagram post response: {post_response.status_code}")
            print(f"DEBUG AGENT_CONNECTOR: Response text: {post_response.text[:500]}")
            
            if post_response.status_code == 200 and post_response.json().get("status") == "success":
                return {
                    'status': 'success',
                    'message': "Content successfully posted to Instagram",
                    'post_id': post_response.json().get("post_id")
                }
            else:
                return {
                    'status': 'error',
                    'message': f"Instagram API error: {post_response.text}"
                }
        
        except Exception as e:
            print(f"DEBUG AGENT_CONNECTOR: Exception in _post_to_instagram: {str(e)}")
            import traceback
            traceback.print_exc()
            return {
                'status': 'error',
                'message': f"Error posting to Instagram: {str(e)}"
            }
    
    def _post_to_facebook(self, content, image_url=None):
        """
        Post content to Facebook using the Facebook API endpoint
        
        Args:
            content: The message for the Facebook post
            image_url: Optional URL to the image to post
            
        Returns:
            Dict with status and result information
        """
        print(f"DEBUG AGENT_CONNECTOR: Attempting to post to Facebook")
        
        try:
            # Check if we have access token for Facebook
            if 'facebook' not in self.access_tokens:
                print(f"DEBUG AGENT_CONNECTOR: No Facebook access token")
                return {
                    'status': 'error',
                    'message': 'Facebook not authenticated. Please connect Facebook first.'
                }
            
            access_token = self.access_tokens['facebook']
            headers = {"Authorization": access_token, "Content-Type": "application/json"}
            
            # Get Facebook pages
            page_response = requests.get(
                f"{self.base_url}/facebook/pages",
                headers=headers,
                verify=self.verify_ssl
            )
            
            print(f"DEBUG AGENT_CONNECTOR: Facebook pages response: {page_response.status_code}")
            
            if page_response.status_code != 200:
                return {
                    'status': 'error',
                    'message': f"Failed to retrieve Facebook pages: {page_response.text}"
                }
            
            pages = page_response.json().get("pages", [])
            if not pages:
                print(f"DEBUG AGENT_CONNECTOR: No Facebook pages available")
                return {
                    'status': 'error',
                    'message': "No Facebook pages available"
                }
            
            # Use the first page
            page = pages[0]
            page_id = page.get("id")
            page_token = page.get("access_token")
            
            # Clean up content for posting
            cleaned_content = self._clean_content_for_posting(content)
            
            # If no image_url is provided, use a default image
            if not image_url:
                image_url = "https://cdn.pixabay.com/photo/2014/06/03/19/38/board-361516_1280.jpg"
            
            print(f"DEBUG AGENT_CONNECTOR: Using image URL: {image_url}")
            
            # Call the Facebook post endpoint
            post_data = {
                "page_id": page_id,
                "page_token": page_token,
                "filename": image_url,
                "message": cleaned_content
            }
            print(f"DEBUG AGENT_CONNECTOR: Posting to Facebook with data: {post_data}")
            
            post_response = requests.post(
                f"{self.base_url}/facebook/post",
                headers=headers,
                json=post_data,
                verify=self.verify_ssl
            )
            
            print(f"DEBUG AGENT_CONNECTOR: Facebook post response: {post_response.status_code}")
            print(f"DEBUG AGENT_CONNECTOR: Response text: {post_response.text[:500]}")
            
            if post_response.status_code == 200 and post_response.json().get("success"):
                return {
                    'status': 'success',
                    'message': "Content successfully posted to Facebook",
                    'post_id': post_response.json().get("post_id")
                }
            else:
                return {
                    'status': 'error',
                    'message': f"Facebook API error: {post_response.text}"
                }
        
        except Exception as e:
            print(f"DEBUG AGENT_CONNECTOR: Exception in _post_to_facebook: {str(e)}")
            import traceback
            traceback.print_exc()
            return {
                'status': 'error',
                'message': f"Error posting to Facebook: {str(e)}"
            }
    
    def _post_to_linkedin(self, content, image_url=None):
        """
        Post content to LinkedIn using the LinkedIn API endpoint
        
        Args:
            content: The content for the LinkedIn post
            image_url: Optional URL to the image to post
            
        Returns:
            Dict with status and result information
        """
        print(f"DEBUG AGENT_CONNECTOR: Attempting to post to LinkedIn")
        
        try:
            # Check if we have access token for LinkedIn
            if 'linkedin' not in self.access_tokens:
                print(f"DEBUG AGENT_CONNECTOR: No LinkedIn access token")
                return {
                    'status': 'error',
                    'message': 'LinkedIn not authenticated. Please connect LinkedIn first.'
                }
            
            access_token = self.access_tokens['linkedin']
            headers = {"Authorization": access_token, "Content-Type": "application/json"}
            
            # Prepare the post data
            post_data = {
                "content": content
            }
            
            if image_url:
                post_data["media_url"] = image_url
            
            print(f"DEBUG AGENT_CONNECTOR: Posting to LinkedIn with data: {post_data}")
            
            # Call the LinkedIn post endpoint
            post_response = requests.post(
                f"{self.base_url}/linkedin/post",
                headers=headers,
                json=post_data,
                verify=self.verify_ssl
            )
            
            print(f"DEBUG AGENT_CONNECTOR: LinkedIn post response: {post_response.status_code}")
            print(f"DEBUG AGENT_CONNECTOR: Response text: {post_response.text[:500]}")
            
            if post_response.status_code == 200 and post_response.json().get("status") == "success":
                return {
                    'status': 'success',
                    'message': "Content successfully posted to LinkedIn",
                    'post_id': post_response.json().get("post_id")
                }
            else:
                return {
                    'status': 'error',
                    'message': f"LinkedIn API error: {post_response.text}"
                }
        
        except Exception as e:
            print(f"DEBUG AGENT_CONNECTOR: Exception in _post_to_linkedin: {str(e)}")
            return {
                'status': 'error',
                'message': f"Error posting to LinkedIn: {str(e)}"
            }
    
    def _clean_content_for_posting(self, content):
        """
        Clean up content by removing section headers and formatting tags
        
        Args:
            content: The raw content with potential section headers
            
        Returns:
            Cleaned content suitable for posting
        """
        import re
        
        # Remove section headers like "### Catchy Headline:" or "## Main Content:"
        content = re.sub(r'#{1,6}\s+[^#\n]+:', '', content)
        
        # Remove any remaining markdown formatting
        content = re.sub(r'#{1,6}\s+', '', content)
        
        # Remove any "---" horizontal rules
        content = re.sub(r'\n---+\n', '\n', content)
        
        # Remove quoted sections with quotes
        content = re.sub(r'\"(.+?)\"', r'\1', content)
        
        # Remove any instructions or prompts at the end
        content = re.sub(r'\n\nWould you like to.*$', '', content, flags=re.DOTALL)
        
        # Clean up any extra newlines
        content = re.sub(r'\n{3,}', '\n\n', content)
        
        # Remove section labels like "Relevant Hashtags:" but keep the hashtags
        content = re.sub(r'(?:Relevant |)Hashtags:\s*', '', content)
        content = re.sub(r'Call to Action:\s*', '', content)
        content = re.sub(r'Main Content Body:\s*', '', content)
        content = re.sub(r'Catchy Headline:\s*', '', content)
        
        # Trim whitespace
        content = content.strip()
        
        return content