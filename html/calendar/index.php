<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<style type="text/css" >
  html, body {
    margin: 0px;
    padding: 0px;
    height: 100%;
    overflow: hidden;
  }

  .add_event_button{
    position: absolute;
    width: 55px;
    height: 55px;
    background: #ff5722;
    border-radius: 50px;
    bottom: 40px;
    right: 55px;
    box-shadow: 0 2px 5px 0 rgba(0,0,0,0.3);
    z-index: 5;
    cursor:pointer;
  }
  .add_event_button:after{
    background: #000;
    border-radius: 2px;
    color: #FFF;
    content: attr(data-tooltip);
    margin: 16px 0 0 -137px;
    opacity: 0;
    padding: 4px 9px;
    position: absolute;
    visibility: visible;
    font-family: "Roboto";
    font-size: 14px;
    visibility: hidden;
    transition: all .5s ease-in-out;
  }
  .add_event_button:hover{
    background: #ff774c;
  }
  .add_event_button:hover:after{
    opacity: 0.55;
    visibility: visible;
  }
  .add_event_button span:before{
    content:"";
    background: #fff;
    height: 16px;
    width: 2px;
    position: absolute;
    left: 26px;
    top: 20px;
  }
  .add_event_button span:after{
    content:"";
    height: 2px;
    width: 16px;
    background: #fff;
    position: absolute;
    left: 19px;
    top: 27px;
  }

  .dhx_cal_event div.dhx_event_resize.dhx_footer{
    background-color: transparent !important;
  }
</style>

<div class="card-box">
  <div class="row sticky no-print">
    <div class="col-md-12">
      <!--<div class="go_back" style="margin-bottom:5px;"><button class="btn btn-primary waves-effect waves-light" id="btn_search_to_main"><i class="zmdi zmdi-arrow-left m-r-5"></i> <span>GO BACK</span></button></div>-->

      <div class="filters_wrapper" id="filters_wrapper">
        <span>Display:</span>

        <?php
        if($event_qry = $dbconn->query('SELECT * FROM calendar_event_types')) {
          while($event = $event_qry->fetch_assoc()) {
            echo "<label style='color:#{$event['color']};font-weight:bold;'><input type='checkbox' name='{$event['key']}'> {$event['name']}</label> &nbsp;";
          }
        }
        ?>
      </div>

      <div id="calendar" class="dhx_cal_container" style='width:100%; height:80vh;'>
        <div class="dhx_cal_navline">
          <div class="dhx_cal_prev_button">&nbsp;</div>
          <div class="dhx_cal_next_button">&nbsp;</div>
          <div class="dhx_cal_today_button"></div>
          <div class="dhx_cal_date"></div>
          <div class="dhx_cal_tab" name="day_tab" style="right:204px;"></div>
          <div class="dhx_cal_tab" name="week_tab" style="right:140px;"></div>
          <div class="dhx_cal_tab" name="month_tab" style="right:76px;"></div>
        </div>
        <div class="dhx_cal_header">
        </div>
        <div class="dhx_cal_data">
        </div>
      </div>
      <div class="add_event_button" onclick="addNewEv()" data-tooltip="Create new event"><span></span></div>
    </div>
  </div>
</div>

<script>
  var default_time_format = "%g:%i%a";
  var default_date_format = "%m-%d-%Y ";

  function addNewEv(){
    scheduler.addEventNow();
  }

  $(function() {
    var evType;

    // this configures the date/time format to be MySQL format
    scheduler.config.xml_date = "%Y-%m-%d %H:%i";
    scheduler.config.first_hour = 7;
    scheduler.config.last_hour = 19;
    scheduler.config.details_on_create = true;
    scheduler.config.hour_date = default_time_format;
    scheduler.config.now_date = new Date();
    scheduler.config.time_step = 15;
    scheduler.config.limit_time_select = true;
    scheduler.config.multi_day = true;
    scheduler.config.event_duration = 30;
    scheduler.config.auto_end_date = true;
    scheduler.keys.edit_save = false; // remove "enter" closing the lightbox
    scheduler.config.repeat_precise = true;


    scheduler.xy.margin_top = 70; // move the calendar down 30 pixels from the top of it's box
    scheduler.locale.labels.section_evType = "Event type";

    scheduler.templates.tooltip_date_format = function(date) {
      var formatFunc = scheduler.date.date_to_str("%m-%d-%Y %g:%i%a");

      return formatFunc (date);
    };

    scheduler.templates.event_class=function(start, end, event) {
      function getLabel(array, key){
        for (var i = 0; i < array.length; i++) {
          if (key === array[i].key)
            return array[i].label;
        }
        return null;
      }

      var css = "";

      if(event.evType) // if event has type property then special class should be assigned
        css += "event_"+getLabel(evType, event.evType).replace(/\s/g, '_').toLowerCase();

      return css; // default return
    };

// TODO: At some point, make this a user preference?
    var filters = {
      md: true,
      scd: true,
      employee: true,
      oo: true,
      cd: true,
      meetings: true
    };

    var filter_inputs = document.getElementById("filters_wrapper").getElementsByTagName("input");

    for (var i=0; i<filter_inputs.length; i++) {
      var filter_input = filter_inputs[i];

      // set initial input value based on filters settings
      filter_input.checked = filters[filter_input.name];

      // attach event handler to update filters object and refresh view (so filters will be applied)
      filter_input.onchange = function() {
        filters[this.name] = !!this.checked;
        scheduler.updateView();
      }
    }

// here we are using single function for all filters but we can have different logic for each view
    scheduler.filter_month = scheduler.filter_day = scheduler.filter_week = function(id, event) {
      // display event only if its type is set to true in filters obj
      // or it was not defined yet - for newly created event
      if (filters[event.evType] || event.evType === scheduler.undefined) {
        return true;
      }

      // default, do not display event
      return false;
    };

// grab the event types from the database
    $.getJSON("/html/calendar/ajax/events.php?action=getEvents", function(data) {
      evType = data; // assign the event types to a variable accessible outside of this function
    }).done(function() {
      // if the lightbox is configured before event type is initialized, it will not be able to process
      scheduler.config.lightbox.sections=[
        { name:"description", height:200, map_to:"text", type:"textarea" , focus:true },
        { name:"recurring", height:115, type:"recurring", map_to:"rec_type", button:"recurring" },
        { name:"evType", height:20, type:"select", options: evType, map_to:"evType" },
        { name:"time", height:72, type:"time", map_to:"auto", time_format: ["%m","%d", "%Y", "%H:%i"] }
      ];
    });

    scheduler.init("calendar",new Date(),"week"); // start the scheduler
    scheduler.load("/html/calendar/ajax/data.php", "json"); // load the data

    var dp = new dataProcessor("/html/calendar/ajax/processor.php"); // the URL for where autosaving is

    dp.init(scheduler); // start the scheduler

    dp.setTransactionMode("JSON"); // tell it to send one update at a time to the processor, as JSON
  });
</script>