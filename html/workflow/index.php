<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<script src="/assets/plugins/dhtmlxGantt/dhtmlxgantt.js"></script>
<link href="/assets/plugins/dhtmlxGantt/dhtmlxgantt.css" rel="stylesheet">

<style>
  html, body{
    margin:0px;
    padding:0px;
    height:100%;
    overflow:hidden;
  }
</style>

<div class="row">
  <div class="col-md-10">
    <div id="gantt" style="width:100%; height: 400px;"></div>
  </div>
</div>

<script>
  var tasks = {
    data:[
      {id:1, text:"Project #1", start_date:"19-08-2018", duration:18},
      {id:2, text:"Task #1", start_date:"19-08-2018", duration:8, parent:1},
      {id:3, text:"Task #2", start_date:"27-08-2018", duration:8, parent:1}
    ]
  };
  gantt.init("gantt");
  gantt.parse(tasks);
</script>