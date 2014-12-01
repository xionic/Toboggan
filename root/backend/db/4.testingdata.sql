--testing media sources
INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/music", "Music");
INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/video", "Video");


--testing users
INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES ("testuser", "W3TZVp9XLQ4eo3BtfSb1yLMZ97Hi+P0gyJzUbHVvH4c=", "test@test.com", 1, 320, 1000, 1000);
	
INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth, 
	enableTrafficLimit, trafficLimit, trafficLimitStartTime, trafficLimitPeriod)
	VALUES ("testuser2", "W3TZVp9XLQ4eo3BtfSb1yLMZ97Hi+P0gyJzUbHVvH4c=", "test2@test.com", 1, 128, 300, 100,
		1, 10000, strftime('%s', 'now'), 600);

INSERT INTO User(username, password, email, enabled, maxAudioBitrate, maxVideoBitrate, maxBandwidth)
	VALUES ("autotest", "W3TZVp9XLQ4eo3BtfSb1yLMZ97Hi+P0gyJzUbHVvH4c=", "autotest@test.com", 1, 320, 1000, 100);
	
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
