<?php 
class MPP_Frontend {
    public function __construct() {
        add_shortcode('mpp_poll', array($this, 'mpp_poll_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_mpp_vote', array($this, 'mpp_vote'));
        add_action('wp_ajax_nopriv_mpp_vote', array($this, 'mpp_vote'));
    }

    public function mpp_poll_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'mpp_poll'
        );
    
        $poll_id = intval($atts['id']);
        $polls = get_option('mpp_polls', array());
    
        if (!isset($polls[$poll_id]) || !$polls[$poll_id]['status']) { // Check if the poll is active
            return 'Poll not found or inactive.';
        }
    
        $poll = $polls[$poll_id];
        $total_votes = array_sum($poll['votes']);
        $poll['votes_percent'] = array();
        if ($total_votes > 0) {
            foreach ($poll['votes'] as $vote) {
                $vote_percentage = round(($vote / $total_votes) * 100, 2); // Round to 2 decimal places
                $poll['votes_percent'][] = $vote_percentage;
            }
        } else {
            $poll['votes_percent'] = array_fill(0, count($poll['votes']), 0);
        }

        ob_start();
        ?>
        <div class="contant_box">
            <div class="title_Box quotation">
                <p class="h3"><?php echo esc_html($poll['question']); ?></p>
            </div>
            <div class="Option_text_box">
                <?php foreach ($poll['options'] as $index => $option) : ?>
                    <div class="option_answer container" data-poll-id="<?php echo esc_attr($poll_id); ?>" data-option-index="<?php echo esc_attr($index); ?>">
                        <div class="sl_no">
                            <div class="number_title">
                                <span class="number"><?php echo chr(65 + $index); ?></span>
                            </div>
                            <div class="option_title">
                                <p><?php echo esc_html($option); ?></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" data-percent="<?php echo esc_attr($poll['votes_percent'][$index]); ?>%" style="width: <?php echo esc_attr($poll['votes_percent'][$index]); ?>%; background-color: echo esc_attr($poll['bgcolor']);" >
                                <span style="width: <?php echo esc_attr($poll['votes_percent'][$index]); ?>%;"><?php echo round($poll['votes_percent'][$index]);?>%</span>
                            </div>
                        </div>
                        <div class="total_vote"><span><?php echo esc_attr($poll['votes'][$index]); ?></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        wp_enqueue_style('mpp-style', plugin_dir_url(__DIR__) . '/assets/css/frontend-style.css');
        wp_enqueue_script('mpp-script', plugin_dir_url(__DIR__) . '/assets/js/main.js', array('jquery'), null, true);
        wp_localize_script('mpp-script', 'mpp_vars', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    public function mpp_vote() {
        if (!isset($_POST['poll_id']) || !isset($_POST['option_index'])) {
            wp_send_json_error(array('message' => 'Invalid data.'));
        }
    
        $poll_id = intval($_POST['poll_id']);
        $option_index = intval($_POST['option_index']);
        $polls = get_option('mpp_polls', array());
    
        if (!isset($polls[$poll_id]) || !isset($polls[$poll_id]['options'][$option_index])) {
            wp_send_json_error(array('message' => 'Poll or option not found.'));
        }
    
        $user_id = get_current_user_id();
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $has_voted = false;
    
        // Check if user is logged in
        if ($user_id) {
            $user_votes = get_user_meta($user_id, 'mpp_votes', true);
            if (!is_array($user_votes)) {
                $user_votes = array();
            }
            if (isset($user_votes[$poll_id])) {
                // User has voted, allow changing the vote
                $previously_selected_option = $user_votes[$poll_id];
                if ($previously_selected_option !== $option_index) {
                    // Decrease the count for the previously selected option
                    $polls[$poll_id]['votes'][$previously_selected_option]--;
                    // Increase the count for the new selected option
                    $polls[$poll_id]['votes'][$option_index]++;
                    $user_votes[$poll_id] = $option_index;
                    update_user_meta($user_id, 'mpp_votes', $user_votes);
                }
            } else {
                // User has not voted yet
                $user_votes[$poll_id] = $option_index;
                update_user_meta($user_id, 'mpp_votes', $user_votes);
                $polls[$poll_id]['votes'][$option_index]++;
            }
        } else {
            // Handle non-logged-in users using IP address and cookies
            if (isset($_COOKIE['mpp_voted_' . $poll_id])) {
                // Check if user has already voted
                $previously_selected_option = $_COOKIE['mpp_voted_' . $poll_id];
                if ($previously_selected_option !== $option_index) {
                    // Decrease the count for the previously selected option
                    $polls[$poll_id]['votes'][$previously_selected_option]--;
                    // Increase the count for the new selected option
                    $polls[$poll_id]['votes'][$option_index]++;
                    setcookie('mpp_voted_' . $poll_id, $option_index, time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN); // 30 days
                }
            } else {
                // First-time vote
                setcookie('mpp_voted_' . $poll_id, $option_index, time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN); // 30 days
                $polls[$poll_id]['votes'][$option_index]++;
            }
        }
    
        // Calculate new percentages
        $total_votes = array_sum($polls[$poll_id]['votes']);
        $polls[$poll_id]['votes_percent'] = array();
        foreach ($polls[$poll_id]['votes'] as $vote) {
            $vote_percentage = ($vote / $total_votes) * 100;
            $polls[$poll_id]['votes_percent'][] = $vote_percentage;
        }
    
        update_option('mpp_polls', $polls);
    
        wp_send_json_success(array(
            'new_percent' => $polls[$poll_id]['votes_percent'][$option_index],
            'new_vote_count' => $polls[$poll_id]['votes'][$option_index],
            'total_votes' => $total_votes,
            'votes' => $polls[$poll_id]['votes'],
            'votes_percent' => $polls[$poll_id]['votes_percent']
        ));
    }
    
    
    
    
    
}

new MPP_Frontend();

?>