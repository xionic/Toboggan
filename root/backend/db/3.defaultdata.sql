
--commands
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -i %path -ab %bitrate -v 0 -f mp3 -", "Anything to mp3");
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'", "Generic bitrate getter");
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*Duration: \([0-9]\{2\}:[0-9]\{2\}:[0-9]\{2\}\).*/\1/p' | awk -F ':' '{print ($1*3600)+($2*60)+$3}'", "Generic duration getter");
INSERT INTO Command(command, displayName) VALUES("/usr/bin/ffmpeg -ss %skipToTime -i %path -async 1 -b %bitrate -vf 'scale=320:trunc((320/a)/2)*2' -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -", "Anything to flv");

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
INSERT INTO FileConverter(fromFileType, ToFileType, idcommand) VALUES("avi","flv",4);
