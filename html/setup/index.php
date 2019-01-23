<input class="ignoreSaveAlert" type="text" name="ausernameidontcareabout" style="display:none;">
<input class="ignoreSaveAlert" type="password" name="apasswordidontcareabout" style="display:none;">

<div class="container">
  <div class="row">
    <div class="col-md-4">
      <table style="width:100%;margin-top:8px;" class="table table-custom-nb">
        <tr>
          <td colspan="2"><u><b>New User</b></u></td>
        </tr>
        <tr>
          <td><label for="login_name">Login Name:</label></td>
          <td><input type="text" name="login_name" class="c_input" placeholder="Login Name" id="login_name" /></td>
        </tr>
        <tr>
          <td><label for="password">Login Password:</label></td>
          <td><input type="password" name="password" class="c_input " placeholder="Password" id="password" /></td>
        </tr>
        <tr>
          <td><label for="email">Email:</label></td>
          <td><input type="email" name="email" class="c_input " placeholder="Email Address" id="email" /></td>
        </tr>
        <tr>
          <td><label for="phone">Phone:</label></td>
          <td><input type="text" name="phone" class="c_input " placeholder="Phone" id="phone" /></td>
        </tr>
        <tr>
          <td><label for="phone">Account Type:</label></td>
          <td>
            <select name="account_type" id="account_type">
              <option value="1">Super Admin</option>
              <option value="2">Admin</option>
              <option value="4">General</option>
              <option value="5">Shop Employee</option>
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="name">Name:</label></td>
          <td><input type="text" name="name" class="c_input " placeholder="Name" id="name" /></td>
        </tr>
        <tr>
          <td><label for="pin">PIN:</label></td>
          <td><input type="text" name="pin" class="c_input " placeholder="PIN code" id="pin" /></td>
        </tr>
        <tr>
          <td><label for="default_queue">Default Queue:</label></td>
          <td>
            <select name="default_queue" id="default_queue">
              <?php
              $queue_qry = $dbconn->query("SELECT DISTINCT bracket FROM operations WHERE bracket != 'Non-Billable' AND bracket != 'Special';");

              while($queue = $queue_qry->fetch_assoc()) {
                echo "<option value='{$queue['bracket']}'>{$queue['bracket']}</option>";
              }
              ?>
            </select>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>


<form action="?add_usr=true" method="post">
  <div>
    <label for="username">Username</label>
    <input type="text" name="username" id="username" />
  </div>

  <div>
    <label for="password">Password</label>
    <input type="password" name="password" id="password" />
  </div>

  <div>
    <label for="email">Email</label>
    <input type="email" name="email" id="email" />
  </div>

  <div>
    <label for="phone">Phone</label>
    + <input type="text" name="phone" id="phone" maxlength="11" />
  </div>

  <div>
    <label for="account_type">Account Type</label>
    <select name="account_type" id="account_type">
      <option value="1">Super Admin</option>
      <option value="2">Admin</option>
      <option value="4">General</option>
      <option value="5">Shop Employee</option>
    </select>
  </div>

  <div>
    <label for="name">Name</label>
    <input type="text" name="name" id="name" />
  </div>

  <div>
    <label for="pin">PIN</label>
    <input type="password" name="pin" id="pin" maxlength="4" />
  </div>

  <div>
    <label for="default_queue">Default Queue</label>
    <select name="default_queue" id="default_queue">
      <?php
      $queue_qry = $dbconn->query("SELECT DISTINCT bracket FROM operations WHERE bracket != 'Non-Billable' AND bracket != 'Special';");

      while($queue = $queue_qry->fetch_assoc()) {
        echo "<option value='{$queue['bracket']}'>{$queue['bracket']}</option>";
      }
      ?>
    </select>
  </div>

  <div>
    <label for="dealer">Dealer</label>
    <input type="checkbox" name="dealer" id="dealer" value="1" />
  </div>

  <div>
    <input type="submit" />
  </div>
</form>