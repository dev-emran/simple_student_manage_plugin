<?php

namespace StudentManage\Admin;
use StudentManage\Admin\Usermeta\Usermeta;
use StudentManage\Admin\Enqueue_Scripts\Enqueue_Scripts;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    /**
     * Instance of Metabox\Metabox
     *
     * @var Metabox\Metabox
     */
    private $metabox;

    /**
     * Instance of Cpt\Student
     *
     * @var Cpt\Student
     */
    private $student_cpt;




    /**
     * Admin constructor.
     */
    public function __construct()
    {
        $this->init_classes();
        $this->dispatch_actions();
    }

    /**
     * Initialize dependent classes.
     */
    private function init_classes()
    {
        $this->metabox = new Metabox\Metabox();
        $this->student_cpt = new Cpt\Student();
            new Usermeta();
            new Enqueue_Scripts();
    }

    /**
     * Hook actions for the admin functionality.
     */
    public function dispatch_actions()
    {
        // Register metabox actions
        add_action('admin_init', [$this->metabox, 'add_metaboxes']);
        add_action('save_post', [$this->metabox, 'save_metafields_value']);

        // Register Custom Post Type (CPT)
        add_action('init', [$this->student_cpt, 'run']);

        

    }

    
}
