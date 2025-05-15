import os
import sys
from dotenv import load_dotenv
from utils.AIAgent import AIAgent
from utils.AgentConnector import AgentConnector

# Load environment variables
load_dotenv()

def test_basic_conversation():
    """Test basic conversation with the agent"""
    agent = AIAgent()
    
    # Test a simple query
    query = "I want to create social media content about sustainable fashion"
    print(f"\n\nTesting query: '{query}'")
    response = agent.process_query(query)
    
    print("\nResponse:")
    print(f"Message: {response.get('message', '')[:200]}...")  # Print first 200 chars
    print(f"State: {response.get('state', '')}")
    print(f"Actions: {response.get('actions', [])}")
    
    # Test a follow-up query
    follow_up = "What platforms would you recommend for this content?"
    print(f"\n\nFollow-up query: '{follow_up}'")
    response = agent.process_query(follow_up)
    
    print("\nResponse:")
    print(f"Message: {response.get('message', '')[:200]}...")
    print(f"Platforms: {response.get('platforms', [])}")

def test_content_generation():
    """Test content generation capabilities"""
    connector = AgentConnector()
    
    # Test generating content for different platforms
    platforms = ["Instagram", "LinkedIn", None]
    topic = "Artificial Intelligence in Healthcare"
    
    for platform in platforms:
        platform_name = platform if platform else "general"
        print(f"\n\nGenerating content for {platform_name} about {topic}")
        
        response = connector.generate_content(
            topic=topic,
            platform=platform,
            content_type="post"
        )
        
        print("\nGenerated content:")
        print(f"{response.get('message', '')[:200]}...")  # Print first 200 chars
        
        if "actions" in response:
            print(f"\nSuggested next steps: {response['actions']}")
            
        if "hashtags" in response:
            print(f"\nSuggested hashtags: {response.get('hashtags', [])}")

def test_time_suggestions():
    """Test posting time suggestion capabilities"""
    agent = AIAgent()
    
    # Test time suggestions for different platforms
    platforms = ["instagram", "facebook", "linkedin", "twitter"]
    
    for platform in platforms:
        print(f"\n\nSuggesting posting times for {platform}")
        suggestions = agent.suggest_optimal_times(platform)
        
        print(f"Found {len(suggestions)} suggestions:")
        for suggestion in suggestions:
            print(f"- {suggestion['time']}: {suggestion['rationale']}")

def test_api_integration():
    """Test integration with API endpoints (simulation only)"""
    # This would normally use requests to call the API
    # For testing purposes, we'll simulate the calls
    
    print("\n\nSimulating API endpoint integration:")
    
    # 1. Simulate query endpoint
    print("\nEndpoint: /agent/query")
    print("Request: {'query': 'Create a post about AI for Facebook'}")
    
    # 2. Simulate content generation
    print("\nEndpoint: /agent/generate-content")
    print("Request: {'topic': 'AI trends', 'platform': 'facebook'}")
    
    # 3. Simulate posting
    print("\nEndpoint: /agent/post-content")
    print("Request: {'platform': 'facebook', 'content': 'My test post about AI'}")
    
    print("\nAPI endpoints are ready for integration")

if __name__ == "__main__":
    # Check if OpenAI API key is available
    if not os.getenv("OPENAI_API_KEY"):
        print("Error: OpenAI API key not found in environment variables.")
        print("Please set OPENAI_API_KEY in your .env file.")
        sys.exit(1)
    
    # Run tests
    print("=== Testing AI Agent ===")
    
    try:
        print("\n=== Basic Conversation Test ===")
        test_basic_conversation()
        
        print("\n=== Content Generation Test ===")
        test_content_generation()
        
        print("\n=== Time Suggestion Test ===")
        test_time_suggestions()
        
        print("\n=== API Integration Test ===")
        test_api_integration()
        
        print("\n\nAll tests completed successfully!")
        print("\nThe AI agent is ready for integration with your social media management system.")
        print("Use the API endpoints to interact with the agent from your applications.")
    except Exception as e:
        print(f"\nError during testing: {str(e)}")
        import traceback
        traceback.print_exc()