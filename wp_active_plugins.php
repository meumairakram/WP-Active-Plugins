<?php
/****
* Plugin Name:  WP Active Plugins | Debugging Made Easy! 
* Plugin URI:   www.codeivo.com
* Description:  A Must Have Debugging Tool for Wordpress developers and Site Ownwers to grab a list of all activated plugins before they are going to turn them off for a debugging sessions. 
* Version:      1.0
* Author:       Umair Akram 
* Author URI:   www.codeivo.com/umair-akram
* License:      GPL 2
****/



register_activation_hook(__FILE__, 'umpl_activation_code');


function umpl_activation_code() {
    
    set_transient('umpl_plugin_activated',true,0);

}


function umpl_active_message() {
    
    if(get_transient('umpl_plugin_activated')) {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><strong><?php _e('WP Active Plugins'); ?></strong> activated successfully.</p>
        
        <p><?php _e( 'To get a list of all your activated plugins, goto ');?> <strong><?php _e('Tools > Active Plugins.'); ?></strong></p>
    </div>

    <?php
        delete_transient('umpl_plugin_activated');
    }
}

add_action('admin_notices','umpl_active_message');






function umpl_my_admin_menu() { 

    add_management_page(
        'Active Plugins', 
        'Active Plugins', 
        7, 
        'active-plugins', 
        'umpl_plugins_list_show');
}
add_action('admin_menu', 'umpl_my_admin_menu'); 

function umpl_plugins_list_show(){

    // Generating and downloading CSV if Get Parameter is set

    //var_dump($_GET);
    

    
    $activated_plugins_name = array();

    $all_plugins = get_option('active_plugins');

    //Getting Activated Plugin Data
    foreach($all_plugins as $plugin) {
        $plugin_path = WP_PLUGIN_DIR.'/'.$plugin;
        $plugin_data = get_plugin_data($plugin_path,false,true);
        $activated_plugins_name[] = $plugin_data;     
    }    
    
    // generate Table

    $output = '<div class="wrap plugins_lister"><h1 class="wp-heading-inline">Activated Plugins List</h1>';
    $output .= '<table class="wp-list-table widefat">';
    $output .= '<thead><tr><th style="width: 2.2em;">Sr.No</th><th>Plugin Name</th></tr></thead>';
    
    $index = 1;
    if(sizeof($activated_plugins_name) > 0):
        foreach($activated_plugins_name as $p) {
            
            $output .= '<tbody><tr><td>'.$index.'</td><td><strong>'.$p['Name'].'</strong></td></tr></tbody>';
            
            $index++;
        }
    endif;

    $output .= '</table><div class="umpl_download_btn"><a href="'.$_SERVER['REQUEST_URI'].'&download=activated'.'" class="button button-primary">Download as CSV</a></div></div>';
    
    $output .= ' 
    <style scoped>
        .plugins_lister table {
            margin: 20px 0px;
        }

        .umpl_download_btn {
            text-align:right;
            margin-bottom: 20px;
        }
    
    </style>
';

    echo $output;


    // getting a list of all plugins

    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();

    $output = '<hr><div class="wrap plugins_lister"><h1 class="wp-heading-inline">All Plugins List</h1>';
    $output .= '<table class="wp-list-table widefat">';
    $output .= '<thead><tr><th style="width: 2.2em;">Sr.No</th><th>Plugin Name</th></tr></thead>';
    
    $index = 1;
    if(sizeof($all_plugins) > 0):
        foreach($all_plugins as $p) {
            
            $output .= '<tbody><tr><td>'.$index.'</td><td><strong>'.$p['Name'].'</strong></td></tr></tbody>';
            
            $index++;
        }
    endif;

    $output .= '</table><div class="umpl_download_btn"><a href="'.$_SERVER['REQUEST_URI'].'&download=all'.'" class="button button-primary">Download as CSV</a></div></div>';

    echo $output;
   
}

// Create CSV and Download

function umpl_create_csv_from_array($array, $filename = "export", $delimiter=";") {
    
    $csv_output = '';

    foreach($array as $line) {
        $line = implode(', ',$line);
        $csv_output .= $line."\n";
    }    

    $filename = $filename."_".date("Y-m-d_H-i",time());

    header("Content-type: application/vnd.ms-excel");
    header("Content-disposition: csv" . date("Y-m-d") . ".csv");
    header( "Content-disposition: filename=".$filename.'.csv');
    print $csv_output;
    exit;
}

// Create Download funtion 

function allow_csv_downloads() {

    if(isset($_GET['download']) && $_GET['download'] != '') {

        $download_action = $_GET['download'];

        if($download_action == 'activated') {

            // Getting Activated Plugins Data
            $activated_plugins_name = array();

            $all_plugins = get_option('active_plugins');

            if( !function_exists('get_plugin_data') ){
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            //Getting Activated Plugin Data
            foreach($all_plugins as $plugin) {
                $plugin_path = WP_PLUGIN_DIR.'/'.$plugin;
                $plugin_data = get_plugin_data($plugin_path,false,true);
                $activated_plugins_name[] = $plugin_data;     
            }    

            // Compiling data
            $data = array();

            $data[] = array('No.','Plugin Name');

            $indexNo = 1;
            foreach($activated_plugins_name as $p) {
                $data[] = array($indexNo,$p['Name']);

                $indexNo++;

            }
        
            $data[] = array('','','','');

            $data[] = array('Generated by: WP Active Plugins');

            umpl_create_csv_from_array($data,'activated_plugins');
        } else if($download_action == 'all') {

            if( !function_exists('get_plugins') ){
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            $all_plugins = get_plugins();


            // Creating CSV for all Plugins

            // Compiling data
            $data = array();

            $data[] = array('No.','Plugin Name');

            $indexNo = 1;
            foreach($all_plugins as $p) {
                $data[] = array($indexNo,$p['Name']);

                $indexNo++;

            }
        
            $data[] = array('','','','');

            $data[] = array('Generated by: WP Active Plugins');

            umpl_create_csv_from_array($data,'all_plugins');


        }


    


    } 

}

add_action('init','allow_csv_downloads');