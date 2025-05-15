import os
import requests
import json
from dotenv import load_dotenv
import time
import sys
from urllib3.exceptions import InsecureRequestWarning

# Suppress SSL warning for self-signed certificates in demo
import urllib3
urllib3.disable_warnings(InsecureRequestWarning)

# Load environment variables
load_dotenv()

class AgentDemo:
    """Demonstration for using the AI Agent API endpoints."""
    
    def __init__(self, base_url="https://localhost:8443"):
        """Initialize the demo with the API base URL."""
        self.base_url = base_url
        self.access_token = os.getenv("SOCIAL_ACCESS_TOKEN", None)
        
        # By default, don't verify SSL (for self-signed certificates)
        # In production, this should be True
        self.verify_ssl = False
    
    def set_access_token(self, token):
        """Set the access token for authenticated requests."""
        self.access_token = token
    
    def execute_query(self, query_text):
        """Send a query to the agent and get a response."""
        headers = {}
        if self.access_token:
            headers["Authorization"] = self.access_token
        
        url = f"{self.base_url}/agent/query"
        payload = {
            "query": query_text
        }
        
        try:
            response = requests.post(
                url, 
                json=payload, 
                headers=headers,
                verify=self.verify_ssl
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error: API returned status code {response.status_code}")
                print(f"Response: {response.text}")
                return None
                
        except requests.RequestException as e:
            print(f"Error connecting to API: {e}")
            return None
    
    def generate_content(self, topic, platform=None, content_type="post"):
        """Generate optimized content for a given topic and platform."""
        headers = {}
        if self.access_token:
            headers["Authorization"] = self.access_token
            
        url = f"{self.base_url}/agent/generate-content"
        payload = {
            "topic": topic,
            "platform": platform,
            "content_type": content_type
        }
        
        try:
            response = requests.post(
                url, 
                json=payload, 
                headers=headers,
                verify=self.verify_ssl
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error: API returned status code {response.status_code}")
                print(f"Response: {response.text}")
                return None
                
        except requests.RequestException as e:
            print(f"Error connecting to API: {e}")
            return None
    
    def suggest_posting_times(self, platform):
        """Get optimal posting time suggestions for a platform."""
        url = f"{self.base_url}/agent/suggest-times"
        payload = {
            "platform": platform
        }
        
        try:
            response = requests.post(
                url, 
                json=payload,
                verify=self.verify_ssl
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error: API returned status code {response.status_code}")
                print(f"Response: {response.text}")
                return None
                
        except requests.RequestException as e:
            print(f"Error connecting to API: {e}")
            return None
    
    def post_content(self, platform, content, image_url=None):
        """Post content to a social media platform."""
        if not self.access_token:
            print("Error: Access token is required for posting content")
            return None
            
        headers = {"Authorization": self.access_token}
        url = f"{self.base_url}/agent/post-content"
        payload = {
            "platform": platform,
            "content": content
        }
        
        if image_url:
            payload["image_url"] = image_url
        
        try:
            response = requests.post(
                url, 
                json=payload, 
                headers=headers,
                verify=self.verify_ssl
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                print(f"Error: API returned status code {response.status_code}")
                print(f"Response: {response.text}")
                return None
                
        except requests.RequestException as e:
            print(f"Error connecting to API: {e}")
            return None
    
    def run_demo(self):
        """Run a full demonstration of the agent capabilities."""
        print("=== AI Agent Demonstration ===\n")
        
        # 1. Check if the base API is available
        try:
            response = requests.get(f"{self.base_url}/", verify=self.verify_ssl)
            if response.status_code != 200:
                print(f"Error: API not available at {self.base_url}")
                return
        except requests.RequestException:
            print(f"Error: Could not connect to API at {self.base_url}")
            return
        
        print("API is available. Beginning demonstration.\n")
        
        # 2. Natural language query
        print("=== 1. Natural Language Query ===")
        query = "I want to create content about sustainable technology for Instagram"
        print(f"Sending query: '{query}'")
        
        query_result = self.execute_query(query)
        if query_result and query_result.get("status") == "success":
            print(f"\nAgent response: {query_result.get('response')}")
            if "actions" in query_result:
                print(f"Suggested actions: {', '.join(query_result.get('actions'))}")
        else:
            print("Failed to get response from agent.")
            return
            
        time.sleep(1)  # Pause for readability
        
        # 3. Generate content
        print("\n=== 2. Content Generation ===")
        topic = "sustainable technology"
        platform = "instagram"
        print(f"Generating content about '{topic}' for {platform}")
        
        content_result = self.generate_content(topic, platform)
        if content_result and content_result.get("status") == "success":
            content = content_result.get("content")
            hashtags = content_result.get("hashtags", [])
            
            print(f"\nGenerated content:")
            print(f"{content}")
            
            if hashtags:
                print(f"\nSuggested hashtags: {hashtags}")
        else:
            print("Failed to generate content.")
            return
            
        time.sleep(1)  # Pause for readability
        
        # 4. Posting time suggestions
        print("\n=== 3. Posting Time Recommendations ===")
        print(f"Getting optimal posting times for {platform}")
        
        time_result = self.suggest_posting_times(platform)
        if time_result and time_result.get("status") == "success":
            suggestions = time_result.get("suggestions", [])
            
            print("\nRecommended posting times:")
            for suggestion in suggestions:
                print(f"- {suggestion.get('time')}: {suggestion.get('rationale')}")
        else:
            print("Failed to get posting time suggestions.")
            return
            
        time.sleep(1)  # Pause for readability
        
        # 5. Posting content (only if access token is available)
        if self.access_token:
            print("\n=== 4. Posting Content ===")
            print(f"Would post the generated content to {platform}")
            print("(This step is simulated in the demo to avoid actual posting)")
            
            # Uncomment to actually post content
            # post_result = self.post_content(platform, content)
            # if post_result and post_result.get("status") == "success":
            #     print(f"Successfully posted to {platform}!")
            #     print(f"Post ID: {post_result.get('post_id')}")
            # else:
            #     print("Failed to post content.")
        else:
            print("\n=== 4. Posting Content ===")
            print("Skipping posting step as no access token is available.")
            print("Set the SOCIAL_ACCESS_TOKEN environment variable to enable posting.")
        
        # Conclusion
        print("\n=== Demonstration Complete ===")
        print("The AI agent successfully demonstrated:")
        print("1. Understanding natural language queries")
        print("2. Generating optimized content")
        print("3. Recommending optimal posting times")
        if self.access_token:
            print("4. Posting capability (simulated)")
        
        print("\nTo use the agent in your application, make API calls to the endpoints shown in this demo.")

if __name__ == "__main__":
    # Get base URL from command line arguments if provided
    base_url = sys.argv[1] if len(sys.argv) > 1 else "https://localhost:8443"
    
    # Create and run the demo
    demo = AgentDemo(base_url)
    
    # Set access token if available
    token = os.getenv("SOCIAL_ACCESS_TOKEN")
    if token:
        demo.set_access_token(token)
        print("Access token found. Full functionality available.")
    else:
        print("No access token found. Some features will be limited.")
    
    # Run the demo
    demo.run_demo()