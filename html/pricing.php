<?php
require '../includes/header_start.php';
?>

<link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="card-box">
            <div class="row">
                <div class="col-md-12"><!-- Start page -->
                    <ul class="nav nav-tabs m-b-10" id="salesTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="global-tab" data-toggle="tab" href="#global"
                               role="tab" aria-controls="global" aria-expanded="true">Globals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="summary-tab" data-toggle="tab" href="#summary"
                               role="tab" aria-controls="summary">Summary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="line-item-tab" data-toggle="tab" href="#line-item"
                               role="tab" aria-controls="line-item">Line Items</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="salesTabContent">
                        <div role="tabpanel" class="tab-pane fade in active" id="global" aria-labelledby="global-tab">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-1"><input type="text" class="form-control" id="sales-order" placeholder="Sales Order"></div>
                                    <div class="col-md-4"><input type="text" class="form-control" id="job-name" placeholder="Job Name"></div>
                                    <div class="col-md-2"><div>
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="mm/dd/yyyy" id="datepicker">
                                                <span class="input-group-addon bg-custom b-0"><i class="icon-calender"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-1"><input type="text" class="form-control" id="dealer-code" placeholder="Dealer Code"></div>
                                    <div class="col-md-2"><input type="text" class="form-control" id="dealer-po-num" placeholder="Dealer PO#"></div>
                                    <div class="col-md-2 col-md-offset-2"><input type="text" class="form-control" id="lead-time" placeholder="Lead Time"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 col-md-offset-1">
                                        <select class="form-control text-muted" id="payment-method">
                                            <option value="Payment Method" selected disabled>Payment Method</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Credit">Credit</option>
                                            <option value="Check (ACH)">Check (ACH)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 col-md-offset-2">
                                        <select class="form-control text-muted" id="ship-via">
                                            <option value="Ship Via" selected disabled>Ship Via</option>
                                            <option value="Cycle Truck">Cycle Truck</option>
                                            <option value="UPS">UPS</option>
                                            <option value="FedEx">FedEx</option>
                                            <option value="LTL">LTL</option>
                                            <option value="Local Pickup">Local Pickup</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 col-md-offset-1">
                                        <select class="form-control text-muted" id="order-type">
                                            <option value="Job Type" selected disabled>Job Type</option>
                                            <option value="Job">Job</option>
                                            <option value="Quote">Quote</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 col-md-offset-2">
                                        <select class="form-control text-muted" id="ship-to">
                                            <option value="Ship To" selected disabled>Ship To</option>
                                            <option value="Showroom">Showroom</option>
                                            <option value="Warehouse">Warehouse</option>
                                            <option value="Job Site">Job Site (+$150)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 col-md-offset-1">
                                        <select class="form-control text-muted" id="product-type">
                                            <option value="Product Type" disabled selected>Product Type</option>
                                            <option value="Cabinet">Cabinet</option>
                                            <option value="Closet">Closet</option>
                                            <option value="Sample">Sample</option>
                                            <option value="Display">Display</option>
                                            <option value="Add-on">Add-on</option>
                                            <option value="Warrany">Warrany</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1">
                                        <select class="form-control text-muted" id="room">
                                            <option value="Room" selected disabled>Room</option>
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="C">C</option>
                                            <option value="D">D</option>
                                            <option value="E">E</option>
                                            <option value="F">F</option>
                                            <option value="G">G</option>
                                            <option value="H">H</option>
                                            <option value="I">I</option>
                                            <option value="J">J</option>
                                            <option value="K">K</option>
                                            <option value="L">L</option>
                                            <option value="M">M</option>
                                            <option value="N">N</option>
                                            <option value="O">O</option>
                                            <option value="P">P</option>
                                            <option value="Q">Q</option>
                                            <option value="R">R</option>
                                            <option value="S">S</option>
                                            <option value="T">T</option>
                                            <option value="U">U</option>
                                            <option value="V">V</option>
                                            <option value="W">W</option>
                                            <option value="X">X</option>
                                            <option value="Y">Y</option>
                                            <option value="Z">Z</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1">
                                        <select class="form-control text-muted" id="sequence">
                                            <option value="Sequence" selected disabled>Sequence</option>
                                            <?php
                                            for($i = 1; $i <= 99; $i++) {
                                                $i < 10 ? $prefix = "0" : $prefix = null;

                                                echo "<option value='$i'>{$prefix}$i</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2 text-md-right">
                                        Ship Zone??
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="separator">Job Globals</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12"><input type="text" class="form-control" id="delivery-notes" placeholder="Delivery Notes"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="pricing_group">
                                            <select class="form-control text-muted" id="dd-specie">
                                                <option value="Specie" selected disabled>Specie</option>
                                                <optgroup label="Alder">
                                                    <option value="Alder - Standard">Standard</option>
                                                    <option value="Alder - Rustic">Rustic</option>
                                                    <option value="Alder - Veneer">Veneer</option>
                                                </optgroup>
                                                <optgroup label="Cherry">
                                                    <option value="Cherry - Premium">Premium</option>
                                                    <option value="Cherry - Standard">Standard</option>
                                                    <option value="Cherry - Rustic">Rustic</option>
                                                    <option value="Cherry - Veneer">Veneer</option>
                                                </optgroup>
                                                <optgroup label="Hickory/Pecan">
                                                    <option value="Hickory/Pecan - Premium">Premium</option>
                                                    <option value="Hickory/Pecan - Standard">Standard</option>
                                                    <option value="Hickory/Pecan - Rustic">Rustic</option>
                                                    <option value="Hickory/Pecan - Veneer">Veneer</option>
                                                </optgroup>
                                                <optgroup label="Maple">
                                                    <option value="Maple - Premium">Premium</option>
                                                    <option value="Maple - Standard">Standard</option>
                                                    <option value="Maple - Rustic">Rustic</option>
                                                    <option value="Maple - Veneer">Veneer</option>
                                                    <option value="Maple - Paint Grade">Paint Grade</option>
                                                </optgroup>
                                                <optgroup label="Black Walnut">
                                                    <option value="Black Walnut - Premium">Premium</option>
                                                    <option value="Black Walnut - Standard">Standard</option>
                                                    <option value="Black Walnut - Rustic*">Rustic*</option>
                                                    <option value="Black Walnut - Veneer">Veneer</option>
                                                </optgroup>
                                                <optgroup label="Other">
                                                    <option value="Barnwood - Rustic*">Barnwood - Rustic*</option>
                                                    <option value="UV Clear - Veneer">UV Clear - Veneer</option>
                                                    <option value="ThermoFoil">ThermoFoil</option>
                                                    <option value="Other/Custom">Other/Custom</option>
                                                    <option value="N/A">N/A</option>
                                                </optgroup>
                                            </select>

                                            <select class="form-control text-muted" id="dd-door-design">
                                                <option value="Door Design" selected disabled>Door Design</option>
                                                <optgroup label="Standard">
                                                    <option value="CRP10">CRP10</option>
                                                    <option value="TW10">TW10</option>
                                                    <option value="CRP10102">CRP10102</option>
                                                    <option value="CRP10751MT">CRP10751MT</option>
                                                    <option value="Madison">Madison</option>
                                                </optgroup>
                                                <optgroup label="Applied Trim">
                                                    <option value="CRP10161">CRP10161</option>
                                                    <option value="CRP10772">CRP10772</option>
                                                    <option value="Hancock">Hancock</option>
                                                    <option value="CRP10797">CRP10797</option>
                                                </optgroup>
                                                <optgroup label="Arched">
                                                    <option value="CRP30">CRP30</option>
                                                </optgroup>
                                                <optgroup label="Mitered">
                                                    <option value="CRP1420">CRP1420</option>
                                                </optgroup>
                                                <optgroup label="Slab (Door Grain/Profile, Drawer Grain)">
                                                    <option value="Hardwood Slab">Hardwood Slab</option>
                                                    <option value="Astoria Slab - Veneered MDF (3 mm)">Astoria Slab - Veneered MDF (3 mm)</option>
                                                    <option value="Savoy Slab - Veneered MDF (1 mm)">Savoy Slab - Veneered MDF (1 mm)</option>
                                                    <option value="Closet Slab - Mixed">Closet Slab - Mixed</option>
                                                </optgroup>
                                                <optgroup label="Traditions Barnwood & Rustic Black">
                                                    <option value="5 Piece Barnwood">5 Piece Barnwood</option>
                                                    <option value="Board & Batten">Board & Batten</option>
                                                </optgroup>
                                                <optgroup label="Non-standard">
                                                    <option value="Conestoga">Conestoga</option>
                                                    <option value="Northern Contours">Northern Contours</option>
                                                    <option value="Richelieu">Richelieu</option>
                                                    <option value="Walzcraft">Walzcraft</option>
                                                    <option value="SMCM Custom">SMCM Custom</option>
                                                </optgroup>
                                                <optgroup label="Other">
                                                    <option value="N/A">N/A</option>
                                                </optgroup>
                                            </select>

                                            <select class="form-control text-muted" id="dd-styles-rails">
                                                <option value="Styles/Rails" selected disabled>Styles/Rails</option>
                                                <option value="2-5/16">2-5/16"</option>
                                                <option value="2-3/4">2-3/4"</option>
                                                <option value="3">3"</option>
                                                <option value="Design Specific">Design Specific</option>
                                                <option value="NA">N/A</option>
                                            </select>

                                            Panel Raises

                                            <select class="form-control text-muted" id="dd-door-panel">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">

                        </div>
                        <div class="tab-pane fade" id="line-item" role="tabpanel" aria-labelledby="line-item-tab">

                        </div>
                    </div>
                </div><!-- End Page -->
            </div>
        </div>
    </div>
</div>

<script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

<script>
    $("#datepicker").datepicker({
        orientation: "top auto",
        autoclose: true
    });

    $("select").on("change", function() {
        $(this).removeClass("text-muted");
    });
</script>