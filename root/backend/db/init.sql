INSERT INTO schema_information(version) VALUES("101");

INSERT INTO fromExt(Extension, bitrateCmd) VALUES("mp3", "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'");
INSERT INTO toExt(Extension, MimeType, MediaType) VALUES("mp3", "audio/mp3", "a");
INSERT INTO transcode_cmd(command) VALUES("ffmpeg -i %path -ab %bitrate -v 0 -f mp3 -");
INSERT INTO extensionMap(idfromExt, idToExt, idtranscode_cmd) VALUES(1,1,1);

INSERT INTO fromExt(Extension, bitrateCmd) VALUES("avi", "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'");
INSERT INTO toExt(Extension, MimeType, MediaType) VALUES("flv", "video/flv", "v");
INSERT INTO transcode_cmd(command) VALUES("/usr/bin/ffmpeg -i %path -async 1 -b %bitrate -vf 'scale=320:trunc((320/a)/2)*2' -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -");
INSERT INTO extensionMap(idfromExt, idToExt, idtranscode_cmd) VALUES(2,2,2);

INSERT INTO fromExt(Extension, bitrateCmd) VALUES("wma", "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'");
INSERT INTO extensionMap(idfromExt, idToExt, idtranscode_cmd) VALUES(3,1,1);

INSERT INTO fromExt(Extension, bitrateCmd) VALUES("mkv", "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'");
INSERT INTO extensionMap(idfromExt, idToExt, idtranscode_cmd) VALUES(4,2,2);



INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/music", "Music");
INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/video", "Video");

INSERT INTO Role(roleName) VALUES("Admin");
INSERT INTO Role(roleName) VALUES("User");

INSERT INTO User(idRole, username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES (0, "testuser", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "test@test.com", 1, 320, 1000, 100);
	
INSERT INTO User(idRole, username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES (1, "testuser2", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "test2@test.com", 1, 128, 300, 75);
	
INSERT INTO APIKey(apikey, displayName) VALUES("{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}", "Main frontend");
INSERT INTO APIKey(apikey, displayName) VALUES("testkey1", "Testing apikey 1");
INSERT INTO APIKey(apikey, displayName) VALUES("testkey2", "Testing apikey2");

INSERT INTO Action(idAction, actionName, displayName) VALUES(1, "streamFile", "Stream files");
INSERT INTO Action(idAction, actionName, displayName) VALUES(2, "downloadFile", "Download files");
INSERT INTO Action(idAction, actionName, displayName) VALUES(3, "administrator", "Is Administrator");
INSERT INTO Action(idAction, actionName, displayName) VALUES(4, "accessMediaSource", "Access a media source");
INSERT INTO Action(idAction, actionName, displayName) VALUES(5, "accessStreamer", "Access a streamer");

INSERT INTO UserPermission(idUser, idAction) VALUES(1,1);
INSERT INTO UserPermission(idUser, idAction) VALUES(1,2);
INSERT INTO UserPermission(idUser, idAction) VALUES(1,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,4,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,4,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,4);

INSERT INTO UserPermission(idUser, idAction) VALUES(2,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,4,1);