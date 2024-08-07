<?php
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
                            <div data-percentage="<?php echo esc_attr(round($percent, 2)); ?>" class="bar">
                                
                                <?php
                                if($votes!=0){
                                    echo '<p class="vote_pqua">QV : ' . esc_html($votes) . '</p>';
                                }
                                ?>
                            </div>
                            <span>
                                <?php 
                                echo esc_html($azRange[$index] . ' : ' . $truncatedOption); 
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
                    <a href="<?php echo esc_url(admin_url('admin.php?page=view_all_polls')); ?>">Back to Poll List</a>
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
?>
