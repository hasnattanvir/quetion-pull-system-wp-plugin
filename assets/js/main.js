
// version two
jQuery(document).ready(function($) {
    $('.option_answer').on('click', function() {
        var poll_id = $(this).data('poll-id');
        var option_index = $(this).data('option-index');
        
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
                    var new_percent = Math.round(response.data.new_percent * 100) / 100; // Round to 2 decimal places
                    var new_vote_count = response.data.new_vote_count;
                    var totalVotes = response.data.total_votes;

                    var $optionAnswer = $('.option_answer[data-poll-id="' + poll_id + '"][data-option-index="' + option_index + '"]');
                    var $progressBar = $optionAnswer.find('.progress');
                    var $totalVote = $optionAnswer.find('.total_vote span');

                    var percent = totalVotes > 0 ? (new_vote_count / totalVotes) * 100 : 0;
                    percent = Math.round(percent * 100) / 100; // Round to 2 decimal places

                    $progressBar.attr('data-percent', percent);
                    $progressBar.css('width', percent + '%');
                    $progressBar.find('span').css('width', percent + '%').text(Math.round(percent) + '%');

                    $totalVote.text(new_vote_count);
                }
            }
        });
    });
});





// version one
// with vote button

// jQuery(document).ready(function($) {
//     $('.vote-button').on('click', function() {
//         var button = $(this);
//         var pollId = button.data('poll-id');
//         var optionIndex = button.data('option-index');
        
//         // Disable all buttons to prevent multiple votes
//         $('.vote-button[data-poll-id="' + pollId + '"]').prop('disabled', true);

//         $.ajax({
//             url: mpp_vars.ajaxurl,
//             type: 'POST',
//             data: {
//                 action: 'mpp_vote',
//                 poll_id: pollId,
//                 option_index: optionIndex
//             },
//             success: function(response) {
//                 if (response.success) {
//                     var newPercent = response.data.new_percent;
//                     var newVoteCount = response.data.new_vote_count;
                    
//                     // Update the total vote count
//                     button.siblings('.total_vote').find('span').text(newVoteCount);
                    
//                     // Update the progress bar
//                     button.siblings('.progress-bar').find('.progress').css('width', newPercent + '%').attr('data-percent', newPercent).find('span').text(newPercent.toFixed(2) + '%');
                    
//                     // Enable all buttons again
//                     $('.vote-button[data-poll-id="' + pollId + '"]').prop('disabled', false);
//                 } else {
//                     alert(response.data.message);
//                     $('.vote-button[data-poll-id="' + pollId + '"]').prop('disabled', false);
//                 }
//             }
//         });
//     });
// });
