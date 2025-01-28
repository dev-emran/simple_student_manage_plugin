<?php
namespace StudentManage\Admin\Usermeta;

class Usermeta
{
    public function __construct()
    {
        //register usermeta
        add_action('show_user_profile', [$this, 'register_usermeta']);
        add_action('edit_user_profile', [$this, 'register_usermeta']);

        //save usermeta
        add_action( 'personal_options_update', [ $this,'save_usermeta'] );
        add_action( 'edit_user_profile_update', [ $this,'save_usermeta'] );
    }

    public function register_usermeta($user)
    {
        wp_nonce_field('save_usermeta', 'save_usermeta_nonce');
        ?>
        <h3>Extra Profile Information</h3>
        <table class="form-table">
            <tr>
                <th><label for="phone_number">Phone Number</label></th>
                <td>
                    <input type="text" name="sm_phone_number" id="phone_number"
                        value="<?php echo esc_attr(get_user_meta($user->ID, 'sm_phone_number', true)); ?>"
                        class="regular-text" /><br />
                    <span class="description">Please enter your phone number.</span>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_usermeta($user_id)
    {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        if (!isset($_POST['save_usermeta_nonce']) || !wp_verify_nonce($_POST['save_usermeta_nonce'], 'save_usermeta')) {
            return false;
        }
    
        if ( isset( $_POST['sm_phone_number'] ) ) {
            $sm_user_phone_no = sanitize_text_field( $_POST['sm_phone_number'] );
            update_user_meta( $user_id, 'sm_phone_number', $sm_user_phone_no );
        }
    }
}