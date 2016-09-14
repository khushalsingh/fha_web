Dear <?php echo $coach_first_name . ' ' . $coach_last_name; ?>,
<p>
    A new client has a question in the Goal Consultation Room. Please login through your Coach Login and reply with advice and promote how you can help with your classes.
</p>
<p>
    User Name : <?php echo $user_first_name . ' ' . $user_last_name; ?><br/>
    Asked On : <?php echo date('d F Y h:i A', strtotime($question_created)); ?><br/>
    Question Topic : <?php echo $question_topic; ?><br/>
    Question :
</p>
<p>
    <i><?php echo $question_value; ?></i>
</p>