<?php
/*
 * Plugin Name: Show footer text
 * Version: 0.0.1
 * Description: Plugin show text from custom fields.
 * Author: Klaudia Wasilewska
 * Author URI: http://edokumenty.eu/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define('PLUGIN_SHOW_TEXT_DIR', plugin_dir_path(__FILE__));
define('SHOW_TEXT_VERSION', '0.0.1');

/**
 * add css
 */
add_action('init', function (){
    wp_register_style('tags-add', plugins_url('/style.css', __FILE__));
    wp_enqueue_style('tags-add');
});

/**
 * 
 * @param string $str
 * @return string
 */
function replace($str){
  return preg_replace('/ ([a-z]{1}) /', " $1&nbsp;", $str);
}

add_shortcode('desc', function(){
    $name = $_SERVER['REDIRECT_URL'];
    $path = explode('/', $name);
    $post_name = (!empty($path[ count($path) - 1])) ? $path[ count($path) - 1] : $path[ count($path) - 2];
    //echo $post_name;

    $path2 = explode('/', home_url());
    $path2 = (!empty($path2[ count($path2) - 1])) ? $path2[ count($path2) - 1] : $path2[ count($path2) - 2];

    if ( !isset($_SERVER['REDIRECT_URL'])) {

        global $wpdb;
        $prefix = $wpdb->prefix;
        $pageOnFront = $wpdb->get_var('SELECT option_value FROM '.$prefix.'options  WHERE option_name = \'show_on_front\'');

        if ($pageOnFront == 'page') {
            $slug = $wpdb->get_var('SELECT p.post_name FROM '.$prefix.'options op, '.$prefix.'posts p WHERE op.option_name = \'page_on_front\' and op.option_value = p.ID ');

            if (!empty($slug)) {
                $post_name = $slug;
            }
        }
    }

    global $wpdb;
    $prefix = $wpdb->prefix;
    $query = "SELECT m.meta_value FROM ".$prefix."posts AS p, ".$prefix."postmeta AS m WHERE p.ID = m.post_id AND m.meta_key = 'footer_text' AND p.post_type IN ('page', 'klienci', 'rozwiazania') AND p.post_name = '".$post_name."'";
    $footer_text = $wpdb->get_var($query);
    if ($footer_text !== NULL){
        return '<div id="footer-text-content"><p class="footer-text">'.replace($footer_text).'</p></div>';
    }
});