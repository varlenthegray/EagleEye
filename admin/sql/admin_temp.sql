SET default_storage_engine = INNODB;

create table batch_category
(
  id int auto_increment,
  parent_category int null,
  category_name varchar(100) null,
  enabled bool default 1 null,
  sort_order int null,
  constraint batch_category_pk primary key (id)
);

create index batch_category_parent_category_index on batch_category (parent_category);

create table batch_global (
  id int auto_increment,
  category_id int null,
  `key` varchar(15) null,
  name text null,
  markup double null,
  markup_calculator varchar(1) null,
  addl_html text null,
  `group` varchar(100) null,
  enabled bool default 1 null,
  default_option bool default 0 null,
  sort_order int null,
  constraint product_global_pk primary key (id)
);

create index product_global_category_id_index on batch_global (category_id);
alter table batch_global add constraint batch_global_batch_category_id_fk foreign key (category_id) references batch_category (id);

drop table global_upcharges;
drop table e_coversheet;
drop table admin_version_history;
drop table attachments;
drop table crm_company;

alter table sales_order drop foreign key sales_order_contact_company_id_fk;
drop index sales_order_company_id_index on sales_order;
alter table sales_order drop column company_id;

drop table contact_company;

DROP FUNCTION IF EXISTS UC_FIRST;
CREATE FUNCTION UC_FIRST(oldWord VARCHAR(255)) RETURNS VARCHAR(255)
RETURN CONCAT(UCASE(SUBSTRING(oldWord, 1, 1)),SUBSTRING(oldWord, 2));

DROP FUNCTION IF EXISTS UC_DELIMETER;
DELIMITER //
CREATE FUNCTION UC_DELIMETER(oldName VARCHAR(255), delim VARCHAR(1), trimSpaces BOOL) RETURNS VARCHAR(255)
BEGIN
  # https://www.thingy-ma-jig.co.uk/blog/30-09-2010/mysql-how-upper-case-words
  SET @oldString := oldName;
  SET @newString := "";

  tokenLoop: LOOP
    IF trimSpaces THEN SET @oldString := TRIM(BOTH " " FROM @oldString); END IF;

    SET @splitPoint := LOCATE(delim, @oldString);

    IF @splitPoint = 0 THEN
      SET @newString := CONCAT(@newString, UC_FIRST(@oldString));
      LEAVE tokenLoop;
    END IF;

    SET @newString := CONCAT(@newString, UC_FIRST(SUBSTRING(@oldString, 1, @splitPoint)));
    SET @oldString := SUBSTRING(@oldString, @splitPoint+1);
  END LOOP tokenLoop;

  RETURN @newString;
END//
DELIMITER ;

INSERT INTO batch_category (parent_category, category_name) SELECT 0, REPLACE(UC_DELIMETER(segment, '_', TRUE), '_', ' ') FROM vin_schema WHERE visible = TRUE GROUP BY segment;
ALTER TABLE batch_category AUTO_INCREMENT = 33;

INSERT INTO batch_global (category_id, `key`, name, markup, markup_calculator, addl_html, `group`, enabled)
SELECT bc.id, `key`, value, markup, markup_calculator, addl_html, `group`, visible
FROM vin_schema vs LEFT JOIN batch_category bc ON REPLACE(UC_DELIMETER(vs.segment, '_', TRUE), '_', ' ') = bc.category_name;

# order is important here, we're renaming existing categories once we've moved them... if we did this before entering into global it'd fail on some items
UPDATE `batch_category` t SET t.`category_name` = 'Finish Color' WHERE t.`id` = 12;
UPDATE `batch_category` t SET t.`category_name` = 'Style/Rail Width' WHERE t.`id` = 31;
UPDATE `batch_category` t SET t.`category_name` = 'Glaze Color' WHERE t.`id` = 15;
UPDATE `batch_category` t SET t.`category_name` = 'Enviro-finish' WHERE t.`id` = 17;
INSERT INTO `batch_category` (`parent_category`, `category_name`, `enabled`) VALUES (0, 'Overlay', DEFAULT);
INSERT INTO `batch_category` (`parent_category`, `category_name`, `enabled`) VALUES (0, 'Carcass Finish Color', DEFAULT);
INSERT INTO `batch_category` (`parent_category`, `category_name`, `enabled`) VALUES (0, 'Carcass Glaze Color', DEFAULT);
INSERT INTO `batch_category` (`parent_category`, `category_name`, `enabled`) VALUES (0, 'Carcass Glaze Technique', DEFAULT);

# <editor-fold desc="Inserting the new/copied information into Batch Global">
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (33, null, '1/2"', 0, '*', null, 'Overlay', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (33, null, '1 1/4"', 0, '*', null, 'Overlay', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (33, null, 'Non-standard', 0, '*', null, 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1cXXXX', 'Non-standard', 209.7, '+', '<input type=''text'' class=''txt_custom_input'' placeholder=''Manufacturer'' name=''custom_finish_mfg''><input type=''text'' class=''txt_custom_input'' placeholder=''Code'' name=''custom_finish_code''><input type=''text'' class=''txt_custom_input'' placeholder=''Name'' name=''custom_finish_name''>', 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8017', 'Alpine - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8058', 'Autumn - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8059', 'Bordeaux - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8060', 'Chestnut - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8061', 'Colonial - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8062', 'Cordovan - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8063', 'Dark Roast - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8064', 'Driftwood - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8065', 'Espresso - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8066', 'Ginger - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8067', 'Harvest Gold - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8068', 'Honey - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8069', 'Natural - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8038', 'Nickel - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8018', 'Nitefall - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8070', 'Nutmeg - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8045', 'Portabella - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8011', 'Saddle - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1c0000', 'N/A', 0, '*', null, 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6043', 'Antique White - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6039', 'Artic White - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6046', 'Cadet Grey - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6044', 'Chesapeake - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6042', 'Crystal White - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6021', 'Designer White - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6059', 'Ebony - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6038', 'Frosty White - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6048', 'Hearthstone Grey - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6045', 'Heron - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6060', 'Marina - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6047', 'Metro Grey - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6051', 'Regent Blue - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6053', 'Sage - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6037', 'Butter - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8004', 'Pecan - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8005', 'Cocoa - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6036', 'Bald Rock - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6035', 'Cliffs - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6049', 'River - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6058', 'Roja - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6050', 'Sand - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6052', 'Taupe - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1c????', 'TBD', 0, '*', null, 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1r0001', 'Raw', 0, '*', null, 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1t0004', 'Traditions/Barnwood - Gray (+15%)', 0, '*', null, 'Traditions/Barnwood', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1u0001', 'Unfinished', 0, '*', null, 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1f0001', 'Thermofoil', 0, '*', null, 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6057', 'Urban Bronze - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3p6075', 'Alabaster - Paint', 0, '*', null, 'Paint', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1t0003', 'Traditions/Barnwood - Sage (+10%)', 0, '*', null, 'Traditions/Barnwood', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1t0002', 'Traditions/Barnwood - White (+10%)', 0, '*', null, 'Traditions/Barnwood', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1t0001', 'Traditions/Barnwood - Natural (+10%)', 0, '*', null, 'Traditions/Barnwood', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1t0005', 'Traditions/Barnwood - Red (+10%)', 0, '*', null, 'Traditions/Barnwood', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '1t0006', 'Traditions/Barnwood - Brown (+10%)', 0, '*', null, 'Traditions/Barnwood', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (34, '3s8071', 'Washington Cherry - Stain', 0, '*', null, 'Stain', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9008', 'Caramel', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9005', 'Brown', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9009', 'Coffee', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9004', 'Chai', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9007', 'Oyster', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9006', 'Pewter', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9003', 'Sable', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g9002', 'White', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3gXXXX', 'Non-standard', 0, '*', '<input type=''text'' class=''txt_custom_input'' placeholder=''Manufacturer'' name=''custom_glaze_mfg''><input type=''text'' class=''txt_custom_input'' placeholder=''Code'' name=''custom_glaze_code''><input type=''text'' class=''txt_custom_input'' placeholder=''Name'' name=''custom_glaze_name''>', 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g0000', 'None', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3gZZZZ', 'Design Specific', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g5064', 'Vintage (3PO)', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g0001', 'Black', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (35, '3g????', 'TBD', 0, '*', null, 'Glaze', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'T1', 'Heirloom', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'T2', 'Highlites', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'T3', 'Brushed', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'TX', 'Non-standard', 0, '*', '<input type=''text'' class=''txt_custom_input'' placeholder=''Customization'' name=''custom_glaze_tech''>', 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'T5', 'Highlights & Brushed', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'T0', 'None', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'T7', 'Traditions', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'TZ', 'D.S.', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'T?', 'TBD', 0, '*', null, 'Hidden', 0);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G0', 'None', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G1', 'Pinstripes', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G2', 'Highlites', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G3', 'Brushed', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G5', 'Highlights & Brushed', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G7', 'Traditions Light', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G8', 'Traditions Heavy', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'G9', 'Heirloom', 0, '*', null, 'Glaze Technique', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'GX', 'Non-standard', 0, '*', '<input type=''text'' class=''txt_custom_input'' placeholder=''Customization'' name=''custom_glaze_tech''>', 'Other', 1);
INSERT INTO `batch_global` (`category_id`, `key`, `name`, `markup`, `markup_calculator`, `addl_html`, `group`, `enabled`) VALUES (36, 'GZ', 'Design Specific', 0, '*', null, 'Other', 1);
# </editor-fold>

UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 154;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 234;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 310;
UPDATE `batch_global` t SET t.`name` = 'Wrap up - Hardwood 5/8"' WHERE t.`id` = 113;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 267;
UPDATE `batch_global` t SET t.`name` = 'Wrap up - Hardwood 5/8" - Finished (Matches Interior)' WHERE t.`id` = 124;
UPDATE `batch_global` t SET t.`name` = 'Yellow' WHERE t.`id` = 42;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 4;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 394;
UPDATE `batch_global` t SET t.`name` = 'Red' WHERE t.`id` = 44;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 59;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 226;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 161;
UPDATE `batch_global` t SET t.`name` = 'ThermoFoil', t.`addl_html` = '<input type=''text'' placeholder=''Manufacturer'' name=''thermofoil_mfg''><input type=''text'' placeholder=''Code'' name=''thermofoil_code''><input type=''text'' placeholder=''Name'' name=''thermofoil_name''>' WHERE t.`id` = 393;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 416;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 409;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 29;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 253;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 16;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 358;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 289;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 78;
UPDATE `batch_global` t SET t.`name` = 'Non-standard' WHERE t.`id` = 244;
UPDATE `batch_global` t SET t.`name` = 'Green' WHERE t.`id` = 41;
UPDATE `batch_global` t SET t.`name` = 'Orange' WHERE t.`id` = 43;

UPDATE batch_category SET sort_order = id;

UPDATE `batch_global` t SET t.`group` = 'Hand Pull' WHERE t.`id` = 272;
UPDATE `batch_global` t SET t.`group` = 'Hand Pull' WHERE t.`id` = 271;
UPDATE `batch_global` t SET t.`group` = 'Hand Pull' WHERE t.`id` = 274;
UPDATE `batch_global` t SET t.`group` = 'Hand Pull' WHERE t.`id` = 275;
UPDATE `batch_global` t SET t.`group` = 'Hand Pull' WHERE t.`id` = 273;

alter table batch_global add show_as varchar(20) null;

UPDATE batch_global SET show_as = 'checkbox' WHERE category_id = 34 OR category_id = 12 OR category_id = 15 OR category_id = 35;
UPDATE batch_global SET show_as = 'select' WHERE category_id != 34 AND category_id != 12 AND category_id != 15 AND category_id != 35;

TRUNCATE log_debug;