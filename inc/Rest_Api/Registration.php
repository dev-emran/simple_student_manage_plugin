<?php
namespace StudentManage\Rest_Api;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_User_Query;

class Registration extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'user-registration/v1';
        $this->rest_base = 'register';
    }

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            $this->rest_base,
            [

                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'register_user'],
                'permission_callback' => '__return_true',
                'args'                => $this->get_endpoint_args(),
            ]
        );

        register_rest_route(
            $this->namespace,
            'users',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_users'],
                'permission_callback' => [ $this, 'get_users_permissions_check' ],
                'args'                => $this->get_endpoint_args_users_list(),
            ]
        );
    }

    public function get_users_permissions_check($request)
    {
        return current_user_can('list_users');
    }

    public function get_endpoint_args_users_list()
    {
        return [
            'page' => [
                'required' => false,
                'type'     => 'integer',
                'default'  => 1,
                'sanitize_callback' => 'absint',
            ],

            'per_page' => [
                'required' => false,
                'type'     => 'integer',
                'default'  => 10,
                'sanitize_callback' => 'absint',
            ],
        ];
    }

    public function get_users($request)
    {
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 5;

        $args = [
            'number' => $per_page,
            'paged'  => $page,
            'fields' => ['ID', 'user_login', 'user_email', 'display_name', 'user_registered'],
            'orderby' => 'ID',
            'order' => 'DESC',
        ];

        $total_users = count_users();
        $total_users = $total_users['total_users'];

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();

        if (empty($users)) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __('No users found', 'student-manage'),
                ], 404
            );
        }

        $formatted_users = [];
        foreach ($users as $user) {
            $user_data = (array) $user;
            $wp_user = get_userdata($user->ID);
            $user_data['roles'] = $wp_user->roles;
            $formatted_users[] = $user_data;
        }

        $total_pages = ceil($total_users / $per_page);

        return new WP_REST_Response(
            [
                'success' => true,
                'data'   => $formatted_users,
                'pagination' => [
                    'total_users' => $total_users,
                    'total_pages' => $total_pages,
                    'current_page' => (int) $page,
                    'per_page' => (int) $per_page,
                ],
            ], 200
        );
    }

    public function get_endpoint_args()
    {
        return [
            'username' => [
                'required'          => true,
                'type'              => 'string',
                'validate_callback' => function ($param, $request, $key) {
                    return ! empty($param);
                },
            ],
            'email'    => [
                'required'          => true,
                'type'              => 'string',
                'validate_callback' => function ($param, $request, $key) {
                    return is_email($param);
                },
            ],
            'password' => [
                'required'          => true,
                'type'              => 'string',
                'validate_callback' => function ($param, $request, $key) {
                    return ! empty($param);
                },
            ],
        ];
    }

    public function register_user($request)
    {
        $username = sanitize_text_field($request->get_param('username'));
        $email    = sanitize_text_field($request->get_param('email'));
        $password = sanitize_text_field($request->get_param('password'));

        if (username_exists($username)) {
            return new WP_Error(
                'user_exists',
                __('Username already exists', 'student-manage'),
                ['status' => 400]
            );
        }

        if (email_exists($email)) {
            return new WP_Error(
                'user_exists',
                __('Email already exists', 'student-manage'),
                ['status' => 400]
            );
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            return new WP_Error(
                'registration_failed',
                __('Failed to register user', 'student-manage'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __('User registered successfully', 'student-manage'),
                'user_id' => $user_id,
            ]
        );
    }
}
