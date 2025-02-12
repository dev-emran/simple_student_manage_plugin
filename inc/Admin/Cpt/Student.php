<?php

namespace StudentManage\Admin\Cpt;

/**
 * Summary of Student
 */
class Student
{
    /**
     * Summary of run
     * @return void
     */
    public function run()
    {
        $this->create_student_cpt();
    }

    /**
     * Summary of create_student_cpt
     * @return void
     */
    private function create_student_cpt()
    {
        
        $labels = [
            'name'                  => _x('Students', 'Post Type General Name', 'student-manage'),
            'singular_name'         => _x('Student', 'Post Type Singular Name', 'student-manage'),
            'menu_name'             => __('Student', 'student-manage'),
            'name_admin_bar'        => __('Student', 'student-manage'),
            'archives'              => __('Item Archives', 'student-manage'),
            'attributes'            => __('Item Attributes', 'student-manage'),
            'parent_item_colon'     => __('Parent Item:', 'student-manage'),
            'all_items'             => __('All Students', 'student-manage'),
            'add_new_item'          => __('Add New Student', 'student-manage'),
            'add_new'               => __('Add Student', 'student-manage'),
            'new_item'              => __('New Student', 'student-manage'),
            'edit_item'             => __('Edit Item', 'student-manage'),
            'update_item'           => __('Update Item', 'student-manage'),
            'view_item'             => __('View Student', 'student-manage'),
            'view_items'            => __('View students', 'student-manage'),
            'search_items'          => __('Search Student', 'student-manage'),
            'not_found'             => __('Not found Student', 'student-manage'),
            'not_found_in_trash'    => __('Not found student in Trash', 'student-manage'),
            'featured_image'        => __('Student Image', 'student-manage'),
            'set_featured_image'    => __('Set Student image', 'student-manage'),
            'remove_featured_image' => __('Remove Student image', 'student-manage'),
            'use_featured_image'    => __('Use as student image', 'student-manage'),
            'insert_into_item'      => __('Insert into Student', 'student-manage'),
            'uploaded_to_this_item' => __('Uploaded to this Student', 'student-manage'),
            'items_list'            => __('Student list', 'student-manage'),
            'items_list_navigation' => __('Items list navigation', 'student-manage'),
            'filter_items_list'     => __('Filter items list', 'student-manage'),
        ];
        $args = [
            'label'               => __('Student', 'student-manage'),
            'description'         => __('Post Type Description', 'student-manage'),
            'labels'              => $labels,
            'supports'            => ['title', 'thumbnail'],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-businesswoman',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
        ];

        register_post_type('student', $args);

    }
}
