<?php
class MPP_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

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
            } elseif ($action === 'toggle_status') {
                $this->toggle_poll_status($poll_id);
            }
        } else {
            $polls = get_option('mpp_polls', array());
    
            if (!is_array($polls)) {
                $polls = array();
            }
    
            ?>
            <div class="wrap">
                <div class="pool_ques_list">
                    <div class="create_btn">
                        <a href="admin.php?page=mpp_create_poll">Create Poll +</a>
                    </div>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>SL NO</th>
                                <th>Title</th>
                                <th>Options</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($polls)): ?>
                                <?php foreach ($polls as $index => $poll) : ?>
                                <tr>
                                    <td><span class="sl_no_cur"><?php echo esc_html($index + 1); ?></span></td>
                                    <td><?php echo esc_html($poll['question']); ?></td>
                                    <td class="option_title">
                                        <span class="option_text">
                                        <?php 
                                        echo esc_html(implode(', ', array_map(function($option, $key) {
                                            return chr(65 + $key) . '. ' . $option;
                                        }, $poll['options'], array_keys($poll['options'])))); 
                                        ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action_btn">
                                            <div class="dropdown">
                                                <button onclick="toggleDropdown(<?php echo esc_attr($index); ?>)" class="dropbtn">Action</button>
                                                <div id="PollListDropdown_<?php echo esc_attr($index); ?>" class="dropdown-content">
                                                    <a href="?page=mpp_polls&action=toggle_status&poll_id=<?php echo esc_attr($index); ?>"><?php echo $poll['status'] ? 'Deactivate' : 'Activate'; ?></a>
                                                    <a href="?page=mpp_polls&action=view&poll_id=<?php echo esc_attr($index); ?>">View</a> 
                                                    <a href="?page=mpp_polls&action=edit&poll_id=<?php echo esc_attr($index); ?>">Edit</a>
                                                    <a href="?page=mpp_polls&action=delete&poll_id=<?php echo esc_attr($index); ?>" onclick="return confirm('Are you sure you want to delete this poll?');">Delete</a> 
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No polls found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                        <script>
                            function toggleDropdown(index) {
                                var dropdown = document.getElementById("PollListDropdown_" + index);
                                dropdown.classList.toggle("show");
                            }
                            
                            // Close the dropdown if the user clicks outside of it
                            window.onclick = function(event) {
                                if (!event.target.matches('.dropbtn')) {
                                    var dropdowns = document.getElementsByClassName("dropdown-content");
                                    for (var i = 0; i < dropdowns.length; i++) {
                                        var openDropdown = dropdowns[i];
                                        if (openDropdown.classList.contains('show')) {
                                            openDropdown.classList.remove('show');
                                        }
                                    }
                                }
                            }
                        </script>
                    </table>
                </div>
            </div>
            <?php
        }
        ob_end_flush(); // Flush the output buffer
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
        $polls = get_option('mpp_polls', array());
    
        if (isset($polls[$poll_id])) {
            $poll = $polls[$poll_id];
            $total_votes = array_sum($poll['votes']); // Total votes in the poll
            ?>
            <div class="wrap">
                <div class="view_single">
                    <div class="q_box">
                        <h2>View Poll</h2>
                        <p><strong>Question:</strong> <?php echo esc_html($poll['question']); ?></p>
                        <p><strong>Options Graph Results:</strong></p>
                    </div>
    
                    <div id="chart">
                        <ul id="numbers">
                            <li><span>100%</span></li>
                            <li><span>90%</span></li>
                            <li><span>80%</span></li>
                            <li><span>70%</span></li>
                            <li><span>60%</span></li>
                            <li><span>50%</span></li>
                            <li><span>40%</span></li>
                            <li><span>30%</span></li>
                            <li><span>20%</span></li>
                            <li><span>10%</span></li>
                            <li><span>0%</span></li>
                        </ul>
                        <ul id="bars">
                            <?php 
                                $azRange = range('A', 'Z');
                                $maxCharLimit = 10;
                                foreach ($poll['options'] as $index => $option) : 
                                $votes = $poll['votes'][$index];
                                $percent = $total_votes > 0 ? ($votes / $total_votes) * 100 : 0;
                                // Truncate the option text to the character limit
                                $truncatedOption = strlen($option) > $maxCharLimit ? substr($option, 0, $maxCharLimit) . '...' : $option;
                                ?>
                                <li>
                                    <div data-percentage="<?php echo round($percent, 2); ?>" class="bar">
                                        
                                        <?php
                                        if($votes!=0){
                                            echo '<p class="vote_pqua">QV : '.$votes.'</p>';
                                        }
                                        ?>
                                    </div>
                                    <span>
                                        <?php 
                                        echo $azRange[$index].' : ';
                                        echo esc_html($truncatedOption); 
                                        ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="text_box">
                        <p class="total_Vot"><strong>Total Votes:</strong> <?php echo esc_html($total_votes); ?></p>
                        <p class="total_per"><strong>Total Percentage:</strong> 100%</p> <!-- Since the total percentage is always 100% -->
                        <p class="short_code"><strong>Shortcode:</strong> <code>[mpp_poll id="<?php echo esc_attr($poll_id); ?>"]</code></p>
                        <div class="back_btn">
                            <a href="<?php echo admin_url('admin.php?page=view_all_polls'); ?>">Back to Poll List</a>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    $(function(){
                        $("#bars li .bar").each(function(key, bar){
                        var percentage = $(this).data('percentage');
                    
                        $(this).animate({
                            'height':percentage+'%'
                        }, 1000);
                        })
                    })
                });
            </script>
            <?php
        } else {
            echo '<div class="wrap"><h1>Poll not found</h1></div>';
        }
    }
    
    
    public function view_all_poll() {
        $polls = get_option('mpp_polls', array());
    
        if (!is_array($polls) || empty($polls)) {
            echo '<p>No polls found.</p>';
            return;
        }
    
        echo '<div class="wrap">';
        echo '<h1>View Polls</h1>';
        
        foreach ($polls as $poll_id => $poll) {
            $total_votes = array_sum($poll['votes']);
    
            echo '<h2>' . esc_html($poll['question']) . '</h2>';
            echo '<table class="wp-list-table widefat fixed striped table-view-list polls">';
            echo '<thead><tr><th>Option</th><th>Votes</th><th>Percentage</th></tr></thead>';
            echo '<tbody>';
    
            foreach ($poll['options'] as $index => $option) {
                $votes = $poll['votes'][$index];
                $percentage = $total_votes > 0 ? round(($votes / $total_votes) * 100, 2) : 0;
    
                echo '<tr>';
                echo '<td>' . esc_html($option) . '</td>';
                echo '<td>' . esc_html($votes) . '</td>';
                echo '<td>' . esc_html($percentage) . '%</td>';
                echo '</tr>';
            }
    
            echo '</tbody>';
            echo '</table>';
            echo '<br>';
        }
    
        echo '</div>';
    }
    
    private function edit_poll($poll_id) {
        ob_start(); // Start output buffering
        $polls = get_option('mpp_polls', array());
    
        if (isset($polls[$poll_id])) {
            $poll = $polls[$poll_id];
    
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_poll'])) {
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
                            <td><input type="checkbox" name="status" value="1" <?php checked($poll['status'], 1); ?> /> Active</td>
                        </tr>
                    </table>
                    <br><br>
                    <?php submit_button('Save Poll', 'primary', 'edit_poll'); ?>
                </form>
            </div>
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
            $status = isset($_POST['status']) ? 1 : 0; // Get the status value
    
            // Generate a unique poll ID
            $poll_id = count($polls); // Generate a new ID based on array count
            while (isset($polls[$poll_id])) { // Ensure the ID is unique
                $poll_id++;
            }
    
            $polls[$poll_id] = array(
                'question' => $question,
                'options' => $options,
                'votes' => array_fill(0, count($options), 0),
                'bgcolor' => $bgcolor,
                'status' => $status // Save the status
            );
    
            update_option('mpp_polls', $polls);
    
            // Display the shortcode for the newly created poll
            $shortcode = "[mpp_poll id=\"$poll_id\"]";
            echo '<div class="notice notice-success is-dismissible"><p>Poll created successfully! Use the following shortcode to display the poll: <strong>' . esc_html($shortcode) . '</strong></p></div>';
        }
        ?>
        <div class="create_page">
            <div class="wrap inner_container">
                <form method="post" action="">
                    <table class="form-table">
                        <tr valign="top">
                            <td>
                                <label for="question_name" class="question_name">Add Poll +</label>
                                <input type="text" id="question_name" name="question" placeholder="Poll Title" required />
                            </td>
                        </tr>
                        <tr>
                            <td class="btn_box">
                                <div class="btn-box-inner">
                                    <div class="color_pic">
                                        <button type="button" onclick="cpickFunction()" class="cdropbtn button">Pick Color</button>
                                    </div>
                                    <div class="dropdown">
                                        <div id="PullListDropdown" class="dropdown-content">
                                            <div class="cpic_box">
                                                <input type="color" id="bgcolor" name="bgcolor" value="#000000">
                                            </div> 
                                        </div>
                                        <script>
                                            function cpickFunction() {
                                                document.getElementById("PullListDropdown").classList.toggle("show");
                                            }
                                            
                                            // Close the dropdown if the user clicks outside of it
                                            window.onclick = function(event) {
                                                if (!event.target.matches('.cdropbtn')) {
                                                    var dropdowns = document.getElementsByClassName("dropdown-content");
                                                    var i;
                                                    for (i = 0; i < dropdowns.length; i++) {
                                                    var openDropdown = dropdowns[i];
                                                    if (openDropdown.classList.contains('show')) {
                                                        openDropdown.classList.remove('show');
                                                    }
                                                    }
                                                }
                                            }
                                        </script>
                                    </div>
                                    <div class="add_option">
                                        <button type="button"  id="add-option" class="button">Add Option +</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td id="poll-options">
                                <div class="option">
                                    <input type="text" name="options[]" placeholder="Poll Option 1" />
                                    <button type="button" class="remove-option button"></button>
                                </div>
                                <div class="option">
                                    <input type="text" name="options[]" placeholder="Poll Option 2" />
                                    <button type="button" class="remove-option button"></button>
                                </div>
                                <div class="option">
                                    <input type="text" name="options[]" placeholder="Poll Option 3" />
                                    <button type="button" class="remove-option button"></button>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="save_darf_box">
                        <?php submit_button('Save Changes', 'primary', 'new_poll'); ?>
                        <?php submit_button('Draft', 'secondary', 'draft_poll'); ?>
                    </div>
                </form>
            </div>
        </div>
    
        <script>
            document.getElementById('add-option').addEventListener('click', function() {
                const container = document.getElementById('poll-options');
                const div = document.createElement('div');
                div.className = 'option';
                div.innerHTML = '<input type="text" name="options[]" placeholder="Poll Option ' + (container.getElementsByClassName('option').length + 1) + '" /><button type="button" class="remove-option button"></button><br>';
                container.appendChild(div);
            });
    
            document.getElementById('poll-options').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-option')) {
                    e.target.parentElement.remove();
                }
            });
        </script>
        <?php
    }
     
    
}

new MPP_Admin();
?>