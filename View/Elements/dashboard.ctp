<div id="dashboardTab">
  <h1><?php print _txt('pl.rciamstatsviewer.summary'); ?></h1>
  <div class="row">
  <?php for($i = 0; $i <= 3; $i ++) : ?>
    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
    </div>
  <?php endfor; ?>
  </div>
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.overall') ?></h3>
    </div>
    <div class="lineChart" id="loginsDashboard">
      <div id="line_div"></div>
      <div id="control_div" style="height:50px"></div>
    </div>
  </div>
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.idp') ?></h3>
    </div>
    <div class="pieChart idpPieChart" id="summaryIdPChart"></div>
  </div>
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.sp') ?></h3>
    </div>
    <div class="pieChart spPieChart" id="summarySpChart"></div>
  </div>
  <!-- /.box-header -->
  <div class="box" data-type="dashboard">
    <div class="box-header with-border">
      <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.logins') ?></h3>
    </div>

    <div class="box-body dataTableWithFilter">
      <?php if ($vv_permissions['registered']) : ?>
        <div class="dataTableDateFilter bg-box-silver">
          From: &nbsp;<input type="text" id="dashboardDateFrom" name="dashboardDateFrom" data-provide="datepicker" />
          &emsp;To: &nbsp;<input type="text" id="dashboardDateTo" name="dashboardDateTo" data-provide="datepicker" />
          &nbsp;
          <div class="btn-group">
              <?php print $this->element('dropdownGroupByDate');?>
          </div>
        </div>
      <?php endif; ?>
      <div class="dataTableContainer" id="dashboardDatatableContainer"></div>
    </div>
    <!-- /.box-body -->
  </div>
  <div class="box box-map">
    <div class="box-header with-border">
      <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.logins.countries') ?><span class="date-specific-dashboard" style="font: inherit;"></span></h3>
    </div>
    <div class="box-body map-container-dashboard" style="position:relative">
      <div id="world-map-dashboard" style="height:500px">
        <div class="map"></div>
        <div class="areaLegend"></div>
      </div>
    </div>
  </div>
</div>