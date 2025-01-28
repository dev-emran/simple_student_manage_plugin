<?php
namespace StudentManage\Rest_Api;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Server;

class Student extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace       = 'student-manage/v1';
        $this->rest_base = 'students';
    }

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_students'],
                    'permission_callback' => [$this, 'permission_check_callback'],
                    'args'                => $this->get_collection_params(),
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'create_student'],
                    'permission_callback' => [$this, 'permission_check_callback'],
                    // 'args'                => $this->get_student_args(),
                ]
            ]
        );
    }

    public function get_students($request)
    {

        $per_page = $request->get_param('per_page') ?: 5;
        $page     = $request->get_param('page') ?: 1;
        $args     = [
            'post_type'      => 'student',
            'post_status'    => 'publish',
            'posts_per_page' => intval($per_page),
            'paged'          => intval($page),
        ];

        $query    = new WP_Query($args);
        $students = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $students[] = [
                    'id'         => get_the_ID(),
                    'name'       => get_the_title(),
                    'student_id' => get_post_meta(get_the_ID(), 'sm_student_id', true),
                    'class'      => get_post_meta(get_the_ID(), 'sm_student_class', true),
                    'gender'     => get_post_meta(get_the_ID(), 'sm_gender', true),
                    'author'     => get_the_author_meta('ID'),
                    'is_gpa'     => get_post_meta(get_the_ID(), 'is_want_add_sm_gpa', true),
                    'gpa'        => get_post_meta(get_the_ID(), 'sm_gpa_student', true) ?: 'N/A',
                ];
            }

            wp_reset_postdata();

        }

        $response = [
            'students'     => $students,
            'total'        => $query->found_posts,
            'pages'        => $query->max_num_pages,
            'current_page' => intval($page),
        ];

        // if (empty($students)) {
        //     return rest_ensure_response(['message' => 'No students found.']);
        // }

        return rest_ensure_response($response);
    }

    public function permission_check_callback()
    {
        if (! current_user_can('read')) {
            return new WP_Error('rest_forbidden', __('You cannot view the post resource.', 'student-manage'), ['status' => 403]);
        }

        return true;
    }

    public function create_student($request)
    {
        $params = $request->get_body_params();
        $student_id = wp_insert_post([
            'post_type'     => 'student',
            'post_title'    => sanitize_text_field($params['name']),
            'post_status'   => 'publish',
            'meta_input'    => [
                'sm_student_id' => sanitize_text_field($params['student_id'] ?? ''),
                'sm_student_class'      => sanitize_text_field($params['class']),
                'sm_gender'             => sanitize_text_field($params['gender']),
                'is_want_add_sm_gpa'    => isset($params['is_gpa']) ? (bool) $params['is_gpa'] : false,
                'sm_gpa_student'        => isset($params['gpa']) ? sanitize_text_field($params['gpa']) : '',
            ],    
        ]);

        if (is_wp_error($student_id)) {
            return new WP_Error('post_creation_failed', __('Failed to create student.', 'student-manage'), ['status' => 500]);
        }

        return rest_ensure_response([
            'success'   => true,
            'message'   => __('Student created successfully.', 'student-manage'),
            'data'      => [
                'id'    => $student_id,
                'name'  => sanitize_text_field($params['name']),
            ],
        ]);
    }

    public function get_collection_params()
    {
        return [
            'per_page' => [
                'description'       => __('Number of students to retrieve per page.', 'student-manage'),
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
            ],
            'page'     => [
                'description'       => __('Current page number.', 'student-manage'),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ],
        ];
    }
}
