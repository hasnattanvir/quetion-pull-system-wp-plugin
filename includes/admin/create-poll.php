<?php 
 // Get all polls and ensure it's an array
 $polls = get_option('mpp_polls', array());
    
 if (!is_array($polls)) {
     $polls = array();
 }

 // Handle form submission for new polls
 if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_poll'])) {
     // Verify the nonce
     if (!isset($_POST['mpp_poll_nonce']) || !wp_verify_nonce($_POST['mpp_poll_nonce'], 'mpp_create_poll')) {
         die('Security check failed.');
     }

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
             <?php wp_nonce_field('mpp_create_poll', 'mpp_poll_nonce'); ?>
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
                                 <button type="button" id="add-option" class="button">Add Option +</button>
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
