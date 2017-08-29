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
        global $wpdb;
        $userTbl = $wpdb->prefix.'users';
        $notific_newTbl = $wpdb->prefix.'magenest_notific_new';
        $sql = 'SELECT ID FROM '.$userTbl;
        $results = $wpdb->get_results($sql, ARRAY_A);
        foreach ($results as $result){
            $data = array('user_id' => $result['ID'], 'post_id' => $post_id);
            $wpdb->insert($notific_newTbl,$data);
        }
    }


}