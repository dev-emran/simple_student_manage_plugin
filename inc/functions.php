<?php


    /**
     * Custom placeholder for student post type
     * return @mixed 
     */
    if(!function_exists('sm_student_post_type_title_placeholder')){
        function sm_student_post_type_title_placeholder($title)
        {
            $current_screen = get_current_screen();
            if($current_screen->post_type === 'student'){
                $title = 'Enter student name';
            }
            return $title;
        }
    }
    add_filter('enter_title_here', 'sm_student_post_type_title_placeholder');