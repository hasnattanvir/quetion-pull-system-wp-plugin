<?php
/*
Plugin Name: My Poll Plugin
Description: A plugin to create and manage polls.
Version: 1.0
Author: Your Name
*/

// Enqueue scripts and styles
function mpp_enqueue_scripts() {
    wp_enqueue_style('mppprogress', plugin_dir_url(__FILE__) . 'assets/css/progress-bar.css');
    wp_enqueue_style('mppdashboard', plugin_dir_url(__FILE__) . 'assets/css/admin-dashboard.css');
    wp_enqueue_style('mppcustomstyle', plugin_dir_url(__FILE__) . 'assets/css/dashboard-style.css');

    wp_enqueue_script('jquery');
    wp_enqueue_script('mpp-progress-bar', plugin_dir_url(__FILE__) . 'assets/js/progress-bar.js', array('jquery'), null, true);
    wp_enqueue_script('mpp-main', plugin_dir_url(__FILE__) . 'assets/js/main.js', array('jquery'), null, true);

    // Localize script to make ajaxurl available on the front end
    wp_localize_script('mpp-main', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'mpp_enqueue_scripts');

// Include admin and frontend classes
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-frontend.php';


?>