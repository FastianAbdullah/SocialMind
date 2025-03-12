from flask import Flask, request, jsonify, redirect
from utils.FacebookManager import FacebookManager
from utils.InstagramManager import InstagramManager
from utils.InstagramContentAnalyzer import InstagramContentAnalyzer
from utils.LinkedInManager import LinkedInManager
from utils.MixtralClient import MixtralClient
from utils.SocialMediaAuth import SocialMediaAuth
from utils.UserPostHistory import UserPostHistory
from utils.SentimentAnalyzer import SentimentAnalyzer
from utils.social_media_strategy import SocialMediaStrategyGenerator
from dotenv import load_dotenv
<<<<<<< Updated upstream
=======
from utils.social_media_strategy import SocialMediaStrategyGenerator

import textblob
>>>>>>> Stashed changes
import os

import ssl
from flask_cors import CORS
# CORS(app, resources={r"/*": {"origins": "https://localhost:8080"}})

load_dotenv()

app = Flask(__name__)



CORS(app, resources={
    r"/*": {
        "origins": ["http://127.0.0.1:8000", "http://localhost:8000", "https://localhost:8080"],
        "methods": ["GET", "POST", "OPTIONS"],
        "allow_headers": [
            "Content-Type",
            "Authorization",
            "Accept",
            "Origin",
            "X-CSRF-TOKEN",
            "X-Requested-With"  # Added this header
        ],
        "supports_credentials": True,
        "expose_headers": ["Content-Type", "Authorization"]
    }
})


# Certificate paths
CERT_FILE = "cert.pem"
KEY_FILE = "key.pem"

# Create SSL context
ssl_context = ssl.create_default_context(purpose=ssl.Purpose.CLIENT_AUTH)
ssl_context.load_cert_chain(certfile=CERT_FILE, keyfile=KEY_FILE)

# Initialize core services
auth = SocialMediaAuth(
    app_id=os.getenv("FACEBOOK_APP_ID"),
    app_secret=os.getenv("FACEBOOK_APP_SECRET"),
    app_id_linkedin=os.getenv("LK_CLIENT_ID"),
    app_secret_linkedin=os.getenv("LK_CLIENT_SECRET"),
    redirect_uri='https://localhost:8443/oauth/callback' # Redirect Back to Flask Application Server.
)
post_history = UserPostHistory()
mixtral_client = MixtralClient()

# Create an instance of SentimentAnalyzer
sentiment_analyzer = SentimentAnalyzer()
api_key = os.environ.get("GROQ_API_KEY")
print(api_key)
strategy_generator = SocialMediaStrategyGenerator(api_key=api_key)

# Load API key from environment variables
DEEPSEEK_API_KEY = os.getenv("DEEPSEEK_API_KEY")

# Initialize the strategy generator
strategy_generator = SocialMediaStrategyGenerator(api_key=DEEPSEEK_API_KEY)

@app.route('/', methods=['GET'])
def hello():
    """Simple test endpoint"""
    return jsonify({
        'message': 'Hello! The HTTPS API is working',
        'status': 'success',
        'version': '1.0'
    })

@app.route('/hello', methods=['POST'])
def hello_post():
    """Test POST endpoint with name parameter"""
    data = request.json
    name = data.get('name', 'World')
    return jsonify({
        'message': f'Hello, {name}!',
        'status': 'success',
        'received_data': data
    })

@app.route('/auth/facebook', methods=['GET'])
def facebook_auth():
    """Generate Facebook authentication URL"""

    print(os.getenv("FACEBOOK_APP_ID"))
    print(os.getenv("FACEBOOK_APP_SECRET"))
    auth_url = auth.generate_auth_url('facebook')
    return jsonify({'auth_url': auth_url})

@app.route('/auth/instagram', methods=['GET'])
def instagram_auth():
    
    """Generate Instagram authentication URL"""
    auth_url = auth.generate_auth_url('instagram')
    return jsonify({'auth_url': auth_url})

@app.route('/auth/linkedin', methods=['GET'])
def linkedin_auth():
    """Generate LinkedIn authentication URL"""
    auth_url = auth.generate_auth_url('linkedin')
    return jsonify({'auth_url': auth_url})

@app.route('/oauth/callback', methods=['GET'])
def oauth_callback():
    """Handle OAuth callback"""
    code = request.args.get('code')
    print(f"Received Code: {code}")
    if not code:
        return jsonify({'error': 'No code provided'}), 400
    
    result = auth.get_access_token(code)
    
    if result:
        # Redirect to Laravel's callback route with the access token
        access_token = result['access_token']
        print(f"Redirect URL: {result['redirect_url']}")
        print(f"Access Token: {access_token}")
        return redirect(result['redirect_url'])
    return jsonify({'error': 'Failed to get access token'}), 400

# In your Flask API
@app.route('/facebook/pages', methods=['GET'])
def get_facebook_pages():
    """Get list of Facebook pages"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401
    
    try:
        fb_manager = FacebookManager(access_token)
        pages = fb_manager.get_pages()
        
        # Clean up the pages data
        clean_pages = [{
            'id': page['id'],
            'name': page['name'],
            'access_token': page['access_token'].strip()  # Remove whitespace
        } for page in pages]
        
        return jsonify({'pages': clean_pages})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/facebook/post', methods=['POST'])
def post_to_facebook():
    """Post content to Facebook"""
    data = request.json
    required_fields = ['page_id', 'page_token', 'image_url', 'message']
    if not all(field in data for field in required_fields):
        return jsonify({'error': 'Missing required fields'}), 400
    
    fb_manager = FacebookManager(data['page_token'])
    print (data['page_id'],data['image_url'],data['message'])
    result = fb_manager.post_content(
        data['page_id'],
        data['page_token'],
        data['image_url'],
        data['message']
    )
    
    if result:
        post_history.add_post('Facebook', result)
        return jsonify({'success': True, 'post_id': result.get('id')})
    return jsonify({'error': 'Failed to post content'}), 400

@app.route('/content/analyze', methods=['POST'])
def analyze_content():
    """Analyze text content and generate purpose/hashtags"""
    data = request.json
    if not data or 'text' not in data:
        return jsonify({
            'status': 'error',
            'message': 'Text content is required'
        }), 400

    try:
        result = mixtral_client.process_text(data['text'])
        return jsonify({
            'status': 'success',
            'purpose': result['purpose'],
            'hashtags': result['hashtags']
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/content/top-descriptions', methods=['POST'])
def get_top_descriptions():
    """Get top performing descriptions for a hashtag"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401

    data = request.json
    if not data or 'hashtag' not in data:
        return jsonify({
            'status': 'error',
            'message': 'Hashtag is required'
        }), 400

    try:
        # 1. Get Instagram account ID
        ig_manager = InstagramManager(access_token)
        accounts = ig_manager.get_accounts()
        
        if not accounts:
            return jsonify({
                'status': 'error',
                'message': 'No Instagram accounts found'
            }), 404

        ig_user_id = accounts[0]['instagram_account_id']
        
        # 2. Get posts for the hashtag
        content_analyzer = InstagramContentAnalyzer(access_token)
        hashtag_posts = content_analyzer.get_top_posts(ig_user_id, data['hashtag'])
        
        if not hashtag_posts:
            return jsonify({
                'status': 'error',
                'message': f'No posts found for hashtag #{data["hashtag"]}'
            }), 404
        
        # 3. Get top performing posts from the hashtag posts
        top_performing_posts = content_analyzer.get_top_performing_posts(hashtag_posts)
        
        # 4. Analyze the top performing posts
        analysis = content_analyzer.analyze_descriptions(top_performing_posts)
        
        return jsonify({
            'status': 'success',
            'data': {
                'hashtag': data['hashtag'],
                'top_posts': [{
                    'caption': post['caption'],
                    'engagement': {
                        'likes': post['likes'],
                        'comments': post['comments'],
                        'total_engagement': post['engagement_score']
                    },
                    'permalink': post.get('permalink', '')
                } for post in top_performing_posts],
                'analysis': {
                    'common_phrases': analysis['common_phrases'],
                    'emoji_usage': analysis['emoji_usage'],
                    'structure_patterns': analysis['structure_patterns'],
                    'avg_length': analysis['avg_length']
                }
            }
        })

    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/content/optimize', methods=['POST'])
def optimize_content():
    """Generate optimized content based on purpose and examples"""
    data = request.json
    if not data or 'purpose' not in data:
        return jsonify({
            'status': 'error',
            'message': 'Purpose is required'
        }), 400

    try:
        # Get descriptions if provided, otherwise use empty list
        descriptions = data.get('descriptions', [])
        
        # Generate optimized response using MixtralClient
        optimized_response = mixtral_client.generate_optimized_response(
            data['purpose'],
            descriptions
        )
        
        return jsonify({
            'status': 'success',
            'optimized_content': optimized_response
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/content/analyze-performance', methods=['POST'])
def analyze_post_performance():
    """Analyze post performance metrics"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401

    data = request.json
    if not data or 'post_id' not in data:
        return jsonify({
            'status': 'error',
            'message': 'Post ID is required'
        }), 400

    try:
        ig_manager = InstagramManager(access_token)
        performance_metrics = ig_manager.get_post_performance(data['post_id'])
        
        return jsonify({
            'status': 'success',
            'metrics': performance_metrics
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/post-history', methods=['GET'])
def get_post_history():
    """Get posting history"""
    history = post_history.get_history()
    return jsonify({'history': history})

@app.route('/instagram/accounts', methods=['GET'])
def get_instagram_accounts():
    """Get Instagram business accounts"""
   
    access_token = request.headers.get('Authorization')
   
    if not access_token:
        return jsonify({
            'status': 'error',
            'message': 'No access token provided'
        }), 401
    
    try:
        ig_manager = InstagramManager(access_token)
        accounts = ig_manager.get_accounts()
        
        debug_info = ig_manager.debug_response if hasattr(ig_manager, 'debug_response') else None
        
        if accounts:
            return jsonify({
                'status': 'success',
                'accounts': accounts,
                'debug_info': debug_info
            })
        return jsonify({
            'status': 'error',
            'message': 'No Instagram business accounts found for your Facebook pages',
            'debug_info': debug_info
        }), 404
        
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e),
            'debug_info': debug_info if 'debug_info' in locals() else None
        }), 400

@app.route('/instagram/post', methods=['POST'])
def post_to_instagram():
    """Post content to Instagram"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401
    
    data = request.json
    required_fields = ['ig_user_id', 'image_url', 'caption']
    if not all(field in data for field in required_fields):
        return jsonify({
            'status': 'error',
            'message': f'Missing required fields. Required: {required_fields}'
        }), 400
    
    ig_manager = InstagramManager(access_token)
    result = ig_manager.post_content(
        data['ig_user_id'],
        data['image_url'],
        data['caption']
    )
    
    if result:
        post_history.add_post('Instagram', result)  # If you're using post history
        return jsonify({
            'status': 'success',
            'post_id': result.get('id'),
            'result': result
        })
    return jsonify({
        'status': 'error',
        'message': 'Failed to post content'
    }), 400

@app.route('/instagram/hashtags', methods=['POST'])
def get_trending_hashtags():
    """Get trending hashtags for a seed hashtag"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401
    
    data = request.json  # Get data from JSON body
    if not data:
        return jsonify({
            'status': 'error',
            'message': 'No JSON body provided'
        }), 400
    
    ig_user_id = data.get('ig_user_id')
    hashtag = data.get('hashtag')
    
    if not ig_user_id or not hashtag:
        return jsonify({
            'status': 'error',
            'message': 'Both ig_user_id and hashtag are required in request body'
        }), 400
    
    try:
        ig_manager = InstagramManager(access_token)
        trending_hashtags = ig_manager.get_trending_hashtags(ig_user_id, hashtag)
        
        if trending_hashtags:
            return jsonify({
                'status': 'success',
                'seed_hashtag': hashtag,
                'trending_hashtags': [
                    {'hashtag': tag, 'count': count} 
                    for tag, count in trending_hashtags
                ]
            })
        return jsonify({
            'status': 'error',
            'message': 'No trending hashtags found'
        }), 404
        
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/content/generate-optimized', methods=['POST'])
def generate_optimized_content():
    """Generate optimized content using enhanced pipeline"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401

    data = request.json
    if not data or 'text' not in data:
        return jsonify({
            'status': 'error',
            'message': 'Text content is required'
        }), 400

    try:
        # 1. Get Instagram account ID
        ig_manager = InstagramManager(access_token)
        accounts = ig_manager.get_accounts()
        
        if not accounts:
            return jsonify({
                'status': 'error',
                'message': 'No Instagram accounts found'
            }), 404

        ig_user_id = accounts[0]['instagram_account_id']

        # 2. Analyze text to get purpose and hashtags
        mixtral_analysis = mixtral_client.process_text(data['text'])
        hashtags = mixtral_analysis['hashtags']
        
        # 3. Get top posts for all extracted hashtags
        content_analyzer = InstagramContentAnalyzer(access_token)
        top_posts = content_analyzer.get_top_posts_for_hashtags(ig_user_id, hashtags)
        
        # 4. Get optimized set of example posts
        example_posts = content_analyzer.get_optimized_examples(top_posts, num_examples=4)
        
        # 5. Analyze the selected examples
        analysis = content_analyzer.analyze_descriptions(example_posts)
        
        # 6. Generate template based on analysis
        template = content_analyzer.generate_description_template(analysis)
        
        # 7. Generate optimized content
        optimized_content = mixtral_client.generate_optimized_response(
            purpose=mixtral_analysis['purpose'],
            descriptions=[{'caption': post['caption']} for post in example_posts]
        )

        return jsonify({
            'status': 'success',
            'analysis': {
                'purpose': mixtral_analysis['purpose'],
                'suggested_hashtags': hashtags,
                'example_posts': example_posts,
                'common_phrases': analysis['common_phrases'],
                'emoji_usage': analysis['emoji_usage'],
                'structure_patterns': analysis['structure_patterns']
            },
            'template': template,
            'optimized_content': optimized_content,
            'metrics': {
                'avg_length': analysis['avg_length'],
                'engagement_patterns': analysis.get('engagement_correlation', {})
            }
        })

    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

# @app.route('/linkedin/companies', methods=['GET'])
# def get_linkedin_companies():
#     """Get list of LinkedIn companies user has access to"""
#     access_token = request.headers.get('Authorization')
#     if not access_token:
#         return jsonify({'error': 'No access token provided'}), 401
    
#     try:
#         linkedin_manager = LinkedInManager(access_token)
#         companies = linkedin_manager.get_companies() # Companies function remoeved for now as we dont have permission for organization_social scope.
#         return jsonify({
#             'status': 'success',
#             'companies': companies
#         })
#     except Exception as e:
#         return jsonify({
#             'status': 'error',
#             'message': str(e)
#         }), 500

@app.route('/linkedin/post', methods=['POST'])
def post_to_linkedin():
    """Post content to LinkedIn profile or company page"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401
    
    data = request.json
    if not data or 'content' not in data:
        return jsonify({
            'status': 'error',
            'message': 'Content is required'
        }), 400
    
    try:
        linkedin_manager = LinkedInManager(access_token)
        
        # Get optional parameters
        company_id = data.get('company_id')
        media_file = data.get('media_file')  # Should be base64 encoded if present
        media_type = data.get('media_type')  # 'image' or 'video'
        
        # Convert base64 to bytes if media file is present
        media_bytes = None
        if media_file:
            import base64
            media_bytes = base64.b64decode(media_file)
        
        result = linkedin_manager.post_content(
            content=data['content'],
            media_file=media_bytes,
            media_type=media_type,
            company_id=company_id
        )
        
        # Add to post history if you're tracking it
        post_history.add_post('LinkedIn', result)
        
        return jsonify({
            'status': 'success',
            'post_id': result.get('id'),
            'result': result
        })
        
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/linkedin/profile', methods=['GET'])
def get_linkedin_profile():
    """Get LinkedIn user profile information"""
    access_token = request.headers.get('Authorization')
    if not access_token:
        return jsonify({'error': 'No access token provided'}), 401
    
    try:
        linkedin_manager = LinkedInManager(access_token)
        profile = linkedin_manager.get_user_profile()
        return jsonify({
            'status': 'success',
            'profile': profile
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/post/comments', methods=['POST'])
def get_post_comments():
    """Get comments for a specific post"""
    try:
        # Validate request
        access_token = request.headers.get('Authorization')
        if not access_token:
            return jsonify({
                'status': 'error',
                'message': 'No access token provided'
            }), 401

        data = request.json
        if not data or 'post_id' not in data or 'platform' not in data:
            return jsonify({
                'status': 'error',
                'message': 'Post ID and platform are required'
            }), 400

        post_id = data['post_id']
        platform = data['platform'].lower()
        limit = data.get('limit', 50)

        # Fetch comments based on platform
        comments = []
        if platform == 'instagram':
            ig_manager = InstagramManager(access_token)
            comments = ig_manager.get_post_comments(post_id, limit)
        elif platform == 'facebook':
            fb_manager = FacebookManager(access_token)
            comments = fb_manager.get_post_comments(post_id, limit)
        else:
            return jsonify({
                'status': 'error',
                'message': 'Unsupported platform'
            }), 400

        return jsonify({
            'status': 'success',
            'comments': comments,
            'count': len(comments)
        })

    except Exception as e:
       
        return jsonify({
            'status': 'error',
            'message': 'An error occurred while fetching comments'
        }), 500

@app.route('/post/sentiment-analysis', methods=['POST'])
def analyze_post_comments():
    """Simple sentiment analysis of post comments"""
    try:
        # Validate request
        access_token = request.headers.get('Authorization')
        if not access_token:
            return jsonify({
                'status': 'error',
                'message': 'No access token provided'
            }), 401

        data = request.json
        if not data or 'post_id' not in data or 'platform' not in data:
            return jsonify({
                'status': 'error',
                'message': 'Post ID and platform are required'
            }), 400

        post_id = data['post_id']
        platform = data['platform'].lower()
        
        # Debug info
        print(f"Analyzing comments for {platform} post: {post_id}")

        # Fetch comments
        comments = []
        try:
            if platform == 'instagram':
                ig_manager = InstagramManager(access_token)
                comments = ig_manager.get_post_comments(post_id)
            elif platform == 'facebook':
                fb_manager = FacebookManager(access_token)
                comments = fb_manager.get_post_comments(post_id)
            else:
                return jsonify({
                    'status': 'error',
                    'message': 'Unsupported platform'
                }), 400
                
            print(f"Retrieved {len(comments)} comments")
        except Exception as e:
            print(f"Error fetching comments: {str(e)}")
            return jsonify({
                'status': 'error',
                'message': f'Error fetching comments: {str(e)}'
            }), 500

        # Perform sentiment analysis
        try:
            analyzer = SentimentAnalyzer()
            analysis = analyzer.analyze_comments(comments)
            
            return jsonify({
                'status': 'success',
                'overall_sentiment': analysis['overall_sentiment'],
                'comment_count': analysis['comment_count']
            })
        except Exception as e:
            print(f"Error analyzing comments: {str(e)}")
            return jsonify({
                'status': 'error',
                'message': f'Error in sentiment analysis: {str(e)}'
            }), 500

    except Exception as e:
        print(f"Unexpected error in analyze_post_comments: {str(e)}")
        return jsonify({
            'status': 'error',
            'message': f'An unexpected error occurred: {str(e)}'
        }), 500

<<<<<<< Updated upstream
@app.route('/generate-strategy', methods=['POST'])
def generate_strategy():
    """
    Endpoint to generate a social media marketing strategy.
    """
    try:
        # Set a longer timeout for this long-running operation
        # Get JSON data from request
        data = request.get_json()
        
        # Check for required fields
        required_fields = ['business_type', 'target_demographics', 'platform', 'business_goals']
        for field in required_fields:
            if field not in data:
                return jsonify({"error": f"Missing required field: {field}"}), 400
        
        # Prepare kwargs for the generator
        kwargs = {field: data.get(field) for field in required_fields}
        
        # Add optional fields if they exist
        optional_fields = ['content_preferences', 'budget', 'timeframe', 'current_challenges']
        for field in optional_fields:
            if field in data:
                kwargs[field] = data[field]
        
        # Generate the strategy (this is the long-running operation)
        print(f"Starting strategy generation for business type: {data['business_type']}")
        strategy = strategy_generator.generate_strategy(**kwargs)
        print(f"Strategy generation complete, length: {len(strategy)}")
        
        # Return the generated strategy
        return jsonify({
            "status": "success",
            "strategy": strategy
        })
    except Exception as e:
        print(f"Error generating strategy: {str(e)}")
        return jsonify({
            "status": "error",
            "message": f"An error occurred: {str(e)}"
=======
@app.route('/business/generate-strategy', methods=['POST'])
def generate_business_strategy():
    """Generate a social media business strategy"""
    try:
        # Get API key from environment
        DEEPSEEK_API_KEY = os.getenv("DEEPSEEK_API_KEY")
        print(f"API Key available: {bool(DEEPSEEK_API_KEY)}")
        
        # Initialize strategy generator with the API key
        from utils.social_media_strategy import SocialMediaStrategyGenerator
        strategy_generator = SocialMediaStrategyGenerator(api_key=DEEPSEEK_API_KEY)
        
        # Validate request
        data = request.json
        if not data:
            return jsonify({
                'status': 'error',
                'message': 'No data provided'
            }), 400
            
        # Required parameters
        required_fields = ['business_type', 'target_demographics', 'platform', 'business_goals']
        for field in required_fields:
            if field not in data:
                return jsonify({
                    'status': 'error', 
                    'message': f'Missing required field: {field}'
                }), 400
        
        print(f"Generating strategy for: {data['business_type']} on {data['platform']}")
        
        # Generate strategy
        strategy = strategy_generator.generate_strategy(
            business_type=data['business_type'],
            target_demographics=data['target_demographics'],
            platform=data['platform'],
            business_goals=data['business_goals'],
            content_preferences=data.get('content_preferences'),
            budget=data.get('budget'),
            timeframe=data.get('timeframe'),
            current_challenges=data.get('current_challenges')
        )
        
        print(f"Strategy generated, length: {len(strategy)}")
        
        return jsonify({
            'status': 'success',
            'strategy': strategy
        })
        
    except Exception as e:
        print(f"Error generating business strategy: {str(e)}")
        return jsonify({
            'status': 'error',
            'message': f'Failed to generate strategy: {str(e)}'
>>>>>>> Stashed changes
        }), 500

if __name__ == '__main__':
    
    app.run(
        host='localhost',    # Changed from 0.0.0.0 to localhost
        port=8443,          # Changed from 443 to 8443 (no need for admin privileges)
        ssl_context=(CERT_FILE, KEY_FILE),
        debug=True
    )