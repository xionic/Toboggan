--testing media sources
INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/music", "Music");
INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/video", "Video");


--testing users
INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES ("testuser", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "test@test.com", 1, 320, 1000, 1000);
	
INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth, 
	enableTrafficLimit, trafficLimit, trafficLimitStartTime, trafficLimitPeriod)
	VALUES ("testuser2", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "test2@test.com", 1, 128, 300, 100,
		1, 10000, strftime('%s', 'now'), 600);

INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES ("autotest", "AzxyIMQP9MXAOvb2IRnA1lvRV/wTHWfP1W97eYCmXlY=", "autotest@test.com", 1, 320, 1000, 100);
	
--PERMISSIONS
--testuser
INSERT INTO UserPermission(idUser, idAction) VALUES(2,1);
INSERT INTO UserPermission(idUser, idAction) VALUES(2,2);
INSERT INTO UserPermission(idUser, idAction) VALUES(2,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,4,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,4,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,5,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,5,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(2,5,4);

--testuser2
INSERT INTO UserPermission(idUser, idAction) VALUES(3,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,4,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,4,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,5,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(3,4,3);

--autotest
INSERT INTO UserPermission(idUser, idAction) VALUES(4,3);
INSERT INTO UserPermission(idUser, idAction) VALUES(4,1);
INSERT INTO UserPermission(idUser, idAction) VALUES(4,2);
INSERT INTO UserPermission(idUser, idAction) VALUES(4,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(4,4,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(4,4,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(4,5,1);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(4,5,2);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(4,5,3);
INSERT INTO UserPermission(idUser, idAction, targetObjectID) VALUES(4,5,4);
