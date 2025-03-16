import json
from datetime import datetime
import os
import pytz
from .FacebookManager import FacebookManager
from .InstagramManager import InstagramManager
from .LinkedInManager import LinkedInManager
import logging
from apscheduler.schedulers.background import BackgroundScheduler
from .NgrokSetupFunctions import setup_ngrok_tunnel, cleanup_temp_file
import base64
from dotenv import load_dotenv
import time
import sys

# Load environment variables from Laravel's .env
load_dotenv(os.path.join(os.path.dirname(os.path.dirname(os.path.dirname(__file__))), '.env'))

# Use the logger from app.py
logger = logging.getLogger('SchedulerManager')

class SchedulerManager:
    def __init__(self):
        # Store scheduled posts in memory
        self.scheduled_posts = {}
        
        logger.info("Initializing SchedulerManager")
        
        # Initialize scheduler with UTC (as a base timezone)
        self.scheduler = BackgroundScheduler({
            'apscheduler.executors.default': {
                'class': 'apscheduler.executors.pool:ThreadPoolExecutor',
                'max_workers': '20'
            },
            'apscheduler.job_defaults.coalesce': 'false',
            'apscheduler.job_defaults.max_instances': '3',
            'apscheduler.timezone': 'UTC',  # Base timezone
        })
        
        self.scheduler.start()
        logger.info("APScheduler started successfully")

    def schedule_post_with_data(self, post_id, platform_id, scheduled_time, user_id, post_data, timezone='UTC'):
        """Schedule a post using provided data, no database connection needed"""
        try:
            # Use provided timezone
            user_tz = pytz.timezone(timezone)
            
            logger.info(f"Scheduling post {post_id} for user {user_id} in timezone: {timezone}")

            # Update scheduler's timezone to match user's timezone
            self.scheduler.timezone = user_tz
            
            # Parse the scheduled time in user's timezone
            local_dt = datetime.strptime(scheduled_time, '%Y-%m-%d %H:%M:%S')
            local_dt = user_tz.localize(local_dt)
            
            # Store post data in memory
            self.scheduled_posts[f"{post_id}_{platform_id}"] = {
                'post_id': post_id,
                'platform_id': platform_id,
                'user_id': user_id,
                'scheduled_time': scheduled_time,
                'timezone': timezone,
                'post_data': post_data,
                'status': 'pending'
            }

            # Schedule the job
            self.scheduler.add_job(
                func=self.execute_post,
                trigger='date',
                run_date=local_dt,
                args=[post_id, platform_id],
                id=f'post_{post_id}_{platform_id}',
                name=f'Scheduled Post {post_id} for Platform {platform_id}'
            )

            logger.info(f"Post {post_id} scheduled for {local_dt} ({timezone})")
            
            return True, {
                'post_id': post_id,
                'platform_id': platform_id,
                'scheduled_time': local_dt.isoformat(),
                'status': 'pending'
            }
            
        except Exception as e:
            logger.error(f"Error scheduling post: {str(e)}")
            return False, {'error': str(e)}

    def execute_post(self, post_id, platform_id):
        """Execute the post using data from memory"""
        key = f"{post_id}_{platform_id}"
        if key not in self.scheduled_posts:
            logger.error(f"Post {post_id} for platform {platform_id} not found in memory")
            return
        
        post_data = self.scheduled_posts[key]['post_data']
        timezone = self.scheduled_posts[key]['timezone']
        
        # Convert to user's timezone for logging
        user_tz = pytz.timezone(timezone)
        current_time = datetime.now(user_tz)
        
        logger.info(f"Executing post {post_id} at {current_time} ({timezone})")
        logger.info(f"\n\nEXECUTED Post data: {post_data}\n\n")
        
        # Update status to processing
        self.scheduled_posts[key]['status'] = 'processing'
        self.scheduled_posts[key]['processing_start'] = current_time.isoformat()
        
        try:
            # Handle media if present
            media_url = None
            temp_file_path = None
            
            if post_data.get('file_path'):
                # Set up ngrok tunnel for media
                tunnel_success, tunnel_result = setup_ngrok_tunnel(post_data['file_path'])
                
                if tunnel_success:
                    media_url = tunnel_result['public_url']
                    temp_file_path = tunnel_result.get('temp_file_path')
                    self.scheduled_posts[key]['media_url'] = media_url
                else:
                    raise Exception(f"Failed to create tunnel for media: {tunnel_result['error']}")

            # Execute based on platform
            if platform_id == 1:  # Facebook
                manager = FacebookManager(post_data['access_token'])
                result = manager.post_content(
                    page_id=post_data.get('platform_page_id'),
                    page_token=post_data['access_token'],
                    image_url=media_url,
                    message=post_data['AI_generated_description']
                )

            elif platform_id == 2:  # Instagram
                manager = InstagramManager(post_data['access_token'])
                result = manager.post_content(
                    ig_user_id=post_data.get('platform_page_id'),
                    image_url=media_url,
                    caption=post_data['AI_generated_description']
                )

            elif platform_id == 3:  # LinkedIn
                manager = LinkedInManager(post_data['access_token'])
                media_data = None
                if post_data.get('file_path'):
                    with open(post_data['file_path'], 'rb') as f:
                        media_data = base64.b64encode(f.read()).decode('utf-8')

                result = manager.post_content(
                    content=post_data['AI_generated_description'],
                    media_file=media_data,
                    media_type=post_data.get('media_type', 'image')
                )

            # Update status in memory
            if result:
                self.scheduled_posts[key]['status'] = 'published'
                self.scheduled_posts[key]['result'] = result
                self.scheduled_posts[key]['published_at'] = current_time.isoformat()
                logger.info(f"Successfully published scheduled post {post_id}")
            else:
                raise Exception("No result returned from platform")

            # Clean up temporary file
            if temp_file_path:
                cleanup_temp_file(temp_file_path)

        except Exception as e:
            logger.error(f"Error publishing post {post_id}: {str(e)}")
            self.scheduled_posts[key]['status'] = 'failed'
            self.scheduled_posts[key]['error'] = str(e)
            self.scheduled_posts[key]['error_time'] = current_time.isoformat()
            
            # Try to clean up anyway on error
            if 'temp_file_path' in locals() and temp_file_path:
                cleanup_temp_file(temp_file_path)

    def get_scheduled_posts(self, status=None):
        """Retrieve scheduled posts with optional status filter"""
        if status:
            return [post for post in self.scheduled_posts.values() if post['status'] == status]
        return list(self.scheduled_posts.values())

    def cancel_scheduled_post(self, post_id, platform_id=None):
        """Cancel a scheduled post"""
        try:
            if platform_id:
                key = f"{post_id}_{platform_id}"
                if key in self.scheduled_posts:
                    # Remove from scheduler
                    try:
                        self.scheduler.remove_job(f'post_{post_id}_{platform_id}')
                    except:
                        pass  # Job might have already been executed

                    # Update status
                    self.scheduled_posts[key]['status'] = 'cancelled'
                    return True, "Scheduled post cancelled successfully"
                return False, "Scheduled post not found"
            else:
                # Try to cancel all posts with this post_id
                found = False
                for key in list(self.scheduled_posts.keys()):
                    if key.startswith(f"{post_id}_"):
                        try:
                            self.scheduler.remove_job(key)
                        except:
                            pass
                        self.scheduled_posts[key]['status'] = 'cancelled'
                        found = True
                
                if found:
                    return True, "Scheduled post(s) cancelled successfully"
                return False, "No scheduled posts found with that ID"

        except Exception as e:
            logger.error(f"Error cancelling scheduled post: {str(e)}")
            return False, str(e)

    def get_post_status(self, post_id, platform_id=None):
        """Get the current status of a scheduled post"""
        if platform_id:
            key = f"{post_id}_{platform_id}"
            if key in self.scheduled_posts:
                return self.scheduled_posts[key]
            return None
        else:
            # Return status for all posts with this post_id
            results = []
            for key, data in self.scheduled_posts.items():
                if key.startswith(f"{post_id}_"):
                    results.append(data)
            return results if results else None

    def get_scheduler_status(self):
        """Get current status of the scheduler"""
        try:
            jobs = self.scheduler.get_jobs()
            return {
                'running': self.scheduler.running,
                'job_count': len(jobs),
                'next_run_time': jobs[0].next_run_time if jobs else None,
                'job_list': [{
                    'id': job.id,
                    'name': job.name,
                    'next_run_time': job.next_run_time.isoformat() if job.next_run_time else None,
                    'pending': job.pending
                } for job in jobs]
            }
        except Exception as e:
            logger.error(f"Error getting scheduler status: {e}")
            return None 