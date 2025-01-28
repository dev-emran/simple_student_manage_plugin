<?php
    namespace StudentManage\Rest_Api;

    if (! defined('ABSPATH')) {
        exit;
    }

    class Student{

        public function __construct()
        {
            
        }
        /**
         * Register REST API route for fetching students
         */
        public function register_student_routes()
        {

            register_rest_route(
                'public/v1', 
                '/students', 
                [
                    'methods'   => 'GET',
                    'callback'  => [$this, 'get_students'],
                    'permission_callback'   => '__return_true',

                ]
            );

            error_log('REST API route registered for students.');
        }


        /**
         * Handle GET request to fetch student data
         *
         * @param \WP_REST_Request $request
         * @return \WP_REST_Response
         */
        public function get_students($request)
        {
            $args = [
                'post_type'      => 'student',
                'post_status'    => 'publish',
                'posts_per_page' => -1, 
            ];

            $query = new \WP_Query($args);
            $students = [];

            if($query->have_posts()){
                while($query->have_posts()){
                    $query->the_post();

                    $students[] = [
                        'id'    => get_the_ID(),
                        'title' => get_the_title(),
                        'student_id'  => get_post_meta(get_the_ID(), 'sm_student_id', true),
                        'class'  => get_post_meta(get_the_ID(), 'sm_student_class', true),
                        'gender'  => get_post_meta(get_the_ID(), 'sm_gender', true),
                    ];
                }

                wp_reset_postdata();
            }

            error_log('REST API route registered for students.');

            return rest_ensure_response($students);
        }

    }