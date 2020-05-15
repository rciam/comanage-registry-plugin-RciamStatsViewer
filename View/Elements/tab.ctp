<div id="<?php print $prefix; ?>Tab">
    <div class="totalData" id="<?php print Inflector::pluralize($prefix); ?>TotalInfo">
        <h1><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.pl'); ?></h1>
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
            </div>
            <!-- ./col -->
        </div>
        <?php if ($prefix != 'registered'): ?>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins'); ?></h3>
                    <div><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins.desc'); ?></div>
                </div>
                <div class="pieChart" id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
            </div>
        <?php else: ?>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.registered.users'); ?></h3>
                    <div><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins.desc'); ?></div>
                </div>
                <div id="selectPeriodContainer">Select Period:&nbsp;
                    <select name="dateRegisteredUsersSelect" id="dateRegisteredUsersSelect">
                        <option value="weekly">Weekly</option>
                        <option value="monthly" selected="selected">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="columnChart" id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
            </div>
        <?php endif; ?>
        <div class="box" data-type="<?php print $prefix ?>">
            <div class="box-header with-border">
                <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.pl'); ?> </h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body dataTableWithFilter">
                <?php if ($vv_permissions['registered']): ?>
                    <div class="dataTableDateFilter bg-box-silver">
                        From: &nbsp;<input type="text" id="<?php print $prefix ?>DateFrom" name="<?php print $prefix ?>DateFrom" data-provide="datepicker" />
                        &nbsp;&nbsp;&nbsp;To: &nbsp;<input type="text" id="<?php print $prefix ?>DateTo" name="<?php print $prefix ?>DateTo" data-provide="datepicker" />
                        &nbsp;
                        <?php if ($prefix == 'registered'): ?>
                            <div class="btn-group">
                                &nbsp;<button type="button" class="btn btn-default dropdown-toggle filter-button" data-toggle="dropdown">
                                    Filter <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="return false;" class="groupDataByDate" data-value="daily">Daily Basis</a></li>
                                    <li><a href="#" onclick="return false;" class="groupDataByDate" data-value="weekly">Weekly Basis</a></li>
                                    <li><a href="#" onclick="return false;" class="groupDataByDate" data-value="monthly">Monthly Basis</a></li>
                                    <li><a href="#" onclick="return false;" class="groupDataByDate" data-value="yearly">Yearly Basis</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button type="button" class="btn btn-default groupDataByDate" data-value="daily">Filter</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="dataTableContainer" id="<?php print $prefix; ?>DatatableContainer"></div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>