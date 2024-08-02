<?php
class MPP_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_menu_page('Poll System', 'Poll System', 'manage_options', 'mpp_polls', array($this, 'polls_list_page'));
        add_submenu_page('mpp_polls', 'Poll question List', 'Poll question List', 'manage_options', 'mpp_polls', array($this, 'polls_list_page'));
        add_submenu_page('mpp_polls', 'Create Poll', 'Create Poll', 'manage_options', 'mpp_create_poll', array($this, 'create_poll_page'));
    }

    public function register_settings() {
        register_setting('mpp_settings_group', 'mpp_polls');
    }

    public function polls_list_page() {
        ob_start(); // Start output buffering
        if (isset($_GET['action']) && isset($_GET['poll_id'])) {
            $poll_id = intval($_GET['poll_id']);
            $action = sanitize_text_field($_GET['action']);
    
            if ($action === 'delete') {
                $this->delete_poll($poll_id);
            } elseif ($action === 'view') {
                $this->view_poll($poll_id);
            } elseif ($action === 'edit') {
                $this->edit_poll($poll_id);
            }
        } else {
            $polls = get_option('mpp_polls', array());
    
            if (!is_array($polls)) {
                $polls = array();
            }
    
            ?>
            <div class="wrap">
                <h1>Poll question List</h1>
                <h2>Existing Polls</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>SL NO</th>
                            <th>Title</th>
                            <th>Options</th>
                            <th>Short Code</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($polls)): ?>
                            <?php foreach ($polls as $index => $poll) : ?>
                            <tr>
                                <td><?php echo esc_html($index + 1); ?></td>
                                <td><?php echo esc_html($poll['question']); ?></td>
                                <td><?php echo esc_html(implode(', ', array_map(function($option, $key) {
                                    return chr(65 + $key) . '. ' . $option;
                                }, $poll['options'], array_keys($poll['options'])))); ?></td>
                                <td><code>[mpp_poll id="<?php echo esc_attr($index); ?>"]</code></td> <!-- Display shortcode -->
                                <td>
                                    <a href="?page=mpp_polls&action=view&poll_id=<?php echo esc_attr($index); ?>">View</a> | 
                                    <a href="?page=mpp_polls&action=edit&poll_id=<?php echo esc_attr($index); ?>">Edit</a> | 
                                    <a href="?page=mpp_polls&action=delete&poll_id=<?php echo esc_attr($index); ?>" onclick="return confirm('Are you sure you want to delete this poll?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No polls found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ob_end_flush(); // Flush the output buffer
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
        $polls = get_option('mpp_polls', array());
    
        if (isset($polls[$poll_id])) {
            $poll = $polls[$poll_id];
            ?>
            <div class="wrap">
                <h1>View Poll</h1>
                <p><strong>Question:</strong> <?php echo esc_html($poll['question']); ?></p>
                <p><strong>Options:</strong></p>
                <ul>
                    <?php foreach ($poll['options'] as $option) : ?>
                    <li><?php echo esc_html($option); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Shortcode:</strong> <code>[mpp_poll id="<?php echo esc_attr($poll_id); ?>"]</code></p>
                <a href="<?php echo admin_url('admin.php?page=mpp_polls'); ?>">Back to Poll List</a>
            </div>
            <?php
        } else {
            echo '<div class="wrap"><h1>Poll not found</h1></div>';
        }
    }
    
    
    private function edit_poll($poll_id) {
        ob_start(); // Start output buffering
        $polls = get_option('mpp_polls', array());
    
        if (isset($polls[$poll_id])) {
            $poll = $polls[$poll_id];
    
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_poll'])) {
                $question = sanitize_text_field($_POST['question']);
                $options = array_map('sanitize_text_field', $_POST['options']);
    
                $polls[$poll_id] = array(
                    'question' => $question,
                    'options' => $options,
                    'votes' => array_fill(0, count($options), 0)
                );
    
                update_option('mpp_polls', $polls);
    
                wp_redirect(admin_url('admin.php?page=mpp_polls'));
                exit;
            }
    
            ?>
            <div class="wrap">
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
                    </table>
                    <!-- <button type="button" id="add-option" class="button">Add Option</button> -->
                    <br><br>
                    <?php submit_button('Save Poll', 'primary', 'edit_poll'); ?>
                </form>
            </div>
    
            <script>
                document.getElementById('add-option').addEventListener('click', function() {
                    const container = document.getElementById('poll-options');
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'options[]';
                    input.placeholder = 'Poll Option ' + (container.getElementsByTagName('input').length + 1);
                    container.appendChild(input);
                    container.appendChild(document.createElement('br'));
                });
            </script>
            <?php
        }
        ob_end_flush(); // Flush the output buffer
    }

    public function create_poll_page() {
        // Get all polls and ensure it's an array
        $polls = get_option('mpp_polls', array());
    
        if (!is_array($polls)) {
            $polls = array();
        }
    
        // Handle form submission for new polls
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_poll'])) {
            $question = sanitize_text_field($_POST['question']);
            $options = array_map('sanitize_text_field', $_POST['options']);
            $bgcolor = sanitize_hex_color($_POST['bgcolor']); // Ensure a valid color format
    
            // Generate a unique poll ID
            $poll_id = count($polls); // Generate a new ID based on array count
            while (isset($polls[$poll_id])) { // Ensure the ID is unique
                $poll_id++;
            }
    
            $polls[$poll_id] = array(
                'question' => $question,
                'options' => $options,
                'votes' => array_fill(0, count($options), 0),
                'bgcolor' => $bgcolor
            );
    
            update_option('mpp_polls', $polls);
    
            // Display the shortcode for the newly created poll
            $shortcode = "[mpp_poll id=\"$poll_id\"]";
            echo '<div class="notice notice-success is-dismissible"><p>Poll created successfully! Use the following shortcode to display the poll: <strong>' . esc_html($shortcode) . '</strong></p></div>';
        }
    
        ?>
        <div class="wrap">
            <h1>Create a Poll</h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Poll Title</th>
                        <td><input type="text" name="question" value="" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Options</th>
                        <td id="poll-options">
                            <input type="text" name="options[]" placeholder="Poll Option 1" /><br>
                            <input type="text" name="options[]" placeholder="Poll Option 2" /><br>
                            <input type="text" name="options[]" placeholder="Poll Option 3" /><br>
                        </td>
                    </tr>
                </table>
                <button type="button" id="add-option" class="button">Add Option</button>
                <br><br>
                <input type="color" id="bgcolor" name="bgcolor" value="#ffffff">
                <label for="bgcolor">Choose background color</label>
                <br><br>
                <?php submit_button('Save Poll', 'primary', 'new_poll'); ?>
                <?php submit_button('Save Draft', 'secondary', 'draft_poll'); ?>
            </form>
        </div>
    
        <script>
            document.getElementById('add-option').addEventListener('click', function() {
                const container = document.getElementById('poll-options');
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'options[]';
                input.placeholder = 'Poll Option ' + (container.getElementsByTagName('input').length + 1);
                container.appendChild(input);
                container.appendChild(document.createElement('br'));
            });
        </script>
        <?php
    }
    
    
    
}

new MPP_Admin();
?>