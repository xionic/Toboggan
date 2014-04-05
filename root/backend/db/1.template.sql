--some general setup
PRAGMA journal_mode=WAL;

--create the tables
CREATE TABLE `FileConverter` (
	`idfileConverter` INTEGER PRIMARY KEY AUTOINCREMENT,
	`fromFileType` CHAR(8) NOT NULL ,
	`toFileType` CHAR(8) NOT NULL ,
	`idcommand` INT NOT NULL ,
	FOREIGN KEY (`fromFileType` ) REFERENCES `FileType` (`extension` )
	FOREIGN KEY (`toFileType` ) REFERENCES `FileType` (`extension` )
	
	CONSTRAINT `command`
		FOREIGN KEY (`idcommand` )
		REFERENCES `Command` (`idcommand` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
	CONSTRAINT `extMap`
		UNIQUE (`fromFileType`, toFileType)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `FileType` (
	`extension` CHAR(8) PRIMARY KEY,
	`mimeType` VARCHAR(32) NOT NULL ,
	`mediaType` VARCHAR(1) NOT NULL ,
	`idbitrateCmd` INT NULL ,
	`iddurationCmd` INT NULL ,
	CONSTRAINT `idbitrateCmd`
		FOREIGN KEY (`idbitrateCmd` )
		REFERENCES `Command` (`idcommand` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT `iddurationCmd`
		FOREIGN KEY (`iddurationCmd` )
		REFERENCES `Command` (`idcommand` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT `ext`
		UNIQUE (`extension`)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `Command` (
	`idcommand` INTEGER PRIMARY KEY AUTOINCREMENT,
	`displayName` VARCHAR(45),
	`command` MEDIUMTEXT NOT NULL
);

CREATE TABLE `mediaSource` (
	`idmediaSource` INTEGER PRIMARY KEY AUTOINCREMENT,
	`path` VARCHAR(64) NOT NULL,
	`displayName` VARCHAR(32) NOT NULL
);

CREATE TABLE `User` (
	`idUser` INTEGER PRIMARY KEY AUTOINCREMENT,
	`username` VARCHAR(32) NOT NULL,
	`password` CHAR(64) NOT NULL,
	`email` VARCHAR(256),
	`enabled` TINYINT(1),
	`maxAudioBitrate` INT,
	`maxVideoBitrate` INT,
	`maxBandwidth` INT,
	`enableTrafficLimit` BOOLEAN DEFAULT 0,
	`trafficLimit` INTEGER NOT NULL DEFAULT 0 CHECK (enableTrafficLimit = 0 OR trafficLimit > 0),
	`trafficUsed` INTEGER NOT NULL DEFAULT 0 CHECK (enableTrafficLimit = 0 OR trafficUsed >= 0),
	`trafficLimitStartTime` INTEGER NULL,
	`trafficLimitPeriod` INTEGER NOT NULL DEFAULT 0 CHECK (enableTrafficLimit = 0 OR trafficLimitPeriod > 0),
	CONSTRAINT `uniqueUsername`
		UNIQUE (`username`)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `ClientSettings` (
	`idClientSettings` INTEGER PRIMARY KEY AUTOINCREMENT,
	`idAPIKey` INTEGER,
	`settings` TEXT,
	`idUser` INTEGER,
	CONSTRAINT `User`
		FOREIGN KEY (`idUser` )
		REFERENCES `User` (`idUser` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
	CONSTRAINT `apiUserMap`
		UNIQUE (`idAPIKey`, `idUser`)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `APIKey` (
	`idAPIKey` INTEGER PRIMARY KEY AUTOINCREMENT,
	`apikey` VARCHAR(40),
	`displayName` VARCHAR(64),
	CONSTRAINT `apikey`
		UNIQUE (`apikey`)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `schema_information` (
	`idschema_information` INTEGER PRIMARY KEY AUTOINCREMENT,
	`version` INTEGER NOT NULL
);

CREATE TABLE `Action` (
	`idAction` INTEGER PRIMARY KEY,
	`actionName` VARCHAR(45) NOT NULL ,
	`displayName` VARCHAR(45) NOT NULL,
	CONSTRAINT `actionName`
		UNIQUE (`actionName`)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `UserPermission` (
	`idUserPermission` INTEGER PRIMARY KEY AUTOINCREMENT,
	`idUser` INTEGER NOT NULL ,
	`idAction` INTEGER NOT NULL ,
	`targetObjectID` INTEGER NULL , --the target of the permission, for instance a mediaSourceID. Context based on the action - use determined by code
	CONSTRAINT `userid`
		FOREIGN KEY (`idUser` )
		REFERENCES `User` (`idUser` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT `actionid`
		FOREIGN KEY (`idAction` )
		REFERENCES `Action` (`idAction` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);





