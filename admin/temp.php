<!doctype html>
<head>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8">
  <title>Material skin</title>

  <script src="../assets/plugins/dhtmlxScheduler/dhtmlxscheduler.js" type="text/javascript" charset="utf-8"></script>
  <link rel="stylesheet" href="../assets/plugins/dhtmlxScheduler/dhtmlxscheduler_material.css" type="text/css"  title="no title" charset="utf-8">
  <script src="../assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_limit.js" type="text/javascript" charset="utf-8"></script>
  <script src="../assets/plugins/dhtmlxScheduler/ext/dhtmlxscheduler_tooltip.js" type="text/javascript" charset="utf-8"></script>


  <style type="text/css" >
    html, body {
      margin: 0px;
      padding: 0px;
      height: 100%;
      overflow: hidden;
    }

    .event_work div,
    .dhx_cal_editor.event_work,
    .dhx_cal_event_line.event_work{
      background-color: #ff9633!important;
    }
    .dhx_cal_event_clear.event_work{
      color: #ff9633!important;
    }

    .event_meeting div,
    .dhx_cal_editor.event_meeting,
    .dhx_cal_event_line.event_meeting
    {
      background-color: #9575cd!important;
    }
    .dhx_cal_event_clear.event_meeting{
      color: #9575cd!important;
    }

    .event_movies div,
    .dhx_cal_editor.event_movies,
    .dhx_cal_event_line.event_movies{
      background-color: #ff5722!important;
    }
    .dhx_cal_event_clear.event_movies{
      color: #ff5722!important;
    }

    .event_rest div,
    .dhx_cal_editor.event_rest,
    .dhx_cal_event_line.event_rest{
      background-color: #0fc4a7!important;
    }
    .dhx_cal_event_clear.event_rest{
      color: #0fc4a7!important;
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

  <script type="text/javascript" charset="utf-8">
    function init() {
      scheduler.config.xml_date = "%Y-%m-%d %H:%i";
      scheduler.config.first_hour = 4;
      scheduler.config.details_on_create = true;

      scheduler.config.now_date = new Date(2018, 3, 20, 14, 17);

      scheduler.templates.event_class=function(start, end, event){
        var css = "";

        if(event.evType) // if event has type property then special class should be assigned
          css += "event_"+getLabel(evType, event.evType).toLowerCase();

        return css; // default return
      };

      function getLabel(array, key){
        for (var i = 0; i < array.length; i++) {
          if (key == array[i].key)
            return array[i].label;
        }
        return null;
      }

      var evType = [
        { key: '', label: 'Select event type' },
        { key: 1, label: 'Rest' },
        { key: 2, label: 'Meeting' },
        { key: 3, label: 'Movies' },
        { key: 4, label: 'Work' }
      ];

      scheduler.locale.labels.section_evType = "Event type";

      scheduler.config.lightbox.sections=[
        { name:"description", height:43, map_to:"text", type:"textarea" , focus:true },
        { name:"evType", height:20, type:"select", options: evType, map_to:"evType" },
        { name:"time", height:72, type:"time", map_to:"auto" }
      ];

      scheduler.init("scheduler_here",new Date(2018,3,20),"week");
      scheduler.parse([
        { start_date: "2018-04-16 10:00", end_date: "2018-04-16 12:00", text:"Front-end meeting"},
        { start_date: "2018-04-17 18:00", end_date: "2018-04-17 20:00", text:"Feed ducks and city walking", evType:1},
        { start_date: "2018-04-18  8:00", end_date: "2018-04-18 11:00", text:"World Darts Championship (morning session)"},
        { start_date: "2018-04-18 12:00", end_date: "2018-04-18 14:00", text:"Lunch with Ann & Alex", evType:2},
        { start_date: "2018-04-19 16:00", end_date: "2018-04-19 17:30", text:"Game of Thrones", evType:3},
        { start_date: "2018-04-21  9:00", end_date: "2018-04-21 11:00", text:"Design workshop", evType:4},
        { start_date: "2018-04-21 18:00", end_date: "2018-04-21 21:00", text:"World Darts Championship (evening session)"},
        { start_date: "2018-04-19 00:00", end_date: "2018-04-22 00:00", text:"Couchsurfing. Family from Portugal"}
      ], "json");
    }

    function addNewEv(){
      scheduler.addEventNow();
    }
  </script>
</head>
<body onload="init();">
<div id="scheduler_here" class="dhx_cal_container" style='width:100%; height:100%;'>
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
</body>