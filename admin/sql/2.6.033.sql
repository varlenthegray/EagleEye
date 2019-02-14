SET default_storage_engine = INNODB;

create table contact_to_contact
(
  id int auto_increment,
  created_by int null,
  contact_from int null,
  contact_to int null,
  associated_as varchar(50) null,
  created_on int null,
  constraint contact_to_contact_pk primary key (id),
  constraint contact_to_contact_contact_id_fk foreign key (contact_from) references contact (id),
  constraint contact_to_contact_contact_id_fk_2 foreign key (contact_to) references contact (id),
  constraint contact_to_contact_user_id_fk foreign key (created_by) references user (id)
);

create index `contact_to_contact_contact_1_index` on contact_to_contact (contact_from);
create index `contact_to_contact_contact_2_index` on contact_to_contact (contact_to);
create index `contact_to_contact_created_by_index`on contact_to_contact (created_by);

alter table contact add billing_type varchar(20) null after line_2;
alter table contact add multiplier double null after billing_type;
alter table contact add payment_processor varchar(30) null after multiplier;

UPDATE contact c LEFT JOIN contact_company cc ON c.company_name = cc.name SET c.multiplier = cc.multiplier, c.billing_type = cc.billing_type, c.payment_processor = cc.payment_processor;

alter table sales_order add contact_id int null after company_id;
create index sales_order_contact_id_index on sales_order (contact_id);
alter table sales_order add constraint sales_order_contact_id_fk foreign key (contact_id) references contact (id);

UPDATE contact_company t SET t.`name` = 'Advanced Cabinetry' WHERE t.`id` = 18;
UPDATE contact_company t SET t.`name` = 'Jason Lorenz Construction' WHERE t.`id` = 22;

INSERT INTO contact (`created_by`, `dealer_id`, `company_name`, `first_name`, `last_name`, `title`, `email`, `cell`, `line_2_desc`, `line_2`, `billing_type`, `multiplier`, `payment_processor`, `shipping_first_name`, `shipping_last_name`, `shipping_addr`, `shipping_city`, `shipping_state`, `shipping_zip`, `billing_first_name`, `billing_last_name`, `billing_addr`, `billing_city`, `billing_state`, `billing_zip`, `creation`) VALUES (1, null, '', 'Joe', 'Acker', null, '', '', null, null, null, null, null, null, null, 'Straus Park', 'Brevard', 'NC', '28712', null, null, null, null, 'NC', null, '1521423497');

UPDATE sales_order so LEFT JOIN contact_company cc on so.company_id = cc.id LEFT JOIN contact c ON cc.name = c.company_name SET contact_id = c.id;

UPDATE sales_order SET contact_id = 169 WHERE id = 316;

create table contact_to_sales_order
(
  id int auto_increment,
  created_by int null,
  contact_id int null,
  sales_order_id int null,
  associated_as varchar(50) null,
  created_on int null,
  constraint contact_to_sales_order_pk primary key (id),
  constraint contact_to_sales_order_contact_id_fk foreign key (contact_id) references contact (id),
  constraint contact_to_sales_order_sales_order_id_fk foreign key (sales_order_id) references sales_order (id),
  constraint contact_to_sales_order_user_id_fk foreign key (created_by) references user (id)
);

create index contact_to_sales_order_contact_id_index on contact_to_sales_order (contact_id);
create index contact_to_sales_order_created_by_index on contact_to_sales_order (created_by);
create index contact_to_sales_order_sales_order_id_index on contact_to_sales_order (sales_order_id);

INSERT INTO contact_to_sales_order (created_by, contact_id, sales_order_id, created_on, associated_as)
SELECT ca.assigned_by, ca.contact_id, ca.type_id, ca.created_on, ca.associated_as FROM contact_associations ca WHERE type = 'project';

CREATE TABLE `contact_add_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `option` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `value` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `contact_add_options_field_index` (`field`)
) AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `contact_add_options` (`id`, `field`, `option`, `value`, `enabled`) VALUES
(1, 'cust_status', 'Active', 'active', 1),
(2, 'cust_status', 'Inactive', 'inactive', 1),
(3, 'cust_status', 'Credit Hold', 'credit_hold', 1),
(4, 'cust_status', 'Lead', 'lead', 1),
(5, 'cust_group', 'OEM', 'oem', 1),
(6, 'cust_group', 'Wholesale', 'wholesale', 1),
(7, 'cust_residential_delivery', 'Yes', '1', 1),
(8, 'cust_residential_delivery', 'No', '0', 1),
(9, 'cust_ship_method', 'UPS', 'ups', 1),
(10, 'cust_ship_method', 'FedEx', 'fedex', 1),
(11, 'cust_ship_method', 'LTL', 'ltl', 1),
(12, 'cust_ship_method', 'Pickup', 'pickup', 1),
(13, 'cust_ship_billto', 'Internal Account', 'internal', 1),
(14, 'cust_ship_billto', 'Customer Account', 'customer', 1),
(15, 'cust_billing_type', 'Distribution - 50/50', 'dist_50_50', 1),
(16, 'cust_billing_type', 'Distribution - 100', 'dist_100', 1),
(17, 'cust_billing_type', 'Retail - 50/40/10', 'retail_50_40_10', 1),
(18, 'cust_billing_type', 'Wholesale - 50/50', 'wholesale_50_50', 1),
(19, 'cust_payment_method', 'Stripe', 'stripe', 1),
(20, 'cust_payment_method', 'Square', 'square', 1),
(21, 'cust_payment_method', 'Bank', 'bank', 1),
(22, 'cust_payment_terms', 'Net 30', 'net_30', 1),
(23, 'cust_payment_terms', 'Net 15', 'net_15', 1),
(24, 'cust_payment_terms', 'Net 10', 'net_10', 1),
(25, 'cust_payment_terms', 'Net 7', 'net_7', 1),
(26, 'cust_payment_terms', 'COD', 'cod', 1),
(27, 'cust_payment_terms', 'Cash', 'cash', 1),
(28, 'cust_fed_exempt_reason', 'Out of State', 'oos', 1),
(29, 'cust_fed_exempt_reason', 'Tax Exempt', 'tax_exempt', 1),
(30, 'vend_status', 'Active', 'active', 1),
(31, 'vend_status', 'Inactive', 'inactive', 1),
(32, 'vend_receive_method', 'UPS', 'ups', 1),
(33, 'vend_receive_method', 'FedEx', 'fedex', 1),
(34, 'vend_receive_method', 'LTL', 'ltl', 1),
(35, 'vend_receive_method', 'Dropoff', 'dropoff', 1),
(36, 'vend_receive_method', 'Pickup', 'pickup', 1),
(37, 'vend_payment_terms', 'Net 30', 'net_30', 1),
(38, 'vend_payment_terms', 'Net 15', 'net_15', 1),
(39, 'vend_payment_terms', 'Net 10', 'net_10', 1),
(40, 'vend_payment_terms', 'Net 7', 'net_7', 1),
(41, 'vend_payment_terms', 'COD', 'cod', 1),
(42, 'vend_payment_terms', 'Cash', 'cash', 1),
(43, 'emp_status', 'Active', 'active', 1),
(44, 'emp_status', 'Inactive', 'inactive', 1),
(45, 'emp_shift', '1st Shift - 7:00AM-4:45PM', '1', 1),
(46, 'emp_shift', '1st Split - 11:00AM-4:00PM', '2', 1),
(47, 'emp_facility', 'Main Street', 'main', 1),
(48, 'emp_facility', '7th Ave', '7th_ave', 1);

alter table contact add unique_id varchar(20) null after dealer_id;
alter table contact add address varchar(200) null after title;
alter table contact add city varchar(100) null after address;
alter table contact add state varchar(2) null after city;
alter table contact add zip varchar(15) null after state;
alter table contact add country varchar(2) null after zip;
alter table contact add primary_phone varchar(30) null after email;
alter table contact add secondary_phone varchar(30) null after primary_phone;
alter table contact add other_phone varchar(30) null after secondary_phone;
alter table contact add fax varchar(30) null after other_phone;

UPDATE contact SET primary_phone = cell, secondary_phone = line_2, address = shipping_addr, city = shipping_city, state = shipping_state, zip = shipping_zip, country = 'US';

UPDATE contact c LEFT JOIN dealers d on c.dealer_id = d.id SET c.unique_id = d.dealer_id WHERE c.dealer_id IS NOT NULL;

alter table contact drop column line_2_desc;
alter table contact drop column line_2;
alter table contact drop column payment_processor;
alter table contact drop column cell;
alter table contact drop column shipping_first_name;
alter table contact drop column shipping_last_name;
alter table contact drop column shipping_addr;
alter table contact drop column shipping_city;
alter table contact drop column shipping_state;
alter table contact drop column shipping_zip;
alter table contact drop column billing_first_name;
alter table contact drop column billing_last_name;
alter table contact drop column billing_addr;
alter table contact drop column billing_city;
alter table contact drop column billing_state;
alter table contact drop column billing_zip;

drop index contact_cell_index on contact;

UPDATE `contact` t SET t.`unique_id` = 'A01z' WHERE t.`id` = 166;
UPDATE contact SET unique_id = IF(company_name != '', CONCAT('R', id), CONCAT('N', id)) WHERE unique_id IS NULL;

alter table contact add constraint contact_pk unique (unique_id);

create table contact_customer
(
  id int auto_increment,
  contact_id int not null,
  created_by int not null,
  established_date int null,
  status int null,
  `group` int null,
  max_commission double null,
  salesman_commission_id int null,
  salesman_commission_percent double null,
  referral_commission_id int null,
  referral_commission_percent double null,
  sales_group_commission_id int null,
  sales_group_commission_percent double,
  other_commission_id int null,
  other_commission_percent double null,
  ship_method int null,
  ship_bill_to int null,
  ship_account varchar(30) null,
  residential_delivery int null,
  ship_address varchar(200) null,
  ship_city varchar(200) null,
  ship_state varchar(2) null,
  ship_zip varchar(30) null,
  ship_country varchar(2) null,
  billing_type int null,
  multiplier double null,
  payment_method int null,
  payment_terms int null,
  federal_id varchar(30) null,
  federal_exempt_reason int null,
  created int null,
  constraint contact_customer_pk primary key (id)
);

create index contact_customer_billing_type_index on contact_customer (billing_type);
create index contact_customer_federal_exempt_reason_index on contact_customer (federal_exempt_reason);
create index contact_customer_group_index on contact_customer (`group`);
create index contact_customer_payment_method_index on contact_customer (payment_method);
create index contact_customer_payment_terms_index on contact_customer (payment_terms);
create index contact_customer_residential_delivery_index on contact_customer (residential_delivery);
create index contact_customer_ship_bill_to_index on contact_customer (ship_bill_to);
create index contact_customer_ship_method_index on contact_customer (ship_method);
create index contact_customer_status_index on contact_customer (status);
create index contact_customer_contact_id_index on contact_customer (contact_id);
create index contact_customer_other_commission_id_index on contact_customer (other_commission_id);
create index contact_customer_referral_commission_id_index on contact_customer (referral_commission_id);
create index contact_customer_sales_group_commission_id_index on contact_customer (sales_group_commission_id);
create index contact_customer_salesman_commission_id_index on contact_customer (salesman_commission_id);

alter table contact_customer add constraint contact_customer_contact_add_options_id_fk foreign key (`status`) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_2 foreign key (`group`) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_3 foreign key (ship_method) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_4 foreign key (ship_bill_to) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_5 foreign key (residential_delivery) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_6 foreign key (billing_type) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_7 foreign key (payment_method) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_8 foreign key (payment_terms) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_contact_add_options_id_fk_9 foreign key (federal_exempt_reason) references contact_add_options (id);
alter table contact_customer add constraint contact_customer_user_id_fk foreign key (created_by) references user (id);
alter table contact_customer add constraint contact_customer_contact_fk foreign key (contact_id) references contact (id);

create table contact_vendor
(
  id int auto_increment,
  contact_id int null,
  created_by int null,
  established_date int null,
  status int null,
  receive_method int null,
  receive_country varchar(2) null,
  receive_address varchar(200) null,
  receive_city varchar(200) null,
  receive_state varchar(2) null,
  receive_zip varchar(30) null,
  payment_terms int null,
  federal_id varchar(30) null,
  payment_contact varchar(200) null,
  payment_country varchar(2) null,
  payment_address varchar(200) null,
  payment_city varchar(200) null,
  payment_state varchar(2) null,
  payment_zip varchar(30) null,
  payment_primary_phone varchar(30) null,
  payment_secondary_phone varchar(30) null,
  payment_other_phone varchar(30) null,
  payment_fax varchar(30) null,
  created int null,
  constraint contact_vendor_pk primary key (id),
  constraint contact_vendor_contact_add_options_id_fk foreign key (status) references contact_add_options (id),
  constraint contact_vendor_contact_add_options_id_fk_2 foreign key (receive_method) references contact_add_options (id),
  constraint contact_vendor_contact_add_options_id_fk_3 foreign key (payment_terms) references contact_add_options (id),
  constraint contact_vendor_contact_id_fk foreign key (contact_id) references contact (id),
  constraint contact_vendor_user_id_fk foreign key (created_by) references user (id)
);

create index contact_vendor_contact_id_index on contact_vendor (contact_id);
create index contact_vendor_created_by_index on contact_vendor (created_by);
create index contact_vendor_payment_terms_index on contact_vendor (payment_terms);
create index contact_vendor_receive_method_index on contact_vendor (receive_method);
create index contact_vendor_status_index on contact_vendor (status);

create table contact_employee
(
  id int auto_increment,
  contact_id int null,
  created_by int null,
  hire_date int null,
  languages varchar(200) null,
  timezone varchar(50) null,
  shift int null,
  facility int null,
  department varchar(50) null,
  employee_status int null,
  user_access int null,
  username varchar(50) null,
  pin varchar(10) null,
  password varchar(100) null,
  pay_schedule int null,
  federal_id varchar(30) null,
  personal_country varchar(2) null,
  personal_address varchar(200) null,
  personal_city varchar(200) null,
  personal_state varchar(2) null,
  personal_zip varchar(30) null,
  personal_email varchar(200) null,
  personal_phone varchar(30) null,
  personal_birthday int null,
  emergency_name varchar(200) null,
  emergency_relationship varchar(50) null,
  emergency_country varchar(2) null,
  emergency_address varchar(200) null,
  emergency_city varchar(200) null,
  emergency_state varchar(2) null,
  emergency_zip varchar(30) null,
  emergency_pri_phone varchar(30) null,
  emergency_secondary_phone varchar(30) null,
  emergency_other_phone varchar(30) null,
  emergency_email varchar(200) null,
  created int null,
  constraint contact_employee_pk primary key (id),
  constraint contact_employee_contact_add_options_id_fk foreign key (shift) references contact_add_options (id),
  constraint contact_employee_contact_add_options_id_fk_2 foreign key (facility) references contact_add_options (id),
  constraint contact_employee_contact_add_options_id_fk_3 foreign key (employee_status) references contact_add_options (id),
  constraint contact_employee_contact_add_options_id_fk_4 foreign key (pay_schedule) references contact_add_options (id),
  constraint contact_employee_contact_id_fk foreign key (contact_id) references contact (id),
  constraint contact_employee_user_id_fk foreign key (created_by) references user (id)
);

create index contact_employee_contact_id_index on contact_employee (contact_id);
create index contact_employee_created_by_index on contact_employee (created_by);
create index contact_employee_employee_status_index on contact_employee (employee_status);
create index contact_employee_facility_index on contact_employee (facility);
create index contact_employee_pay_schedule_index on contact_employee (pay_schedule);
create index contact_employee_shift_index on contact_employee (shift);

UPDATE `contact` t SET t.`first_name` = '', t.`last_name` = '' WHERE t.`id` = 160;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 145;
UPDATE `contact` t SET t.`first_name` = '', t.`last_name` = '' WHERE t.`id` = 163;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 147;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 149;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 151;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 155;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 156;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 157;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 2;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 3;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 4;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 5;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 7;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 8;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 9;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 11;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 12;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 14;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 16;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 18;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 20;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 22;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 24;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 26;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 28;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 29;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 30;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 31;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 33;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 34;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 36;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 38;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 40;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 41;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 43;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 44;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 45;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 46;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 48;
UPDATE `contact` t SET t.`company_name` = '' WHERE t.`id` = 162;
UPDATE `contact` t SET t.`company_name` = 'RT Ward Inc ', t.`first_name` = ' ', t.`last_name` = ' ' WHERE t.`id` = 84;
INSERT INTO `contact` (`created_by`, `dealer_id`, `unique_id`, `company_name`, `first_name`, `last_name`, `title`, `address`, `city`, `state`, `zip`, `country`, `email`, `primary_phone`, `secondary_phone`, `other_phone`, `fax`, `billing_type`, `multiplier`, `creation`) VALUES (9, null, 'R168', '', 'Skip', 'MacMillan', null, '', '', 'NC', '', 'US', 'skip@shippingyes.com', '803-493-3849', '', null, null, null, null, '1547126824');


# TODO: REMOVE FROM CONTACT: dealer_id, billing_type, multiplier



# TODO: get to the point where we can remove contact_company and contact_associations
# TODO: discuss renaming project to sales order