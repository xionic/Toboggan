INSERT INTO fromExt(Extension, bitrateCmd) VALUES("mp3", "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'");
INSERT INTO toExt(Extension, MimeType, MediaType) VALUES("mp3", "audio/mp3", "a");
INSERT INTO transcode_cmd(command) VALUES("ffmpeg -i %path -ab %bitrate -v 0 -f mp3 -");
INSERT INTO extensionMap(idfromExt, idToExt, idtranscode_cmd) VALUES(1,1,1);

INSERT INTO fromExt(Extension, bitrateCmd) VALUES("avi", "/usr/bin/ffmpeg -i %path 2>&1 | sed -n -e 's/.*bitrate: \([0-9]\+\).*/\1/p'");
INSERT INTO toExt(Extension, MimeType, MediaType) VALUES("flv", "video/flv", "v");
INSERT INTO transcode_cmd(command) VALUES("/usr/bin/ffmpeg -i %path -async 1 -b %bitrate -s 320x240 -ar 44100 -ac 2 -v 0 -f flv -vcodec libx264 -preset superfast -");
INSERT INTO extensionMap(idfromExt, idToExt, idtranscode_cmd) VALUES(2,2,2);

INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/music", "Music");
INSERT INTO mediaSource(path, displayName) VALUES("/mnt/storage/video", "Video");
