<?php
require '../../includes/header_start.php';

//outputPHPErrs();
?>

<div class="col-md-12">
  <div class="card-box">
    <div class="row">
      <div class="col-md-6 m-t-20">
        <ul class="nav nav-tabs m-b-10" id="roomNotes" role="tablist">
          <li class="nav-item">
            <a class="nav-link active show" id="room-tab" data-toggle="tab" href="#room" role="tab" aria-controls="room" aria-expanded="true" aria-selected="true">Room</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="so-tab" data-toggle="tab" href="#so" role="tab" aria-controls="so" aria-selected="false">SO</a>
          </li>
        </ul>
        <div class="tab-content" id="roomNotesContent">
          <div role="tabpanel" class="tab-pane fade in active show" id="room" aria-labelledby="room-tab"></div>
          <div class="tab-pane fade" id="so" role="tabpanel" aria-labelledby="so-tab"></div>
        </div>
      </div>
    </div>
  </div>
</div>

