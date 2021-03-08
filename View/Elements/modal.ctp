<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content  overlay-wrapper">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title" id="myModalLabel"></h1>
            </div>
            <div class="modal-body">
                <div class="specificData" id="specificData">
                    <p class="subTitle"></p>
                    <div class="row">
                    <?php for($i = 0; $i <= 3; $i ++) : ?>
                        <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        </div>
                    <?php endfor; ?>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title"></h3>
                                </div>
                                <div class="lineChart" id="loginLineChart">
                                    <div id="modalline_div"></div>
                                    <div id="modalcontrol_div" style="height:50px"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title"></h3>
                                </div>
                                <div class="pieChart" id="specificChart"></div>
                            </div>
                            <div class="box" data-type="">
                                <div class="box-header with-border">
                                    <h3 class="box-title"></h3>
                                </div>
                                <div class="box-body dataTableWithFilter">
                                    <?php if($datatableExport) : ?>
                                    <div class="dataTableDateFilter bg-box-silver">
                                        <?php print _txt('pl.rciamstatsviewer.ranges.from'); ?>: &nbsp;
                                        <input type="text" id="specificDateFrom" name="specificDateFrom" data-provide="datepicker" />
                                        &emsp;
                                        <?php print _txt('pl.rciamstatsviewer.ranges.to'); ?>: &nbsp;<input type="text" id="specificDateTo" name="specificDateTo" data-provide="datepicker" />
                                        &nbsp;&nbsp;
                                        <button type="button" class="btn btn-default groupDataByDate" data-value="daily"><?php print _txt('pl.rciamstatsviewer.ranges.filter.button'); ?></button>
                                    </div>
                                    <?php endif; ?>
                                    <div class="dataTableContainer" id="specificDataTableContainer"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <div id="tabs-modal">
                    <ul>
                        <li><a href="#tabs-map">Map</a></li>
                        <li><a href="#tabs-datatable">Datatable</a></li>
                    </ul>
                    <div id="tabs-map">
                        <div class="box box-map">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php print _txt('pl.rciamstatsviewer.dashboard.logins.countries') ?><span class="date-specific-modal" style="font: inherit;"></span></h3>
                            </div>
                            <div class="box-body map-container-modal" style="position:relative">
                                <div id="world-map-modal" style="height:500px">
                                    <div class="map"></div>
                                    <div class="areaLegend"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tabs-datatable">
                        <div id="specificDataTableMapContainer"></div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php print _txt('pl.rciamstatsviewer.modal.close'); ?></button>
            </div>
            <div class="overlay">
                <div id="coSpinnerModal"></div>
            </div>
        </div>
    </div>
</div>