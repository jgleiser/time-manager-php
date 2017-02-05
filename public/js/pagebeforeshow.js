(function($) {
  $(document).on('pagebeforeshow', '#index', function(){
    if (parseInt(TM.getCookie('userid'), 10) > 0) {
      window.location.href = "#app";
      $("form input.apikey").val(TM.getCookie('apiKey'));
    }
  });
  
  // on login check cookies before page show, if set then go to app
  $(document).on('pagebeforeshow', '#login', function(){
    if (parseInt(TM.getCookie('userid'), 10) > 0) {
      window.location.href = "#app";
      $("form input.apikey").val(TM.getCookie('apiKey'));
    }
  });
  
  $(document).on("pagebeforeshow", "#app", function() {
    if (TM.getCookie('userid') === undefined) {
      window.location.href = "#index";
    } else {
      // clear admin/manager links
      $("#app .appfooter span").empty();
      // clear page date
      $("#app-date").empty();
      // clear data from time notes
      $("#time-list a").removeData("noteid").removeData("notetime").find("p").removeData("hours");
      // check for page date, set to today if not seted
      if (typeof $("#app").data("date") === 'undefined') {
        $("#app").data("date", new Date().getFormatDate("-"));
      }
      // set default date to app to today
      var page_date = $("#app").data("date");
      $("#app-date").text(page_date);
      TM.refreshApp(page_date);
      $("form input.apikey").val(TM.getCookie('apiKey'));
    }
  });
  
  // Set data for add_note form
  $(document).on("pagebeforeshow", "#add_note", function() {
    var form_add_node = $("#form_add_note");
    form_add_node.attr("action", "/api/v1/users/" + TM.getCookie('userid') + "/timenotes");
    form_add_node.find("input[name=start_dt]").val($("#app").data("date"));
    form_add_node.find("input[name=work_hours]").val("");
    form_add_node.find("input[name=time_note]").val("");
  });
  
  // Set data for change preferred hours
  $(document).on("pagebeforeshow", "#changeHours", function() {
    var form_change_hours = $("#form_change_hours");
    form_change_hours.attr("action", "/api/v1/users/" + TM.getCookie('userid'));
  });
  
  // set userid and default date to work summary form
  $(document).on("pagebeforeshow", "#filter-dates", function() {
    var form_filter_dates = $("#form_filter_dates");
    form_filter_dates.attr("action", "/api/v1/users/" + TM.getCookie('userid') + "/timenotes/summary");
    form_filter_dates.find("input[name=start_dt]").val($("#app").data("date"));
    form_filter_dates.find("input[name=end_dt]").val($("#app").data("date"));
  });
  
  // allow only admin to admin page
  $(document).on("pagebeforeshow", "#admin_page", function() {
    var role = TM.getCookie('role');
    if (!role || role !== "ADMIN") window.location.href = "#app";
    else TM.ajaxAdminList();
  });
  
  // allow only manager user to manager page
  $(document).on("pagebeforeshow", "#manager_page", function() {
    var role = TM.getCookie('role');
    if (!role || role !== "MANAGER") window.location.href = "#app";
    else TM.ajaxManagerList();
  });
  
  // allow only admin/manager to edit users form
  $(document).on("pagebeforeshow", "#admin_edit", function() {
    var role = TM.getCookie('role');
    if (!role || (role !== "MANAGER" && role !== "ADMIN")) {
      window.location.href = "#app";
    }
  });
})(jQuery);