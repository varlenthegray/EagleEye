/**
 * Created by Ben on 3/28/2017.
 */
function generateTab(searchCounter) {
    var searchTabsLength = $("#searchTabs li").length - 1;

    $("#search" + searchTabsLength + "_li").after('<li class="nav-item" id="search' + searchCounter +'_li">\
    <a class="nav-link" searchid="' + searchCounter + '" id="searchTab' + searchCounter +'" data-toggle="tab" href="#search' + searchCounter +'" role="tab" aria-controls="search' + searchCounter +'" aria-expanded="true">Search ' + searchCounter +'</a>\
</li>');

    $("#searchTabContent").append('<div role="tabpanel" class="tab-pane fade" id="search' + searchCounter +'" aria-labelledby="search' + searchCounter +'">\
    <div id="search_accordion' + searchCounter +'">\
        <h3>Customer</h3>\
        <div class="pad-lr-12">\
            <div class="row">\
                <div class="col-md-12" style="padding-bottom: 10px;">\
                    <label class="c-input c-checkbox">\
                        <input type="checkbox" name="cu_project_status' + searchCounter +'" value="Quote" checked>\
                        <span class="c-indicator"></span>\
                        Quote\
                    </label>\
                    <label class="c-input c-checkbox">\
                        <input type="checkbox" name="cu_project_status' + searchCounter +'" value="Production" checked>\
                        <span class="c-indicator"></span>\
                        Production\
                    </label>\
                    <label class="c-input c-checkbox">\
                        <input type="checkbox" name="cu_project_status' + searchCounter +'" value="Closed">\
                        <span class="c-indicator"></span>\
                        Closed\
                    </label>\
                </div>\
            </div>\
\
            <div class="row">\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="SO #" id="cu_sales_order_num' + searchCounter +'" name="cu_sales_order_num' + searchCounter +'" />\
                </div>\
\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Project" id="cu_project_name' + searchCounter +'" name="cu_project_name' + searchCounter +'" />\
                </div>\
\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Dealer/Contractor" id="cu_dealer_contractor' + searchCounter +'" name="cu_dealer_contractor' + searchCounter +'" />\
                </div>\
\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Project Manager" id="cu_project_manager' + searchCounter +'" name="cu_project_manager' + searchCounter +'" />\
                </div>\
            </div>\
        </div>\
\
        <h3>Vendor</h3>\
        <div class="pad-lr-12">\
            <div class="row">\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Sales Order #" id="vn_sales_order_num' + searchCounter +'" name="vn_sales_order_num' + searchCounter +'" />\
                </div>\
\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Project Name" id="vn_project_name' + searchCounter +'" name="vn_project_name' + searchCounter +'" />\
                </div>\
            </div>\
\
            <div class="row">\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Vendor" id="vn_vendor' + searchCounter +'" name="vn_vendor' + searchCounter +'" />\
                </div>\
\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Acknowledgement #" id="vn_ack_number' + searchCounter +'" name="vn_ack_number' + searchCounter +'" />\
                </div>\
\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Invoice Number" id="vn_invoice_num' + searchCounter +'" name="vn_invoice_num' + searchCounter +'" />\
                </div>\
\
                <div class="col-md-3 pad-lr-4">\
                    <input class="form-control" type="text" placeholder="Date Range" id="vn_date_range' + searchCounter +'" name="vn_date_range' + searchCounter +'" />\
                </div>\
            </div>\
        </div>\
\
        <h3>Inventory</h3>\
        <div class="pad-lr-12">\
            <div class="col-md-3 pad-lr-4">\
                <input class="form-control" type="text" placeholder="Sales Order #" id="inv_sales_order_num' + searchCounter +'" name="inv_sales_order_num' + searchCounter +'" />\
            </div>\
\
            <div class="col-md-3 pad-lr-4">\
                <input class="form-control" type="text" placeholder="Description" id="inv_description' + searchCounter +'" name="inv_description' + searchCounter +'" />\
            </div>\
\
            <div class="col-md-3 pad-lr-4">\
                <input class="form-control" type="text" placeholder="Part #" id="inv_part_num' + searchCounter +'" name="inv_part_num' + searchCounter +'" />\
            </div>\
\
            <div class="col-md-3 pad-lr-4">\
                <input class="form-control" type="text" placeholder="Date Range" id="inv_date_range' + searchCounter +'" name="inv_date_range' + searchCounter +'" />\
            </div>\
        </div>\
    </div>\
</div>');

    $("#search_accordion" + searchCounter).accordion();

    return searchCounter + 1;
}