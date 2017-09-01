<?php
//check that file was called from wordpress admin
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

global $wpdb;
//delete tables
$userMetaTbl = $wpdb->prefix.'usermeta';
$optionTbl = $wpdb->prefix.'options';

//delete options
$wpdb->query("DELETE FROM $userMetaTbl WHERE meta_key LIKE '%post_id_seen%';");
$wpdb->query("DELETE FROM $optionTbl WHERE option_name LIKE '%magenest_giftregistry_version%';");
?>