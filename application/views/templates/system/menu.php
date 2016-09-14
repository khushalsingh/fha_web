<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu">
            <li <?php
if ($this->router->class === 'dashboard' && $this->router->method === 'index') {
    echo 'class="active"';
}
?>><a href="<?php echo base_url(); ?>dashboard"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <?php if ($_SESSION['user']['group_slug'] === 'administrator') { ?>
                <li <?php
                if ($this->router->class === 'user' && $this->router->method === 'coach') {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>user/coach"><i class="fa fa-user-md"></i> <span>Coaches</span></a></li>
                <li <?php
                if ($this->router->class === 'user' && $this->router->method === 'add_coach') {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>user/add_coach"><i class="fa fa-user-plus"></i> <span>Add Coach</span></a></li>
                <li <?php
                if ($this->router->class === 'user' && $this->router->method === 'coach_availability') {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>user/coach_availability"><i class="fa fa-clock-o"></i> <span>Coach Availability</span></a></li>
                <li <?php
                if ($this->router->class === 'user' && ($this->router->method === 'index' || $this->router->method === 'tracking_progress')) {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>user"><i class="fa fa-group"></i> <span>Users</span></a></li>
                <li <?php
                if ($this->router->class === 'survey' && $this->router->method === 'index') {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>survey"><i class="fa fa-bar-chart"></i> <span>Survey</span></a></li>
                <li <?php
                if ($this->router->class === 'configuration' && $this->router->method === 'index') {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>configuration"><i class="fa fa-cogs"></i> <span>Configuration</span></a></li>
                <?php } ?>
                <?php if ($_SESSION['user']['group_slug'] === 'coach') { ?>
                <li <?php
                if ($this->router->class === 'user' && $this->router->method === 'earnings') {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>user/earnings"><i class="fa fa-money"></i> <span>Earnings</span></a></li>
                <li <?php
                if ($this->router->class === 'user' && $this->router->method === 'coach_availability') {
                    echo 'class="active"';
                }
                    ?>><a href="<?php echo base_url(); ?>user/coach_availability"><i class="fa fa-clock-o"></i> <span>Coach Availability</span></a></li>
            <?php } ?>
        </ul>
    </section>
</aside>