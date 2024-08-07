<?php
class MPP_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    //Make Menu
    public function add_admin_menu() {
        add_menu_page('Poll System', 'Poll System', 'manage_options', 'mpp_polls', array($this, 'polls_list_page'),'dashicons-admin-comments');
        add_submenu_page('mpp_polls', 'Poll question List', 'Poll question List', 'manage_options', 'mpp_polls', array($this, 'polls_list_page'));
        add_submenu_page('mpp_polls', 'Create Poll', 'Create Poll', 'manage_options', 'mpp_create_poll', array($this, 'create_poll_page'));
        add_submenu_page(
            'mpp_polls', // Main menu slug
            'View All Polls',          // Page title
            'View All Polls',          // Menu title
            'manage_options',          // Capability
            'view_all_polls',          // Menu slug
            array($this, 'view_all_poll')  // Callback function
        );
    }
    // Register Settings
    public function register_settings() {
        register_setting('mpp_settings_group', 'mpp_polls');
    }

    public function polls_list_page() {
        include plugin_dir_path(__FILE__) . 'polls-list.php';
    }

    private function toggle_poll_status($poll_id) {
        $polls = get_option('mpp_polls', array());
    
        if (isset($polls[$poll_id])) {
            $polls[$poll_id]['status'] = !$polls[$poll_id]['status'];
            update_option('mpp_polls', $polls);
        }
    
        wp_redirect(admin_url('admin.php?page=mpp_polls'));
        exit;
    }
    
    private function delete_poll($poll_id) {
        ob_start(); // Start output buffering
        $polls = get_option('mpp_polls', array());
    
        if (isset($polls[$poll_id])) {
            unset($polls[$poll_id]);
            update_option('mpp_polls', $polls);
        }
    
        wp_redirect(admin_url('admin.php?page=mpp_polls'));
        exit;
        ob_end_flush(); // Flush the output buffer
    }

    private function view_poll($poll_id) {
        include plugin_dir_path(__FILE__) . 'view-poll.php';
    }
    
    public function view_all_poll() {
        include plugin_dir_path(__FILE__) . 'view-all-polls.php';
    }
    
    private function edit_poll($poll_id) {
        ob_start(); // Start output buffering
        $polls = get_option('mpp_polls', array());
    
        if (isset($polls[$poll_id])) {
            $poll = $polls[$poll_id];
    
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_poll'])) {
                // Verify the nonce
                if (!isset($_POST['edit_poll_nonce']) || !wp_verify_nonce($_POST['edit_poll_nonce'], 'edit_poll_action')) {
                    wp_die('Nonce verification failed');
                }
            
                // Sanitize and process form data
                $question = sanitize_text_field($_POST['question']);
                $options = array_map('sanitize_text_field', $_POST['options']);
                $status = isset($_POST['status']) ? 1 : 0;
            
                $polls[$poll_id] = array(
                    'question' => $question,
                    'options' => $options,
                    'votes' => array_fill(0, count($options), 0),
                    'status' => $status
                );
            
                update_option('mpp_polls', $polls);
            
                wp_redirect(admin_url('admin.php?page=mpp_polls'));
                exit;
            }
            
    
            ?>
            <div class="wrap">
                <div class="edit_pull">
                    <h1>Edit Poll</h1>
                    <form method="post" action="">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Poll Title</th>
                                <td><input type="text" name="question" value="<?php echo esc_attr($poll['question']); ?>" /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Options</th>
                                <td id="poll-options">
                                    <?php foreach ($poll['options'] as $index => $option) : ?>
                                        <input type="text" name="options[]" value="<?php echo esc_attr($option); ?>" /><br>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Status</th>
                                <td><input class="status_chek" id="status" type="checkbox" name="status" value="1" <?php checked($poll['status'], 1); ?> /> <label for="status">Active</label></td>
                            </tr>
                        </table>
                        <br><br>
                        <?php submit_button('Update Poll', 'primary submitbtn', 'edit_poll'); ?>
                    </form>
               </div>
            </div>
            <?php
        }
        ob_end_flush(); // Flush the output buffer
    }
    
    public function create_poll_page() {
        include plugin_dir_path(__FILE__) . 'create-poll.php';
    }
     
}

new MPP_Admin();
?>