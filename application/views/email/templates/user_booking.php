Dear <?php echo $user_first_name . ' ' . $user_last_name; ?>,
<p>
    You have booked a coach at <?php echo date('d F Y h:i A', strtotime($booking_created)); ?>.<br/><br/>
    Following are the details :
</p>
<p>
    Coach Name : <?php echo $coach_first_name . ' ' . $coach_last_name; ?><br/>
    Booking Start : <?php echo date('d F Y h:i A', strtotime($booking_start_time)); ?><br/>
    Booking End : <?php echo date('d F Y h:i A', strtotime($booking_end_time)); ?><br/>
    Booking Length : <?php echo $booking_length; ?> Minutes<br/>
</p>
<p>
    Tokens Remaining in your account : <?php echo $user_token_count; ?>
</p>