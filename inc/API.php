<?php
    namespace StudentManage;

    use StudentManage\Rest_Api\Login;
    use StudentManage\Rest_Api\Registration;
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

            $register = new Registration();
            $register->register_routes();

            $login = new Login();
            $login->register_routes();
        }
    }