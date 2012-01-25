/**
	Holds the JS used for the player system, playlist management etc
*/
(function(){
	/**
		jQuery Entry Point
	*/
	$(document).ready(function(){
		/** Setup the jPlayer */
		$("#jquery_jplayer_1").jPlayer({
			ready: function () {
				$(this).jPlayer("setMedia", {
				mp3: "testMP3.mp3",
				});
			},
			swfPath: "./js/jQuery.jPlayer.2.1.0/",
			supplied: "mp3",
			wmode: "window"
		});
		
		/** Make the playlist drag-sortable & Droppable*/
		
		$( "#tracklist li" ).draggable({
			appendTo: "body",
			helper: "clone"
		});
		
		$("#playlistTracks").droppable({
				accept: ":not(.ui-sortable-helper)",	//make sure that if its being rearranged this doesn't count as a drop
				drop: function( event, ui ) {
					$( this ).find( ".placeholder" ).remove();
					
					//TODO: Extract the metadata information for the track and clone it as well as the filename etc
					//or some sort of deep clone:
					//$(ui.draggable).clone().appendTo(this);
					var trackTagObject = $(ui.draggable).children("span.trackName");
					
					$("<li></li>").append(
						$("<a href='javascript:;'></a>")
							.text( trackTagObject.text() )
							.attr( "data-trackpath", trackTagObject.attr("data-trackpath"))
					).appendTo(this);
				}
			}).sortable({
				items: "li:not(.placeholder)",
				sort: function() {
					// gets added unintentionally by droppable interacting with sortable
					// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
					$( this ).removeClass( "ui-state-default" );
				}
			});
		
		addTrackClickHandlers();
		//addFolderClickHandlers();
		updateFolderBrowser();
	});

	/**
		Adds the handlers to the track list in the "album view"
	*/
	function addTrackClickHandlers()
	{
		$("#tracklist").children("li").children("a").click(function(){
			
			var obj = $("<a href='javascript:;'></a>");
			obj.text($(this).parent().children("span.trackName").text());
			
			$("#playlistTracks").append(
				$("<li/>").append(obj)
			);
		});
	}
	
	/**
		Click Handler for folders in the folder browser
	*/
	function addFolderClickHandlers()
	{
		$("#folderlist").children("li").children("a").click(function(){
			alert("Folder Clicked");
			updateFolderBrowser(this);
		});
	}
	
	/**
		Actually updates the folder browser with content
	*/
	function updateFolderBrowser(clickedObj)
	{
	
		var folderName = $(clickedObj).text();
		//retrieve a list of new folders
		$.ajax({
			cache: false,
			url: "list_files.php",
			type: "GET",
			data: { 'dir' : folderName },
			complete: function(jqxhr, status) {},
			error: function(jqxhr, status, errorThrown) {
				alert("AJAX ERROR - check the console!");
				console.error(jqxhr, status, errorThrown);
			},
			success: function(data, status, jqxhr) {
				console.debug(data);
				
				$("#folderlist").empty();
				
				for (dir in data.Directories)
				{
					$("<li></li>").append(
						$("<a href='javascript:;'></a>")
							.text(data.Directories[dir])
					)
					.appendTo($("#folderlist"));
				}
				
				
				//TODO: List files too
				//data.Files
				
				addFolderClickHandlers();
			},
		});
	}
	
})();
