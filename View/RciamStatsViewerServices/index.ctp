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
print $this->Html->script('//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js');
print $this->Html->script("https://www.gstatic.com/charts/loader.js");

?>
<script type="text/javascript">
    var dashboard;
    var chartRangeFilter;
    $(function() {
        //Initialize Tabs
        var tabs = $("#tabs").tabs();

        //Initialize Datables
        $("#idpDatatable").DataTable({
            "order": [1, 'desc']
        });
        $("#spDatatable").DataTable({
            "order": [1, 'desc']
        });

        // Going Back to General Idp/ Sp Details
        $(document).on("click", ".backToTotal", function() {
            $(".overlay").show();
            idSpecData = $(this).parent().parent().attr("id");

            // $( "#"+idSpecData ).toggle("slide", {direction: "right"}, 500);
            $("#" + idSpecData).hide();

            if (idSpecData == "spSpecificData") {
                //$("#totalSpsInfo").toggle("slide", {direction: "left"}, 500);
                $("#totalSpsInfo").show()
            } else {
                //$("#totalIdpsInfo").toggle("slide", {direction: "left"}, 500);
                $("#totalIdpsInfo").show()
            }
            $(".overlay").hide();
        })

        $(document).on("click", ".back-to-overall", function() {
            var type = '';
            var linerangeChartId = "loginsDashboard";
            var identifier = null;
            var spChart = "summarySpChart";
            var idpChart = "summaryIdPChart";
            if ($(this).attr("data-type") != undefined) {
                type = $(this).attr("data-type");
                linerangeChartId = type + "loginsDashboard";
                identifier = $(this).attr("identifier");
                spChart = type + "SpecificChart";
                idpChart = type + "SpecificChart";
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

            var url_str = '<?php echo $this->Html->url(array(
                                'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                'controller' => 'rciam_stats_viewer_services',
                                'action' => 'getlogincountperday',
                                'co'  => $cur_co['Co']['id']
                            )); ?>';
            $.ajax({

                url: url_str,
                data: {
                    days: days,
                    identifier: identifier,
                    type: type
                },
                success: function(data) {

                    fValues = [];
                    fValues.push(['Date', 'Count'])
                    data['range'].forEach(function(item) {
                        var temp = [];
                        temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                        temp.push(parseInt(item[0]["count"]));
                        fValues.push(temp);
                    })

                    var dataRange = new google.visualization.arrayToDataTable(fValues);

                    drawLoginsChart(document.getElementById(linerangeChartId), dataRange, type)
                    if (type == '' || type == 'sp') {
                        fValues = [];
                        dataValues = "";
                        fValues.push(['sourceIdp', 'sourceIdPEntityId', 'Count'])
                        data['idps'].forEach(function(item) {
                            var temp = [];
                            temp.push(item[0]["idpname"]);
                            temp.push(item[0]["sourceidp"])
                            temp.push(parseInt(item[0]["count"]));
                            fValues.push(temp);
                        })
                        var dataIdp = new google.visualization.arrayToDataTable(fValues);
                        drawIdpsChart(document.getElementById(idpChart), dataIdp);
                    }
                    if (type == '' || type == 'idp') {
                        fValues = [];
                        dataValues = "";
                        fValues.push(['service', 'serviceIdentifier', 'Count'])
                        data['sps'].forEach(function(item) {
                            var temp = [];
                            temp.push(item[0]["spname"]);
                            temp.push(item[0]["service"])
                            temp.push(parseInt(item[0]["count"]));
                            fValues.push(temp);
                        })

                        var dataSp = new google.visualization.arrayToDataTable(fValues);
                        drawSpsChart(document.getElementById(spChart), dataSp);
                    }
                    $(".overlay").hide()
                }
            });

        })

        // Get Data For Specific Days 
        $(document).on("click", ".more-info", function() {
            var type = '';
            var linerangeChartId = "loginsDashboard";
            var identifier = null;
            var spChart = "summarySpChart";
            var idpChart = "summaryIdPChart";
            if ($(this).attr("data-type") != undefined) {
                type = $(this).attr("data-type");
                linerangeChartId = type + "loginsDashboard";
                identifier = $(this).attr("identifier");
                spChart = type + "SpecificChart";
                idpChart = type + "SpecificChart";
            }
            $(".overlay").show();
            // Set the other tiles to inactive
            var active = $(this).closest(".small-box");
            var row = $(this).closest(".row");
            active.removeClass("inactive");

            $(this).html('<i class="fa fa-arrow-circle-left"></i> Click again to reset')
            $(this).removeClass("more-info");
            $(this).addClass("back-to-overall")

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

            var url_str = '<?php echo $this->Html->url(array(
                                'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                'controller' => 'rciam_stats_viewer_services',
                                'action' => 'getlogincountperday',
                                'co'  => $cur_co['Co']['id']
                            )); ?>';
            $.ajax({

                url: url_str,
                data: {
                    days: days,
                    identifier: identifier,
                    type: type
                },
                success: function(data) {

                    fValues = [];
                    fValues.push(['Date', 'Count'])
                    data['range'].forEach(function(item) {
                        var temp = [];
                        temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                        temp.push(parseInt(item[0]["count"]));
                        fValues.push(temp);
                    })

                    var dataRange = new google.visualization.arrayToDataTable(fValues);

                    drawLoginsChart(document.getElementById(linerangeChartId), dataRange, type)
                    if (type == '' || type == 'sp') {
                        fValues = [];
                        dataValues = "";
                        fValues.push(['sourceIdp', 'sourceIdPEntityId', 'Count'])
                        data['idps'].forEach(function(item) {
                            var temp = [];
                            temp.push(item[0]["idpname"]);
                            temp.push(item[0]["sourceidp"])
                            temp.push(parseInt(item[0]["count"]));
                            fValues.push(temp);
                        })
                        var dataIdp = new google.visualization.arrayToDataTable(fValues);
                        drawIdpsChart(document.getElementById(idpChart), dataIdp);
                    }
                    if (type == '' || type == 'idp') {
                        fValues = [];
                        dataValues = "";
                        fValues.push(['service', 'serviceIdentifier', 'Count'])
                        data['sps'].forEach(function(item) {
                            var temp = [];
                            temp.push(item[0]["spname"]);
                            temp.push(item[0]["service"])
                            temp.push(parseInt(item[0]["count"]));
                            fValues.push(temp);
                        })

                        var dataSp = new google.visualization.arrayToDataTable(fValues);
                        drawSpsChart(document.getElementById(spChart), dataSp);
                    }
                    $(".overlay").hide()
                }
            });
        })

        // draw IdP/ Sp  Charts when click at the tab or backToTotal for the first time 
        $(document).on("click", ".tabset_tabs li a, .backToTotal", function() {
            if ($(this).hasClass("backToTotal")) {
                if ($(this).parent().parent().attr("id") == "idpSpecificData")
                    drawIdpsChart(document.getElementById('idpsChartDetail'));
                else
                    drawSpsChart(document.getElementById('spsChartDetail'));
            }
            if ($(this).attr("data-draw") == "drawIdpsChart") {
                drawIdpsChart(document.getElementById('idpsChartDetail'));
                $(this).attr("data-draw", "")

            } else if ($(this).attr("data-draw") == "drawSpsChart") {
                drawSpsChart(document.getElementById('spsChartDetail'));
                $(this).attr("data-draw", "")
            }
        })

    });

    google.charts.load('current', {
        'packages': ['corechart', 'controls', 'table']
    });
    google.charts.setOnLoadCallback(function() {
        drawLoginsChart(document.getElementById("loginsDashboard"))
        drawIdpsChart(document.getElementById("summaryIdPChart"))
        drawSpsChart(document.getElementById("summarySpChart"))
    });

    function setZerosIfNoDate(dataTable) {
        var datePattern = 'd.M.yy';
        var formatDate = new google.visualization.DateFormat({
            pattern: datePattern
        });
        var startDate = dataTable.getColumnRange(0).min;
        var endDate = dataTable.getColumnRange(0).max;
        var oneDay = (1000 * 60 * 60 * 24);
        for (var i = startDate.getTime(); i < endDate.getTime(); i = i + oneDay) {
            var coffeeData = dataTable.getFilteredRows([{
                column: 0,
                test: function(value, row, column, table) {
                    var coffeeDate = formatDate.formatValue(table.getValue(row, column));
                    var testDate = formatDate.formatValue(new Date(i));
                    return (coffeeDate === testDate);
                }
            }]);
            if (coffeeData.length === 0) {
                dataTable.addRow([
                    new Date(i),
                    0
                ]);
            }
        }
        dataTable.sort({
            column: 0
        });
        return dataTable;
    }

    // Hide more-info link for 0 logins
    function setHiddenElements(element, value) {
        console.log(element)
        console.log(value);
        if (value == null) {
            element.find(".more-info").addClass("hidden")
            element.find(".no-data").removeClass("hidden")
        } else {
            element.find(".more-info").removeClass("hidden")
            element.find(".no-data").addClass("hidden")
        }
    }

    // Line Chart - Range
    function drawLoginsChart(elementId, data = null, type = '') {
        console.log("range" + elementId)
        if (data == null) {
            var data = google.visualization.arrayToDataTable([
                ['Date', 'Count'],
                <?php
                foreach ($vv_logincount_per_day as $record) {
                    echo "[new Date(" . $record[0]["year"] . "," . ($record[0]["month"] - 1) . ", " . $record[0]["day"] . "), {v:" . $record[0]["count"] . "}],";
                }
                ?>
            ]);
        }
        if (data.getNumberOfRows() > 0)
            data = setZerosIfNoDate(data);
        cur_dashboard = new google.visualization.Dashboard(document.getElementById(elementId));

        chartRangeFilter = new google.visualization.ControlWrapper({
            controlType: 'ChartRangeFilter',
            containerId: type + 'control_div',
            options: {
                filterColumnLabel: 'Date',
                'ui': {
                    'chartType': 'LineChart',
                    'chartOptions': {
                        'chartArea': {
                            'width': '95%'
                        },
                    },
                }
            }
        });
        var chart = new google.visualization.ChartWrapper({
            'chartType': 'LineChart',
            'containerId': type + 'line_div',
            'options': {
                'legend': 'none'
            }
        });

        cur_dashboard.bind(chartRangeFilter, chart);
        cur_dashboard.draw(data);
    }


    // IdP Chart
    function drawIdpsChart(elementId, data = null) {
        if (data == null) {
            var data = google.visualization.arrayToDataTable([
                ['sourceIdp', 'sourceIdPEntityId', 'Count'],
                <?php
                foreach ($vv_logincount_per_idp as $record) {
                    echo "['" . str_replace("'", "\'", $record[0]["idpname"]) . "', '" . $record[0]["sourceidp"] . "', " . $record[0]["count"] . "],";
                }
                ?>
            ]);
        }

        data.sort([{
            column: 2,
            desc: true
        }]);
        var view = new google.visualization.DataView(data);
        view.setColumns([0, 2]);

        var options = {
            pieSliceText: 'value',
            width: '100%',
            height: '350',
            chartArea: {
                left: "3%",
                top: "3%",
                height: "94%",
                width: "94%"
            }
        };

        var chart = new google.visualization.PieChart(elementId);
        chart.draw(view, options);

        google.visualization.events.addListener(chart, 'select', selectHandler);

        function selectHandler() {
            $(".overlay").show();
            $('html,body').animate({
                scrollTop: 150
            }, 'slow');

            var selection = chart.getSelection();
            if (selection.length) {
                var identifier = data.getValue(selection[0].row, 1);
                var legend = data.getValue(selection[0].row, 0);
                //initialize tiles
                $("#idpSpecificData .more-info").each(function() {
                    $(this).attr("identifier", identifier);
                    $(this).parent().removeClass("inactive");

                })

                $("#idpSpecificData").find(".back-to-overall").each(function() {
                    $(this).html('More info <i class="fa fa-arrow-circle-right"></i>')
                    $(this).addClass("more-info");
                    $(this).removeClass("back-to-overall")
                })

                var url_str = '<?php echo $this->Html->url(array(
                                    'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                    'controller' => 'rciam_stats_viewer_services',
                                    'action' => 'getdataforidp',
                                    'co'  => $cur_co['Co']['id']
                                )); ?>';
                $.ajax({
                    url: url_str,
                    data: {
                        idp: identifier,
                    },
                    success: function(data) {
                        var ref_this = $("ul.tabset_tabs li.ui-state-active");
                        console.log(ref_this.attr("aria-controls"));
                        $('#tabs').tabs({
                            active: 1
                        }); // first tab selected

                        $("#idpSpecificData .bg-aqua h3").text(data['tiles'][0] != null ? data['tiles'][0] : 0);
                        setHiddenElements($("#idpSpecificData .bg-aqua"), data['tiles'][0])
                        $("#idpSpecificData .bg-green h3").text(data['tiles'][1] != null ? data['tiles'][1] : 0);
                        setHiddenElements($("#idpSpecificData .bg-green"), data['tiles'][1])
                        $("#idpSpecificData .bg-yellow h3").text(data['tiles'][2] != null ? data['tiles'][2] : 0);
                        setHiddenElements($("#idpSpecificData .bg-yellow"), data['tiles'][2])
                        $("#idpSpecificData .bg-red h3").text(data['tiles'][3] != null ? data['tiles'][3] : 0);
                        setHiddenElements($("#idpSpecificData .bg-red"), data['tiles'][3])
                        $("#idpSpecificData h1").html("<a href='#' onclick='return false;' style='font-size:2.5rem' class='backToTotal'>Identity Providers</a> > " + legend);
                        // Hide to left / show from left
                        //$("#totalIdpsInfo").toggle("slide", {direction: "left"}, 500);
                        $("#totalIdpsInfo").hide();
                        // Show from right / hide to right
                        //$("#idpSpecificData").toggle("slide", {direction: "right"}, 500);
                        $("#idpSpecificData").show();

                        fValues = [];
                        dataValues = "";
                        fValues.push(['service', 'serviceIdentifier', 'Count'])
                        data['sp'].forEach(function(item) {
                            var temp = [];
                            temp.push(item[0]["spname"]);
                            temp.push(item[0]["service"])
                            temp.push(parseInt(item[0]["count"]));
                            dataValues += "[" + new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]), parseInt(item[0]["count"]) + "],";
                            fValues.push(temp);
                        })

                        var dataSp = new google.visualization.arrayToDataTable(fValues);

                        drawSpsChart(document.getElementById("idpSpecificChart"), dataSp);

                        ////Draw Line - Range Chart
                        fValues = [];
                        fValues.push(['Date', 'Count'])

                        data['idp'].forEach(function(item) {
                            var temp = [];
                            temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                            temp.push(parseInt(item[0]["count"]));
                            fValues.push(temp);
                        })
                        var dataIdp = new google.visualization.arrayToDataTable(fValues);
                        drawLoginsChart(document.getElementById("idpsloginsDashboard"), dataIdp, 'idp')

                        $(".overlay").hide();
                    }
                });
            }
        }
    }

    // Sp Chart 
    function drawSpsChart(elementId, data = null) {
        if (data == null) {
            var data = google.visualization.arrayToDataTable([
                ['service', 'serviceIdentifier', 'Count'],
                <?php
                foreach ($vv_logincount_per_sp as $record) {
                    echo "['" . str_replace("'", "\'", $record[0]["spname"]) . "', '" . $record[0]["service"] . "', " .  $record[0]["count"] . "],";
                }
                ?>
            ]);
        }
        data.sort([{
            column: 2,
            desc: true
        }]);

        var view = new google.visualization.DataView(data);
        view.setColumns([0, 2]);

        var options = {
            pieSliceText: 'value',
            width: '100%',
            height: '350',
            chartArea: {
                left: "3%",
                top: "3%",
                height: "94%",
                width: "94%"
            }
        };

        var chart = new google.visualization.PieChart(elementId);

        chart.draw(view, options);

        google.visualization.events.addListener(chart, 'select', selectHandler);

        function selectHandler() {

            $(".overlay").show();
            $('html,body').animate({
                scrollTop: 150
            }, 'slow');
            var selection = chart.getSelection();
            if (selection.length) {
                var identifier = data.getValue(selection[0].row, 1);
                var legend = data.getValue(selection[0].row, 0);
                //initialize tiles
                $("#spSpecificData .more-info").each(function() {
                    $(this).attr("identifier", identifier);
                    $(this).parent().removeClass("inactive");
                })
                $("#spSpecificData").find(".back-to-overall").each(function() {
                    $(this).html('More info <i class="fa fa-arrow-circle-right"></i>')
                    $(this).addClass("more-info");
                    $(this).removeClass("back-to-overall")
                })

                var url_str = '<?php echo $this->Html->url(array(
                                    'plugin' => Inflector::singularize(Inflector::tableize($this->plugin)),
                                    'controller' => 'rciam_stats_viewer_services',
                                    'action' => 'getdataforsp',
                                    'co'  => $cur_co['Co']['id']
                                )); ?>';
                $.ajax({

                    url: url_str,
                    data: {
                        sp: identifier,
                    },
                    success: function(data) {

                        var ref_this = $("ul.tabset_tabs li.ui-state-active");
                        $('#tabs').tabs({
                            active: 2
                        }); // first tab selected
                        // initialize tiles
                        $("#spSpecificData .bg-aqua h3").text(data['tiles'][0] != null ? data['tiles'][0] : 0);
                        setHiddenElements($("#spSpecificData .bg-aqua"), data['tiles'][0])
                        $("#spSpecificData .bg-green h3").text(data['tiles'][1] != null ? data['tiles'][1] : 0);
                        setHiddenElements($("#spSpecificData .bg-green"), data['tiles'][1])
                        $("#spSpecificData .bg-yellow h3").text(data['tiles'][2] != null ? data['tiles'][2] : 0);
                        setHiddenElements($("#spSpecificData .bg-yellow"), data['tiles'][2])
                        $("#spSpecificData .bg-red h3").text(data['tiles'][3] != null ? data['tiles'][3] : 0);
                        setHiddenElements($("#spSpecificData .bg-red"), data['tiles'][3])
                        $("#spSpecificData h1").html("<a href='#' onclick='return false;' style='font-size:2.5rem' class='backToTotal'>Service Providers</a> > " + legend);
                        // Hide to left / show from left
                        //$("#totalSpsInfo").toggle("slide", {direction: "left"}, 500);
                        $("#totalSpsInfo").hide();

                        // Show from right / hide to right
                        //$("#spSpecificData").toggle("slide", {direction: "right"}, 500);
                        $("#spSpecificData").show();

                        fValues = [];
                        dataValues = "";
                        fValues.push(['sourceIdp', 'sourceIdPEntityId', 'Count'])
                        data['idp'].forEach(function(item) {
                            var temp = [];
                            temp.push(item[0]["idpname"]);
                            temp.push(item[0]["sourceidp"])
                            temp.push(parseInt(item[0]["count"]));
                            fValues.push(temp);
                        })

                        var dataSp = new google.visualization.arrayToDataTable(fValues);
                        drawIdpsChart(document.getElementById("spSpecificChart"), dataSp);

                        ////Draw Line - Range Chart
                        fValues = [];
                        fValues.push(['Date', 'Count'])

                        data['sp'].forEach(function(item) {
                            var temp = [];
                            temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                            temp.push(parseInt(item[0]["count"]));
                            fValues.push(temp);
                        })

                        var dataSp = new google.visualization.arrayToDataTable(fValues);
                        drawLoginsChart(document.getElementById("spsloginsDashboard"), dataSp, 'sp')

                        $(".overlay").hide();
                    }
                });
            }



            //var ul = $("#tabs").find( "ul" );
            // $( "<li><a href='#newtab'>New Tab</a></li>" ).appendTo( ul );
            //$( "<div id='newtab'><p>New Content</p></div>" ).appendTo( tabs );
            //$("#tabs").tabs( "refresh" );

        }
    }
</script>

<div class="box">
    <div class="box-body">
        <div id="tabs">
            <ul class="tabset_tabs" width="100px">
                <li><a href='#dashboardTab'>Summary</a></li>
                <li><a data-draw="drawIdpsChart" href='#idpProvidersTab'>Identity Providers Details</a></li>
                <li><a data-draw="drawSpsChart" href='#spProvidersTab'>Service Providers Details</a></li>
            </ul>
            <div id="dashboardTab">
                <h1>Summary</h1>
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3><?php echo ($vv_totalloginscount[0] != null ? $vv_totalloginscount[0] : 0); ?></h3>
                                <p>Todays Logins</p>
                            </div>
                            <?php
                            if ($vv_totalloginscount[0] == null) {
                                $nodata = "";
                                $more_info = "hidden";
                            } else {
                                $nodata = "hidden";
                                $more_info = "";
                            }
                            ?>
                            <div class="small-box-footer <?php echo $nodata; ?>">No data</div>
                            <a href="#" onlick="return false" data-days="1" class="more-info small-box-footer <?php echo $more_info; ?>">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-green">
                            <div class="inner">
                                <!--<h3>53<sup style="font-size: 20px">%</sup></h3>-->
                                <h3><?php echo ($vv_totalloginscount[1] != null ? $vv_totalloginscount[1] : 0); ?></h3>
                                <p>Last 7 days Logins</p>
                            </div>
                            <?php
                            if ($vv_totalloginscount[1] == null) {
                                $nodata = "";
                                $more_info = "hidden";
                            } else {
                                $nodata = "hidden";
                                $more_info = "";
                            }
                            ?>
                            <div class="small-box-footer <?php echo $nodata; ?>">No data</div>
                            <a href="#" onclick="return false" data-days="7" class="more-info small-box-footer <?php echo $more_info; ?>">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3><?php echo ($vv_totalloginscount[2] != null ? $vv_totalloginscount[2] : 0) ?></h3>
                                <p>Last 30 days Logins</p>
                            </div>
                            <?php
                            if ($vv_totalloginscount[2] == null) {
                                $nodata = "";
                                $more_info = "hidden";
                            } else {
                                $nodata = "hidden";
                                $more_info = "";
                            }
                            ?>
                            <div class="small-box-footer <?php echo $nodata; ?>">No data</div>
                            <a href="#" onclick="return false" data-days="30" class="more-info small-box-footer <?php echo $more_info; ?>">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3><?php echo ($vv_totalloginscount[3] != null ? $vv_totalloginscount[3] : 0) ?></h3>
                                <p>Last year logins</p>
                            </div>
                            <?php
                            if ($vv_totalloginscount[3] == null) {
                                $nodata = "";
                                $more_info = "hidden";
                            } else {
                                $nodata = "hidden";
                                $more_info = "";
                            }
                            ?>
                            <div class="small-box-footer <?php echo $nodata; ?>">No data</div>
                            <a href="#" onclick="return false" data-days="365" class="more-info small-box-footer <?php echo $more_info; ?>">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Overall number of logins per day</h3>
                    </div>
                    <div id="loginsDashboard">
                        <div id="line_div"></div>
                        <div id="control_div"></div>
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

            <div id="idpProvidersTab">
                <div id="idpSpecificData">
                    <h1></h1>
                    <div class="row">
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Todays Logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="1" data-type="idp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Last 7 days Logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="7" data-type="idp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Last 30 days Logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="30" data-type="idp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Last year logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="365" data-type="idp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Overall number of logins from this IdP per day</h3>
                                </div>
                                <div id="idpsloginsDashboard">
                                    <div id="idpline_div"></div>
                                    <div id="idpcontrol_div"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Service Providers that have been accessed by this Identity Provider</h3>
                                </div>
                                <div id="idpSpecificChart"></div>
                            </div>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <div id="totalIdpsInfo">
                    <h1>Identity Providers</h1>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Number of logins per Identity Provider</h3>
                            <div>Click a specific identity provider to view detailed statistics.</div>
                        </div>
                        <div id="idpsChartDetail"></div>
                    </div>

                    <!-- Create Datatable -->
                    <table id="idpDatatable" class="stripe row-border hover">
                        <thead>
                            <tr>
                                <th>Identity Providers</th>
                                <th>Number of Logins</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($vv_logincount_per_idp as $record) {
                                echo "<tr>";
                                echo "<td>" . str_replace("'", "\'", $record[0]["idpname"]) . "</td>";
                                echo "<td>" . $record[0]["count"] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="spProvidersTab">
                <div id="spSpecificData">
                    <h1></h1>
                    <div class="row">
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Todays Logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="1" data-type="sp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Last 7 days Logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="7" data-type="sp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Last 30 days Logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="30" data-type="sp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-xs-6">
                            <!-- small box -->
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3></h3>
                                    <p>Last year logins</p>
                                </div>
                                <div class="small-box-footer no-data">No data</div>
                                <a href="#" onclick="return false" data-days="365" data-type="sp" class="more-info small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Overall number of accesses to this Service Provider per day</h3>
                                </div>
                                <div id="spsloginsDashboard">
                                    <div id="spline_div"></div>
                                    <div id="spcontrol_div"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">IdP logins for this SP</h3>
                                </div>
                                <div id="spSpecificChart"></div>
                            </div>
                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <div id="totalSpsInfo">
                    <h1>Service Providers</h1>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Number of logins per Service Provider</h3>
                            <div>Click a specific service provider to view detailed statistics.</div>
                        </div>
                        <div id="spsChartDetail"></div>
                    </div>
                    <!-- Create Datatable -->
                    <table id="spDatatable" class="stripe row-border hover">
                        <thead>
                            <tr>
                                <th>Service Providers</th>
                                <th>Number of Logins</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($vv_logincount_per_sp as $record) {
                                echo "<tr>";
                                echo "<td>" . str_replace("'", "\'", $record[0]["spname"]) . "</td>";
                                echo "<td>" . $record[0]["count"] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="overlay">
        <i class="fa fa-refresh fa-spin"></i>
    </div>
</div>