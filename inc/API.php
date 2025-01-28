<?php
    namespace StudentManage;
    class API{
        public function __construct()
        {
            // Register REST API routes
            add_action('rest_api_init', [$this, 'register_rest_api']);
        }

        public function register_rest_api()
        {
            $student = new Rest_Api\Student();
            $student->register_routes();
        }
    }