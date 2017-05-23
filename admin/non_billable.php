<?php
require ("../includes/header_start.php");

$dbconn->query("ALTER TABLE devsmc.operations ADD sub_tasks VARCHAR(250) NULL");

$box_tasks = ["Morning Task (10 Mins)", "LEAN (30 Mins)", "Inventory (10 Mins)", "Dust Collection (15 Mins)", "Meeting (10 Mins)", "Training (30 Mins)", "Equipment Maint (15 Mins)"];
$box = json_encode($box_tasks);

$custom_tasks = ["Morning Task (10 Mins)", "LEAN (30 Mins)", "Inventory (15 Mins)", "Meeting (10 Mins)", "Training (30 Mins)", "Equipment Maint (15 Mins)"];
$custom = json_encode($custom_tasks);

$finishing_tasks = ["Morning Task (10 Mins)", "LEAN (30 Mins)", "Inventory (20 Mins)", "Meeting (10 Mins)", "Training (30 Mins)", "Equipment Maint (15 Mins)"];
$finishing = json_encode($finishing_tasks);

$assembly_tasks = ["Morning Task (10 Mins)", "LEAN (30 Mins)", "Inventory (10 Mins)", "Meeting (10 Mins)", "Training (30 Mins)", "Equipment Maint (15 Mins)"];
$assembly = json_encode($assembly_tasks);

$sf_tasks = ["Morning Task (10 Mins)", "LEAN (30 Mins)", "Dust Collection (15 Mins", "Inventory (10 Mins)", "Meeting (10 Mins)", "Training (30 Mins)", "Equipment Maint (15 Mins)"];
$sf = json_encode($sf_tasks);

$engineering_tasks = ["Morning Task (10 Mins)", "LEAN (30 Mins)", "Inventory (15 Mins)", "Meeting (10 Mins)", "Training (30 Mins)", "Equipment Maint (15 Mins)"];
$engineering = json_encode($engineering_tasks);

$sales_tasks = ["Morning Task (10 Mins)", "LEAN (30 Mins)", "Inventory (15 Mins)", "Meeting (10 Mins)", "Training (30 Mins)", "Equipment Maint (15 Mins)"];
$sales = json_encode($sales_tasks);

$dbconn->query("UPDATE operations SET sub_tasks = '$box' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Box'");
$dbconn->query("UPDATE operations SET sub_tasks = '$custom' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Custom'");
$dbconn->query("UPDATE operations SET sub_tasks = '$finishing' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Finishing'");
$dbconn->query("UPDATE operations SET sub_tasks = '$assembly' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Assembly'");
$dbconn->query("UPDATE operations SET sub_tasks = '$sf' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Shop Foreman'");
$dbconn->query("UPDATE operations SET sub_tasks = '$engineering' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Engineering'");
$dbconn->query("UPDATE operations SET sub_tasks = '$sales' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Sales'");
$dbconn->query("UPDATE operations SET sub_tasks = '$sf' WHERE job_title = 'Non-Billable' AND responsible_dept = 'Production Administrator'");