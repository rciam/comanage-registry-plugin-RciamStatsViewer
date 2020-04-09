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

//var_dump($vv_logincount_per_day);
?>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
<script type="text/javascript" src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<style>
#control_div {
    height:50px;
    
}
#control_div *{
    font-size: 0.98em!important;
    
}
#idpsChartDetail, #spsChartDetail{
    padding-top:60px;

}
#idpDatatable_wrapper, #spDatatable_wrapper{
    margin-top:100px;
}
#idpDatatable, #spDatatable{
    padding-top: 15px;
}


</style>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
  $( function() {
    $( "#tabs" ).tabs();
    $( "#idpDatatable" ).DataTable( {
        "order": [1,'desc']
    });
    $( "#spDatatable" ).DataTable({
        "order": [1,'desc']
    });
    
    $( ".tabset_tabs li a" ).on("click", function(){
        if($(this).attr("data-draw")=="drawIdpsChart")
            drawIdpsChart();    
        else if($(this).attr("data-draw")=="drawSpsChart")
            drawSpsChart();    
        })
    } );  
</script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});
    google.charts.setOnLoadCallback(drawLoginsChart);

    function drawLoginsChart() {
        var data = google.visualization.arrayToDataTable([
            ['Date', 'Count'],
            <?php 
                foreach ($vv_logincount_per_day as $record){
                     echo "[new Date(".$record[0]["year"].",". ($record[0]["month"] - 1 ). ", ".$record[0]["day"]."), {v:".$record[0]["count"]."}],";
                 }
            ?>
        ]);

        var dashboard = new google.visualization.Dashboard(document.getElementById('loginsDashboard'));

        var chartRangeFilter=new google.visualization.ControlWrapper({
            controlType: 'ChartRangeFilter',
            containerId: 'control_div',
            options: {
                filterColumnLabel: 'Date',
                'ui': {
                    'chartType': 'LineChart',
                    'chartOptions': {
                    'chartArea': {'width': '95%'},
                },
             }
          }
        });
        var chart = new google.visualization.ChartWrapper({
            'chartType' : 'LineChart',
            'containerId' : 'line_div',
            'options':{
                'legend' : 'none'
            }
        });

        dashboard.bind(chartRangeFilter, chart);
        dashboard.draw(data);

    }
    
    // IdP Details Tab

    function drawIdpsChart() {
        var data = google.visualization.arrayToDataTable([
            ['sourceIdp', 'sourceIdPEntityId', 'Count'],
            <?php 
                foreach ($vv_logincount_per_idp as $record){
                    echo "['" . str_replace("'", "\'", $record[0]["idpname"]) . "', '" . $record[0]["sourceidp"] . "', " . $record[0]["count"] . "],";
                }
            ?>
        ]);

        data.sort([{column: 2, desc: true}]);

        var view = new google.visualization.DataView(data);

        view.setColumns([0,2]);

        var options = {
            pieSliceText: 'value',
            width: '100%',
            height: '300',
            chartArea: {
                left: "3%",
                top: "3%",
                height: "94%",
                width: "94%"
            }
        };

        var chart = new google.visualization.PieChart(document.getElementById('idpsChartDetail'));
        chart.draw(view, options);

        google.visualization.events.addListener(chart, 'select', selectHandler);

        function selectHandler() {
            var selection = chart.getSelection();
            if (selection.length) {
                var entityId = data.getValue(selection[0].row, 1);
                window.location.href = 'idpDetail.php?entityId=' + entityId;
            }
        }
    }

    //Sp Details
    function drawSpsChart() {
        var data = google.visualization.arrayToDataTable([
            ['service', 'serviceIdentifier', 'Count'],
            <?php 
                foreach ($vv_logincount_per_sp as $record){
                    //echo "['" . str_replace("'", "\'", $record[0]["idpname"]) . "', '" . $record[0]["sourceidp"] . "', " . $record[0]["count"] . "],";
                    echo "['" . str_replace("'", "\'", $record[0]["spname"]) . "', '". $record[0]["service"] . "', " .  $record[0]["count"] . "],";
                }
            ?>
        ]);

        data.sort([{column: 2, desc: true}]);

        var view = new google.visualization.DataView(data);

        view.setColumns([0,2]);

        var options = {
            pieSliceText: 'value',
            width: '100%',
            height: '400',
            chartArea: {
                left: "3%",
                top: "3%",
                height: "94%",
                width: "94%"
            }
        };

        var chart = new google.visualization.PieChart(document.getElementById('spsChartDetail'));

        chart.draw(view, options);

        google.visualization.events.addListener(chart, 'select', selectHandler);

        function selectHandler() {
            var selection = chart.getSelection();
            if (selection.length) {
                var identifier = data.getValue(selection[0].row, 1);
                window.location.href = 'spDetail.php?identifier=' + identifier;
            }
        }
    }

</script>

<div id="tabs">
    <ul class="tabset_tabs" width="100px">
        <li><a href='#dashboardTab'>Summary</a></li>
        <li><a data-draw="drawIdpsChart" href='#idpProvidersTab'>Identity Providers Details</a></li>
        <li><a data-draw="drawSpsChart" href='#spProvidersTab'>Service Providers Details</a></li>
    </ul>
    <div id="dashboardTab">
        <h1>Number of Logins</h1>
        <div class="legend-logins">
            The chart shows overall number of logins from identity providers for each day.
        </div>
        <div id="loginsDashboard">
            <div id="line_div"></div>
            <div id="control_div"></div>
        </div>
    </div>

    <div id="idpProvidersTab">
        <h1>Identity Providers</h1>
        <div>The chart and the table show number of logins from each identity provider in selected time range. Click a specific identity provider to view detailed statistics for that identity provider.</div>
        <div id="idpsChartDetail" style="width:100%;"></div>
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
                    foreach ($vv_logincount_per_idp as $record){
                        echo "<tr>";
                        echo "<td>" . str_replace("'", "\'", $record[0]["idpname"]) . "</td>";
                        echo "<td>" . $record[0]["count"] . "</td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>

    <div id="spProvidersTab">
        <h1>Service Providers</h1>
        <div id="spsChartDetail"></div>
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
                    foreach ($vv_logincount_per_sp as $record){
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

