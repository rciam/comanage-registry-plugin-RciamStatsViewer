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
            <h3 class="box-title">Overall number of logins per day</h3>
        </div>
        <div id="loginsDashboard">
            <div id="line_div"></div>
            <div id="control_div" style="height:50px"></div>
        </div>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Overall number of logins per IdP</h3>
        </div>
        <div id="summaryIdPChart"></div>
    </div>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Overall number of logins per SP</h3>
        </div>
        <div id="summarySpChart"></div>
    </div>
</div>