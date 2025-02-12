<?php
namespace StudentManage\Rest_Api;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_User;

class Login extends WP_REST_Controller
{

    const RATE_LIMIT = 3;
    const LOCKOUT_TIME = 15 * MINUTE_IN_SECONDS;
    public function __construct()
    {
        $this->namespace = 'user-login/v1';
        $this->rest_base = 'login';
    }

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            $this->rest_base,
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'user_login_handle'],
                'permission_callback' => [$this, 'login_permissions_check'],
                'args'                => $this->get_endpoint_args(),

            ]

        );
    }

    public function login_permissions_check( WP_REST_Request $request)
    {
        $ip            = $this->get_client_ip();
        $transient_key = 'login_attempts_' . $ip;
        $attempts      = get_transient($transient_key) ?: 0;

        if ($attempts >= self::RATE_LIMIT) {
            return new WP_Error(
                'too_many_request',
                __('Too many login attempts. Please try again later.', 'student-manage'),
                ['status' => 429]
            );
        }

        return true;

    }

    public function user_login_handle(WP_REST_Request $request)
    {
        $ip            = $this->get_client_ip();
        $transient_key = 'login_attempts_' . $ip;

        $identifier = sanitize_text_field($request['identifier']);
        $password   = sanitize_text_field($request['password']);
         $remember   = filter_var($request['remember'], FILTER_VALIDATE_BOOLEAN);

        $user = $this->authenticate_user($identifier, $password);

        if (is_wp_error($user)) {
            $attempts = get_transient($transient_key) ?: 0;
            set_transient($transient_key, $attempts + 1, self::LOCKOUT_TIME);

            return new WP_Error(
                'authentication_failed',
                __('Invalid credentials', 'student-manage'),
                ['status' => 401]

            );
        }

        // Generate secure session token
        $token = $this->generate_session_token($user);

        /// Set login session (cookie-based for WordPress)
        wp_set_auth_cookie($user->ID, $remember);
        wp_set_current_user($user->ID);

        // Return response without sensitive data
        return new WP_REST_Response([
            'success'    => true,
            'message'    => __('Login successful.', 'student-manage'),
            'user_id'    => $user->ID,
            'user_email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles'      => $user->roles,
            'token'      => $token,
            'expires'    => time() + (DAY_IN_SECONDS * 7),
        ], 200);

    }

    private function authenticate_user($identifier, $password)
    {
        if (is_email($identifier)) {
            $user = get_user_by('email', $identifier);
        } else {
            $user = get_user_by('login', $identifier);
        }

        if (! $user || ! wp_check_password($password, $user->user_pass, $user->ID)) {
            return new WP_Error('auth_failed', __('Invalid credentials', 'student-manage'));
        }

        return $user;

    }

    public function get_endpoint_args()
    {
        return [
            'identifier' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function ($value) {
                    return ! empty(trim($value));
                },

            ],

            'password'   => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function ($value) {
                    return ! empty(trim($value));
                },
            ],

            'remember'   => [
                'required' => false,
                'type'     => 'boolean',
            ],

        ];
    }

    private function get_client_ip()
    {
        return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_REQUEST['REMOTE_ADDR'] ?? '';
    }


    private function generate_session_token(WP_User $user) {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        
        // Store hashed token in user meta
        $hashed_token = wp_hash_password($token);
        update_user_meta($user->ID, 'api_auth_token', $hashed_token);
        update_user_meta($user->ID, 'api_auth_token_expires', time() + (DAY_IN_SECONDS * 7));
        
        return $token;
    }
}
