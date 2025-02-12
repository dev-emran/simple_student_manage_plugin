<?php
    namespace StudentManage\Frontend\User_Registration;
    class User_Registration{
        public function __construct(){
            // add_shortcode('user_registration_form', array($this, 'user_registration_form'));
            add_action('init', array($this, 'process_registration'));
        }
        /*
        public function user_registration_form(){
            if (is_user_logged_in()) {
                wp_redirect(home_url());
                exit;
            }
            ob_start();
            ?>
            <div class="registration-form">
                <?php
                    // Display success/error messages
                    if (isset($_GET['registration'])) {
                        switch ($_GET['registration']) {
                            case 'success':
                                echo '<div class="success">Registration successful!</div>';
                                break;
                            case 'error':
                                echo '<div class="error">There was an error. Please try again.</div>';
                                break;
                        }
                    }
                ?>
                <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                    <?php wp_nonce_field('register_user', 'registration_nonce'); ?>
                    <p>
                        <label>Username *</label>
                        <input type="text" name="username" required>
                    </p>
                    <p>
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </p>
                    <p>
                        <label>Password *</label>
                        <input type="password" name="password" required>
                    </p>
                    <p>
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" required>
                    </p>
                    <input type="submit" name="submit" value="Register">
                </form>
            </div>
            <?php
            return ob_get_clean();
        }
            */
        public function process_registration(){
            if (isset($_POST['submit']) && isset($_POST['registration_nonce'])) {
                // Verify nonce
                if (!wp_verify_nonce($_POST['registration_nonce'], 'register_user')) {
                    wp_die('Security check failed!');
                }
        
                // Sanitize inputs
                $username = sanitize_user($_POST['username']);
                $email = sanitize_email($_POST['email']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
        
                // Validate inputs
                $errors = array();
                if (username_exists($username)) {
                    $errors[] = 'Username already exists.';
                }
                if (email_exists($email)) {
                    $errors[] = 'Email already exists.';
                }
                if ($password !== $confirm_password) {
                    $errors[] = 'Passwords do not match.';
                }
        
                // Create user if no errors
                if (empty($errors)) {
                    $user_id = wp_create_user($username, $password, $email);
                    
                    if (!is_wp_error($user_id)) {
                        // Redirect with success
                        wp_redirect(add_query_arg('registration', 'success', get_permalink()));
                        exit;
                    } else {
                        // Handle WordPress error
                        wp_redirect(add_query_arg('registration', 'error', get_permalink()));
                        exit;
                    }
                } else {
                    // Handle custom errors (store in transient/session if needed)
                    wp_redirect(add_query_arg('registration', 'error', get_permalink()));
                    exit;
                }
            }
        }
    }