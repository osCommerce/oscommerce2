# $Id: tax_zones_example_europe_uk.sql,v 1.1 2001/08/04 12:33:21 mbradley Exp $
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

INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,date_added) VALUES (1,"European Union","EU VAT Zone",now());

INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (1,222,NULL,1,now()); #UK
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (2,81,NULL,1,now()); #Germany
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (3,73,NULL,1,now()); #France
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (4,105,NULL,1,now()); #Italy
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (5,21,NULL,1,now()); #Belgium
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (6,150,NULL,1,now()); #Holland
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (7,195,NULL,1,now()); #Spain
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (8,203,NULL,1,now()); #Sweden
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (9,72,NULL,1,now()); #Finland
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (10,57,NULL,1,now()); #Denmark
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (11,84,NULL,1,now()); #Greece
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (12,171,NULL,1,now()); #Portugal
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (13,103,NULL,1,now()); #Ireland
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (14,124,NULL,1,now()); #Luxembourg
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (15,14,NULL,1,now()); #Austria

INSERT INTO tax_rates ( tax_rates_id, tax_zone_id, tax_class_id, tax_rate, tax_description, last_modified, date_added)
       VALUES (1, 1, 1, 17.5, 'EU TAX 17.5%', now(), now());