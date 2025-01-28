<?php
    namespace StudentManage\Admin\Metabox;
    if (! defined('ABSPATH')) {
        exit;
    }
    class Metabox
    {
        public function add_metaboxes()
        {
            add_meta_box(
                'sm_student_meta_box',
                __('Student Custom Fields', 'student-manage'),
                [$this, 'student_meta_box_html'],
                'student'
            );
        }
    
        public function student_meta_box_html($post)
        {
            wp_nonce_field('save_student_meta', 'student_meta_nonce');
    
            $sm_student_id = get_post_meta($post->ID, 'sm_student_id', true) ?? '';
            $sm_class = get_post_meta($post->ID, 'sm_student_class', true) ?? '';
            $sm_gender = get_post_meta($post->ID, 'sm_gender', true) ?? '';
            $is_want_add_sm_gpa = get_post_meta($post->ID, 'is_want_add_sm_gpa', true) ?? '';
            $sm_gpa_student = get_post_meta($post->ID, 'sm_gpa_student', true) ?? '';
            
            ?>
            <div class="wrap">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="sm_student_id"><?php esc_html_e('Student ID', 'student-manage'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                    name="sm_student_id" 
                                    id="sm_student_id" 
                                    value="<?php echo esc_attr($sm_student_id); ?>" 
                                    class="regular-text" 
                                    placeholder="<?php esc_attr_e('Auto-generated upon save', 'student-manage'); ?>" 
                                    readonly>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Student Class', 'student-manage'); ?></th>
                            <td>
                                <select name="sm_student_class" id="sm_student_class" class="regular-text">
                                    <option value="" disabled <?php selected($sm_class, ''); ?>>
                                        <?php esc_html_e('Select class', 'student-manage'); ?>
                                    </option>
                                    <?php
                                    $classes = [6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten'];
                                    foreach ($classes as $value => $label) {
                                        printf(
                                            '<option value="%d" %s>%s</option>',
                                            $value,
                                            selected($sm_class, $value, false),
                                            esc_html($label)
                                        );
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Gender', 'student-manage'); ?></th>
                            <td>
                                <?php
                                $genders = ['male' => 'Male', 'female' => 'Female', 'others' => 'Others'];
                                foreach ($genders as $value => $label) {
                                    ?>
                                    <input type="radio" 
                                        name="sm_gender" 
                                        id="sm_gender_<?php echo esc_attr($value); ?>" 
                                        value="<?php echo esc_attr($value); ?>" 
                                        <?php checked($sm_gender, $value); ?>>
                                    <label for="sm_gender_<?php echo esc_attr($value); ?>">
                                        <?php esc_html_e($label, 'student-manage'); ?>
                                    </label>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Add GPA?', 'student-manage'); ?></th>
                            <td>
                                <input id="is_want_add_sm_gpa" type="checkbox" name="is_want_add_sm_gpa"  <?php echo $is_want_add_sm_gpa ? 'checked' : '' ?> >
                            </td>
                            <td style="display: none;" class="sm_gpa_row">
                                <label for="">GPA</label>
                                <input class="sm_gpa_student_meta_field" name="sm_gpa_student" value="<?php echo esc_attr($sm_gpa_student); ?>" type="text" placeholder="Enter GPA">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="student_subjects_mark">Subjects</label></th>
                            <td colspan="3">
                                <div id="group-fields-container">
                                    <?php
                                    $student_subjects_mark = get_post_meta($post->ID, 'student_subjects_mark', true);
                                    if (!empty($student_subjects_mark) && is_array($student_subjects_mark)) {
                                        foreach ($student_subjects_mark as $index => $field) {
                                            ?>
                                            <div class="group-field-row">
                                                <input type="text" name="subject_name[]" value="<?php echo esc_attr($field['subject_name']); ?>" placeholder="Subject Name">
                                                <input type="number" name="mark[]" value="<?php echo esc_attr($field['mark']); ?>" placeholder="Mark">
                                                <input type="number" name="total_mark[]" value="<?php echo esc_attr($field['total_mark']); ?>" placeholder="Total Mark">
                                                <button type="button" class="remove-field">Remove</button>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <button type="button" id="add-group-field">Add Subject</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
        }
    
        public function save_metafields_value($post_id)
        {
            if (!isset($_POST['student_meta_nonce']) || !wp_verify_nonce($_POST['student_meta_nonce'], 'save_student_meta')) {
                return;
            }
    
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
    
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
    
            // Auto-generate student ID if not set
            $sm_student_id = get_post_meta($post_id, 'sm_student_id', true);
            if (empty($sm_student_id)) {
                $unique_id = 'STU-' . time() . '-' . wp_rand(100, 999);
                update_post_meta($post_id, 'sm_student_id', $unique_id);
            }
    
            // Validate and save Student Class
            if (isset($_POST['sm_student_class'])) {
                $valid_classes = ['6', '7', '8', '9', '10'];
                $class = sanitize_text_field($_POST['sm_student_class']);
                if (in_array($class, $valid_classes, true)) {
                    update_post_meta($post_id, 'sm_student_class', $class);
                }
            }
    
            // Validate and save Gender
            if (isset($_POST['sm_gender'])) {
                $valid_genders = ['male', 'female', 'others'];
                $gender = sanitize_text_field($_POST['sm_gender']);
                if (in_array($gender, $valid_genders, true)) {
                    update_post_meta($post_id, 'sm_gender', $gender);
                }
            }

            if(isset($_POST['is_want_add_sm_gpa'])){
                $is_want_add_sm_gpa = sanitize_text_field($_POST['is_want_add_sm_gpa']);
                update_post_meta($post_id, 'is_want_add_sm_gpa', $is_want_add_sm_gpa);
            }else{
                delete_post_meta($post_id, 'is_want_add_sm_gpa');
            }

            if(isset($_POST['sm_gpa_student'])){
                $sm_gpa_student = $_POST['sm_gpa_student'];
                update_post_meta($post_id, 'sm_gpa_student', $sm_gpa_student);
            }


            if (
                isset($_POST['subject_name']) && 
                isset($_POST['mark']) && 
                isset($_POST['total_mark']) && 
                is_array($_POST['subject_name']) && 
                is_array($_POST['mark']) && 
                is_array($_POST['total_mark'])
            ) {
                $student_subjects_mark = [];
                foreach ($_POST['subject_name'] as $index => $subject_name) {
                    $student_subjects_mark[] = [
                        'subject_name' => sanitize_text_field($subject_name),
                        'mark' => intval($_POST['mark'][$index]),
                        'total_mark' => intval($_POST['total_mark'][$index]),
                    ];
                }
                update_post_meta($post_id, 'student_subjects_mark', $student_subjects_mark);
            } else {
                delete_post_meta($post_id, 'student_subjects_mark');
            }
        }
    }
    