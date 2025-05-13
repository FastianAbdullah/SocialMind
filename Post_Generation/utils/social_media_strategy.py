import os
import requests
import json
from typing import Dict, Any, Optional
from requests.adapters import HTTPAdapter
from requests.packages.urllib3.util.retry import Retry

class SocialMediaStrategyGenerator:
    """
    A tool that generates social media marketing strategies using OpenRouter API.
    """
    
    def __init__(self, api_key: Optional[str] = None):
        """
        Initialize the strategy generator with OpenRouter API key.
        
        Args:
            api_key: OpenRouter API key (if None, will check environment variable)
        """
        self.api_key = api_key or os.environ.get("OPENROUTER_API_KEY")
      
        if not self.api_key:
            raise ValueError("API key must be provided or set as OPENROUTER_API_KEY environment variable")
        
        self.api_url = "https://openrouter.ai/api/v1/chat/completions"
        
        # Setup session with retries and timeouts
        self.session = requests.Session()
        
        # Retry strategy
        retries = Retry(
            total=3,  # number of retries
            backoff_factor=1,  # wait 1, 2, 4 seconds between retries
            status_forcelist=[408, 429, 500, 502, 503, 504]  # retry on these status codes
        )
        
        # Mount the adapter with retry strategy
        self.session.mount('https://', HTTPAdapter(max_retries=retries))
      
    def generate_strategy(self, 
                         business_type: str,
                         target_demographics: str,
                         platform: str,
                         business_goals: str,
                         content_preferences: Optional[str] = None,
                         budget: Optional[str] = None,
                         timeframe: Optional[str] = None,
                         current_challenges: Optional[str] = None) -> str:
        """
        Generate a social media marketing strategy using DeepSeek R1 (free) model via OpenRouter.
        """
        # Create prompt from parameters
        prompt = self._create_prompt(
            business_type=business_type,
            target_demographics=target_demographics,
            platform=platform,
            business_goals=business_goals,
            content_preferences=content_preferences,
            budget=budget,
            timeframe=timeframe,
            current_challenges=current_challenges
        )
        
        try:
            # Call OpenRouter API with increased timeouts
            response = self.session.post(
                url=self.api_url,
                headers={
                    "Authorization": f"Bearer {self.api_key}",
                    "HTTP-Referer": "https://socialmind.com",
                    "X-Title": "SocialMind",
                    "Content-Type": "application/json"
                },
                data=json.dumps({
                    "model": "deepseek/deepseek-r1:free",
                    "messages": [{
                        "role": "user",
                        "content": prompt
                    }],
                    "temperature": 0.6,
                    "max_tokens": 4096,
                    "top_p": 0.95
                }),
                timeout=(30, 300)  # (connect timeout, read timeout) in seconds
            )
            
            # Check if request was successful
            response.raise_for_status()
            
            # Parse the response
            result = response.json()
            
            # Extract the generated content
            if result and 'choices' in result and len(result['choices']) > 0:
                return result['choices'][0]['message']['content']
            else:
                raise Exception("No content in response")
            
        except requests.Timeout as e:
            raise Exception(f"Request timed out. The operation took too long to complete: {str(e)}")
        except requests.ConnectionError as e:
            raise Exception(f"Connection error occurred: {str(e)}")
        except requests.RequestException as e:
            raise Exception(f"An error occurred during the request: {str(e)}")
        except Exception as e:
            raise Exception(f"Strategy generation failed: {str(e)}")
            
    def __del__(self):
        """Cleanup method to close the session"""
        if hasattr(self, 'session'):
            self.session.close()
    
    def _create_prompt(self, **kwargs) -> str:
        """Create an effective prompt for the LLM based on input parameters."""
        # Build a detailed prompt with all provided parameters
        prompt = f"""
        Create a comprehensive social media marketing strategy for the following:
        
        BUSINESS TYPE: {kwargs['business_type']}
        TARGET AUDIENCE: {kwargs['target_demographics']}
        PLATFORM: {kwargs['platform']}
        BUSINESS GOALS: {kwargs['business_goals']}
        """
        
        # Add optional parameters if provided
        if kwargs.get('content_preferences'):
            prompt += f"\nCONTENT PREFERENCES: {kwargs['content_preferences']}"
        
        if kwargs.get('budget'):
            prompt += f"\nBUDGET: {kwargs['budget']}"
            
        if kwargs.get('timeframe'):
            prompt += f"\nTIMEFRAME: {kwargs['timeframe']}"
            
        if kwargs.get('current_challenges'):
            prompt += f"\nCURRENT CHALLENGES: {kwargs['current_challenges']}"
        
        # Add specific instructions for the strategy format
        prompt += """
        
        Please provide a detailed social media marketing strategy that includes:
        
        1. Executive Summary
        2. Platform-Specific Strategy
        3. Content Strategy (types, topics, frequency)
        4. Engagement Tactics
        5. Growth Strategy
        6. Metrics to Track
        7. Implementation Timeline
        8. Budget Allocation `(if applicable`)
        9. Potential Challenges and Solutions
        
        Format the strategy in a clear, organized manner with headings and bullet points where appropriate.
        """
        
        return prompt 