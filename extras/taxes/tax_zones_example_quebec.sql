# $Id: tax_zones_example_quebec.sql,v 1.1 2001/08/04 12:33:21 mbradley Exp $
#
# The Exchange Project Database Model for Preview Release 2.2
#
# NOTE: * Please make any modifications to this file by hand!
#       * DO NOT use a mysqldump created file for new changes!
#       * Please take note of the table structure, and use this
#         structure as a standard for future modifications!
#       * To see the 'diff'erence between MySQL databases, use
#         the mysqldiff perl script located in the extras
#         directory of the 'catalog' module.

# tax zones
# separated from zones because we would like to have the option of:
# 1. Tax for a particular Country/state combination as normal
# 2. Tax for a whole country or group of counties in one zone (i.e. European Union)

DELETE FROM geo_zones;
DELETE FROM zones_to_geo_zones;
DELETE FROM tax_rates;

INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,date_added) 
       VALUES (1,"Canada","Canadian Federal Tax Zone",now());
INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,date_added) 
       VALUES (2,"Quebec","Quebec Local Tax Zone",now());

INSERT INTO zones_to_geo_zones VALUES (1,38,0,1,now(),now());
INSERT INTO zones_to_geo_zones VALUES (2,38,76,2,now(),now());

INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added)
       VALUES (1, 1, 1, 1, 7.0, 'Canada 7%', now(), now());
INSERT INTO tax_rates ( tax_rates_id, tax_zone_id, tax_class_id, tax_priority,tax_rate, tax_description, last_modified, date_added)
       VALUES (2, 2, 1, 2, 7.5, 'Quebec 7.5%', now(), now());
