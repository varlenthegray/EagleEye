<?php
require ("../includes/header_start.php");

echo ($dbconn->query("CREATE TABLE materials (id INT PRIMARY KEY AUTO_INCREMENT,name VARCHAR(150),description VARCHAR(250),type VARCHAR(50),uom VARCHAR(50),cost DOUBLE,markup DOUBLE,taxable BOOLEAN,thickness DOUBLE);")) ? "Materials table created. Please import data.<br />" : "<b>Error</b> with creating materials table.<br />";

echo "<h1>Database prepared.</h1>";