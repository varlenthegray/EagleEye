<?php
require ("../includes/header_start.php");

echo ($dbconn->query("CREATE TABLE p_orders (id INT PRIMARY KEY AUTO_INCREMENT,room_id INT,name VARCHAR(100),account_id INT,type VARCHAR(30),status VARCHAR(30),ip VARCHAR(50),deposit BOOLEAN,payment BOOLEAN,created INT);")) ? "Orders table created. Please import data.<br />" : "<b>Error</b> with creating orders table.<br />";
echo ($dbconn->query("CREATE TABLE p_order_line (id INT PRIMARY KEY AUTO_INCREMENT,order_id INT,cabinet_num DECIMAL,custom_height DECIMAL,custom_width DECIMAL,custom_depth DECIMAL,hinge VARCHAR(10),finish CHAR,qty INT,const_method VARCHAR(50),description VARCHAR(255),assembly_id INT,part_id INT,comments VARCHAR(255));CREATE INDEX p_order_line_index0 ON p_order_line (assembly_id, part_id);")) ? "Order line table created. Please import data.<br />" : "<b>Error</b> with creating order line table.<br />";
echo ($dbconn->query("CREATE INDEX p_order_line__index1 ON p_order_line (order_id);")) ? "Created order line order ID index.<br />" : "<b>Error</b> with creating order line order ID index.<br />";
echo ($dbconn->query("ALTER TABLE p_orders ADD CONSTRAINT p_orders___fk0 FOREIGN KEY (id) REFERENCES p_order_line (order_id) ON DELETE CASCADE;")) ? "Created FK p_orders.id > p_order_line.order_id.<br />" : "<b>Error</b> with creating FK p_orders.id > p_order_line.order_id.<br />";
echo ($dbconn->query("CREATE TABLE p_refAssembly (id INT PRIMARY KEY AUTO_INCREMENT,sku VARCHAR(30),width DECIMAL,height DECIMAL,depth DECIMAL,dxf VARCHAR(100),type VARCHAR(20),created INT,last_updated INT);")) ? "Created refAssembly table.<br />" : "<b>Error</b> with creating refAssembly table.<br />";


echo "<h1>Database prepared.</h1>";