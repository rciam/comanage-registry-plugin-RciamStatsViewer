function createTile(row, bgClass, value, text, days, type = null) {

    if (value == 0 || value == null) {
        nodata = "";
        more_info = "hidden";
    } else {
        nodata = "hidden";
        more_info = "";
    }
    
    data_type = 'data-tab="dashboard"';
    if (type == "idpSpecificData")
        data_type = 'data-type="idp" data-tab="idp" data-spec="specific"';
    else if (type == "spSpecificData")
        data_type = 'data-type="sp" data-tab="sp" data-spec="specific"';
    else if (type == 'idpsTotalInfo')
        data_type = 'data-tab="idp" data-spec="total"';
    else if (type == 'spsTotalInfo')
        data_type = 'data-tab="sp" data-spec="total"';

    if (type != 'registerdTotalInfo') {
        row.html('<div class="small-box ' + bgClass + '">' +
            '<div class="inner">' +
            '<h3>' + (value != 0 ? value : 0) + '</h3>' +
            '<p>' + text + '</p>' +
            '</div>' +
            '<div class="small-box-footer no-data ' + nodata + '">No data</div>' +
            '<a href="#" onclick="return false" ' + data_type + ' data-days="' + days + '" class="more-info small-box-footer ' + more_info + '">More info <i class="fa fa-arrow-circle-right"></i></a>' +
            '</div>');
    }
    else {
        row.html('<div class="small-box ' + bgClass + '">' +
            '<div class="inner">' +
            '<h3>' + (value != 0 ? value : 0) + '</h3>' +
            '<p>' + text + '</p>' +
            '</div>' +
            '</div>');
    }
}

// Create Modal
function createModal(){
    $("body").append('<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'+
        '<div class="modal-dialog modal-xl" role="document">'+
            '<div class="modal-content  overlay-wrapper">'+
            '<div class="modal-header">'+
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                '<h1 class="modal-title" id="myModalLabel">Modal title</h1>'+
            '</div>'+
            '<div class="modal-body">'+
                '<div class="specificData" id="specificData">'+         
                    '<p class="subTitle"></p>'+
                    '<div class="row">'+
                        '<div class="col-lg-3 col-xs-6">'+
                            '<!-- small box -->'+
                    '</div>'+
                        '<!-- ./col -->'+
                    '<div class="col-lg-3 col-xs-6">'+
                            '<!-- small box -->'+
                    '</div>'+
                        '<!-- ./col -->'+
                    '<div class="col-lg-3 col-xs-6">'+
                            '<!-- small box -->'+
                    '</div>'+
                        '<!-- ./col -->'+
                    '<div class="col-lg-3 col-xs-6">'+
                            '<!-- small box -->'+
                    '</div>'+
                        '<!-- ./col -->'+
                    '</div>'+
                        '<div class="row">'+
                            '<div class="col-lg-12">'+
                                '<div class="box">'+
                                    '<div class="box-header with-border">'+
                                        '<h3 class="box-title"></h3>'+
                                    '</div>'+
                                    '<div class="lineChart" id="loginLineChart">'+
                                        '<div id="modalline_div"></div>'+
                                        '<div id="modalcontrol_div" style="height:50px"></div>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="row">'+
                            '<div class="col-lg-12">'+
                                '<div class="box">'+
                                    '<div class="box-header with-border">'+
                                        '<h3 class="box-title"></h3>'+
                                    '</div>'+
                                    '<div class="pieChart" id="specificChart"></div>'+
                                '</div>'+
                                '<div class="dataTableContainer" id="specificDataTableContainer"></div>'+
                            '</div>'+
                        '</div>'+
                        '<!-- ./col -->'+
                '</div>'+
            '</div>'+
            '<div class="modal-footer">'+
            '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'+
            '</div>'+
            '<div class="overlay">'+
                '<div id="coSpinnerModal"></div>'+
            '</div>'+
        '</div>'+
    '</div>'+
'</div>');
}

// This is for Dates with no logins, we have to set 0 for these dates
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

// Hide more-info link/ show no data for 0 logins
function setHiddenElements(element, value) {

    if (value == null || value == 0) {
        element.find(".more-info").addClass("hidden")
        element.find(".no-data").removeClass("hidden")
    } else {
        element.find(".more-info").removeClass("hidden")
        element.find(".no-data").addClass("hidden")
    }
}

// Line Chart with Range
function drawLineChart(elementId, data, type = '') {

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

// Pie Chart
function drawPieChart(elementId, data, type) {

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
        tooltip: { isHtml: true, trigger: 'selection' }
    };

    var chart = new google.visualization.PieChart(elementId);
    chart.draw(view, options);


    google.visualization.events.addListener(chart, 'onmouseover', function (entry) {
        chart.setSelection([{ row: entry.row }]);
        
        //Add Identifier to tooltip
        $("#" + chart.container.id + " .google-visualization-tooltip-item-list li:eq(0)").append('<li> (' + data.getValue(entry.row, 1) + ')</li>').css("font-family", "Arial");

        widthNew = data.getValue(entry.row, 1).length * 9;
        heightNew =  $("#" + chart.container.id + " .google-visualization-tooltip").height() + 30;

        if (widthNew >  $("#" + chart.container.id + " .google-visualization-tooltip").outerWidth())
        $("#" + chart.container.id + " .google-visualization-tooltip").css("width", widthNew + "px")
        $("#" + chart.container.id + " .google-visualization-tooltip").css("height", heightNew + "px")

    });

    google.visualization.events.addListener(chart, 'click', selectHandler);
    google.visualization.events.addListener(chart, 'onmouseover', uselessHandler2);
    google.visualization.events.addListener(chart, 'onmouseout', uselessHandler3);
    function uselessHandler2() {
        $('.pieChart').css('cursor','pointer')
         }  
    function uselessHandler3() {
        chart.setSelection([]);
        $('.pieChart').css('cursor','default')
         } 
    function selectHandler() {
        var selection = chart.getSelection();
        if (selection.length) {
            var identifier = data.getValue(selection[0].row, 1);
            var legend = data.getValue(selection[0].row, 0);
            goToSpecificProvider(identifier, legend, type);
        }
    }
}

// Column Chart
function drawColumnChart(elementId, data, type, hticks = null) {
    if (type == 'monthly') {
        format = 'MM/YY'
    //    formatter = new google.visualization.DateFormat({ pattern: 'MM/YY' })
      //  formatter.format(data, 0)
    }
    else if (type == 'yearly') {
        format = 'Y'
       // formatter = new google.visualization.DateFormat({ pattern: 'yyyy' })
        //formatter.format(data, 0)
    }
    else if (type == 'weekly') {
        format = ''
        //console.log(hticks)
        //formatter = new google.visualization.DateFormat({ pattern: 'dd/M/YY' })
        //formatter.format(data, 0)
    }
   // console.log(data)
   // console.log(data.getDistinctValues(0))
    data.sort([{
        column: 1,
        desc: false
    }]);
    //var view = new google.visualization.DataView(data);
    //view.setColumns([0, 2]);
    var options = {
        hAxis: {
            format: format,
            //slantedText: true,
            maxTextLines: 2,
            textStyle: {fontSize: 15},
            ticks: (type != 'weekly' ? data.getDistinctValues(0) : hticks)
        },
        tooltip: {isHtml: true},
        width: '100%',
        height: '350',
        bar: { groupWidth: "95%" },
        legend: { position: "none" },
    };

    var chart = new google.visualization.ColumnChart(elementId);
    chart.draw(data, options);
}

function getWeekNumber(d) {
    // Copy date so don't modify original
    d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    // Set to nearest Thursday: current date + 4 - current day number
    // Make Sunday's day number 7
    d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
    // Get first day of year
    var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
    // Calculate full weeks to nearest Thursday
    var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
    // Return array of year and week number
    return weekNo + ' (' + d.getUTCFullYear() + ')';
}

// Update Column Chart AJAX 
function updateColumnChart(elementId, range = null, init = false) {
    $(".overlay").show();
    $.ajax({
        url: url_str_userschart,
        data: {
            range: range,      
        },
        success: function (data) {
            
            fValues = [];
            hticks = [];
                fValues.push(['Date', 'Count' , {'type': 'string', 'role': 'tooltip', 'p': {'html': true}}])
                data.forEach(function (item) {
                    var temp = [];    
                    valueRange = new Date(item[0]['range_date']);
                    
                    temp.push(valueRange);
                    temp.push(parseInt(item[0]['count']));
                    temp.push('<div style="padding:5px 5px 5px 5px;">' + convertDateByGroup (valueRange, range) + "<br/> Registered Users: " + parseInt(item[0]['count']) + '</div>');
                    hticks.push({v: valueRange, f: getWeekNumber(valueRange)})
                    fValues.push(temp);
                })
            //console.log(fValues)
            var dataRange = new google.visualization.arrayToDataTable(fValues);
            drawColumnChart(elementId, dataRange, range, hticks)
            if(init === true){
                
                data.forEach(function (item){
                    newDate = new Date(item[0]['range_date']);
                    fDate = newDate.getMonth()+1
                    if (fDate < 10)
                        fDate = '0' + fDate
                    item[0]['show_date'] = fDate + "/" + newDate.getFullYear();
                })
                var options = {}
                options['idDataTable'] = 'registeredDatatable'
                options['title'] = 'Registered Users the Last 12 Months'
                createDataTable($("#registeredDatatableContainer"), data , "registered", options)

            }
            $(".overlay").hide();
        }
    })
}

function getLoginCountPerDay(url_str, days, identifier, type, tabId, specific) {

    $.ajax({
        url: url_str,
        data: {
            days: days,
            identifier: identifier,
            type: type
        },
        success: function (data) {
            element = "#" + tabId + 'Tab'

            if (specific == "specific"){
                element = '.modal-body .specificData'
                type = 'modal'
            }
            else  if (specific != false)
                element += ' .' + specific + 'Data'

            if ($(element + " .lineChart").length > 0) {
                fValues = [];
                fValues.push(['Date', 'Count'])
                data['range'].forEach(function (item) {
                    var temp = [];
                    temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                    temp.push(parseInt(item[0]["count"]));
                    fValues.push(temp);
                })
                
                var dataRange = new google.visualization.arrayToDataTable(fValues);
                drawLineChart($(element + " .lineChart"), dataRange, type)
            }
            if (tabId == 'dashboard' || (tabId == 'idp' && specific == 'total') || (tabId == 'sp' && specific == 'specific')) {
                //Summary. Idp Total or SP specific
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

                if (tabId == 'dashboard') { // Dashboard has 2 pieCharts
                    pieId = $(element + " .pieChart").eq(0).attr("id");
                }
                else {
                    pieId = $(element + " .pieChart").attr("id");
                }
                drawPieChart(document.getElementById(pieId), dataIdp, "idp");
                if (tabId == 'sp' && specific == 'specific')
                    createDataTable($(element + " .dataTableContainer"), data['idps'], "idp")
                else if (tabId == 'idp' && specific == 'total'){ //for Identity Providers Details Tab
                    var options = {}
                    options['idDataTable'] = 'idpDatatable'
                    createDataTable($(element + " .dataTableContainer"), data['idps'], "idp", options)
                }
            }
            if (tabId == 'dashboard' || (tabId == 'sp' && specific == 'total') || (tabId == 'idp' && specific == 'specific')) {

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

                if (tabId == 'dashboard') {
                    pieId = $(element + " .pieChart").eq(0).attr("id");
                }
                else {
                    pieId = $(element + " .pieChart").attr("id");
                }

                drawPieChart(document.getElementById(pieId), dataSp, "sp");
                if (tabId == 'idp' && specific == 'specific')
                    createDataTable($(element + " .dataTableContainer"), data['sps'], "sp")
                else if (tabId == 'sp' && specific == 'total') { //for Service Providers Details Tab
                    var options = {}
                    options['idDataTable'] = 'spDatatable'
                    createDataTable($(element + " .dataTableContainer"), data['sps'], "sp", options)
                }
            }

            $(".overlay").hide();
        },
        error: function (x, status, error) {
            if (x.status == 403) {
                generateSessionExpiredNotification("Sorry, your session has expired. Please click here to renew your session.", "error");

            }
        }
    })
}

// Modal Functionality
function goToSpecificProvider(identifier, legend, type) {
    $("#myModal").modal();
    
    $(".modal .overlay").show();
     $('#myModal').animate({
       scrollTop: 0
     }, 'slow');
     
    item="specificData";

    //initialize tiles
    createTile($("#" + item + " .row .col-lg-3").eq(0), "bg-aqua", "0", "Todays Logins", 1, type+"SpecificData")
    createTile($("#" + item + " .row .col-lg-3").eq(1), "bg-green", "0", "Last 7 days Logins", 7, type+"SpecificData")
    createTile($("#" + item + " .row .col-lg-3").eq(2), "bg-yellow", "0", "Last 30 days Logins", 30, type+"SpecificData")
    createTile($("#" + item + " .row .col-lg-3").eq(3), "bg-red", "0", "Last Year Logins", 365, type+"SpecificData")

    
    $("#specificData .more-info").each(function () {
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
    }
    else {
        url_str = url_str_sp
        obj = { sp: identifier };
    }
    $.ajax({
        url: url_str,
        data: obj,
        success: function (data) {
        
            $(".modal-body .specificData .bg-aqua h3").text(data['tiles'][0] != null ? data['tiles'][0] : 0);
            setHiddenElements($(".modal-body .specificData .bg-aqua"), data['tiles'][0])
            $(".modal-body .specificData .bg-green h3").text(data['tiles'][1] != null ? data['tiles'][1] : 0);
            setHiddenElements($(".modal-body .specificData .bg-green"), data['tiles'][1])
            $(".modal-body .specificData .bg-yellow h3").text(data['tiles'][2] != null ? data['tiles'][2] : 0);
            setHiddenElements($(".modal-body .specificData .bg-yellow"), data['tiles'][2])
            $(".modal-body .specificData .bg-red h3").text(data['tiles'][3] != null ? data['tiles'][3] : 0);
            setHiddenElements($(".modal-body .specificData .bg-red"), data['tiles'][3])
            $("h1.modal-title").html(legend);
            $(".modal-body .specificData > p").html("<b>Identifier:</b> " + identifier);
        
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
            $("#specificChart").closest(".box").find(".box-title").html(specificText[type])
            if (type == "idp")
                drawPieChart(document.getElementById("specificChart"), dataTable, "sp");
            else
                drawPieChart(document.getElementById("specificChart"), dataTable, "idp");
            
            //Draw Line - Range Chart
            fValues = [];
            fValues.push(['Date', 'Count'])

            data[type].forEach(function (item) {
                var temp = [];
                temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
                temp.push(parseInt(item[0]["count"]));
                fValues.push(temp);
            })
            var dataTable = new google.visualization.arrayToDataTable(fValues);
            
            $("#loginLineChart").closest(".box").find(".box-title").html(overallText[type])
            drawLineChart(document.getElementById("loginLineChart"), dataTable, 'modal')

            createDataTable($("#specificDataTableContainer"), data[dataCol], dataCol)
            $(".modal .overlay").hide();
            
        },
        error: function (x, status, error) {
            if (x.status == 403) {
                generateSessionExpiredNotification("Sorry, your session has expired. Please click here to renew your session.", "error");

            }
        }
    });
}


function convertDate(jsDate){
    date = null;
    if (jsDate != null && jsDate instanceof Date) {
        month = (jsDate.getMonth() + 1).toString()
        if (month.length < 2)
            month = '0' + month;
        day = jsDate.getDate().toString()
        if (day.length < 2)
            day = '0' + day;
        date = jsDate.getFullYear() + '-' + month + '-' + day;
    }
    return date;
}

function convertDateByGroup(jsDate, groupBy) {

    month = (jsDate.getMonth() + 1).toString()
    if (month.length < 2)
        month = '0' + month;
    day = jsDate.getDate().toString()
    if (day.length < 2)
        day = '0' + day;
    if (groupBy == 'daily')
        showDate = day + '/' + month + '/' + jsDate.getFullYear();
    else if (groupBy == 'weekly') {
        showDate = day + '/' + month + '/' + jsDate.getFullYear();
        console.log(showDate + "WEEK DATE")
        var nextWeek = new Date(jsDate.setDate(jsDate.getDate() + 6));
        month = (nextWeek.getMonth() + 1).toString()
        if (month.length < 2)
            month = '0' + month;
        day = nextWeek.getDate().toString()
        if (day.length < 2)
            day = '0' + day;
        showDate += " - " + day + '/' + month + '/' + nextWeek.getFullYear();
    }
    else if (groupBy == 'monthly')
        showDate = month + '/' + jsDate.getFullYear();
    else if (groupBy == 'yearly')
        showDate = jsDate.getFullYear();

        return showDate
}
// From - To Functionality 
function from_to_range(element) {

    $('input[id$="DateFrom"],input[id$="DateTo"]').each(function () {
        $(this).datepicker({ changeMonth: true, changeYear: true, format: "dd/mm/yyyy", autoclose: true });
    })

    $(document).on("click", ".searchDateFilter, .groupDataByDate", function () {
        $(".overlay").show();
        dataTableToUpdate = $(this).closest(".dataTableWithFilter").find(".dataTableContainer")
        boxTitle = $(this).closest(".box").find(".box-title").text();
        $(this).closest(".dataTableDateFilter").find('input[id$="DateFrom"]').each(function () {
            jsDate = ($(this).datepicker("getDate"))
            dateFrom = convertDate(jsDate);
        })
        $(this).closest(".dataTableDateFilter").find('input[id$="DateTo"]').each(function () {
            jsDate = ($(this).datepicker("getDate"))
            dateTo = convertDate(jsDate);
        })
        if (dateFrom != null && dateTo != null) {
            //groupBy = $(this).closest(".dataTableDateFilter").find('.groupDataByDate').val()
            groupBy = $(this).attr('data-value')
            dates = { dateFrom: dateFrom, dateTo: dateTo, groupBy: groupBy }
            $.ajax({
                url: url_str_datatable_ranges,
                data: dates,
                success: function (data) {

                    data.forEach(function (item) {
                        jsDate = new Date(item[0]['show_date']);
                        item[0]['show_date'] = convertDateByGroup (jsDate, groupBy)
                    })
                    var options = {}
                    options['idDataTable'] = dataTableToUpdate.attr("id").replace("Container","")
                    options['title'] = boxTitle +' for period ' + dateFrom + ' to ' + dateTo + ' in ' + groupBy + ' basis';
                    createDataTable(dataTableToUpdate, data, "registered", options)
                    $(".overlay").hide();
                }
            })
        }
        else {
            $(".overlay").hide();
            noty({
                text: 'You must fill both Dates From and To',
                type: 'alert',
                dismissQueue: true,
                layout: 'topCenter',
                theme: 'comanage',
            })
        }
    })
}

function getDataForUsersTiles() {
    $.ajax({
        url: url_str_userstiles,
            success: function (dataTiles) {
            createTile($("#registeredsTotalInfo .row .col-lg-3").eq(0), "bg-aqua", (dataTiles[0] ? dataTiles[0] : '0'),  "Todays Registered Users", 1, 'registerdTotalInfo')
            createTile($("#registeredsTotalInfo .row .col-lg-3").eq(1), "bg-aqua", (dataTiles[1] ? dataTiles[1] : '0'), "Last 7 days Registered Users", 7, 'registerdTotalInfo')
            createTile($("#registeredsTotalInfo .row .col-lg-3").eq(2), "bg-aqua", (dataTiles[2] ? dataTiles[2] : '0'), "Last 30 days Registered Users", 30, 'registerdTotalInfo')
            createTile($("#registeredsTotalInfo .row .col-lg-3").eq(3), "bg-aqua", (dataTiles[3] ? dataTiles[3] : '0'), "Last Year Registered Users", 365, 'registerdTotalInfo')       
        }
    })
    
}

// Create Datatables
function createDataTable(element, data, type, options = null) {

    if (type == "idp") {
        column1 = 'idpname'
        column2 = 'count'
        data_param = 'sourceidp'
        th = 'Identity Provider'
        ths = '<th>' + th + ' Name</th>' +
        '<th>' + th + ' Identifier</th>' +
        '<th>Number of Logins</th>'
        sort_order = 1
        from_to = false
    }
    else if (type == "sp") {
        column1 = 'spname'
        column2 = 'count'
        data_param = 'service'
        th = 'Service Provider'
        ths = '<th>' + th + ' Name</th>' +
        '<th>' + th + ' Identifier</th>' +
        '<th>Number of Logins</th>'
        sort_order = 1
        from_to = false
    }
    else if (type == "registered") {
        column1 = 'show_date'
        column2 = 'count'
        data_param = false
        th = 'Dates'
        ths = '<th> Date </th>' +
        '<th> Number of Registered Users </th>' 
        sort_order = 0
        from_to =true
    }
    dataAppend = '';

    data.forEach(function (item) {
        
        if(type != 'registered')
            dataAppend += '<tr><td><a class="datatable-link" href="#" onclick="return false;" data-type="' + type + '" data-identifier="' + item[0][data_param] + '">' + item[0][column1] + '</a></td><td>' + item[0][data_param] + '</td><td>' + item[0][column2] + '</td></tr>';
        else if (type == 'registered')
            dataAppend += '<tr><td data-sort=' + item[0]['range_date'] + '>' + item[0][column1] + '</td><td>' + item[0][column2] + '</td></tr>';
    })

    title = (options!=null && options['title'] != null ? options['title'] : '')
    id = ( options!= null && options['idDataTable'] != null ? options['idDataTable'] : type + 'SpecificDatatable');
    
    element.html('<table id="' + id + '" class="stripe row-border hover">' +
        '<thead>' +
        '<tr>' +
        ths +
        '</tr>' +
        '</thead>' +
        '<tbody>' +
        dataAppend +
        '</tbody>' +
        '</table>');
    if (from_to === true)
    {
        from_to_range(element)
    }
    if(datatableExport){
        $("#" + id).DataTable({
            dom: 'Bfrtip',
            order: [sort_order, 'desc'],
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export DataTable',
                    buttons: [
                        'copy',
                        {
                            extend: 'excel',
                            title: title
                        },
                        {
                            extend: 'csv',
                            title: title
                        },
                        {
                            extend: 'pdf',
                            title: title
                        },
                        {
                            extend: 'print',
                            title: title
                        }
                        
                    ]
                }
            ]
        });
    }
    else
        $("#" + id).DataTable({
            order: [1, 'desc']
        });
}

function reloadPage(){ 
    location.reload();
};

// Generate flash notifications for messages
function generateSessionExpiredNotification(text, type) {
     noty({
        text: '<span onclick="reloadPage()">' + text + '</span>',
        type: type,
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'comanage',
        id: 'session-expired',
        closeWith: ['click'],
    });
}