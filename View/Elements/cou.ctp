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
        <!-- Draw Column Chart with Name List-->
      <?php if($vv_permissions["general_cous_stats"]) : ?>
          <div class="box" data-type="<?php print $prefix; ?>">
              <div class="box-header with-border">
                  <h3 class="box-title"><?php
                    print _txt('pl.rciamstatsviewer.' . $prefix . 'column.chart'); ?></h3>
              </div>
              <div class="row">
                  <div class="col-lg-12">
                      <div class="selectPeriodContainer fullWidth">Select Period:&nbsp;
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

                        if ($this->Form->isFieldError('date' . ucfirst($prefix) . 'Select')) {
                          print $this->Form->error('date' . ucfirst($prefix) . 'Select');
                        }
                        ?>
                      </div>
                  </div>
                  <div class="col-lg-9">
                      <div class="columnChart" id="<?php
                      print Inflector::pluralize($prefix); ?>ChartDetail"></div>
                  </div>
                  <div class="col-lg-3">
                      <ul class="<?php
                      print $prefix; ?>Names columnList">
                      </ul>
                  </div>
              </div>
          </div>
          <div class="box" data-type="<?php print $prefix ?>">
              <div class="box-header with-border">
                  <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . '.pl'); ?> </h3>
              </div>
              <!-- /.box-header -->
              <div class="box-body dataTableWithFilter">
                <?php if ($vv_permissions['registered']) : ?>
                    <div class="dataTableDateFilter bg-box-silver">
                        From: &nbsp;
                        <input type="text" id="<?php print $prefix ?>DateFrom" name="<?php print $prefix ?>DateFrom" data-provide="datepicker"/>
                        &emsp;To: &nbsp;
                        <input type="text" id="<?php print $prefix ?>DateTo" name="<?php print $prefix ?>DateTo" data-provide="datepicker"/>
                      <?php if ($prefix == 'registered' || $prefix == 'cou') : ?>
                        &nbsp;
                          <div class="btn-group">
                              <?php print $this->element('dropdownGroupByDate');?>
                          </div>
                      <?php else : ?>
                          <button type="button" class="btn btn-default groupDataByDate" data-value="daily">Filter
                          </button>
                      <?php endif; ?>
                    </div>
                <?php endif; ?>
                  <div class="dataTableContainer" id="<?php print $prefix; ?>DatatableContainer"></div>
              </div>
              <!-- /.box-body -->
          </div>
      <?php endif; ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?php
                  print _txt('pl.rciamstatsviewer.cou.statistics'); ?></h3>
            </div>
            <div class="box-body" style="padding-right:0px; padding-bottom:0px">
                <div class="row">
                    <div class="col-md-3 col-sm-3">
                        <div class="perCouStatsSelect"></div>
                        <div class="perCouStatsContent"></div>
                    </div>
                    <div class="col-md-7 col-sm-7">
                        <div class="pad1">
                            <!-- Map will be created here -->
                            <div id="world-map-cous" style="height: 364px; display:none">
                                <div class="map"></div>
                                <div class="areaLegend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-2">
                        <div class="pad box-pane-right bg-green status-box" style="min-height: 280px; display:none">
                            <div class="active-users description-block margin-bottom">
                                <div class="sparkbar pad" data-color="#fff"></div>
                                <h5 class="description-header">0</h5>
                                <span class="description-text">Active Users</span>
                            </div>
                            <!-- /.description-block -->
                            <div class="graceperiod-users description-block margin-bottom">
                                <div class="sparkbar pad" data-color="#fff"></div>
                                <h5 class="description-header">0</h5>
                                <span class="description-text">Grace Period Users</span>
                            </div>
                            <!-- /.description-block -->
                            <div class="suspended-users description-block">
                                <div class="sparkbar pad" data-color="#fff"></div>
                                <h5 class="description-header">0</h5>
                                <span class="description-text">Suspended Users</span>
                            </div>
                            <!-- /.description-block -->
                            <div class="other-users description-block">
                                <div class="sparkbar pad" data-color="#fff"></div>
                                <h5 class="description-header">0</h5>
                                <span class="description-text">Other Status</span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>