# Find the category ID's you want, from there you simply update the category ID in the following sequences
# You can obtain the category ID by enabling the category ID display in the system and referencing the old database in the config file
# Alternatively, simply punch the ID's in - in the future it'd be nice to automatically select any related categories (right now 2443 is the parent, 2444 is a child)
SET @cat1 = 2443, @cat2 = 2444, @cat3 = 2448, @cat4 = 2452;
SELECT * FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = @cat1 OR category_id = @cat2 OR category_id = @cat3 OR category_id = @cat4;
SELECT * FROM `3erp_old_pricing`.pricing_nomenclature_details WHERE id IN (SELECT description_id FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = @cat1 OR category_id = @cat2 OR category_id = @cat3 OR category_id = @cat4);
SELECT * FROM `3erp_old_pricing`.pricing_categories WHERE id IN (SELECT category_id FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = @cat1 OR category_id = @cat2 OR category_id = @cat3 OR category_id = @cat4);
SELECT * FROM `3erp_old_pricing`.pricing_price_map WHERE nomenclature_id IN (SELECT id FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = @cat1 OR category_id = @cat2 OR category_id = @cat3 OR category_id = @cat4);

SET foreign_key_checks = 0;
# DELETE FROM `3erp_dev`.pricing_nomenclature_details WHERE id IN (SELECT id FROM `3erp_old_pricing`.pricing_nomenclature_details WHERE id IN (SELECT description_id FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = 2443 OR category_id = 2444 OR category_id = 2448 OR category_id = 2452));
# DELETE FROM `3erp_dev`.pricing_categories WHERE id IN (SELECT id FROM `3erp_old_pricing`.pricing_categories WHERE id IN (SELECT category_id FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = 2443 OR category_id = 2444 OR category_id = 2448 OR category_id = 2452) OR id = 2443);
# DELETE FROM `3erp_dev`.pricing_nomenclature WHERE id IN (SELECT id FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = 2443 OR category_id = 2444 OR category_id = 2448 OR category_id = 2452);
# DELETE FROM `3erp_dev`.pricing_price_map WHERE id IN (SELECT id FROM `3erp_old_pricing`.pricing_price_map WHERE nomenclature_id IN (SELECT id FROM `3erp_old_pricing`.pricing_nomenclature WHERE category_id = 2443 OR category_id = 2444 OR category_id = 2448 OR category_id = 2452));
set foreign_key_checks = 1;