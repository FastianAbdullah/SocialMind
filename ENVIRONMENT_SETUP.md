# Environment Configuration Setup

This project now supports dynamic domain configuration that automatically switches between local development and production environments.

## Environment Files Created

### Root Directory
- `.env.local` - Local development configuration
- `.env.production` - Production configuration
- `switch-env.sh` - Script to switch between environments

### Post_Generation Directory
- `Post_Generation/.env.local` - Python backend local configuration
- `Post_Generation/.env.production` - Python backend production configuration

## How to Use

### 1. Switch to Local Environment
```bash
./switch-env.sh local
```
This will:
- Set `APP_DOMAIN=http://localhost:8000`
- Set `VITE_APP_DOMAIN=http://localhost:8000`
- Set `FLASK_API_URL=http://localhost:8000`

### 2. Switch to Production Environment
```bash
./switch-env.sh production
```
This will:
- Set `APP_DOMAIN=https://socialmidsai.com`
- Set `VITE_APP_DOMAIN=https://socialmidsai.com`
- Set `FLASK_API_URL=https://socialmidsai.com`

### 3. Check Current Environment
```bash
./switch-env.sh
```
This will show the current environment variables.

## Environment Variables

### Domain Configuration
- `APP_DOMAIN` - Used by Python backend for CORS, redirects, and API calls
- `VITE_APP_DOMAIN` - Used by frontend (Vue.js) for API calls and dynamic resources
- `FLASK_API_URL` - Used by Laravel for Flask API communication

### Default Behavior
If no environment variables are set, the application defaults to:
- `http://localhost:8000` for local development

## Files Modified for Dynamic Configuration

1. **Post_Generation/app.py**
   - Uses `APP_DOMAIN` for CORS origins
   - Uses `APP_DOMAIN` for OAuth redirect URIs
   - Uses `APP_DOMAIN` for AI agent base URLs

2. **resources/js/composables/useDynamicResources.js**
   - Uses `VITE_APP_DOMAIN` for loading dynamic CSS/JS files

3. **resources/js/services/instagramService.js**
   - Uses `VITE_APP_DOMAIN` for API base URL

## Adding Your API Keys

Edit the `.env.local` file and add your actual API keys:

```bash
# Social Media API Keys
FACEBOOK_APP_ID=your_actual_facebook_app_id
FACEBOOK_APP_SECRET=your_actual_facebook_app_secret
LK_CLIENT_ID=your_actual_linkedin_client_id
LK_CLIENT_SECRET=your_actual_linkedin_client_secret

# AI API Keys
OPENAI_API_KEY=your_actual_openai_api_key
OPENROUTER_API_KEY=your_actual_openrouter_api_key
```

## Development Workflow

1. **Start Development:**
   ```bash
   ./switch-env.sh local
   npm run dev  # Start frontend
   python Post_Generation/app.py  # Start backend
   ```

2. **Deploy to Production:**
   ```bash
   ./switch-env.sh production
   # Deploy your application
   ```

## Benefits

- ✅ No more manual domain changes
- ✅ Automatic environment detection
- ✅ Easy switching between local and production
- ✅ Consistent configuration across frontend and backend
- ✅ Safe for version control (environment files are separate)

## Troubleshooting

If you encounter issues:

1. **Check current environment:**
   ```bash
   ./switch-env.sh
   ```

2. **Verify environment files exist:**
   ```bash
   ls -la .env*
   ls -la Post_Generation/.env*
   ```

3. **Restart your servers** after switching environments

4. **Clear browser cache** if frontend changes don't appear 