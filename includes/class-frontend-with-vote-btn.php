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
    
        if (!isset($polls[$poll_id])) {
            return 'Poll not found.';
        }
    
        $poll = $polls[$poll_id];
        $total_votes = array_sum($poll['votes']);
        ob_start();
        ?>
        <div class="contant_box" style="background-color: <?php echo esc_attr($poll['bgcolor']); ?>;">
            <div class="title_Box quotation">
                <p class="h3"><?php echo esc_html($poll['question']); ?></p>
            </div>
            <div class="Option_text_box">
                <?php foreach ($poll['options'] as $index => $option) : 
                    $vote_count = isset($poll['votes'][$index]) ? $poll['votes'][$index] : 0;
                    $percentage = $total_votes > 0 ? ($vote_count / $total_votes) * 100 : 0;
                ?>
                    <div class="option_answer container" >
                        <div class="sl_no">
                            <div class="number_title">
                                <span class="number"><?php echo chr(65 + $index); ?></span>
                            </div>
                            <div class="option_title">
                                <p><?php echo esc_html($option); ?></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" data-percent="<?php echo esc_attr($percentage); ?>" data-color="#FF7979" style="width: <?php echo esc_attr($percentage); ?>%;">
                                <span></span>
                            </div>
                        </div>
                        <span>
                            <?php 
                            // echo esc_html(round($percentage, 2)); 
                            ?>
                        </span>
                        <div class="total_vote"><span><?php echo esc_html($vote_count); ?></span></div>
                        <button class="vote-button" data-poll-id="<?php echo esc_attr($poll_id); ?>" data-option-index="<?php echo esc_attr($index); ?>">Vote</button>
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
    
        $polls[$poll_id]['votes'][$option_index]++;
        $total_votes = array_sum($polls[$poll_id]['votes']);
        $polls[$poll_id]['votes_percent'] = array();
        foreach ($polls[$poll_id]['votes'] as $vote) {
            $vote_percentage = ($vote / $total_votes) * 100;
            $polls[$poll_id]['votes_percent'][] = $vote_percentage;
        }
    
        update_option('mpp_polls', $polls);
    
        wp_send_json_success(array(
            'new_percent' => $polls[$poll_id]['votes_percent'][$option_index],
            'new_vote_count' => $polls[$poll_id]['votes'][$option_index]
        ));
    }
    
}

new MPP_Frontend();
