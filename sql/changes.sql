ALTER TABLE `aircraft_maintenance_programs` ADD COLUMN `mp_archived` TINYINT(1) NULL DEFAULT 0 AFTER `mp_name`;

ALTER TABLE `aircraft_maintenance_programs` ADD COLUMN `mp_notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `mp_name`;
ALTER TABLE `aircraft_maintenance_programs` ADD COLUMN `mp_revision` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `mp_archived`;
ALTER TABLE `aircraft_maintenance_programs` ADD COLUMN `mp_date` DATE NULL DEFAULT NULL AFTER `mp_revision`;
ALTER TABLE `aircraft_maintenance_programs` ADD COLUMN `mp_doc_nr` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `mp_date`;
ALTER TABLE `aircraft_maintenance_programs` ADD COLUMN `mp_validity` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `mp_doc_nr`;
ALTER TABLE `aircraft_maintenance_programs` ADD COLUMN `mp_approval_nr` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `mp_validity`;

RENAME TABLE `aircraft_maintenance_programs` TO `maintenance_programs`;


CREATE TABLE `maintenance_items` (
     `mi_id` int(11) NOT NULL AUTO_INCREMENT,
     `mi_mp_id` int(11) DEFAULT NULL,
     `mi_type` int(11) DEFAULT NULL,
     `mi_name` varchar(255) DEFAULT NULL,
     `mi_interval` int(11) DEFAULT 0,
     `mi_interval_unit` tinyint(5) DEFAULT 0,
     `mi_duration` int(11) DEFAULT 0,
     `mi_duration_unit` tinyint(5) DEFAULT NULL,
     PRIMARY KEY (`mi_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;