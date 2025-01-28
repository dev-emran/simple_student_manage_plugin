<?php
    namespace StudentManage\Admin\Enqueue_Scripts;

    class Enqueue_Scripts{
        public function __construct()
        {
            add_action('admin_enqueue_scripts', [ $this, 'enqueue_scripts' ]);
        }

        public function enqueue_scripts($hook)
        {
            $version = time();
            if( $hook !== 'post.php'  && $hook !== 'post-new.php' ){
                return;
            }
            if( get_post_type() === 'student' ){
                
                wp_enqueue_script('student-metafields', SM_PLUGIN_URL . '/assets/js/student-meta.js', ['jquery'], $version, true );
            }
        }
    }