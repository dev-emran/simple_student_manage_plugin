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
        $this->namespace = 'student-manage/v1';
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
                ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/student/(?P<id>\d+)',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_single_student'],
                'permission_callback' => [$this, 'permission_check_callback'],
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/student/delete/(?P<id>\d+)',
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_student'],
                'permission_callback' => [$this, 'permission_check_callback'],
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/student/(?P<id>\d+)',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'update_student'],
                'permission_callback' => [$this, 'permission_check_callback'],
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                    ],
                ],
            ]
        );

    }

    public function update_student($request)
    {
        $student_id = (int) $request['id'];
        $params     = $request->get_body_params();
        $files      = $request->get_file_params();
        $student    = get_post($student_id);

        if (! $student || $student->post_type !== 'student') {
            return new WP_Error('student_not_found', __('Student not found.', 'student-manage'), ['status' => 404]);
        }



        $user_id = get_current_user_id();
        if ($user_id !== (int) $student->post_author && ! current_user_can('edit_others_posts')) {
            return new WP_Error('update_forbidden', __('You are not allowed to update this student.', 'student-manage'), ['status' => 403]);
        }

        // Prepare update data
        $update_data = [
            'ID'         => $student_id,
            'post_title' => sanitize_text_field($params['name'] ?? $student->post_title),
            'post_name'  => sanitize_title($params['name'] ?? $student->post_name),
        ];

        $updated_post_id = wp_update_post($update_data, true);
        if (is_wp_error($updated_post_id)) {
            return new WP_Error('update_failed', __('Failed to update student.', 'student-manage'), ['status' => 500]);
        }

        // Update meta fields only if values are provided
        if (! empty($params['class'])) {
            update_post_meta($student_id, 'sm_student_class', sanitize_text_field($params['class']));
        }
        if (! empty($params['gender'])) {
            update_post_meta($student_id, 'sm_gender', sanitize_text_field($params['gender']));
        }
        if (isset($params['is_gpa'])) {
            update_post_meta($student_id, 'is_want_add_sm_gpa', filter_var($params['is_gpa'], FILTER_VALIDATE_BOOLEAN));
        }
        if (! empty($params['gpa'])) {
            update_post_meta($student_id, 'sm_gpa_student', sanitize_text_field($params['gpa']));
        }

        // Handle image upload (delete old image if a new one is uploaded)
        if (! empty($files['std_image']) && is_array($files['std_image'])) {
            $old_image_id = get_post_thumbnail_id($student_id);
            $upload       = $this->handle_student_image_upload($files['std_image']); // Ensure function name is correct

            if (! is_wp_error($upload) && isset($upload['attachment_id'])) {
                $new_image_id = $upload['attachment_id'];
                set_post_thumbnail($student_id, $new_image_id);

                // Delete old image after confirming new upload was successful
                if ($old_image_id) {
                    wp_delete_attachment($old_image_id, true);
                }
            }
        }

        // Get updated student details
        $updated_student = get_post($student_id);

        return rest_ensure_response([
            'success' => true,
            'message' => __('Student updated successfully.', 'student-manage'),
            'data'    => [
                'id'     => $student_id,
                'name'   => $updated_student->post_title,
                'slug'   => $updated_student->post_name,
                'class'  => get_post_meta($student_id, 'sm_student_class', true),
                'gender' => get_post_meta($student_id, 'sm_gender', true),
                'is_gpa' => get_post_meta($student_id, 'is_want_add_sm_gpa', true),
                'gpa'    => get_post_meta($student_id, 'sm_gpa_student', true),
                'image'  => get_the_post_thumbnail_url($student_id, 'full'),
            ],
        ]);
    }

    public function get_single_student($request)
    {
        $student_id = (int) $request['id'];
        $student    = get_post($student_id);
        if (! $student || $student->post_type !== 'student') {
            return new WP_Error('student_not_found', __('Student not found.', 'student-manage'), ['status' => 404]);
        }

        $student_meta  = get_post_meta($student_id);
        $student_image = get_the_post_thumbnail_url($student_id, 'full');

        $response = [
            'id'         => $student_id,
            'name'       => get_the_title($student_id),
            'slug'       => $student->post_name,
            'class'      => $student_meta['sm_student_class'][0] ?? '',
            'gender'     => $student_meta['sm_gender'][0] ?? '',
            'is_gpa'     => ! empty($student_meta['is_want_add_sm_gpa'][0]) ? (bool) $student_meta['is_want_add_sm_gpa'][0] : false,
            'gpa'        => $student_meta['sm_gpa_student'][0] ?? '',
            'student_id' => $student_meta['sm_student_id'][0] ?? '',
            'image'      => $student_image ?: __('No image uploaded', 'student-manage'),
            'created_at' => get_the_date('Y-m-d H:i:s', $student_id),
        ];

        return rest_ensure_response($response);
    }

    public function delete_student($request)
    {
        $student_id = $request['id'];
        $student    = get_post($student_id);
        $user_id    = get_current_user_id();
        $author_id  = (int) $student->post_author;

        if (! $student || $student->post_type !== 'student') {
            return new WP_Error('delete_failed', __('Student not found', 'student-manage'), ['status' => 404]);
        }

        if ($user_id !== $author_id && ! current_user_can('delete_others_posts')) {
            return new WP_Error('delete_failed', __('You are not allow to delete this student', 'student-manage'), ['status' => 403]);
        }

        $student_image_id = get_post_thumbnail_id($student_id);
        if ($student_image_id) {
            wp_delete_attachment($student_image_id, true);
        }

        $deleted_student = wp_delete_post($student_id, true);

        if (! $deleted_student) {
            return new WP_Error('delete_failed', __('Failed to delete student.', 'student-manage'), ['status' => 500]);
        }

        return rest_ensure_response([
            'success'    => true,
            'message'    => __('Student deleted successfully.', 'student-manage'),
            'student_id' => $student_id,
        ]);

    }

    public function check_permission($request)
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (! wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('rest_forbidden', __('Nonce is invalid', 'student-manage'), ['status' => 403]);
        }

        return true;
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
                    'std_image'  => wp_get_attachment_image_src(get_post_thumbnail_id(), 'large'),
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
            return new WP_Error('rest_forbidden', __('You cannot create student', 'student-manage'), ['status' => 403]);
        }

        return true;
    }

    public function create_student($request)
    {
        $params = $request->get_body_params();
        $files  = $request->get_file_params();

        $sm_name       = sanitize_text_field($params['name']) ?? '';
        $sm_class      = sanitize_text_field($params['class']) ?? '';
        $sm_gender     = sanitize_text_field($params['gender']) ?? '';
        $sm_is_gpa     = isset($params['is_gpa']) ? filter_var($params['is_gpa'], FILTER_VALIDATE_BOOLEAN) : false;
        $sm_gpa        = sanitize_text_field($params['gpa']) ?? '';
        $sm_student_id = 'STU-' . time() . '-' . wp_rand(100, 999);

        $std_image_id = null;
        $image_url    = '';

        if (! empty($files['std_image']) && is_array($files['std_image'])) {
            $upload = $this->handle_student_image_upload($files['std_image']);

            if (is_wp_error($upload)) {
                return $upload;
            }

            if ($upload) {
                $std_image_id = isset($upload['attachment_id']) ? $upload['attachment_id'] : null;
                $image_url    = isset($upload['url']) ? esc_url($upload['url']) : '';
            }
        }

        $student_id = wp_insert_post([
            'post_type'   => 'student',
            'post_title'  => $sm_name,
            'post_status' => 'publish',
            'meta_input'  => [
                'sm_student_id'      => $sm_student_id,
                'sm_student_class'   => $sm_class,
                'sm_gender'          => $sm_gender,
                'is_want_add_sm_gpa' => $sm_is_gpa,
                'sm_gpa_student'     => $sm_gpa,
            ],
        ]);

        if (is_wp_error($student_id)) {
            return new WP_Error('post_creation_failed', __('Failed to create student.', 'student-manage'), ['status' => 500]);
        }

        if ($std_image_id) {
            set_post_thumbnail($student_id, $std_image_id);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => __('Student created successfully.', 'student-manage'),
            'data'    => [
                'id'        => $student_id,
                'name'      => $sm_name,
                'std_image' => $image_url ?: __('No image uploaded', 'student-manage'),
            ],
        ]);
    }

    public function handle_student_image_upload($file)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', $file['error'], ['status' => 500]);
        }

        if ($file['size'] > wp_max_upload_size()) {
            return new WP_Error('file_too_large', __('File exceeds maximum upload size.'), ['status' => 400]);
        }

        $allowed_mimes = ['jpg' => 'image/jpeg', 'png' => 'image/png'];
        $file_info     = wp_check_filetype($file['name'], $allowed_mimes);
        if (! $file_info['ext']) {
            return new WP_Error('invalid_file_type', __('Only JPG and PNG images are allowed.'), ['status' => 400]);
        }

        $uploaded_file = wp_handle_upload($file, ['test_form' => false]);

        if (isset($uploaded_file['error'])) {
            return new WP_Error('upload_failed', $uploaded_file['error'], ['status' => 400]);
        }

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $uploaded_file['type'],
            'post_title'     => sanitize_file_name($file['name']),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ], $uploaded_file['file']);

        if (is_wp_error($attachment_id) || ! $attachment_id) {
            return new WP_Error('attachment_error', __('Failed to create attachment.'), ['status' => 500]);
        }

        $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_metadata);

        return [
            'attachment_id' => $attachment_id,
            'url'           => $uploaded_file['url'],
        ];
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
