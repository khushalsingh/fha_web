<div class="content-wrapper">
    <section class="content-header">
        <h1>Coach : <?php echo $user_details_array['user_first_name'] . ' ' . $user_details_array['user_last_name']; ?> <small>coach earnings for <?php echo date('F', mktime(0, 0, 0, $month, 10)) . ' ' . $year; ?></small></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <?php if ($_SESSION['user']['group_slug'] === 'administrator') { ?>
                <li><a href="<?php echo base_url(); ?>user/coach"><i class="fa fa-user-md"></i> Coach User</a></li>
            <?php } ?>
            <li class="active">Earnings</li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?php
            if (is_file(FCPATH . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $user_details_array['user_profile_image'])) {
                echo base_url() . 'uploads/users' . date('/Y/m/d/H/i/s/', strtotime($user_details_array['user_created'])) . $user_details_array['user_profile_image'];
            } else {
                echo base_url() . 'assets/img/profile.png';
            }
            ?>" class="img-circle img-thumbnail img-responsive" alt="User Image" />
                    </div>
                    <div class="col-md-9">
                        <form class="form-inline">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select class="form-control" id="year">
                                    <?php
                                    for ($i = date('Y'); $i >= date('Y') - 5; $i--) {
                                        ?>
                                        <option <?php
                                    if ($year == $i) {
                                        echo 'selected="selected"';
                                    }
                                        ?>><?php echo $i; ?></option>
                                        <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="month">Month</label>
                                <select class="form-control" id="month">
                                    <?php
                                    $months_array = array();
                                    for ($i = 1; $i <= 12; $i++) {
                                        $timestamp = mktime(0, 0, 0, $i, 1);
                                        $months_array[date('n', $timestamp)] = date('F', $timestamp);
                                    }
                                    foreach ($months_array as $key => $months) {
                                        ?>
                                        <option value="<?php echo str_pad($key, 2, '0', STR_PAD_LEFT); ?>" <?php
                                    if ($key == (int) ($month)) {
                                        echo 'selected="selected"';
                                    }
                                        ?>><?php echo $months; ?></option>
                                            <?php } ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="view_earnings();">View</button>
                        </form>
                        <br/>
                        <table id="coach_datatable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>PAID TOKENS EARNED</th>
                                    <th>FREE TOKENS EARNED</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $token_count_array = array();
                                $token_count_array['paid_one_to_one_chat'] = 0;
                                $token_count_array['free_one_to_one_chat'] = 0;
                                $token_count_array['paid_one_to_one_coaching'] = 0;
                                $token_count_array['free_one_to_one_coaching'] = 0;
                                $token_count_array['paid_group_coaching'] = 0;
                                $token_count_array['free_group_coaching'] = 0;
                                foreach ($monthly_bookings_array as $monthly_booking) {
                                    switch ($monthly_booking['availability_for']) {
                                        case '1':
                                            if ($monthly_booking['booking_paid_tokens_used'] > 0) {
                                                $token_count_array['paid_one_to_one_chat'] += $monthly_booking['booking_paid_tokens_used'];
                                            }
                                            if ($monthly_booking['booking_free_tokens_used'] > 0) {
                                                $token_count_array['free_one_to_one_chat'] += $monthly_booking['booking_free_tokens_used'];
                                            }
                                            break;
                                        case '2':
                                            if ($monthly_booking['booking_paid_tokens_used'] > 0) {
                                                $token_count_array['paid_one_to_one_coaching'] += $monthly_booking['booking_paid_tokens_used'];
                                            }
                                            if ($monthly_booking['booking_free_tokens_used'] > 0) {
                                                $token_count_array['free_one_to_one_coaching'] += $monthly_booking['booking_free_tokens_used'];
                                            }
                                            break;
                                        case '3':
                                            if ($monthly_booking['booking_paid_tokens_used'] > 0) {
                                                $token_count_array['paid_group_coaching'] += $monthly_booking['booking_paid_tokens_used'];
                                            }
                                            if ($monthly_booking['booking_free_tokens_used'] > 0) {
                                                $token_count_array['free_group_coaching'] += $monthly_booking['booking_free_tokens_used'];
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                }
                                ?>
                                <tr>
                                    <th><i class="fa fa-user"></i> <i class="fa fa-comment"></i></th>
                                    <td><?php echo $token_count_array['paid_one_to_one_chat']; ?></td>
                                    <td><?php echo $token_count_array['free_one_to_one_chat']; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa fa-user"></i> <i class="fa fa-video-camera"></i></th>
                                    <td><?php echo $token_count_array['paid_one_to_one_coaching']; ?></td>
                                    <td><?php echo $token_count_array['free_one_to_one_coaching']; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fa fa-users"></i> <i class="fa fa-video-camera"></i></th>
                                    <td><?php echo $token_count_array['paid_group_coaching']; ?></td>
                                    <td><?php echo $token_count_array['free_group_coaching']; ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>GROSS :
                                        <?php
                                        echo $token_count_array['paid_one_to_one_chat'] +
                                        $token_count_array['free_one_to_one_chat'] +
                                        $token_count_array['paid_one_to_one_coaching'] +
                                        $token_count_array['free_one_to_one_coaching'] +
                                        $token_count_array['paid_group_coaching'] +
                                        $token_count_array['free_group_coaching'];
                                        ?>
                                    </th>
                                    <th>PAID :
                                        <?php
                                        echo $token_count_array['paid_one_to_one_chat'] +
                                        $token_count_array['paid_one_to_one_coaching'] +
                                        $token_count_array['paid_group_coaching'];
                                        ?>
                                    </th>
                                    <th>FREE :
                                        <?php
                                        echo $token_count_array['free_one_to_one_chat'] +
                                        $token_count_array['free_one_to_one_coaching'] +
                                        $token_count_array['free_group_coaching'];
                                        ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <hr/>
                <?php if (count($monthly_bookings_array) > 0) { ?>
                    <table id="coach_datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Topic</th>
                                <th>Earned From</th>
                                <th>Paid Tokens</th>
                                <th>Free Tokens</th>
                                <th>Booking Length</th>
                                <th>Booking Start</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly_bookings_array as $monthly_booking) { ?>
                                <tr>
                                    <td><?php echo $monthly_booking['booking_id']; ?></td>
                                    <td><?php echo $monthly_booking['topic_name']; ?></td>
                                    <td><?php
                        switch ($monthly_booking['availability_for']) {
                            case '1':
                                echo '<i class="fa fa-user"></i> <i class="fa fa-comment"></i>';
                                break;
                            case '2':
                                echo '<i class="fa fa-user"></i> <i class="fa fa-video-camera"></i>';
                                break;
                            case '3':
                                echo '<i class="fa fa-users"></i> <i class="fa fa-video-camera"></i>';
                                break;
                            default:
                                echo '<i class="fa fa-circle-thin"></i>';
                                break;
                        }
                                ?></td>
                                    <td><?php echo $monthly_booking['booking_paid_tokens_used']; ?></td>
                                    <td><?php echo $monthly_booking['booking_free_tokens_used']; ?></td>
                                    <td><?php echo $monthly_booking['booking_length']; ?> Mins</td>
                                    <td><?php echo date('d M Y h:i A', strtotime($monthly_booking['booking_start_time'])); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
    </section>
</div>
<script>
    function view_earnings(){
        document.location.href = base_url + 'user/earnings/<?php echo $user_details_array['user_id']; ?>/'+ $('#year').val() + '/'+$('#month').val();
    }
</script>