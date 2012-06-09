<div id='trackListHeaderContainer'>	
	<div id='searchContainer'>
		<p>
			<form action='#' id='searchForm' >
				<input type='text' id='search_query' placeholder='Enter your search terms here' name='query' />
				<span class='selectWrapper'><select id='search_mediaSourceSelector' ></select></span>
				<input type='submit' id='search_submitBtn' value='Search' />
				<input type='hidden' id='search_dir'  value='' />
			</form
		</p>
	</div>
	<h1 id='tracklistHeader'></h1>
	<div id='trackListAddControls'>
		<input type='checkbox' name='selectAll' id='selectAll_inputs' />| <a href='#' id='addSelectedToPlaylist'>Add Selected to Playlist</a>
	</div>
</div>
<div id='centerTrackContainer'>
	<ol id='tracklist' class='' >
	</ol>
</div>
<div id='centerPlayerContainer'>
	<div id="jquery_jplayer_1"></div>
</div>
