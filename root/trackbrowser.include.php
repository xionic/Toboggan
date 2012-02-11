<div id='centerTrackContainer'>
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
	<div class='containerPadding'>
		<h1 id='tracklistHeader'></h1>
		<div id='trackListAddControls'>
			<a href='#' id='addSelectedToPlaylist'>Add Selected to Playlist</a>
		</div>
		<ol id='tracklist' class='' >
		</ol>
	</div>
</div>
<div id='centerPlayerContainer'>
	<div id="jquery_jplayer_1"></div>
</div>
