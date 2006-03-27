DELETE FROM geo_zones;
DELETE FROM zones_to_geo_zones ;
DELETE FROM tax_rates;

INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,date_added) 
       VALUES (1,"Florida","Florida local sales tax zone",now());

INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) 
       VALUES (1,223,18,1,now()); # USA/Florida

INSERT INTO tax_rates ( tax_rates_id, tax_zone_id, tax_class_id, tax_rate, tax_description, last_modified, date_added)
       VALUES (1, 1, 1, 7.0, 'FL TAX 7.0%', now(), now());
