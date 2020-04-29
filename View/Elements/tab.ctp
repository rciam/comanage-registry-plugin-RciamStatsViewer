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
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins'); ?></h3>
                <div><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins.desc'); ?></div>
            </div>
            <div class="pieChart" id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
        </div>
        <div class="dataTableContainer" id="<?php print $prefix; ?>DatatableContainer"></div>
        <!-- Create Datatable -->
    </div>
</div>