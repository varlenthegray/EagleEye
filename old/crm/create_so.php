<?php
require '../includes/header_start.php';
require '../includes/header_end.php';
?>


<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <!--<div class="btn-group pull-right m-t-15">
            <button type="button" class="btn btn-custom dropdown-toggle waves-effect waves-light"
                    data-toggle="dropdown" aria-expanded="false">Settings <span class="m-l-5"><i
                        class="fa fa-cog"></i></span></button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#">Action</a>
                <a class="dropdown-item" href="#">Another action</a>
                <a class="dropdown-item" href="#">Something else here</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Separated link</a>
            </div>

        </div>-->
        <h4 class="page-title">Create New Service Order</h4>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12">
                    <h2>Service Order Information</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="order_type">Order Type</label>
                        <select name="order_type" id="order_type" class="form-control" tabindex="1">
                            <option value="Sample" selected>Sample</option>
                            <option value="Job">Job</option>
                        </select>
                    </fieldset>
                </div>

                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="last_name">Service Order #</label>
                        <input class="form-control" type="text" placeholder="676" id="so_number" name="so_number" tabindex="2" disabled value="<?php echo $_REQUEST['so_number']; ?>">
                    </fieldset>
                </div>

                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="room">Room</label>
                        <select name="room" id="room" class="form-control" tabindex="3">
                            <option value="Whole House">Whole House</option>
                            <option value="Kitchen" selected>Kitchen</option>
                            <option value="Bedroom">Bedroom</option>
                            <option value="Bathroom">Bathroom</option>
                            <option value="Dining Room">Dining Room</option>
                            <option value="Living Room">Living Room</option>
                            <option value="Office">Office</option>
                            <option value="Study">Study</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Closet">Closet</option>
                            <option value="Den">Den</option>
                            <option value="Hallway">Hallway</option>
                            <option value="Laundry Room">Laundry Room</option>
                            <option value="Bar">Bar</option>
                            <option value="Media Room">Media Room</option>
                            <option value="Fireplace">Fireplace</option>
                            <option value="Other">Other</option>
                        </select>
                    </fieldset>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="project_manager">Project Manager</label>
                        <input type="text" placeholder="Project Manager" name="project_manager" id="project_manager" class="form-control" tabindex="4">
                    </fieldset>
                </div>

                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="product_type">Product Type</label>
                        <select name="product_type" id="product_type" class="form-control" tabindex="5">
                            <option value="Cabinet" selected>Cabinet</option>
                            <option value="Closet">Closet</option>
                            <option value="Sample">Sample</option>
                            <option value="Display">Display</option>
                            <option value="Add-on">Add-on</option>
                            <option value="Warranty">Warranty</option>
                        </select>
                    </fieldset>
                </div>

                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="request_info">Brief Request Info.</label>
                        <input type="text" placeholder="Brief Request Info." name="request_info" id="request_info" class="form-control" tabindex="6">
                    </fieldset>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    &nbsp;
                </div>

                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="remodel_required">Remodel Required</label>
                        <select name="remodel_required" id="remodel_required" class="form-control" tabindex="7">
                            <option value="1">Yes</option>
                            <option value="0" selected>No</option>
                        </select>
                    </fieldset>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <fieldset class="form-group">
                        <label for="task_status">Task Status</label>
                        <textarea class="form-control" id="task_status" name="task_status" placeholder="Task Status" rows="3"
                                  data-toggle="popover" data-placement="top" data-trigger="focus" title="" data-html="true"
                                  data-content="<table style='font-size: 9px;'>
                                <tr>
                                    <td>CON = Conestoga</td>
                                    <td class='text-md-right'>RW = Rework</td>
                                </tr>
                                <tr>
                                    <td>DEL = Delivery</td>
                                    <td class='text-md-right'>S/B = Scheduled Back</td>
                                </tr>
                                <tr>
                                    <td>DPL = Diminishing Punch List</td>
                                    <td class='text-md-right'>SEL = Selections</td>
                                </tr>
                                <tr>
                                    <td>EM = Email</td>
                                    <td class='text-md-right'>T/W = This Week</td>
                                </tr>
                                <tr>
                                    <td>ETA = Estimated Time of Arrival</td>
                                    <td class='text-md-right'>W/A = Will Advise</td>
                                </tr>
                                <tr>
                                    <td>FU = Follow Up</td>
                                    <td class='text-md-right'>W/C = Will Contact</td>
                                </tr>
                                <tr>
                                    <td>N/A = Not Available</td>
                                    <td class='text-md-right'>WO = Work Order</td>
                                </tr>
                            </table>" data-original-title="Abbreviations">
                        </textarea>
                    </fieldset>
                </div>
            </div>

            <div class="row text-md-center">
                <button type="submit" class="btn btn-primary waves-effect" id="go_button">Create</button>
            </div>
        </div>
    </div>
</div>

<?php 
require '../includes/footer_start.php';
require '../includes/footer_end.php'; 
?>