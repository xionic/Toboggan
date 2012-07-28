/**
	Holds the JS used for the administration system
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
		doLogin();
	});

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
			success: function(data,textStatus,jqHXR){	
				currentUserID = jqHXR.getResponseHeader("X-AuthenticatedUserID");
				$("#loginFormContainer").dialog("close");
				displayConfig();
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
						.append($("<li><a href='#tab_welcome'>Welcome</a></li>"))
			//			.append($("<li><a href='#tab_client'>Client</a></li>"))
						.append($("<li><a href='#tab_server_streamers'>Streamers</a></li>"))
						.append($("<li><a href='#tab_server_users'>Users</a></li>"))
						.append($("<li><a href='#tab_server_mediaSources'>Media Sources</a></li>"))
						.append($("<li><a href='#tab_server_log_contents'>View Server Log</a></li>"))
				)
				/*.append(
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
				)*/
				.append(
					$("<div id='tab_welcome'></div>")
						.append($("	<h1>Welcome to 'The administration page'</h1> \
									<p>Please select from the tabs at the top of the page to chose which facet of the application to configure</p>"))
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
				.append(
					$("<div id='tab_server_log_contents'><h1>The last 10KiB of the Server Log</h1><pre id='server_log_contents_target' ></pre></div>")
				)
				.appendTo("body");
		
		$("#configDialog").tabs({
			selected: 0,
			select: function(event, ui){
				
				//TODO: display loading placeholder here
				switch(ui.panel.id)
				{
					case 'tab_server_streamers':
						$(ui.panel).empty();
						$(ui.panel).append("<h1>Add/Remove Streamers</h1>");
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
											$("<a href='#'>Del</a>")
												.button({
													icons: {primary: "ui-icon-circle-minus"},
													text: false
												}).click(function(){
													$(this).parent().remove();
													return false;
												})
											
										)
									);
								}
								
								$(ui.panel)
									.append(outputUL)
									.append($("<a href='#' class='add' >New Streamer</a>")
										.button({
											icons: {primary: "ui-icon-circle-plus"},
											text: true
										})
										.click(function(){
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
													$("<a href='#'>Del</a>")
														.button({
															icons: {primary: "ui-icon-circle-minus"},
															text: false
														})
														.click(function(){
															$(this).parent().remove();
															return false;
														})
												)
											);
											
											return false;
										})
									)
									.append($("<p class='saveBar'/>").append($("<a href='#' class='save'>Save Streamer Settings</a>")
										.button({
											icons: {primary: "ui-icon-circle-check"},
											text: true
										})
										.click(function(event){
											event.preventDefault();
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
										})
									));
							},
							error: function(jqHXR, textStatus, errorThrown){
								alert("An error occurred while retrieving the streamer settings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});
					break;
					case 'tab_server_users':
						updateUserList(ui);
					break;
					case 'tab_server_mediaSources':
						$(ui.panel).empty();
						$(ui.panel).append("<h1>Add/Remove Media Sources</h1>");
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
								
								for (var x=0; x<data.length; ++x)
								{
									//	data[x].mediaSourceID+" "+data[x].path+" "+data[x].displayName
									$(output).append($("<li/>").append(
										$("<input name='id' type='hidden'/>").val(data[x].mediaSourceID),
										$("<input type='text' name='path'/>").val(data[x].path),
										$("<input type='text' name='displayName'/>").val(data[x].displayName),
										$("<a href='#'>Del</a>")
											.button({
												icons: {primary: "ui-icon-circle-minus"},
												text: false
											})
											.click(function(){
												$(this).parent().remove();
												return false;
											})
									));
								}
								$(ui.panel).append(output)
									.append($("<a href='#' class='add'>New Media Source</a>")
										.button({
											icons: {primary: "ui-icon-circle-plus"},
											text: true
										})
										.click(function(){
											$("#tab_server_mediaSources ul").append(
												$("<li/>").append(
													$("<input type='text' name='path' />"),
													$("<input type='text' name='displayName' />"),
													$("<a href='#'>Del</a>")
														.button({
															icons: {primary: "ui-icon-circle-minus"},
															text: false
														}).click(function(){
															$(this).parent().remove();
															return false;
														})
												)
											);
										})
									)
									.append($("<p class='saveBar'/>").append($("<a href='#' class='save'>Save Media Sources</a>")
										.button({
											icons: {primary: "ui-icon-circle-check"},
											text: true
										})
										.click(function(event){
											event.preventDefault();
											var mediaSourceArray = [];
											//build an array of mediaSources
											$("#tab_server_mediaSources ul li").each(function(){
												var newObj = {
													'path':			$(this).children('input[name=path]').val(),
													'displayName':	$(this).children('input[name=displayName]').val()
												};
												if($(this).children('input[name=id]').length>0)
												{
													//include the id
													newObj['mediaSourceID'] = $(this).children('input[name=id]').val();
												}
												mediaSourceArray.push(newObj);
											});
											
											//console.debug(mediaSourceArray);
											
											$.ajax({
												url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=saveMediaSourceSettings&apikey="+apikey+"&apiver="+apiversion,
												type:'POST',
												data: {mediaSourceSettings: JSON.stringify(mediaSourceArray)},
												success: function(data, textStatus, jqXHR){
													$( "#configDialog" ).dialog( "close" );
												},
												error: function(jqHXR, textStatus, errorThrown){
													alert("A mild saving catastrophe has occurred, please check the error log");
													console.error(jqHXR, textStatus, errorThrown);
												}	
											});
										})
									));;
							},
						});	
					break;
					case 'tab_server_log_contents':
						//TODO: Loading placeholder
						$.ajax({
							url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=getApplicationLog&apikey="+apikey+"&apiver="+apiversion,
							type:'POST',
							data: {lastNBytes: 10240},
							success: function(data, textStatus, jqXHR){
								//TODO: probably trim up until the first newline as this line is probably incomplete and just
								//clouds the view
								$("#server_log_contents_target").text(data.logFileText);
							},
							error: function(jqHXR, textStatus, errorThrown){
								alert("A mild loading catastrophe has occurred, please check the error log");
								console.error(jqHXR, textStatus, errorThrown);
							}	
						});
					break;
					default:
						
				}
			}
		}).select(0);
			
		return false;
	}
	
	function updateUserList(ui)
	{
		$.ajax({
			url: g_ultrasonic_basePath+"/backend/rest.php"+"?action=listUsers&apikey="+apikey+"&apiver="+apiversion,
			success: function(data, textStatus, jqXHR){
				
				currentUserID = jqXHR.getResponseHeader("X-AuthenticatedUserID");
				
				$(ui.panel).empty();
				$(ui.panel).append("<h1>Add/Remove and Configure Users</h1>");
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
									case "trafficLimit":
									case "trafficLimitPeriod":
										newinputType = "number";													
									break;
									case "enableTrafficLimit":
									case "enabled":
										newinputType = "checkbox";													
									break;
									case "permissions":
										
										$("#permissionsTarget").remove();
										$("#opt_usr_rightFrameTarget").append(
											$("<h2 class='miniheading'>Permissions</h2>"),
											$("<div id='permissionsTarget' ></div>")
										);
										
										var tabBarContainer = $("<ul/>");
										var tabIndex=0;
										for (permissionCategory in data[lbl])
										{
											var categoryContainer = $("<div/>").attr("id","perm_tab_"+tabIndex);
											tabBarContainer.append($("<li/>")
																.append($("<a/>")
																	.attr("href","#perm_tab_"+tabIndex)
																	.text(permissionCategory)
																)
														);
											
											for (permIndex in data[lbl][permissionCategory] )
											{
												$(categoryContainer).append(
													$("<p>")
														.append($("<label />").text(data[lbl][permissionCategory][permIndex]["displayName"]))
														.append($("<input type='checkbox' />").attr('checked',data[lbl][permissionCategory][permIndex]["granted"]==="Y"))
														.append($("<input type='hidden' />").attr(data[lbl][permissionCategory][permIndex]["id"]))
												);
											}
											categoryContainer.appendTo("#permissionsTarget");
											tabIndex++;
										}
										
										$("#permissionsTarget").prepend(tabBarContainer);
										$("#permissionsTarget").tabs({selected: 0});
										
										
										continue;
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
								$("<button id='opt_usr_input_updateBtn'>Update User</button>")
									.button({
										icons: {primary: 'ui-icon-circle-check'},
										text: true
									}).click(function(e){
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
								$("<button id='opt_usr_input_deleteBtn'>Delete User</button>")
									.button({
										disabled: (currentUserID==$("#opt_usr_input_idUser").val()),
										icons: { primary: 'ui-icon-circle-minus'},
										text: true
									}).click(function(e){
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
													updateUserList(ui);
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
										$("<button id='opt_usr_input_changePasswd_button'>Update User's Password</button>").button({
												icons: { primary: 'ui-icon-circle-check'},
												text: true
											}).click(function(e){
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
														btnObj.text("Update User's Password");
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
							$("<a href='#'>New User</a>")
								.button({
										icons: {primary: "ui-icon-circle-plus"},
										text: true
								}).click(function(e){
									e.preventDefault();
									$("#opt_usr_rightFrameTarget").empty();
									var inputNames = new Array("username","password","email","enabled",
																	"maxAudioBitrate","maxVideoBitrate","maxBandwidth",
																	"enableTrafficLimit","trafficLimit","trafficLimitPeriod");
									var newinputType = "";
									for (x=0;x<inputNames.length;++x)
									{
										switch(inputNames[x])
										{
											case "maxAudioBitrate":
											case "maxVideoBitrate":
											case "maxBandwidth":
											case "trafficLimitPeriod":
											case "trafficLimit":
												newinputType = "number";													
											break;
											case "enableTrafficLimit":
											case "enabled":
												newinputType = "checkbox";													
											break;
											case "password":
												newinputType = "password";
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
										$("<button id='opt_usr_input_addBtn'>Add User</button>")
											.button({
												icons: {primary: "ui-icon-circle-plus"},
												text: true
											})
											.click(function(){
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
														updateUserList(ui);
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
	}
})();
