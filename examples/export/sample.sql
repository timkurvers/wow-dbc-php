DROP TABLE IF EXISTS `my_table`;

CREATE TABLE `my_table` (
	`id` INT(11) UNSIGNED NOT NULL, 
	`name` TEXT NOT NULL, 
	`points` INT(11) SIGNED NOT NULL, 
	`height` FLOAT NOT NULL, 
	`friend1` INT(11) SIGNED NOT NULL, 
	`friend2` INT(11) SIGNED NOT NULL, 
	PRIMARY KEY (`id`) 
);

INSERT INTO `my_table` VALUES 
	(1, 'John', 100, 1.79999995232, 2, 0),
	(2, 'Tim', 1337, 1.79999995232, 1, 0),
	(3, 'Pete', -10, 1.54999995232, 1, 2),
	(11, 'I am providing too many fields', 123, 1.20000004768, 0, 0),
	(12, 'I am providing too little fields', 0, 0, 0, 0),
	(4, 'Helen', 100, 1.79999995232, 0, 0),
	(8, 'Frank', 1337, 1.73000001907, 0, 0),
	(10, 'Brad', -10, 1.54999995232, 0, 0);

