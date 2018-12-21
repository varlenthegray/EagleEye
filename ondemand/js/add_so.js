$("body")
  .on("click", ".nav_add_so", function() {
    $.post('/html/new_customer.php', function(data) {
      $("#modalAddCustomer").html(data).modal('show');
    });
  })
  .on("change", "input[name='cu_type']", function() {
    var add_rc = $("#add_retail_customer");
    var add_dist = $("#add_distributor_cc");

    switch($(this).val()) {
      case 'retail':
        add_rc.show();
        add_dist.hide();

        break;
      case 'distribution':
        add_rc.hide();
        add_dist.show();

        break;
      case 'cutting':
        add_rc.hide();
        add_dist.show();

        break;
      default:
        break;
    }
  })
  .on("click", "#submit_new_customer", function() {
    var cuData;

    cuData = $("#add_retail_customer").serialize();

    $.post("/ondemand/so_actions.php?action=add_customer&" + cuData, {so_num: $("#so_num").val()}, function(data) {
      $("body").append(data);

      $("#modalAddCustomer").modal('hide');
    });

    unsaved = false;
  })
  .on("change", "#secondary_addr_chk", function() {
    $(".secondary_addr_disp").toggle();
  })
  .on("change", "#billing_addr_chk", function() {
    $(".billing_info_disp").toggle();
  })
  .on("change", "#contractor_chk", function() {
    $(".contractor_disp").toggle();
  })
;