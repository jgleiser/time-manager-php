// Add number of days to a date
Date.prototype.addDays = function(days) {
  var dat = new Date(this.valueOf());
  dat.setDate(dat.getDate() + days);
  return dat;
}

// get date format in string YYYY/MM/DD or other separator as 'c'
Date.prototype.getFormatDate = function(c) {
  if (!c) c = "/";
  var dat = new Date(this.valueOf());
  var day = dat.getDate().toString();
  var month = (dat.getMonth() + 1).toString();
  var year = dat.getFullYear().toString();
  if (parseInt(day, 10) < 10) day = "0" + day;
  if (parseInt(month, 10) < 10) month = "0" + month;
  return year+c+month+c+day;
}

var TM = window.TM = window.TM || {};

// Cookies functions from www.w3schools.com/js/js_cookies.asp
// Modified for more options
TM.setCookie = function(c_name, value, exdays, path) {
  "use strict";
  var exdate = new Date(), c_value;
  path = path ? "/" + path + "/" : "/";
  exdate.setTime(exdate.getTime() + (exdays*24*60*60*1000)); // to set for days
  c_value = escape(value) + ((exdays == null) ?
    "" :
    "; expires=" + exdate.toUTCString()) + "; path=" + path;
  document.cookie = c_name + "=" + c_value;
};

TM.getCookie = function(c_name) {
  "use strict";
  var
    i, x, y,
    ARRcookies = document.cookie.split(";");

  for (i = 0; i < ARRcookies.length; i++) {
    x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
    y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
    x = x.replace(/^\s+|\s+$/g, "");
    if (x == c_name) {
      return unescape(y);
    }
  }
};

TM.clearCookie = function(c_name) {
  "use strict";
  TM.setCookie(c_name, "", 0);
};

// Mark preferred work hours in app view
TM.markPreferredWorkHours = function(msg) {
  "user strict";
  var start = msg.workHoursStart.split(":");
  var end = msg.workHoursEnd.split(":");
  var start_time = parseInt(start[0], 10);
  var end_time = parseInt(end[0], 10);
  var time_list = $("#time-list");
  time_list.find("a").removeClass("PreferredWorkHour");
  for (var i = start_time; i < end_time; i++) {
    if (!time_list.find("a.time-"+("0" + i).slice(-2)).hasClass("PreferredWorkHour")) {
      time_list.find("a.time-"+("0" + i).slice(-2)).addClass("PreferredWorkHour");
    }
  }
  $("#form_change_hours").find("input[name=start_time]").val(msg.workHoursStart)
    .parent().find("input[name=end_time]").val(msg.workHoursEnd);
};

// Mark worked hours in app view
TM.markWorkedHours = function(msg, date) {
  "user strict";
  var page_date = date ? new Date(date).getFormatDate("-") : new Date().getFormatDate("-");
  var time_list = $("#time-list");
  time_list.find("a").removeClass("worked");
  $.each(msg, function(idx, val) {
    // [0] = date, [1] = time
    var start = val['START_DT'].split(" ");
    var end = val['END_DT'].split(" ");
    // if start is before today, then end is today
    if (start[0] !== page_date && end[0] === page_date) {
      var split_time = end[1].split(":");
      var start_time = 0;
      var end_time = parseInt(split_time[0], 10);
      for (var i = start_time; i < end_time; i++) {
        if (!time_list.find("a.time-"+("0" + i).slice(-2)).hasClass("worked")) {
          time_list.find("a.time-"+("0" + i).slice(-2)).addClass("worked");
        }
      }
      if (parseInt(split_time[1], 10) > 0) {
        if (!time_list.find("a.time-"+("0" + i).slice(-2)).hasClass("worked")) {
          time_list.find("a.time-"+("0" + i).slice(-2)).addClass("worked");
        }
      }
    }
    // if end is after today, then start is today
    else if (start[0] === page_date && end[0] !== page_date) {
      var split_time = start[1].split(":");
      var start_time = parseInt(split_time[0], 10);
      var end_time = 23;
      for (var i = start_time; i <= end_time; i++) {
        if (!time_list.find("a.time-"+("0" + i).slice(-2)).hasClass("worked")) {
          time_list.find("a.time-"+("0" + i).slice(-2)).addClass("worked");
        }
      }
    }
    // else start and end are from today
    else {
      var split_time_start = start[1].split(":");
      var split_time_end = end[1].split(":");
      var start_time = parseInt(split_time_start[0], 10);
      var end_time = parseInt(split_time_end[0], 10);;
      for (var i = start_time; i < end_time; i++) {
        if (!time_list.find("a.time-"+("0" + i).slice(-2)).hasClass("worked")) {
          time_list.find("a.time-"+("0" + i).slice(-2)).addClass("worked");
        }
      }
      if (parseInt(split_time_end[1], 10) > 0) {
        if (!time_list.find("a.time-"+("0" + i).slice(-2)).hasClass("worked")) {
          time_list.find("a.time-"+("0" + i).slice(-2)).addClass("worked");
        }
      }
    }
  });
};

// Append note data to hours in app view
TM.writeNotesToHours = function(msg, date) {
  "user strict";
  var page_date = date ? new Date(date).getFormatDate("-") : new Date().getFormatDate("-");
  var time_list = $("#time-list");
  time_list.find("a span").text("-");
  $.each(msg, function(idx, val) {
    var start_dt = val['START_DT'].split(" ");
    var start_time = start_dt[1].split(":");
    var end_dt = val['END_DT'].split(" ");
    var end_time = end_dt[1].split(":");
    var hours = parseInt(val['MINUTES'], 10) / 60;
    // add notes if start_dt is page_date
    if (start_dt[0] === page_date) {
      time_list.find("a.time-" + start_time[0]).data("noteid", val['ROW_ID'])
        .data("notetime", start_time[0]+":"+start_time[1])
        .find("p").data("hours", hours);
      time_list.find("a.time-" + start_time[0] + " p span:first")
        .text(hours+"h")
        .next().next().text(val['NOTE']);
      // add data noteid to other time slots with the same note
      st = start_time[0];
      total_hours = hours;
      while (total_hours > 1) {
        total_hours -= 1;
        st = ("0" + (parseInt(st,10)+1)).slice(-2);
        time_list.find("a.time-" + st).data("noteid", val['ROW_ID'])
          .data("notetime", start_time[0]+":"+start_time[1])
          .find("p").data("hours", hours);
        time_list.find("a.time-" + st + " p span:first")
          .text(hours+"h")
          .next().next().text(val['NOTE']);
        if (st === "23") break;
      }
    }
    // TODO: add data from notes comming from last day
  });
};

// Refresh app view
TM.refreshApp = function(date) {
  // get user data to mark preferred work hours
  $.ajax({
     type: 'GET',
     url: '/api/v1/users/' + TM.getCookie('userid') + '?apikey=' + TM.getCookie('apiKey'),
     error: TM.ajaxError,
     success: function (data, textStatus, jqXHR) {
      var msg = JSON.parse(data);
      if (msg.error) {
        console.log(msg.error.msg);
      }
      else {
        TM.markPreferredWorkHours(msg);
      }
     }
  });
  // mark worked hours and add notes
  $.ajax({
    type: 'GET',
    url: '/api/v1/users/' + TM.getCookie('userid') + "/timenotes/workday/" + date + '?apikey=' + TM.getCookie('apiKey'),
    error: TM.ajaxError,
    success: function (data, textStatus, jqXHR) {
      var msg = JSON.parse(data);
      if (msg.error) {
        console.log(msg.error.msg);
      }
      else {
        TM.markWorkedHours(msg, date.replace(/-/g, "/"));
        TM.writeNotesToHours(msg, date.replace(/-/g, "/"));
      }
    }
  });
  // load admin/manager links
  var role = TM.getCookie('role');
  if (role && role === 'ADMIN') {
    $("#app .appfooter span").html('<a href="#admin_page" class="admin-link">Admin</a>');
  }
  else if (role && role === 'MANAGER') {
    $("#app .appfooter span").html('<a href="#manager_page" class="admin-link">Manager</a>');
  }

};

/*
  input: data array with objects, each object has
         {'date', 'minutes', 'note'}
  returns: response object with date, minutes (sum) and array of notes
*/
TM.parseSummary = function(data) {
  var response = [];
  if (data.length > 0) {
    var date = data[0].date;
    var sum = 0;
    var notes = [];
    for (var i = 0; i < data.length; i++) {
      if (data[i]['date'] === date) {
        sum += parseInt(data[i].minutes, 10);
        notes.push(data[i].note);
      }
      else {
        response.push({"date": date, "minutes": sum, "notes": notes});
        date = data[i].date;
        sum = parseInt(data[i].minutes, 10);
        notes = [data[i].note];
      }
      if (i+1 === data.length) {
        response.push({"date": date, "minutes": sum, "notes": notes});
      }
    }
  }
  return response;
};

// Default ajax error
TM.ajaxError = function(jqXHR, textStatus, errorThrown) {
  console.log("error with ajax request");
  console.log("jqXHR: " + JSON.stringify(jqXHR));
  console.log("textStatus: " + JSON.stringify(textStatus));
  console.log("errorThrown: " + JSON.stringify(errorThrown));
};

// Refresh admin view
TM.ajaxAdminList = function() {
  // TODO
};

// Refresh manager view
TM.ajaxManagerList = function() {
  // TODO
};

// Refresh admin time notes view
TM.ajaxAdminNotes = function(userid) {
   // TODO
};

// Actions for links in admin view
TM.loadOptUpdUser = function(e) {
  // TODO
};

// Actions for links in admin time notes view
TM.loadOptUpdNotes = function(e) {
  // TODO
};
