Dear <?php echo $user_first_name . ' ' . $user_last_name; ?>,
<p>
    There was a problem initiating group video call scheduled by you at : <?php echo date('d M Y h:i A', strtotime($start_time)); ?>
    <br/>
    <br/>
    Users have received their tokens back.
</p>