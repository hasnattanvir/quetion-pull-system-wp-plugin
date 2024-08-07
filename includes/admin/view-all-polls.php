<?php 
// Get all polls
$polls = get_option('mpp_polls', array());

// Check if polls array is valid
if (!is_array($polls) || empty($polls)) {
    echo '<p>No polls found.</p>';
    return;
}

// Handle search
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
if (!empty($search_query)) {
    $polls = array_filter($polls, function($poll) use ($search_query) {
        return stripos($poll['question'], $search_query) !== false;
    });
}

// Handle pagination
$polls_per_page = 4;
$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$total_polls = count($polls);
$total_pages = ceil($total_polls / $polls_per_page);
$start_index = ($current_page - 1) * $polls_per_page;
$paginated_polls = array_slice($polls, $start_index, $polls_per_page);

// Display search form
echo '<div class="wrap">';
echo '<h1>View All Poll</h1>';
echo '<div class="form_box"><form method="get">';
echo '<input type="hidden" name="page" value="' . esc_attr($_GET['page']) . '" />';
echo '<input class="search_box" type="text" name="s" value="' . esc_attr($search_query) . '" placeholder="Search polls..." />';
echo '<input type="submit" value="Search" class="button" />';
echo '</div></form>';

// Display polls
if (empty($paginated_polls)) {
    echo '<p>No polls found.</p>';
} else {
    foreach ($paginated_polls as $poll_id => $poll) {
        $total_votes = array_sum($poll['votes']);

        echo '<h2> Q : ' . esc_html($poll['question']) . '</h2>';
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
}

// Display pagination
if ($total_pages > 1) {
    echo '<div class="pagination">';
    echo wp_kses_post(paginate_links(array(
        'base' => add_query_arg('paged', '%#%'),
        'format' => '',
        'prev_text' => __('&laquo; Previous'),
        'next_text' => __('Next &raquo;'),
        'total' => $total_pages,
        'current' => $current_page
    )));
    echo '</div>';
}

echo '</div>';
