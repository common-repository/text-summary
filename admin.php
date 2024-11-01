<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*****************
 * ADMIN AREA
 *****************/
function text_summary_admin(){




    global $wpdb;
    require_once( plugin_dir_path( __FILE__ ).'subscribe.php');
    echo '<h2>Text Summary</h2>';
    echo '<p><a href="https://www.aitextsummary.net" target="_blank"><img src="'.esc_url(plugin_dir_url( __FILE__ ).'text-summary-header.png').'"></a></p>';
    if(!empty($_POST['_wpnonce']) && !empty($_POST['api_key'])){
       
        check_admin_referer('text-summary-options');
        $text_summary_api_key = !empty($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : null;
        if(!empty($text_summary_api_key)){update_option('text_summary_api_key',$text_summary_api_key);}
        $title = !empty($_POST['summary_title']) ? sanitize_text_field(wp_unslash($_POST['summary_title'])):'Summary';
        update_option('text_summary_title',$title);
        echo'<div class="notice notice-success"><h2>Settings Updated</h2></div>';
    }


    $text_summary_api_key=get_option('text_summary_api_key');
    if(empty($text_summary_api_key)){
        echo'<p>Please setup a subscription to activate AI text summaries of your posts.</p>';
        require_once('subscribe.php');
        text_summary_subscribe();
    }
    
    /********************************
     * Options
     *********************************/
    echo'<h3>Text Summary Plugin Settings</h3>';
    echo'<form action="" method="post">';
    echo'<div class="text-summary-form-group"><label>Summary title</label><input class="text-summary-form-control" type="text" name="summary_title" ';
    $text_summary_title = get_option('text_summary_title');
    if(!empty($text_summary_title)){echo 'value="'.esc_attr($text_summary_title).'"'; }
    echo'></div>';
    echo'<div class="text-summary-form-group"><label>API Key (obtained automatically on subscribing and in the welcome email)</label><input class="text-summary-form-control" type="text" name="api_key" ';
    if(!empty($text_summary_api_key)){echo 'value="'.esc_attr($text_summary_api_key).'"'; }
    echo'></div>';
    echo wp_kses_post(wp_nonce_field('text-summary-options'));
    echo'<p><input class="button-primary" type="submit" value="Save"></p>';
    echo'</form>';
    echo'<p>Powered by <a target="_blank" href="https://www.aitextsummary.net">www.aitextsummary.net</a></p>';
}

