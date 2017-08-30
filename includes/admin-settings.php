<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class ADMIN_SETTINGS{
    public function __construct(){

    }
    public function index(){
        if(isset($_POST['btnSubmit'])){
            $number_date = isset($_POST['date'])?$_POST['date']:0;
            update_option('numberdate',$number_date);
            echo 'data saved';
        }
        $number = get_option('numberdate');
?>
        <form method="post">
            <h2>a post will has new status within x days</h2>
            <input type="text" name="date" required value="<?= isset($number)?$number:''; ?>" style="text-align: center;"/>
            <input type="submit" name="btnSubmit" value="Save changes"/>
        </form>
<?php
    }
    public function save_table_notific_news($post_id, $post){
        $postcat = get_the_category( $post_id );
        $term_id = $postcat[0]->term_id;
        if(isset($term_id)){
            global $wpdb;
            $userTbl = $wpdb->prefix.'users';
            $notific_newTbl = $wpdb->prefix.'magenest_notific_new';
            $sql = 'SELECT ID FROM '.$userTbl;
            $results = $wpdb->get_results($sql, ARRAY_A);
            foreach ($results as $result){
                $data = array('user_id' => $result['ID'], 'post_id' => $post_id, 'term_id' => $term_id);
                $wpdb->insert($notific_newTbl,$data);
            }
        }
    }
    public function insert_notific(){
        global $wpdb;
        $notific_newTbl = $wpdb->prefix.'magenest_notific_new';

        $args = array( 'theme_location' => 'primary', 'menu_class' => 'bluebell-primary-menu' );
        static $menu_id_slugs = array();

        $defaults = array( 'menu' => '', 'container' => 'div', 'container_class' => '', 'container_id' => '', 'menu_class' => 'menu', 'menu_id' => '',
            'echo' => true, 'fallback_cb' => 'wp_page_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>', 'item_spacing' => 'preserve',
            'depth' => 0, 'walker' => '', 'theme_location' => '' );

        $args = wp_parse_args( $args, $defaults );

        if ( ! in_array( $args['item_spacing'], array( 'preserve', 'discard' ), true ) ) {
            // invalid value, fall back to default.
            $args['item_spacing'] = $defaults['item_spacing'];
        }
        $args = apply_filters( 'wp_nav_menu_args', $args );
        $args = (object) $args;
        $nav_menu = apply_filters( 'pre_wp_nav_menu', null, $args );

        if ( null !== $nav_menu ) {
            if ( $args->echo ) {
                echo $nav_menu;
                return;
            }

            return $nav_menu;
        }

        // Get the nav menu based on the requested menu
        $menu = wp_get_nav_menu_object( $args->menu );

        // Get the nav menu based on the theme_location
        if ( ! $menu && $args->theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->theme_location ] ) )
            $menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );

        // get the first menu that has items if we still can't find a menu
        if ( ! $menu && !$args->theme_location ) {
            $menus = wp_get_nav_menus();
            foreach ( $menus as $menu_maybe ) {
                if ( $menu_items = wp_get_nav_menu_items( $menu_maybe->term_id, array( 'update_post_term_cache' => false ) ) ) {
                    $menu = $menu_maybe;
                    break;
                }
            }
        }

        if ( empty( $args->menu ) ) {
            $args->menu = $menu;
        }

        // If the menu exists, get its items.
        if ( $menu && ! is_wp_error($menu) && !isset($menu_items) )
            $menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
        
        // Set up the $menu_item variables
        _wp_menu_item_classes_by_context( $menu_items );

        $sorted_menu_items = $menu_items_with_children = array();

        $number_date = get_option('numberdate');
//        if(isset($number_date)) {
//            $posts = get_posts(array(
//                'numberposts' => -1,
//                'post_status' => 'publish',
//                'orderby' => 'date',
//                'order' => 'DESC',
//                'date_query' => array(
//                    'column' => 'post_date',
//                    'after' => '- ' . $number_date . ' days'  // -7 Means last 7 days
//                )));
//            $number = '('.count($posts).')';
//        }
//
        //global $post;
        $number2 = 0;
        $form = "</a><ul class='notific_new'>";
        foreach ( (array) $menu_items as $menu_item ) {
            $user_id = get_current_user_id();
            if($menu_item->object == 'category' && $menu_item->type == 'taxonomy'){//term_id='.$menu_item->object_id;
                $sql = 'SELECT * FROM '.$notific_newTbl.' WHERE user_id='.$user_id.' AND term_id='.$menu_item->object_id;
                $news = $wpdb->get_results($sql, ARRAY_A); $number1 = 0;
                foreach ($news as $new){
                    $number1 = count($news);
                    $item = "<li class='items'><a href='".get_permalink($new['post_id'])."'>".get_the_title($new['post_id'])."</a></li>";
                }

                $form .= $item;
                $number2 += $number1;
                $parent = $menu_item->menu_item_parent;
            }
        }
        $form .= "</ul><a>";
        foreach ( (array) $menu_items as $menu_item ) {

            //$sql = 'SELECT * FROM '.$notific_newTbl.' WHERE term_id=';
            $sorted_menu_items[ $menu_item->menu_order ] = $menu_item;

            if ( $menu_item->menu_item_parent ){
                $menu_items_with_children[ $menu_item->menu_item_parent ] = true;
            }elseif($menu_item->ID == $parent){
                $menu_item->title = "<span>$menu_item->title"."($number2)</span>".$form;
            }

        }

        // Add the menu-item-has-children class where applicable
        if ( $menu_items_with_children ) {
            foreach ( $sorted_menu_items as &$menu_item ) {
                if ( isset( $menu_items_with_children[ $menu_item->ID ] ) )
                    $menu_item->classes[] = 'menu-item-has-children';
            }
        }
        unset( $menu_items, $menu_item );
        return $sorted_menu_items;
    }
}