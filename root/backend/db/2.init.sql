INSERT INTO schema_information(version) VALUES("104");

--commands
INSERT INTO Command(command, displayName) VALUES("ffmpeg -i %path -ab %bitrate -v 0 -f mp3 -", "Anything to mp3");
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'", "Generic bitrate getter");
INSERT INTO Command(command, displayName) VALUES("ffmpeg -i %path 2>&1 | sed -n -e 's/.*Duration: \([0-9]\{2\}:[0-9]\{2\}:[0-9]\{2\}\).*/\1/p' | awk -F ':' '{print ($1*3600)+($2*60)+$3}'", "Generic duration getter");

--file types
INSERT INTO FileType(extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES("mp3", "audio/mp3", "a", 2 , 3);
	INSERT INTO FileType(extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES("wma", "audio/wma", "a", 2 , 3);
	
INSERT INTO FileType(extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES("flv", "video/flv", "v", NULL , NULL);

INSERT INTO FileType(extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES("avi", "video/avi", "v", 2, 3);
	
--file convertors
INSERT INTO FileConverter(fromFileType, ToFileType, idcommand) VALUES("mp3","mp3",1);
INSERT INTO FileConverter(fromFileType, ToFileType, idcommand) VALUES("wma","mp3",1);
INSERT INTO FileConverter(fromFileType, ToFileType, idcommand) VALUES("avi","flv",1);


INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/music", "Music");
INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/video", "Video");


INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES ("testuser", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "test@test.com", 1, 320, 1000, 100);
	
INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth, 
	enableTrafficLimit, trafficLimit, trafficLimitStartTime, trafficLimitPeriod)
	VALUES ("testuser2", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "test2@test.com", 1, 128, 300, 500,
		1, 10000, strftime('%s', 'now'), 600);

INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES ("autotest", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "autotest@test.com", 1, 320, 1000, 100);
	
INSERT INTO APIKey(apikey, displayName) VALUES("{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}", "Main frontend");
INSERT INTO APIKey(apikey, displayName) VALUES("testkey1", "Testing apikey 1");
INSERT INTO APIKey(apikey, displayName) VALUES("testkey2", "Testing apikey2");

INSERT INTO Action(idAction, actionName, displayName) VALUES(1, "streamFile", "Stream Files");
INSERT INTO Action(idAction, actionName, displayName) VALUES(2, "downloadFile", "Download Files");
INSERT INTO Action(idAction, actionName, displayName) VALUES(3, "administrator", "Is Administrator");
INSERT INTO Action(idAction, actionName, displayName) VALUES(4, "accessMediaSource", "Access a Media Source");
INSERT INTO Action(idAction, actionName, displayName) VALUES(5, "accessStreamer", "Access a Streamer");

--testuser
INSERT INTO UserPermission(idUser, idAction) VALUES(1,1);
INSERT INTO UserPermission(idUser, idAction) VALUES(1,2);
INSERT INTO UserPermission(idUser, idAction) VALUES(1,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,4,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,4,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(1,5,4);

--testuser2
INSERT INTO UserPermission(idUser, idAction) VALUES(2,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,4,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,4,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,5,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,4,3);

--autotest
INSERT INTO UserPermission(idUser, idAction) VALUES(3,3);
INSERT INTO UserPermission(idUser, idAction) VALUES(3,1);
INSERT INTO UserPermission(idUser, idAction) VALUES(3,2);
INSERT INTO UserPermission(idUser, idAction) VALUES(3,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,4,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,4,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,5,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,5,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,5,4);
