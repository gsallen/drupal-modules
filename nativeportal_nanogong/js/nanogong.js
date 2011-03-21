function sendFile(applet_id,extension,nid) {

	var recording_type = $('#recording_type').val();
	recording_type = mytrim(recording_type);
	var filename = nid + '_' + recording_type + '.' + extension;

	var recorder = document.getElementById(applet_id);
	if (recorder == null) {
		alert("Recorder not found.");
		return;
	}

	var duration =parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0;
	if (duration <= 0) {
		alert("No recording found.");
		return;
	}

	var urlToPost= Drupal.settings.basePath + '?q=entry/nanogong_save_filename/'+ nid + '/' + recording_type;
	var msg=recorder.sendGongRequest("PostToForm",urlToPost,'file','cookieblah','sound.wav');

	alert(msg);

	location.reload();
}

function mytrim (myString) {
	return myString.replace(/\n/gi,'').replace(/^\s+/g,'').replace(/\s+$/g,'');
}