// Globals(?)
var bufferpos=null;
var currentplayerstate=null;

$(document).ready(function(){
	// Selector for specific table in 'Manage Captions/Manage Links' area.
	//$('.captionElementLinkContainer > * > * > * > .captionNativePhraseCell').bind("mouseup", nativePortal.Selector.mouseup);
	$('.bindNativeMouseUpSelector').bind("mouseup", nativePortal.Selector.mouseup);
});


function playerReady(obj) {
	player = document.getElementById('jwplayer-1');
	player.addModelListener('TIME', 'timeMonitor');
	player.addModelListener('BUFFER', 'bufferListener');
	player.addModelListener('STATE', 'stateListener');
	// alert($('#startAtTime').html());

	// nativePortalJumpToTimeInVideo(18);
};


function bufferListener(obj) {
		bufferpos=obj.percentage;
}


function stateListener(obj) {
	currentplayerstate=obj.newstate;
	if (currentplayerstate=='PLAYING') {
		nativePortalJumpToTimeInVideo(0);
	}
}


// Updates divs on caption edit page.
function timeMonitor(obj) {
	if ($('.newStartTime') && $('.newEndTime')) {
		// TODO: need to do some modulus stuff or something to make sure this
		// only fires once per second.
		time = obj.position;
		$('.newStartTime').val(nativePortalFormatSubripTimeFromSeconds(parseFloat(time).toFixed(2)));
		$('.newEndTime').val(nativePortalFormatSubripTimeFromSeconds(parseFloat(time).toFixed(2)));
	}
}


// TODO : Not called yet. Skagged from web.
function reloadCaptions(swf, caps) {
	player = document.getElementById('jwplayer-1');

	obj['captions'] = caps;
	thisMovie(swf).addItem(obj, currentItem);
	setTimeout("thisMovie(swf).sendEvent('stop')", 150);
	setTimeout("thisMovie(swf).sendEvent('playitem', currentItem)", 300);
	setTimeout("thisMovie(swf).removeItem(removeItem)", 450);
};


function nativePortalAddCaptionLineToDatabase(nid) {

	// Make sure player isn't running. This updates the time values and could mess up
	// intended inserts.
	if (currentplayerstate == 'PLAYING') {
		alert('The video is currently playing. Please pause it at the point you wish to insert the caption.');
	} else {
		// Set up values to shove at callback.
		var englishPhraseToInsert=$('.newEnglishPhrase').val();
		var nativePhraseToInsert=$('.newNativePhrase').val();
		var startTimeToInsert=$('.newStartTime').val();
		var endTimeToInsert=$('.newEndTime').val();

		var urlToGet= Drupal.settings.basePath + '?q=video/addcaptions/'+nid+'/'+startTimeToInsert+'/'+endTimeToInsert+'/'+englishPhraseToInsert+'/'+nativePhraseToInsert;

		// Post data.
		jQuery.get(urlToGet,{},
		function(data) {
			// On Success (no return):
			if (data == '') {

				// TODO: reload video captions file?

				// Notify of Successful Insert
				alert('Caption Successfully Added!');

				// Clear form boxes and notify on successful inserts.
				$('.newEnglishPhrase').val('');
				$('.newNativePhrase').val('');

				// Clean and repop data table.
				nativePortalClearAndRepopulateCaptionsDataTable(nid);

			} else {
				// On Fail (problem given as return STR):
				alert(data);
			}

		});
	}

	return false;
}


function nativePortalClearAndRepopulateCaptionsDataTable(nid) {

	// Clean both tables.
	$('.captionElementRow').remove();

	// Repop caption elements table.
	var jsonUrlToUse= Drupal.settings.basePath + '?q=video/captions/' + nid + '.json/updateCaptionLink';
	jQuery.getJSON(jsonUrlToUse,
			{
				format: "json"
			},
			function(data) {
				var rowCounter=0;
				jQuery.each(data, function(i,item){

					// This should draw the data from drupal's builder. Shame on me for building a table element myself.
					// Add to manage captions table.
					// TODO: Also this string of appends is not correct. 
					$('.captionElementListContainer > .captionDisplayTable').append($('<tr class="captionElementRow" id="captionLine'+(rowCounter+1)+'"></tr>')
							.append( $('<td onclick="nativePortalJumpToTimeInVideo('+"'"+item.start_time+"'"+');" class="captionElementRow timeJumper"></td>').html(nativePortalFormatSubripTimeFromSeconds(item.start_time)) )
							.append( $('<td onclick="nativePortalJumpToTimeInVideo('+"'"+item.end_time+"'"+');" class="captionElementRow timeJumper"></td>').html(nativePortalFormatSubripTimeFromSeconds(item.end_time)) )
							.append( $('<td class="captionEnglishPhraseCell"></td>').html(item.english_phrase) )
							.append( $('<td class="captionNativePhraseCell"></td>').html(item.native_phrase) )
							.append( $('<td onclick="nativePortalDeleteCaptionRow('+"'"+nid+"','"+(rowCounter)+"'"+');" class="deleteCaptionRow"></td>').html('Delete') )
					);

					// Add to manage links table.
					$('.captionElementLinkContainer > .captionDisplayTable').append($('<tr class="captionElementRow" id="captionLine'+(rowCounter+1)+'"></tr>')
							.append( $('<td onclick="nativePortalJumpToTimeInVideo('+"'"+item.start_time+"'"+');" class="captionElementRow timeJumper"></td>').html(nativePortalFormatSubripTimeFromSeconds(item.start_time)) )
							.append( $('<td onclick="nativePortalJumpToTimeInVideo('+"'"+item.end_time+"'"+');" class="captionElementRow timeJumper"></td>').html(nativePortalFormatSubripTimeFromSeconds(item.end_time)) )
							.append( $('<td class="captionEnglishPhraseCell"></td>').html(item.english_phrase) )
							.append( $('<td class="captionNativePhraseCell bindNativeMouseUpSelector"></td>').html(item.native_phrase) )
					);

					// TODO: Inefficient.
					$('.bindNativeMouseUpSelector').bind("mouseup", nativePortal.Selector.mouseup);
					rowCounter++;

				});

	});


	

}


function nativePortalFormatSubripTimeFromSeconds(secondsval) {
	var t = new Date(1970,0,1);
	t.setSeconds(parseInt(secondsval));
	var s = t.toTimeString().substr(0,8) + String(secondsval).substr(-3);
	return s;
}


function nativePortalJumpToTimeInVideo(timeval) {

	// Check if player state is stopped. If it is, start it.
	if (bufferpos==null) {
		document.getElementById('jwplayer-1').sendEvent('PLAY', timeval);
	}

	// Jump to time.
	document.getElementById('jwplayer-1').sendEvent('SEEK', timeval);

	return('');
}


function setupHighlights(numofdivs,excludenum,colortouse) {

	// Loop over all divs.
	for (curdivid=numofdivs; curdivid>0; curdivid--) {

		// If this is the current div, set it to color. Otherwise, set to transparent.
		if(curdivid==excludenum) {
			document.getElementById( 'captionLine'+curdivid ).style.backgroundColor = colortouse;
		} else {
			document.getElementById( 'captionLine'+curdivid ).style.backgroundColor = 'transparent';
		}
	}
	return('');
}


function updateCaptionLink(entryID,entryTitle,op) {
	$('.dialogTextToLink').val(entryTitle);

	if (op == 'mod') {
		$('.dialogNodeToReference').val(entryID);
	}

	$('#modLinkDialog').css('display','block');

	// Unless we false autoOpen this cannot open twice!
	$('#modLinkDialog').dialog({autoOpen: false});

	$('#modLinkDialog').dialog('open');
}



function addEntryTab(entryID,entryTitle) {

	// See if this tab is already open
	// If so, switch to it instead of adding a new one
	curindex=getIndexForTitle('tabs-tabset', entryTitle );

	// Check if tab is open.
	if (curindex != -1) {
		// Select the tab and move on.
		$('.ui-tabs-nav').tabs('select', curindex);
	} else {
		// else, add tab.
		if ($('.ui-tabs-nav').tabs('length')>6) {
			// Pop off first tab opened before opening new one.
		} 

		// Open new tab.
		var newtab=$('.ui-tabs-nav').tabs('add','?q=entry/cleandefinition/'+entryID,entryTitle);
		$('.ui-tabs-nav').tabs('select', $('.ui-tabs-nav').tabs('length')-1);
	}
}


function getIndexForTitle( tabsDivId, searchedId ) {
	var index = -1;
	var i = 0, els = $("#" + tabsDivId).find("a");
	var l = els.length, e;
	while ( i < l && index == -1 ) {
		e = els[i];
		var tabName=$(e).find("span").html();
		if (searchedId == tabName) {//$(e).attr('href'))
			index = i;
		}
	i++;
	};
	return index;
}


//Selector for caption link editor.
if(!window.nativePortal){
	nativePortal = {};
}


nativePortal.Selector = {};
nativePortal.Selector.getSelected = function(){
	var t = '';
	if(window.getSelection){
		t = window.getSelection();
	}else if(document.getSelection){
		t = document.getSelection();
	}else if(document.selection){
		t = document.selection.createRange().text;
	}
	return t;
}


// See if we 
nativePortal.Selector.mouseup = function(){

	var st = nativePortal.Selector.getSelected();
	if(st!=''){

		$('.dialogTextToLink').val(st);
		$('.dialogNodeToReference').val('');
		$('#addLinkDialog').css('display','block');

		// Unless we false autoOpen this cannot open twice!
		$('#addLinkDialog').dialog({autoOpen: false});
		$('#addLinkDialog').dialog('open');
	}
	return true;
}


/*
 * op = new,update,delete
 */
function manageCaptionLink(op,nid,dialogid) {

	// Validate? Naw. Do it in PHP.
	// Cases
	var urlToGet= Drupal.settings.basePath + '?q=video/captionlinkmanage/' + nid + '/' + op + '/' + $('#' + dialogid + '> * > .dialogTextToLink').val() + '/' + $('#' + dialogid + '> * > .dialogNodeToReference').val();

	// Post data.
	jQuery.get(urlToGet,{},
	function(data) {
		// On Success (no return):
		if (data == '') {

			var successMessage='';

			if (op=='del') {
				successMessage='Link Successfully Removed';
			}

			if (op=='add') {
				successMessage='Link Details Successfully Created';
			}

			// Notify of Successful Insert
			alert(successMessage);
			$('#' + dialogid).dialog('close');

			nativePortalClearAndRepopulateCaptionsDataTable(nid);


		} else {
			// On Fail (problem given as return STR):
			// TODO: Notify of problem and keep box contents.
			alert(data);
		}

	});

}


// Hits a callback and deletes a row from caption file. 
function nativePortalDeleteCaptionRow(nid,capKey) {

	// Since this is a delete, let's make sure.
	var userAgreed=confirm("Are you sure you want to delete the caption row?");

	if (userAgreed) {
		var urlToGet= Drupal.settings.basePath + '?q=video/delcaptions/'+nid+'/'+capKey;

		// Post data.
		jQuery.get(urlToGet,{},
		function(data) {
			// On Success (no return):
			if (data == '') {

				// Clean and repop data table.
				nativePortalClearAndRepopulateCaptionsDataTable(nid);

			} else {
				// On Fail (problem given as return STR):
				alert(data);
			}

		});
	}
}
