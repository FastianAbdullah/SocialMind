import os
from typing import List, Dict, Optional
from huggingface_hub import InferenceClient
from dotenv import load_dotenv


class MixtralClient:
  
    def __init__(self, model: str = "mistralai/Mixtral-8x7B-Instruct-v0.1", token: Optional[str] = None):
        load_dotenv()
        
        self.token = token or os.getenv("API_TOKEN")
       
       
        
        if not self.token:
            raise ValueError(
                "No Hugging Face token found. "
                "Please set HUGGINGFACE_TOKEN in .env or pass token directly."
            )
        
        # Initialize inference client
        self.client = InferenceClient(model=model, token=self.token)
    
    def generate_completion(self, messages: List[Dict[str, str]], max_tokens: int = 1000,temperature: float = 0.7) -> str:

        try:
            completion = self.client.chat.completions.create(
                model=self.client.model,
                messages=messages,
                max_tokens=max_tokens,
                temperature=temperature
            )
            return completion.choices[0].message.content.strip()
        
        except Exception as e:
            print(f"Error generating completion: {e}")
            return ""
    
    def extract_purpose_and_hashtags(
        self, 
        response: str
    ) -> Dict[str, List[str]]:
        """
        Extract purpose and hashtags from a structured response.
        
        Args:
            response (str): Model's response text
        
        Returns:
            Dict containing purpose and cleaned hashtags
        """
        try:
            # Split and extract purpose
            purpose = response.split('Purpose: ')[1].split('\nHashtags:')[0].strip()
            
            # Extract and clean hashtags
            hashtag_section = response.split('Hashtags: ')[1].strip().split('#')
            hashtags = [
                tag.strip().lower().translate(str.maketrans('', '', ',.!?')) 
                for tag in hashtag_section if tag.strip()
            ]
            
            return {
                "purpose": purpose,
                "hashtags": hashtags
            }
        
        except (IndexError, ValueError) as e:
            print(f"Error extracting purpose and hashtags: {e}")
            return {"purpose": "", "hashtags": []}
    
    def process_text(
        self, 
        text: str, 
        prompt_template: Optional[str] = None
    ) -> Dict[str, List[str]]:
        """
        Comprehensive text processing method.
        
        Args:
            text (str): Input text to process
            prompt_template (str, optional): Custom prompt template
        
        Returns:
            Dict with purpose and hashtags
        """
        # Default prompt template if not provided
        if not prompt_template:
            prompt_template = """Distill the core purpose of this text in one concise sentence:
            - Focus on the primary topic and main intent
            - Be clear and specific
            - Capture the essence in 15-20 words

            Provide 2-3 strategic hashtags that precisely target the content's core audience.
             - Hashtags should be 1-2 words each
             - Use commonly recognized terms and avoid long phrases
            - Ensure each hashtag is relevant and impactful

            Text to analyze: {text}

            IMPORTANT: Respond ONLY in this exact format:
            Purpose: [Your concise purpose sentence]
            Hashtags: [#hashtag1 #hashtag2 #hashtag3]"""
        
        # Prepare messages
        messages = [{
            "role": "user",
            "content": prompt_template.format(text=text)
        }]

       
        # Generate and process completion
        response = self.generate_completion(messages)
        return self.extract_purpose_and_hashtags(response)

    def set_purpose_descriptions(self,descriptions,purpose):
        print(descriptions)
        print(purpose)
        
    def generate_optimized_response(self, purpose: str, descriptions: List[Dict[str, str]]) -> str:
        """
        Generate an optimized response based on purpose and optionally analyzed top-performing descriptions.

        Args:
            purpose (str): Extracted purpose of the user's query.
            descriptions (List[Dict[str, str]]): List of top-performing post descriptions.

        Returns:
            str: Optimized response generated by the LLM.
        """
        if not descriptions:
            # Template for when descriptions are not available
    
            prompt_template_no_descriptions = """
            Generate an engaging and optimized post based solely on the following purpose. The response should stand out while aligning with the intended goal.

            Purpose: {purpose}

            IMPORTANT:
            - Create a fresh, innovative post that resonates with the intended audience.
            - Use an emotionally engaging tone, concise structure, and persuasive language.
            - Incorporate trends, buzzwords, and relevant themes to enhance performance.
            - Include a strong call-to-action to encourage interaction (e.g., likes, shares, or comments).
            - Suggest 7-8 trending and purpose-aligned hashtags to boost reach.
            - Ensure brevity (e.g., 250-400 characters) for optimal readability.
            """

            # Generate the response using the purpose-focused prompt
            prompt = prompt_template_no_descriptions.format(purpose=purpose)
        else:
            # Analyze descriptions for keywords, emojis, and tone
            analyzed_descriptions = []
            for desc in descriptions:
                caption = desc['caption']
                analyzed_descriptions.append({
                    "caption": caption.strip(),
                    "length": len(caption),
                })

            # Format captions for examples
            examples = "\n".join([f"- {desc['caption']}" for desc in analyzed_descriptions])

            # Template for when descriptions are provided
            prompt_template_with_descriptions = """
            Create an engaging and optimized post based on the following purpose and insights from successful examples. The response should stand out while aligning with the intended goal.

            Purpose: {purpose}

            Examples of successful posts:
            {examples}

            IMPORTANT:
            - Draw inspiration from the tone, structure, and trends in the examples, but do not copy or overly imitate them.
            - Adapt the emotional tone and core themes to fit the purpose uniquely.
            - Use concise, compelling language with clear calls-to-action to drive interaction.
            - Incorporate fresh ideas while staying relevant to the trends highlighted in the examples.
            - Include 7-8 trending hashtags relevant to the purpose, and ensure brevity (e.g., 250-300 characters).
            """

            # Generate the response using the full prompt
            prompt = prompt_template_with_descriptions.format(
                purpose=purpose,
                examples=examples,
            )

        # Generate the optimized response
        messages = [{"role": "user", "content": prompt}]
        optimized_response = self.generate_completion(messages)
       

        return optimized_response



     
# Example usage
def main():
    # Initialize client
    mixtral_client = MixtralClient()
    
    # Example text
    user_text = "Completed my first marathon today! From couch potato to marathon finisher in 18 months of dedicated training and self-transformation"
    
    # Process text
    result = mixtral_client.process_text(user_text)
    
    # Print results
    print("Purpose:", result['purpose'])
    print("Hashtags:", result['hashtags'])

if __name__ == "__main__":
    main()