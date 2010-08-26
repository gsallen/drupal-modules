<?php

/*
 * Custom functions for the Synergies drupal installation.
 * Jacob Sanford
 * March 2010 
 */


/*
 * Function to hook into node views for custom types.
 * All Content Modifications Should be Done Here! 
 */
function atlanticsynergies_custom_functions_nodeapi(&$node, $op, $teaser = NULL, $page = NULL) {
	switch ($op) {
		case 'alter':
			if($node->nid == 23) {
				// EXAMPLE FOR EASY IMPLEMENT
			}
	} // Switch $op
} // Custom_functions_nodeapi


?>