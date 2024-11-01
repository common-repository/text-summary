<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Plugin Name:  Text Summary
 * Plugin URI:   https://aitextsummary.net/
 * Description:  Add a text summary to posts
 * Author:       Andy Moyle
 * Author URI:   https://www.themoyles.co.uk/
 * Version:     1.0.1
 * Text Domain:  text-summary
 
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License URI:  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * License:      GPL v2 or later
 *
 * LICENSE
 * This file is part of Text Summary.
 *
 * Text Summary is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package    text-summary
 * @author     Andy Moyle
 * @copyright  Copyright 2024 Andy Moyle
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GPL 2.0
 * @link      
 */
define('TEXT_SUMMARY_URL','https://www.aitextsummary.net/');
define('TEXT_SUMMARY_AJAX','https://www.aitextsummary.net/wp-admin/admin-ajax.php?action=text_summary');
require_once( plugin_dir_path( __FILE__ ).'admin.php');

//Build Admin Menus
add_action('admin_menu', 'text_summary_menus');
/**
 *
 * Admin menu
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function text_summary_menus()

{

    global $level;
    
    add_menu_page('Text Summary', 'Text Summary',  'manage_options', 'text-summary', 'text_summary_admin');
}

/**************************************************************************
 * On subscription this function fires on a call from aitextsummary.net
 * and checks api_token, by sending back the received api_token and url
 *************************************************************************/
add_action('init','text_summary_api_receive');
function text_summary_api_receive(){
   
    if(!empty($_GET['text_summary_api_token'])){
        $api_token =  sanitize_text_field(wp_unslash($_GET['text_summary_api_token'])) ;
        
        //check valid token
        $url = TEXT_SUMMARY_AJAX;
        $args = array('timeout' => 45,
                        'body'=>array('method'=>'check-sub',
                                    'url'=>site_url(),  
                                    'api_token'=>esc_attr($api_token),
                                    )
                    );
        $result = wp_remote_post( $url , $args );
        if(is_array( $result) && !is_wp_error( $result)  ){
            //echo $result['body'];
            if($result['body']==$api_token){
                update_option('text_summary_api_key',$api_token);
            }
        }
        exit();
    }
}





/*******************************
 * Scripts enqueue
 *******************************/
 //add_action( 'wp_enqueue_scripts', 'text_summary_register_frontend_scripts' );
 add_action( 'admin_enqueue_scripts', 'text_summary_register_frontend_scripts' );
 function text_summary_register_frontend_scripts() {
    

    wp_enqueue_script('text-summary',plugins_url('text-summary.js',__FILE__),array('jquery'),'1.0.1',TRUE);
     wp_enqueue_script('jquery');
     wp_enqueue_style( 'text-summary', plugins_url('style.css',__FILE__ ) ,'',filemtime(plugin_dir_path('style.css',__FILE__ )));
	$settings = get_option('text_summary_plugin_options');
    if(empty($settings)){
        $settings = array('title'=>'Post Summary');
        update_option('text_summary_plugin_options', $settings);
    }
 } 


/*******************************
* Meta box functions
*******************************/

 function text_summary_add_custom_box() {
	$screens = [ 'post'];
	foreach ( $screens as $screen ) {
		add_meta_box(
			'text_summary_id',                 // Unique ID
			'Text Summary',      // Box title
			'text_summary_custom_box_html',  // Content callback, must be of type callable
			$screen                            // Post type
		);
	}
}
add_action( 'add_meta_boxes', 'text_summary_add_custom_box' );
function text_summary_custom_box_html( $post ) {

    $text_summary_api_key=get_option('text_summary_api_key');
   


        $value = get_post_meta( $post->ID, 'text-summary', true );
        wp_nonce_field( 'text_summary_box_nonce', 'text_summary_nonce' );
        ?>
        <p>Generate an AI text summary which will displayed at the top of your blog posts, edit as you wish, it is autosaved with the post.</p>
        <p><button class="grab-text button-secondary">Copy Text</button>&nbsp; 
        <?php
            if(!empty($text_summary_api_key)){
                echo'<button class="button-primary auto-ai">Generate AI Summary</button><span class="text-summary-waiting" style="display:none;margin-left:10px" ><img src="'.esc_url(site_url().'/wp-admin/images/loading.gif').'"></span>';
            }
            else{
                echo'<a class="button-primary" target="_blank" href="admin.php?page=text-summary">Subscribe to AI service</a></p>';
            } 
        ?>
        </p>
        <div class="text-summary-form-group"><label for="text-summary">Text Summary</label>
            <textarea class="text-summary-form-control" name="text-summary" id="text-summary"><?php if(!empty($value)){echo wp_kses_post($value);}?></textarea>
        </div>
      
        <?php
        echo wp_get_inline_script_tag(
           'var text_summary_nonce = "'.esc_attr( wp_create_nonce('ai-text')).'";',
            array(
                'id'    => 'text_summary_nonce_var',
            )
        );

}


/**************************************
 * This function receives the call 
 * to summarise text and sends it to 
 * the server for processing
 **************************************/

add_action('wp_ajax_text-summary-public', 'text_summary_public_ajax');

function text_summary_public_ajax()
{
    check_ajax_referer('ai-text','nonce');
  
    
    //sanitize and validate
    $text = !empty($_POST['text-to-be-summarised']) ? sanitize_text_field(wp_unslash($_POST['text-to-be-summarised'])) : null;
    if(empty($text)){
        echo 'No text to summarise'; exit();
    }

    $text_summary_api_key=get_option('text_summary_api_key');
    if(empty($text_summary_api_key)){
        echo 'No api key'; exit();
    }

    $url = TEXT_SUMMARY_AJAX;
    $args = array('timeout' => 45,
                    'body'=>array('method'=>'text-summary',
                                'url'=>site_url(),  
                                'api_token'=>$text_summary_api_key,
                                'text'=>$text)
                );
    $result = wp_remote_post( $url , $args );
    if(is_array( $result) && !is_wp_error( $result)  ){
        echo wp_kses_post($result['body']);
    }else{
        echo 'NO AI result, sorry'; 
        print_r($result);
    }


    exit();
}

function text_summary_save_postdata( $post_id ) {
    if( !isset( $_POST['text_summary_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['text_summary_nonce'])), 'text_summary_box_nonce' ) ) return;
	if ( array_key_exists( 'text-summary', $_POST ) ) {
		update_post_meta(
			$post_id,
			'text-summary',
			wp_kses_post(wp_unslash($_POST['text-summary']))
		);
	}
}
add_action( 'save_post', 'text_summary_save_postdata' );

/*******************************
 * Prepend Post with Summary
 *******************************/

add_filter('the_content','text_summary');

function text_summary($content)
{
    global $post;
    $title = get_option('text_summary_title');
    if(empty($title)){
        $title = 'Summary';
        update_option('text_summary_title',$title);
    }

    $summary = get_post_meta( $post->ID, 'text-summary', true );
    if(!is_single() && !is_page()){return $content;}
    if(!empty($summary)){
        
        $output_summary = '<div class="text-summary aligncenter"><p class="text-summary-title"><strong>'.esc_html($title).'</strong></p>'.wp_kses_post(wpautop($summary)).'</div>';    
        $content = $output_summary . $content;
    
    }
    return $content;

}


 