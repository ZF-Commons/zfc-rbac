CREATE TABLE `rbac_permission` (
  `perm_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `perm_name` varchar(32) NULL,
  PRIMARY KEY (`perm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE `rbac_role` (
  `role_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_role_id` int(11) unsigned NULL,
  `role_name` varchar(32) NULL,
  PRIMARY KEY (`role_id`),
  KEY `parent_role_id` (`parent_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `rbac_role`
  ADD CONSTRAINT `rbac_role_ibfk_1` FOREIGN KEY (`parent_role_id`) REFERENCES `rbac_role` (`role_id`);


CREATE TABLE `rbac_role_permission` (
  `role_id` int(11) unsigned NOT NULL,
  `perm_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`perm_id`),
  KEY `perm_id` (`perm_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `rbac_role_permission`
  ADD CONSTRAINT `rbac_role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `rbac_role` (`role_id`),
  ADD CONSTRAINT `rbac_role_permission_ibfk_2` FOREIGN KEY (`perm_id`) REFERENCES `rbac_permission` (`perm_id`);
