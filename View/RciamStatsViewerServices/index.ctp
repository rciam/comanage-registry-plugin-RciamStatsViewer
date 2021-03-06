<?php

/**
 * COmanage Registry CO Service Tokens Index View
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry
 * @since         COmanage Registry v3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

// Get a pointer to our model
$model = $this->name;
$req = Inflector::singularize($model);

$this->Html->addCrumb(_txt('ct.rciam_stats_viewer_services.pl'));

// Add page title
$params = array();
$params['title'] = _txt('ct.rciam_stats_viewer_services.pl');

// For tiles
print $this->Html->css('/RciamStatsViewer/css/bootstrap.min', array('inline' => false));
print $this->Html->css('/RciamStatsViewer/css/bootstrap-datepicker3.min', array('inline' => false));
print $this->Html->css('/RciamStatsViewer/css/AdminLTE.min', array('inline' => false));
print $this->Html->css('/RciamStatsViewer/css/ionicons.min', array('inline' => false));
print $this->Html->css('/RciamStatsViewer/css/font-awesome.min', array('inline' => false));
print $this->Html->css('/RciamStatsViewer/css/style', array('inline' => false));
print $this->Html->css('//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', array('inline' => false));
print $this->Html->css('//cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css', array('inline' => false));
print $this->Html->script('//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js', array('inline' => false));
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js', array('inline' => false));
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js', array('inline' => false));
print $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('inline' => false));
print $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array('inline' => false));
print $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('inline' => false));
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js', array('inline' => false));
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js', array('inline' => false));

print $this->Html->script("https://www.gstatic.com/charts/loader.js", array('inline' => false));
print $this->Html->script('/RciamStatsViewer/js/functions.js', array('inline' => false));
print $this->Html->script('/RciamStatsViewer/js/bootstrap.min.js', array('inline' => false));
print $this->Html->script('/RciamStatsViewer/js/datepicker3/bootstrap-datepicker.min.js', array('inline' => false));

// Map
print $this->Html->script('/RciamStatsViewer/js/jquery-mousewheel/jquery.mousewheel.js');
print $this->Html->script('/RciamStatsViewer/js/raphael/raphael.min.js');
print $this->Html->script('/RciamStatsViewer/js/jquery-mapael/jquery.mapael.min.js');
print $this->Html->script('/RciamStatsViewer/js/jquery-mapael/maps/world_countries.min.js');
print $this->Html->script('/RciamStatsViewer/js/jquery-mapael/maps/world_countries_mercator.min.js');

?>
<script type="text/javascript">
    //Global Variables
    var defaultdataIdp, defaultdataSp;
    var datatableExport = <?php print(($vv_permissions['registered']) ? 1 : 0) ?>;
    var cou_general_stats = <?php print(($vv_permissions['general_cous_stats']) ? 1 : 0) ?>;
    var overallText = [];
    var specificText = [];
    var specificTextDataTable = [];
    var registeredUsersBy = [];
    var urlByType = [];
    var vAxisTitle = [];
    var tooltipDescription = [];
    var defaultExportTitle = [];
    loginsNotAvailable = '<?php print _txt('pl.rciamstatsviewer.logins.na'); ?>';
    todaysLoginsText = '<?php print _txt('pl.rciamstatsviewer.logins.today'); ?>';
    weekLoginsText = '<?php print _txt('pl.rciamstatsviewer.logins.week'); ?>';
    monthLoginsText = '<?php print _txt('pl.rciamstatsviewer.logins.month'); ?>';
    yearLoginsText = '<?php print _txt('pl.rciamstatsviewer.logins.year'); ?>';
    overallText['idp'] = '<?php print _txt('pl.rciamstatsviewer.idp.overall'); ?>';
    overallText['sp'] = '<?php print _txt('pl.rciamstatsviewer.sp.overall'); ?>';
    specificText['idp'] = '<?php print _txt('pl.rciamstatsviewer.idp.specific'); ?>';
    specificText['sp'] = '<?php print _txt('pl.rciamstatsviewer.sp.specific'); ?>';
    specificTextDataTable['idp'] = '<?php print _txt('pl.rciamstatsviewer.idp.specific.datatable'); ?>';
    specificTextDataTable['sp'] = '<?php print _txt('pl.rciamstatsviewer.sp.specific.datatable'); ?>';
    registeredUsersBy['weekly'] = '<?php print _txt('pl.rciamstatsviewer.registered.users.weekly'); ?>';
    registeredUsersBy['monthly'] = '<?php print _txt('pl.rciamstatsviewer.registered.users.monthly'); ?>';
    registeredUsersBy['yearly'] = '<?php print _txt('pl.rciamstatsviewer.registered.users.yearly'); ?>';
    vAxisTitle['registered'] = '<?php print _txt('pl.rciamstatsviewer.registered.column'); ?>';
    vAxisTitle['cou'] = '<?php print _txt('pl.rciamstatsviewer.cou.column'); ?>';
    vAxisTitle['dashboard'] = '<?php print _txt('pl.rciamstatsviewer.dashboard.column'); ?>';
    tooltipDescription['registered'] = '<?php print _txt('pl.rciamstatsviewer.registered.tooltip'); ?>';
    tooltipDescription['cou'] = '<?php print _txt('pl.rciamstatsviewer.cou.tooltip'); ?>';
    defaultExportTitle['registered'] = '<?php print _txt('pl.rciamstatsviewer.registered.defaultexporttitle'); ?>';
    defaultExportTitle['cou'] = '<?php print _txt('pl.rciamstatsviewer.cou.defaultexporttitle'); ?>';
    var dataTableExportButtonText = '<?php print _txt('pl.rciamstatsviewer.datatable.export'); ?>';
    var statusEnum = <?php print json_encode($vv_status_enum); ?>;
    urlByType['idp'] = '<?php print $this->Html->url(array(
                            'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                            'controller' => 'rciam_stats_viewer_services',
                            'action' => 'getdataforidp',
                            'co'  => $cur_co['Co']['id']
                        )); ?>';
    urlByType['sp'] = '<?php print $this->Html->url(array(
                            'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                            'controller' => 'rciam_stats_viewer_services',
                            'action' => 'getdataforsp',
                            'co'  => $cur_co['Co']['id']
                        )); ?>';
    urlRefreshTab = '<?php print $this->Html->url(array(
                            'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                            'controller' => 'rciam_stats_viewer_services',
                            'action' => 'getdatafortabs',
                            'co'  => $cur_co['Co']['id']
                        )); ?>';
    var url_str_columnchart = '<?php print $this->Html->url(array(
                                    'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                    'controller' => 'rciam_stats_viewer_services',
                                    'action' => 'getdataforcolumnchart',
                                    'co'  => $cur_co['Co']['id']
                                )); ?>';
    var url_str_userscousowner = '<?php print $this->Html->url(array(
                                        'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                        'controller' => 'rciam_stats_viewer_services',
                                        'action' => 'getuserscousowner',
                                        'co'  => $cur_co['Co']['id']
                                    )); ?>';
    var url_str_statspercou = '<?php print $this->Html->url(array(
                                    'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                    'controller' => 'rciam_stats_viewer_services',
                                    'action' => 'getstatspercou',
                                    'co'  => $cur_co['Co']['id']
                                )); ?>';
    var url_str_userstiles = '<?php print $this->Html->url(array(
                                    'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                    'controller' => 'rciam_stats_viewer_services',
                                    'action' => 'getdataforuserstiles',
                                    'co'  => $cur_co['Co']['id']
                                )); ?>';
    var url_str_datatable_ranges = '<?php print $this->Html->url(array(
                                        'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                        'controller' => 'rciam_stats_viewer_services',
                                        'action' => 'getdatafordatatable',
                                        'co'  => $cur_co['Co']['id']
                                    )); ?>';

    $(function() {
        // Initialize Tabs
        var tabs = $("#tabs").tabs();
        // Initialize Tooltip
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
        // Initialize Modal
        $("body").append(`<?php print $this->element('modal', array('datatableExport' => $vv_permissions['registered'])) ?>`);
        // Initialize Spinners - we have one spinner for body and one for modal
        $("div[id^=coSpinner]").each(function() {
            new Spinner(coSpinnerOpts).spin(document.getElementById($(this).attr("id")));
        })

        // Initialize Tiles
        var tabsIds = ["dashboardTab", "idpsTotalInfo", "spsTotalInfo"];
        tabsIds.forEach(function(item) {
            if ($("#" + item).length > 0) {
                createTile($("#" + item + " .row .col-lg-3").eq(0), "bg-aqua", <?php print !empty($vv_totalloginscount[0]) ? $vv_totalloginscount[0] : '0'; ?>, todaysLoginsText, 1, item)
                createTile($("#" + item + " .row .col-lg-3").eq(1), "bg-green", <?php print !empty($vv_totalloginscount[1]) ? $vv_totalloginscount[1] : '0'; ?>, weekLoginsText, 7, item)
                createTile($("#" + item + " .row .col-lg-3").eq(2), "bg-yellow", <?php print !empty($vv_totalloginscount[2]) ? $vv_totalloginscount[2] : '0'; ?>, monthLoginsText, 30, item)
                createTile($("#" + item + " .row .col-lg-3").eq(3), "bg-red", <?php print !empty($vv_totalloginscount[3]) ? $vv_totalloginscount[3] : '0'; ?>, yearLoginsText, 365, item)
            }
        });

        // Initialize Date Range Format (DataTable)
        from_to_range()

        var options = {}
        options['idDataTable'] = 'dashboardDatatable'

        data = <?php print json_encode($vv_logincount_per_month); ?>;
        map_data = <?php print json_encode($vv_logins_per_country); ?>;
        if(map_data !== undefined && map_data.length > 0) {
            date_from_to = calculateMinMax(map_data)
            $(".date-specific-dashboard").html(" from " + date_from_to[0] + " to " + date_from_to[1]);
            createMap(map_data, "world-map-dashboard")
        }
        else {
            $(".box-map").hide();
        }
        options['title'] = 'Number of Logins per month'
        i = 0;
        data.forEach(function(item) {
            newDate = new Date(item[0]['range_date'].split(" ")[0]);
            if (i == 0)
                minDate = new Date(item[0]['min_date']);
            i++;
            fDate = newDate.getMonth() + 1
            if (fDate < 10)
                fDate = '0' + fDate
            //item[0]['show_date'] = fDate + "/" + newDate.getFullYear();
            item[0]['show_date'] = newDate.getFullYear() + '-' + fDate;
            // Now must transform countries array to plain text
            
            if (item[0]['countries'] !== undefined) {
                item[0]['plain_countries'] = '';
                for (country in item[0]['countries']) {
                    item[0]['plain_countries'] += country + ': ' + item[0]['countries'][country]['count'] + '|| ';
                }
                item[0]['plain_countries'] = item[0]['plain_countries'].slice(0, -3)
            }
        })

        // Initialize Date Ranges startDate and endDate for idp, sp, summary and modal
        $("input[id$=DateFrom]:not(input[id=couDateFrom],input[id=registeredDateFrom]), input[id$=DateTo]:not(input[id=couDateTo],input[id=registeredDateTo])").each(function() {
            $(this).datepicker('setStartDate', minDate);
        })
        createDataTable($('#dashboardDatatableContainer'), data, "dashboard", options)
        options['idDataTable'] = 'idpDatatable'
        options['title'] = 'Number of Logins per Identity Provider'
        createDataTable($("#idpDatatableContainer"), <?php print json_encode($vv_logincount_per_idp); ?>, "idp", options)
        options['idDataTable'] = 'spDatatable'
        options['title'] = 'Number of Logins per Service Provider'
        createDataTable($("#spDatatableContainer"), <?php print json_encode($vv_logincount_per_sp); ?>, "sp", options)

        // when clear filter is clicked
        $(document).on("click", ".back-to-overall", function() {
            type = $(this).attr("data-type") != undefined ? $(this).attr("data-type") : '';
            tabId = $(this).attr("data-tab");
            specific = ($(this).attr("data-spec") != undefined ? $(this).attr("data-spec") : false);
            identifier = ($(this).attr("identifier") != undefined ? $(this).attr("identifier") : null);
            if (identifier == null) {
                unique_logins = type == '' ? $("#unique-logins-dashboard").is(":checked") : $("#unique-logins-" + type).is(":checked")
            }
            else{
                unique_logins = $("#unique-logins-modal").is(":checked")
            }
            var row = $(this).closest(".row");
            $(".overlay").show();

            $(this).html('More info <i class="fa fa-arrow-circle-right"></i>')
            $(this).addClass("more-info");
            $(this).removeClass("back-to-overall")

            row.find(".small-box").each(function() {
                $(this).removeClass("inactive");
                $(this).addClass("active");
            })
            var days = 0;

            fValues = new Array();

            var url_str = '<?php print $this->Html->url(array(
                                'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                'controller' => 'rciam_stats_viewer_services',
                                'action' => 'getlogincountperday',
                                'co'  => $cur_co['Co']['id']
                            )); ?>';
            getLoginCountPerDay(url_str, days, identifier, type, tabId, specific, unique_logins);

        })

        // Get Data For Specific Days 
        $(document).on("click", ".more-info", function() {
            type = $(this).attr("data-type") !== undefined ? $(this).attr("data-type") : '';
            tabId = $(this).attr("data-tab");
            specific = ($(this).attr("data-spec") != undefined ? $(this).attr("data-spec") : false);
            identifier = ($(this).attr("identifier") != undefined ? $(this).attr("identifier") : null);
           
            activeTab = $("ul.tabset_tabs li.ui-tabs-active").attr("aria-controls").replace("Tab","");
            unique_logins = $("#myModal").is(':visible') ? $("#unique-logins-modal").is(":checked") : $("#unique-logins-"+activeTab).is(":checked");
         
            $(".overlay").show();
            var active = $(this).closest(".small-box");
            var row = $(this).closest(".row");
            active.removeClass("inactive");
            $(this).html('<i class="fa fa-arrow-circle-left"></i> Clear Filter')
            $(this).removeClass("more-info");
            $(this).addClass("back-to-overall")
            // Set the other tiles to inactive
            row.find(".small-box").each(function() {
                if ($(this)[0] != active[0]) {
                    $(this).addClass("inactive");
                    $(this).find(".back-to-overall").each(function() {
                        $(this).html('More info <i class="fa fa-arrow-circle-right"></i>')
                        $(this).addClass("more-info");
                        $(this).removeClass("back-to-overall")
                    })

                }
            })
            var days = $(this).attr("data-days");
            fValues = new Array();
            var url_str = '<?php print $this->Html->url(array(
                                'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                'controller' => 'rciam_stats_viewer_services',
                                'action' => 'getlogincountperday',
                                'co'  => $cur_co['Co']['id']
                            )); ?>';
            getLoginCountPerDay(url_str, days, identifier, type, tabId, specific, unique_logins);
        })

        // DataTable Links Functionality 
        $(document).on("click", ".datatable-link", function() {
            identifier = $(this).attr("data-identifier")
            type = $(this).attr("data-type")
            legend = $(this).text();
            // if modal is visible check for modal's unique-logins checkbox else get checkbox from the tab
            activeTab = $("ul.tabset_tabs li.ui-tabs-active").attr("aria-controls").replace("Tab","");
            unique_logins = $("#myModal").is(':visible') ? $("#unique-logins-modal").is(":checked") : $("#unique-logins-"+activeTab).is(":checked");
            goToSpecificProvider(identifier, legend, type, unique_logins);
        })
        // When clicking unique logins at modal
        $(document).on("change", "#unique-logins-modal", function(){
            identifier = $(this).attr("data-identifier")
            type = $(this).attr("data-type")
            legend = $(this).attr("data-legend");
            unique_logins = $("#unique-logins-modal").is(":checked") 
            goToSpecificProvider(identifier, legend, type, unique_logins);
        })

        // When clicking unique logins at tab
        $(document).on("change", "input[id^=unique-logins-][id!=unique-logins-modal]", function(){
            type = $(this).attr("id").split("-")[2]
            getDataForTabs(type);
        })

        // When change Period at RegisteredUsers Column Chart
        $(document).on("change", "#dateRegisteredSelect, #dateCouSelect", function() {
            tab = $(this).closest(".box").attr("data-type")
            elementId = $(this).closest(".box").find(".columnChart").attr("id")
            updateColumnChart(document.getElementById(elementId), $(this).val(), false, tab);
        })

        // Draw IdP/ Sp  Charts when click at the tab or backToTotal for the first time 
        $(document).on("click", ".tabset_tabs li a", function() {
            if($(this).attr("data-draw") == "drawIdpsChart") {
                drawPieChart(document.getElementById('idpsChartDetail'), defaultdataIdp, "idp");
                $(this).attr("data-draw", "")
            } else if($(this).attr("data-draw") == "drawSpsChart") {
                drawPieChart(document.getElementById('spsChartDetail'), defaultdataSp, "sp");
                $(this).attr("data-draw", "")
            } else if($(this).attr("data-draw") == "drawUsersChart") { //Initialize whole registered users tab
                dataTiles = getDataForUsersTiles("registereds");
                updateColumnChart(document.getElementById("registeredsChartDetail"), 'yearly', true, 'registered');
                $(this).attr("data-draw", "")
            } else if($(this).attr("data-draw") == "drawCousChart") { //Initialize whole cous tab
                if(cou_general_stats) { // permission to see general stats for cous
                    updateColumnChart(document.getElementById("cousChartDetail"), 'yearly', true, 'cou');
                }

                let jqxhr = $.ajax({
                    url: url_str_userscousowner
                })
                jqxhr.done((data) => {
                    options = '<option></option>';
                    data.forEach(function(name, index) {
                        options += "<option data-created='" + data[index]["Cou"]["created"] + "' data-title='" + data[index]["Cou"]["name"] + "' data-description='" + data[index]["Cou"]["description"] + "' value='" + data[index]["CoGroup"]["cou_id"] + "'>" + data[index]["Cou"]["name"] + "</option>"
                    })
                    $(".perCouStatsSelect").append('Select Community: <select class="couStatsSelect">' + options + '</select>')
                })
                jqxhr.fail((xhr, textStatus, error) => {
                    handleFail(xhr, textStatus, error)
                })
                $(this).attr("data-draw", "")
            }
        })
    });

    google.charts.load('current', {
        'packages': ['corechart', 'controls', 'table', 'bar']
    });
    google.charts.setOnLoadCallback(function() {
        var data = google.visualization.arrayToDataTable([
            ['Date', 'Count'],
            <?php
            foreach ($vv_logincount_per_day as $record) {
                print "[new Date(" . $record[0]["year"] . "," . ($record[0]["month"] - 1) . ", " . $record[0]["day"] . "), {v:" . $record[0]["count"] . "}],";
            }
            ?>
        ]);
        drawLineChart(document.getElementById("loginsDashboard"), data)

        // Initialize Subtitle Date Ranges to tabs
        updateSubtitleDateRanges('dashboard', data);
        updateSubtitleDateRanges('idp', data);
        updateSubtitleDateRanges('sp', data);

        defaultdataIdp = google.visualization.arrayToDataTable([
            ['sourceIdp', 'sourceIdPEntityId', 'Count'],
            <?php
            foreach ($vv_logincount_per_idp as $record) {
                print "['" . str_replace("'", "\'", $record[0]["idpname"]) . "', '" . $record[0]["sourceidp"] . "', " . $record[0]["count"] . "],";
            }
            ?>
        ]);
        drawPieChart(document.getElementById("summaryIdPChart"), defaultdataIdp, "idp")

        defaultdataSp = google.visualization.arrayToDataTable([
            ['service', 'serviceIdentifier', 'Count'],
            <?php
            foreach ($vv_logincount_per_sp as $record) {
                print "['" . str_replace("'", "\'", $record[0]["spname"]) . "', '" . $record[0]["service"] . "', " .  $record[0]["count"] . "],";
            }
            ?>
        ]);
        drawPieChart(document.getElementById("summarySpChart"), defaultdataSp, "sp")
    });
</script>

<div class="box">
    <div class="box-body">
        <div id="tabs">
            <ul class="tabset_tabs" width="100px">
                <li><a href='#dashboardTab'><?php print _txt('pl.rciamstatsviewer.summary'); ?></a></li>
                <?php if ($vv_permissions["idp"]) : ?>
                    <li><a data-draw="drawIdpsChart" href='#idpTab'><?php print _txt('pl.rciamstatsviewer.idp.tabname.pl'); ?></a></li>
                <?php endif; ?>
                <?php if ($vv_permissions["sp"]) : ?>
                    <li><a data-draw="drawSpsChart" href='#spTab'><?php print _txt('pl.rciamstatsviewer.sp.tabname.pl'); ?></a></li>
                <?php endif; ?>
                <?php if ($vv_permissions["registered"]) : ?>
                    <li><a data-draw="drawUsersChart" href='#registeredTab'><?php print _txt('pl.rciamstatsviewer.registered.tabname.pl'); ?></a></li>
                <?php endif; ?>
                <?php if ($vv_permissions["cou"]) : ?>
                    <li><a data-draw="drawCousChart" href='#couTab'><?php print _txt('pl.rciamstatsviewer.cou.tabname.pl'); ?></a></li>
                <?php endif; ?>
            </ul>
            <?php
            print $this->element('dashboard');
            foreach ($vv_tab_settings as $key => $value) {
                if ($vv_permissions[$value['prefix']]) {
                    print $this->element($value['ctpName'], $value);
                }
            }
            ?>
        </div>
    </div>
    <div class="overlay">
        <div id="coSpinner"></div>
    </div>
</div>