TRUNCATE TABLE `mc_gmap_content`;
DROP TABLE `mc_gmap_content`;
TRUNCATE TABLE `mc_gmap`;
DROP TABLE `mc_gmap`;
TRUNCATE TABLE `mc_gmap_address_content`;
DROP TABLE `mc_gmap_address_content`;
TRUNCATE TABLE `mc_gmap_address`;
DROP TABLE `mc_gmap_address`;
TRUNCATE TABLE `mc_gmap_config`;
DROP TABLE `mc_gmap_config`;

DELETE FROM `mc_config_img` WHERE `module_img` = 'plugins' AND `attribute_img` = 'gmap';

DELETE FROM `mc_admin_access` WHERE `id_module` IN (
    SELECT `id_module` FROM `mc_module` as m WHERE m.name = 'gmap'
);