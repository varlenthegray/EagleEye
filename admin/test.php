<?php
if($_GET['action'] === 'json') {
//build data array
    $data = [
        [id=>1, name=>"Billy Bob", age=>"12", gender=>"male", height=>1, col=>"red", dob=>"", cheese=>1],
        [id=>2, name=>"Mary May", age=>"1", gender=>"female", height=>2, col=>"blue", dob=>"14/05/1982", cheese=>true],
        [id=>3, name=>"Christine Lobowski", age=>"42", height=>0, col=>"green", dob=>"22/05/1982", cheese=>"true"],
        [id=>4, name=>"Brendon Philips", age=>"125", gender=>"male", height=>1, col=>"orange", dob=>"01/08/1980"],
        [id=>5, name=>"Margret Marmajuke", age=>"16", gender=>"female", height=>5, col=>"yellow", dob=>"31/01/1999"],
    ];

//return JSON formatted data
    echo(json_encode($data));

    die();
}

require ("../includes/header_start.php");
require ("../includes/header_end.php");
?>

<!-- tabulator -->
<script type="text/javascript" src="/assets/plugins/tabulator/dist/js/tabulator.min.js"></script>
<link href="/assets/plugins/tabulator/dist/css/tabulator_simple.min.css" rel="stylesheet" type="text/css"/>

<!-- Search results box -->
<div class="row">
    <div class="col-md-12">
        <div class="card-box" id="search_results_card">
            <div class="row">
                <div class="col-md-12">
                    <button type="button" id="ajax-trigger" class="btn btn-primary waves-effect waves-light w-xs">Load AJAX</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12" id="search_results_table"></div>
            </div>

            <div class="row">
                <div class="col-md-12 text-md-right">
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Collapse</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Print</button>
                    <button type="button" class="btn btn-primary waves-effect waves-light w-xs">Export</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End search results box -->

<script>
    //Build Tabulator
    $("#search_results_table").tabulator({
        height:"311px",
        fitColumns:true,
        placeholder:"No Data Set",
        columns:[
            {title:"Name", field:"name", sorter:"string", width:200},
            {title:"Progress", field:"progress", sorter:"number", align:"right", formatter:"progress"},
            {title:"Gender", field:"gender", sorter:"string"},
            {title:"Rating", field:"rating", formatter:"star", align:"center", width:100},
            {title:"Favourite Color", field:"col", sorter:"string", sortable:false},
            {title:"Date Of Birth", field:"dob", sorter:"date", align:"center"},
            {title:"Driver", field:"car", align:"center", formatter:"tickCross", sorter:"boolean"},
        ],
    });

    //trigger AJAX load on "Load Data via AJAX" button click
    $("#ajax-trigger").click(function(){
        $("#search_results_table").tabulator("setData", "test.php?action=json");
    });
</script>

<?php
require '../includes/footer_start.php';
require '../includes/footer_end.php';
?>