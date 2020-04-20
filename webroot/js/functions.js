function createTile(row, bgClass, value, text, days, type = null) {
    data_type = "";
    if (value == 0 || value == null) {
        nodata = "";
        more_info = "hidden";
    } else {
        nodata = "hidden";
        more_info = "";
    }

    if (type == "idpSpecificData")
        data_type = 'data-type="idp"';
    else if (type == "spSpecificData")
        data_type = 'data-type="sp"';
    else if (type == 'totalIdpsInfo')
        data_type = 'data-type="totalIdps"';
    else if (type == 'totalSpsInfo')
        data_type = 'data-type="totalSps"';


    row.append('<div class="small-box ' + bgClass + '">' +
        '<div class="inner">' +
        '<h3>' + (value != 0 ? value : 0) + '</h3>' +
        '<p>' + text + '</p>' +
        '</div>' +
        '<div class="small-box-footer no-data ' + nodata + '">No data</div>' +
        '<a href="#" onclick="return false" ' + data_type + ' data-days="' + days + '" class="more-info small-box-footer ' + more_info + '">More info <i class="fa fa-arrow-circle-right"></i></a>' +
        '</div>');
}

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
            test: function (value, row, column, table) {
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
    if (value == null || value == 0) {
        element.find(".more-info").addClass("hidden")
        element.find(".no-data").removeClass("hidden")
    } else {
        element.find(".more-info").removeClass("hidden")
        element.find(".no-data").addClass("hidden")
    }
}

// Line Chart - Range
function drawLoginsChart(elementId, data, type = '') {

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
function drawIdpsChart(elementId, data, url_str) {

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
        },
        sliceVisibilityThreshold: .005,
        tooltip: { isHtml: true }
    };

    var chart = new google.visualization.PieChart(elementId);
    chart.draw(view, options);

    google.visualization.events.addListener(chart, 'select', selectHandler);

    function selectHandler() {
        var selection = chart.getSelection();
        if (selection.length) {

            var identifier = data.getValue(selection[0].row, 1);
            var legend = data.getValue(selection[0].row, 0);
            type = "idp";
            goToSpecificProvider(identifier, legend, type);
        }
    }
}

// Sp Chart 
function drawSpsChart(elementId, data, url_str) {

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
        },
        sliceVisibilityThreshold: .005,
        tooltip: { isHtml: true }
    };

    var chart = new google.visualization.PieChart(elementId);

    chart.draw(view, options);

    google.visualization.events.addListener(chart, 'select', selectHandler);

    function selectHandler() {
        var selection = chart.getSelection();
        if (selection.length) {

            var identifier = data.getValue(selection[0].row, 1);
            var legend = data.getValue(selection[0].row, 0);
            type = "sp";
            goToSpecificProvider(identifier, legend, type);
        }
        //var ul = $("#tabs").find( "ul" );
        // $( "<li><a href='#newtab'>New Tab</a></li>" ).appendTo( ul );
        //$( "<div id='newtab'><p>New Content</p></div>" ).appendTo( tabs );
        //$("#tabs").tabs( "refresh" );

    }
}

function getLoginCountPerDay(url_str, days, identifier, type, linerangeChartId, idpChart, spChart) {

    $.ajax({

        url: url_str,
        data: {
            days: days,
            identifier: identifier,
            type: type
        },
        success: function (data) {
            console.log(data)
            if (linerangeChartId != null) {
                fValues = [];
                fValues.push(['Date', 'Count'])
                data['range'].forEach(function (item) {
                    var temp = [];
                    temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                    temp.push(parseInt(item[0]["count"]));
                    fValues.push(temp);
                })

                var dataRange = new google.visualization.arrayToDataTable(fValues);

                drawLoginsChart(document.getElementById(linerangeChartId), dataRange, type)
            }
            if ((type == '' || type == 'sp') && idpChart != null) {
                fValues = [];
                dataValues = "";
                fValues.push(['sourceIdp', 'sourceIdPEntityId', 'Count'])
                data['idps'].forEach(function (item) {
                    var temp = [];
                    temp.push(item[0]["idpname"]);
                    temp.push(item[0]["sourceidp"])
                    temp.push(parseInt(item[0]["count"]));
                    fValues.push(temp);
                })
                var dataIdp = new google.visualization.arrayToDataTable(fValues);
                drawIdpsChart(document.getElementById(idpChart), dataIdp, url_str_idp);
                if (type == 'sp')
                    createDataTable($("#spSpecificDataTableContainer"), data['idps'], "idp")
                else if (type == '' && spChart == null) //for Identity Providers Details Tab
                    createDataTable($("#idpDatatableContainer"), data['idps'], "idp", "idpDatatable")
            }
            if ((type == '' || type == 'idp') && spChart != null) {
                fValues = [];
                dataValues = "";
                fValues.push(['service', 'serviceIdentifier', 'Count'])
                data['sps'].forEach(function (item) {
                    var temp = [];
                    temp.push(item[0]["spname"]);
                    temp.push(item[0]["service"])
                    temp.push(parseInt(item[0]["count"]));
                    fValues.push(temp);
                })

                var dataSp = new google.visualization.arrayToDataTable(fValues);
                drawSpsChart(document.getElementById(spChart), dataSp, url_str_sp);
                if (type == 'idp')
                    createDataTable($("#idpSpecificDataTableContainer"), data['sps'], "sp")
                else if (type == '' && idpChart == null) //for Service Providers Details Tab
                    createDataTable($("#spDatatableContainer"), data['sps'], "sp", "spDatatable")
            }

            $(".overlay").hide();
        },
        error: function (x, status, error) {
            if (x.status == 403) {
                //alert("Sorry, your session has expired. Please login again to continue");
                generateSessionExpiredNotification("Sorry, your session has expired. Please login again to continue","error");
                //location.reload();
            }
        }
    })
}

function goToSpecificProvider(identifier, legend, type) {
     $(".overlay").show();
    $('html,body').animate({
        scrollTop: 150
    }, 'slow');


    //initialize tiles
    $("#" + type + "SpecificData .more-info").each(function () {
        $(this).attr("identifier", identifier);
        $(this).parent().removeClass("inactive");

    })

    $("#" + type + "SpecificData").find(".back-to-overall").each(function () {
        $(this).html('More info <i class="fa fa-arrow-circle-right"></i>')
        $(this).addClass("more-info");
        $(this).removeClass("back-to-overall")
    })

    if (type == "idp") {
        url_str = url_str_idp
        obj = { idp: identifier };
        tab_active = 1;
        root_title = 'Identity Providers';

    }
    else {
        url_str = url_str_sp
        obj = { sp: identifier };
        tab_active = 2;
        root_title = 'Service Providers';
    }
    $.ajax({
        url: url_str,
        data: obj,
        success: function (data) {
            var ref_this = $("ul.tabset_tabs li.ui-state-active");
            console.log(ref_this.attr("aria-controls"));
            $('#tabs').tabs({
                active: tab_active
            }); // first tab selected

            $("#" + type + "SpecificData .bg-aqua h3").text(data['tiles'][0] != null ? data['tiles'][0] : 0);
            setHiddenElements($("#" + type + "SpecificData .bg-aqua"), data['tiles'][0])
            $("#" + type + "SpecificData .bg-green h3").text(data['tiles'][1] != null ? data['tiles'][1] : 0);
            setHiddenElements($("#" + type + "SpecificData .bg-green"), data['tiles'][1])
            $("#" + type + "SpecificData .bg-yellow h3").text(data['tiles'][2] != null ? data['tiles'][2] : 0);
            setHiddenElements($("#" + type + "SpecificData .bg-yellow"), data['tiles'][2])
            $("#" + type + "SpecificData .bg-red h3").text(data['tiles'][3] != null ? data['tiles'][3] : 0);
            setHiddenElements($("#" + type + "SpecificData .bg-red"), data['tiles'][3])
            $("#" + type + "SpecificData h1").html("<a href='#' onclick='return false;' style='font-size:2.5rem' class='backToTotal'>" + root_title + "</a> > " + legend);
            // Hide to left / show from left
            //$("#totalIdpsInfo").toggle("slide", {direction: "left"}, 500);
            $("#total" + type.charAt(0).toUpperCase() + type.slice(1) + "sInfo").hide();
            // Show from right / hide to right
            //$("#idpSpecificData").toggle("slide", {direction: "right"}, 500);
            $("#" + type + "SpecificData").show();

            fValues = [];
            dataValues = "";
            if (type == 'idp') {
                columnNames = ['service', 'serviceIdentifier', 'Count'];
                dataCol = 'sp';
                columns = ['spname', 'service', 'count'];
            }
            else {
                columnNames = ['sourceIdp', 'sourceIdPEntityId', 'Count'];
                dataCol = 'idp';
                columns = ['idpname', 'sourceidp', 'count']
            }
            fValues.push(columnNames)
            data[dataCol].forEach(function (item) {
                var temp = [];
                temp.push(item[0][columns[0]]);
                temp.push(item[0][columns[1]])
                temp.push(parseInt(item[0][columns[2]]));
                dataValues += "[" + new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]), parseInt(item[0]["count"]) + "],";
                fValues.push(temp);
            })


            var dataTable = new google.visualization.arrayToDataTable(fValues);
            if (type == "idp")
                drawSpsChart(document.getElementById(type + "SpecificChart"), dataTable);
            else
                drawIdpsChart(document.getElementById(type + "SpecificChart"), dataTable);
            ////Draw Line - Range Chart
            fValues = [];
            fValues.push(['Date', 'Count'])

            data[type].forEach(function (item) {
                var temp = [];
                temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                temp.push(parseInt(item[0]["count"]));
                fValues.push(temp);
            })
            var dataTable = new google.visualization.arrayToDataTable(fValues);
            drawLoginsChart(document.getElementById(type + "sloginsDashboard"), dataTable, type)

            createDataTable($("#" + type + "SpecificDataTableContainer"), data[dataCol], dataCol)
            $(".overlay").hide();
        },
        error: function (x, status, error) {
            if (x.status == 403) {
                //alert("Sorry, your session has expired. Please login again to continue");
                generateSessionExpiredNotification("Sorry, your session has expired. Please login again to continue","error");
               
            }
        }
    });
}



function createDataTable(element, data, type, idDataTable = null) {
    console.log("Creating Datatable...")
    console.log(type)
    console.log(idDataTable)
    if (type == "idp") {
        column1 = 'idpname'
        column2 = 'count'
        data_param = 'sourceidp'
        th = 'Identity Providers'
    }
    else {
        column1 = 'spname'
        column2 = 'count'
        data_param = 'service'
        th = 'Service Providers'
    }
    dataAppend = '';
    data.forEach(function (item) {
        dataAppend += '<tr><td><a class="datatable-link" href="#" onclick="return false;" data-type="' + type + '" data-identifier="' + item[0][data_param] + '">' + item[0][column1] + '</a></td><td>' + item[0][column2] + '</td></tr>';
    })

    id = (idDataTable != null ? idDataTable : type + 'SpecificDatatable');
    element.html('<table id="' + id + '" class="stripe row-border hover">' +
        '<thead>' +
        '<tr>' +
        '<th>' + th + '</th>' +
        '<th>Number of Logins</th>' +
        '</tr>' +
        '</thead>' +
        '<tbody>' +
        dataAppend +
        '</tbody>' +
        '</table>');
    $("#" + id).DataTable({
        "order": [1, 'desc'],
    });

}


    // Generate flash notifications for messages
    function generateSessionExpiredNotification(text, type) {
        var n = noty({
            text: text,
            type: type,
            dismissQueue: true,
            layout: 'topCenter',
            theme: 'comanage',
            buttons: [
                {
                    addClass: 'general-button red', text: 'Ok', onClick: function($noty)
                    {
                          
                       $noty.close();
                       location.reload();
                    }
                },
                ]
        });
    }