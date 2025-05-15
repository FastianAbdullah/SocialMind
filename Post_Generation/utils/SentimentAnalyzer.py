import os
import re
from typing import List, Dict, Any, Tuple
import numpy as np
from openai import OpenAI
from dotenv import load_dotenv
from collections import Counter
from nltk.tokenize import word_tokenize
import nltk
from textblob import TextBlob
import matplotlib.pyplot as plt
import io
import base64

# Download NLTK resources
try:
    nltk.data.find('tokenizers/punkt')
except LookupError:
    nltk.download('punkt')

class SentimentAnalyzer:
    def __init__(self):
        load_dotenv()
        self.api_key = os.getenv("OPENAI_API_KEY")
        if self.api_key:
            self.client = OpenAI(api_key=self.api_key)
            self.model = "gpt-4o-mini-2024-07-18"
        
    def analyze_text_basic(self, text: str) -> Dict[str, Any]:
        """
        Basic sentiment analysis using TextBlob
        """
        if not text:
            return {"sentiment": "neutral", "score": 0.0}
        
        try:
            analysis = TextBlob(text)
            polarity = analysis.sentiment.polarity
            
            sentiment = "positive" if polarity > 0.1 else "negative" if polarity < -0.1 else "neutral"
            return {
                "sentiment": sentiment,
                "score": polarity
            }
        except Exception as e:
            print(f"Error analyzing text: {str(e)}")
            return {"sentiment": "neutral", "score": 0.0}
    
    def analyze_text_advanced(self, text: str) -> Dict[str, Any]:
        """
        Advanced sentiment analysis using OpenAI API
        """
        if not self.api_key or not text:
            return self.analyze_text_basic(text)
        
        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[
                    {"role": "system", "content": "Analyze the sentiment of the following text and provide a structured response with scores and details. Return only JSON."},
                    {"role": "user", "content": f"Analyze this text: {text}"}
                ],
                response_format={"type": "json_object"}
            )
            
            content = response.choices[0].message.content
            import json
            result = json.loads(content)
            
            # Ensure we have all required fields
            if "sentiment" not in result:
                result["sentiment"] = "neutral"
            if "score" not in result:
                result["score"] = 0.0
                
            return result
            
        except Exception as e:
            print(f"Error in advanced sentiment analysis: {e}")
            return self.analyze_text_basic(text)
    
    def analyze_comments(self, comments: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Analyze a list of comments and return simplified sentiment analysis
        """
        try:
            if not comments:
                return {
                    "overall_sentiment": "neutral",
                    "comment_count": 0
                }
            
            # Analyze individual comments
            scores = []
            for comment in comments:
                text = comment.get("text", "")
                if text:
                    analysis = self.analyze_text_basic(text)
                    scores.append(analysis["score"])
            
            # Calculate average score
            average_score = sum(scores) / len(scores) if scores else 0
            
            # Determine overall sentiment
            if average_score > 0.1:
                overall_sentiment = "positive"
            elif average_score < -0.1:
                overall_sentiment = "negative"
            else:
                overall_sentiment = "neutral"
            
            return {
                "overall_sentiment": overall_sentiment,
                "comment_count": len(comments)
            }
        except Exception as e:
            print(f"Error in analyze_comments: {str(e)}")
            return {
                "overall_sentiment": "neutral",
                "comment_count": 0,
                "error": str(e)
            }
    
    def _extract_common_words(self, text: str) -> List[str]:
        """Extract common meaningful words from text"""
        if not text:
            return []
            
        # Clean and tokenize text
        text = re.sub(r'[^\w\s]', '', text.lower())
        words = word_tokenize(text)
        
        # Remove common stop words (simplified version)
        stop_words = {'i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 
                      'your', 'yours', 'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 
                      'she', 'her', 'hers', 'herself', 'it', 'its', 'itself', 'they', 'them', 
                      'their', 'theirs', 'themselves', 'what', 'which', 'who', 'whom', 'this', 
                      'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 'been', 
                      'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 
                      'a', 'an', 'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 
                      'while', 'of', 'at', 'by', 'for', 'with', 'about', 'against', 'between', 
                      'into', 'through', 'during', 'before', 'after', 'above', 'below', 'to', 
                      'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under', 'again', 
                      'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how', 
                      'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 
                      'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 
                      'very', 's', 't', 'can', 'will', 'just', 'don', 'should', 'now'}
        
        filtered_words = [word for word in words if word not in stop_words and len(word) > 2]
        word_counts = Counter(filtered_words)
        
        # Return most common words
        return [word for word, count in word_counts.most_common(10)]
    
    def generate_sentiment_charts(self, analysis: Dict[str, Any]) -> Dict[str, str]:
        """Generate charts for sentiment analysis"""
        charts = {}
        
        # Sentiment distribution pie chart
        distribution = analysis.get("sentiment_distribution", {})
        if distribution:
            fig, ax = plt.subplots(figsize=(6, 4))
            labels = list(distribution.keys())
            sizes = list(distribution.values())
            colors = {'positive': '#4CAF50', 'neutral': '#FFC107', 'negative': '#F44336'}
            chart_colors = [colors.get(label, '#9E9E9E') for label in labels]
            
            ax.pie(sizes, labels=labels, autopct='%1.1f%%', colors=chart_colors)
            ax.set_title('Sentiment Distribution')
            
            # Convert plot to base64 string
            buffer = io.BytesIO()
            fig.savefig(buffer, format='png', bbox_inches='tight')
            buffer.seek(0)
            image_png = buffer.getvalue()
            buffer.close()
            plt.close(fig)
            
            charts["sentiment_distribution"] = base64.b64encode(image_png).decode('utf-8')
        
        # Sentiment score distribution
        comment_sentiments = analysis.get("comment_sentiments", [])
        if comment_sentiments:
            scores = [comment["score"] for comment in comment_sentiments]
            
            fig, ax = plt.subplots(figsize=(8, 4))
            ax.hist(scores, bins=20, color='#2196F3', alpha=0.7)
            ax.set_title('Sentiment Score Distribution')
            ax.set_xlabel('Sentiment Score')
            ax.set_ylabel('Frequency')
            ax.axvline(x=0, color='#9E9E9E', linestyle='--')
            
            buffer = io.BytesIO()
            fig.savefig(buffer, format='png', bbox_inches='tight')
            buffer.seek(0)
            image_png = buffer.getvalue()
            buffer.close()
            plt.close(fig)
            
            charts["score_distribution"] = base64.b64encode(image_png).decode('utf-8')
        
        return charts 