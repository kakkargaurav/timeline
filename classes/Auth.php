<?php

/**
 * Basic Authentication Class
 * 
 * Handles simple username/password authentication using environment variables
 * Only protects web interface, not API endpoints
 */
class Auth
{
    private static $sessionStarted = false;

    /**
     * Start session if not already started
     */
    private static function ensureSession()
    {
        if (!self::$sessionStarted && session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$sessionStarted = true;
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        self::ensureSession();
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    /**
     * Authenticate user with credentials
     */
    public static function authenticate($username, $password)
    {
        $validUsername = getenv('APP_USER') ?: 'admin';
        $validPassword = getenv('APP_PASS') ?: 'password';

        if ($username === $validUsername && $password === $validPassword) {
            self::ensureSession();
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['login_time'] = time();
            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public static function logout()
    {
        self::ensureSession();
        $_SESSION = array();
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        self::$sessionStarted = false;
    }

    /**
     * Require authentication for web pages
     * Redirects to login if not authenticated
     */
    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            self::redirectToLogin();
        }
    }

    /**
     * Redirect to login page
     */
    public static function redirectToLogin()
    {
        $loginUrl = 'login.php';
        $currentUrl = $_SERVER['REQUEST_URI'];
        
        // Don't redirect if already on login page
        if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
            // Store the original URL to redirect back after login
            self::ensureSession();
            $_SESSION['redirect_after_login'] = $currentUrl;
            header("Location: $loginUrl");
            exit();
        }
    }

    /**
     * Get logged in username
     */
    public static function getUsername()
    {
        self::ensureSession();
        return $_SESSION['username'] ?? null;
    }

    /**
     * Get login time
     */
    public static function getLoginTime()
    {
        self::ensureSession();
        return $_SESSION['login_time'] ?? null;
    }

    /**
     * Check if this is an API request
     */
    public static function isApiRequest()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($requestUri, '/api/') !== false || strpos($requestUri, 'api/') === 0;
    }

    /**
     * Protect web pages but allow API access
     */
    public static function protectWebPage()
    {
        // Don't protect API endpoints
        if (self::isApiRequest()) {
            return;
        }

        // Don't protect login page
        if (basename($_SERVER['PHP_SELF']) === 'login.php') {
            return;
        }

        // Require authentication for all other pages
        self::requireAuth();
    }

    /**
     * Get redirect URL after successful login
     */
    public static function getRedirectUrl()
    {
        self::ensureSession();
        $redirectUrl = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        return $redirectUrl;
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken()
    {
        self::ensureSession();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token)
    {
        self::ensureSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}