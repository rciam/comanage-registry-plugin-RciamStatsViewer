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
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
            'controlType': 'ChartRangeFilter',
            'containerId': 'control_div',
            'options': {
                'filterColumnLabel': 'Date'
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

</script>
<div id="loginsDashboard" >
    <div id="line_div"></div>
    <div id="control_div"></div>
</div>