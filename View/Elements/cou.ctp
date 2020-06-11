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
        <!-- Draw Column Chart with Name List-->
        <?php if ($vv_permissions["general_cous_stats"]) : ?>
        <div class="box" data-type="<?php print $prefix; ?>">
            <div class="box-header with-border">
                <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.' . $prefix . 'column.chart'); ?></h3>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="selectPeriodContainer fullWidth">Select Period:&nbsp;
                        <?php
                        $attrs = array();
                        $attrs['value'] = RciamStatsViewerDateEnum::yearly;
                        $attrs['empty'] = false;

                        print $this->Form->select(
                            'date' . ucfirst($prefix) . 'Select',
                            RciamStatsViewerDateEnum::type,
                            $attrs
                        );

                        if ($this->Form->isFieldError('date' . ucfirst($prefix) . 'Select')) {
                            print $this->Form->error('date' . ucfirst($prefix) . 'Select');
                        }
                        ?>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="columnChart" id="<?php print Inflector::pluralize($prefix); ?>ChartDetail"></div>
                </div>
                <ul class="col-lg-3">
                    <div class="<?php print $prefix; ?>Names columnList">
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
                    From: &nbsp;<input type="text" id="<?php print $prefix ?>DateFrom" name="<?php print $prefix ?>DateFrom" data-provide="datepicker" />
                    &nbsp;&nbsp;&nbsp;To: &nbsp;<input type="text" id="<?php print $prefix ?>DateTo" name="<?php print $prefix ?>DateTo" data-provide="datepicker" />
                    &nbsp;
                    <?php if ($prefix == 'registered' || $prefix == 'cou') : ?>
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
                    <?php else : ?>
                        <button type="button" class="btn btn-default groupDataByDate" data-value="daily">Filter</button>
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
        <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.cou.statistics');?></h3>
    </div>
    <div class="box-body">
        <div class="perCouStatsSelect"></div>
        <div class="perCouStatsContent"></div>
    </div>
</div>
</div>
</div>