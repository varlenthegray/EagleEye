<?php
require("../includes/header_start.php");

$dbconn->query("ALTER TABLE devsmc.operations ADD always_visible BOOLEAN DEFAULT 0 NULL");
$dbconn->query("ALTER TABLE devsmc.op_queue ADD subtask VARCHAR(50)");
$dbconn->query("ALTER TABLE devsmc.user ADD default_queue VARCHAR(30) NULL");
$dbconn->query("ALTER TABLE devsmc.rooms DROP assigned_bracket");

$dbconn->query("UPDATE user SET department = '[\"Sales\",\"Production Administrator\",\"Engineering\",\"Finishing\",\"Custom\",\"Assembly\",\"Box\",\"Shop Foreman\",\"Global\"]' WHERE id BETWEEN 1 AND 10 OR id = 14 OR id BETWEEN 18 AND 19");
$dbconn->query("UPDATE user SET department = '[\"Production Administrator\",\"Finishing\",\"Custom\",\"Assembly\",\"Box\",\"Shop Foreman\",\"Global\"]' WHERE id BETWEEN 11 AND 13 OR id = 15");
$dbconn->query("UPDATE user SET default_queue = 'Sales' WHERE id BETWEEN 1 AND 8 OR ID = 19");
$dbconn->query("UPDATE user SET default_queue = 'Production Administrator' WHERE id = 9");
$dbconn->query("UPDATE user SET default_queue = 'Engineering' WHERE id = 10");
$dbconn->query("UPDATE user SET default_queue = 'Shop Foreman' WHERE id = 14");
$dbconn->query("UPDATE user SET default_queue = 'Engineering' WHERE id = 18");
$dbconn->query("UPDATE user SET default_queue = 'Sales' WHERE id = 19");

$dbconn->query("ALTER TABLE devsmc.debug_log RENAME TO devsmc.log_debug");
$dbconn->query("ALTER TABLE devsmc.log RENAME TO devsmc.log_error");
$dbconn->query("CREATE TABLE devsmc.log_cron (id INT(11) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(100), time INT(15))");
$dbconn->query("ALTER TABLE devsmc.user ADD auto_clock BOOLEAN DEFAULT TRUE NULL");

// Misc operations cleanup, thanks PHPMYADMIN!
echo "Must go in and restore operation table separately!";

echo "<br /><br /><h4>ALL DONE!</h4>";