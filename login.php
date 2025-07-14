<?php
require_once __DIR__ . '/classes/Auth.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Verify CSRF token
    if (!Auth::verifyCsrfToken($csrfToken)) {
        $error = 'Invalid security token. Please try again.';
    } elseif (Auth::authenticate($username, $password)) {
        // Successful login - redirect to original page or dashboard
        $redirectUrl = Auth::getRedirectUrl();
        header("Location: $redirectUrl");
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}

// Generate CSRF token for the form
$csrfToken = Auth::generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Driveway Timeline</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .login-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background: white;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #f44336;
            text-align: left;
        }

        .info-box {
            background: rgba(102, 126, 234, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            border-left: 4px solid #667eea;
        }

        .info-box h3 {
            margin-bottom: 10px;
            color: #333;
            font-size: 1.1rem;
        }

        .info-box p {
            color: #666;
            font-size: 0.9rem;

        <?php if (isset($_GET['logged_out'])): ?>
            <div class="success-message" style="background: #e8f5e8; color: #2e7d32; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #4CAF50; text-align: left;">
                <strong>Logged Out:</strong> You have been successfully logged out.
            </div>
        <?php endif; ?>
            line-height: 1.5;
        }

        .env-info {
            font-family: monospace;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 0.85rem;
            color: #444;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #888;
            font-size: 0.9rem;
        }

        .api-note {
            background: rgba(76, 175, 80, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #4CAF50;
            text-align: left;
        }

        .api-note strong {
            color: #2e7d32;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Timeline Login</h1>
            <p>Access the Driveway Timeline Dashboard</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <strong>Login Failed:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn">
                🚀 Login to Timeline
            </button>
        </form>

        <div class="api-note">
            <strong>📡 API Access:</strong> All API endpoints remain publicly accessible for third-party integrations.
        </div>

        <div class="info-box">
            <h3>🔧 Configuration</h3>
            <p>Login credentials are configured via environment variables:</p>
            <div class="env-info">
                APP_USER=your_username<br>
                APP_PASS=your_password
            </div>
            <p>Default credentials: admin / password</p>
        </div>

        <div class="footer">
            <p>🏠 <a href="index.php" style="color: #667eea; text-decoration: none;">Back to Timeline</a></p>
            <p style="margin-top: 10px;">Secure access to timeline management</p>
        </div>
    </div>

    <script>
        // Auto-focus username field
        document.getElementById('username').focus();
        
        // Handle form submission feedback
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.querySelector('.login-btn');
            btn.textContent = '🔄 Logging in...';
            btn.disabled = true;
        });
    </script>
</body>
</html>