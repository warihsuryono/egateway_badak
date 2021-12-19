UPDATE configurations SET addr_num='57' where id='1';
ALTER TABLE sensors ADD COLUMN group_name VARCHAR(10) AFTER formula, ADD INDEX (group_name);
UPDATE sensors SET group_name='YOKOGAWA';
INSERT INTO sensors (sensor_code,unit,formula,group_name) VALUES 
('NOx','ppm','round(data[52]/10,2)','ROSEMOUNT'),
('SO<sub></sub>2','ppm','round(data[53]/10,2)','ROSEMOUNT'),
('CO<sub></sub>2','%','round(data[54]/10,2)','ROSEMOUNT'),
('CO','ppm','round(data[55]/10,2)','ROSEMOUNT'),
('O<sub>2</sub>','%','round(data[56]/10,2)','ROSEMOUNT');

CREATE TABLE `sensor_value_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `sensor_id` int(11) NOT NULL DEFAULT 0,
  `data` double NOT NULL DEFAULT 0,
  `xtimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `sensor_value_histories` ADD PRIMARY KEY (`id`),ADD KEY `sensor_id` (`sensor_id`);
ALTER TABLE `sensor_value_histories` MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;