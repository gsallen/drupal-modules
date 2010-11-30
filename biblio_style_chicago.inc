<?php
// Original File:       ./cite/styles/cite_Chicago.php
// Original Author(s):  Matthias Steffens <mailto:refbase@extracts.de> and
//                      Richard Karnesky <mailto:karnesky@gmail.com>
//

// This is a citation style file (which must reside within the 'cite/styles/' sub-directory of your refbase root directory). It contains a
// version of the 'citeRecord()' function that outputs a reference list from selected records according to the citation style documented
// in the "Chicago Manual of Style" (2003), and Kate Turabian's "Manual for Writer's of Term Papers, Theses, and Dissertations" (1996)

// Modified for use in biblio by Ron Jerome
//
// $Id: biblio_style_chicago.inc,v 1.4.2.14 2010/05/03 00:57:36 rjerome Exp $
/**
 * Get the style information
 *
 * @return
 *   The name of the style
 */
function biblio_style_chicago_info() {
  return array (
    'chicago' => 'Chicago'
    );
}
function biblio_style_chicago_author_options() {
  $author_options = array(
    'BetweenAuthorsDelimStandard' =>', ',         //4
    'BetweenAuthorsDelimLastAuthor' => ', and ',  //5
    'AuthorsInitialsDelimFirstAuthor' => ', ',     //7
    'AuthorsInitialsDelimStandard'=> ' ',         //8
    'betweenInitialsDelim' => '. ',               //9
    'initialsBeforeAuthorFirstAuthor' => false,   //10
    'initialsBeforeAuthorStandard' => true,       //11
    'shortenGivenNames' => FALSE,                 //12
    'numberOfAuthorsTriggeringEtAl' => 10,         //13
    'includeNumberOfAuthors' => 10,                //14
    'customStringAfterFirstAuthors' => ' et al.',//15
    'encodeHTML' => true
  );

  return $author_options;
}
/**
 * Apply a bibliographic style to the node
 *
 *
 * @param $node
 *   An object containing the node data to render
 * @param $base
 *   The base URL of the biblio module (defaults to /biblio)
 * @param $inline
 *   A logical value indicating if this is being rendered within the
 *   Drupal framwork (false) or we are just passing back the html (true)
 * @return
 *   The styled biblio entry
 */
function biblio_style_chicago($node, $base = 'biblio', $inline = false) {

  // @todo: punctuate output fields, push them onto a list; implode the list, instead of 
  // constantly checking for punctuation & spacing.
  
  // Citation key
  if (!empty($node->biblio_citekey) && variable_get('biblio_display_citation_key', 0)) {
    $output .= '[' . check_plain($node->biblio_citekey) . '] ';
  }

  // Primary author(s)
  // Do not use 'anonymous' for entries without authors; for anonymous works, 
  // Chicago 16th requires the title as a substitute.
  $author_options = biblio_style_chicago_author_options();
  $authors = theme('biblio_format_authors', $node->biblio_contributors[1], $author_options, $inline);
  
  if (! empty($authors)) {
    $output .= '<span class="biblio-authors">' . $authors . '</span>';
    if (! _biblio_style_chicago_field_is_punctuated($authors)) {
      $output .= '.';
    }
    $output .= ' ';
  }
  
  // Primary title
  if (! empty($node->title)) {
    $url = biblio_get_title_url_info($node, $base, $inline);
    
    $title = '<span class="biblio-title-chicago">';
    $title .= l($node->title, $url['link'], $url['options']);
    $title .= '</span>';

    if (! _biblio_style_chicago_field_is_punctuated($node->title)) {
      $title .= '.';
    }

    // quoted title for journal/newspaper/magazine article (102/105/106), thesis (108)
    if (in_array($node->biblio_type, array(102, 105, 106, 108)) ) {
      $output .= '"' . $title . '" ';
    }
    else {
      // italicized title for book chapter, conference paper, default
      $output .= '<i>' . $title . '</i> ';
    }
  }
  
  switch ($node->biblio_type) {
    case 100:
      // Book 
      // series title, series editors, original/translated title, translators
      // editors, edition, volume
      
      // place of publication, publisher, pub year
      $output .= _biblio_style_chicago_format_pubinfo($node);
      break;
      
    case 102: //Journal Article
    case 105: //Newspaper Article
    case 106: //Magazine Article

      // From here on we'll assume that at least either the 'author' or the 'title' field did contain some contents
      // if this is not the case, the output string will begin with a space. However, any preceding/trailing whitespace will be removed at the cleanup stage (see below)

      if (!empty($node->biblio_secondary_title))      // publication (= journal) name
      $output .= " <i>$node->biblio_secondary_title</i>";

      // if there's no full journal name, we'll use the abbreviated journal name
      elseif (!empty($node->biblio_alternate_title))      // abbreviated journal name
      $output .= " <i>$node->biblio_alternate_title</i>";

      if (!empty($node->biblio_volume))      // volume
      $output .= " " . $node->biblio_volume;

      if (!empty($node->biblio_issue))      // issue
      $output .=  ", no. " . $node->biblio_issue;

      if (!empty($node->biblio_year))      // year
      $output .= " (" . $node->biblio_year . ")";

      if (!empty($node->biblio_pages))      // pages
      {
        if (!empty($node->biblio_year) || !empty($node->biblio_volume) || !empty($node->biblio_issue) || !empty($node->biblio_alternate_title) || !empty($node->biblio_secondary_title)) // only add ": " if either year, volume, issue, abbrev_journal or publication isn't empty
        $output .= ": ";

        $output .= theme_biblio_page_number($node->biblio_pages); // function 'formatPageInfo()' is defined in 'cite.inc.php'
      }

      if (!preg_match("/\. *$/", $output)) $output .= ".";

      break;
      
    case 101: //Book Chapter
    case 103: //Conference Paper

      $publication = preg_replace("/[ \r\n]*\(Eds?:[^\)\r\n]*\)/", "", $node->biblio_secondary_title);
      if (!empty($publication))      // publication
      $output .= " In <i>$publication</i>";


      // From here on we'll assume that at least either the 'author' or the 'title' field did contain some contents
      // if this is not the case, the output string will begin with a space. However, any preceding/trailing whitespace will be removed at the cleanup stage (see below)

      if (!empty($node->biblio_contributors[2]))      // editor
      {
        $editor_options = array(
          'BetweenAuthorsDelimStandard'     => ', ',
          'BetweenAuthorsDelimLastAuthor'   => ' and ',
          'AuthorsInitialsDelimFirstAuthor' => ' ',
          'AuthorsInitialsDelimStandard'    => ' ',
          'betweenInitialsDelim'            => '. ',
          'initialsBeforeAuthorFirstAuthor' => TRUE,
          'initialsBeforeAuthorStandard'    => TRUE,
          'shortenGivenNames'               => FALSE,
          'numberOfAuthorsTriggeringEtAl'   => 10,
          'includeNumberOfAuthors'          => 10,
          'customStringAfterFirstAuthors'   => ' et al.',
          'encodeHTML'                      => true
        );

        $editor = theme('biblio_format_authors', $node->biblio_contributors[2], $editor_options, $inline);

        $output .= ", edited by " . $editor;
      }

      if (!empty($node->biblio_pages))      // pages
      $output .= ", " . theme_biblio_page_number($node->biblio_pages); // function 'formatPageInfo()' is defined in 'cite.inc.php'

      if (!empty($node->biblio_edition) && !preg_match("/^(1|1st|first|one)( ed\.?| edition)?$/i", $node->biblio_edition))      // edition
      {
        if (!preg_match("/[?!.][ \"<i>]*$/", $output)) $output .= ".";

        if (preg_match("/^\d{1,3}$/", $node->biblio_edition)) // if the edition field contains a number of up to three digits, we assume it's an edition number (such as "2nd ed.")
        {
          if ($node->biblio_edition == "2")
          $editionSuffix = "nd";
          elseif ($node->biblio_edition == "3")
          $editionSuffix = "rd";
          else
          $editionSuffix = "th";
        }
        else
        $editionSuffix = "";

        if (!preg_match("/( ed\.?| edition)$/i", $node->biblio_edition))
        $editionSuffix .= " ed.";

        $output .= " " . $node->biblio_edition . $editionSuffix;
      }

      if (!empty($node->biblio_volume))      // volume
      {
        if (!preg_match("/[?!.][ \"<i>]*$/", $output)) $output .= ".";

        $output .= " Vol. " . $node->biblio_volume;
      }

      if (!empty($node->biblio_alternate_title) OR !empty($node->biblio_tertiary_title)) // if there's either a full or an abbreviated series title
      {
        if (!preg_match("/[?!.][ \"<i>]*$/", $output))  $output .= ".";

        $output .= " ";

        if (!empty($node->biblio_alternate_title))
        $output .= $node->biblio_alternate_title;      // abbreviated series title

        // if there's no abbreviated series title, we'll use the full series title instead:
        elseif (!empty($node->biblio_tertiary_title))
        $output .= $node->biblio_tertiary_title;      // full series title

        if (!empty($node->biblio_volume)||!empty($node->biblio_issue))
        $output .= " ";

        if (!empty($node->biblio_volume))      // series volume
        $output .= $node->biblio_volume;

        if (!empty($node->biblio_issue))      // series issue (I'm not really sure if -- for this cite style -- the series issue should be rather omitted here)
        $output .= ", no. " . $node->biblio_issue; // is it correct to format series issues similar to journal article issues?
      }

      if (!preg_match("/[?!.][ \"<i>]*$/", $output)) $output .= ".";

      // place of publication, publisher, pub year
      $output .= _biblio_style_chicago_format_pubinfo($node);

      break;
      
    case 108: 
      // thesis
      
      // degree granted
      if ($node->biblio_volume) {
        // degree granted
        $output .= $node->biblio_volume . ', ';
      }
      
      // institution & pub year
      $output .= _biblio_style_chicago_format_pubinfo($node);
      
      break;

    default : // all other types
      //TODO
      //        if (preg_match("[ \r\n]*\(ed\)", $node->author)) // single editor
      //          $author = $author . ", ed";
      //        elseif (preg_match("[ \r\n]*\(eds\)", $node->author)) // multiple editors
      //          $author = $author . ", eds";

      $publication = preg_replace("/[ \r\n]*\(Eds?:[^\)\r\n]*\)/", "", $node->biblio_secondary_title);
      if (!empty($publication))      // publication
      $output .= " In <i>$publication</i>";

      if (count($node->biblio_contributors[2]))      // editor (if different from author, see note above regarding the check for ' (ed)' or ' (eds)')
      {

        $editor_options = array(
          'BetweenAuthorsDelimStandard'     => ', ',
          'BetweenAuthorsDelimLastAuthor'   => ' and ',
          'AuthorsInitialsDelimFirstAuthor' => ' ',
          'AuthorsInitialsDelimStandard'    => ' ',
          'betweenInitialsDelim'            => '. ',
          'initialsBeforeAuthorFirstAuthor' => TRUE,
          'initialsBeforeAuthorStandard'    => TRUE,
          'shortenGivenNames'               => FALSE,
          'numberOfAuthorsTriggeringEtAl'   => 10,
          'includeNumberOfAuthors'          => 10,
          'customStringAfterFirstAuthors'   => ' et al.',
          'encodeHTML'                      => true
        );

        $editor = theme('biblio_format_authors', $node->biblio_contributors[2], $editor_options, $inline);
        $output .= ", Edited by " . $editor;
      }

      if (!empty($node->biblio_edition) && !preg_match("/^(1|1st|first|one)( ed\.?| edition)?$/i", $node->biblio_edition))      // edition
      {
        if (!preg_match("/[?!.][ \"<i>]*$/", $output)) $output .= ".";

        if (preg_match("/^\d{1,3}$/", $node->biblio_edition)) // if the edition field contains a number of up to three digits, we assume it's an edition number (such as "2nd ed.")
        {
          if ($node->biblio_edition == "2")
          $editionSuffix = "nd";
          elseif ($node->biblio_edition == "3")
          $editionSuffix = "rd";
          else
          $editionSuffix = "th";
        }
        else
        $editionSuffix = "";

        if (!preg_match("/( ed\.?| edition)$/i", $node->biblio_edition)) $editionSuffix .= " ed.";

        $output .= " " . $node->biblio_edition . $editionSuffix;
      }

      if (!empty($node->biblio_volume))      // volume
      {
        if (!preg_match("/[?!.][ \"<i>]*$/", $output)) $output .= ".";

        $output .= " Vol. " . $node->biblio_volume;
      }
      
      if (!empty($node->biblio_alternate_title) OR !empty($node->biblio_tertiary_title)) // if there's either a full or an abbreviated series title
      {
        if (!preg_match("/[?!.][ \"<i>]*$/", $output)) $output .= ".";

        $output .= " ";

        if (!empty($node->biblio_alternate_title))
        $output .= $node->biblio_alternate_title;      // abbreviated series title

        // if there's no abbreviated series title, we'll use the full series title instead:
        elseif (!empty($node->biblio_tertiary_title))
        $output .= $node->biblio_tertiary_title;      // full series title

        if (!empty($node->biblio_volume)||!empty($node->biblio_issue))
        $output .= " ";

        if (!empty($node->biblio_volume))      // series volume
        $output .= $node->biblio_volume;

        if (!empty($node->biblio_issue))      // series issue (I'm not really sure if -- for this cite style -- the series issue should be rather omitted here)
        $output .= ", no. " . $node->biblio_issue; // is it correct to format series issues similar to journal article issues?
      }

      if (!preg_match("/[?!.][ \"" . $markupPatternsArray["italic-suffix"] . "]*$/", $output)) $output .= ".";

      // Pub place, publisher & year.
      $output .= _biblio_style_chicago_format_pubinfo($node);
      break;
  }

  // DOI: optional, common to all ref types
  if (! empty($node->biblio_doi)) {
    // @todo: check if it's valid?
    $output .= ' doi: ' . check_plain($node->biblio_doi) . '. ';
  }
  
  // URL: optional, common to all ref types
  if (! empty($node->biblio_url)) {
    $output .= l($node->biblio_url, $node->biblio_url, array('external' => TRUE));
  }
  
  return filter_xss($output, biblio_get_allowed_tags());
}

function _biblio_style_chicago_field_is_punctuated($output) {
  // $output may have been run through theming functions
  return preg_match("/ [.!?'\"]+ \s* (?: <\/\w+> \s* )*  \Z /xms", $output);
}

function _biblio_style_chicago_format_pubinfo($node) {
  $pubinfo = '';
  
  if (! empty($node->biblio_place_published)) {
    $pubinfo .= $node->biblio_place_published;
  }
  
  if (! empty($node->biblio_publisher)) {
    // precede w/ colon, if we have a publisher 
    if (! empty($node->biblio_place_published)) {
      $pubinfo .= ': ';
    }
    $pubinfo .= $node->biblio_publisher;
  }

  if (! empty($node->biblio_year)) {
    // precede w/ comma, if publisher or pub. place given
    if (! empty($node->biblio_place_published) or ! empty($node->biblio_publisher)) {
      $pubinfo .= ', ';
    }
    $pubinfo .= $node->biblio_year;
  }
  
  if ($pubinfo and ! _biblio_style_chicago_field_is_punctuated($pubinfo)) {
    $pubinfo .= '. ';
  }
  
  return $pubinfo;
}