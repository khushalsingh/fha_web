<div class="content-wrapper">
    <section class="content-header">
        <h1>Tracking Progress : <?php echo $user_details_array['user_first_name'] . ' ' . $user_details_array['user_last_name']; ?> <small>user tracking progress</small></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="<?php echo base_url(); ?>user"><i class="fa fa-users"></i> Users</a></li>
            <li class="active">Tracking Progress</li>
        </ol>
    </section>
    <section class="content">
        <?php
        if (count($trackings_array) > 0) {
            foreach ($trackings_array as $tracking) {
                ?>
                <div class="box">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="well well-sm"><h3 class="text-center"><?php echo date('d F Y', strtotime($tracking['tracking_created'])); ?></h3></div>
                                <img class="img img-responsive img-circle img-thumbnail" src="<?php echo $tracking['tracking_image_url']; ?>" />
                                <br/>
                                <br/>
                                <div class="well well-sm"><h4 class="text-center">Weight : <?php echo $tracking['weight']; ?></h4></div>
                            </div>
                            <div class="col-md-9">
                                <div class="box box-info box-solid">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">General</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>BMI : </b><?php echo $tracking['bmi']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Body Fat : </b><?php echo $tracking['body_fat']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Cal. Consumed : </b><?php echo $tracking['calories_consumed']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Cal. Burned : </b><?php echo $tracking['calories_burned']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Fat Consumed : </b><?php echo $tracking['fat_consumed']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Protein Consumed : </b><?php echo $tracking['protein_consumed']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Body Area : </b><?php echo $tracking['body_area']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Measure Diff : </b><?php echo $tracking['measurement_difference']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box box-warning box-solid">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Performance</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Sports : </b><?php echo $tracking['sports_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Distance : </b><?php echo $tracking['distance_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Time : </b><?php echo $tracking['time_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Position : </b><?php echo $tracking['position_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Win / Lose : </b><?php echo $tracking['win_lose_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Trng. Sess. : </b><?php echo $tracking['training_sessions_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Exercise : </b><?php echo $tracking['exercise_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Load : </b><?php echo $tracking['load_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Avg. Rpts. : </b><?php echo $tracking['average_repetitions_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Avg. Sets : </b><?php echo $tracking['average_sets_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Avg. Pace : </b><?php echo $tracking['average_pace_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Avg. Heart Rate : </b><?php echo $tracking['average_heart_rate_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Avg. Watts : </b><?php echo $tracking['average_watts_performance']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Avg. Cadence : </b><?php echo $tracking['average_cadence']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Rec. Sess. : </b><?php echo $tracking['recovery_sessions']; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="well well-sm">
                                                    <b>Flex. Sess. : </b><?php echo $tracking['flexibility_sessions']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            No Tracking Progress Record Yet !!!
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

    </section>
</div>
