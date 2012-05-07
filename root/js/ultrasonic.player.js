/**
	Holds the JS used for the player system, playlist management etc
*/
(function(){
	var apikey='{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}',
		apiversion='0.58',
		initialProgressEvent=false,	//used to ensure that the initial progress event is the only one handled
		playerCSSProperties = {},
		isFullscreen = {};
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
			muted: localStorage.getItem("isMuted")=="true"?true:false			
					
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
		
		$("#searchForm").submit(function(event){
			event.preventDefault();
			event.stopPropagation();

			//un-highlight the selected folder			
			$("#folderlist .currentlySelected").removeClass("currentlySelected");
			
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
		$("#configButton_txt").click(displayConfig);
		
		//init for search functionality
		$("#searchButton_txt").click(function(){
			$("#searchContainer").slideDown("fast",function(){
				$("#search_query").focus();
			});
			return false;
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
		
		getMediaSources();
		//load the nowPlaying from localStorage
		loadNowPlaying();
		
		//load jPlayer Inspector
	//	$("#jPlayerInspector").jPlayerInspector({jPlayer:$("#jquery_jplayer_1")});
	});

	/**
		Load the now playing list from HTML5 LocalStorage
	*/
	function loadNowPlaying()
	{
		var nowPlaying = localStorage.getItem("nowPlaying");
		
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
			nowPlaying = [];
		
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
		
		localStorage.setItem("nowPlaying", JSON.stringify(nowPlaying));
	}
	
	/**
		Add a track to the now playing list
	*/
	function addToNowPlaying(trackObject)
	{	
		$("#playlistTracks").append(		
			$("<li></li>").append(
				$("<a href='javascript:;' class='removeFromPlaylist'>-</a>")
			).append(
				$("<a href='javascript:;' class='playNow'></a>")
					.text( trackObject.text )
					.attr( "data-filename", trackObject.filename )
					.attr( "data-dir", trackObject.dir )
					.attr( "data-streamers", trackObject.streamers )
					.attr( "data-media_source", trackObject.media_source )
			)
		);
	}
	
	/**
		Add a track to the list of playable files (centre container)
	*/
	function addTrackToFileList(file, folderName, mediaSourceID)
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
					.attr("data-dir", folderName+"/")
					.attr("data-filename", file.filename)					
					.attr("data-streamers", JSON.stringify(file.streamers))
					.attr("data-media_source", mediaSourceID)
			)
			.addClass(className)
			.appendTo($("#tracklist"));
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
			
			var trackObject = $(this).parent().children("span.trackName");
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
		    		
			window.open(url);	//open in new window
		});
		
		parentElement.children("a.addToPlaylistButton").click(function(){
			var parentObj = $(this).parent(),
				trackTagObject = parentObj.children("span.trackName");
			
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
				trackTagObject = parentObj.children("span.trackName");
			
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
			drop: function( event, ui ) {
				$( this ).find( ".placeholder" ).remove();
				
				//TODO: Extract the metadata information for the track and clone it as well as the filename etc
				//or some sort of deep clone:
				//$(ui.draggable).clone().appendTo(this);
				var trackTagObject = $(ui.draggable).children("span.trackName");
							
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
			}
		});
		
		addNowPlayingClickHandlers();
		
	}
	
	/**
		Click Handler for folders in the folder browser
	*/
	function addFolderClickHandlers()
	{
		//updated to include sub-directory browsing, now slower than before as it
		// is forced to recurse through subdirectories etc, 
		// unbind is now required as the tree structure is now no longer entirely 
		// replaced with the new one
		$("#folderlist li a").unbind('click').click(function(){	
			$("#folderlist .currentlySelected").removeClass("currentlySelected");
			$(this).parent().addClass("currentlySelected");
			updateFolderBrowser($(this).attr("data-media_source"),this);
		});
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
		Actually updates the folder browser with content
	*/
	function updateFolderBrowser(mediaSourceID, clickedObj)
	{
		var folderName = "", appendTarget;
		
		if(clickedObj)	// if it's a subfolder, else it's the root
		{
			folderName = $(clickedObj).attr("data-parent")+""+$(clickedObj).text();
			
			appendTarget = $(clickedObj).siblings("ul.subdir");
			if(appendTarget.length==0)
			{
				appendTarget = $("<ul class='subdir'></ul>");
				$(clickedObj).parent().append(appendTarget);
			}
		}
		else
		{
			appendTarget = $("#folderlist");
		}		
		
		//display loading placeholder
		displayLoading();
		
		//retrieve a list of new folders
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
			},
			success: function(data, status, jqxhr) {
				
				//remove loading placeholder
				$("#tracklist").empty();
				$(appendTarget).empty();
				
				//reset the checkbox in the file header
				$("#selectAll_inputs").attr("checked", false);
				
				$("#tracklistHeader").text($("#mediaSourceSelector option:selected").text()+""+(folderName==""?"/":folderName));
				
				for (dir in data.Directories)
				{
					$("<li></li>").append(
						$("<a href='javascript:;'></a>")
							.text(data.Directories[dir])
							.attr("data-parent", data.CurrentPath)
							.attr("data-media_source", mediaSourceID)
					)
					.appendTo(appendTarget);
				}
				addFolderClickHandlers();

				//data.Files
				for (file in data.Files)
				{	
					addTrackToFileList(data.Files[file], folderName, mediaSourceID);
				}
				addTrackClickHandlers();
				
			},
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
		$("#tracklist").empty().append();
		$("#tracklistHeader").html("Loading..."+"<img src='img/ajax.gif' alt='loading' class='loading' />");
	}	
	
	/**
		Search the backend for media
	*/
	function searchForMedia(mediaSourceID,dir,query)
	{
		displayLoading();
		$.ajax({
			cache: false,
			url: g_ultrasonic_basePath+"/backend/rest.php?apikey="+apikey+"&apiver="+apiversion+"&action=search&mediaSourceID="+mediaSourceID+"&query="+query+"&dir="+dir,
			type: "GET",
			complete: function(jqxhr,status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX Error - check the console");
				console.error(jqxhr, status, errorThrown);
			},
			success: function(data, status, jqxhr) {	
			
				$("#tracklist").empty();
				
				$("#tracklistHeader").text("Search Results Within "+$("#search_mediaSourceSelector option:selected").text()+" For: "+query);
				
				for (var x=0; x<data.length; ++x)
				{
					//data[x].mediaSourceID
					//data[x].results.dirs
					for (var fx=0; fx<data[x].results.files.length; ++fx)
					{
						addTrackToFileList(data[x].results.files[fx].fileObject, data[x].results.files[fx].path, data[x].mediaSourceID);
					}
				}
				addTrackClickHandlers();
				
				//reset the search bar
				$("#searchContainer").slideUp();
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
				$("#loginFormContainer").dialog("close");
				getMediaSources();
			},
			error: function(jqhxr,textstatus,errorthrown){
				console.debug(jqhxr,textstatus,errorthrown);
				alert("Login Failed");							
			}
		});
		
	}
	
	/******************************************************************
		Configuration Functions
	*******************************************************************/
	function displayConfig(event)
	{
	
				
		if($("#configDialog").length==0)
			$("<div id='configDialog' />")
				.append(
					$("<ul id='configTabs'/>")
						.append($("<li><a href='#tab_client'>Client</a></li>"))
						.append($("<li><a href='#tab_server_streamers'>Streamers</a></li>"))
						.append($("<li><a href='#tab_server_users'>Users</a></li>"))
						.append($("<li><a href='#tab_server_mediaSources'>Media Sources</a></li>"))
				)
				.append(
					$("<div id='tab_client'></div>")
						.append($("<fieldset><legend>OptionGroup</legend>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								</fieldset>"))
						.append($("<fieldset><legend>OptionGroup</legend>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								</fieldset>"))
						.append($("<fieldset><legend>OptionGroup</legend>\
								<p><label>Option Label</label><input type='text' /></p>\
								<p><label>Option Label</label><input type='text' /></p>\
								</fieldset>"))
				)
				.append(
					$("<div id='tab_server_streamers'></div>")
				)
				.append(
					$("<div id='tab_server_users'></div>")
				)
				.append(
					$("<div id='tab_server_mediaSources'></div>")
				)
				.appendTo("body");
		
		$("#configDialog").dialog({
			autoOpen: true,
			modal: true,
			title: 'Ultrasonic Configuration - Mockup',
			width: "800px",
			buttons: {
				'Save' : function(){
					
					//get the index of the selectedtab and look it up by the href on the a inside the li
					var index = $("#configDialog").tabs('option','selected'),
						selected = $($("#configDialog ul li").tabs()[index]).find("a").attr('href');
					
					switch(selected)
					{
						case "#tab_server_streamers":
							//build an array of streamers
							var streamersArray = [];
							
							$("#tab_server_streamers ul li").each(function(){
								streamersArray.push({
									'fromExtensions' : $(this).children('input[name=fromExt]').val(),
									'bitrateCmd' : $(this).children('input[name=bitrateCmd]').val(),
									'toExtension' : $(this).children('input[name=toExt]').val(),
									'MimeType' : $(this).children('input[name=outputMimeType]').val(),
									'MediaType' : $(this).children('select[name=outputMediaType]').children('option:selected').val(),
									'command' : $(this).children('input[name=command]').val(),
								})
							});
							
							$.ajax({
								url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=saveStreamerSettings&apikey="+apikey+"&apiver="+apiversion,
								type: 'POST',
								data: {settings: JSON.stringify(streamersArray)},
								success: function(data, textStatus, jqXHR){
									$( "#configDialog" ).dialog( "close" );
								},
								error: function(jqHXR, textStatus, errorThrown){
									alert("A mild saving catastrophe has occurred, please check the error log");
									console.error(jqHXR, textStatus, errorThrown);
								}
							})
						break;
						case 'tab_server_users':
					
						break;
						case 'tab_server_mediaSources':
						
						break;
						case 'tab_client':
						
						break;
						default:
						
					}
				},
				Cancel: function(){
					$( this ).dialog( "close" );
				}
			}
		}).tabs({
			selected: 0,
			select: function(event, ui){
				
				//TODO: display loading placeholder here
				switch(ui.panel.id)
				{
					case 'tab_server_streamers':
						$(ui.panel).empty();
						
						$.ajax({
							url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=retrieveStreamerSettings&apikey="+apikey+"&apiver="+apiversion,
							success: function(data, textStatus, jqXHR){
								
								var outputUL = $("<ul/>");
								
								for (var x=0; x<data.length; ++x)
								{
									outputUL.append(
										$("<li/>").addClass('streamer').append(
											$("<input type='text' name='fromExt' />").val(data[x].fromExtensions),
											$("<input type='text' name='bitrateCmd' />").val(data[x].bitrateCmd),
											$("<input type='text' name='command' />").val(data[x].command),
											$("<input type='text' name='toExt' maxlength='8' />").val(data[x].toExtension),
										//	$("<input type='text' name='outputMediaType' />").val(data[x].MediaType),
											$("<select name='outputMediaType'/>")
												.append(
													$("<option value='a'>Audio</option>").attr('selected',(data[x].MediaType=='a')?'selected':false),
													$("<option value='v'>Video</option>").attr('selected',(data[x].MediaType=='v')?'selected':false)
												),
											$("<input type='text' name='outputMimeType' maxlength='32' />").val(data[x].MimeType),
											$("<a href='#'>Del</a>").click(function(){
												$(this).parent().remove();
												return false;
											})
											
										)
									);
								}
								
								$(ui.panel)
									.append(outputUL)
									.append($("<a href='#' class='add' >Add</a>").click(function(){
											$("#tab_server_streamers ul").append(
												$("<li/>").addClass('streamer').append(
													$("<input type='text' name='fromExt' />"),
													$("<input type='text' name='bitrateCmd' />"),
													$("<input type='text' name='command' />"),
													$("<input type='text' name='toExt' maxlength='8' />"),
													$("<select name='outputMediaType'/>")
														.append(
															$("<option value='a'>Audio</option>"),
															$("<option value='v'>Video</option>")
														),
													$("<input type='text' name='outputMimeType' maxlength='32' />"),
													$("<a href='#'>Del</a>").click(function(){
														$(this).parent().remove();
														return false;
													})
												)
											);
											
											return false;
										})
									);
							},
							error: function(jqHXR, textStatus, errorThrown){
								alert("An error occurred while retrieving the streamer settings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});
					break;
					case 'tab_server_users':
						//TODO: display loading placeholder here
						$.ajax({
							url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listUsers&apikey="+apikey+"&apiver="+apiversion,
							success: function(data, textStatus, jqXHR){
							
								$(ui.panel).empty();
								var userList = $("<select name='userList' id='opt_user_select' />");
								
								for (var intx=0; intx<data.length; ++intx)
								{
									userList.append($("<option></option>")
														.val(data[intx].idUser)
														.text(data[intx].username)
													);
								}

								userList.change(function(){
									$("#opt_usr_rightFrameTarget").empty();
									//TODO: display loading placeholder here
									$.ajax({
										url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=retrieveUserSettings&apikey="+apikey+"&apiver="+apiversion,
										data: { 'userid': $(this).val() },
										success: function(data, textStatus,jqHXR){
											
											//Data driven for now!
											for (lbl in data)
											{	
												var newinputID = "opt_usr_input_"+lbl,
													newinputType = "",
													newinputDisabled = (lbl=="idUser")?true:false;		//Hacks for wierd types
											
												//if it's a type that should be numerical (bandwidth etc set the type to number
												switch(lbl)
												{
													case "maxAudioBitrate":
													case "maxVideoBitrate":
													case "maxBandwidth":
														newinputType = "number";													
													break;
													case "enabled":
														newinputType = "checkbox";													
													break;
													default:
														newinputType = "text";
												}
											
												$("#opt_usr_rightFrameTarget").append(
													$("<p>").append(
														$("<label>").text(lbl).attr("for", newinputID)
													).append(
														$("<input class='opt_usr_input' type='"+newinputType+"'>")
															.attr({
																	"id": newinputID,
																	"name": lbl,
																	"value": data[lbl],
																	"disabled": newinputDisabled,
																	"checked": (lbl=="enabled" && data[lbl]=="1")
																	})
															
													)
												);
											}
											//Add the update button
											$("#opt_usr_rightFrameTarget").append(
												$("<button id='opt_usr_input_updateBtn'>Update</button>").click(function(e){
													e.preventDefault();
													//display indication of it!
													var btnObj = $(this);
													btnObj.text("Saving...");
													btnObj.attr("disabled",true);
													$("#opt_user_select").attr("disabled",true);
													
													var saveData = {};
													$("#opt_usr_rightFrameTarget input").each(function(){
														saveData[$(this).attr("name")] = $(this).val();
														if($(this).attr("type") == "checkbox")
															saveData[$(this).attr("name")] = $(this).attr("checked")?"1":"0";	
													});

													//save the user's settings
													$.ajax({
														url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=updateUserSettings&apikey="+apikey+"&apiver="+apiversion+"&userid="+($("#opt_usr_input_idUser").val()),
														type: "POST",
														data: {
															settings:	JSON.stringify(saveData)
														},
														success: function(data, textStatus,jqHXR){
															btnObj.text("Update");
															btnObj.attr("disabled",false);
															$("#opt_user_select").attr("disabled",false);
														},
														error: function(jqHXR, textStatus, errorThrown){
															alert("An error occurred while saving the user settings");
															console.error(jqXHR, textStatus, errorThrown);
														}
													});
												})
											).append(	//add the delete button
												$("<button id='opt_usr_input_deleteBtn'>Delete User</button>").click(function(e){
													e.preventDefault();
													
													if( confirm("Delete this user?") )
													{
														var btnObj = $(this);
														btnObj.text("Deleting...");
														btnObj.attr("disabled",true);
														$("#opt_user_select").attr("disabled",true);
														
														$.ajax({
															url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=deleteUser&apikey="+apikey+"&apiver="+apiversion+"&userid="+($("#opt_usr_input_idUser").val()),
															type: "POST",
															success: function(data, textStatus,jqHXR){
																btnObj.text("Delete User");
																btnObj.attr("disabled",false);
																$("#opt_user_select").attr("disabled",false);
																alert("User Successfully Deleted");
															},
															error: function(jqHXR, textStatus, errorThrown){
																alert("An error occurred while deleting the user");
																console.error(jqXHR, textStatus, errorThrown);
															}
														});
													}
												})
											)
											.append(	//add the fields to change the password
												$("<div id='opt_usr_input_changePasswd_container' />")
													.append(
														$("<p><label for='opt_usr_input_changePass1'>New Password</label><input type='password' id='opt_usr_input_changePass1' name='opt_usr_input_changePass1' /></p>"),
														$("<p><label for='opt_usr_input_changePass2'>Repeat</label><input type='password' id='opt_usr_input_changePass2' name='opt_usr_input_changePass2' /></p>"),
														$("<button id='opt_usr_input_changePasswd_button'>Update Password</button>").click(function(e){
															e.preventDefault();
															//check the two are the same
															
															if($("#opt_usr_input_changePass1").val() != $("#opt_usr_input_changePass2").val() && $("#opt_usr_input_changePass1").val()!="")
															{
																alert("Passwords are not equal or 0 characters");
																return;
															}
															//sha512 and then submit!
															var passwd = new jsSHA($("#opt_usr_input_changePass1").val()).getHash("SHA-256","B64");
															var btnObj = $(this);
															
															btnObj.text("Updating...");
															btnObj.attr("disabled",false);
															$("#opt_user_select").attr("disabled",false);
															
															$.ajax({
																url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=changeUserPassword&apikey="+apikey+"&apiver="+apiversion+"&userid="+($("#opt_usr_input_idUser").val()),
																type: "POST",
																data: {
																	password:	passwd
																},
																success: function(data, textStatus,jqHXR){
																	btnObj.text("Update Password");
																	btnObj.attr("disabled",false);
																	$("#opt_user_select").attr("disabled",false);
																},
																error: function(jqHXR, textStatus, errorThrown){
																	alert("An error occurred while saving the user settings");
																	console.error(jqXHR, textStatus, errorThrown);
																}
															});

														})

													)
											)
											
										},
										error: function(jqHXR, textStatus, errorThrown){
											alert("An error occurred while retrieving the user settings");
											console.error(jqXHR, textStatus, errorThrown);
										}
									})
								})
								
								$(ui.panel).append(
									$("<div id='opt_usr_leftFrame' />")
										.append(userList)
										.append(
											$("<a href='#'>Add</a>").click(function(e){
												e.preventDefault();
												$("#opt_usr_rightFrameTarget").empty();
												
												var inputNames = new Array("username","password","email","enabled","maxAudioBitrate","maxVideoBitrate","maxBandwidth");
												var newinputType = "";
												for (x=0;x<inputNames.length;++x)
												{
													switch(inputNames[x])
													{
														case "maxAudioBitrate":
														case "maxVideoBitrate":
														case "maxBandwidth":
															newinputType = "number";													
														break;
														case "enabled":
															newinputType = "checkbox";													
														break;
														default:
															newinputType = "text";
													}
													
													newinputID = "opt_usr_input_new"+inputNames[x];
												
													$("#opt_usr_rightFrameTarget").append(
														$("<p>").append(
															$("<label>").text(inputNames[x]).attr("for", newinputID)
														).append(
															$("<input class='opt_usr_input' type='"+newinputType+"'>")
																.attr({
																		"id":		newinputID,
																		"name":		inputNames[x],
																		"value":	'',
																		})
																
														)
													);
												}
												$("#opt_usr_rightFrameTarget").append(
													$("<button id='opt_usr_input_addBtn'>Add</button>").click(function(){
														//display indication of it!
														var btnObj = $(this);
														btnObj.text("Saving...");
														btnObj.attr("disabled",true);
														$("#opt_user_select").attr("disabled",true);
														
														var saveData = {};
														$("#opt_usr_rightFrameTarget input").each(function(){
														
															saveData[$(this).attr("name")] = $(this).val();
															
															if($(this).attr("type") == "checkbox")
																saveData[$(this).attr("name")] = $(this).attr("checked")?"1":"0";
															else if ($(this).attr("name")=="password")
															{
																//SHA256 the password
																saveData[$(this).attr("name")] = new jsSHA($(this).val()).getHash("SHA-256","B64");
															}
														});

														//save the new user
														$.ajax({
															url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=addUser&apikey="+apikey+"&apiver="+apiversion,
															type: "POST",
															data: {
																settings:	JSON.stringify(saveData)
															},
															success: function(data, textStatus,jqHXR){
																btnObj.text("Add");
																btnObj.attr("disabled",false);
																$("#opt_user_select").attr("disabled",false);
															},
															error: function(jqHXR, textStatus, errorThrown){
																alert("An error occurred while adding the user");
																console.error(jqXHR, textStatus, errorThrown);
															}
														});
													})
												);
											})
										)
									)
									.append($("<fieldset id='opt_usr_rightFrameFieldset'><legend>User Details</legend><div id='opt_usr_rightFrameTarget'/></fieldset>"));
								//trigger the change to populate the fieldset
								userList.change();
							
							},
							error: function(jqHXR, textStatus, errorThrown){
								alert("An error occurred while retrieving the user settings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});
					break;
					case 'tab_server_mediaSources':
						$(ui.panel).empty();
						//list mediaSources
						$.ajax({
							cache: false,
							url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=retrieveMediaSourceSettings&apikey="+apikey+"&apiver="+apiversion,
							type: "GET",
							complete: function(jqxhr,status) {},
							error: function(jqxhr, status, errorThrown) {
						
								//if not logged in, display the login form
								if(jqxhr.status==401)
									doLogin();
							},
							success: function(data, status, jqxhr) {		
								//display mediaSources
								//permit update to mediaSources
								var output= $("<ul/>");
								console.log(data);
								for (var x=0; x<data.length; ++x)
								{
									//	data[x].mediaSourceID+" "+data[x].path+" "+data[x].displayName
									$(output).append($("<li/>").append(
										$("<input name='id' type='hidden'/>").val(data[x].mediaSourceID),
										$("<input name='path'/>").val(data[x].path),
										$("<input name='displayName'/>").val(data[x].displayName),
										$("<a href='#'>Del</a>").click(function(){
												$(this).parent().remove();
												return false;
											})
									));
								}
								$(ui.panel).append(output)
										.append($("<a href='#' class='add'>Add</a>").click(function(){
											$("#tab_server_mediaSources ul").append(
												$("<li/>").append(
													$("<input name='path' />"),
													$("<input name='displayName' />"),
													$("<a href='#'>Del</a>").click(function(){
                                            			$(this).parent().remove();
                                               			return false;
                                            		})
												)
											);
										})
								);
							},
						});	
					break;
					case 'tab_client':
					
					break;
					default:
						
				}
			}
		});
			
		return false;
	}
	
})();
