CREATE TABLE `extensionMap` (
	`idextensionMap` INTEGER PRIMARY KEY AUTOINCREMENT,
	`idfromExt` INT NOT NULL ,
	`idtoExt` INT NOT NULL ,
	`idtranscode_cmd` INT NOT NULL ,
	CONSTRAINT `fromExt`
		FOREIGN KEY (`idtoExt` )
		REFERENCES `fromExt` (`idfromExt` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT `toExt`
		FOREIGN KEY (`idtoExt` )
		REFERENCES `toExt` (`idtoExt` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT `transcode_cmd`
		FOREIGN KEY (`idtranscode_cmd` )
		REFERENCES `transcode_cmd` (`idtranscode_cmd` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
	CONSTRAINT `extMap`
		UNIQUE (`idfromExt`, idtoExt)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `fromExt` (
	`idfromExt` INTEGER PRIMARY KEY AUTOINCREMENT ,
	`Extension` VARCHAR(8) NOT NULL ,
	`bitrateCmd` MEDIUMTEXT NOT NULL ,
	CONSTRAINT `Ext`
		UNIQUE (`Extension`)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `toExt` (
	`idtoExt` INTEGER PRIMARY KEY AUTOINCREMENT,
	`Extension` VARCHAR(8) NOT NULL ,
	`MimeType` VARCHAR(32) NOT NULL ,
	`MediaType` VARCHAR(1) NOT NULL ,
	CONSTRAINT `Ext`
		UNIQUE (`Extension`)
		ON CONFLICT ROLLBACK
);

CREATE TABLE `mediaSource` (
	`idmediaSource` INTEGER PRIMARY KEY AUTOINCREMENT,
	`path` VARCHAR(64) NOT NULL,
	`displayName` VARCHAR(32) NOT NULL
);
  
CREATE TABLE `transcode_cmd` (
	`idtranscode_cmd` INTEGER PRIMARY KEY AUTOINCREMENT,
	`command` MEDIUMTEXT NOT NULL
);

CREATE TABLE `User` (
	`idUser` INTEGER PRIMARY KEY AUTOINCREMENT,
	`idRole` INTEGER NOT NULL,
	`username` VARCHAR(32) NOT NULL,
	`password` CHAR(64) NOT NULL,
	`email` VARCHAR(256),
	`enabled` TINYINT(1),
	`maxAudioBitrate` INT,
	`maxVideoBitrate` INT,
	`maxBandwidth` INT,
	CONSTRAINT `uniqueUsername`
		UNIQUE (`username`)
		ON CONFLICT ROLLBACK
	CONSTRAINT `UserRole`
		FOREIGN KEY (`idRole` )
		REFERENCES `Role` (`idRole` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
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

CREATE TABLE `Role` (
	`idRole` INTEGER PRIMARY KEY AUTOINCREMENT,
	`roleName` VARCHAR(45) NOT NULL ,
	CONSTRAINT `rolename`
		UNIQUE (`roleName`)
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

CREATE TABLE `UserTrafficLimit` (
	`idUserTrafficLimit` INTEGER PRIMARY KEY AUTOINCREMENT,
	`idUser` INTEGER NOT NULL,
	`trafficLimit` INTEGER NOT NULL CHECK (trafficLimit > 0),
	`trafficUsed` INTEGER NOT NULL DEFAULT 0 CHECK (trafficUsed >= 0),
	`startTime` INTEGER NULL,
	`period` INTEGER NOT NULL CHECK (period > 0),
	CONSTRAINT `userid`
		FOREIGN KEY (`idUser` )
		REFERENCES `User` (`idUser` )
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);




