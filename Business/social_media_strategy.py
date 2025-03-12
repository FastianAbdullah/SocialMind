import os
import requests
import json
from typing import Dict, Any, Optional, List

class SocialMediaStrategyGenerator:
    """
    A tool that generates social media marketing strategies using the DeepSeek R1 LLM.
    """
    
    def __init__(self, api_key: Optional[str] = None):
        """
        Initialize the strategy generator.
        
        Args:
            api_key: OpenRouter API key (if None, will check environment variable)
            site_url: Your site URL for OpenRouter rankings
            site_name: Your site name for OpenRouter rankings
        """
        self.api_key = api_key or os.environ.get("OPENROUTER_API_KEY")
      
        if not self.api_key:
            raise ValueError("API key must be provided or set as OPENROUTER_API_KEY environment variable")
    
        self.api_url = "https://openrouter.ai/api/v1/chat/completions"
    
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
        Generate a social media marketing strategy based on the provided parameters.
        
        Args:
            business_type: Type/industry of the business
            target_demographics: Description of target audience
            platform: Social media platform (e.g., Instagram, TikTok, LinkedIn)
            business_goals: What the business wants to achieve with social media
            content_preferences: Any preferences for content types (optional)
            budget: Available marketing budget (optional)
            timeframe: Timeframe for implementing the strategy (optional)
            current_challenges: Current marketing challenges (optional)
            
        Returns:
            Generated marketing strategy as a string
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
        
      
        response = self._call_api(prompt)
        
        # Extract and return the generated strategy
        return self._extract_strategy(response)
    
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
        8. Budget Allocation (if applicable)
        9. Potential Challenges and Solutions
        
        Format the strategy in a clear, organized manner with headings and bullet points where appropriate.
        """
        
        return prompt
    
    def _call_api(self, prompt: str) -> Dict[str, Any]:
        """Call the OpenRouter API with the generated prompt."""
        headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json",
         
        }
        
        data = {
            "model": "deepseek/deepseek-r1-zero:free",
            "messages": [
                {
                    "role": "user",
                    "content": prompt
                }
            ]
        }
        
        try:
            response = requests.post(
                url=self.api_url,
                headers=headers,
                data=json.dumps(data)
            )
            response.raise_for_status()  # Raise exception for HTTP errors
            return response.json()
        except requests.exceptions.RequestException as e:
            raise Exception(f"API call failed: {str(e)}")
    
    def _extract_strategy(self, response: Dict[str, Any]) -> str:
        """Extract the strategy text from the API response."""
        try:
            return response['choices'][0]['message']['content']
        except (KeyError, IndexError) as e:
            raise Exception(f"Failed to extract strategy from response: {str(e)}") 