
#DROP DATABASE streetart;
CREATE DATABASE IF NOT EXISTS `streetart`;
USE `streetart`; 



-- --------------------------------------------------------

--
-- Table structure for table `art`
--

CREATE TABLE IF NOT EXISTS `art` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `featured` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  `lat` float(10,6) NOT NULL,
  `lon` float(10,6) NOT NULL,
  `image_final` varchar(25) NOT NULL,
  `image_thumb` varchar(25) NOT NULL,
  `image_tag` varchar(25) NOT NULL,
  `image_background` varchar(25) NOT NULL,
  `data` varchar(25) NOT NULL,
  `zone` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lon` (`lon`),
  KEY `lat` (`lat`),
  KEY `zone_id` (`zone`),
  KEY `artist` (`user`,`views`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `like`
--

CREATE TABLE IF NOT EXISTS `like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `art` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `username` varchar(30) NOT NULL,
  `location` text NOT NULL,
  `image` text NOT NULL,
  `confirmed_email` tinyint(1) NOT NULL DEFAULT '0',
  `confirmation_token` varchar(13) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zone`
--

CREATE TABLE IF NOT EXISTS `zone` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_ref` varchar(20) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`zone_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

# CHANGES
ALTER TABLE  `art` ADD  `likes` INT NOT NULL AFTER  `views`;

# root node
INSERT INTO `zone` (`zone_id`, `zone_ref`, `lft`, `rgt`) VALUES
(1, '0-0-0', 1, 2);


# stored procedure to get art
DROP PROCEDURE IF EXISTS `getArt`;
DELIMITER //
CREATE PROCEDURE getArt(IN artId VARCHAR(255))
BEGIN
SELECT *
FROM art
WHERE art_id = artId;
END //
DELIMITER ; 



# stored procedure show tree
DROP PROCEDURE IF EXISTS `getZoneTree`;
DELIMITER //
CREATE PROCEDURE getZoneTree()
BEGIN
SELECT CONCAT( REPEAT('-', COUNT(parent.zone_ref) - 1), node.zone_ref) AS zone_ref 
FROM `zone` AS node, `zone` AS parent WHERE node.lft 
BETWEEN parent.lft AND parent.rgt GROUP BY node.zone_ref ORDER BY node.lft;
END //
DELIMITER ; 


# stored procedure show tree
DROP PROCEDURE IF EXISTS `getZoneArtCount`;
DELIMITER //
CREATE PROCEDURE getZoneArtCount() 
BEGIN
SELECT parent.zone_ref, COUNT(art.`zone`)
FROM `zone` AS node, `zone` AS parent, art
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.zone_id = art.`zone`
GROUP BY parent.zone_ref
ORDER BY node.lft;
END //
DELIMITER ;



# fetch art from zone
DROP PROCEDURE IF EXISTS `getZoneArt`;
DELIMITER //
CREATE PROCEDURE getZoneArt(IN zoneRef VARCHAR(255))
BEGIN
SELECT * FROM art 
WHERE `zone` IN 
(SELECT node.zone_id FROM zone AS node, `zone` AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.zone_ref = zoneRef ORDER BY node.lft);
END //
DELIMITER ;



# add zone neighbour, adds a zone at the same level as another zone (just after)
DROP PROCEDURE IF EXISTS `addNeighbourZone`;
DELIMITER //
CREATE PROCEDURE addNeighbourZone(IN newZoneRef VARCHAR(255), IN neighbourZoneRef VARCHAR(255))
BEGIN
START TRANSACTION;

SET @myRight = (SELECT rgt FROM `zone` WHERE zone_ref = neighbourZoneRef);
UPDATE `zone` SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE `zone` SET lft = lft + 2 WHERE lft > @myRight;
INSERT INTO `zone`(zone_ref, lft, rgt) VALUES(newZoneRef, @myRight + 1, @myRight + 2);
select LAST_INSERT_ID() AS LastInsertId;
COMMIT;
END //
DELIMITER ;



# add zone neighbour, adds a zone at the same level as another zone (just after)
DROP PROCEDURE IF EXISTS `addChildZone`;
DELIMITER //
CREATE PROCEDURE addChildZone(IN newZoneRef VARCHAR(255), IN parentZoneRef VARCHAR(255))
BEGIN
START TRANSACTION;
SET @myLeft = (SELECT lft FROM `zone` WHERE zone_ref = parentZoneRef);
UPDATE `zone` SET rgt = rgt + 2 WHERE rgt > @myLeft;
UPDATE `zone` SET lft = lft + 2 WHERE lft > @myLeft;
INSERT INTO `zone`(zone_ref, lft, rgt) VALUES(newZoneRef, @myLeft + 1, @myLeft + 2);
select LAST_INSERT_ID() AS LastInsertId;
COMMIT;
END //
DELIMITER ;


# delete zone
DROP PROCEDURE IF EXISTS `deleteZone`;
DELIMITER //
CREATE PROCEDURE deleteZone(IN zoneRef VARCHAR(255))
BEGIN
START TRANSACTION;

SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1
FROM `zone` 
WHERE zone_ref = zoneRef;

DELETE FROM `zone` WHERE lft BETWEEN @myLeft AND @myRight;

UPDATE `zone` SET rgt = rgt - @myWidth WHERE rgt > @myRight;
UPDATE `zone` SET lft = lft - @myWidth WHERE lft > @myRight;

COMMIT;
END //
DELIMITER ;





/*



# fetch full tree
SELECT CONCAT( REPEAT('-', COUNT(parent.zone_name) - 1), node.zone_name) AS zone_name FROM zone AS node, zone AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt GROUP BY node.zone_name ORDER BY node.lft;



# fetch from zone AA
SELECT * FROM pin WHERE zone_id IN (SELECT node.zone_id FROM zone AS node, zone AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.zone_name = 'AA' ORDER BY node.lft);


# add zone
LOCK TABLE zone WRITE;
SELECT @myRight := rgt FROM zone
WHERE zone_name = '000';
UPDATE zone SET rgt = rgt + 2 WHERE rgt > @myRight;
UPDATE zone SET lft = lft + 2 WHERE lft > @myRight;
INSERT INTO zone(zone_name, lft, rgt) VALUES('011', @myRight + 1, @myRight + 2);
UNLOCK TABLES;
*/
