<div id="<?php print $prefix; ?>Tab">
  <div class="totalData" id="<?php print Inflector::pluralize($prefix); ?>TotalInfo">
    <h1><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.pl'); ?></h1>
    <div class="row">
      <?php for($i = 0; $i <= 3; $i ++) : ?>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
        </div>
      <?php endfor; ?>
    </div>
    <!-- Draw Pie Chart -->
    <?php if($prefix != 'registered' && $prefix != 'cou') : ?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins'); ?></h3>
          <div><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.numberoflogins.desc'); ?></div>
        </div>
        <div class="pieChart" id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
      </div>
      <!-- Draw Column Chart -->
    <?php else : ?>
      <div class="box" data-type="<?php print $prefix; ?>">
        <div class="box-header with-border">
          <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . 'column.chart'); ?></h3>
        </div>
        <div class="selectPeriodContainer">Select Period:&nbsp;
          <?php
          $attrs = array();
          $attrs['value'] = RciamStatsViewerDateEnum::yearly;
          $attrs['empty'] = false;
          $periods = RciamStatsViewerDateEnum::type;
          unset($periods[RciamStatsViewerDateEnum::daily]);
          print $this->Form->select(
            'date' . ucfirst($prefix) . 'Select',
            $periods,
            $attrs
          );

          if($this->Form->isFieldError('date' . ucfirst($prefix) . 'Select')) {
            print $this->Form->error('date' . ucfirst($prefix) . 'Select');
          }
          ?>
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
        <?php if($vv_permissions['registered']) : ?>
          <div class="dataTableDateFilter bg-box-silver">
            From: &nbsp;<input type="text" id="<?php print $prefix ?>DateFrom" name="<?php print $prefix ?>DateFrom" data-provide="datepicker" />
            &emsp;To: &nbsp;<input type="text" id="<?php print $prefix ?>DateTo" name="<?php print $prefix ?>DateTo" data-provide="datepicker" />
            &nbsp;
            <?php if($prefix == 'registered' || $prefix == 'cou') : ?>
              &nbsp;
              <div class="btn-group">
                  <?php print $this->element('dropdownGroupByDate');?>
              </div>
            <?php else : ?>
              <button type="button" class="btn btn-default groupDataByDate" data-value="daily">Filter</button>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="dataTableContainer" id="<?php print $prefix; ?>DatatableContainer"></div>
      </div>
      <!-- /.box-body -->
    </div>
    <?php if($prefix == 'registered') : ?>
      <div class="box box-map" data-type="<?php print $prefix ?>">
        <div class="box-header with-border">
          <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.registered.logged_in_users.countries') ?><span class="date-specific-registered" style="font: inherit;"></span></h3>
        </div>
        <div class="box-body map-container-registered" style="position:relative">
          <div id="world-map-registered" style="height:500px">
            <div class="map"></div>
            <div class="areaLegend"></div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
