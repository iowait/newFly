# Changes for High-Far-Long HFL
# DB changes since last release FLY_1.2_LOGGING_25Sep2020

# need to update mode.inc
SELECT 'Make sure mode.inc is update also with defines needed for HFL' AS note;

# create the storage dir - define in lib/hfl.inc
SELECT 'Make sure the subdirectory "hfl" is created and writable by the webserver' AS note;

CREATE TABLE hfl (
       `hflID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
       `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       `date` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
       `pilot` CHAR(50) NOT NULL DEFAULT '',
       `glider` ENUM('ASK 21','Discus CS','Duo Discus','G 102','L-13','SGS 1-26A','SGS 2-33A','SGS 1-34') NOT NULL,
       `extraName` CHAR(50) NOT NULL DEFAULT '' COMMENT 'Any other crew member',
       `fileName` CHAR(80) NOT NULL DEFAULT '' COMMENT 'Base name of IGC file as uploaded',
       `units` ENUM('US','METRIC','INVALID') NOT NULL DEFAULT 'INVALID',
       `lat`  DECIMAL(9,6) NOT NULL DEFAULT 0.0 COMMENT 'Lat, Lon, and Elev of field from mode.inc',
       `lon`  DECIMAL(9,6) NOT NULL DEFAULT 0.0,
       `elev` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0, 
       `timezone` MEDIUMINT NOT NULL DEFAULT 0 COMMENT 'Seconds offset from GMT, mode.inc',
       `high` MEDIUMINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'high/far values as defined by units',
       `far`  DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 0,
       `long` DECIMAL(4,1) NOT NULL DEFAULT 0.0 COMMENT 'Minutes',
       `notes` CHAR(255) NOT NULL DEFAULT '',
       INDEX (`date`),
       INDEX (`glider`),
       INDEX (`high`),
       INDEX (`far`),
       INDEX (`long`),
       INDEX (`pilot`),
       UNIQUE KEY theEntry (date, fileName)
)\p;
