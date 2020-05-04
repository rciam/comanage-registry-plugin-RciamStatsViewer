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
        <?php if ($prefix != 'registered') { ?>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins'); ?></h3>
                    <div><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins.desc'); ?></div>
                </div>
                <div class="pieChart" id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
            </div>
            <div class="dataTableContainer" id="<?php print $prefix; ?>DatatableContainer"></div>
            <!-- Create Datatable -->
        <?php } else { ?>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.registered.users'); ?></h3>
                    <div><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins.desc'); ?></div>
                </div>
                <div id="selectPeriodContainer">Select Period:&nbsp;
                    <select name="dateRegisteredUsersSelect" id="dateRegisteredUsersSelect">
                        <option value="weekly">Weekly</option>
                        <option value="monthly" selected = "selected">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="columnChart" id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
            </div>
        <?php } ?>
    </div>
</div>