<?php

/*
Plugin Name: Zip Code
Description: Plugin to demonstrate CSV import
Version: 0.1
Author: Arka Bhattacharyya
Author URI: 
*/

// Create a new table
function plugin_table(){

   global $wpdb;
   $charset_collate = $wpdb->get_charset_collate();

   $tablename = $wpdb->prefix."zipcode";

   $sql = "CREATE TABLE $tablename (
     id mediumint(11) NOT NULL AUTO_INCREMENT,
     zipcode varchar(80) NOT NULL,
     PRIMARY KEY (id)
   ) $charset_collate;";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

}
register_activation_hook( __FILE__, 'plugin_table' );

// Add menu
function plugin_menu() {

   add_menu_page("Zip Code", "Zip Code","manage_options", "zipcode", "displayList");

}
add_action("admin_menu", "plugin_menu");

function displayList(){
   include "displaylist.php";
   Email_subscription_handler();
}