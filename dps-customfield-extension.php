<?php
/**
 * Plugin Name: Custom Field Shortcode - A Display Posts Extension
 * Description: Custom Field Extension for Display Posts
 * Version: 1.0.0
 * Author: Ross MacDonald - For Webspace
 * Author URI: http://wspace.ie/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * @package Custom Field Shortcode |Display Posts Extension
 * @version 1.0.0
 * @author Ross MacDonald - For Webspace <wspace.ie>
 * @copyright Copyright (c) 2019, Eoan O'Dea & Ross MacDonald
 * @link http://wspace.ie/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

 /*
 * Checks for Display Posts plugin
 */
register_activation_hook( __FILE__, 'child_plugin_activate' );
function child_plugin_activate(){

    // Require parent plugin
    if ( ! is_plugin_active( 'display-posts-shortcode/display-posts-shortcode.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Plugin Display Posts Shortcode to be installed and active first. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}


$idAddition=0;


function be_custom_fields_dps($output='',$original_atts=array() ){
    //Check for definition of customfields
    // if(!function_exists('customfields'))
    //     return  $output;
    
/*
*Set Default Values
*/
    $atts = shortcode_atts( array( 
        'include_presented_by'	=> false,
        'presented_by'			=> '',
        'include_event_date'=>false,
        'event_date'=>'',
    ), $original_atts, 'dps-customfield-extension' );

    /*
    *This gets the values that are inputted into the shortcode to display a particular field
    *Original atts = variables put into shortcode
    */
    $include_presented_by = filter_var($atts['include_presented_by'],FILTER_VALIDATE_BOOLEAN);
    $include_event_date = filter_var( $atts['include_event_date'], FILTER_VALIDATE_BOOLEAN );
    
    
    if($include_presented_by){
        /*
        *Create Presented_By and its Html wrapper when called from Shortcode
        */
        $presented_by='<li class="presented_by"><p>'.get_the_presented_by(). ' Presents. '  .'</p></li>';
    } else {
        $presented_by=$atts['presented_by'];
    }

    if($include_event_date){
        /* 
        *Check if event date if after today
        */
        $now = date("Ymd");
        if (get_the_event_date() < $now) {
            $atts = shortcode_atts($atts, 
                array('posts_per_page' => $atts->posts_per_page + 1),
                'dps-customfield-extension');
            return '';
        }
        /*
        *Format Date
        */
        $newDate=date_format(date_create(get_the_event_date()),'d/m ');
        /*
        *Create Date Html
        */
        $event_date='<li class="event_date"><h2>'. $newDate  .'</h2></li>';
    } else {
        $event_date=$atts['event_date'];
    }
    
    /*
    *Used to give a individual label to each event post
    */
    $GLOBALS["idAddition"]+=1;

    /*
    *Creates the final output string with Html wrapper
    */
    return '<div class="event-post event-'.$GLOBALS["idAddition"].'">'. $event_date .$presented_by.'<h3>'. $output.'</h3></div>';
}

/*
 *Function to get the presented by value for each post - based on the wordpress get_the_title() function
 *
 */
function get_the_presented_by( $post = 0 ) {
	$post = get_post( $post );

    $presented_by = isset( $post->presented_by ) ? $post->presented_by : '';
    $id    = isset( $post->ID ) ? $post->ID : 0;
	
	/**
	 * Filters the post title.
	 *
	 * @since 0.71
	 *
	 * @param string $title The post title.
	 * @param int    $id    The post ID.
	 */
	return apply_filters( 'the_presented_by', $presented_by, $id );
}

/*
 *Function to get the event date for each post - based on the wordpress get_the_title() function
 *
 */
function get_the_event_date( $post = 0 ) {
	$post = get_post( $post );

    $event_date = isset( $post->event_date ) ? $post->event_date : '';
    $id    = isset( $post->ID ) ? $post->ID : 0;
	
	/**
	 * Filters the post title.
	 *
	 * @since 0.71
	 *
	 * @param string $title The post title.
	 * @param int    $id    The post ID.
	 */
	return apply_filters( 'the_event_date', $event_date, $id );
}
/*
*When the Display Post plugin runs the output filter, the main function hooks in
*
*/
add_filter( 'display_posts_shortcode_output', 'be_custom_fields_dps', 10, 2 );