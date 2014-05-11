
--commands
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -ss %skipToTime -i %path -ab %bitrate -v 0 -f mp3 -", "Anything to mp3");
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'", "Generic bitrate getter");
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*Duration: \([0-9]\{2\}:[0-9]\{2\}:[0-9]\{2\}\).*/\1/p' | awk -F ':' '{print ($1*3600)+($2*60)+$3}'", "Generic duration getter");
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -ss %skipToTime -i %path -async 1 -b %bitrate -vf 'scale=320:trunc((320/a)/2)*2' -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -", "Anything to flv");

--file types
INSERT INTO FileType(idfileType, extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES(1, "mp3", "audio/mp3", "a", 2 , 3);
	
INSERT INTO FileType(idfileType, extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES(2, "wma", "audio/wma", "a", 2 , 3);
	
INSERT INTO FileType(idfileType, extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES(3, "flv", "video/flv", "v", NULL , NULL);

INSERT INTO FileType(idfileType, extension, mimeType, mediaType, idbitrateCmd, iddurationCmd) 
	VALUES(4, "avi", "video/avi", "v", 2, 3);
	
--file convertors
INSERT INTO FileConverter(fromidfileType, toidfileType, idcommand) VALUES(1,1,1);
INSERT INTO FileConverter(fromidfileType, toidfileType, idcommand) VALUES(2,1,1);
INSERT INTO FileConverter(fromidfileType, toidfileType, idcommand) VALUES(4,3,4);
