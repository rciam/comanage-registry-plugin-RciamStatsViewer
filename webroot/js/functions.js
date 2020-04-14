function createTile(row, bgClass, value, text, days, type = null) {
    data_type = "";
    if (value == 0) {
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

    row.append('<div class="small-box ' + bgClass + '">' +
        '<div class="inner">' +
        '<h3>' + (value != 0 ? value : 0) + '</h3>' +
        '<p>' + text + '</p>' +
        '</div>' +
        '<div class="small-box-footer ' + nodata + '">No data</div>' +
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
    if (value == null) {
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
            $("#idpSpecificData .more-info").each(function () {
                $(this).attr("identifier", identifier);
                $(this).parent().removeClass("inactive");

            })

            $("#idpSpecificData").find(".back-to-overall").each(function () {
                $(this).html('More info <i class="fa fa-arrow-circle-right"></i>')
                $(this).addClass("more-info");
                $(this).removeClass("back-to-overall")
            })


            $.ajax({
                url: url_str,
                data: {
                    idp: identifier,
                },
                success: function (data) {
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
                    data['sp'].forEach(function (item) {
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

                    data['idp'].forEach(function (item) {
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
            $("#spSpecificData .more-info").each(function () {
                $(this).attr("identifier", identifier);
                $(this).parent().removeClass("inactive");
            })
            $("#spSpecificData").find(".back-to-overall").each(function () {
                $(this).html('More info <i class="fa fa-arrow-circle-right"></i>')
                $(this).addClass("more-info");
                $(this).removeClass("back-to-overall")
            })


            $.ajax({

                url: url_str,
                data: {
                    sp: identifier,
                },
                success: function (data) {

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
                    data['idp'].forEach(function (item) {
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

                    data['sp'].forEach(function (item) {
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