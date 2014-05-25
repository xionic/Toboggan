/**
	Holds the JS used for the administration system
*/
(function(){
	var apikey = '{05C8236E-4CB2-11E1-9AD8-A28BA559B8BC}';
	var apiversion = '0.6';
	var currentUserID = "";
	var ajaxCache = {
		fileTypeSettings: false,
		commandSettings: false,
		fileConverterSettings: false
	};
	var converterSettings = {
		commands: {},
		fileTypes: {},
		converters: {}
	};
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
		$("#loginForm").keypress(function(e) {
			if(e.which === 13)
			{
				ajaxLogin();
				e.preventDefault();
			}
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
			success: function(data, textStatus, jqXHR){
				var allowedAdminLogin=false;
				for (var x=0; x < data.permissions.length; ++x)
				{
					//3 == is Administrator
					if(data.permissions[x].id==3 && data.permissions[x].granted=='Y')
					{
						allowedAdminLogin=true;
						break;
					}
				}
				
				if(allowedAdminLogin)
				{
					$("#loginFormContainer").dialog("close");
					displayConfig();
				}
				else
				{
					alert("Administrative access has not been granted by the remote server");
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				console.debug(jqXHR, textStatus, errorThrown);
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
						.append($("<li><a href='#tab_server_converters'>File Converters</a></li>"))
						.append($("<li><a href='#tab_server_users'>Users</a></li>"))
						.append($("<li><a href='#tab_server_mediaSources'>Media Sources</a></li>"))
						.append($("<li><a href='#tab_server_log_contents'>View Server Log</a></li>"))
				)
				.append(
					$("<div id='tab_welcome'></div>")
						.append($("	<h1>Toboggan Maintenance Page</h1> \
									<p>Please select from the tabs at the top of the page to chose which facet of Toboggan to configure</p>"))
				)
				.append(
					$("<div id='tab_server_converters'></div>")
				)
				.append(
					$("<div id='tab_server_users'></div>")
				)
				.append(
					$("<div id='tab_server_mediaSources'></div>")
				)
				.append(
					$("<div id='tab_server_log_contents'>\
						<h1>The last <input type='number' name='serverLogSize' min='1' max='100' id='serverLogSize' value='5' class='inlineInput'/> KiB of the Server Log <span class='refresh'>Update</span></h1>\
						<pre id='server_log_contents_target' ></pre></div>")
				)
				.appendTo("body");
		
		$("#configDialog").tabs({
			selected: 0,
			select: function(event, ui){
				
				//TODO: display loading placeholder here
				switch(ui.panel.id)
				{
					case 'tab_server_converters':
						$(ui.panel).empty();
						
						//pull down retrieveFileTypeSettings
						$.ajax({
							url: g_Toboggan_basePath+"/backend/rest.php"+"?action=retrieveFileTypeSettings&apikey="+apikey+"&apiver="+apiversion,
							success: function(data, textStatus, jqXHR) {
								ajaxCache.fileTypeSettings = data;
								prepareConverters();
							},
							error: function(jqXHR, textStatus, errorThrown) {
								alert("An error occurred while retrieveFileTypeSettings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});

						//pull down retrieveCommandSettings
						$.ajax({
							url: g_Toboggan_basePath+"/backend/rest.php"+"?action=retrieveCommandSettings&apikey="+apikey+"&apiver="+apiversion,
							success: function(data, textStatus, jqXHR) {
								ajaxCache.commandSettings = data;
								prepareConverters();
							},
							error: function(jqXHR, textStatus, errorThrown) {
								alert("An error occurred while retrieveCommandSettings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});

						//pull down retrieveFileConverterSettings
						$.ajax({
							url: g_Toboggan_basePath+"/backend/rest.php"+"?action=retrieveFileConverterSettings&apikey="+apikey+"&apiver="+apiversion,
							success: function(data, textStatus, jqXHR) {
								ajaxCache.fileConverterSettings = data;
								prepareConverters();
							},
							error: function(jqXHR, textStatus, errorThrown) {
								alert("An error occurred while retrieveFileConverterSettings");
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
							url: g_Toboggan_basePath+"/backend/rest.php"+"?action=retrieveMediaSourceSettings&apikey="+apikey+"&apiver="+apiversion,
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
												url: g_Toboggan_basePath+"/backend/rest.php"+"?action=saveMediaSourceSettings&apikey="+apikey+"&apiver="+apiversion,
												type:'POST',
												data: {mediaSourceSettings: JSON.stringify(mediaSourceArray)},
												success: function(data, textStatus, jqXHR){
													$( "#configDialog" ).dialog( "close" );
												},
												error: function(jqXHR, textStatus, errorThrown){
													alert("A mild saving catastrophe has occurred, please check the error log");
													console.error(jqXHR, textStatus, errorThrown);
												}	
											});
										})
									));;
							},
						});	
					break;
					case 'tab_server_log_contents':
						$("#serverLogSize").change(function(){
							$.ajax({
								url: g_Toboggan_basePath+"/backend/rest.php"+"?action=getApplicationLog&apikey="+apikey+"&apiver="+apiversion,
								type:'GET',
								data: {lastNBytes: (1024*$("#serverLogSize").val())},
								success: function(data, textStatus, jqXHR){
									$("#serverLogSizeDisplay").text($("#serverLogSize").val());
									$("#server_log_contents_target").text(data.logFileText.substring(data.logFileText.indexOf('\n')+1,data.logFileText.length));
								},
								error: function(jqXHR, textStatus, errorThrown){
									alert("An error has occurred loading the server log: " + textStatus + "\n see the js error console for the full error object");
									console.error(jqXHR, textStatus, errorThrown);
								}	
							});
						});
						
						$("#serverLogSize").change();
						
						$(this).find("span.refresh").click(function(){
							$("#serverLogSize").change();
						});
						
					break;
					default:
						
				}
			}
		}).select(0);
			
		return false;
	}
	
	function jsonObjectToTable(jsonObject, jsonObjectSchema, classToAssign, contentCallback, actionCallback) {
		var outputTable = $("<table></table>").addClass("configTable");
		outputTable.addClass(classToAssign);
		var headerRow = $("<tr></tr>");
		for (var heading in jsonObjectSchema[0])
		{
			headerRow.append(
				$("<th></th>").text(jsonObjectSchema[0][heading].displayName)
			);
		}
		headerRow.append($("<th></th>").text(" "));

		outputTable.append(headerRow);
		for (var objectProperty in jsonObject) {
			objectProperty = jsonObject[objectProperty];
			var rowContent = $("<tr></tr>");
			var dataAttributes = {};
			for (var c in objectProperty)
			{
				var tableCell = contentCallback($("<td></td>"), c, objectProperty[c])
				rowContent.append(tableCell);
				dataAttributes["data-"+c] = objectProperty[c];
			}

			rowContent.append(
				$("<td></td>")
					.append($("<a href='#'>Remove</a>")
						.button({
							icons: {primary: "ui-icon-circle-minus"},
							text: false
						})
						.click(function(e){
							e.preventDefault();
							if(actionCallback)
								actionCallback(this, e);
							return false;
						})
						.attr(dataAttributes)
					)
			);

			outputTable.append(rowContent);
		}
		return outputTable;
	}

	function getCommandsAsSelectBox()
	{
		var commandId = $("<select></select>");
		for (var cmd in converterSettings.commands)
		{
			var textToDisplay = converterSettings.commands[cmd].displayName + "(" + converterSettings.commands[cmd].commandID + ")";
			var valueOfOption = converterSettings.commands[cmd].commandID;
			commandId.append(
				$("<option />")
					.text(textToDisplay)
					.val(valueOfOption)
			);
		}
		return commandId
	}
	
	function getFileTypesAsSelectBox()
	{
		var fileTypes = $("<select></select>");
		for (var ft in converterSettings.fileTypes)
		{
			var textToDisplay = converterSettings.fileTypes[ft].extension + "(" + converterSettings.fileTypes[ft].fileTypeID + ")";
			var valueOfOption = converterSettings.fileTypes[ft].fileTypeID;
			fileTypes.append(
				$("<option />")
				.text(textToDisplay)
				.val(valueOfOption)
			);
		}
		return fileTypes;
	}
	
	function prepareConverters()
	{
		if(!ajaxCache.fileTypeSettings || !ajaxCache.commandSettings || !ajaxCache.fileConverterSettings)
			return;

		var content = $("<div></div>");

		for (var x in ajaxCache.fileConverterSettings.data)
			converterSettings.converters[ajaxCache.fileConverterSettings.data[x].fileConverterID] = ajaxCache.fileConverterSettings.data[x];

		for (var y in ajaxCache.commandSettings.data)
			converterSettings.commands[ajaxCache.commandSettings.data[y].commandID] = ajaxCache.commandSettings.data[y];

		for (var z in ajaxCache.fileTypeSettings.data)
			converterSettings.fileTypes[ajaxCache.fileTypeSettings.data[z].fileTypeID] = ajaxCache.fileTypeSettings.data[z];

		//TODO: Refactor these deletes in with the saves below into one method
		var removeConverterCallback = function(obj, e){
			var fc_id = $(obj).attr("data-fileconverterid");
			var saveData = JSON.parse(JSON.stringify(ajaxCache.fileConverterSettings.data));
			for(var idx in saveData) {
				if(saveData[idx].fileconverterid == fc_id){
					saveData.splice(idx, 1);
					break;
				}
			}
			$(obj).button("disable");
			
			$.ajax({
				url: g_Toboggan_basePath + "/backend/rest.php" + "?action=saveFileConverterSettings&apikey=" + apikey + "&apiver=" + apiversion,
				type: "POST",
				data: {
					settings:	JSON.stringify(saveData)
				},
				success: function(data, textStatus,jqXHR){
					$(obj).show();
					$(obj).parent().parent().remove();
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert("An error occurred while saving the user settings");
					console.error(jqXHR, textStatus, errorThrown);
				}
			});
		};
		var removeCommandCallback = function(obj, e){
            var rc_id = $(obj).attr("data-commandid");
			var saveData = JSON.parse(JSON.stringify(ajaxCache.commandSettings.data));
			for(var idx in saveData) {
				if(saveData[idx].commandID == rc_id){
					saveData.splice(idx, 1);
					break;
				}
			}
			
			$.ajax({
				url: g_Toboggan_basePath + "/backend/rest.php" + "?action=saveCommandSettings&apikey=" + apikey + "&apiver=" + apiversion,
				type: "POST",
				data: {
					settings: JSON.stringify(saveData)
				},
				success: function(data, textStatus,jqXHR){
					$(obj).parent().parent().remove();
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert(jqXHR.responseText);
					console.error(jqXHR, textStatus, errorThrown);
				}
			});
		};
		var removeFileTypeCallback = function(obj, e){
            var ft_id = $(obj).attr("data-filetypeid");
			var saveData = JSON.parse(JSON.stringify(ajaxCache.fileTypeSettings.data));
			for(var idx in saveData) {
				if(saveData[idx].fileTypeID == ft_id){
					saveData.splice(idx, 1);
					break;
				}
			}
			
			$.ajax({
				url: g_Toboggan_basePath + "/backend/rest.php" + "?action=saveFileTypeSettings&apikey=" + apikey + "&apiver=" + apiversion,
				type: "POST",
				data: {
					settings: JSON.stringify(saveData)
				},
				success: function(data, textStatus,jqXHR){
					$(obj).parent().parent().remove();
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert(jqXHR.responseText);
					console.error(jqXHR, textStatus, errorThrown);
				}
			});
		};

		var commandTable = jsonObjectToTable(converterSettings.commands, ajaxCache.commandSettings.schema, "commands", function(tableCell, key, value) {
			tableCell.text(value);
			return tableCell;
		}, removeCommandCallback);
		var fileTypeTable = jsonObjectToTable(converterSettings.fileTypes, ajaxCache.fileTypeSettings.schema, "filetypes", function(tableCell, key, value) {
			tableCell.text(value); 
			return tableCell;
		}, removeFileTypeCallback);
		var converterTable = jsonObjectToTable(converterSettings.converters, ajaxCache.fileConverterSettings.schema, "converters", function(tableCell, key, value) {
			var thisID = "fc_" + key;
			switch(key)
			{
				case "fromFileTypeID":
				case "toFileTypeID":
					var ftSelects = getFileTypesAsSelectBox();
					ftSelects.attr("id",thisID);
					ftSelects.attr("name",thisID);
					$(ftSelects).find("option[value=" + value + "]").attr("selected","selected");
					tableCell.append(ftSelects);
				break;
				case "commandID":
					var commandSelect = getCommandsAsSelectBox();
					commandSelect.attr("id", thisID);
					commandSelect.attr("name", thisID);
					$(commandSelect).find("option[value=" + value + "]").attr("selected","selected");
					tableCell.append(commandSelect);
				break;
				default:
					tableCell.text(value);
			}
			return tableCell;
		}, removeConverterCallback);
		
		var newConverterButton = $("<a href='#'>New</a>")
			.button({
				icons: {primary: "ui-icon-circle-plus"},
				text: true
			}).click(function(e){
				$(newConverterButton).hide();
				var contentToInsert = $("<div class='skeletonRow converters'></div>");
				//this is essentially just 3 option->selects
				//FromFileType
				var fromFileType = $("<select name='fromFileType' id='converters_fromFileType'></select>");
				
				//ToFileType
				var toFileType = $("<select name='toFileType' id='converters_toFileType'></select>");
				
				//populate options on To/From
				for (var fileType in converterSettings.fileTypes)
				{
					var fileTypeID = converterSettings.fileTypes[fileType].fileTypeID;
					var extensionName = converterSettings.fileTypes[fileType].extension;
					var textToDisplay = extensionName + " (" + converterSettings.fileTypes[fileType].mimeType + " " + converterSettings.fileTypes[fileType].mediaType + ")";
					fromFileType.append(
						$("<option/>")
							.text(textToDisplay)
							.val(fileTypeID)
					);
					toFileType.append(
						$("<option/>")
							.text(textToDisplay)
							.val(fileTypeID)
					);
				}
				
				var commandObject = getCommandsAsSelectBox();
				commandObject.attr('id', "converters_commandID");
				commandObject.attr('name', "commandID");
				
				contentToInsert.append("<span>FromFileType</span>", fromFileType);
				contentToInsert.append("<span>ToFileType</span>", toFileType);
				contentToInsert.append("<span>Command</span>", commandObject);
				
				var addConverterButton = $("<a href='#'>Add!</a>")
					.button({
						icons: {primary: "ui-icon-circle-check"},
						text: false
					}).click(function(){
						var saveData = JSON.parse(JSON.stringify(ajaxCache.fileConverterSettings.data));
						saveData[saveData.length] = {
							"fromFileTypeID": $("#converters_fromFileType").find(":selected").val(),
							"toFileTypeID": $("#converters_toFileType").find(":selected").val(),
							"commandID": $("#converters_commandID").find(":selected").val()
						};

						$(addConverterButton).button("disable");
						$.ajax({
							url: g_Toboggan_basePath + "/backend/rest.php" + "?action=saveFileConverterSettings&apikey=" + apikey + "&apiver=" + apiversion,
							type: "POST",
							data: {
								settings:	JSON.stringify(saveData)
							},
							success: function(data, textStatus,jqXHR){
								$(newConverterButton).show();
								$("div.skeletonRow.converters").remove();
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert("An error occurred while saving the user settings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});
					});
				contentToInsert.append(addConverterButton);
				
				$("table.configTable.converters").after(contentToInsert);
			});
			
		var newCommandButton = $("<a href='#'>New</a>")
			.button({
				icons: {primary: "ui-icon-circle-plus"},
				text: true
			}).click(function(e){
				$(newCommandButton).hide();
				var contentToInsert = $("<div class='skeletonRow commands'></div>");
				
				contentToInsert.append("<span>Description</span>", $("<input type='text' id='commands_displayName' />"));
				contentToInsert.append("<span>Command</span>", $("<input type='text' id='commands_command' />"));
				
				var addCommandButton = $("<a href='#'>Add!</a>")
					.button({
						icons: {primary: "ui-icon-circle-check"},
						text: false
					}).click(function(){
						var saveData = JSON.parse(JSON.stringify(ajaxCache.commandSettings.data));
						saveData[saveData.length] = {
							"command": $("#commands_command").val(),
							"displayName": $("#commands_displayName").val(),
						};

						$(addCommandButton).button("disable");
						$.ajax({
							url: g_Toboggan_basePath + "/backend/rest.php" + "?action=saveCommandSettings&apikey=" + apikey + "&apiver=" + apiversion,
							type: "POST",
							data: {
								settings:	JSON.stringify(saveData)
							},
							success: function(data, textStatus,jqXHR){
								$(newConverterButton).show();
								$("div.skeletonRow.converters").remove();
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert("An error occurred while saving the user settings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});
					});
				contentToInsert.append(addCommandButton);
				$("table.configTable.commands").after(contentToInsert);
			});

		var newFileTypeButton = $("<a href='#'>New</a>")
			.button({
				icons: {primary: "ui-icon-circle-plus"},
				text: true
			}).click(function(e){
				$(newFileTypeButton).hide();
				var contentToInsert = $("<div class='skeletonRow filetypes'></div>");
				
				contentToInsert.append("<span>Extension</span>", $("<input type='text' id='filetypes_extension' />"));
				contentToInsert.append("<span>MIME Type</span>", $("<input type='text' id='filetypes_mimetype' />"));
				contentToInsert.append("<span>Media Type</span>", $("<input type='text' id='filetypes_mediatype' />"));
				
				var bitrateCmdObject = getCommandsAsSelectBox();
				bitrateCmdObject.attr('id', "fileTypes_bitrateCmdID");
				bitrateCmdObject.attr('name', "bitrateCmdID");
				//bitrateCmdObject.append("<option value=''>None</option>");
				contentToInsert.append("<span>Bitrate Command</span>", bitrateCmdObject);
				
				var durationCmdObject = getCommandsAsSelectBox();
				durationCmdObject.attr('id', "fileTypes_durationCmdID");
				durationCmdObject.attr('name', "durationCmdID");
				//durationCmdObject.append("<option value=''>None</option>");
				contentToInsert.append("<span>Duration Command</span>", durationCmdObject);
				
				var addFileTypeButton = $("<a href='#'>Add!</a>")
					.button({
						icons: {primary: "ui-icon-circle-check"},
						text: false
					}).click(function(){
						var saveData = JSON.parse(JSON.stringify(ajaxCache.fileTypeSettings.data));
						saveData[saveData.length] = {
							"extension": $("#filetypes_extension").val(),
							"mimeType": $("#filetypes_mimetype").val(),
							"mediaType": $("#filetypes_mediatype").val(),
							"bitrateCmdID": $("#fileTypes_bitrateCmdID").find(":selected").val(),
							"durationCmdID": $("#fileTypes_durationCmdID").find(":selected").val()
						};

						$(addFileTypeButton).button("disable");
						$.ajax({
							url: g_Toboggan_basePath + "/backend/rest.php" + "?action=saveFileTypeSettings&apikey=" + apikey + "&apiver=" + apiversion,
							type: "POST",
							data: {
								settings:	JSON.stringify(saveData)
							},
							success: function(data, textStatus,jqXHR){
								$(newConverterButton).show();
								$("div.skeletonRow.converters").remove();
							},
							error: function(jqXHR, textStatus, errorThrown){
								alert("An error occurred while saving the user settings");
								console.error(jqXHR, textStatus, errorThrown);
							}
						});
					});
				contentToInsert.append(addFileTypeButton);
				$("table.configTable.filetypes").after(contentToInsert);
			});
		
		content.append($("<h2>File Converters</h2>"));
		content.append(converterTable);
		
		content.append(newConverterButton);
		
		content.append($("<h2>Commands</h2>"));
		content.append(commandTable);
		content.append(newCommandButton);

		content.append($("<h2>File Types</h2>"));
		content.append(fileTypeTable);
		content.append(newFileTypeButton);

		$("#tab_server_converters").empty().append(content);
	}
	
	function updateUserList(ui)
	{
		function createAndAppendInputsTo(target, newInput) {
			var newHTMLInput = {
				type: "",
				id: "",
				isInteger: false
			};
			switch (newInput.type) {
				case "int":
					newHTMLInput.type = "number";
					newHTMLInput.isInteger = true;
					break;
				case "boolean":
					newHTMLInput.type = "checkbox";
					break;
				case "password":
					newHTMLInput.type = "password";
					break;
				case "text":
				default:
					newHTMLInput.type = "text";
		}

			var newHTMLInputID = "opt_usr_input_new" + newInput.name;
			target.append(
				$("<p>").append(
						$("<label>").text(newInput.displayName).attr("for", newHTMLInputID)
					).append(
						$("<input class='opt_usr_input' type='" + newHTMLInput.type + "'/>")
							.attr({
								"id": newHTMLInputID,
								"name": newInput.name,
								"value": '',
								"readonly": (newInput.readonly ? "readonly" : false)
							})
					)
			);
		}

		$.ajax({
			url: g_Toboggan_basePath+"/backend/rest.php"+"?action=listUsers&apikey="+apikey+"&apiver="+apiversion,
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
						url: g_Toboggan_basePath+"/backend/rest.php"+"?action=retrieveUserSettings&apikey="+apikey+"&apiver="+apiversion,
						data: { 'userid': $(this).val() },
						success: function(data, textStatus,jqXHR){
							
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
										for (var permissionCategory in data[lbl])
										{
											var categoryContainer = $("<div/>").attr("id","perm_tab_"+tabIndex);
											tabBarContainer.append($("<li/>")
																.append($("<a/>")
																	.attr("href","#perm_tab_"+tabIndex)
																	.text(permissionCategory)
																)
														);
											
											for (var permIndex in data[lbl][permissionCategory] )
											{
												$(categoryContainer).append(
													$("<p>")
														.append($("<label />").text(data[lbl][permissionCategory][permIndex]["displayName"]))
														.append($("<input type='checkbox' />")
																	.attr('checked',data[lbl][permissionCategory][permIndex]["granted"]==="Y")
																	.attr("data-permIndex", data[lbl][permissionCategory][permIndex]["id"])
																	.attr("data-permCat", permissionCategory)
																)
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
													"checked": (newinputType=="checkbox" && data[lbl]=="Y")
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
										
										var saveData = { };
										$("#opt_usr_rightFrameTarget>p>input").each(function(){
											saveData[$(this).attr("name")] = $(this).val();
											if($(this).attr("type") == "checkbox")
												saveData[$(this).attr("name")] = $(this).attr("checked")?"Y":"N";	
										});
										
										saveData.permissions = {};
										//do permissions object
										$("#permissionsTarget input[type='checkbox']").each(function(){
											if(!$.isArray(saveData.permissions[$(this).attr("data-permcat")]))
												saveData.permissions[$(this).attr("data-permcat")] = []
												
											saveData.permissions[$(this).attr("data-permcat")].push({
													id:			$(this).attr("data-permindex"),
													granted:	$(this).attr("checked")?"Y":"N"
												});
										});

										//save the user's settings
										$.ajax({
											url: g_Toboggan_basePath+"/backend/rest.php"+"?action=updateUserSettings&apikey="+apikey+"&apiver="+apiversion+"&userid="+($("#opt_usr_input_idUser").val()),
											type: "POST",
											data: {
												settings:	JSON.stringify(saveData)
											},
											success: function(data, textStatus,jqXHR){
												btnObj.text("Update");
												btnObj.attr("disabled",false);
												$("#opt_user_select").attr("disabled",false);
											},
											error: function(jqXHR, textStatus, errorThrown){
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
												url: g_Toboggan_basePath+"/backend/rest.php"+"?action=deleteUser&apikey="+apikey+"&apiver="+apiversion+"&userid="+($("#opt_usr_input_idUser").val()),
												type: "POST",
												success: function(data, textStatus,jqXHR){
													btnObj.text("Delete User");
													btnObj.attr("disabled",false);
													$("#opt_user_select").attr("disabled",false);
													alert("User Successfully Deleted");
													updateUserList(ui);
												},
												error: function(jqXHR, textStatus, errorThrown){
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
													url: g_Toboggan_basePath+"/backend/rest.php"+"?action=changeUserPassword&apikey="+apikey+"&apiver="+apiversion+"&userid="+($("#opt_usr_input_idUser").val()),
													type: "POST",
													data: {
														password:	passwd
													},
													success: function(data, textStatus,jqXHR){
														btnObj.text("Update User's Password");
														btnObj.attr("disabled",false);
														$("#opt_user_select").attr("disabled",false);
													},
													error: function(jqXHR, textStatus, errorThrown){
														alert("An error occurred while saving the user settings");
														console.error(jqXHR, textStatus, errorThrown);
													}
												});

											})

									)
							)
							
						},
						error: function(jqXHR, textStatus, errorThrown){
							alert("An error occurred while retrieving the user settings");
							console.error(jqXHR, textStatus, errorThrown);
						}
					})
				});
				
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

									$.ajax({
										url: g_Toboggan_basePath+"/backend/rest.php"+"?action=getAddUserSchema&apikey="+apikey+"&apiver="+apiversion+"&userid="+($("#opt_usr_input_idUser").val()),
										type: "POST",
										error: function(jqXHR, textStatus, errorThrown){
											alert("An error occurred while deleting the user");
											console.error(jqXHR, textStatus, errorThrown);
										},
										success: function(data, textStatus,jqXHR)
										{
											for(var x in data.schema) {
												if (x == "permissions")
												{
													//create target input thingy
													var permissionsTarget = $("<div id='permissionsTarget'></div>");
													var tabBarContainer = $("<ul/>");
													var tabIndex=0;
													
													for(var permCat in data.schema.permissions)
													{
														var categoryContainer = $("<div/>").attr("id","perm_tab_"+tabIndex);
														tabBarContainer.append($("<li/>")
															.append($("<a/>")
																.attr("href","#perm_tab_"+tabIndex)
																.text(permCat)
															)
														);

														for (var permName in data.schema.permissions[permCat] )
														{
															var newPermissionsInput = data.schema.permissions[permCat][permName];
															newPermissionsInput.name = permName;
															createAndAppendInputsTo(categoryContainer, newPermissionsInput);
														}
														categoryContainer.appendTo(permissionsTarget);
														tabIndex++;
													}
													permissionsTarget.prepend(tabBarContainer);
													permissionsTarget.tabs({selected: 0});
													$("#opt_usr_rightFrameTarget").append(permissionsTarget);
													continue;
												}

												var newInput = data.schema[x];
												newInput.name = x;
												createAndAppendInputsTo($("#opt_usr_rightFrameTarget"), newInput);
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
																saveData[$(this).attr("name")] = $(this).attr("checked")?"Y":"N";
															else if ($(this).attr("name")=="password")
															{
																//SHA256 the password
																saveData[$(this).attr("name")] = new jsSHA($(this).val()).getHash("SHA-256","B64");
															}
														});

														//save the new user
														$.ajax({
															url: g_Toboggan_basePath+"/backend/rest.php"+"?action=addUser&apikey="+apikey+"&apiver="+apiversion,
															type: "POST",
															data: {
																settings:	JSON.stringify(saveData)
															},
															success: function(data, textStatus,jqXHR){
																btnObj.text("Add");
																btnObj.attr("disabled",false);
																$("#opt_user_select").attr("disabled",false);
																updateUserList(ui);
															},
															error: function(jqXHR, textStatus, errorThrown){
																alert("An error occurred while adding the user");
																console.error(jqXHR, textStatus, errorThrown);
															}
														});
													})
											);
										}
									});

								})
						)
					)
					.append($("<fieldset id='opt_usr_rightFrameFieldset'><legend>User Details</legend><div id='opt_usr_rightFrameTarget'/></fieldset>"));
				//trigger the change to populate the fieldset
				userList.change();
			
			},
			error: function(jqXHR, textStatus, errorThrown){
				alert("An error occurred while retrieving the user settings");
				console.error(jqXHR, textStatus, errorThrown);
			}
		});
	}
})();
