$("body")
    .on("click", "#btn_add_acct", function() {
        $.post('/ondemand/shopfloor/new_customer.php', function(data) {
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

        if($("input[name='cu_type']:checked").val() === 'retail') {
            cuData = $("#add_retail_customer").serialize();
        } else {
            cuData = $("#add_distributor_cc").serialize();
        }

        $.post("/ondemand/shopfloor/job_actions.php?action=add_customer&" + cuData, {new_so_num: $("#new_so_num").val()}, function(data) {
            $("body").append(data);

            $("#modalAddCustomer").modal('hide');
        });
    });