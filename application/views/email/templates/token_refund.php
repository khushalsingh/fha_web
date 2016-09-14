Dear <?php echo $user_first_name . ' ' . $user_last_name; ?>,
<p>
    There was a problem initiating group coaching with <?php echo $coach_first_name . ' ' . $coach_last_name; ?> .
    <br/>
    <br/>
    This group coaching call was scheduled by you at : <?php echo date('d M Y h:i A', strtotime($start_time)); ?>
    <br/>
    <br/>
    Thus your <?php echo $token_count; ?> tokens are refunded.
</p>