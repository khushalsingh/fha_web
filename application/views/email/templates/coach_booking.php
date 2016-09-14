Dear <?php echo $coach_first_name . ' ' . $coach_last_name; ?>,
<p>
    You have been booked by a user at <?php echo date('d F Y h:i A', strtotime($booking_created)); ?>.<br/><br/>
    Following are the details :
</p>
<p>
    User Name : <?php echo $user_first_name . ' ' . $user_last_name; ?><br/>
    Booking Start : <?php echo date('d F Y h:i A', strtotime($booking_start_time)); ?><br/>
    Booking End : <?php echo date('d F Y h:i A', strtotime($booking_end_time)); ?><br/>
    Booking Length : <?php echo $booking_length; ?> Minutes<br/>
</p>