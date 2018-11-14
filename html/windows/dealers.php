<?php
require_once '../../includes/header_start.php';
?>

<div style="width:100%;height:100%;overflow-x:hidden;">
  <div class="row">
    <div class="col-md-12">
      <div class="card-box table-responsive">
        <table class="table table-striped table-bordered" width="100%">
          <colgroup>
            <col width="4%">
            <col width="10%">
            <col width="10%">
            <col width="10%">
            <col width="8%">
            <col width="4%">
            <col width="5%">
            <col width="10%">
            <col width="8%">
            <col width="4%">
            <col width="5%">
            <col width="7%">
            <col width="7%">
            <col width="4%">
            <col width="4%">
          </colgroup>
          <thead>
          <tr>
            <th>Dealer ID</th>
            <th>Contact</th>
            <th>Dealer Name</th>
            <th>Physical Address</th>
            <th>Physical City</th>
            <th>Physical State</th>
            <th>Physical Zip</th>
            <th>Shipping Address</th>
            <th>Shipping City</th>
            <th>Shipping State</th>
            <th>Shipping Zip</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Multiplier</th>
            <th>Account Type</th>
          </tr>
          </thead>
          <tbody>
          <?php
          $dealer_qry = $dbconn->query('SELECT * FROM dealers ORDER BY dealer_id ASC');

          while($dealer = $dealer_qry->fetch_assoc()) {
            echo '<tr>';
            echo "<td><input type='text' class='form-control dealer_input' data-col='dealer_id' data-id=\"{$dealer['id']}\" value=\"{$dealer['dealer_id']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='contact' data-id=\"{$dealer['id']}\" value=\"{$dealer['contact']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='dealer_name' data-id=\"{$dealer['id']}\" value=\"{$dealer['dealer_name']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='physical_address' data-id=\"{$dealer['id']}\" value=\"{$dealer['physical_address']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='physical_city' data-id=\"{$dealer['id']}\" value=\"{$dealer['physical_city']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='physical_state' data-id=\"{$dealer['id']}\" value=\"{$dealer['physical_state']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='physical_zip' data-id=\"{$dealer['id']}\" value=\"{$dealer['physical_zip']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='shipping_address' data-id=\"{$dealer['id']}\" value=\"{$dealer['shipping_address']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='shipping_city' data-id=\"{$dealer['id']}\" value=\"{$dealer['shipping_city']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='shipping_state' data-id=\"{$dealer['id']}\" value=\"{$dealer['shipping_state']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='shipping_zip' data-id=\"{$dealer['id']}\" value=\"{$dealer['shipping_zip']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='phone' data-id=\"{$dealer['id']}\" value=\"{$dealer['phone']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='email' data-id=\"{$dealer['id']}\" value=\"{$dealer['email']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='multiplier' data-id=\"{$dealer['id']}\" value=\"{$dealer['multiplier']}\" /></td>";
            echo "<td><input type='text' class='form-control dealer_input' data-col='account_type' data-id=\"{$dealer['id']}\" value=\"{$dealer['account_type']}\" /></td>";
            echo '</tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>