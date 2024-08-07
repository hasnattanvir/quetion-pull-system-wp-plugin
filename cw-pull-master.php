<?php
/*
Plugin Name: CW Pull Master
Description: A plugin to create and manage polls.
Version: 1.0
Author: Curl Ware
Requires at least: 5.6
Requires PHP: 8.0
Author URI: https://github.com
License: GPL V2 or later
License URI: http://www.gnu.org/licenses/lgpl.html
Company Name: Curl Ware
Company URI: https://curlware.com/
*/



// Enqueue frontend scripts and styles
function mpp_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('mppprogress', plugin_dir_url(__FILE__) . 'assets/css/progress-bar.css');
    wp_enqueue_script('mpp-progress-bar', plugin_dir_url(__FILE__) . 'assets/js/progress-bar.js', array('jquery'), null, true);
    wp_enqueue_script('mpp-main', plugin_dir_url(__FILE__) . 'assets/js/main.js', array('jquery'), null, true);
    wp_enqueue_script('mpp-viewsingle', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), null, true);
    echo '<!-- My Plugin JavaScript Enqueued -->';
    // Localize script to make ajaxurl and nonce available on the front end
    wp_localize_script('mpp-main', 'mpp_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mpp_vote_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'mpp_enqueue_scripts');

// Enqueue admin scripts and styles
function mpp_enqueue_admin_scripts() {
    wp_enqueue_style('mppdashboard', plugin_dir_url(__FILE__) . 'assets/css/admin-dashboard.css');
    wp_enqueue_style('mppcustomstyle', plugin_dir_url(__FILE__) . 'assets/css/dashboard-style.css');
    wp_enqueue_style('mppsingleview', plugin_dir_url(__FILE__) . 'assets/css/style.css');
}
add_action('admin_enqueue_scripts', 'mpp_enqueue_admin_scripts');

// Include admin and frontend classes
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-frontend.php';
?>
