<?php
require ("../includes/header_start.php");

echo ($dbconn->query("ALTER TABLE log_cron CHANGE name `desc` TEXT;")) ? "Successfully altered Cron Table.<br />" : "Error altering Cron Table.<br />";
echo ($dbconn->query("CREATE TABLE dealers (id INT PRIMARY KEY AUTO_INCREMENT,dealer_id VARCHAR(10),contact VARCHAR(150),dealer_name VARCHAR(150),physical_address VARCHAR(150),physical_city VARCHAR(50),physical_state VARCHAR(2),physical_zip VARCHAR(10),shipping_address VARCHAR(150),shipping_city VARCHAR(50),shipping_state VARCHAR(2),shipping_zip VARCHAR(10),phone VARCHAR(15),
    email VARCHAR(150),
    multiplier DOUBLE,
    ship_zone CHAR,
    sales_rep_number INT(5));")) ? "Successfully created Dealers table.<br />" : "Error creating Cron Table.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD iteration DOUBLE DEFAULT .01 NULL")) ? "Successful with alter Rooms table ADD iteration.<br />" : "Error with alter Rooms table ADD iteration.<br />";
echo ($dbconn->query("INSERT INTO `dealers` (`id`, `dealer_id`, `contact`, `dealer_name`, `physical_address`, `physical_city`, `physical_state`, `physical_zip`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zip`, `phone`, `email`, `multiplier`, `ship_zone`, `sales_rep_number`) VALUES
(1, 'A01a', 'Robert Grieves', 'Stone Mountain Cabinetry', '309 S. Country Club Rd', 'Brevard', 'NC', '', '309 S. Country Club Rd', 'Brevard', 'NC', '', '828.966.9000', 'orders@smcm.us', 0, 'A', 1),
(2, 'A01b', 'Brent Chapman', 'Stone Mountain Cabinetry', '309 S. Country Club Rd', 'Brevard', 'NC', '', '309 S. Country Club Rd', 'Brevard', 'NC', '', '828.966.9000', 'orders@smcm.us', 0, 'A', 2),
(3, 'A02a', 'Dave Parks', 'Distinctive Cabinetry', '3990 NC Hwy 105', 'Banner Elk', 'NC', '', '125 Dick Watson Rd', 'Deep Gap', 'NC', '', '828.898.9633', 'dave@design105.com', 0.391, 'B', 3),
(4, 'A02b', 'Leah Parks', 'Distinctive Cabinetry', '3990 NC Hwy 105', 'Banner Elk', 'NC', '', '125 Dick Watson Rd', 'Deep Gap', 'NC', '', '828.963.0236', 'leah@design105.com', 0.391, 'B', 3),
(5, 'A02c', 'John Parks', 'Distinctive Cabinetry', '3990 NC Hwy 105', 'Banner Elk', 'NC', '', '125 Dick Watson Rd', 'Deep Gap', 'NC', '', '828.963.0235', 'john@design105.com', 0.391, 'B', 3),
(6, 'A03a', 'Hugh Bannon', 'CHW Cabinetry', '1819 Two Notch Rd.', 'Lexington', 'SC', '', '1819 Two Notch Rd.', 'Lexington', 'SC', '', '803.520.6837', 'hughbrannon@gmail.com', 0.407, 'C', 3),
(7, 'A03b', 'Franki hall', 'CHW Cabinetry', '1819 Two Notch Rd.', 'Lexington', 'SC', '', '1819 Two Notch Rd.', 'Lexington', 'SC', '', '803.520.6837', 'frankihall61@gmail.com', 0.407, 'C', 3),
(8, 'A04a', 'Adriana Herrera', 'Cucina Design Studio', '903 Churchill Dr.', 'Gastonia', 'NC', '', '903 Churchill Dr.', 'Gastonia', 'NC', '', '704.689.1465', '', 0.407, 'B', 3),
(9, 'A05a', 'Lisa McCamy', 'Aurora Design Studio', '145 West Sycamore Ave', 'Wake Forest', 'NC', '', '145 West Sycamore Ave', 'Wake Forest', 'NC', '', '919.270.6549', 'auroradesignstudio@gmail.com', 0.407, 'C', 4),
(10, 'A06a', 'Leslie Cohen', 'Leslie Cohen Design', '3316 Tall Tree Place', 'Raleigh', 'NC', '', '3316 Tall Tree Place', 'Raleigh', 'NC', '', '619.995.9162', 'lesliecohendesign@gmail.com', 0.407, 'C', 3),
(11, 'A07a', 'Eric Freirich', 'Landmark Woodworking', '24 Penland Cove', 'Black Mountain', 'NC', '', '24 Penland Cove', 'Black Mountain', 'NC', '', '828.669.8221', '', 0.407, 'A', 3),
(12, 'A08a', 'Michael Rutherford', 'All House Designs', '2726 Ashley Ferry Rd', 'Charleston', 'SC', '', '2726 Ashley Ferry Rd', 'Charleston', 'SC', '', '843.990.0930', 'rthr4d1@gmail.com', 0.407, 'C', 5),
(13, 'A09a', 'Bob Dewan', 'Dewan Cabinetry', '27 Timber Marsh', 'Hilton Head', 'SC', '', '27 Timber Marsh', 'Hilton Head', 'SC', '', '843.263.8630', 'dewancabinetry@gmail.com', 0.407, 'D', 5),
(14, 'A10a', 'Sandy', 'Wall to Wall Cabinetry', '7217 Ogden Business Lane Unit 112', 'Wilmington', 'NC', '', '7217 Ogden Business Lane Unit 112', 'Wilmington', 'NC', '', '910-686-4877', '', 0.407, 'D', 4),
(15, 'A13a', 'Charles McCamy', 'Criterion Sales', '4540 Forest Cove Drive Belmont, NC', 'Belmont', 'NC', '', '4540 Forest Cove Drive Belmont, NC', 'Belmont', 'NC', '', '336.263.3941', 'charlesmccamy@gmail.com', 0.407, 'A', 3),
(16, 'A13b', 'Cam McCamy', 'Criterion Sales', '145 W Sycamore Ave ', 'Wake Forest', 'NC', '', '145 W Sycamore Ave ', 'Wake Forest', 'NC', '', '919.270.0966', 'citerioncam@gmail.com', 0.407, 'C', 4),
(17, 'A13c', 'Phil Cowart', 'Criterion Sales', '1838 Brogdon St. ', 'Savannah', 'GA', '', '1838 Brogdon St. ', 'Savannah', 'GA', '', '912.660.4091', 'philcowart@gmail.com', 0.407, 'C', 5),
(18, 'A13d', 'Christopher Sink', 'Criterion Sales', '', '', '', '', '', '', '', '', '704.634.6817', 'chrissink@gmail.com', 0.407, 'C', 6),
(19, 'A14', 'State Interiors', 'State Interiors ', '200 A Trade Street', 'Greer', 'SC', '29651', '200 A Trade Street', 'Greer', 'SC', '29651', '864.655.7486', 'thom@e3cabinets.com', 0.407, 'B', 1);")) ? "Successful with insert information into Dealers.<br />" : "Error with insert information into Dealers.<br />";
echo ($dbconn->query("ALTER TABLE `dealers` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20")) ? "Successful with alter table Dealers modify Auto Increment.<br />" : "Error with alter table Dealers modify Auto Increment.<br />";
echo ($dbconn->query("ALTER TABLE customer MODIFY job_type VARCHAR(4)")) ? "Successful with alter table Customer modify job type to varchar 4.<br />" : "Error with alter table Customer modify job type to varchar 4.<br />";
echo ($dbconn->query("UPDATE customer SET job_type = '$'")) ? "Successful with update customer set job type to $.<br />" : "Error with update customer set job type to $.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD salesperson VARCHAR(75) NULL")) ? "Successful with alter table customer add salesperson.<br />" : "Error with alter table customer add salesperson.<br />";
echo ($dbconn->query("ALTER TABLE user ADD hourly BOOLEAN DEFAULT 1 NULL")) ? "Successful with alter table user add hourly as true.<br />" : "Error with alter table user add hourly as true.<br />";
echo ($dbconn->query("UPDATE user SET hourly = 0 WHERE id = 1 OR id = 7 OR id = 8 OR id = 9 OR id = 14")) ? "Successful with updating salary employees to salary.<br />" : "Error with updating salary employees to salary.<br />";
echo ($dbconn->query("CREATE TABLE vin_schema (id INT PRIMARY KEY AUTO_INCREMENT,segment VARCHAR(50),`key` VARCHAR(50),value VARCHAR(100))")) ? "Successful with create table Vin Schema.<br />" : "Error with create table Vin Schema.<br />";
echo ($dbconn->query("INSERT INTO `vin_schema` (`id`, `segment`, `key`, `value`) VALUES
(1, 'product_type', 'Cabinets', 'C'),
(2, 'product_type', 'Closet', 'L'),
(3, 'product_type', 'Sample', 'S'),
(4, 'product_type', 'Display', 'D'),
(5, 'product_type', 'Addon', 'A'),
(6, 'product_type', 'Warranty', 'W'),
(7, 'days_to_ship', 'Green', 'G'),
(8, 'days_to_ship', 'Yellow', 'Y'),
(9, 'days_to_ship', 'Orange', 'N'),
(10, 'days_to_ship', 'Red', 'R')")) ? "Successful with insert into Vin Schema.<br />" : "Error with insert into Vin Schema.<br />";
echo ($dbconn->query("ALTER TABLE `vin_schema` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11")) ? "Successful with alter Vin Schema auto-increment number.<br />" : "Error with alter Vin Schema auto-increment number.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD order_status CHAR NULL")) ? "Successful with alter table rooms add order status.<br />" : "Error with alter table rooms add order status.<br />";
echo ($dbconn->query("ALTER TABLE customer DROP job_type")) ? "Successful with alter table customer drop job type.<br />" : "Error with alter table customer drop job type.<br />";
echo ($dbconn->query("UPDATE rooms SET order_status = '$'")) ? "Successful with update rooms set order status to $.<br />" : "Error with update rooms set order status to $.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD days_to_ship VARCHAR(10) DEFAULT 'Green' NULL")) ? "Successful with alter table rooms add days to ship with value Green.<br />" : "Error with alter table rooms add days to ship with value Green.<br />";
echo ($dbconn->query("ALTER TABLE operations CHANGE department bracket VARCHAR(50) NOT NULL;")) ? "Successful with alter table operations change department to bracket varchar 50.<br />" : "Error with alter table operations change department to bracket varchar 50.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD shipping_bracket TINYINT(4) NULL;")) ? "Successful with alter table rooms add shipping bracket.<br />" : "Error with alter table rooms add shipping bracket.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD shipping_bracket_priority TINYINT(4) NULL;")) ? "Successful with alter table rooms add shipping bracket priority.<br />" : "Error with alter table rooms add shipping bracket priority.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD shipping_published BOOLEAN DEFAULT 0 NULL;")) ? "Successful with alter table rooms add shipping published.<br />" : "Error with alter table rooms add shipping published.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD install_bracket TINYINT(4) NULL;")) ? "Successful with alter table rooms add install bracket.<br />" : "Error with alter table rooms add install bracket.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD install_bracket_priority TINYINT(4) NULL;")) ? "Successful with alter table rooms add install bracket priority.<br />" : "Error with alter table rooms add install bracket priority.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD install_bracket_published BOOLEAN DEFAULT 0 NULL;")) ? "Successful with alter table rooms add install bracket published.<br />" : "Error with alter table rooms add install bracket published.<br />";
echo ($dbconn->query("ALTER TABLE rooms CHANGE box_bracket main_bracket TINYINT(4) NOT NULL;")) ? "Successful with alter table rooms change box bracket to main bracket.<br />" : "Error with alter table rooms change box bracket to main bracket.<br />";
echo ($dbconn->query("ALTER TABLE rooms CHANGE box_bracket_priority main_bracket_priority TINYINT(4) NOT NULL;")) ? "Successful with alter table rooms change box bracket priority to main bracket priority.<br />" : "Error with alter table rooms change box bracket priority to main bracket priority.<br />";
echo ($dbconn->query("ALTER TABLE rooms CHANGE box_published main_published TINYINT(1) DEFAULT '0';")) ? "Successful with alter table rooms change box bracket published to main bracket published.<br />" : "Error with alter table rooms change box bracket published to main bracket published.<br />";
echo ($dbconn->query("UPDATE rooms SET install_bracket_priority = 4, shipping_bracket_priority = 4, install_bracket = 15, shipping_bracket = 66;")) ? "Successful with update rooms set install & shipping priority and ops.<br />" : "Error with update rooms set install & shipping priority and ops.<br />";
echo ($dbconn->query("TRUNCATE operations;")) ? "Successful with altering table rooms add delivery date.<br />" : "Error with altering table rooms add delivery date.<br />";
echo ($dbconn->query("INSERT INTO `operations` (`id`, `op_id`, `bracket`, `job_title`, `icon`, `color`, `responsible_dept`, `sub_tasks`, `always_visible`) VALUES
  (1, '110', 'Sales', 'Initial Meeting', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Project Manager', NULL, 0),
  (3, '120', 'Sales', 'Basic Design', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Design', NULL, 0),
  (4, '125', 'Sales', 'Review with Sales Lead', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Project Manager', NULL, 0),
  (5, '130', 'Sales', 'Revisions with Customer #1', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Project Manager', NULL, 0),
  (6, '135', 'Sales', 'Pricing', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Design', NULL, 0),
  (7, '140', 'Sales', 'Customer Review #2', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Project Manager', NULL, 0),
  (8, '145', 'Sales', 'Drawings Approved', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Design', NULL, 0),
  (9, '150', 'Sales', 'Contract Signed', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Project Manager', NULL, 0),
  (10, '155', 'Sales', 'Site Layout', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Project Manager', NULL, 0),
  (13, '170', 'Sales', 'Prep for Shop', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Design', NULL, 0),
  (14, '175', 'Sales', 'DOS Review', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Sales Administrator', NULL, 0),
  (15, '705', 'Installation', 'Manage Install', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Global', NULL, 0),
  (18, '305', 'Pre-Production', 'Review & Release to Shop Foreman', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Sales Administrator', NULL, 0),
  (19, '330', 'Pre-Production', 'Engineering  ', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Engineering', NULL, 0),
  (21, '335', 'Pre-Production', 'Engineering Inspection', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Shop Foreman', NULL, 0),
  (26, '345', 'Pre-Production', 'Making Job Folders', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Shop Foreman', NULL, 0),
  (27, '350', 'Pre-Production', 'Pick List for Assembly', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Production Administrator', NULL, 0),
  (28, '355', 'Pre-Production', 'Place Orders', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Production Administrator', NULL, 0),
  (29, '360', 'Pre-Production', 'Receive Material', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Production Administrator', NULL, 0),
  (31, '210', 'Sample', 'Sample Order', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Production Administrator', NULL, 0),
  (32, '215', 'Sample', 'Sample Received', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Production Administrator', NULL, 0),
  (33, '220', 'Sample', 'Sample Finish', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Finishing', NULL, 0),
  (34, '225', 'Sample', 'Finish Inspection', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Shop Foreman', NULL, 0),
  (37, '240', 'Sample', 'Sample Assembly', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Custom', NULL, 0),
  (38, '410', 'Drawer & Doors', 'Door Quote', 'zmdi zmdi-inbox', 'rgba(255,160,0,.75)', 'Production Administrator', NULL, 0),
  (39, '415', 'Drawer & Doors', 'Quote Review', 'zmdi zmdi-inbox', 'rgba(255,160,0,.75)', 'Engineering', NULL, 0),
  (42, '427', 'Drawer & Doors', 'Door Order', 'zmdi zmdi-inbox', 'rgba(255,160,0,.75)', 'Production Administrator', NULL, 0),
  (43, '430', 'Drawer & Doors', 'Door Pick Up', 'zmdi zmdi-inbox', 'rgba(255,160,0,.75)', 'Production Administrator', NULL, 0),
  (44, '435', 'Drawer & Doors', 'Doors Received', 'zmdi zmdi-inbox', 'rgba(255,160,0,.75)', 'Production Administrator', NULL, 0),
  (45, '605', 'Custom', 'Pick List for Custom', 'zmdi zmdi-puzzle-piece', 'rgba(215,83,83,.75)', 'Custom', NULL, 0),
  (46, '610', 'Custom', 'Custom', 'zmdi zmdi-puzzle-piece', 'rgba(215,83,83,.75)', 'Custom', NULL, 0),
  (47, '615', 'Custom', 'Custom Inspection', 'zmdi zmdi-puzzle-piece', 'rgba(215,83,83,.75)', 'Shop Foreman', NULL, 0),
  (50, '505', 'Main', 'Pick List for Box', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Production Administrator', NULL, 0),
  (51, '510', 'Main', 'Cut Plywood Panels to Size', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Box', NULL, 0),
  (52, '515', 'Main', 'Edge Band', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Box', NULL, 0),
  (53, '520', 'Main', 'Bore / Dado / Pocket Hole', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Box', NULL, 0),
  (54, '525', 'Main', 'Box Inspection', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Shop Foreman', NULL, 0),
  (58, '540', 'Main', 'Finishing', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Finishing', NULL, 0),
  (59, '545', 'Main', 'Finishing Inspection', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Shop Foreman', NULL, 0),
  (62, '560', 'Main', 'Assembly', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Assembly', NULL, 0),
  (63, '565', 'Main', 'Assembly Inspection', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Shop Foreman', NULL, 0),
  (66, '805', 'Shipping', 'Load All Parts', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Assembly', NULL, 0),
  (67, '810', 'Shipping', 'Load Inspection', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Shop Foreman', NULL, 0),
  (68, '815', 'Shipping', 'Delivery', 'zmdi zmdi-window-maximize', 'rgba(200,155,255,.75)', 'Global', NULL, 0),
  (69, '000', 'Box', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Box', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Inventory (10 Mins)\",\"Dust Collection (15 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (70, '310', 'Pre-Production', 'Acknowledge Inspection', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Shop Foreman', NULL, 0),
  (71, '000', 'Engineering', 'Cabinet Vision', 'zmdi zmdi-assignment', 'rgba(255,240,20,.75)', 'Engineering', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Inventory (15 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (73, '218', 'Sample', 'Sample Custom', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Custom', NULL, 0),
  (74, '000', 'Engineering', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Engineering', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Inventory (15 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (75, '000', 'Assembly', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Assembly', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Inventory (10 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (76, '000', 'Custom', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Custom', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Inventory (15 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (78, '000', 'Production Administrator', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Production Administrator', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Dust Collection (15 Mins\",\"Inventory (10 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (79, '000', 'Sales', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Sales', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Inventory (15 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (80, '000', 'Finishing', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Finishing', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Inventory (20 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (82, '000', 'Shop Foreman', 'Non-Billable', 'zmdi zmdi-money-off', 'rgba(183,20,20,.75)', 'Shop Foreman', '[\"Morning Task (10 Mins)\",\"LEAN (30 Mins)\",\"Dust Collection (15 Mins\",\"Inventory (10 Mins)\",\"Meeting (10 Mins)\",\"Training (30 Mins)\",\"Equipment Maint (15 Mins)\"]', 1),
  (83, '205', 'Sample', 'Sample Door Request', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Project Manager', NULL, 0),
  (84, '245', 'Sample', 'Sample Door Approved', 'zmdi zmdi-time-countdown', 'rgba(0,255,200,.75)', 'Project Manager', NULL, 0),
  (85, '302', 'Pre-Production', 'Acknowledgement', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Engineering', NULL, 0),
  (86, '315', 'Pre-Production', 'Review & Release to Design/Distribution', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Sales Administrator', NULL, 0),
  (87, '320', 'Pre-Production', 'Acknowledgement Acceptance', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Project Manager', NULL, 0),
  (88, '325', 'Pre-Production', 'Review & Release to Engineering', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Sales Administrator', NULL, 0),
  (89, '340', 'Pre-Production', 'Review & Release to Main/Custom', 'zmdi zmdi-comment-image', 'rgba(255,215,65,.75)', 'Production Administrator', NULL, 0),
  (90, '440', 'Drawer & Doors', 'Doors Checked In & Inspected', 'zmdi zmdi-inbox', 'rgba(255,160,0,.75)', 'Production Administrator', NULL, 0),
  (91, '570', 'Main', 'Payment Received', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Accounting', NULL, 0),
  (92, '710', 'Installation', 'DPL', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'Project Manager', NULL, 0),
  (93, '198', 'Sales', 'Bracket Completed', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'N/A', NULL, 0),
  (94, '298', 'Sample', 'Bracket Completed', 'zmdi zmdi-time-countdown', 'rgba(91,255,120,.75)', 'N/A', NULL, 0),
  (95, '398', 'Pre-Production', 'Bracket Completed', 'zmdi zmdi-comment-image', 'rgba(91,255,120,.75)', 'N/A', NULL, 0),
  (96, '498', 'Door & Drawer', 'Bracket Completed', 'zmdi zmdi-inbox', 'rgba(91,255,120,.75)', 'N/A', NULL, 0),
  (97, '598', 'Main', 'Bracket Completed', 'zmdi zmdi-window-maximize', 'rgba(91,255,120,.75)', 'N/A', NULL, 0),
  (98, '698', 'Custom', 'Bracket Completed', 'zmdi zmdi-puzzle-piece', 'rgba(91,255,120,.75)', 'N/A', NULL, 0),
  (99, '798', 'Installation', 'Bracket Completed', 'zmdi zmdi-money', 'rgba(91,255,120,.75)', 'N/A', NULL, 0),
  (100, '898', 'Shipping', 'Bracket Completed', 'zmdi zmdi-window-maximize', 'rgba(91,255,120,.75)', 'N/A', NULL, 0);")) ? "Successful with insert into operations ALL operations.<br />" : "Error with insert into operations ALL operations.<br />";
echo ($dbconn->query("ALTER TABLE rooms ADD delivery_date INT(20) NULL;")) ? "Successful with altering table rooms add delivery date.<br />" : "Error with altering table rooms add delivery date.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_1 VARCHAR(100) NULL;")) ? "Successful with adding contact 1.<br />" : "Error with adding contact 1.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_1_cell VARCHAR(20) NULL;")) ? "Successful with adding contact 1 cell.<br />" : "Error with adding contact 1 cell.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_1_business_ph VARCHAR(20) NULL;")) ? "Successful with adding contact 1 business phone.<br />" : "Error with adding contact 1 business phone.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_1_email VARCHAR(200) NULL;")) ? "Successful with adding contact 1 email.<br />" : "Error with adding contact 1 email.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_2 VARCHAR(100) NULL;")) ? "Successful with adding contact 2.<br />" : "Error with adding contact 2.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_2_cell VARCHAR(20) NULL;")) ? "Successful with adding contact 2 cell.<br />" : "Error with adding contact 2 cell.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_2_business_ph VARCHAR(20) NULL;")) ? "Successful with adding contact 2 business phone.<br />" : "Error with adding contact 2 business phone.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD contact_2_email VARCHAR(200) NULL;")) ? "Successful with adding contact 2 email.<br />" : "Error with adding contact 2 email.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD phys_addr VARCHAR(255) NULL;")) ? "Successful with adding physical address.<br />" : "Error with adding physical address.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD phys_city VARCHAR(100) NULL;")) ? "Successful with adding physical city.<br />" : "Error with adding physical city.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD phys_state VARCHAR(2) NULL;")) ? "Successful with adding physical state.<br />" : "Error with adding physical state.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD phys_zip VARCHAR(12) NULL;")) ? "Successful with adding physical zip.<br />" : "Error with adding physical zip.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD global_email VARCHAR(200) NULL;")) ? "Successful with adding global email.<br />" : "Error with adding global email.<br />";
echo ($dbconn->query("ALTER TABLE customer ADD global_cell VARCHAR(20) NULL;")) ? "Successful with adding global cell.<br />" : "Error with adding global cell.<br />";


echo "<h1>Database prepared.</h1>";