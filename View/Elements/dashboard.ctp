<div id="dashboardTab">
    <h1><?php print _txt('pl.rciamstatsviewer.summary'); ?></h1>
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
            <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.overall')?></h3>
        </div>
        <div class="lineChart" id="loginsDashboard">
            <div id="line_div"></div>
            <div id="control_div" style="height:50px"></div>
        </div>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.idp')?></h3>
        </div>
        <div class="pieChart idpPieChart" id="summaryIdPChart"></div>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.sp')?></h3>
        </div>
        <div class="pieChart spPieChart" id="summarySpChart"></div>
    </div>
</div>