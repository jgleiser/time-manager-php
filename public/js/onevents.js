(function($) {
  $("#form_register_user").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      error: function (jqXHR, textStatus, errorThrown) {
         var error_data = JSON.parse(jqXHR.responseText);
         popupRegisterNOK.find("div p").text(error_data.error.msg);
          popupRegisterNOK.popup({ positionTo: form }).popup("open");
      },
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        if (msg.error) {
          var popupRegisterNOK = $("#popupRegisterNOK");
          popupRegisterNOK.find("div p").text(msg.error.msg);
          popupRegisterNOK.popup({ positionTo: form }).popup("open");
        }
        else {
          $("#popupRegisterOK").popup({ positionTo: form }).popup("open");
        }
      }
    });
  });
  
  $("#form_login").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      error: function (jqXHR, textStatus, errorThrown) {
        var error_data = JSON.parse(jqXHR.responseText);
        var popupLoginNOK = $("#popupLoginNOK");
        popupLoginNOK.find("div p").text(error_data.error.msg);
        popupLoginNOK.popup({ positionTo: form }).popup("open");
      },
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        if (msg.error) {
          var popupLoginNOK = $("#popupLoginNOK");
          popupLoginNOK.find("div p").text(msg.error.msg);
          popupLoginNOK.popup({ positionTo: form }).popup("open");
        }
        else {
          //var exp_date = new Date();
          TM.setCookie('userid', msg.id, 1);
          TM.setCookie('username', msg.username, 1);
          TM.setCookie('role', msg.role, 1);
          TM.setCookie('workHoursStart', msg.workHoursStart, 1);
          TM.setCookie('workHoursEnd', msg.workHoursEnd, 1);
          TM.setCookie('apiKey', msg.apiKey, 1);
          TM.setCookie('apiKeyExpiration', msg.apiKeyExpiration, 1);
          $("form input.apikey").val(msg.apiKey);
          window.location.href = "#app";
        }
      }
    });
  });
  
  $("#btn-logout").on("click", function(e) {
    e.preventDefault();
    $.ajax({
      type: 'GET',
      url: '/api/v1/logout/' + TM.getCookie('userid') + '?apikey=' + TM.getCookie('apiKey'),
      error: TM.ajaxError,
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        if (msg.error) {
          console.log(msg.error);
        }
        else {
          $("#form_login")[0].reset();
          TM.clearCookie('userid');
          TM.clearCookie('username');
          TM.clearCookie('role');
          TM.clearCookie('workHoursStart');
          TM.clearCookie('workHoursEnd');
          TM.clearCookie('apiKey');
          TM.clearCookie('apiKeyExpiration');
          $("form input.apikey").val("");
          window.location.href = "#index";
        }
      }
    });
  });
  
  $("#time-list a").on("click", function(e) {
    e.preventDefault();
    // no note for the time
    if ($(this).data("noteid") === undefined && !$(this).hasClass("worked")) {
      var form_add_note = $("#form_add_note");
      form_add_note.find("input[name=start_time]").val($(this).parent().data("time"));
      form_add_note.find("input[name=work_hours]").val("");
      form_add_note.find("input[name=time_note]").val("");
      window.location.href = "#add_note";
    }
    // worked hour without note, happens on cross day notes
    else if ($(this).data("noteid") === undefined && $(this).hasClass("worked")) {
      //continue;
      // TODO: add message
    }
    // there is a note
    else {
      var form_edit_note = $("#form_edit_note");
      form_edit_note.attr("action", "/api/v1/users/" + TM.getCookie('userid') + "/timenotes/" + $(this).data("noteid"));
      form_edit_note.find("input[name=start_dt]").val($("#app-date").text());
      form_edit_note.find("input[name=start_time]").val($(this).data("notetime"));
      form_edit_note.find("input[name=work_hours]").val($(this).find("p").data("hours"));
      form_edit_note.find("input[name=time_note]").val($(this).find("p span:first").next().next().text());
      $("#delete_note").attr("href", "/api/v1/users/" + TM.getCookie('userid') + "/timenotes/" + $(this).data("noteid"));
      window.location.href = "#edit_note";
    }
  });
  
  $("#day-links a").on("click", function(e) {
    e.preventDefault();
    var page_date = $("#app").data("date").replace(/-/g, "/");
    var date;
    if (this.hash === "#prevday") {
      date = new Date(page_date).addDays(-1).getFormatDate("-");
    }
    else if (this.hash === "#nextday") {
      date = new Date(page_date).addDays(1).getFormatDate("-");
    }
    else {
      date = new Date().getFormatDate("-");
    }
    $("#app").data("date", date);
    $("#app-date").text(date);
    TM.refreshApp(date);
  });
  
  $("#form_add_note").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      error: function (jqXHR, textStatus, errorThrown) {
        var error_data = JSON.parse(jqXHR.responseText);
        var popupAddNoteNOK = $("#popupAddNoteNOK");
        popupAddNoteNOK.find("div p").text(error_data.error.msg);
        popupAddNoteNOK.popup({ positionTo: form }).popup("open");
      },
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        if (msg.error) {
          var popupAddNoteNOK = $("#popupAddNoteNOK");
          popupAddNoteNOK.find("div p").text(msg.error.msg);
          popupAddNoteNOK.popup({ positionTo: form }).popup("open");
        }
        else {
          $("#popupAddNoteOK").popup({ positionTo: form }).popup("open");
        }
      }
    });
  });
  
  $("#form_edit_note").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      headers: 'application/x-www-form-urlencoded',
      error: function (jqXHR, textStatus, errorThrown) {
        var popupEditNoteNOK = $("#popupEditNoteNOK");
        popupEditNoteNOK.find("div p").text(msg.error.msg);
        popupEditNoteNOK.popup({ positionTo: form }).popup("open");
      },
      success: function (data, textStatus, jqXHR) {
        var msg = textStatus === "nocontent" ? [] : JSON.parse(data);
        //var form_start_dt = form.find("input[name=start_dt]").val();
        if (msg.error) {
          var popupEditNoteNOK = $("#popupEditNoteNOK");
          popupEditNoteNOK.find("div p").text(msg.error.msg);
          popupEditNoteNOK.popup({ positionTo: form }).popup("open");
        }
        else {
          $("#popupEditNoteOK").popup({ positionTo: form }).popup("open");
        }
      }
    });
  });
  
  $("#delete_note").on("click", function(e) {
    e.preventDefault();
    $.ajax({
      data: {'apikey': TM.getCookie('apiKey')},
      type: 'DELETE',
      url: this.href,
      headers: 'application/x-www-form-urlencoded',
      error: TM.ajaxError,
      success: function (data, textStatus, jqXHR) {
        var msg = textStatus === "nocontent" ? [] : JSON.parse(data);
        if (msg.error) {
          var popupDeleteNoteNOK = $("#popupDeleteNoteNOK");
          popupDeleteNoteNOK.find("div p").text(msg.error.msg);
          popupDeleteNoteNOK.popup({ positionTo: $(this).parent().parent() }).popup("open");
        }
        else {
          $("#popupDeleteNoteOK").popup({ positionTo: $(this).parent().parent() }).popup("open");
        }
      }
    });
  });
  
  $("#form_change_hours").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      error: TM.ajaxError,
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        if (msg.error) {
          var popupAddNoteNOK = $("#popupChangeHoursNOK");
          popupAddNoteNOK.find("div p").text(msg.error.msg);
          popupAddNoteNOK.popup({ positionTo: form }).popup("open");
        }
        else {
          $("#popupChangeHoursOK").popup({ positionTo: form }).popup("open");
        }
      }
    });
  });
  
  $("#form_filter_dates").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      error: TM.ajaxError,
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        // populate summary and redirect
        var summary_content = $("#summary-content");
        summary_content.empty();
        var summary = TM.parseSummary(msg);
        var content = "";
        for (var i = 0; i < summary.length; i++) {
          content += "<li>Date: "+summary[i].date+"</li>";
          content += "<li>Total time: "+parseInt(summary[i].minutes,10)/60+"h</li>";
          content += "<li>Notes: <ul>";
          for (var j = 0; j < summary[i].notes.length; j++) {
            content += "<li>"+summary[i].notes[j]+"</li>";
          }
          content += "</ul></li><li class='divider'></li>";
        }
        if (summary.length === 0) {
          content = "<li>No notes found for the given dates</li>";
        }
        summary_content.html(content);
        
        window.location.href = '#summary';
      }
    });
  });
  
  $("#form_update_user").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      error: TM.ajaxError,
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        if (msg.error) {
          var popupUpdUserNOK = $("#popupUpdUserNOK");
          popupUpdUserNOK.find("div p").text(msg.error.msg);
          popupUpdUserNOK.popup({ positionTo: form }).popup("open");
        }
        else {
          var popupUpdUserOK = $("#popupUpdUserOK");
          if (TM.getCookie('role') === 'MANAGER')
            popupUpdUserOK.find("a").attr("href", "#manager_page");
          else if (TM.getCookie('role') === 'ADMIN')
            popupUpdUserOK.find("a").attr("href", "#admin_page");
          popupUpdUserOK.popup({ positionTo: form }).popup("open");
        }
      }
    });
  });
  
  $("#form_adm_edit_note").on("submit", function(e) {
    e.preventDefault();
    var form = $(this);
    var datos_form = form.serialize();
    $.ajax({
      data: datos_form,
      type: form.attr('method'),
      url: form.attr('action'),
      error: TM.ajaxError,
      success: function (data, textStatus, jqXHR) {
        var msg = JSON.parse(data);
        if (msg.error) {
          var popupAdmEditNoteNOK = $("#popupAdmEditNoteNOK");
          popupAdmEditNoteNOK.find("div p").text(msg.error.msg);
          popupAdmEditNoteNOK.popup({ positionTo: form }).popup("open");
        }
        else {
          $("#popupAdmEditNoteOK").popup({ positionTo: form }).popup("open");
          var userid = $("#admin_tm_page table.time-note-list").data("userid");
          TM.ajaxAdminNotes(userid);
        }
      }
    });
  });
  
})(jQuery);