INSERT INTO schema_information(version) VALUES("104");

INSERT INTO APIKey(apikey, displayName) VALUES("{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}", "Main frontend");
INSERT INTO APIKey(apikey, displayName) VALUES("testkey1", "Testing apikey 1");
INSERT INTO APIKey(apikey, displayName) VALUES("testkey2", "Testing apikey2");

INSERT INTO Action(idAction, actionName, displayName) VALUES(1, "streamFile", "Stream Files");
INSERT INTO Action(idAction, actionName, displayName) VALUES(2, "downloadFile", "Download Files");
INSERT INTO Action(idAction, actionName, displayName) VALUES(3, "administrator", "Is Administrator");
INSERT INTO Action(idAction, actionName, displayName) VALUES(4, "accessMediaSource", "Access a Media Source");
INSERT INTO Action(idAction, actionName, displayName) VALUES(5, "accessStreamer", "Access a Streamer");

--the first user "admin"
INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
        VALUES ("admin", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "admin@test.com", 1, 320, 1000, 1000);
	        
	--give "admin" user admin permissions
INSERT INTO UserPermission(idUser, idAction) VALUES(1,3);
