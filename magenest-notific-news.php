<?php
/**
Plugin Name: Magenest notific news
Plugin URI: http://store.magenest.com/
Description:
Version: 1.0
Author: Magenest
Author URI:
License:
Text Domain: NOTIFICNEWS
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly


if (!defined('NOTIFICNEWS_TEXT_DOMAIN'))
    define('NOTIFICNEWS_TEXT_DOMAIN', 'NOTIFICNEWS');

// Plugin Folder Path
if (!defined('NOTIFICNEWS_PATH'))
    define('NOTIFICNEWS_PATH', plugin_dir_path(__FILE__));

// Plugin Folder URL
if (!defined('NOTIFICNEWS_URL'))
    define('NOTIFICNEWS_URL', plugins_url('notific-news-post', 'magenest-notific-news.php'));

// Plugin Root File
if (!defined('NOTIFICNEWS_FILE'))
    define('NOTIFICNEWS_FILE', plugin_basename(__FILE__));

class MAGENEST_NOTIFIC_NEWS{
    //Plugin version
    const VERSION = '1.0';
    private static $notific_news;
    public function __construct(){
        global $wpdb;
        register_activation_hook(NOTIFICNEWS_FILE, array($this, 'install'));
        require NOTIFICNEWS_PATH .'includes/admin-settings.php';
        add_action('wp_enqueue_scripts', array($this,'addStyles'));
        add_action('wp_enqueue_scripts', array($this,'addScripts'));
        add_action('save_post', array('ADMIN_SETTINGS','save_table_notific_news'), 10, 2);
        add_filter('wp_nav_menu_objects', array('ADMIN_SETTINGS','insert_notific'), 1);
        //add_action('wp_enqueue_scripts', array($this,'load_custom_scripts'));
        if (is_admin ()) {
            add_action ( 'admin_enqueue_scripts', array ($this,'load_admin_scripts' ), 99 );
            add_action('admin_menu', array($this, 'create_admin_menu'), 5);
        }
    }
    public function create_admin_menu(){
        global $menu;
        $admin = new ADMIN_SETTINGS();
        add_menu_page(__('Setting notifict news', NOTIFICNEWS_TEXT_DOMAIN), __('Setting notifict news', NOTIFICNEWS_TEXT_DOMAIN), 'manage_options','notific_news', array($admin,'index'));
    }
    public function install(){
        global $wpdb;
        // get current version to check for upgrade
        $installed_version = get_option('magenest_notificnews_version');
        if (!function_exists('dbDelta')) {
            include_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        $prefix = $wpdb->prefix;
        $query = "CREATE TABLE IF NOT EXISTS `{$prefix}magenest_notific_new` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NULL,
			  `post_id` int(11) NULL,
			  `term_id` int(11) NULL ,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
        dbDelta($query);

        update_option('magenest_notificnews_version', self::VERSION);
    }
    public function addStyles(){
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('	jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-widget');
    }

    public function addScripts(){
        wp_enqueue_media();
        if (!wp_script_is('jquery', 'queue')){
            wp_enqueue_script('jquery');
        }
        if (!wp_script_is('jquery-ui-sortable', 'queue')){
            wp_enqueue_script('jquery-ui-sortable');
        }
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('	jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_style('magenestnotific' , NOTIFICNEWS_URL .'/assets/style.css');
    }

    public function load_admin_scripts($hook){
        global $woocommerce;
        if (is_object($woocommerce))
            wp_enqueue_style ( 'woocommerce_admin_styles', $woocommerce->plugin_url () . '/assets/css/admin.css' );

    }
    /**
     * Get the singleton instance of our plugin
     *
     * @return class The Instance
     * @access public
     */
    public static function getInstance() {
        if (! self::$notific_news) {
            self::$notific_news = new MAGENEST_NOTIFIC_NEWS();
        }
        return self::$notific_news;
    }
}
return new MAGENEST_NOTIFIC_NEWS();
?>