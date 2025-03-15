import os
import json
from typing import Dict, List, Any, Optional
from openai import OpenAI
from datetime import datetime
import time
import requests

class AIAgent:
    """
    Autonomous AI agent for social media management.
    This agent can understand user queries, generate content,
    suggest posting strategies, and manage conversation context.
    """
    
    def __init__(self, api_key: Optional[str] = None, base_url: str = "https://localhost:8443"):
        """
        Initialize the AI agent with necessary API clients.
        
        Args:
            api_key: OpenAI API key (defaults to environment variable)
            base_url: Base URL for API endpoints
        """
        self.api_key = api_key or os.getenv("OPENAI_API_KEY")
        if not self.api_key:
            raise ValueError("OpenAI API key must be provided or set as OPENAI_API_KEY environment variable")
        
        # Initialize OpenAI client with GPT-4o
        self.client = OpenAI(api_key=self.api_key)
        self.model = "gpt-4o-mini-2024-07-18"  # Can be upgraded to full gpt-4o for better performance
        
        # Set base URL for API calls
        self.base_url = base_url
        self.verify_ssl = False  # Set to True in production
        
        # Conversation memory to maintain context
        self.conversation_history = []
        
        # Agent state tracking
        self.current_state = "idle"  # idle, content_generation, platform_selection, scheduling, confirmation
        self.current_task = {}
        
    def add_message(self, role: str, content: str) -> None:
        """
        Add a message to the conversation history.
        
        Args:
            role: The role of the message sender (user, assistant, system)
            content: The content of the message
        """
        self.conversation_history.append({
            "role": role,
            "content": content,
            "timestamp": datetime.now().isoformat()
        })
        
        # Keep history at a reasonable size (retaining last 20 messages)
        if len(self.conversation_history) > 20:
            self.conversation_history = self.conversation_history[-20:]
    
    def get_formatted_messages(self) -> List[Dict[str, str]]:
        """
        Format conversation history for the OpenAI API.
        
        Returns:
            List of message dictionaries in the format expected by OpenAI
        """
        system_message = {
            "role": "system",
            "content": """You are an autonomous social media management agent. Your capabilities include:
            1. Understanding user's content needs and goals
            2. Generating optimized content for different platforms
            3. Suggesting optimal posting times based on platform analytics
            4. Helping refine content through conversation
            5. Coordinating the posting process

            When suggesting posting times, consider these platform-specific optimal times:
            - Instagram: Weekdays 11am-1pm and 7pm-9pm, Weekends 10am-1pm
            - Facebook: Weekdays 1pm-4pm, Weekends 12pm-1pm
            - LinkedIn: Weekdays 8am-10am and 1pm-2pm, Tuesday-Thursday best
            - Twitter: Weekdays 8am-10am and 6pm-9pm

            Always maintain a helpful, concise, and professional tone. Ask clarifying questions when needed.
            """
        }
        
        # Format just the role and content for OpenAI API
        formatted_messages = [system_message] + [
            {"role": msg["role"], "content": msg["content"]} 
            for msg in self.conversation_history
        ]
        
        return formatted_messages
    
    def process_query(self, user_query: str, access_token: Optional[str] = None, context: Optional[Dict] = None, autonomous_mode: bool = False) -> Dict[str, Any]:
        """
        Process a user query and update the agent state accordingly.
        
        Args:
            user_query: The user's query/request
            access_token: Optional access token for API calls
            context: Optional conversation context from previous interactions
            autonomous_mode: Whether the agent should act autonomously
            
        Returns:
            A dictionary containing the agent's response and action plan
        """
        # Add user query to conversation history
        self.add_message("user", user_query)
        
        # Print debugging information
        print(f"DEBUG AGENT: Processing query: '{user_query}'")
        print(f"DEBUG AGENT: Current state before processing: {self.current_state}")
        print(f"DEBUG AGENT: Current task before processing: {self.current_task}")
        
        # Update state from context if provided
        if context:
            if 'state' in context:
                self.current_state = context['state']
                print(f"DEBUG AGENT: Updated state from context to: {self.current_state}")
            if 'currentTask' in context and context['currentTask']:
                self.current_task = context['currentTask']
                print(f"DEBUG AGENT: Updated task from context")
        
        # Special case handling for platform selection if we already have content
        if user_query.lower().strip() in ["instagram", "facebook", "linkedin", "twitter"]:
            platform = user_query.strip()
            if self.current_task and "content" in self.current_task:
                print(f"DEBUG AGENT: Platform '{platform}' selected with existing content - proceeding to post")
                
                # Update platforms in current task
                if "platforms" not in self.current_task:
                    self.current_task["platforms"] = []
                
                if platform not in self.current_task["platforms"]:
                    self.current_task["platforms"] = [platform]  # Replace with just the selected platform
                
                # Skip straight to confirmation/posting
                response = self._handle_confirmation({
                    "intent": "confirmation",
                    "parameters": {
                        "confirmation": "yes",
                        "schedule": "immediately",
                        "platform": platform
                    }
                }, access_token)
                
                # Add agent response to conversation history
                self.add_message("assistant", response["message"])
                
                # Include current task and state in response
                response["current_task"] = self.current_task
                response["state"] = "posting"  # Set state to posting
                response["intent"] = "confirmation"
                response["to_post"] = {
                    "content": self.current_task["content"],
                    "platforms": self.current_task["platforms"]
                }
                
                print(f"DEBUG AGENT: Direct posting response: {response}")
                return response
        
        # Special case handling for "Post immediately" to ensure it's treated as confirmation
        if user_query.lower().strip() == "post immediately":
            print("DEBUG AGENT: 'Post immediately' detected - forcing confirmation flow")
            self.current_state = "confirmation"
            response = self._handle_confirmation({
                "intent": "confirmation",
                "parameters": {
                    "confirmation": "yes",
                    "schedule": "immediately"
                }
            }, access_token)
            
            # Add agent response to conversation history
            self.add_message("assistant", response["message"])
            
            # Include current task and state in response
            response["current_task"] = self.current_task
            response["state"] = self.current_state
            response["intent"] = "confirmation"
            
            print(f"DEBUG AGENT: Response for 'Post immediately': {response}")
            return response
        
        # Determine intent and next actions based on the query
        intent_analysis = self._analyze_intent(user_query)
        print(f"DEBUG AGENT: Intent analysis result: {intent_analysis}")
        
        # Update agent state based on intent
        if intent_analysis["intent"] == "content_generation":
            self.current_state = "content_generation"
            response = self._handle_content_generation(intent_analysis, access_token)
        elif intent_analysis["intent"] == "platform_selection":
            self.current_state = "platform_selection"
            response = self._handle_platform_selection(intent_analysis, access_token)
        elif intent_analysis["intent"] == "scheduling":
            self.current_state = "scheduling"
            response = self._handle_scheduling(intent_analysis)
        elif intent_analysis["intent"] == "confirmation":
            self.current_state = "confirmation"
            response = self._handle_confirmation(intent_analysis, access_token)
        elif intent_analysis["intent"] == "edits":
            # Handle edit requests for content
            self.current_state = "editing"
            response = self._handle_content_edit(intent_analysis, access_token)
        else:
            # General conversation or unclear intent
            response = self._generate_conversation_response(user_query)
        
        # Include the detected intent in the response
        response["intent"] = intent_analysis["intent"]
        
        # Add agent response to conversation history
        self.add_message("assistant", response["message"])
        
        print(f"DEBUG AGENT: Current state after processing: {self.current_state}")
        
        # In autonomous mode, take next actions automatically if possible
        if autonomous_mode and self.current_state != "error":
            response["autonomous_mode"] = True
            
            # If content is generated, and we have a clear platform preference
            if self.current_state == "content_generation" and "content" in response:
                # Include detected platforms in response for automatic selection
                platforms = intent_analysis.get("parameters", {}).get("platforms", [])
                if platforms:
                    response["platforms"] = platforms
            
            # If platforms are selected and we have content ready
            elif self.current_state == "platform_selection" and "platforms" in response:
                if "content" in self.current_task:
                    response["content"] = self.current_task["content"]
                    
            # If we're ready for confirmation and have all necessary data
            elif self.current_state == "confirmation":
                if all(key in self.current_task for key in ["content", "platforms"]):
                    response["ready_to_post"] = True
                    response["content"] = self.current_task["content"]
                    response["platforms"] = self.current_task["platforms"]
                    
        # Include current task in response for stateful interactions
        response["current_task"] = self.current_task
        response["state"] = self.current_state
        
        return response
    
    def _analyze_intent(self, query: str) -> Dict[str, Any]:
        """
        Analyze the user's query to determine their intent.
        
        Args:
            query: The user's query
            
        Returns:
            A dictionary with the detected intent and relevant parameters
        """
        messages = self.get_formatted_messages()
        
        # Debug: Check if query matches "Post immediately"
        print(f"DEBUG: Analyzing intent for query: '{query}'")
        
        # Special case for "Post immediately" - explicitly set confirmation intent
        if query.lower().strip() == "post immediately":
            print("DEBUG: Detected 'Post immediately' - setting confirmation intent")
            return {
                "intent": "confirmation",
                "parameters": {
                    "confirmation": "yes",
                    "schedule": "immediately"
                }
            }
            
        # Special case for platform selection - bypass scheduling and go straight to confirmation
        if query.lower().strip() in ["instagram", "facebook", "linkedin", "twitter"]:
            print(f"DEBUG: Detected platform selection: '{query}' - setting direct posting intent")
            platform = query.strip()
            # Update current task with the selected platform
            if self.current_task and "content" in self.current_task:
                if "platforms" not in self.current_task:
                    self.current_task["platforms"] = []
                
                if platform not in self.current_task["platforms"]:
                    self.current_task["platforms"].append(platform)
                
                print(f"DEBUG: Updated current task with platform: {self.current_task}")
                
                # Skip scheduling and directly go to confirmation
                return {
                    "intent": "confirmation",
                    "parameters": {
                        "confirmation": "yes",
                        "schedule": "immediately",
                        "platform": platform
                    }
                }
        
        # Add a specific instruction for intent classification
        intent_prompt = {
            "role": "system",
            "content": """Analyze the user's query to determine their intent. 
            Possible intents: content_generation, platform_selection, scheduling, confirmation, general_question.
            Extract any relevant parameters like content_type, platform, scheduling_preferences, etc.
            Respond with a JSON object."""
        }
        
        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=messages + [intent_prompt],
                max_tokens=500,
                temperature=0.2,
                response_format={"type": "json_object"}
            )
            
            intent_data = json.loads(response.choices[0].message.content)
            print(f"DEBUG: Intent analysis result: {intent_data}")
            return intent_data
        except Exception as e:
            print(f"Error analyzing intent: {e}")
            return {"intent": "general_question", "parameters": {}}
    
    def _generate_conversation_response(self, query: str) -> Dict[str, Any]:
        """
        Generate a conversational response for general queries.
        
        Args:
            query: The user's query
            
        Returns:
            A dictionary with the agent's response message and suggested actions
        """
        messages = self.get_formatted_messages()
        
        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=messages,
                max_tokens=1000,
                temperature=0.7
            )
            
            return {
                "message": response.choices[0].message.content,
                "actions": [],
                "state": "conversation"
            }
        except Exception as e:
            print(f"Error generating conversation response: {e}")
            return {
                "message": "I'm having trouble understanding right now. Could you please rephrase your question?",
                "actions": [],
                "state": "error"
            }
    
    def _handle_content_generation(self, intent_data: Dict[str, Any], access_token: Optional[str] = None) -> Dict[str, Any]:
        """
        Handle content generation requests, using existing API if possible.
        
        Args:
            intent_data: Dict containing intent parameters
            access_token: Optional access token for API calls
            
        Returns:
            Dict with response message and suggested actions
        """
        # Extract relevant parameters
        params = intent_data.get("parameters", {})
        content_type = params.get("content_type", "general")
        topic = params.get("topic", "")
        platforms = params.get("platforms", [])
        platform = platforms[0] if platforms else None
        
        # Try to use the existing content generation API if access token is available
        if access_token and topic:
            try:
                headers = {"Authorization": access_token, "Content-Type": "application/json"}
                input_text = f"{content_type} about {topic}"
                if platform:
                    input_text += f" for {platform}"
                
                response = requests.post(
                    f"{self.base_url}/content/generate-optimized",
                    headers=headers,
                    json={"text": input_text},
                    verify=self.verify_ssl
                )
                
                if response.status_code == 200:
                    data = response.json()
                    if data.get("status") == "success":
                        optimized_content = data.get("optimized_content", "")
                        hashtags = data.get("analysis", {}).get("suggested_hashtags", [])
                        
                        # Update current task
                        self.current_task = {
                            "type": "content_generation",
                            "content": optimized_content,
                            "platforms": platforms,
                            "topic": topic,
                            "hashtags": hashtags,
                            "timestamp": datetime.now().isoformat()
                        }
                        
                        # Prepare next steps
                        next_steps = [
                            "Select specific platforms to post this content",
                            "Refine or edit this content",
                            "Schedule this content for posting"
                        ]
                        
                        return {
                            "message": optimized_content,
                            "actions": next_steps,
                            "state": "content_generated",
                            "content": optimized_content,
                            "hashtags": hashtags
                        }
            except Exception as e:
                print(f"Error using content generation API: {e}")
                # Will fall back to agent-based generation
        
        # Create a specialized prompt for content generation
        content_prompt = f"""Create engaging social media content about {topic}.
        Content type: {content_type}
        Target platforms: {', '.join(platforms) if platforms else 'all platforms'}
        
        Include:
        1. A catchy headline or title
        2. Main content body
        3. Relevant hashtags
        4. Call to action
        """
        
        messages = self.get_formatted_messages()
        messages.append({"role": "user", "content": content_prompt})
        
        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=messages,
                max_tokens=1500,
                temperature=0.7
            )
            
            generated_content = response.choices[0].message.content
            
            # Try to extract hashtags from generated content
            hashtags = []
            for line in generated_content.split('\n'):
                if '#' in line:
                    for word in line.split():
                        if word.startswith('#'):
                            hashtags.append(word[1:])  # Remove the # symbol
            
            # Update current task with the generated content
            self.current_task = {
                "type": "content_generation",
                "content": generated_content,
                "platforms": platforms,
                "topic": topic,
                "hashtags": hashtags,
                "timestamp": datetime.now().isoformat()
            }
            
            # Prepare next steps suggestions
            next_steps = [
                "Select specific platforms to post this content",
                "Refine or edit this content",
                "Schedule this content for posting"
            ]
            
            return {
                "message": generated_content,
                "actions": next_steps,
                "state": "content_generated",
                "content": generated_content,
                "hashtags": hashtags
            }
            
        except Exception as e:
            print(f"Error generating content: {e}")
            return {
                "message": "I encountered an issue while generating content. Let's try a different approach.",
                "actions": [],
                "state": "error"
            }
    
    def _handle_platform_selection(self, intent_data: Dict[str, Any], access_token: Optional[str] = None) -> Dict[str, Any]:
        """
        Handle platform selection for content posting, including fetching available accounts.
        
        Args:
            intent_data: Dict containing intent parameters
            access_token: Optional access token for API calls
        """
        available_platforms = ["Instagram", "Facebook", "LinkedIn"]
        selected_platforms = []
        
        # If we have an access token, check which platforms the user has connected
        if access_token:
            try:
                # Check Instagram accounts
                ig_response = requests.get(
                    f"{self.base_url}/instagram/accounts",
                    headers={"Authorization": access_token},
                    verify=self.verify_ssl
                )
                if ig_response.status_code == 200 and ig_response.json().get("status") == "success":
                    if ig_response.json().get("accounts"):
                        selected_platforms.append("Instagram")
                
                # Check Facebook pages
                fb_response = requests.get(
                    f"{self.base_url}/facebook/pages",
                    headers={"Authorization": access_token},
                    verify=self.verify_ssl
                )
                if fb_response.status_code == 200 and fb_response.json().get("pages"):
                    selected_platforms.append("Facebook")
                
                # Check LinkedIn profile
                li_response = requests.get(
                    f"{self.base_url}/linkedin/profile",
                    headers={"Authorization": access_token},
                    verify=self.verify_ssl
                )
                if li_response.status_code == 200 and li_response.json().get("status") == "success":
                    selected_platforms.append("LinkedIn")
                    
            except Exception as e:
                print(f"Error checking available platforms: {e}")
                # Fallback to all platforms if API calls fail
                selected_platforms = available_platforms
        else:
            # Without access token, suggest all platforms
            selected_platforms = available_platforms
            
        # Extract platform preference if any
        params = intent_data.get("parameters", {})
        preferred_platform = params.get("platform")
        
        # If there's a preferred platform, prioritize it
        if preferred_platform:
            for platform in available_platforms:
                if preferred_platform.lower() in platform.lower() and platform in selected_platforms:
                    selected_platforms.insert(0, selected_platforms.pop(selected_platforms.index(platform)))
                    break
        
        # Generate message based on available platforms
        if selected_platforms:
            message = f"Based on your connected accounts, I recommend posting to: {', '.join(selected_platforms)}."
            if "content" in self.current_task:
                message += "\n\nWould you like to post the content we just created to these platforms?"
        else:
            message = "I don't see any connected social media accounts. Would you like to set up platform connections first?"
        
        # Update current task
        if "platforms" not in self.current_task or not self.current_task["platforms"]:
            self.current_task["platforms"] = selected_platforms
        
        return {
            "message": message,
            "actions": selected_platforms,
            "state": "platform_selection",
            "platforms": selected_platforms
        }
    
    def _handle_scheduling(self, intent_data: Dict[str, Any]) -> Dict[str, Any]:
        """Handle scheduling requests for social media posts"""
        # Extract platform from current task or intent data
        platforms = self.current_task.get("platforms", [])
        if not platforms:
            params = intent_data.get("parameters", {})
            platform = params.get("platform")
            if platform:
                platforms = [platform]
        
        # If no platforms selected, use a default
        if not platforms:
            platforms = ["instagram"]
        
        # Get posting time suggestions for each platform
        all_suggestions = {}
        for platform in platforms:
            all_suggestions[platform] = self.suggest_optimal_times(platform)
        
        # Format response message
        message = "Here are my suggested posting times:\n\n"
        for platform, suggestions in all_suggestions.items():
            message += f"📱 {platform.title()}:\n"
            for i, suggestion in enumerate(suggestions, 1):
                message += f"  {i}. {suggestion['time']} - {suggestion['rationale']}\n"
            message += "\n"
        
        message += "Would you like to schedule your post for one of these times or post it immediately?"
        
        # Create actions for scheduling options
        actions = [
            "Post immediately",
            "Schedule for recommended time",
            "Select a custom time"
        ]
        
        return {
            "message": message,
            "actions": actions,
            "state": "scheduling",
            "suggestions": all_suggestions
        }
    
    def _handle_confirmation(self, intent_data: Dict[str, Any], access_token: Optional[str] = None) -> Dict[str, Any]:
        """
        Handle confirmation requests for finalizing and posting content.
        
        Args:
            intent_data: Dict containing intent parameters
            access_token: Optional access token for API calls
        """
        params = intent_data.get("parameters", {})
        confirmation = params.get("confirmation", "").lower()
        
        print(f"DEBUG: Handling confirmation with params: {params}")
        print(f"DEBUG: Current task state: {self.current_task}")
        print(f"DEBUG: Confirmation value: '{confirmation}'")
        
        if "yes" in confirmation or "confirm" in confirmation or "proceed" in confirmation:
            # Check if we have all required information in current_task
            if not self.current_task or "content" not in self.current_task:
                print("DEBUG: Missing required information for posting")
                return {
                    "message": "I don't have any content to edit. Let's create some content first.",
                    "actions": ["Create content"],
                    "state": "idle"
                }
                
            # We have confirmation and all required data
            content = self.current_task.get("content")
            platforms = self.current_task.get("platforms", [])
            
            # If no platforms are selected, use Instagram as default
            if not platforms and "platform" in params:
                platform = params.get("platform")
                if platform:
                    platforms = [platform]
                    print(f"DEBUG: Using platform from params: {platform}")
            
            # If we still have no platforms, default to Instagram for testing
            if not platforms:
                platforms = ["Instagram"]
                print("DEBUG: No platforms specified, defaulting to Instagram")
            
            print(f"DEBUG: Ready to post. Content: '{content[:30]}...' to platforms: {platforms}")
            
            if not access_token:
                # Without access token, we can only simulate
                platform_list = ", ".join([p.title() for p in platforms])
                return {
                    "message": f"I would now post the following content to {platform_list}:\n\n{content}\n\nHowever, I don't have access tokens to actually post. You'll need to provide authentication to complete this action.",
                    "actions": ["Authenticate"],
                    "state": "confirmation"
                }
            
            # With access token, we could actually post, but will defer to the AgentConnector
            # which handles the actual posting via API calls
            print("DEBUG: Returning post information for actual posting via connector")
            return {
                "message": f"Great! I'll post your content to the selected platforms. This process may take a moment.",
                "actions": ["View post status", "Create new content"],
                "state": "posting",
                "to_post": {
                    "content": content,
                    "platforms": platforms
                }
            }
            
        else:
            # User didn't confirm
            print("DEBUG: User did not confirm posting")
            return {
                "message": "No problem. What would you like to do instead?",
                "actions": ["Edit content", "Change platforms", "Start over"],
                "state": "cancelled"
            }
    
    def suggest_optimal_times(self, platform: str) -> List[Dict[str, Any]]:
        """
        Suggest optimal posting times based on the platform.
        
        Args:
            platform: The social media platform (instagram, facebook, linkedin, twitter)
            
        Returns:
            List of suggested posting times with rationale
        """
        # Get current day and time
        now = datetime.now()
        day_of_week = now.strftime("%A")
        current_hour = now.hour
        
        # Platform-specific optimal times (simplified version)
        platform_times = {
            "instagram": [
                {"day": "Weekday", "times": ["11:00 AM", "12:00 PM", "7:00 PM", "8:00 PM"]},
                {"day": "Weekend", "times": ["10:00 AM", "11:00 AM", "12:00 PM"]}
            ],
            "facebook": [
                {"day": "Weekday", "times": ["1:00 PM", "2:00 PM", "3:00 PM"]},
                {"day": "Weekend", "times": ["12:00 PM", "1:00 PM"]}
            ],
            "linkedin": [
                {"day": "Weekday", "times": ["8:00 AM", "9:00 AM", "1:00 PM", "2:00 PM"]},
                {"day": "Weekend", "times": []}  # LinkedIn generally less effective on weekends
            ],
            "twitter": [
                {"day": "Weekday", "times": ["8:00 AM", "9:00 AM", "6:00 PM", "7:00 PM", "8:00 PM"]},
                {"day": "Weekend", "times": ["9:00 AM", "11:00 AM", "1:00 PM"]}
            ]
        }
        
        # Determine if today is a weekday or weekend
        is_weekend = day_of_week in ["Saturday", "Sunday"]
        day_type = "Weekend" if is_weekend else "Weekday"
        
        # Get suggested times for the platform
        platform = platform.lower()
        if platform not in platform_times:
            return [{"time": "12:00 PM", "rationale": "Default suggestion as platform not recognized"}]
        
        # Identify day-specific times
        times_for_day_type = []
        for time_group in platform_times[platform]:
            if time_group["day"] == day_type:
                times_for_day_type = time_group["times"]
                break
        
        # Format suggestions with rationale
        suggestions = []
        for time_str in times_for_day_type:
            hour = int(time_str.split(":")[0])
            if "PM" in time_str and hour != 12:
                hour += 12
            
            # Skip times that have already passed today
            if hour <= current_hour:
                continue
                
            suggestion = {
                "time": time_str,
                "rationale": f"Optimal engagement time for {platform} on {day_type}s",
                "timestamp": datetime.now().replace(hour=hour, minute=0, second=0).isoformat()
            }
            suggestions.append(suggestion)
        
        # If no valid times for today, suggest for tomorrow
        if not suggestions:
            next_day = "Weekday" if is_weekend else "Weekend"
            for time_group in platform_times[platform]:
                if time_group["day"] == next_day:
                    for time_str in time_group["times"]:
                        suggestion = {
                            "time": time_str,
                            "rationale": f"Optimal engagement time for {platform} tomorrow ({next_day})",
                            "timestamp": f"Tomorrow at {time_str}"
                        }
                        suggestions.append(suggestion)
                    break
        
        return suggestions[:3]  # Return top 3 suggestions
    
    def _handle_content_edit(self, intent_data: Dict[str, Any], access_token: Optional[str] = None) -> Dict[str, Any]:
        """
        Handle editing requests for previously generated content.
        
        Args:
            intent_data: Dict containing intent parameters
            access_token: Optional access token for API calls
            
        Returns:
            Dict with edited content and next steps
        """
        # Extract relevant parameters
        params = intent_data.get("parameters", {})
        edit_instructions = params.get("edit_instructions", "")
        
        # Check if we have content to edit
        if not self.current_task or "content" not in self.current_task:
            return {
                "message": "I don't have any content to edit. Let's create some content first.",
                "actions": ["Create content"],
                "state": "idle"
            }
        
        current_content = self.current_task["content"]
        
        # Create a specialized prompt for content editing
        edit_prompt = f"""Edit the following content based on these instructions: {edit_instructions}
        
        ORIGINAL CONTENT:
        {current_content}
        
        Please provide the complete revised content. Maintain the original style and tone unless specifically instructed otherwise.
        """
        
        messages = self.get_formatted_messages()
        messages.append({"role": "user", "content": edit_prompt})
        
        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=messages,
                max_tokens=1500,
                temperature=0.7
            )
            
            edited_content = response.choices[0].message.content
            
            # If the content starts with explanatory text, try to extract just the edited content
            if "EDITED CONTENT:" in edited_content:
                edited_content = edited_content.split("EDITED CONTENT:")[1].strip()
            
            # Update the current task with the edited content
            self.current_task["content"] = edited_content
            
            # Prepare next steps suggestions
            next_steps = [
                "Keep this edited version",
                "Make additional edits",
                "Select platforms to post to",
                "Schedule this content for posting"
            ]
            
            return {
                "message": f"I've edited the content as requested. Here's the updated version:\n\n{edited_content}",
                "actions": next_steps,
                "state": "content_edited",
                "content": edited_content
            }
            
        except Exception as e:
            print(f"Error editing content: {e}")
            return {
                "message": "I encountered an issue while editing the content. Let's try again with simpler instructions.",
                "actions": ["Try editing again"],
                "state": "error"
            }