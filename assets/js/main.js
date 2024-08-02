jQuery(document).ready(function($) {
    let selectedOptions = {};

    $('.option_answer').on('click', function() {
        var poll_id = $(this).data('poll-id');
        var option_index = $(this).data('option-index');

        if (selectedOptions[poll_id] !== undefined) {
            // De-select previously selected option
            $('.option_answer[data-poll-id="' + poll_id + '"][data-option-index="' + selectedOptions[poll_id] + '"]')
                .removeClass('selected');
        }

        // Mark the new option as selected
        $(this).addClass('selected');
        selectedOptions[poll_id] = option_index;

        $.ajax({
            url: mpp_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'mpp_vote',
                poll_id: poll_id,
                option_index: option_index
            },
            success: function(response) {
                if (response.success) {
                    $('.option_answer[data-poll-id="' + poll_id + '"]').each(function() {
                        var $optionAnswer = $(this);
                        var $progressBar = $optionAnswer.find('.progress');
                        var $totalVote = $optionAnswer.find('.total_vote span');

                        var index = $optionAnswer.data('option-index');
                        var percent = response.data.total_votes > 0 ? (response.data.votes_percent[index] / response.data.total_votes) * 100 : 0;
                        percent = Math.round(percent * 100) / 100; // Round to 2 decimal places

                        $progressBar.attr('data-percent', percent);
                        $progressBar.css('width', percent + '%');
                        $progressBar.find('span').css('width', percent + '%').text(Math.round(percent) + '%');

                        $totalVote.text(response.data.votes[index]);
                    });
                }
            }
        });
    });
});
