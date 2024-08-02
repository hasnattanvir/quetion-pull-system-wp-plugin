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

    // Localize script to make ajaxurl and nonce available on the front end
    wp_localize_script('mpp-main', 'mpp_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mpp_vote_nonce')
    ));
}

add_action('wp_enqueue_scripts', 'mpp_enqueue_scripts');

// add_action('wp_ajax_mpp_vote', 'handle_mpp_vote');
// add_action('wp_ajax_nopriv_mpp_vote', 'handle_mpp_vote');

// function handle_mpp_vote() {
//     // Verify nonce for security
//     if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mpp_vote_nonce')) {
//         wp_send_json_error('Invalid nonce');
//         return;
//     }

//     $poll_id = intval($_POST['poll_id']);
//     $option_index = intval($_POST['option_index']);

//     $polls = get_option('mpp_polls', array());
//     if (!isset($polls[$poll_id])) {
//         wp_send_json_error('Invalid poll ID');
//         return;
//     }

//     $poll = $polls[$poll_id];
//     if (!isset($poll['options'][$option_index])) {
//         wp_send_json_error('Invalid option');
//         return;
//     }

//     // Increment vote count
//     $poll['votes'][$option_index]++;
//     $polls[$poll_id] = $poll;

//     update_option('mpp_polls', $polls);

//     wp_send_json_success('Vote recorded');
// }

// Include admin and frontend classes
require_once plugin_dir_path(__FILE__) . 'includes/admin/class-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-frontend.php';
?>
