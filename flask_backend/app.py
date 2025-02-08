








# ...existing code...)    redirect_uri='https://localhost:8000/oauth/callback'  # Updated to HTTPS    app_secret=os.getenv("FACEBOOK_APP_SECRET"),    app_id=os.getenv("FACEBOOK_APP_ID"),auth = SocialMediaAuth(# ...existing code...