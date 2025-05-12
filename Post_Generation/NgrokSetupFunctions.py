import os
import shutil
import requests
import time
from PIL import Image
import subprocess
import signal

def check_ngrok_auth():
    """Verify ngrok is authenticated"""
    try:
        result = subprocess.run(["ngrok", "config", "check"], capture_output=True, text=True)
        if "valid" not in result.stdout.lower():
            raise Exception("Ngrok not authenticated. Run 'ngrok authtoken <YOUR_TOKEN>' first")
        print("[DEBUG] Ngrok authentication verified")
    except FileNotFoundError:
        raise Exception("Ngrok not installed or not in PATH")

def kill_processes_on_port(port):
    """Kill any processes using the specified port"""
    try:
        if os.name == 'nt':  # Windows
            print(f"[DEBUG] Checking port {port} processes...")
            
            # Find processes using the port
            netstat = subprocess.run(
                f"netstat -ano | findstr :{port}",
                shell=True,
                capture_output=True,
                text=True
            )
            
            if netstat.returncode == 0:
                print(f"[DEBUG] Found processes using port {port}:")
                print(netstat.stdout)
                
                # Extract PIDs from netstat output
                pids = set()
                for line in netstat.stdout.split('\n'):
                    if f":{port}" in line and "LISTENING" in line:
                        parts = line.strip().split()
                        if len(parts) >= 5:
                            pids.add(parts[-1])
                
                # Kill specific PIDs
                for pid in pids:
                    print(f"[DEBUG] Killing PID {pid}")
                    subprocess.run(f"taskkill /F /PID {pid}", shell=True)
            else:
                print(f"[DEBUG] No processes found on port {port}")
                
        else:  # Unix-like systems
            subprocess.run(f"lsof -ti :{port} | xargs kill -9", shell=True)
            
    except Exception as e:
        print(f"[DEBUG] Error killing processes: {e}")

def is_local_server_ready(port, max_retries=5, delay=1):
    """Check if local HTTP server is responding"""
    for i in range(max_retries):
        try:
            response = requests.get(f"http://localhost:{port}", timeout=2)
            if response.status_code < 500:
                print(f"[DEBUG] Local server on port {port} is ready")
                return True
        except Exception as e:
            print(f"[DEBUG] Waiting for local server... (attempt {i+1}/{max_retries}) - {str(e)}")
            time.sleep(delay)
    return False

def wait_for_ngrok_tunnel(max_retries=10, retry_delay=2):
    """Wait for ngrok tunnel to be ready"""
    for i in range(max_retries):
        try:
            print(f"[DEBUG] Checking ngrok tunnel (attempt {i+1}/{max_retries})")
            response = requests.get("http://localhost:4040/api/tunnels", timeout=5)
            tunnels = response.json()['tunnels']
            if tunnels:
                tunnel_url = tunnels[0]['public_url']
                print(f"[DEBUG] Ngrok tunnel is ready: {tunnel_url}")
                return True, tunnel_url
        except Exception as e:
            print(f"[DEBUG] Tunnel not ready yet... ({str(e)})")
        time.sleep(retry_delay)
    return False, None

def setup_ngrok_tunnel(file_path):
    """
    Sets up an ngrok tunnel for serving a file.
    
    Args:
        file_path: Path to the file to be served
        
    Returns:
        tuple: (success, result_dict) where result_dict contains either:
            - On success: {'public_url': url, 'temp_file_path': str}
            - On failure: {'error': error_message}
    """
    temp_file_path = None
    http_server = None
    ngrok_process = None
    
    try:
        # Verify ngrok authentication first
        check_ngrok_auth()
        
        # Clean up any existing processes
        kill_processes_on_port(8080)
        
        # Create a directory to serve files
        temp_media_dir = os.path.abspath('temp_media')
        os.makedirs(temp_media_dir, exist_ok=True)
        print(f"[DEBUG] Serving from directory: {temp_media_dir}")
        
        # Verify input file exists
        if not os.path.exists(file_path):
            raise Exception(f"File not found: {file_path}")
            
        # Prepare the file (resize if image)
        file_basename = os.path.basename(file_path)
        temp_file_path = os.path.join(temp_media_dir, file_basename)
        
        try:
            # Try to resize if it's an image
            img = Image.open(file_path)
            print(f"[DEBUG] Resizing image to 1280x970 pixels")
            img = img.resize((1280, 970), Image.LANCZOS)
            img.save(temp_file_path)
            print("[DEBUG] Image resized and saved")
        except Exception as img_error:
            print(f"[DEBUG] Not an image or resize failed, copying original: {img_error}")
            shutil.copy(file_path, temp_file_path)
        
        PORT = 8080
        
        # Start HTTP server
        print(f"[DEBUG] Starting HTTP server on port {PORT}")
        http_server = subprocess.Popen(
            ['python', '-m', 'http.server', str(PORT)],
            cwd=temp_media_dir,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE
        )
        
        # Verify server is running
        if not is_local_server_ready(PORT):
            raise Exception("Local HTTP server failed to start")
        
        # Start ngrok (if not already running)
        try:
            requests.get("http://localhost:4040/api/tunnels", timeout=2)
            print("[DEBUG] Using existing ngrok process")
        except:
            print("[DEBUG] Starting new ngrok process")
            ngrok_process = subprocess.Popen(
                ["ngrok", "http", str(PORT)],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE
            )
        
        # Wait for tunnel
        tunnel_ready, ngrok_url = wait_for_ngrok_tunnel()
        if not tunnel_ready:
            raise Exception("Ngrok tunnel failed to initialize")
        
        # Ensure HTTPS
        if ngrok_url.startswith('http:'):
            ngrok_url = ngrok_url.replace('http:', 'https:')
        
        # Construct final URL
        base_url = ngrok_url.rstrip('/')
        public_url = f"{base_url}/{file_basename}"
        print(f"[DEBUG] Final public URL: {public_url}")
        
        # Test URL accessibility
        # print("[DEBUG] Testing URL accessibility...")
        # try:
        #     response = requests.get(
        #         public_url,
        #         headers={
        #             'ngrok-skip-browser-warning': 'true',
        #             'User-Agent': 'Mozilla/5.0'
        #         },
        #         allow_redirects=True,
        #         timeout=10
        #     )
        #     if response.status_code != 200:
        #         raise Exception(f"URL test failed with status {response.status_code}")
        # except Exception as e:
        #     print(f"[DEBUG] Full test error: {str(e)}")
        #     raise
        
        return True, {
            'public_url': public_url,
            'temp_file_path': temp_file_path,
            'http_server': http_server,
            'ngrok_process': ngrok_process
        }
        
    except Exception as e:
        print(f"[ERROR] Setup failed: {str(e)}")
        # Cleanup
        if http_server:
            http_server.terminate()
        if ngrok_process:
            ngrok_process.terminate()
        if temp_file_path and os.path.exists(temp_file_path):
            try:
                os.remove(temp_file_path)
            except:
                pass
        return False, {'error': str(e)}

def cleanup_resources(result_dict):
    """Clean up all created resources"""
    if not result_dict:
        return
        
    try:
        # Clean up processes
        if 'http_server' in result_dict and result_dict['http_server']:
            result_dict['http_server'].terminate()
        if 'ngrok_process' in result_dict and result_dict['ngrok_process']:
            result_dict['ngrok_process'].terminate()
        
        # Clean up file
        if 'temp_file_path' in result_dict and result_dict['temp_file_path']:
            if os.path.exists(result_dict['temp_file_path']):
                os.remove(result_dict['temp_file_path'])
                print(f"[DEBUG] Removed {result_dict['temp_file_path']}")
    except Exception as e:
        print(f"[DEBUG] Cleanup error: {e}")

if __name__ == "__main__":
    import argparse
    
    parser = argparse.ArgumentParser(description='Test ngrok tunnel setup')
    parser.add_argument('file_path', help='Path to the file to serve')
    parser.add_argument('--wait', type=int, default=30, help='Seconds to keep tunnel open')
    
    args = parser.parse_args()
    
    print("\n=== Starting Ngrok Tunnel Test ===\n")
    print(f"[DEBUG] Input file: {args.file_path}")
    
    success, result = setup_ngrok_tunnel(args.file_path)
    
    if success:
        print("\n=== Tunnel Setup Successful ===")
        print(f"Public URL: {result['public_url']}")
        print(f"\nKeeping tunnel open for {args.wait} seconds...")
        print("Press Ctrl+C to exit early.")
        
        try:
            time.sleep(args.wait)
        except KeyboardInterrupt:
            print("\nEarly exit requested...")
        finally:
            cleanup_resources(result)
    else:
        print("\n❌ Tunnel setup failed!")
        print(f"Error: {result.get('error', 'Unknown error')}")
        cleanup_resources(result)
    
    print("\n=== Test Complete ===\n")