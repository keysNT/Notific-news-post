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
        add_action('wp_ajax_remove_news_seen', array($this, 'remove_news_seen') );
        add_action('save_post', array('ADMIN_SETTINGS','save_table_notific_news'), 10);
        add_filter('wp_nav_menu', array('ADMIN_SETTINGS','insert_notific'), 1,2);
        //add_action('wp_enqueue_scripts', array($this,'load_custom_scripts'));
        if (is_admin ()) {
            add_action ( 'admin_enqueue_scripts', array ($this,'load_admin_scripts' ), 99 );
            add_action('admin_menu', array($this, 'create_admin_menu'), 5);
        }
    }
    public function remove_news_seen(){
        // do something ...
        $user_id = get_current_user_id();
        $post_id = $_REQUEST['post_id'];
        $strdata = get_user_meta($user_id, 'post_id_seen');
        if(isset($strdata)){
            $data = explode(' ', $strdata[0]);
        }
        $strdata = str_replace( $post_id, '', $strdata[0] );
        update_user_meta($user_id, 'post_id_seen', $strdata);

        $output['id'] = $post_id;
        $output['type'] = 'success';
        echo json_encode($output);
        wp_die();

    }
    public function create_admin_menu(){
        global $menu;
        $admin = new ADMIN_SETTINGS();
        add_menu_page(__('Setting notifict news', NOTIFICNEWS_TEXT_DOMAIN), __('Setting notifict news', NOTIFICNEWS_TEXT_DOMAIN), 'manage_options','notific_news', array($admin,'index'));
    }
    public function install(){
        global $wpdb;
        $installed_version = get_option( 'magenest_giftregistry_version' );
        // install
       // if ( ! $installed_version ) {
            $i=0;
            $number_date = 5;
            update_option('numberdate',$number_date);
            $number_date = get_option('numberdate');
	
            $all_post = get_posts(array(
            	'numberposts' => -1,
            	'post_status' => 'publish',
            	'orderby' => 'date',
            	'order' => 'DESC',
            	'date_query' => array(
            	'column' => 'post_date',
             	'after' => '- ' . $number_date . ' days'
            )));
            foreach ($all_post as $a_post){
                $data[$i] = $a_post->ID;
                $i++;
            }

            $strdata = implode(' ', $data);
            $users = get_users( array( 'fields' => array( 'ID' ) ) );
            foreach ($users as $user){
                update_user_meta($user->ID, 'post_id_seen', $strdata);
            }
       // }
        
        // get current version to check for upgrade

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
        wp_enqueue_script('magenestnotificjs', NOTIFICNEWS_URL . '/assets/ajax_notific.js' );
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
