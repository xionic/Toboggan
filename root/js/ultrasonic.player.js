/**
	Holds the JS used for the player system, playlist management etc
*/
(function(){
	var apikey='{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}',
		apiversion='0.58',
		initialProgressEvent=false,	//used to ensure that the initial progress event is the only one handled
		playerCSSProperties = {},
		isFullscreen = {},
		rightClickedObject = {},
		currentUserName = "",
		currentUserID = "";
	/**
		jQuery Entry Point
	*/
	$(document).ready(function(){
	
		/** Hide the video pane **/
		showHideVideoPane("a");
	
		/** Setup the jPlayer */
		$("#jquery_jplayer_1").jPlayer({
			ready: function () {
				$(this).jPlayer("setMedia", {
					mp3: "",
					flv: ""
				});	
				
				//Enormous hack for fullscreen to apply custom css to it
				$("#jp_container_1 .jp-full-screen").click(function(){
					
					playerCSSProperties = {
											'position':$("#centerPlayerContainer").css("position"),
											'top':$("#centerPlayerContainer").css("top"),
											'bottom': $("#centerPlayerContainer").css("bottom"),
											'left': $("#centerPlayerContainer").css("left"),
											'right': $("#centerPlayerContainer").css("right"),
											'background': $("#centerPlayerContainer").css("background"),
											'z-index': $("#centerPlayerContainer").css("z-index")
										};
					
					$("#centerPlayerContainer").css({
														'position':'fixed',
														'top':'0px',
														'bottom': $("#playerControlsContainer").height(),
														'left': '0px',
														'right': '0px',
														'background':'black',
														'zIndex': '10'
													});
				});
				
				//Enormous hack to remove the above hack's results
				$("#jp_container_1 .jp-restore-screen").click(function(){
					$("#centerPlayerContainer").css({
														'position':playerCSSProperties.position,
														'top':playerCSSProperties.top,
														'bottom': playerCSSProperties.bottom,
														'left': playerCSSProperties.left,
														'right': playerCSSProperties.right,
														'background':playerCSSProperties.black,
														'z-index': playerCSSProperties.zIndex
													});
				});
			},	//hack/workaround for the nightmarish streamed video layback stopping issue!
			progress: function(event) {	
				//if this is the first time the progress event has been handled				
				if(!initialProgressEvent)
				{
 					$("#jquery_jplayer_1").jPlayer("play");
 					initialProgressEvent=true;
 				}
			},
			volumechange: function(event) {
				//save the volume and mute settings to HTML5 LocalStorage
				localStorage.setItem("playbackVolume", event.jPlayer.options.volume);
				localStorage.setItem("isMuted", event.jPlayer.options.muted);
			},
			error: function(event){
				console.error(event);
				if(event.jPlayer.error.type == $.jPlayer.error.URL)
				{
					alert("there was an error with the media url!");
				}
			},
			warning: function(event){
				console.warn(event);
			},
			swfPath: "./js/jQuery.jPlayer.2.1.0/",
			supplied: "mp3,flv",
			preload: "none",
			wmode: "window",
			verticalVolume: false,
			volume: ( (localStorage.getItem("playbackVolume") !== null)?localStorage.getItem("playbackVolume"):0.8),
			muted: localStorage.getItem("isMuted")=="true"?true:false,		
			cssSelector: {
				videoPlay: "",
				volumeMax: "",
				repeat: "",
				repeatOff: ""
			},
			backgroundColor: "#000000"
		}).bind($.jPlayer.event.ended, function(event){

		//get the currently played one
			var playingObject = $("#playlistTracks .jPlaying");
			playingObject.removeClass("jPlaying");
			
			var nextObjectSpan = playingObject.parent().next("li").children("a.playNow");
			if(!nextObjectSpan)
				return;
			
			play_jPlayerTrack(nextObjectSpan);
		});
		
		$("#jp_container_1 ul.jp-controls .jp-play").click(function(){
			//if there is no track being played then play the first
			if($("#playlistTracks .jPlaying").length == 0)
			{
				$("#playlistTracks li:first a.playNow").click();
			}
		});

		//add an additional handler onto the stop buttn
		$("#jp_container_1 ul.jp-controls .jp-stop").click(function(){
			//reset the media if it's a video
		});
		
		$("#mediaSourceSelector").change(function(event){
			updateFolderBrowser($("#mediaSourceSelector").val());
		});
		
		
		$("#search_submitBtn").button({
			icons: {primary: 'ui-icon-search'},
			text: true
		})
		
		$("#searchForm").submit(function(event){
			event.preventDefault();
			event.stopPropagation();

			//un-highlight the selected folder			
			if(activeNode = $("#folderlist").dynatree("getTree").getActiveNode())
				activeNode.deactivate();
			
			searchForMedia(	
				$("#search_mediaSourceSelector").val(), 
				$("#search_dir").val(), 
				$("#search_query").val()
			);			
		});
		
		/**
			Add handlers for buttons
		**/
		//initialise the click handler for config
		$("#configButton_txt").click(function(event){
			event.preventDefault();
			window.open(this.href,"Administration");
		});

		//Add handling for "select all" box
		$("#selectAll_inputs").click(function(){
			var checkStat = Boolean($(this).attr("checked"));

			$("#tracklist input[type='checkbox'][name='trackCheckbox']").attr("checked",checkStat);
		});
		
		//addSelectedToPlaylist handling
		$("#addSelectedToPlaylist").click(function(){
			$("#tracklist input[type='checkbox'][name='trackCheckbox']:checked").siblings("a.addToPlaylistButton").click();
			return false;
		});
		
		//logout button handling
		$("#logoutButton").click(function(){

	        $.ajax({
	            cache: false,
	            url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=logout&apikey="+apikey+"&apiver="+apiversion,
   		        type: "GET",
            	complete: function(jqxhr, status) {},
            	error: function(jqxhr, status, errorThrown) {
            	    alert("AJAX ERROR - check the console!");
            	    console.error(jqxhr, status, errorThrown);
            	},
            	success: function(data, status, jqxhr) {
					$("<div id='logoutNotification'>Successfully Logged Out</div>").appendTo($("body")).dialog({
						modal: true,
						closeOnEscape: false,
						draggable: false,
						resizable: false,
						dialogClass: "loggedout",
						height: 60,
						width: 180,
						title: "Information",
					});
				},
			});
			return false;
		});		
	
		/**
			Configure Dynatree
		*/
		$("#folderlist").dynatree({
			title: "folderlist",
			keyboard: false,
			autoCollapse: false,
			generateIds: false,
			noLink: true,
			debugLevel: 0,
			onActivate: function(node){
				updateFolderBrowser($("#mediaSourceSelector").val(), node);
			},
			onExpand: function(node){
				// triggered on shrinking as well as expanding
				if(node && node.activate)
					node.activate();
			},
			onLazyRead: function(node){
				node.activate();
			}
		});
	
		getMediaSources();
		//load the nowPlaying from localStorage
		loadNowPlaying();
		
		configureContextMenuCallbacks();
		
		addKeepAlives();
		
		//load jPlayer Inspector
	//	$("#jPlayerInspector").jPlayerInspector({jPlayer:$("#jquery_jplayer_1")});
	});

	/**
		Load the now playing list from HTML5 LocalStorage
	*/
	function loadNowPlaying()
	{
		var nowPlayingKey = "nowPlaying-" + window.location.host + window.location.pathname,
			nowPlaying = localStorage.getItem(nowPlayingKey);
		
		if(typeof nowPlaying === "undefined" || !nowPlaying)
			return;
		
		var trackList = $.parseJSON(nowPlaying);
		for(var x=0; x<trackList.length; ++x)
		{
			addToNowPlaying(trackList[x]);
		}
		addNowPlayingClickHandlers();
	}
	
	
	/**
		Saves the now playing list to HTML5 LocalStorage
	*/
	function saveNowPlaying()
	{
		var trackList = $("#playlistTracks li a.playNow"),
			nowPlaying = [],
			nowPlayingKey = nowPlayingKey = "nowPlaying-" + window.location.host + window.location.pathname;
		
		for(var x=0; x<trackList.length; ++x)
		{
			nowPlaying.push({
				'text': $(trackList[x]).text(),
				'filename': $(trackList[x]).attr("data-filename"),
				'dir': $(trackList[x]).attr("data-dir"),
				'streamers':$(trackList[x]).attr("data-streamers"),
				'media_source':$(trackList[x]).attr("data-media_source"),
			});
		}
		
		localStorage.setItem(nowPlayingKey, JSON.stringify(nowPlaying));
	}
	
	/**
		Add a periodic AJAX call to ensure the session stays alive
			This uses retrieveClientSettings REST call for now
			until a more appropriate verb is created/discovered
	*/
	function addKeepAlives()
	{
	
		setInterval(function(){
			
			$.ajax({
				cache: false,
				url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=retrieveClientSettings&apikey="+apikey+"&apiver="+apiversion,
				type: "GET",
				data: { },
				complete: function(jqxhr, status) {},
				error: function(jqxhr, status, errorThrown) {
					alert("AJAX ERROR - check the console!");
					console.error(jqxhr, status, errorThrown);
				},
				
				success: function(data, status, jqxhr) {
				}
			});
			
		},60000);
	}
	
	/**
		Add a track to the now playing list
	*/
	function addToNowPlaying(trackObject)
	{	
		$("#playlistTracks").append(		
			$("<li></li>").append(
				$("<a href='javascript:;' class='removeFromPlaylist'>&minus;</a>")
			).append(
				$("<a href='javascript:;' class='playNow trackObject'></a>")
					.text( trackObject.text )
					.attr( "data-filename", trackObject.filename )
					.attr( "data-dir", trackObject.dir )
					.attr( "data-streamers", trackObject.streamers )
					.attr( "data-media_source", trackObject.media_source )
			).bind('contextmenu', function(e){

				e.preventDefault();
				rightClickedObject = this;
				$("#trackMenu .hideInPlaylist").hide();

				return setupContextMenu(e,{
					streamers: JSON.parse(trackObject.streamers)
				})
			})
		);
	}
	
	/**
		Adds static callbacks on for the context menu
	*/
	function configureContextMenuCallbacks()
	{
		$('#trackMenu .first_li, #trackMenu .inner_li span').live('click',function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var mediaSourceID = $(rightClickedObject).find(".trackObject").attr("data-media_source");
			
			if($(this).hasClass("show_containing_dir"))
			{
				displayLoading();
				
				//un-highlight the selected folder			
				if(activeNode = $("#folderlist").dynatree("getTree").getActiveNode())
					activeNode.deactivate();
					
				//TODO: make this somehow select the correct folder in the tree?
				//	 tree.loadKeyPath(keyPath, callback) won't work as it requires the secret ID things
				
				$.ajax({
					cache: false,
					url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listDirContents&apikey="+apikey+"&apiver="+apiversion,
					type: "GET",
					data: { 
						'dir' : $(rightClickedObject).find(".trackObject").attr("data-dir"), 
						'mediaSourceID' : mediaSourceID
					},
					complete: function(jqxhr, status) {},
					error: function(jqxhr, status, errorThrown) {
						alert("AJAX ERROR - check the console!");
						console.error(jqxhr, status, errorThrown);
					},
					
					success: function(data, status, jqxhr) {

						refreshFileListState();
						
						//$("#tracklistHeader").text($("#mediaSourceSelector option:selected").text()+""+(folderName==""?"/":folderName));
						$("#tracklistHeader").text($("#mediaSourceSelector option[value='"+mediaSourceID+"']").text()+""+data.CurrentPath);

						//add files
						var appendTarget=$("<ol id='tracklist' />");
						for (file in data.Files)
						{	
							addTrackToFileList(data.Files[file], data.CurrentPath, mediaSourceID, appendTarget);
						}
						$("#tracklist").replaceWith(appendTarget);
						addTrackClickHandlers();
					}
				});
			}
			else if($(this).hasClass("add_to_playlist"))
			{
				$(rightClickedObject).find("a.addToPlaylistButton").click();
			}
			else if($(this).hasClass("del_from_playlist"))
			{
				$(rightClickedObject).find(".removeFromPlaylist").click();
			}
			else if($(this).hasClass("play_now"))
			{
				$(rightClickedObject).find(".playNowButton, .playNow").click();
			}
			else if($(this).hasClass("download"))
			{
				doDownloadOfTrack(rightClickedObject);
			}
			else if($(this).hasClass("downcode_streamer"))
			{
				var trackObject = $(rightClickedObject).find(".trackObject");
				var		remote_filename = $(trackObject).attr("data-filename"),
						remote_directory = $(trackObject).attr("data-dir"),
						remote_mediaSource = $(trackObject).attr("data-media_source");
				
				var url = g_ultrasonic_basePath+"/backend/rest.php"+"?action=getStream"+
											"&filename="+encodeURIComponent(remote_filename)+
											"&dir="+encodeURIComponent(remote_directory)+
											"&mediaSourceID="+encodeURIComponent(remote_mediaSource)+
											"&streamerID="+$(this).attr("data-streamerid")+
											"&apikey="+apikey+
											"&apiver="+apiversion;
				window.location = url;
			}
			
		});
	
		$('#trackMenu .first_li').live('click',function() {
			if( $(this).children().size() == 1 ) {
				hideContextMenu();
			}
		});

		$('#trackMenu .inner_li span').live('click',function() {
			hideContextMenu();
		});


		$(".first_li , .sec_li, .inner_li span").hover(function () {
			if ( $(this).children().size() >0 )
					$(this).find('.inner_li').show();	
					$(this).css({cursor : 'default'});
		}, 
		function () {
			$(this).find('.inner_li').hide();
		});
	}
	
	function setupContextMenu(e, file)
	{
		e.preventDefault();
		//dynamically update submenu
		$("#trackMenu_downcodestreamers").empty();
		
		for ( x in file.streamers)
		{
			$("#trackMenu_downcodestreamers").append(
				$("<span/>")
					.addClass("downcode_streamer")
					.text(file.streamers[x].extension)
					.attr("data-streamerID",file.streamers[x].streamerID)
			);
		}
		
		$('<div class="overlay"></div>').css({left : '0px', top : '0px',position: 'absolute', width:'100%', height: '100%', zIndex: '1000' })
			.click(function() {
				hideContextMenu();
			}).bind('contextmenu' , function(e){
				e.preventDefault();
				hideContextMenu();
			})
			.appendTo(document.body);
		
		$("#trackMenu").css({ left: e.pageX, top: e.pageY, zIndex: '1001' }).show();

		return false;
	}
	
	function hideContextMenu()
	{
		$("#trackMenu .hideInPlaylist, #trackMenu .hideInTracklist").show();
		$('#trackMenu').hide();
		$('.overlay').hide();
	}
	
	/**
		Add a track to the list of playable files (centre container)
	*/
	function addTrackToFileList(file, folderName, mediaSourceID, appendTarget)
	{
		var className = (file.streamers.length == 0)?"unplayable":"playable";
				
		$("<li></li>").append(
				$("<input type='checkbox' name='trackCheckbox'/>")
			).append(
				$("<a href='javascript:;' class='addToPlaylistButton'>+</a>")
			).append(
				$("<a href='javascript:;' class='playNowButton'>P</a>")
			).append(
				$("<a href='javascript:;' class='downloadButton'>D</a>")
			).append(
				$("<span></span>")
					.text(file.displayName)
					.addClass("trackName")
					.addClass("trackObject")
					.attr("data-dir", folderName+"/")
					.attr("data-filename", file.filename)					
					.attr("data-streamers", JSON.stringify(file.streamers))
					.attr("data-media_source", mediaSourceID)
			)
			.addClass(className)
			.bind('contextmenu', function(e){
				e.preventDefault();
				rightClickedObject = this;
				$("#trackMenu .hideInTracklist").hide();
				return setupContextMenu(e,file)
			})
			.appendTo(appendTarget);
	}
	
	/**
		Make the jquery player play a track from the passed object
	*/
	function play_jPlayerTrack(trackObject)
	{
		
		var		remote_filename = $(trackObject).attr("data-filename"),
				remote_directory = $(trackObject).attr("data-dir"),
				remote_streamers = $(trackObject).attr("data-streamers"),
				remote_mediaSource = $(trackObject).attr("data-media_source");
		
		$("#playlistTracks .jPlaying").removeClass("jPlaying");
		
		$(trackObject).addClass("jPlaying");
		
		var streamerObject = $.parseJSON(remote_streamers), mediaObject = {}, mediaType="a";
		for(var x=0; x<streamerObject.length; ++x)
		{
			mediaObject[streamerObject[x].extension] = g_ultrasonic_basePath+"/backend/rest.php"+"?action=getStream"+
														"&filename="+encodeURIComponent(remote_filename)+
														"&dir="+encodeURIComponent(remote_directory)+
														"&mediaSourceID="+encodeURIComponent(remote_mediaSource)+
														"&streamerID="+streamerObject[x].streamerID+
														"&apikey="+apikey+
														"&apiver="+apiversion;
			mediaType = streamerObject[x].mediaType=="v"?"v":"a";
		}
		
		showHideVideoPane(mediaType);
		
		if(mediaType=="a")
		{
			initialProgressEvent=true;
			$("#jquery_jplayer_1").jPlayer( "setMedia", mediaObject).jPlayer("play");
		}
		else
		{
			//play event is actually handled by the progress event
			initialProgressEvent=false;
			$("#jquery_jplayer_1").jPlayer( "setMedia", mediaObject ).jPlayer("load");
		}
		
	}
	
	/**
		Shows or hides the videopane depending on the passed mediaType (a/v)
	*/
	function showHideVideoPane(mediaType)
	{
			//is only based on the last mediaType:
		if(mediaType=="v")
		{
			$("#centerPlayerContainer").height("auto");
			$("#centerTrackContainer").css({ 'bottom': $("#centerPlayerContainer").height() });
		}
		else
		{
			$("#centerPlayerContainer").height(3);
			$("#centerTrackContainer").css({ 'bottom':"3px"});
		}

	}
	
	/**
		Adds the handlers to the track list in the "album view"
	*/
	function addTrackClickHandlers()
	{

		var parentElement = $("#tracklist").children("li");
		parentElement.children("a.downloadButton").click(function(){
			doDownloadOfTrack($(this).parent());
		});
		
		parentElement.children("a.addToPlaylistButton").click(function(){
			var parentObj = $(this).parent(),
				trackTagObject = parentObj.children(".trackObject");
			
			if(parentObj.hasClass("unplayable"))
				return false;
			
			trackObject = {
				'text': $(trackTagObject).text(),
				'filename': $(trackTagObject).attr("data-filename"),
				'dir': $(trackTagObject).attr("data-dir"),
				'streamers': $(trackTagObject).attr("data-streamers"),
				'media_source': $(trackTagObject).attr("data-media_source")
			}
			
			addToNowPlaying(trackObject);
			saveNowPlaying();
			addNowPlayingClickHandlers();
		});
		
		parentElement.children("a.playNowButton").click(function(){
			var parentObj = $(this).parent(),
				trackTagObject = parentObj.children(".trackObject");
			
			if(parentObj.hasClass("unplayable"))
				return false;
			
			trackObject = {
				'text': $(trackTagObject).text(),
				'filename': $(trackTagObject).attr("data-filename"),
				'dir': $(trackTagObject).attr("data-dir"),
				'streamers': $(trackTagObject).attr("data-streamers"),
				'media_source': $(trackTagObject).attr("data-media_source")
			}
			
			addToNowPlaying(trackObject);
			saveNowPlaying();
			addNowPlayingClickHandlers();
			
			$("#playlistTracks li:last a.playNow").click();
			
		});
		
		$( "#tracklist li" ).draggable({
			appendTo: "body",
			helper: function(event) {
				return $("<div class='draggingTrack'></div>");
			}, 
			cancel: ".unplayable",
			cursor: "move",
			cursorAt: {
				top: 16,
				left: 16
			}
		});
		
		/** Make the playlist drag-sortable & Droppable*/
		$("#playlistTracks").droppable({
			accept: ":not(.ui-sortable-helper)",	//make sure that if its being rearranged this doesn't count as a drop
			activeClass: "playlistDropZoneActive",
			hoverClass: "playlistDropZoneHover",
			drop: function( event, ui ) {
				$( this ).find( ".placeholder" ).remove();
				
				//TODO: Extract the metadata information for the track and clone it as well as the filename etc
				//or some sort of deep clone:
				//$(ui.draggable).clone().appendTo(this);
				var trackTagObject = $(ui.draggable).children(".trackObject");
							
				trackObject = {
					'text': $(trackTagObject).text(),
					'filename': $(trackTagObject).attr("data-filename"),
					'dir': $(trackTagObject).attr("data-dir"),
					'streamers': $(trackTagObject).attr("data-streamers"),
					'media_source': $(trackTagObject).attr("data-media_source")
				}
				
				addToNowPlaying(trackObject);
				saveNowPlaying();
				addNowPlayingClickHandlers();
			}
		}).sortable({
			items: "li:not(.placeholder)",
			sort: function() {
				// gets added unintentionally by droppable interacting with sortable
				// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
				$( this ).removeClass( "ui-state-default" );
				$( this ).removeClass( "playlistDropZoneActive");
			}
		});
		
		addNowPlayingClickHandlers();
		
	}
		
	/**
		Click Handler for tracks in the playlist
	*/
	function addNowPlayingClickHandlers()
	{
		$("#playlistTracks li a").unbind("click");
		$("#playlistTracks li a.playNow").click(function(){
			play_jPlayerTrack(this);
		});
		$("#playlistTracks li a.removeFromPlaylist").click(function(){
			//play_jPlayerTrack(this);
			$(this).parent().remove();
			saveNowPlaying();
		});
	}
	
	/**
		Refactored out of the click handler into a separate function :)
	*/
	function doDownloadOfTrack(trackSrc)
	{
		
		var trackObject = $(trackSrc).find(".trackObject");
		var     remote_filename = $(trackObject).attr("data-filename"),
				remote_directory = $(trackObject).attr("data-dir"),
				remote_streamers = $(trackObject).attr("data-streamers"),
				remote_mediaSource = $(trackObject).attr("data-media_source");
														 

		var url = g_ultrasonic_basePath+"/backend/rest.php"+"?action=downloadFile"+
				"&filename="+encodeURIComponent(remote_filename)+
				"&dir="+encodeURIComponent(remote_directory)+
				"&mediaSourceID="+encodeURIComponent(remote_mediaSource)+
				"&apikey="+apikey+
				"&apiver="+apiversion;
				
		window.location = url;
	}
	
	/**
		Actually updates the folder browser with content
	*/
	function updateFolderBrowser(mediaSourceID, node)
	{
		var clearAllNodes=false;
		var folderName="/";
		
		if(typeof node === "undefined")
		{
			node = $("#folderlist").dynatree("getRoot");
			clearAllNodes=true;
		}
		else
		{
			//build tree to the node
			//getKeyPath ?
			node.visitParents(function(p_node){
				if(p_node.data.title)
					folderName = "/"+ p_node.data.title + folderName;
			},true);
			clearAllNodes = true;
		}
			
		$.ajax({
			cache: false,
			url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listDirContents&apikey="+apikey+"&apiver="+apiversion,
			type: "GET",
			data: { 
				'dir' : folderName, 
				'mediaSourceID' : mediaSourceID
			},
			complete: function(jqxhr, status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX ERROR - check the console!");
				console.error(jqxhr, status, errorThrown);
				node.setLazyNodeStatus(DTNodeStatus_Error, {
					tooltip: status,
					info: status
				});
			},
			
			success: function(data, status, jqxhr) {
				$("#tracklistHeader").text($("#mediaSourceSelector option:selected").text()+""+(folderName==""?"/":folderName));
				
				//directory handling
				var res = []
				for (var x=0; x<data.Directories.length; ++x)
				{
					res.push({
						title:	data.Directories[x],
						isFolder: true,
					//	icon:	true,
						isLazy:	true
					});
				}
				if(clearAllNodes)
					node.removeChildren();
				
				node.setLazyNodeStatus(DTNodeStatus_Ok);
				node.addChild(res);
				
				refreshFileListState();

				//add files
				var appendTarget=$("<ol id='tracklist' />");
				for (file in data.Files)
				{	
					addTrackToFileList(data.Files[file], folderName, mediaSourceID, appendTarget);
				}
				$("#tracklist").replaceWith(appendTarget);
				addTrackClickHandlers();
			}
		});
	}
		
	/**
		Get the list of media Sources from the backend
	*/
	function getMediaSources()
	{
		$.ajax({
			cache: false,
			url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listMediaSources&apikey="+apikey+"&apiver="+apiversion,
			type: "GET",
			complete: function(jqxhr,status) {},
			error: function(jqxhr, status, errorThrown) {
				
				//if not logged in, display the login form
				if(jqxhr.status==401)
					doLogin();
			},
			success: function(data, status, jqxhr) {		
				$("#mediaSourceSelector, #search_mediaSourceSelector").empty();
				$("#search_mediaSourceSelector").append("<option value='all'>All</option>");
				for (var x=0; x<data.length; ++x)
				{
					$("#mediaSourceSelector, #search_mediaSourceSelector").append(
						$("<option>").val(data[x].mediaSourceID).text(data[x].displayName)
					);
				}
				$("#mediaSourceSelector").change();
			},
		});	
	}
	
	function displayLoading()
	{
		refreshFileListState();
		$("#tracklistHeader").html("Loading..."+"<img src='img/ajax.gif' alt='loading' class='loading' />");
	}	
	
	function refreshFileListState()
	{
		$("#selectAll_inputs").attr("checked",false);
		$("#tracklist").empty();
		$("#centerTrackContainer").scrollTop(0);
	}
	
	/**
		Search the backend for media
	*/
	function searchForMedia(mediaSourceID,dir,query)
	{
		displayLoading();
		$.ajax({
			cache: false,
			url: g_ultrasonic_basePath+"/backend/rest.php",
			data: {
				'apikey':			apikey,
				'apiver':			apiversion,
				'action':			"search",
				'mediaSourceID':	mediaSourceID,
				'query':			query,
				'dir':				dir	
			},
			type: "GET",
			complete: function(jqxhr,status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX Error - check the console");
				console.error(jqxhr, status, errorThrown);
			},
			success: function(data, status, jqxhr) {	
			
				refreshFileListState();
				
				$("#tracklistHeader").text("Search Results Within "+$("#search_mediaSourceSelector option:selected").text()+" For: "+query);
				var appendTarget=$("<ol id='tracklist' />");
				for (var x=0; x<data.length; ++x)
				{
					//data[x].mediaSourceID
					//data[x].results.dirs
					for (var fx=0; fx<data[x].results.files.length; ++fx)
					{
						addTrackToFileList(data[x].results.files[fx].fileObject, data[x].results.files[fx].path, data[x].mediaSourceID, appendTarget);
					}
				}
				$("#tracklist").replaceWith(appendTarget);
				
				addTrackClickHandlers();
				
				//reset the search bar
				$("#search_mediaSourceSelector option[value='all']").attr("selected","selected");
				$("#search_query").val("");
			},
		});	
	}
	
	/**
		display and handle the login form if required
	*/
	function doLogin()
	{
		$("#loginForm").submit(function(){	//handle pressing enter
			ajaxLogin();
			return false;
		});
		//present the login form:
		$("#loginFormContainer").dialog({
			autoOpen: true,
			modal: true,
			title: 'Login',
			buttons: {
				'Login': ajaxLogin
			}
		});
	}
	
	
	function ajaxLogin()
	{
	
		var hash = new jsSHA($("#passwordInput").val()).getHash("SHA-256","B64");
		$.ajax({
			url:'backend/rest.php?action=login&apikey='+apikey+"&apiver="+apiversion,
			type: 'POST',
			data: {
				'username': $("#username").val(),
				'password': hash
			},
			success: function(){
				currentUserName = $("#username").val();
				$("#topBarContainer span.username").text("Logged in as: "+currentUserName+" | ");
				$("#loginFormContainer").dialog("close");
				getMediaSources();
			},
			error: function(jqhxr,textstatus,errorthrown){
				console.debug(jqhxr,textstatus,errorthrown);
				alert("Login Failed");							
			}
		});
		
	}
})();
