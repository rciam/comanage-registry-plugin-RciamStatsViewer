$(document).on('click','input[id$=DateFrom], input[id$=DateTo]', function(e) {
    e.preventDefault();
    $(this).attr("autocomplete", "off");   
 });

$(document).on('change', 'select[class=couStatsSelect]', function (e) {
    description = $(this).children('option:selected').data("description")
    title = $(this).children('option:selected').data("title")
    created = $(this).children('option:selected').data("created")
    if($(this).val() != "") {
        let jqxhr = $.ajax({
            url: url_str_statspercou,
            data: {
                cou_id: $(this).val(),
            }
        });

        jqxhr.done((data) => {
            $(".status-box").show();
            $("#world-map-cous").show();
            // First format the data for map
            other_statuses = 0;
            if(Object.keys(data['map']).length > 0) {
                map = [];
                i=0;
                for(countryItem in data['map']) {
                    map[i] = [];
                    map[i][0] = {sum: 0, country: `${countryItem}`};
                    map[i][0]["additional_text"] = '';
                    other = 0; // for other statuses
                    for (status in data['map'][countryItem]) {
                        
                        map[i][0]["sum"] += data['map'][countryItem][status]["sum"]
                        map[i][0]["countrycode"] = data['map'][countryItem][status]["countrycode"]
                        if(statusEnum[status] !== undefined) {
                            map[i][0]["additional_text"] += statusEnum[status] + ": " + data['map'][countryItem][status]["sum"] + "<br/>";
                        }
                        else {
                            other += data['map'][countryItem][status]["sum"];
                        }
                    }
                    if(other > 0) {
                        map[i][0]["additional_text"] += "Other Status: " + other;
                    }
                    i++;
                }
                createMap(map, "world-map-cous", "Number of Users", "Users")
            }
            data = data['cou']
            content = "<h3>" + title + "</h3><hr/><p>Created: " + created + "</p><p>" + description + "</p>";
            for (let [keyEnum, valueEnum] of Object.entries(statusEnum)) {
                found = false;
                if (data[0] != undefined) {
                    for (let [key, value] of Object.entries(data)) {
                        if (statusEnum[data[key]['CoPersonRole']["status"]] == statusEnum[keyEnum]) {
                            elementClass = statusEnum[data[key]['CoPersonRole']["status"]].toLowerCase().replace(/ /g, '')
                            $("." + elementClass + "-users .description-header").text(data[key][0]["count"])
                            found = true;
                        }
                    }
                    if (found === false) {
                        elementClass = statusEnum[keyEnum].toLowerCase().replace(/ /g, '')
                        $("." + elementClass + "-users .description-header").text(0)
                    }

                }
                else { // Didnt find anything
                    elementClass = statusEnum[keyEnum].toLowerCase().replace(/ /g, '')
                    $("." + elementClass + "-users .description-header").text(0)
                }
            }
            // Find users with other statuses
            data.forEach(function(user_status){
                if(statusEnum[user_status["CoPersonRole"]["status"]] !== 'undefined') {
                    other_statuses++;
                }
            })   
            $(".other-users .description-header").text(other_statuses)
  
            $(".perCouStatsContent").html(content)
        })
        jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
    }
    else {
        $(".status-box").hide();
        $("#world-map-cous").hide();
        $(".perCouStatsContent").html("")
    }
})
// Tooltip for Cous
$(document).tooltip({
    items: "[data-date-column]",
    position: {
        my: "center bottom-5",
        at: "center top"
    },
    content: function () {
        var element = $(this);
        if (element.is("[data-date-column]")) {
            return "<b>" + element.text() + "</b><br/> Created Date: " + element.attr("data-date-column") + "<br/>" + element.attr("data-descr");
        }
    }
})

$(document).on("click", ".unique-logins-text", function(e) {
    checkbox =$(this).parent().find("input[type=checkbox]")
    checkbox.prop("checked", !checkbox.prop("checked")).change();
})

// when clicking groupBy
$(document).on("click", ".groupDataByDate", function () {    
    console.log("testmpaine")
    $(".overlay").show();
    dataTableToUpdate = $(this).closest(".dataTableWithFilter").find(".dataTableContainer")
    boxTitle = $(this).closest(".box").find(".box-title").text();
    type = $(this).closest(".box").attr("data-type")
    $(this).closest(".dataTableDateFilter").find('input[id$="DateFrom"]').each(function () {
        jsDate = ($(this).datepicker("getDate"))
        console.log(jsDate)
        dateFrom = convertDate(jsDate);
    })
    $(this).closest(".dataTableDateFilter").find('input[id$="DateTo"]').each(function () {
        jsDate = ($(this).datepicker("getDate"))
        dateTo = convertDate(jsDate);
    })
    
    if (dateFrom != null && dateTo != null && dateTo >= dateFrom) {
        groupBy = $(this).attr('data-value')
        if(type.includes("Specific")) {
            identifier =  $(this).closest(".box").attr("data-identifier")
            unique_logins = $("#unique-logins-modal").is(":checked")
        }
        else {
            identifier = null;
            unique_logins = $("#unique-logins-" + type).is(":checked")
        }
        dates = { dateFrom: dateFrom, dateTo: dateTo, groupBy: groupBy, type: type, identifier: identifier, unique_logins: unique_logins  }
        let jqxhr = $.ajax({
            url: url_str_datatable_ranges,
            data: dates,
            statusCode : { 
              403 : function () {
                generateSessionExpiredNotification("Sorry, your session has expired. Please click here to renew your session.", "error"); 
              }
            }
          })
        jqxhr.done((data) => {
            // Initialize classes and map id
            date_specific_class = 'date-specific-modal';
            map_container_class = 'map-container-modal';
            map_id = 'world-map-modal';

            if(type == 'dashboard' || type == 'registered') {
                date_specific_class = 'date-specific-' + type;
                map_container_class = 'map-container-' + type;
                map_id = 'world-map-' + type;
            }
            // Create Map only for Dashboard, Registered Users Tab and Modal
            if(data['map'] !== undefined && data['map'].length > 0 && type != 'idp' && type != 'sp') {
                // If tab is "Registered Users" we must take into account dates user put otherwise we take into account 
                // dates that we actually have data
                date_from_to = type == 'registered' ? [dateFrom, dateTo] : calculateMinMax(data['map'])
                // Create element for map
                $("." + map_container_class).html(
                    '<div id="' + map_id + '" style="height:500px">'
                    + '<div class="map"></div>'
                    + '<div class="areaLegend"></div>'
                    + '</div>'
                )
                // Put Date Ranges to the title of the map
                $(".box-map ." + date_specific_class).html(" from " + date_from_to[0] + " to " + date_from_to[1])
                
                if (type == 'registered') {
                    createMap(data['map'], map_id, "Number of Users", "Users")
                }   
                else {
                    containerName = type == "idpSpecific" || type == "spSpecific" ? "modal" : type
                    if(type == "idpSpecific" || type == "spSpecific") {
                        containerName = "modal"
                        options = { title: 'Number of Logins per country for period ' + dateFrom + ' to ' + dateTo + ' for ' + $("h1.modal-title").html() } 
                    }
                    else {
                        containerName = type
                        options = null
                    }
                    createMapElement(data['map'], "map-container-" + containerName , map_id, type, options)
                }
            }
            else {
                date_specific = (dateFrom != '' && dateTo != '' ? " from " + dateFrom + " to " + dateTo : "")
                $(".box-map ." + date_specific_class).html(date_specific)
                $("." + map_container_class).html("No data available.")
            }

            // Creation of Datatable
            if(type == 'registered' || type == 'cou' || type == 'dashboard') {
                if(type == 'dashboard') {
                    data = data['dashboard'];
                }
                else if(type == 'registered' || type == 'cou') {
                    data = data['data'];
                }
                
                // We put an extra column with Countries for this tabs
                data.forEach(function (item) {
                    jsDate = new Date(item[0]['show_date'].split(" ")[0]);
                    item[0]['show_date'] = convertDateByGroup(jsDate, groupBy)
                    // Transformation of countries array to plain text to render it at the datatable
                    if(item[0]['countries'] !== undefined) {
                        item[0]['plain_countries'] = '';
                        for(country in item[0]['countries']) {
                            item[0]['plain_countries'] += country + ': ' + item[0]['countries'][country]['count'] + '|| ';
                        }
                        // Remove last comma with space
                        item[0]['plain_countries'] = item[0]['plain_countries'].slice(0, -3)
                    }
                })
                typeDataTable = type;
                basis = ' in ' + groupBy + ' basis'
            }
            else if (type == 'idp' || type == 'spSpecific') {
                data = data["idps"]
                typeDataTable = 'idp'
                basis = ''
            }
            else if (type == 'sp' || type == 'idpSpecific') {
                data = data["sps"]
                typeDataTable = 'sp'
                basis = ''
            }

            var options = {}
            options['idDataTable'] = dataTableToUpdate.attr("id").replace("Container", "")
            // Title for exporting datatable
            options['title'] = boxTitle + ' for period ' + dateFrom + ' to ' + dateTo + basis;
            createDataTable(dataTableToUpdate, data, typeDataTable, options)
            $(".overlay").hide();
        })
        jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
            
    }
    else if (dateFrom != null && dateTo != null && dateTo < dateFrom) {
        $(".overlay").hide();
        noty({
            text: '"Date From" must be prior to "Date To"',
            type: 'error',
            timeout: 2000,
            dismissQueue: true,
            layout: 'topCenter',
            theme: 'comanage',
        })
    }
    else {
        $(".overlay").hide();       
        noty({
            text: 'You must fill both Dates From and To',
            type: 'error',
            timeout: 2000,
            dismissQueue: true,
            layout: 'topCenter',
            theme: 'comanage',
        })
    }
})

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

    if (type != 'registeredsTotalInfo') {
        row.html('<div class="small-box ' + bgClass + '">' +
            '<div class="inner">' +
            '<h3>' + (value != 0 ? value : loginsNotAvailable) + '</h3>' +
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
    cur_dashboard = new google.visualization.Dashboard(document.getElementById(elementId));
    if(data.getNumberOfRows() > 0) {
        data = setZerosIfNoDate(data);
    }
    else {
        // No Data available so we must initialize data variable
        fValue = ['Date', 'Count']
        var temp = [new Date(), 0];
        fValues.push(temp);
        data = new google.visualization.arrayToDataTable(fValues);
        data = setZerosIfNoDate(data);
    }
    
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
            activeTab = $("ul.tabset_tabs li.ui-tabs-active").attr("aria-controls").replace("Tab","");
            unique_logins = $("#myModal").is(':visible') ? $("#unique-logins-modal").is(":checked") : $("#unique-logins-"+activeTab).is(":checked");
            goToSpecificProvider(identifier, legend, type, unique_logins);
        }
    }
}

// Column Chart
function drawColumnChart(elementId, data, type, hticks = null, tab) {
    if (type == 'monthly') {
        format = 'YYYY-MM'       
    }
    else if (type == 'yearly') {
        format = 'Y'     
    }
    else if (type == 'weekly') {
        format = ''
    }

    data.sort([{
        column: 1,
        desc: false
    }]);

    var options = {
        vAxis: {
            title: vAxisTitle[tab],
            format: '0'
        },
        hAxis: {
            format: format,
            maxTextLines: 2,
            title: registeredUsersBy[type], // globar variable found at index.ctp
            textStyle: {fontSize: 15},
            ticks: (type != 'weekly' ? data.getDistinctValues(0) : hticks)
        },
        tooltip: {isHtml: true},
        width: '100%',
        height: '350',
        bar: { groupWidth: "92%" },
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
function updateColumnChart(elementId, range = null, init = false, tab) {
    $(".overlay").show();
    let jqxhr = $.ajax({
        url: url_str_columnchart,
        data: {
            range: range,
            tab: tab
        },
        statusCode: {
          403 : function() {
            generateSessionExpiredNotification("Sorry, your session has expired. Please click here to renew your session.", "error");
          }
        }
      })
    jqxhr.done((data) => {
        
        if(tab == 'registered' || tab =='cou') {
            dataMap = data['map'] !== undefined && data['map'].length > 0 ? data['map'] : [];
            data = data['data'];          
        }        
        fValues = [];
        hticks = [];
        fValues.push(['Date', 'Count', { 'type': 'string', 'role': 'tooltip', 'p': { 'html': true } }])

        data.forEach(function (item) {
            var temp = [];
            valueRange = new Date(item[0]['range_date']);
            temp.push(valueRange);
            temp.push(parseInt(item[0]['count']));
            temp.push('<div style="padding:5px 5px 5px 5px;">' + convertDateByGroup(valueRange, range) + "<br/> " + tooltipDescription[tab] + ": " + parseInt(item[0]['count']) + '</div>');
            hticks.push({ v: valueRange, f: getWeekNumber(valueRange) })
            fValues.push(temp);
        })

        var dataRange = new google.visualization.arrayToDataTable(fValues);
        drawColumnChart(elementId, dataRange, range, hticks, tab)
        if(tab == 'cou'){ // we add a column to the right with cou names
            $('.' + tab + 'Names').html("");
            cous = [];
            
            data.forEach(function (item) {                  
                valueRange = item[0]['created_date']
                valueRange = valueRange.split(", ")
                description = item[0]['description'].split("|| ")               
                item[0]['names'].split("|| ").forEach(function (name, index){
                    cous.push({name:name, created: valueRange[index], description: description[index]})
                })
            })
                // sort by value
            cous = cous.sort(function (a, b) {
                var nameA = a.name.toUpperCase(); // ignore upper and lowercase
                var nameB = b.name.toUpperCase(); // ignore upper and lowercase
                if (nameA < nameB) {
                    return -1;
                }
                if (nameA > nameB) {
                    return 1;
                }
                // names must be equal
                return 0;
            });
            
            cous.forEach(function (name, index){
                $('.' + tab + 'Names').append('<li class="rowList" data-date-column="'+ cous[index]['created'] +'" data-descr="'+ cous[index]['description'] +'">' + cous[index]['name'] + '</li>')
            })
        }
        if(init){ // initialize datatable
            // initialize from_to_range
            from_to_range()
            i = 0;
            data.forEach(function (item){
                newDate = new Date(item[0]['range_date']);
                if (i == 0){                        
                    minDate = new Date(item[0]['min_date']);
                }  
                i++;
                fDate = newDate.getMonth()+1
                if (fDate < 10)
                    fDate = '0' + fDate
                item[0]['show_date'] =  newDate.getFullYear()
                // Transformation of countries array to plain text, for adding
                // a new column for countries to datatable
                if(item[0]['countries'] !== undefined) {
                    item[0]['plain_countries'] = '';
                    for(country in item[0]['countries']) {
                        item[0]['plain_countries'] += country + ': ' + item[0]['countries'][country]['count'] + '|| ';
                    }
                    // Remove last comma with space
                    item[0]['plain_countries'] = item[0]['plain_countries'].slice(0,-3)
                }
            })
            //Set minimum Date
            $("#" + tab + "DateFrom, #" + tab + "DateTo").each(function(){
                if(tab!='registered') {
                    $(this).datepicker('setStartDate', minDate);
                }
            })
           
            
            var options = {}
            options['idDataTable'] = tab + 'Datatable'
            options['title'] = defaultExportTitle[tab];
            createDataTable($("#" + tab + "DatatableContainer"), data , tab, options)

            if (tab == 'registered') {
                
                const today = new Date();
                const yyyy = today.getFullYear();
                let mm = today.getMonth() + 1; // Months start at 0!
                let dd = today.getDate();
                if (dd < 10) dd = '0' + dd;
                if (mm < 10) mm = '0' + mm;
                $("#" + tab + "DateFrom").datepicker( "setDate", (yyyy-2)+'-'+mm+'-'+dd);
                $("#" + tab + "DateTo").datepicker( "setDate", yyyy+'-'+mm+'-'+dd);
                $("#" + tab + "DateFrom").val((yyyy-2)+'-'+mm+'-'+dd);
                $("#" + tab + "DateTo").val(yyyy+'-'+mm+'-'+dd);
                console.log( $("#" + tab + "DateFrom").val())
                //$('.btn.btn-default.dropdown-toggle.filter-button').click();
                $(".overlay").hide();
                $('#registeredTab a.groupDataByDate[data-value="yearly"]').click();    
            }

        }
        if(tab == 'registered' && dataMap.length > 0) {
            createMap(dataMap, 'world-map-registered', 'Number of Users', 'Users')
        }
        
        $(".overlay").hide();
                
    })
    jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
}

// When clicking on tiles
function getLoginCountPerDay(url_str, days, identifier, type, tabId, specific, unique_logins = null) {
    let jqxhr = $.ajax({
        url: url_str,
        data: {
            days: days,
            identifier: identifier,
            type: type,
            unique_logins: unique_logins
        },
        statusCode: {
          403: function() {            
            generateSessionExpiredNotification("Sorry, your session has expired. Please click here to renew your session.", "error");
          }
        }
      })
    jqxhr.done((data) => {
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
      if(tabId == 'dashboard'){
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

        // Dashboard has 2 pieCharts
        pieId = $(element + " .pieChart").eq(0).attr("id");
        drawPieChart(document.getElementById(pieId), dataIdp, "idp");
          
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
        drawPieChart(document.getElementById($(element + " .pieChart").eq(1).attr("id")), dataSp, "sp");
        //Initialize DataTable Date Range
        $(element + " .dataTableContainer").closest(".box").find('input[id$="DateFrom"],input[id$="DateTo"]').each(function () {
            $(this).val("")
        })
        var options = {}
        options['idDataTable'] = 'dashboardDatatable'
        data['datatable'].forEach(function (item) {
            groupBy = (days == 365 || days == 0 ? 'monthly' : 'daily')
            jsDate = new Date(item[0]['show_date']);
            item[0]['show_date'] = convertDateByGroup(jsDate, groupBy)
            // Now must transform countries array to plain text
            if (item[0]['countries'] !== undefined) {
                item[0]['plain_countries'] = '';
                for (country in item[0]['countries']) {
                    item[0]['plain_countries'] += country + ': ' + item[0]['countries'][country]['count'] + '|| ';
                }
                // Remove last comma with space
                item[0]['plain_countries'] = item[0]['plain_countries'].slice(0, -3)
            }
        })
        options['title'] = 'Number of logins the last ' + days + ' days'
        createDataTable($(element + " .dataTableContainer"), data['datatable'], "dashboard", options)
        createMapElement(data['map'], 'map-container-dashboard', 'world-map-dashboard')
      }
      else if ((tabId == 'idp' && specific == 'total') || (tabId == 'sp' && specific == 'specific')) {
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

          // Dashboard has 2 pieCharts
          pieId = $(element + " .pieChart").attr("id");
          drawPieChart(document.getElementById(pieId), dataIdp, "idp");

          //Initialize DataTable Date Range
          $(element + " .dataTableContainer").closest(".box").find('input[id$="DateFrom"],input[id$="DateTo"]').each(function () {
              $(this).val("")
          })
          if(tabId == 'sp' && specific == 'specific') {
              var options = {}
              options['title'] = 'Number of logins the last ' + days + ' days per Identity Provider'
              createDataTable($(element + " .dataTableContainer"), data['idps'], "idp", options)
              // Map Visualisation
              options['title'] = 'Number of logins the last ' + days + ' days per Country for ' + $("h1.modal-title").html() 
              createMapElement(data['map'], 'map-container-modal', 'world-map-modal', tabId + 'Specific', options)              
          }
          else if(tabId == 'idp' && specific == 'total') { //for Identity Providers Details Tab
              var options = {}
              options['idDataTable'] = 'idpDatatable'
              options['title'] = 'Number of logins the last ' + days + ' days per Identity Provider'
              createDataTable($(element + " .dataTableContainer"), data['idps'], "idp", options)
          }
      }
      if ((tabId == 'sp' && specific == 'total') || (tabId == 'idp' && specific == 'specific')) {
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
          pieId = $(element + " .pieChart").attr("id")
          drawPieChart(document.getElementById(pieId), dataSp, "sp");
          //Initialize DataTable Date Range
          $(element + " .dataTableContainer").closest(".box").find('input[id$="DateFrom"],input[id$="DateTo"]').each(function(){
              $(this).val("")
          })      
          if (tabId == 'idp' && specific == 'specific'){
              var options = {}
              options['title'] = 'Number of logins the last ' + days + ' days per Service Provider'
              createDataTable($(element + " .dataTableContainer"), data['sps'], "sp", options)
              options['title'] = 'Number of logins the last ' + days + ' days per Country for ' + $("h1.modal-title").html() 
              createMapElement(data['map'], 'map-container-modal', 'world-map-modal', tabId + 'Specific', options)
          }
          else if (tabId == 'sp' && specific == 'total') { //for Service Providers Details Tab
              var options = {}
              options['idDataTable'] = 'spDatatable'
              options['title'] = 'Number of logins the last ' + days + ' days per Service Provider'
              createDataTable($(element + " .dataTableContainer"), data['sps'], "sp", options)
          }
      }
      $(".overlay").hide();        
    })
    jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
}

function getDataForTabs(tab) {
    $(".overlay").show();
    unique_logins = $("#unique-logins-" + tab).is(":checked")
    let jqxhr =  $.ajax({
        url: urlRefreshTab,
        data: {unique_logins: unique_logins},
        statusCode: {
          403 : function() {
            generateSessionExpiredNotification("Sorry, your session has expired. Please click here to renew your session.", "error");
          }
        }
    });
    jqxhr.done((data, textStatus, xhr) => { 
        refreshTabs(tab ,data)
        $(".overlay").hide();
    });
    jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
}

function refreshTabs(tab, data) {
    
    // Initialize Tiles
    item = tab + "Tab";
    type = tab + "sTotalInfo";
    if ($("#" + item).length > 0) {
        createTile($("#" + item + " .row .col-lg-3").eq(0), "bg-aqua", data['tiles'][0] != null && data['tiles'][0] != 0 ? data['tiles'][0] : loginsNotAvailable, todaysLoginsText, 1, type)
        createTile($("#" + item + " .row .col-lg-3").eq(1), "bg-green", data['tiles'][1] != null && data['tiles'][1] != 0 ? data['tiles'][1] : loginsNotAvailable, weekLoginsText, 7, type)
        createTile($("#" + item + " .row .col-lg-3").eq(2), "bg-yellow", data['tiles'][2] != null && data['tiles'][2] != 0 ? data['tiles'][2] : loginsNotAvailable, monthLoginsText, 30, type)
        createTile($("#" + item + " .row .col-lg-3").eq(3), "bg-red", data['tiles'][3] != null && data['tiles'][3] != 0 ? data['tiles'][3] : loginsNotAvailable, yearLoginsText, 365, type)
    }
    columnNames = {};
    dataCol = [];
    dataValues = {};
    dataTable = {};
    columns = {};
    if (tab == 'sp') {
        columnNames['sp'] = ['service', 'serviceIdentifier', 'Count'];
        dataCol = ['sp'];
        columns['sp'] = ['spname', 'service', 'count'];       
    }
    else if(tab == 'idp') {
        columnNames['idp'] = ['sourceIdp', 'sourceIdPEntityId', 'Count'];
        dataCol = ['idp'];
        columns['idp'] = ['idpname', 'sourceidp', 'count']
    }
    else { // dashboard
        columnNames['idp'] = ['sourceIdp', 'sourceIdPEntityId', 'Count'];
        columnNames['sp'] = ['service', 'serviceIdentifier', 'Count'];
        columns['idp'] = ['idpname', 'sourceidp', 'count']
        columns['sp'] = ['spname', 'service', 'count'];
        dataCol = ['idp', 'sp']
        charts = ['summaryIdPChart', 'summarySpChart']
    }
    dataCol.forEach(function (type) {
        fValues = [];
        fValues.push(columnNames[type])
        data[type].forEach(function (item) {
            var temp = [];
            temp.push(item[0][columns[type][0]]);
            temp.push(item[0][columns[type][1]])
            temp.push(parseInt(item[0][columns[type][2]]));
            dataValues[type] += "[" + new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]), parseInt(item[0]["count"]) + "],";
            fValues.push(temp);
        })        
        dataTable[type] = new google.visualization.arrayToDataTable(fValues);
    });  
      
    if(tab == "idp" || tab == "sp") {
        drawPieChart(document.getElementById(tab + "sChartDetail"), dataTable[tab], tab);
        $("#" + tab + "DatatableContainer").closest(".box").find('input[id$="DateFrom"],input[id$="DateTo"]').each(function () {
            $(this).val("")
        })
        var options = { idDataTable: tab + 'Datatable' }
        createDataTable($("#" + tab + "DatatableContainer"), data[tab], tab, options)

        // Update SubTile Date Ranges
        fValues = [];
        fValues.push(['Date', 'Count'])
        data['dashboard'].forEach(function (item) {
            var temp = [];
            temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
            temp.push(parseInt(item[0]["count"]));
            fValues.push(temp);
        })
        var dataTable = new google.visualization.arrayToDataTable(fValues);
        updateSubtitleDateRanges(tab, dataTable);
    }
    else { // Dashboard
        drawPieChart(document.getElementById("summaryIdPChart"), dataTable['idp'], "idp");
        drawPieChart(document.getElementById("summarySpChart"), dataTable['sp'], "sp");
        //Draw Line - Range Chart
        fValues = [];
        fValues.push(['Date', 'Count'])
        data['dashboard'].forEach(function (item) {
            var temp = [];
            temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"]));
            temp.push(parseInt(item[0]["count"]));
            fValues.push(temp);
        })
        var dataTable = new google.visualization.arrayToDataTable(fValues);
        drawLineChart(document.getElementById("loginsDashboard"), dataTable, '')
        var options = {};
        data['datatable_dashboard'].forEach(function(item) {
            newDate = new Date(item[0]['range_date'].split(" ")[0]);
            //if (i == 0)
            //    minDate = new Date(item[0]['min_date']);
            i++;
            fDate = newDate.getMonth() + 1
            if (fDate < 10)
                fDate = '0' + fDate

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

        updateSubtitleDateRanges('dashboard', dataTable);
        createDataTable($("#"+tab+"DatatableContainer"), data['datatable_dashboard'], tab)
        createMap(data['map'], "world-map-dashboard")
    }
}
function updateSubtitleDateRanges(element, data) {
    subTitleElement = $("." + element + "-data-dates")
    startDate = convertDate(new Date(data.getColumnRange(0).min));
    endDate = convertDate(new Date(data.getColumnRange(0).max));
    subTitleElement.html(startDate + " to " + endDate);
}
// Modal Functionality
function goToSpecificProvider(identifier, legend, type, unique_logins = false) {
    $("#myModal").modal();
    $("#tabs-modal").tabs({active: 0});
    $("#unique-logins-modal").attr("data-identifier", identifier)
    $("#unique-logins-modal").attr("data-type", type)
    $("#unique-logins-modal").attr("data-legend", legend)
    if(unique_logins === true){
        if($("#unique-logins-modal").prop('checked') === false) {
            $("#unique-logins-modal").prop('checked', true);
        }
    }
    else
    $("#unique-logins-modal").prop('checked', false);
    // Bug Fix For DatePicker Position When scrolling on modal
    $("#myModal").on("scroll", function() {
        $('#myModal input[id$="DateFrom"],#myModal input[id$="DateTo"]').datepicker('place')
    }); 
    $(".modal .overlay").show();
     $('#myModal').animate({
       scrollTop: 0
     }, 'slow');
     
    item="specificData";
    //initialize tiles
    createTile($("#" + item + " .row .col-lg-3").eq(0), "bg-aqua", loginsNotAvailable, todaysLoginsText, 1, type+"SpecificData")
    createTile($("#" + item + " .row .col-lg-3").eq(1), "bg-green", loginsNotAvailable, weekLoginsText, 7, type+"SpecificData")
    createTile($("#" + item + " .row .col-lg-3").eq(2), "bg-yellow", loginsNotAvailable, monthLoginsText, 30, type+"SpecificData")
    createTile($("#" + item + " .row .col-lg-3").eq(3), "bg-red", loginsNotAvailable, yearLoginsText, 365, type+"SpecificData")
  
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
        obj = { idp: identifier, unique_logins: unique_logins };
    }
    else {
        obj = { sp: identifier, unique_logins: unique_logins };
    }

    let jqxhr =  $.ajax({
        url: urlByType[type],
        data: obj,
        statusCode: {
          403 : function() {
            generateSessionExpiredNotification("Sorry, your session has expired. Please click here to renew your session.", "error");
          }
        }
    });
    jqxhr.done((data, textStatus, xhr) => {  
        $(".modal-body .specificData .bg-aqua h3").text(data['tiles'][0] != null && data['tiles'][0] != 0 ? data['tiles'][0] : loginsNotAvailable);
        setHiddenElements($(".modal-body .specificData .bg-aqua"), data['tiles'][0])
        $(".modal-body .specificData .bg-green h3").text(data['tiles'][1] != null && data['tiles'][1] != 0 ? data['tiles'][1] : loginsNotAvailable);
        setHiddenElements($(".modal-body .specificData .bg-green"), data['tiles'][1])
        $(".modal-body .specificData .bg-yellow h3").text(data['tiles'][2] != null && data['tiles'][2] != 0 ? data['tiles'][2] : loginsNotAvailable);
        setHiddenElements($(".modal-body .specificData .bg-yellow"), data['tiles'][2])
        $(".modal-body .specificData .bg-red h3").text(data['tiles'][3] != null && data['tiles'][3] != 0 ? data['tiles'][3] : loginsNotAvailable);
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
            temp.push(new Date(item[0]["year"], item[0]["month"] - 1, item[0]["day"],2));
            temp.push(parseInt(item[0]["count"]));
            fValues.push(temp);
        })
        var dataTable = new google.visualization.arrayToDataTable(fValues);
        updateSubtitleDateRanges('modal', dataTable);
        $("#loginLineChart").closest(".box").find(".box-title").html(overallText[type])
        drawLineChart(document.getElementById("loginLineChart"), dataTable, 'modal')
        //Set DataTable Title
        $("#specificDataTableContainer").closest(".box").find('.box-title').text(specificTextDataTable[type]);//global variable initialized at index.ctp
        //Initialize DataTable Date Range
        $("#specificDataTableContainer").closest(".box").find('input[id$="DateFrom"],input[id$="DateTo"]').each(function(){
            $(this).val("")
        })
        $("#specificDataTableContainer").closest(".box").attr("data-type", type + 'Specific')
        $("#specificDataTableContainer").closest(".box").attr("data-identifier", identifier) 
        
        createDataTable($("#specificDataTableContainer"), data[dataCol], dataCol)
        // Create Map Element
        var options = {};
        options['title'] = 'Number of Logins per country for ' + legend;
        createMapElement(data['map'], 'map-container-modal' , 'world-map-modal', type + 'Specific',  options)     
        
        $(".modal .overlay").hide();        
    });
    jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
}

// Convert Date in Format compatible with query
function convertDate(jsDate){
    date = null;
    if (jsDate != null && jsDate instanceof Date) {
        month = (jsDate.getMonth() + 1).toString()
        if(month.length < 2) {
            month = '0' + month;
        }
        day = jsDate.getDate().toString()
        if(day.length < 2) {
            day = '0' + day;
        }
       date = jsDate.getFullYear() + '-' + month + '-' + day;
        
    }
    return date;
}

function convertDateByGroup(jsDate, groupBy) {
    month = (jsDate.getMonth() + 1).toString()
    if(month.length < 2) {
        month = '0' + month;
    }
    day = jsDate.getDate().toString()
    if(day.length < 2) {
        day = '0' + day;
    }
    if(groupBy == 'daily') {
        showDate = jsDate.getFullYear()+ '-' + month + '-' +  day;
    }
    else if(groupBy == 'weekly') {
        showDate = jsDate.getFullYear() + '-' + month + '-' + day;
        var nextWeek = new Date(jsDate.setDate(jsDate.getDate() + 6));
        month = (nextWeek.getMonth() + 1).toString()
        if (month.length < 2) {
            month = '0' + month;
        }
        day = nextWeek.getDate().toString()
        if (day.length < 2) {
            day = '0' + day;
        }
        showDate += " to " + nextWeek.getFullYear() + '-' + month + '-' + day;
    }
    else if(groupBy == 'monthly') {
        showDate = jsDate.getFullYear() + '-' +  month;
    }
    else if(groupBy == 'yearly') {
        showDate = jsDate.getFullYear();
    }
    return showDate;
}
// From - To Functionality 
function from_to_range() {  
    $('input[id$="DateFrom"],input[id$="DateTo"]').each(function () {
        id = $(this).attr("id")
        if(id.indexOf("DateTo")!= -1) {
            $(this).datepicker({ changeMonth: true, changeYear: true, 
                format: "yyyy-mm-dd", autoclose: true, endDate: new Date() });
        }
        else {
            $(this).datepicker({ changeMonth: true, changeYear: true, 
                format: "yyyy-mm-dd", autoclose: true, endDate: new Date() });
        }
    }) 
}

// Initialize Tiles for Registered Users
function getDataForUsersTiles(elementId) {
    let jqxhr = $.ajax({
        url: url_str_userstiles,
    })
    jqxhr.done((dataTiles) => {      
      createTile($("#" + elementId + "TotalInfo .row .col-lg-3").eq(0), "bg-blue", (dataTiles[0] ? dataTiles[0] : '0'),  "Total Registered Users", 1, elementId + 'TotalInfo')
      createTile($("#" + elementId + "TotalInfo .row .col-lg-3").eq(1), "bg-aqua", (dataTiles[1] ? dataTiles[1] : '0'), "Last 7 days Registered Users", 7, elementId + 'TotalInfo')
      createTile($("#" + elementId + "TotalInfo .row .col-lg-3").eq(2), "bg-aqua", (dataTiles[2] ? dataTiles[2] : '0'), "Last 30 days Registered Users", 30, elementId + 'TotalInfo')
      createTile($("#" + elementId + "TotalInfo .row .col-lg-3").eq(3), "bg-aqua", (dataTiles[3] ? dataTiles[3] : '0'), "Last Year Registered Users", 365, elementId + 'TotalInfo')       
    })
    jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
}

// Create Datatables
function createDataTable(element, data, type, options = null) {
    asc_desc = '';
    if(type == "idp") {
        column1 = 'idpname'
        column2 = 'count'
        data_param = 'sourceidp'
        th = 'Identity Provider'
        ths = '<th>' + th + ' Name</th>' +
        '<th>' + th + ' Identifier</th>' +
        '<th>Number of Logins</th>'
        sort_order = 2
    }
    else if(type == "sp") {
        column1 = 'spname'
        column2 = 'count'
        data_param = 'service'
        th = 'Service Provider'
        ths = '<th>' + th + ' Name</th>' +
        '<th>' + th + ' Identifier</th>' +
        '<th>Number of Logins</th>'
        sort_order = 2
    }
    else if(type == "cou") {
        column1 = 'show_date'
        column2 = 'count'
        data_param = 'names'
        ths = '<th> Date </th>' +
        '<th>' + vAxisTitle[type] + '</th>' +
        '<th>' + 'Names' + '</th>'
        sort_order = 0
    }
    else if(type == "dashboard") {
        column1 = 'show_date'
        column2 = 'count'
        data_param = 'plain_countries'
        ths = '<th> Date </th>' +
        '<th> ' + vAxisTitle[type] + ' </th>'  +
        '<th>' + 'Logins per Country' + '</th>'
        sort_order = 0
    }
    else if(type == "registered") { 
        column1 = 'show_date'
        column2 = 'count'
        data_param = 'plain_countries'
        ths = '<th> Date </th>' +
        '<th> ' + vAxisTitle[type] + ' </th>' +
        '<th>' + 'Registered Users per Country' + '</th>'
        sort_order = 0
    }
    else if(type == "map") {
        column1 = 'country'
        column2 = 'sum'
        ths = '<th> Country </th>' +
        '<th>' + 'Logins' + '</th>'
        sort_order = 0
        asc_desc = 'asc'
    }
    dataAppend = '';
    data.forEach(function (item) {
        if (type == 'idp' || type == 'sp')
            dataAppend += '<tr><td><a class="datatable-link" href="#" onclick="return false;" data-type="' + type + '" data-identifier="' + item[0][data_param] + '">' + item[0][column1] + '</a></td><td>' + item[0][data_param] + '</td><td>' + item[0][column2] + '</td></tr>';
        else if (type == 'cou' || type == 'registered' || type == 'dashboard') {
            lis = ''
            if(item[0][data_param] !== undefined) {
                item[0][data_param].split("|| ").sort(function (a, b) {
                    var nameA = a.toUpperCase(); // ignore upper and lowercase
                    var nameB = b.toUpperCase(); // ignore upper and lowercase
                    if (nameA < nameB) {
                        return -1;
                    }
                    if (nameA > nameB) {
                        return 1;
                    }
                    // names must be equal
                    return 0;
                    
                }).forEach(function (value) {
                    lis += '<li>' + value.trim() + '</li>'
                })
            }
            dataAppend += '<tr><td data-sort=' + item[0]['range_date'] + '>' + item[0][column1] + '</td><td>' + item[0][column2] + '</td><td><ul>' + lis + '</ul></td></tr>';
        }
        else if (type == 'map')
            dataAppend += '<tr><td>' + item[0][column1] + '</td><td>' + item[0][column2] + '</td></tr>';
    })

    title = (options != null && options['title'] != null ? options['title'] : '')
    id = (options != null && options['idDataTable'] != null ? options['idDataTable'] : type + 'SpecificDatatable');
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
    
    if(datatableExport){
        $("#" + id).DataTable({
            dom: 'Bfrtip',
            order: [sort_order, typeof asc_desc != '' ? asc_desc : 'desc'],
            buttons: [
                {
                    extend: 'collection',
                    text: dataTableExportButtonText,
                    buttons: [
                        {
                            extend:'copy',
                            exportOptions: {
                                stripHtml: false,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (column === 2)
                                            return data.replace(/<li>/g, "").replace(/<\/li>/g, ",").replace(/<ul>/g, "").replace(/<\/ul>/g, "")
                                        else
                                            return data.replace(/(<([^>]+)>)/ig, "");
                                    }
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            title: title,
                            exportOptions: {
                                stripHtml: false,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (column === 2)
                                            return data.replace("<li>","").replace(/<li>/g, ", ").replace(/<\/li>/g, "").replace(/<ul>/g, "").replace(/<\/ul>/g, "")
                                        else
                                            return data.replace(/(<([^>]+)>)/ig, "");
                                    }
                                }
                            }
                        },
                        {
                            extend: 'csv',
                            title: title,
                            exportOptions: {
                                stripHtml: false,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (column === 2)
                                            return data.replace("<li>","").replace(/<li>/g, ", ").replace(/<\/li>/g, "").replace(/<ul>/g, "").replace(/<\/ul>/g, "")
                                        else
                                            return data.replace(/(<([^>]+)>)/ig, "");
                                    }
                                }
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: title,
                            exportOptions: {
                                stripHtml: false,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (column === 2)
                                            return data.replace(/<li>/g, "• ").replace(/<\/li>/g, "\n").replace(/<ul>/g, "").replace(/<\/ul>/g, "")
                                        else
                                            return data.replace(/(<([^>]+)>)/ig, "");
                                    }
                                }
                            }
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
    else {
        $("#" + id).DataTable({
            order: [0, typeof asc_desc !== 'undefined' ? asc_desc : 'desc']
        });
    }
}

function createMapElement(dataMap, elementContainer, elementId, type, options = null) {
    if(dataMap !== undefined && dataMap.length >0) {
        dateSpecificClass = "date-specific"
        if (type == "idpSpecific" || type == "spSpecific") {
            $("#tabs-modal").tabs({active: 0});
            createDataTable($("#specificDataTableMapContainer"), dataMap, 'map', options)
            dateSpecificClass = "date-specific-modal"
        }
        $("." + elementContainer).html(
            '<div id="' + elementId + '" style="height:500px">'
            + '<div class="map"></div>'
            + '<div class="areaLegend"></div>'
            + '</div>'
        )
        date_from_to = calculateMinMax(dataMap)
        
        $("." + dateSpecificClass).html(" from " + date_from_to[0] + " to " + date_from_to[1]);
        createMap(dataMap, elementId);
        
    }
    else {
      $("." + elementContainer).html("No data available.")
      if (type == "idpSpecific" || type == "spSpecific") { 
          $("#specificDataTableMapContainer").html("No data available")
      }
    }
}

function reloadPage(){ 
    location.reload();
};

function createMap(mapData, id, legendLabel = 'Number of Logins', tooltipLabel = 'Logins') {
    areas = {};
    i = 1;
    maxSum = 0;
    mapData.forEach(function (mapRow) {
        mapRow = mapRow[0]
        contentTooltip = "<span style=\"font-weight:bold;\">" + mapRow.country + "</span><br />" + tooltipLabel + " : " + mapRow.sum
        contentTooltip += mapRow.additional_text !== undefined ? '<hr style="border-color:#fff; margin:5px 0px"/>' + mapRow.additional_text : '';
        areas[mapRow.countrycode] = {
            value: mapRow.sum,
            tooltip: { content: contentTooltip }
        }
        if (mapRow.sum > maxSum) {
            maxSum = mapRow.sum;
        }
        i++;
    })
    // Set Number of Legends
    numLegends = maxSum < 5 ? maxSum : 5;
    spaces = Math.round(maxSum / numLegends);
    legends = [];
    fill = ["#09EBEE", "#19CEEB", "#28ACEA", "#388EE9", "#3D76E0"];
    for(i = 0; i < numLegends; i++) {
        maxValue = ((i + 1) != numLegends ? ((i + 1) * spaces) : maxSum);
        legend = {
            min: i * spaces,
            max: maxValue,
            attrs: {
                fill: fill[i]
            },
            label: i * spaces + "-" + maxValue
        }
        legends.push(legend)
    }

    $("#" + id).mapael({
        map: {
            name: "world_countries_mercator",
            zoom: {
                enabled: true,
                maxLevel: 15,
                init: {
                    latitude: 40.717079,
                    longitude: -74.00116,
                    level: 5
                }
            },
            defaultArea: {
                attrs: {
                    fill: "#ccc", // my function for color i want to define
                    stroke: "#5d5d5d",
                    "stroke-width": 0.2,
                    "stroke-linejoin": "round",

                },
                attrsHover: {
                    fill: "#E98300",
                    animDuration: 300
                },

            },
        },
        legend: {
            area: {
                title: legendLabel,
                titleAttrs: { "font": "unset", "font-size": "12px", "font-weight": "bold" },
                slices: legends
            }
        },
        areas: areas
    })
}
// Find the min and max values at an Array
function calculateMinMax(dataArray) {
    let min = dataArray[0][0]['min'], max = dataArray[0][0]['max'] 
    for (let i = 1; i < dataArray.length; i++) {
        let minValue = dataArray[i][0]['min']
        let maxValue = dataArray[i][0]['max']
        min = (minValue < min) ? minValue : min
        max = (maxValue > max) ? maxValue : max
      }
    return [min, max]
}
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

function handleFail(xhr, textStatus, error){
  // Show an error message
  // HTML Text
  let err_msg = $.parseHTML(xhr.responseText)[0].innerHTML;
  // JSON text
  try{
    //try to parse JSON
    encodedJson = $.parseJSON(xhr.responseText);
    err_msg = encodedJson.msg;
  }catch(error){
    // Plain text
    err_msg = xhr.responseText;
  }

  if(err_msg != null) {
    error = error + ': ' + err_msg;
  }
  generateFlash(error, textStatus);
}
