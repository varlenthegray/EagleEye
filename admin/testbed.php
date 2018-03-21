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
            <img src="/assets/images/sample_display.jpg" width="778" height="800" border="0" usemap="#sampleMap" />

            <map name="sampleMap">
              <area shape="rect" coords="145,40,210,90" href="#articwhite" />
              <area shape="rect" coords="215,40,280,90" href="#designerwhite" />
              <area shape="rect" coords="285,40,350,90" href="#frostywhite" />
              <area shape="rect" coords="355,40,425,90" href="#alabaster" />
              <area shape="rect" coords="425,40,495,90" href="#antiquewhite" />
              <area shape="rect" coords="495,40,565,90" href="#crystalwhite" />
              <area shape="rect" coords="565,40,635,90" href="#chesapeake" />
              <area shape="rect" coords="75,120,140,170" href="#heron" />
              <area shape="rect" coords="145,120,210,170" href="#hearthstonegrey" />
              <area shape="rect" coords="215,120,280,170" href="#cadetgrey" />
              <area shape="rect" coords="285,120,350,170" href="#metrogrey" />
              <area shape="rect" coords="425,120,495,170" href="#baldrock" />
              <area shape="rect" coords="495,120,565,170" href="#cliffs" />
              <area shape="rect" coords="565,120,635,170" href="#sand" />
              <area shape="rect" coords="635,120,705,170" href="#river" />
              <area shape="rect" coords="145,190,210,240" href="#roja" />
              <area shape="rect" coords="215,190,280,240" href="#butter" />
              <area shape="rect" coords="285,190,350,240" href="#taupe" />
              <area shape="rect" coords="355,190,425,240" href="#sage" />
              <area shape="rect" coords="425,190,495,240" href="#marina" />
              <area shape="rect" coords="495,190,565,240" href="#regentblue" />
              <area shape="rect" coords="565,190,635,240" href="#urbanbronze" />
              <area shape="rect" coords="635,190,705,240" href="#ebony" />
              <area shape="rect" coords="5,330,70,375" href="#a_harvestgold" />
              <area shape="rect" coords="70,325,140,375" href="#a_ginger" />
              <area shape="rect" coords="145,325,210,375" href="#a_honey" />
              <area shape="rect" coords="215,325,280,375" href="#a_chestnut" />
              <area shape="rect" coords="285,324,350,374" href="#a_cordovan" />
              <area shape="rect" coords="355,325,425,375" href="#a_bordeaux" />
              <area shape="rect" coords="495,325,565,375" href="#a_alpine" />
              <area shape="rect" coords="565,325,635,375" href="#a_nickel" />
              <area shape="rect" coords="635,325,705,375" href="#a_driftwood" />
              <area shape="rect" coords="710,325,775,375" href="#a_nitefall" />
              <area shape="rect" coords="5,395,70,445" href="#c_harvestgold" />
              <area shape="rect" coords="70,395,140,445" href="#c_ginger" />
              <area shape="rect" coords="145,395,210,445" href="#c_honey" />
              <area shape="rect" coords="215,395,280,445" href="#c_chestnut" />
              <area shape="rect" coords="285,395,350,445" href="#c_cordovan" />
              <area shape="rect" coords="355,395,425,445" href="#c_bordeaux" />
              <area shape="rect" coords="495,395,565,445" href="#c_alpine" />
              <area shape="rect" coords="565,395,635,445" href="#c_nickel" />
              <area shape="rect" coords="635,395,705,445" href="#c_driftwood" />
              <area shape="rect" coords="710,395,775,445" href="#c_nitefall" />
              <area shape="rect" coords="0,460,70,510" href="#m_harvestgold" />
              <area shape="rect" coords="70,460,140,510" href="#m_ginger" />
              <area shape="rect" coords="145,460,210,510" href="#m_honey" />
              <area shape="rect" coords="215,460,280,510" href="#m_chestnut" />
              <area shape="rect" coords="285,460,350,510" href="#m_cordovan" />
              <area shape="rect" coords="355,460,425,510" href="#m_bordeaux" />
              <area shape="rect" coords="495,460,565,510" href="#m_alpine" />
              <area shape="rect" coords="565,460,635,510" href="#m_nickel" />
              <area shape="rect" coords="635,460,705,510" href="#m_driftwood" />
              <area shape="rect" coords="710,460,775,510" href="#m_nitefall" />
              <area shape="rect" coords="2,597,72,647" href="#a_natural" />
              <area shape="rect" coords="72,597,142,647" href="#a_nutmeg" />
              <area shape="rect" coords="142,597,212,647" href="#a_portabella" />
              <area shape="rect" coords="212,597,282,647" href="#a_autumn" />
              <area shape="rect" coords="282,597,352,647" href="#a_colonial" />
              <area shape="rect" coords="352,597,422,647" href="#a_saddle" />
              <area shape="rect" coords="427,597,492,647" href="#a_cocoa" />
              <area shape="rect" coords="497,597,567,647" href="#a_pecan" />
              <area shape="rect" coords="567,597,637,647" href="#a_darkroast" />
              <area shape="rect" coords="637,597,707,647" href="#a_espresso" />
              <area shape="rect" coords="2,667,72,717" href="#c_natural" />
              <area shape="rect" coords="72,667,142,717" href="#c_nutmeg" />
              <area shape="rect" coords="142,667,212,717" href="#c_portabella" />
              <area shape="rect" coords="212,667,282,717" href="#c_autumn" />
              <area shape="rect" coords="282,667,352,717" href="#c_colonial" />
              <area shape="rect" coords="352,667,422,717" href="#c_saddle" />
              <area shape="rect" coords="427,667,492,717" href="#c_cocoa" />
              <area shape="rect" coords="497,667,562,717" href="#c_pecan" />
              <area shape="rect" coords="567,667,632,717" href="#c_darkroast" />
              <area shape="rect" coords="637,667,707,717" href="#c_espresso" />
              <area shape="rect" coords="2,732,72,782" href="#m_natural" />
              <area shape="rect" coords="72,732,142,782" href="#m_nutmeg" />
              <area shape="rect" coords="142,732,212,782" href="#m_portabella" />
              <area shape="rect" coords="212,732,282,782" href="#m_autumn" />
              <area shape="rect" coords="282,732,352,782" href="#m_colonial" />
              <area shape="rect" coords="352,732,422,782" href="#m_saddle" />
              <area shape="rect" coords="427,732,492,782" href="#m_cocoa" />
              <area shape="rect" coords="497,732,562,782" href="#m_pecan" />
              <area shape="rect" coords="567,732,632,782" href="#m_darkroast" />
              <area shape="rect" coords="637,732,707,782" href="#m_espresso" />
            </map>
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