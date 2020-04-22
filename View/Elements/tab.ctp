<div id="<?php print $prefix; ?>ProvidersTab">
    <div id="<?php print $prefix; ?>SpecificData">
        <h1></h1>
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
        <div class="row">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.overall'); ?></h3>
                    </div>
                    <div id="<?php print Inflector::pluralize($prefix); ?>loginsDashboard">
                        <div id="<?php print $prefix; ?>line_div"></div>
                        <div id="<?php print $prefix; ?>control_div" style="height:50px"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.specific');?></h3>
                    </div>
                    <div id="<?php print $prefix; ?>SpecificChart"></div>
                </div>
                <div id="<?php print $prefix; ?>SpecificDataTableContainer"></div>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <div id="<?php print Inflector::pluralize($prefix); ?>TotalInfo">
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
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins'); ?></h3>
                <div><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins.desc'); ?></div>
            </div>
            <div id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
        </div>
        <div id="<?php print $prefix; ?>DatatableContainer"></div>
        <!-- Create Datatable -->
    </div>
</div>