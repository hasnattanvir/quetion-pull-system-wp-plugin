<?php 
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
?>