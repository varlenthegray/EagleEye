<?php
require ("../includes/header_start.php");

outputPHPErrs();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully functional ERP designed to manage cabinetry and automation.">
    <meta name="author" content="Stone Mountain Cabinetry & Millwork">

    <!-- App Favicon -->
    <link rel="shortcut icon" href="/assets/images/favicon.ico">

    <!-- App title -->
    <title><?php echo TAB_TEXT; ?></title>

    <!-- JQuery & JQuery UI -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/includes/js/jquery-ui.min.js"></script>
    <link href="/includes/css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>

    <!-- Global JS functions -->
    <script src="/includes/js/functions.js?v=<?php echo VERSION; ?>"></script>
    <link href="https://fonts.googleapis.com/css?family=Patua+One" rel="stylesheet">

    <!-- App CSS -->
    <link href="/assets/css/style.min.css?v=<?php echo VERSION; ?>" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <!-- Modernizr js -->
    <script src="/assets/js/modernizr.min.js"></script>

    <!-- Datatables -->
    <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.2.0/css/rowReorder.dataTables.min.css"/>

    <!-- Fancytree -->
    <link rel="stylesheet" type="text/css" href="/assets/plugins/fancytree/skin-win8-n/ui.fancytree.css"/>

    <!-- Date Picker -->
    <link href="/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

    <!-- Alert Windows -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">

    <link rel="stylesheet" type="text/css" href="/assets/plugins/jquery.steps/demo/css/jquery.steps.css">

    <?php
    $server = explode(".", $_SERVER['HTTP_HOST']);

    if($server[0] === 'dev') {
        echo "<style>body, html, .account-pages, #topnav .topbar-main, .footer {background-color: #750909 !important; }</style>";
    } else {
        echo "<script>$.fn.dataTable.ext.errMode = 'throw';</script>";
    }

    if(stristr($_SERVER["REQUEST_URI"],  'inset_sizing.php')) {
        echo '<link href="/assets/css/inset_sizing.css" rel="stylesheet">';
    }
    ?>
</head>

<body>
    <div class="wrapper">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-box">
                        <div id="contact_tree">
                            <input type="text" id="treeFilter" placeholder="Search..." />

                            <ul>
                                <?php
                                $contype_qry = $dbconn->query("SELECT * FROM contact_types ORDER BY description ASC;");

                                if($contype_qry->num_rows > 0) {
                                    while($contype = $contype_qry->fetch_assoc()) {
                                        $children = null;

                                        $personal_filter = ($contype['description'] === 'Personal') ? "AND created_by = {$_SESSION['userInfo']['id']}" : null;

                                        $company_qry = $dbconn->query("SELECT DISTINCT(company_name) FROM contact WHERE type = '{$contype['id']}' $personal_filter ORDER BY company_name ASC");

                                        if($company_qry->num_rows > 0) {
                                            $children = "<ul>";

                                            while($company = $company_qry->fetch_assoc()) {
                                                $subchild_qry = $dbconn->query("SELECT * FROM contact WHERE company_name = '{$company['company_name']}' AND type = '{$contype['id']}' $personal_filter ORDER BY first_name, last_name ASC");

                                                if($subchild_qry->num_rows > 0) {
                                                    $subchildren = "<ul>";

                                                    while($subchild = $subchild_qry->fetch_assoc()) {
                                                        $subchildren .= "<li id='{$subchild['id']}' data-icon='fa fa-user'>{$subchild['first_name']} {$subchild['last_name']}</li>";
                                                    }

                                                    $subchildren .= "</ul>";
                                                }

                                                $company_name = (empty($company['company_name'])) ? "<em>Individuals</em>" : $company['company_name'];


                                                $children .= "<li class='folder' data-icon='fa fa-group'>$company_name $subchildren</li>";
                                            }

                                            $children .= "</ul>";
                                        }

                                        echo "<li class='folder'>{$contype['description']} $children</li>";
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer modal -->
    <div id="modalEditContact" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEditContactLabel" aria-hidden="true">
        <!-- Inserted via AJAX -->
    </div>
    <!-- /.modal -->

    <script>
        $(function() {
            $("#contact_tree").fancytree({
                extensions: ["filter"],
                filter: {
                    counter: false,
                    mode: "hide",
                    autoExpand: true,
                    fuzzy: true
                },
                autoScroll: true,
                activate: function(e, data) {
                    var node = data.node;

                    if(!node.isFolder()) {
                        $.post("/html/add_contact.php?action=edit", {id: node.key}, function(data) {
                            $("#modalEditContact").html(data).modal('show');
                        });
                    }
                },
                beforeSelect: function(e, data) {
                    if(data.node.isFolder()) {
                        return false;
                    }
                }
            });
        });

        // filters the view on keyup
        $("body")
            .on("keyup", "#treeFilter", function() {
                // grab this value and filter it down to the node needed
                $("#contact_tree").fancytree("getTree").filterNodes($(this).val());
            })
            .on("change", "#contact_type", function() {
                if($(this).find("option:selected").text() === 'Dealer') {
                    $("#dealer_code").show();
                } else {
                    $("#dealer_code").hide();
                }
            })
        ;
    </script>

    <!-- jQuery  -->
    <script src="/assets/js/tether.min.js"></script><!-- Tether for Bootstrap -->
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/waves.js"></script>
    <script src="/assets/js/jquery.nicescroll.js"></script>

    <!-- Toastr setup -->
    <script src="/assets/plugins/toastr/toastr.min.js"></script>
    <link href="/assets/plugins/toastr/toastr.min.css" rel="stylesheet" type="text/css"/>

    <!-- Datatables -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables/vfs_fonts.js"></script>
    <script src="/assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables/responsive.bootstrap4.min.js"></script>

    <!-- Moment.js for Timekeeping -->
    <script src="/assets/plugins/moment/moment.js"></script>

    <!-- Alert Windows -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

    <!-- Mask -->
    <script src="/assets/plugins/jquery.mask.min.js"></script>

    <!-- Counter Up  -->
    <script src="/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
    <script src="/assets/plugins/counterup/jquery.counterup.min.js"></script>

    <!-- App js -->
    <script src="/assets/js/jquery.core.js"></script>
    <script src="/assets/js/jquery.app.js"></script>

    <!-- Tinysort -->
    <script type="text/javascript" src="/assets/plugins/tinysort/tinysort.min.js"></script>

    <!-- Input Masking -->
    <script type="text/javascript" src="/assets/plugins/jquery.mask.min.js"></script>

    <!-- Datepicker -->
    <script src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <!-- JScroll -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>

    <!-- Math, fractions and more -->
    <script src="/assets/plugins/math.min.js"></script>

    <!-- Fancytree -->
    <script src="/assets/plugins/fancytree/jquery.fancytree.js"></script>
    <script src="/assets/plugins/fancytree/jquery.fancytree.js"></script>
    <script src="/assets/plugins/fancytree/jquery.fancytree.filter.js"></script>

    <!-- Unsaved Changes -->
    <script src="/assets/js/unsaved_alert.js?v=<?php echo VERSION; ?>"></script>

    <!--Form Wizard-->
    <script src="/assets/plugins/jquery.steps/build/jquery.steps.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/assets/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
</body>
</html>
<?php
$dbconn->close();
?>