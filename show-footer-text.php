<?php
/*
 * Plugin Name: Show footer text
 * Version: 1.0.0
 * Description: Plugin show text from custom fields.
 * Author: Klaudia Wasilewska
 * Author URI: http://edokumenty.eu/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define('PLUGIN_SHOW_TEXT_DIR', plugin_dir_path(__FILE__));
define('SHOW_TEXT_VERSION', '1.0.0');

/**
 * Hook activation plugin
 */
register_activation_hook(__FILE__, function() {
    $group_post = [
        //'post_author'  => '',
        'post_content' => 'a:7:{s:8:"location";a:3:{i:0;a:1:{i:0;a:3:{s:5:"param";s:9:"post_type";s:8:"operator";s:2:"==";s:5:"value";s:4:"page";}}i:1;a:1:{i:0;a:3:{s:5:"param";s:9:"post_type";s:8:"operator";s:2:"==";s:5:"value";s:7:"klienci";}}i:2;a:1:{i:0;a:3:{s:5:"param";s:9:"post_type";s:8:"operator";s:2:"==";s:5:"value";s:11:"rozwiazania";}}}s:8:"position";s:6:"normal";s:5:"style";s:7:"default";s:15:"label_placement";s:3:"top";s:21:"instruction_placement";s:5:"label";s:14:"hide_on_screen";s:0:"";s:11:"description";s:0:"";}',
        'post_title'   => 'Footer description',
        'post_excerpt' => 'footer-description',
        'post_status'  => 'publish',
        'post_name'    => 'group_1',
        'post_type'    => 'acf-field-group'
    ];
    
    $post_id_group = wp_insert_post( $group_post );
    
    $field_post = [
        'post_content' => 'a:9:{s:4:"type";s:7:"wysiwyg";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:4:"tabs";s:3:"all";s:7:"toolbar";s:5:"basic";s:12:"media_upload";i:1;}',
        'post_title'   => 'Footer text',
        'post_excerpt' => 'footer_text',
        'post_status'  => 'publish',
        'post_name'    => 'field_1',
        'post_parent'  => $post_id_group,
        'post_type'    => 'acf-field'
    ];
    $post_id_field = wp_insert_post( $field_post );
});

/**
 * Hook deactivation plugin
 */
register_deactivation_hook(__FILE__, function() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    
    $query = "DELETE FROM ".$prefix."posts WHERE post_name = 'field_1'";
    $wpdb->query($query);
    
    $query = "DELETE FROM ".$prefix."posts WHERE post_name = 'group_1'";
    $wpdb->query($query);
});

/**
 * add css
 */
add_action('init', function (){
    wp_register_style('show-footer-text', plugins_url('/style.css', __FILE__));
    wp_enqueue_style('show-footer-text');
});

/**
 * 
 * @param string $str
 * @return string
 */
function replace($str){
  return preg_replace('/ ([a-z]{1}) /', " $1&nbsp;", $str);
}

add_shortcode('show-footer-text', function(){
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

/**
 * Self-update hook
 */
require PLUGIN_SHOW_TEXT_DIR.'/update-core/plugin-update-checker.php';
$className = PucFactory::getLatestClassVersion('PucGitHubChecker');
$myUpdateChecker = new $className(
    'https://github.com/eDokumenty/show-footer-text/',
    __FILE__,
    'master'
);
/**
 * end hook
 */
