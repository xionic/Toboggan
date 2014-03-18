<div id='trackListHeaderContainer'>	
	<div id='searchContainer'>
		<p>
			<form action='#' id='searchForm' >
				<input type='hidden' id='search_dir'  value='' />
				<input type='text' id='search_query' placeholder='Enter your search terms here' name='query' />
				<span class='selectWrapper'><select id='search_mediaSourceSelector' ></select></span>
				<button id='search_submitBtn' value='Search' >Search</button>
			</form
		</p>
	</div>
	<h1 id='tracklistHeader'></h1>
</div>
<div id='centerTrackContainer'>
	<div id='trackListAddControls'>
		<input type='checkbox' name='selectAll' id='selectAll_inputs' />| <a href='#' id='addSelectedToPlaylist'>Add Selected to Playlist</a>
	</div>
	<ol id='tracklist' class='' >
	</ol>
</div>
<div id='centerPlayerContainer'>
	<div id="jquery_jplayer_1"></div>
</div>
