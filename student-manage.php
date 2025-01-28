<?php
if (! defined('ABSPATH')) {
    exit;
}

/*
		* Plugin Name:       Student Manage
		* Plugin URI:        https://github.com/ehossin3/
		* Description:       This is a simple student manage plugin.
		* Version:           1.1.0
		* Requires at least: 5.2
		* Requires PHP:      7.2
		* Author:            Emran
		* Author URI:        https://github.com/dev-emran3/
		* License:           GPL v2 or later
		* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
		* Update URI:        https://example.com/my-plugin/
		* Text Domain:       student-manage
		* Domain Path:       /languages
		* Requires Plugins:
	*/

require_once __DIR__ . '/vendor/autoload.php';
// require_once __DIR__ . '/usermeta.php';
/**
 * Summary of Student_manage
 */
final class Student_manage
{

    private $admin;

    /**
     * Summary of VERSION
     * @var string
     */
    const VERSION = '1.1.0';

    /**
     * Summary of __construct
     */
    public function __construct()
    {

        $this->define_constants();
        register_activation_hook(__FILE__, [$this, 'plugin_active']);
        add_action('plugins_loaded', [ $this, 'plugin_init']);

    }

    /**
     * Summary of define_constants
     * @return void
     */
    public function define_constants()
    {
        define('SM_PLUGIN_VERSION', self::VERSION);
        define('SM_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('SM_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('SM_PLUGIN_BASENAME', plugin_basename(__FILE__));

    }

 
    /**
     * Summary of plugin_active
     * @return void
     */
    public function plugin_active()
    {

        $installer = new StudentManage\Admin\Installer();
        $installer->run();
    }

    /**
     * Summary of init
     * @return bool|Student_manage
     */
    public static function init()
    {
        static $instance = false;
        if (! $instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function plugin_init()
    {
        if(is_admin()){
            new StudentManage\Admin\Admin();
        }else{
            
        }

        new StudentManage\API();
    }

    /**
     * Summary of loaded_plugin
     * @return void
     */
    public function loaded_plugin()
    {
        load_plugin_textdomain('student-manage', false, dirname(SM_PLUGIN_BASENAME) . '/languages');
    }
}


/**
 * Summary of sm_plugin_run
 * @return bool|Student_manage
 */
function sm_plugin_run()
{
    return Student_manage::init();
}

sm_plugin_run();
