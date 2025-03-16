import os
import shutil  # Add this import for file operations
# Running ngrok libraries
import requests,time
from PIL import Image

def setup_ngrok_tunnel(file_path):
    """
    Sets up an ngrok tunnel for serving a file.
    
    Args:
        file_path: Path to the file to be served
        
    Returns:
        tuple: (success, result_dict) where result_dict contains either:
            - On success: {'public_url': url}
            - On failure: {'error': error_message}
    """
    temp_file_path = None
    try:
        # Create a directory to serve files if it doesn't exist
        temp_media_dir = 'temp_media'
        os.makedirs(temp_media_dir, exist_ok=True)
        print(f"[DEBUG] Created {temp_media_dir} directory")
        
        # Check if file exists
        print(f"[DEBUG] Looking for file: {file_path}")
        if not os.path.exists(file_path):
            print(f"[DEBUG] File not found at: {file_path}")
            return False, {'error': f"File {file_path} not found"}
            
        # Copy file to Post_Generation directory for easier serving
        file_basename = os.path.basename(file_path)
        temp_file_path = os.path.join(temp_media_dir, file_basename)
        
        # Resize the image to 1280x970 pixels before saving to temp_media
        try:
            print(f"[DEBUG] Resizing image to 1280x970 pixels")
            img = Image.open(file_path)
            img = img.resize((1280, 970), Image.LANCZOS)
            
            # Save the resized image to temp_media directory
            print(f"[DEBUG] Saving resized image to: {temp_file_path}")
            img.save(temp_file_path)
            print(f"[DEBUG] Image resized and saved successfully")
            
        except Exception as resize_error:
            print(f"[DEBUG] Error resizing image: {str(resize_error)}")
            # Fall back to copying the original file if resizing fails
            print(f"[DEBUG] Falling back to copying original file to: {temp_file_path}")
            shutil.copy(file_path, temp_file_path)
        
        # Start HTTP server in a separate process, not thread
        import subprocess
        PORT = 8080
        
        # Kill any existing process on port 8080 - Fix for Windows
        try:
            if os.name == 'nt':  # Windows
                print(f"[DEBUG] Attempting to kill any process on port {PORT} (Windows)")
                # Use a safer approach for Windows
                try:
                    # Find PID using netstat
                    netstat_output = subprocess.check_output(f'netstat -ano | findstr :{PORT}', shell=True).decode()
                    print(f"[DEBUG] Netstat output: {netstat_output}")
                    
                    # Extract PID from netstat output if it exists
                    if netstat_output.strip():
                        lines = netstat_output.strip().split('\n')
                        for line in lines:
                            if f":{PORT}" in line:
                                parts = line.strip().split()
                                if len(parts) >= 5:
                                    pid = parts[-1]
                                    print(f"[DEBUG] Found process with PID {pid} on port {PORT}")
                                    subprocess.run(f'taskkill /F /PID {pid}', shell=True, 
                                                  stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
                except Exception as kill_error:
                    print(f"[DEBUG] Error killing process: {str(kill_error)}")
                    # Continue anyway, as the port might not be in use
        except Exception as e:
            print(f"[DEBUG] Exception when trying to kill existing process: {str(e)}")
            # Continue anyway
            
        # Start HTTP server
        print(f"[DEBUG] Starting HTTP server on port {PORT}")
        http_server = subprocess.Popen(['python', '-m', 'http.server', str(PORT)], 
                                      stdout=subprocess.DEVNULL, 
                                      stderr=subprocess.DEVNULL)
        print(f"[DEBUG] HTTP server started with PID: {http_server.pid}")
        
        # Start ngrok with authtoken (you need to set this up)
        # First, check if ngrok is already running
        try:
            print("[DEBUG] Checking if ngrok is already running")
            requests.get("http://localhost:4040/api/tunnels")
            # If the above doesn't throw an exception, ngrok is already running
            print("[DEBUG] ngrok is already running")
        except:
            # Start ngrok
            print("[DEBUG] Starting ngrok")
            ngrok_cmd = ["ngrok", "http", str(PORT)]
                        
            ngrok = subprocess.Popen(ngrok_cmd, 
                                    stdout=subprocess.DEVNULL, 
                                    stderr=subprocess.DEVNULL)
            print(f"[DEBUG] ngrok started with PID: {ngrok.pid}")
            
            # Give ngrok time to start up
            print("[DEBUG] Waiting for ngrok to start up")
            time.sleep(5)
        
        # Get the public URL from ngrok
        print("[DEBUG] Attempting to get ngrok URL")
        max_retries = 3
        ngrok_url = None
        for i in range(max_retries):
            try:
                print(f"[DEBUG] Getting ngrok URL (attempt {i+1}/{max_retries})")
                resp = requests.get("http://localhost:4040/api/tunnels")
                tunnels = resp.json()['tunnels']
                print(f"[DEBUG] Found {len(tunnels)} tunnels")
                if tunnels:
                    ngrok_url = tunnels[0]['public_url']
                    print(f"[DEBUG] Got ngrok URL: {ngrok_url}")
                    break
            except Exception as e:
                print(f"[DEBUG] Error getting ngrok URL (attempt {i+1}/{max_retries}): {str(e)}")
                time.sleep(2)
        
        if not ngrok_url:
            print("[DEBUG] Failed to get ngrok URL after all retries")
            return False, {'error': 'Failed to get ngrok URL'}
        
        # Make sure we use HTTPS URL
        if ngrok_url.startswith('http:'):
            ngrok_url = ngrok_url.replace('http:', 'https:')
            print(f"[DEBUG] Converted to HTTPS URL: {ngrok_url}")
            
        # Use the basename of the temporary file for the public URL
        temp_file_basename = os.path.basename(temp_file_path)
        public_url = f"{ngrok_url}/temp_media/{temp_file_basename}"
        print(f"[DEBUG] Public URL: {public_url}")

        # Test if the URL is accessible
        success, result = test_ngrok_url(public_url)
        if not success:
            return False, result
        
        return True, {'public_url': public_url, 'temp_file_path': temp_file_path}
        
    except Exception as e:
        print(f"[DEBUG] Error in ngrok tunnel setup: {str(e)}")
        return False, {'error': f'Error: {str(e)}'}


def test_ngrok_url(public_url):
    """Test if the Public Url is Accessible"""
    try:
        headers = {
            'ngrok-skip-browser-warning': 'true'
        }
        response = requests.head(public_url, headers=headers, timeout=5)
        if response.status_code != 200:
            return False, {'error': f'URL test failed with status {response.status_code}'}
        return True, {}
    except Exception as e:
        return False, {'error': f'URL test failed: {str(e)}'}


def cleanup_temp_file(temp_file_path):
    """
    Remove temporary file after posting is done
    
    Args:
        temp_file_path: Path to the temporary file to remove
    """
    try:
        if temp_file_path and os.path.exists(temp_file_path):
            os.remove(temp_file_path)
            print(f"[DEBUG] Removed temporary file: {temp_file_path}")
            return True
    except Exception as e:
        print(f"[DEBUG] Error removing temporary file: {str(e)}")
        return False
