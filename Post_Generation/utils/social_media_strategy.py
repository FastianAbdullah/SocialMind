import os
from groq import Groq
from typing import Dict, Any, Optional

class SocialMediaStrategyGenerator:
    """
    A tool that generates social media marketing strategies using the DeepSeek R1 model via Groq.
    """
    
    def __init__(self, api_key: Optional[str] = None):
        """
        Initialize the strategy generator with Groq client.
        
        Args:
            api_key: Groq API key (if None, will check environment variable)
        """
        self.api_key = api_key
      
        if not self.api_key:
            raise ValueError("API key must be provided or set as GROQ_API_KEY environment variable")
        
        self.client = Groq(api_key=self.api_key)
      
    
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
        Generate a social media marketing strategy using Groq's DeepSeek model.
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
            # Call Groq API with streaming
            completion = self.client.chat.completions.create(
                model="deepseek-r1-distill-qwen-32b",
                messages=[{
                    "role": "user",
                    "content": prompt
                }],
                temperature=0.6,
                max_tokens=4096,
                top_p=0.95,
                stream=True
            )
            
            # Collect streamed response
            strategy_parts = []
            for chunk in completion:
                if chunk.choices[0].delta.content:
                    strategy_parts.append(chunk.choices[0].delta.content)
            
            # Combine all parts into final strategy
            return ''.join(strategy_parts)
            
        except Exception as e:
            raise Exception(f"Strategy generation failed: {str(e)}")
    
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