from social_media_strategy import SocialMediaStrategyGenerator

# Create an instance of the generator
generator = SocialMediaStrategyGenerator(api_key="api_key")

# Generate a strategy
strategy = generator.generate_strategy(
    business_type="Artisanal Bakery",
    target_demographics="Urban professionals aged 25-40 interested in organic food",
    platform="Instagram",
    business_goals="Increase online orders by 30% and grow following by 5,000 in 6 months",
    content_preferences="Short-form videos and high-quality food photography",
    budget="$500 per month",
    timeframe="6 months",
    current_challenges="Low engagement rate and inconsistent posting schedule"
)

# Print or display the strategy
print(strategy) 