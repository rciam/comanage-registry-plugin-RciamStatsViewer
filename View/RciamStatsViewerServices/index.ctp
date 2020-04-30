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
 * @since         COmanage Registry v2.0.0
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
print $this->Html->css('/RciamStatsViewer/css/bootstrap.min');
print $this->Html->css('/RciamStatsViewer/css/AdminLTE.min');
print $this->Html->css('/RciamStatsViewer/css/ionicons.min');
print $this->Html->css('/RciamStatsViewer/css/font-awesome.min');
print $this->Html->css('/RciamStatsViewer/css/style');
print $this->Html->css('//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css');
print $this->Html->css('https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css');
print $this->Html->script('//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js');
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js');
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js');
print $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js');
print $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js');
print $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js');
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js');
print $this->Html->script('//cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js');

print $this->Html->script("https://www.gstatic.com/charts/loader.js");
print $this->Html->script('/RciamStatsViewer/js/functions.js');
print $this->Html->script('/RciamStatsViewer/js/bootstrap.min.js');
?>
<script type="text/javascript">
    //Global Variables
    var defaultdataIdp, defaultdataSp;
    var datatableExport = <?php print (($permissions['idp']) ? 1 : 0) ?>;
    var overallText = [];
    var specificText = [];
    overallText['idp'] = '<?php print _txt('pl.rciamstatsviewer.idp.overall'); ?>'
    overallText['sp'] = '<?php print _txt('pl.rciamstatsviewer.sp.overall'); ?>'
    specificText['idp'] = '<?php print _txt('pl.rciamstatsviewer.idp.specific'); ?>'
    specificText['sp'] = '<?php print _txt('pl.rciamstatsviewer.sp.specific'); ?>'

    var url_str_idp = '<?php print $this->Html->url(array(
                            'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                            'controller' => 'rciam_stats_viewer_services',
                            'action' => 'getdataforidp',
                            'co'  => $cur_co['Co']['id']
                        )); ?>';
    var url_str_sp = '<?php print $this->Html->url(array(
                            'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                            'controller' => 'rciam_stats_viewer_services',
                            'action' => 'getdataforsp',
                            'co'  => $cur_co['Co']['id']
                        )); ?>';

    $(function() {

        // Initialize Tabs
        var tabs = $("#tabs").tabs();

        // Initialize Modal
        createModal();

        // Initialize Spinners
        $("div[id^=coSpinner]").each(function() {
            new Spinner(coSpinnerOpts).spin(document.getElementById($(this).attr("id")));
        })

        // Initialize Tiles
        var tabsIds = ["dashboardTab", "idpsTotalInfo", "spsTotalInfo"];
        tabsIds.forEach(function(item) {
            createTile($("#" + item + " .row .col-lg-3").eq(0), "bg-aqua", <?php print !empty($vv_totalloginscount[0]) ? $vv_totalloginscount[0] : '0'; ?>, "Todays Logins", 1, item)
            createTile($("#" + item + " .row .col-lg-3").eq(1), "bg-green", <?php print !empty($vv_totalloginscount[1]) ? $vv_totalloginscount[1] : '0'; ?>, "Last 7 days Logins", 7, item)
            createTile($("#" + item + " .row .col-lg-3").eq(2), "bg-yellow", <?php print !empty($vv_totalloginscount[2]) ? $vv_totalloginscount[2] : '0'; ?>, "Last 30 days Logins", 30, item)
            createTile($("#" + item + " .row .col-lg-3").eq(3), "bg-red", <?php print !empty($vv_totalloginscount[3]) ? $vv_totalloginscount[3] : '0'; ?>, "Last Year Logins", 365, item)
        });

        // Initialize Datatables
        $("#idpDatatable").DataTable({
            "order": [1, 'desc']
        });
        $("#spDatatable").DataTable({
            "order": [1, 'desc']
        });
        createDataTable($("#idpDatatableContainer"), <?php print json_encode($vv_logincount_per_idp); ?>, "idp", "idpDatatable")
        createDataTable($("#spDatatableContainer"), <?php print json_encode($vv_logincount_per_sp); ?>, "sp", "spDatatable")

        // when clear filter is clicked
        $(document).on("click", ".back-to-overall", function() {

            type = $(this).attr("data-type") != undefined ? $(this).attr("data-type") : '';
            tabId = $(this).attr("data-tab");
            specific = ($(this).attr("data-spec") != undefined ? $(this).attr("data-spec") : false);
            identifier = ($(this).attr("identifier") != undefined ? $(this).attr("identifier") : null);

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
            getLoginCountPerDay(url_str, days, identifier, type, tabId, specific);

        })

        // Get Data For Specific Days 
        $(document).on("click", ".more-info", function() {

            type = $(this).attr("data-type") != undefined ? $(this).attr("data-type") : '';
            tabId = $(this).attr("data-tab");
            specific = ($(this).attr("data-spec") != undefined ? $(this).attr("data-spec") : false);
            identifier = ($(this).attr("identifier") != undefined ? $(this).attr("identifier") : null);

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

            getLoginCountPerDay(url_str, days, identifier, type, tabId, specific);
        })


        // Datable Links Functionality 
        $(document).on("click", ".datatable-link", function() {
            identifier = $(this).attr("data-identifier")
            type = $(this).attr("data-type")
            legend = $(this).text();
            goToSpecificProvider(identifier, legend, type);
        })

        // Draw IdP/ Sp  Charts when click at the tab or backToTotal for the first time 
        $(document).on("click", ".tabset_tabs li a", function() {
            
            if ($(this).attr("data-draw") == "drawIdpsChart") {
                drawPieChart(document.getElementById('idpsChartDetail'), defaultdataIdp, "idp");
                $(this).attr("data-draw", "")

            } else if ($(this).attr("data-draw") == "drawSpsChart") {
                drawPieChart(document.getElementById('spsChartDetail'), defaultdataSp, "sp");
                $(this).attr("data-draw", "")
            }
        })

    });

    google.charts.load('current', {
        'packages': ['corechart', 'controls', 'table']
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
                <?php if ($permissions["idp"]) {?>
                    <li><a data-draw="drawIdpsChart" href='#idpTab'><?php print _txt('pl.rciamstatsviewer.idp_details.pl'); ?></a></li>
                <?php } ?>
                <?php if ($permissions["sp"]) {?>
                <li><a data-draw="drawSpsChart" href='#spTab'><?php print _txt('pl.rciamstatsviewer.sp_details.pl'); ?></a></li>
                <?php } ?>
            </ul>
            <?php
            print $this->element('dashboard');
            
                foreach ($vv_tab_settings as $key => $value) {
                    if($permissions[$value['prefix']]){
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