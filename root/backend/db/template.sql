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
  `bitrateCmd` MEDIUMTEXT NOT NULL
);
CREATE TABLE `mediaSource` (
  `idmediaSource` INTEGER PRIMARY KEY AUTOINCREMENT,
  `path` VARCHAR(64) NOT NULL,
  `displayName` VARCHAR(32) NOT NULL
);
CREATE TABLE `toExt` (
  `idtoExt` INTEGER PRIMARY KEY AUTOINCREMENT,
  `Extension` VARCHAR(8) NOT NULL ,
  `MimeType` VARCHAR(32) NOT NULL ,
  `MediaType` VARCHAR(1) NOT NULL
  );
CREATE TABLE `transcode_cmd` (
`idtranscode_cmd` INTEGER PRIMARY KEY AUTOINCREMENT,
command TEXT NOT NULL
);
CREATE INDEX `idx_fromExt` ON `extensionMap` (`idfromExt`);
CREATE INDEX `idx_toExt` ON `extensionMap` (`idtoExt`);
CREATE INDEX `idx_transcode_cmd` ON `extensionMap` (`idtranscode_cmd`);