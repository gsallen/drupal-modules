<?php
/*
 * Custom functions for the ETC drupal installation.
 * Jacob Sanford
 * March 2010 
 */



/*
 * Function to hook into node views for custom types.
 * All Content Modifications Should be Done Here! 
 */
function etc_custom_functions_nodeapi(&$node, $op, $teaser = NULL, $page = NULL) {

	switch ($op) {

		// Node 'alter' - seems to be the only case that allows $node->body to be modified.
		case 'alter':
			// Book Pages
			if($node->type == 'book') {
				// Type Template
			}


			// Front page.
			// Display Research Areas Below Content
			if($node->nid == 1) {
				$node_type='research_area';

				$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid DESC",$rid);

				while ($u = db_fetch_object($result)) {
					$tipnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
					$tipnode->teaser = drupal_render($tipnode->content);
					$items[] = l($u->title,"node/$u->nid")."<div>$tipnode->teaser</div>";
				}
				$tipstructure=theme('item_list', $items);
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								$tipstructure.
						"</div>";
				$node->content['body']['#value'].=$displaybuild;
			}


			// Display Research Partners Below Content on 'Research Partners Page
			// TODO : Honestly, should be a view.
			if($node->nid == 2) {
				$node_type='research_partner';

				$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid DESC",$rid);

				while ($u = db_fetch_object($result)) {
					$tipnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
					$tipnode->teaser = drupal_render($tipnode->content);
					$items[] = l($u->title,"node/$u->nid")."<div style='margin-bottom:10px;'>$tipnode->body</div>";
				}

				$tipstructure=theme('item_list', $items);
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								$tipstructure.
								"</div>";
				$node->content['body']['#value'].=$displaybuild;  	      	    	      	
			}


			// Staff List Page
			// TODO : Honestly, should be a view.
			if($node->nid == 10) {
				$node_type='staff_member';

				$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid",$rid);

				while ($u = db_fetch_object($result)) {
					$staffnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
					$staffnode->teaser = drupal_render($staffnode->content);
					$items[] = l($u->title,"node/$u->nid").", {$staffnode->field_staff_title[0][safe]}";
				}

				$staffstructure=theme('item_list', $items);
				$displaybuild =	"<div style='margin-top:15px;'>\n".
								$staffstructure.
								"</div>";
				$node->content['body']['#value'].=$displaybuild;
			}


			// Display Research Projects Below Content
			// TODO : Honestly, should be a view.
			if($node->nid == 87) {
				$node_type='research_project';

				$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
									AND node.status = 1 ORDER by node.nid",$rid);

				while ($u = db_fetch_object($result)) {
  					$tipnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
  					$tipnode->teaser = drupal_render($tipnode->content);
  					$items[] = l($u->title,$tipnode->field_link_url[0][safe])."<div style='margin-bottom:10px;'>$tipnode->body</div>";
				}
				
				$tipstructure=theme('item_list', $items);

				$displaybuild =	"<div style='margin-top:15px;'>\n".
								$tipstructure.
								"</div>";
				$node->content['body']['#value'].=$displaybuild;
			}


				// Display News Items Below Content
				// TODO : Honestly, should be a view.
				if($node->nid == 7) {
					$node_type='news_item';

					$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
										AND node.status = 1 ORDER by node.nid",$rid);

				while ($u = db_fetch_object($result)) {
					$tipnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
					$tipnode->teaser = drupal_render($tipnode->content);
					$curbody=check_plain(strip_tags($tipnode->body));
					$items[] = l($u->title,"node/$u->nid")." - ".date("D, F j, Y",$node->created)."\n<div style='margin-bottom:10px;'>$curbody</div>";
				}

				$tipstructure=theme('item_list', $items);

				$displaybuild =	"<div style='margin-top:15px;'>\n".
								$tipstructure.
								"</div>";
				$node->content['body']['#value'].=$displaybuild;
				}


				// Display Collections Below Content
				// TODO : Honestly, should be a view.
				if($node->nid == 5) {
					$node_type='collection';

					$result = db_query("SELECT node.nid, node.title FROM node WHERE node.type = '$node_type'
										AND node.status = 1 ORDER by node.nid",$rid);

					while ($u = db_fetch_object($result)) {
						$tipnode = node_build_content(node_load(array('nid' => $u->nid)), TRUE, FALSE);
						$tipnode->teaser = drupal_render($tipnode->content);
						$curbody=check_plain(strip_tags($tipnode->body));
						$items[] = l($u->title,$tipnode->field_link_url[0][safe])."<div style='margin-bottom:10px;'>$curbody</div>";
					}

					$tipstructure=theme('item_list', $items);
					$displaybuild =	"<div style='margin-top:15px;'>\n".
									$tipstructure.
									"</div>";
					$node->content['body']['#value'].=$displaybuild;
				}

			} // End Switch $op
		} // Custom_functions_nodeapi

?>