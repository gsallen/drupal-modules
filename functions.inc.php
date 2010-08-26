<?php
/*
 * Custom functions for the MIFO drupal installation.
 * Jacob Sanford
 * Summer 2010 
 */

/*
 * Function to hook into profile data for display.
 * Content display is handled in user-profile.tpl.php 
 */
function mifo_custom_functions_profile_alter(&$account)
{
	drupal_set_title($account->profile_fullname);
}


/*
 * Function to hook into node views for custom types.
 * All Content Modifications Should be Done Here! 
 */
function mifo_custom_functions_nodeapi(&$node, $op, $teaser = NULL, $page = NULL)
{
	// print_r($node);
	switch ($op) {


		case 'view':

			// Here for legacy, could probably nuke.

		break;


		// 'alter' - seems to be the only hook case that allows $node->body to be modified.
		case 'alter':

			if($node->type == 'node_gallery_image') {
				$permissionsstring=str_replace("Images","Image",get_parent_node_permissions($node->gid));
				$curUserName=getUserFirstLastName($node->uid);
				$curUserLink=l(t($curUserName),"user/$node->uid");
				$sourceheaderblurb="<div style='margin:-10px 0 15px 0; padding: 0 0 0 0;'>Image Source : $curUserLink<div style='font-style:italic;'>$permissionsstring</div></div>";
				$node->body=$sourceheaderblurb.$node->body;
			}

			// Node_gallery
			if($node->type == 'node_gallery_gallery') {

				// Hook if teaser generated content
				if($node->content['field_permissions']['field']['#teaser']==1){
					$node->teaser.="<div>(";
					$node->teaser.=l(t('View All Photos'),"node/$node->nid");
					$node->teaser.=")</div>";
				}

				// Hook if full content
				if($node->content['field_permissions']['field']['#teaser']!=1){
					$permissions_string=$node->field_permissions[0][value];
					$curUserName=getUserFirstLastName($node->uid);
					$curUserLink=l(t($curUserName),"user/$node->uid");
					$sourceheaderblurb="<div style='margin:-10px 0 15px 0; padding: 0 0 0 0;'>Image Source : $curUserLink<div style='font-style:italic;'>$permissions_string</div></div>";
					$node->body=$sourceheaderblurb.$node->body;
				}

			}

			// Book Pages
			if($node->type == 'book') {
				// print_r($node);
				// Build Section Number And Set Title with It
				$sectionno=node_build_branch_string($node->book[plid],$node->book[weight],$node->nid,$node->book[mlid]);
				drupal_set_title($sectionno.' '.$node->title);


				// Walk through and number graphics .
				$node->body=str_replace("{|SECTIONNO|}",$sectionno,$node->body);			

				// Old Method, CPU Intensive..
				// $node->body=node_number_photos($sectionno,$node->body);

				// Parse References, again CPU Intensive but will leave here
				// $node->body=parse_for_references($node->body);
			}

			// News Items - Add Date
			if($node->type == 'news_item') {
				$postdatestring=date('l jS \of F Y h:i:s A',$node->changed);
				$dateappend="<div style=\"font-style:italic; margin-top:0px;\">$postdatestring</div>";
				$node->body=$dateappend.$node->body;
			}

			// "Ask an Expert" page - Node 47
			// Probably should be changed to check for title or something. Node can change if content
			// gets imported strangely to another server.
			if($node->nid == 47) {
				// Display Previously Asked / Answered Questions
				$node_type = "expert_question";

				$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid DESC",$rid);

				while ($u = db_fetch_object($result)) {
					$items[] = l($u->title,"node/$u->nid");
				}

				$questionstructure=theme('item_list', $items);

				$headerforpreviouslyasked='Previously Answered Questions:';    	      	
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								"<h2>$headerforpreviouslyasked</h2>".
								$questionstructure.
								"</div>";

				$node->body.=$displaybuild;

				// Display Question Ask Form

				// Build form.
				$new_blognode = new stdClass();
				$new_blognode->type = 'expert_question';

				module_load_include('inc', 'node', 'node.pages');
				$formstructure .= drupal_get_form('expert_question_node_form', $new_blognode);

				$headerforform="Ask Your Question Here:";

				$formbuild =	"<div style='margin-top:15px;'>\n".
								"<h2>$headerforform</h2><div style='border:1px dotted grey; padding:5px;'>".
								$formstructure.
								"</div></div>";

				// Append onto body.
				$node->body.=$formbuild;

			}

			// "Teaching Tips" page - Node 13
			if($node->nid == 13) {
				// Display Teaching Tips Below Content
				$node_type = "teaching_tips";

				$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid DESC",$rid);

				while ($u = db_fetch_object($result)) {
					$tipnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
					$tipnode->teaser = drupal_render($tipnode->content);
					$items[] = l($u->title,"node/$u->nid")."<div>$tipnode->teaser</div>";
				}

				$tipstructure=theme('item_list', $items);

				$headerforteachingtips='List of Teaching Tips:';    	      	
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								"<h2>$headerforteachingtips</h2>".
								$tipstructure.
								"</div>";

				$node->body.=$displaybuild;
			}

			// Screencast display page
			if($node->nid == 235) {
				// Display Teaching Tips Below Content

				$node_type = "screencast";

				$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid DESC",$rid);

				while ($u = db_fetch_object($result)) {
					$tipnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
					$tipnode->teaser = drupal_render($tipnode->content);
					$items[] = l($u->title,"node/$u->nid");
				}

				$tipstructure=theme('item_list', $items);

				$headerforteachingtips='List of Tutorial Screencasts:';    	      	
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								"<h2>$headerforteachingtips</h2>".
								$tipstructure.
								"</div>";

				$node->body.=$displaybuild;
			}

			// "News Item Hub" page - Node 61
			if($node->nid == 61) {
				$node_type = "news_item";
				$result = db_query("SELECT node.nid, node.title, node.changed FROM node
									WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid DESC",$rid);

				while ($u = db_fetch_object($result)) {
					$curdateformatted=date('l jS \of F Y | h:i:s A',$u->changed);
					$nodestory = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
					$nodestory->teaser = drupal_render($nodestory->content);
					$items[] = l($u->title,"node/$u->nid").
						"<div>$curdateformatted</div><div>$nodestory->teaser</div>";
				}

				$storystructure=theme('item_list', $items);

				$headerfornewsitems='List of Recent News Items:';    	      	
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								"<h2>$headerfornewsitems</h2>".
								$storystructure.
								"</div>";

				$node->body.=$displaybuild;
			}

			// "Contributors List" page - Node 62
			if($node->nid == 62) {
				// Here we define FIDs - only way to specify what fields are institution
				// and what are full name. INSTALL specific!
				$fullnamefid=3;
				$institutionfid=6;

				// Select statement to get parent values.
				$querystring=	"SELECT pv1.uid, pv1.value AS Organization, pv2.value AS UserName
								FROM profile_values pv1, profile_values pv2
								WHERE pv1.uid=pv2.uid 
								AND pv1.uid IN (
									select uid from (
										select uid, COUNT(uid) as counter from node group by uid
										order by counter desc
									) as topcontribcounter
								)
								AND pv1.fid=$institutionfid
								AND pv2.fid=$fullnamefid";	

				$result = db_query($querystring,$rid);

				while ($u = db_fetch_object($result)) {
					$items[] = l($u->UserName, "user/" . $u->uid)."<br>".$u->Organization;
				}

				$contribustructure=theme('item_list', $items);
				
				$headerforcontriblist='Project Contributors:';    	      	
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								"<h2>$headerforcontriblist</h2>".
								$contribustructure.
								"</div>";

				$node->body.=$displaybuild;
			}

			if($node->nid == 11) {
				// JS Feb 2010
				// Counts top contributors, then displays their
				// necesary values. 
				// Rank is based on node ownership.
				// fids may change on install so variables set.
				$fullnamefid=3;
				$institutionfid=6;
				$rid = 3;

				$querystring=<<<EOT
					SELECT pv1.uid, pv1.value AS Organization, pv2.value AS UserName
					FROM profile_values pv1, profile_values pv2
					WHERE pv1.uid=pv2.uid 
					AND pv1.uid IN (

						select uid from (

							select uid, COUNT(uid) as counter from node group by uid order by counter desc

						) as topcontribcounter

					)

					AND pv1.fid=$institutionfid
					AND pv2.fid=$fullnamefid
EOT;

				$result = db_query($querystring,$rid);

				while ($u = db_fetch_object($result)) {
					if ($u->uid!=71) {
						$items[] = l($u->UserName, "user/" . $u->uid)."<br>".$u->Organization;
					}
				}

				$node->body.="<strong>Active Contributor List</strong>\n";
				$node->body.=theme('item_list', $items);
			}

		break;
	}
}



/*
 * 
 * Alters the form on the 'expert question' node page to redirect back to same page.
 * Normally pops the user to new item created.
 */
function mifo_custom_functions_form_alter(&$form, $form_state, $form_id) {

	switch ($form_id) {

		case 'node_gallery_image_node_form':
			// Hook upload image buttons
			$findstring=' <input id';
			$replacestring='<br><br> <input id';
			$form[fupload][upload_buttons]['#value']=str_replace($findstring,$replacestring,$form[fupload][upload_buttons]['#value']);

			$findstring='Cancel All Uploads';
			$replacestring='Clear Upload Queue';
			$form[fupload][upload_buttons]['#value']=str_replace($findstring,$replacestring,$form[fupload][upload_buttons]['#value']);

			// Hook save button
			$findstring='value="Save"';
			$replacestring='value="Start Uploading Queued Files"';
			$form[buttons][fupload_submit]['#value']=str_replace($findstring,$replacestring,$form[buttons][fupload_submit]['#value']);

			// Hook notification
			$findstring='clicking the "Save" button';
			$replacestring='clicking the "Start Uploading Queued Files" button';
			$form[fupload][upload_info]['#value']=str_replace($findstring,$replacestring,$form[fupload][upload_info]['#value']);

			// Hook notification
			$newstring='<span style="font-style:italic;">';
			$newstring.='To begin adding files to upload, please click on the upload icon below this box.';
			$newstring.='</span>';
			$form[fupload][image]['#value']=$newstring;
		break;

		case 'node_gallery_gallery_node_form':
			$form['title']['#title']="Please describe the collection of images you are about to upload. No images are uploaded in this step - only after selecting 'Save' in this dialog can you click on 'Upload Images' in the next window.  If you need help or clarification about this process, please see the related ".l('Screencast on Contributing Images','node/232').".<br><br>Title of image collection";
			$form['body_field']['body']['#title']="Describe the general theme of this image collection.";
		break;

		case 'expert_question_node_form':
			// Only Limit Input Form if On 'Ask An Expert' Page.
			// Editors need to answer the question in the body of 
			// node.
			if ($form['#action'] == '/node/47') {
				// Set redirect to go back to node 47.
				$form['#redirect'] = 'node/47';

				// Hide the preview button, input format stuff
				// body field, and clear the question title label. 
				unset ($form['buttons']['preview']);
				unset ($form['body_field']['format']);
				unset ($form['body_field']['body']);
				unset ($form['title']['#title']);

				// Hide the teaser split stuff
				$form['body_field']['teaser_js']['#type'] = 'hidden';
				$form['body_field']['teaser_include']['#type'] = 'hidden';
			}
		break;

		// Sidebar fuzzysearch form.
		case 'fuzzysearch_box_form':
		
		break;
		
		
		case 'comment_form':
			// Looks like we do nothing here. Cull?
		break;
	}
}



/*
 * Function to build string representing tree branch numbering of node.
 * uses '.' as a delimiter, but this can be changed.
 */
function node_build_branch_string($plid,$weight,$nid,$mlid) {
	// Get ordered array of parent nodes.
	// This also conveniently returns THIS node 
	// (from pX structure in tables)
	$mlids_to_build=node_get_parents($mlid);

	// For each node, get their rank amongst siblings.
	foreach ($mlids_to_build as $curmlid) {
		$returnrank=node_rank_get($curmlid);

		// Ignore 0 rank - that means it's the top level.
		if ($returnrank!=0) {
			// Build string. We start at the top rank, so just keep tacking
			// on the rank of each parent. 
			$returnstring.=$returnrank.'.';
		} // End 0 rank if check.
	} // End foreach parent node.

	return ($returnstring);
}



/*
 * Function to return node from mlid.
 * Less trashy than I originally thought,
 * But still sloppy.
 */
function get_node($mlid) {
	// Does a replace on link_path value to get node-only.
	$querystring="SELECT REPLACE(menu_links.link_path,'node/','') as nid FROM menu_links where mlid=$mlid";
	$result = db_query($querystring);

	// Loop over results. Again, why is this a loop? 
	while ($u = db_fetch_object($result)) {
		$nidreturn=$u->nid;
	} // End MySQL results looper.

	return($nidreturn);
}



/*
 * Function to return parent from mlid
 */
function get_plid($mlid) {
	// query to get parent node from mlid.
	$querystring="SELECT plid FROM menu_links where mlid=$mlid";

	$result = db_query($querystring);

	while ($u = db_fetch_object($result)) {
		$plidreturn=$u->plid;
	} // End while loop for MySQL result array

	return($plidreturn);
}


/*
 * Function to build string representing tree branch numbering of node
 */
function node_get_parents($mlid) {
	// Query to determine entire parental heritage (lineage?).
	// Lucky for us: Drupal stores this explicitly.
	// Unlucky for us: this means it only supports 9 deep.
	// Very Unlucky for Us: If they add deeper parentage support, or change the table structure - this breaks.

	$querystring="SELECT p1,p2,p3,p4,p5,p6,p7,p8,p9 FROM menu_links where mlid=$mlid";
	$result = db_query($querystring);

	// Loop through all heritage.
	while ($u = db_fetch_object($result)) {
		for ($i=1;$i<=9;$i++) {
			// Build pvar - I think this could be skipped using {} constructs.
			$pvar="p".$i;

			// Ignore heritage that reaches 0 (top).
			if ( $u->$pvar != 0 ) {
				$items[] =  $u->$pvar;
			} // end if $u->$pvar!=0 item adder
		} // end for $i  
	} // end while object/result looper.
	return($items);
}



/*
 * Function to determine the current rank in the weighted structure of the node.
 * NOTE : Needs to secondary sort on node creation date. LEFT Join, etc.
 * JS Feb 2010
 */
function node_rank_get($mlid) {
	$plid=get_plid($mlid);

	// Check that plid not equal zero. If it is, it is top level, which is the. Return 0 immediately
	if ($plid==0) return (0);

	$nid=get_node($mlid);

	// Get other relevant items such as weight, and node id
	$querystring="SELECT menu_links.weight FROM menu_links WHERE menu_links.mlid=$mlid";
	$result = db_query($querystring);

	while ($u = db_fetch_object($result)) {
		$weight=$u->weight;
	} // End while loop fetching weight.		

	// Scheme :
	// First, find out how many of the siblings have the SAME weight as this mlid
	// Then, do a count with those with create dates greater than it
	// This gives you a count of same weight-siblings above it in the structure 
	// Add this to the number of siblings with lighter weights (lower number)
	// and you have a 'current level' section number.
	$querystring=<<<EOT
		SELECT SUM(sum1) AS totalcount FROM 
		(

			SELECT
				(
					/* GET NODES THAT HAVE A CREATE DATE SOONER THAN OURS */
					SELECT count(nid) AS counter FROM node WHERE created >
					(
						/* DETERMINE OUR CREATED DATE */
						SELECT created FROM node WHERE nid=$nid
					)
						
					AND CONCAT('node/',nid) IN 
					(
						/* LINK MLID TO NID */
						/* BLECH - NEEDS CLEANUP */
						SELECT menu_links.link_path AS nid FROM menu_links WHERE mlid IN
						(
							/* DETERMINE MLIDs THAT HAVE SAME PLID AS OURS */
							/* */
							SELECT mlid FROM menu_links WHERE plid=$plid AND weight=$weight
						
						) 

					)

				) AS sum1

			UNION

			SELECT
			(
				SELECT count(plid) AS counter FROM menu_links WHERE plid=$plid AND weight<$weight AND mlid<>$mlid
			)

		) as sumtable
EOT;

	$result=db_query($querystring);

	$row = db_fetch_object($result);

	// +1 because no sibilings would return rank of 0
	return ($row->totalcount+1);
}



/*
 * Function that numbers photos dynamically on each node of book pages. 
 */
function node_number_photos($nodestring,$body) {
	// Define arrays used later. Stores positions of start and end of all IMG tags.
	$startpositionarray=array();
	$endpositionarray=array();

	// Defines tags to search for. In this case <img opens and
	// > closes.
	$starttagtosearch='<img';
	$endtagtosearch='>';

	// Fill array of all positions of start tag.
	$startpositionarray=strallipos($body,$starttagtosearch);

	// Foreach instance of starttag begin found.
	foreach ($startpositionarray as $curpos) {
		// get end position by finding next occurrence of endtag.
		$endpositionarray[]=stripos($body,$endtagtosearch,$curpos);
	} // End foreach build of endposition.

	// Determine how many iterations we need to find/replace for.
	// Also initiate 'offset', which will hold the info required
	// to find-replace based on position multiple times - with 
	// string length changing.
	//
	// Finally, get initial string length.
	$lengthcounter=sizeof($startpositionarray);
	$lastoffset=0;
	$initialstringlength=strlen($body);

	if ($lengthcounter!=0) {
		// Intelligence acquired - data checks out.
		// Mission is a go.
		for ($i=0;$i<$lengthcounter;$i++) {
			// Process:
			// Get length of current find-replace string.
			// Use substr to pull out what that string actually is.
			// Define $imgno to use in output. Always 1 more than $i,
			// so we don't get image 2.3.0
			// Construct new HTML to append to string, and append it.
			// Replace old string with new one based on position integers
			// only. This avoids potential problems with completely dupe 
			// img tags.
			// Update offset to allow next iter to correctly use positions.
			// *** Needs support for IMG tags wrapped by href ones.
			// *** Needs to extract title from alt tag and display.
			$lengthofstring=($endpositionarray[$i]+strlen($endtagtosearch))-$startpositionarray[$i];
			$curimgtag=substr($body,$startpositionarray[$i]+$totaloffset,$lengthofstring);
			$imgno=$i+1;
			$newimgcode="$curimgtag<div>Image $nodestring$imgno</div>";
			$body=substr_replace($body,$newimgcode,$startpositionarray[$i]+$totaloffset,$lengthofstring);
			$totaloffset=strlen($body)-$initialstringlength;
			} // End looping i over lengthcounter. 
		} // Close if lengthcounter!=0
		return ($body);
} // end function node_number_photos



/*
 * Return array of stripos (from PHP.net)
 * Comments, who needs em?
 */
function strallipos($haystack,$needle,$offset = 0) {
	$result = array();
	for($i = $offset; $i<strlen($haystack); $i++){
		$pos = stripos($haystack,$needle,$i);
		if($pos !== FALSE){
			$offset =  $pos;
			if($offset >= $i){
				$i = $offset;
				$result[] = $offset;
			}
		}
	} 
	return $result; 
}



/*
 * Parse body of node for references
 */
function parse_for_references($body) {
	// Define arrays used later. Stores positions of start and end of all tags.
	// Format defined for referencing is {(NODE|TAG|STRING)}
	// 
	// Where:
	// - NODE is the node (book page node) that is being referenced.
	// - TAG is the tag of the media (img, embed, etc)
	// - STRING is a unique identifier inside that tag. Usually filename? This can be
	// changed as the archiving system evolves.
	// - LABEL is a label corresponding to the tag (i.e. img => Image)
	//
	$startpositionarray=array();
	$endpositionarray=array();

	// Defines tags to search for. In this case <img opens and
	// > closes.
	$starttagtosearch='{(';
	$endtagtosearch=')}';

	// Fill array of all positions of start tag.
	$startpositionarray=strallipos($body,$starttagtosearch);

	// Get initial size and set offset for use later.
	$lengthcounter=sizeof($startpositionarray);
	$lastoffset=0;
	$initialstringlength=strlen($body);

	// Foreach instance of starttag begin found.
	// Now we have position of all references, let's parse them all.
	foreach ($startpositionarray as $curpos) {
		// get end position by finding next occurrence of endtag.
		$curendposition=stripos($body,$endtagtosearch,$curpos+$totaloffset);

		// Extract the entire tag and pass to reference generator
		$lengthofstring=($curendposition+strlen($endtagtosearch))-($curpos+$totaloffset);
		$curtag=substr($body,$curpos+$totaloffset,$lengthofstring);

		// Pass!
		$referencegenerated=generate_reference($curtag);

		// Now replace in text 
		$body=substr_replace($body,$referencegenerated,$curpos+$totaloffset,$lengthofstring);
		$totaloffset=strlen($body)-$initialstringlength;
	} // End foreach build of endposition.

	// Finally, return it back
	return($body);
}



/*
 * Function to generate reference from pre-defined tags.
 */
function generate_reference($curtag) {
	// Extract reference input
	$matchresult=explode("|",$curtag);
	$matchresult[0]=str_replace("{(","",$matchresult[0]);
	$matchresult[3]=str_replace(")}","",$matchresult[3]);

	// Validate reference input
	// First, make sure that #1 is a number
	// AND it exists as a node
	// AND the node is a book page
	if (is_numeric($matchresult[0])) {
		$querystring="SELECT node.type, menu_links.mlid, menu_links.plid, menu_links.weight FROM node LEFT JOIN menu_links ON node.nid=REPLACE(menu_links.link_path,'node/','') WHERE node.nid=$matchresult[0]";
		$result=db_query($querystring);
		$r=db_fetch_array($result);
		if ($r) {
			if ($r['type']!="book") {
				return("Node reference is not a book page");
			}	
		} else {
			return("Node reference is not node");
		}
	} else {
		return ("Inproper Node Input Format");
	}

	$sectionno=node_build_branch_string($r['plid'],$r['weight'],$matchresult[0],$r['mlid']);
	$reference=$matchresult[3]." ".$sectionno;

	// If reference type is 'node' then we can dump out now!
	if ($matchresult[1]=='node') {
		return ($reference);
	}

	// If we get here, we need the node body :(
	$querystring="SELECT body FROM node_revisions WHERE nid=$matchresult[0] ORDER BY timestamp DESC LIMIT 1";
	$result=db_query($querystring);
	$r2=db_fetch_array($result);

	// Finally, check the occurrences of the tags 
	// And see if one of them contains the unique string.
	$searchstarttag="<".$matchresult[1];
	$searchendtag=">";

	// Fill array of all positions of start tag.
	$startpositionarray=strallipos($r2->body,$searchstarttag);

	$occurcounter=1;

	// Foreach instance of starttag begin found.
	// Now we have position of all references, let's parse them all.
	foreach ($startpositionarray as $curpos) {
		// Get end position by finding next occurrence of endtag.
		$curendposition=stripos($r2->body,$searchendtag,$curpos);

		// Extract the entire tag and pass to reference generator
		$lengthofstring=($curendposition+strlen($searchendtag))-$curpos;
		$curtag=substr($r2->body,$curpos,$lengthofstring);

		if (strpos($curtag,$matchresult[2])) {
			$foundcounter=$occurcounter;
		}

		$occurcounter++;
	}

	if (trim($foundcounter)!="") {
		$reference=$reference.$foundcounter;
		return($reference);	
	} else {
		return("Unique ID Not Found in Node");
	}
}


/*
 * Returns first and last name of contributor from ID.
 * Relies on fid=3 being full_name. If this breaks, it won't work.
 */
function getUserFirstLastName($id) {
	$nameReturned=db_result(db_query("SELECT value FROM {profile_values} WHERE uid = %d AND fid = 3", $id));
	if (trim($nameReturned)=='')
		return db_result(db_query("SELECT name FROM {users} WHERE uid = %d", $id));
	else {
		return ($nameReturned);
	}
}



function mifo_custom_functions_db_rewrite_sql($query, $primary_table, $primary_field, $args) {
	if ($query == '' && $primary_table == 'n' && $primary_field = 'nid' && empty($args)) {
		// return array('where' => " n.type IN ('node_gallery_gallery') ");
		// Old search alter to do ONLY gallery nodes.
	}
}



function get_parent_node_permissions($nodeid) {
	$field = content_database_info(content_fields('field_permissions'));
	$tabletouse=$field[table];
	$columnname=$field[columns][value][column];
	$content = db_fetch_object(db_query("SELECT $columnname FROM $tabletouse WHERE nid = %d", $nodeid));	
	return($content->{$columnname});
}



function mifo_custom_functions_luceneapi_query_alter($query, $module, $type = NULL) {
	$terms = $query->getQueryTerms();
	if(!empty($terms)) {
		foreach($terms as $term) {
			// print_r($term);
			// if($term instanceof Zend_Search_Lucene_Index_Term) {
				// if (str_word_count($term->text)==1) {
					// $term->text.="~";
					// echo "Fuzzy search added";
				// }
			// }
			// 
				// Force fuzzy search
				// $term->text.="~";
			// }
		}
	}
}


?>