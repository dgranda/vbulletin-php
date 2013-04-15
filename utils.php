<?php
date_default_timezone_set('Europe/Madrid');
/*
 * Converts CSV to JSON
 * Example uses Google Spreadsheet CSV feed
 * csvToArray function I think I found on php.net
 */

// Function to convert CSV into associative array
function csvToArray($file, $delimiter) { 
  if (($handle = fopen($file, 'r')) !== FALSE) { 
    $i = 0; 
    while (($lineArray = fgetcsv($handle, 4000, $delimiter, '"')) !== FALSE) { 
      for ($j = 0; $j < count($lineArray); $j++) { 
        $arr[$i][$j] = $lineArray[$j]; 
      } 
      $i++; 
    } 
    fclose($handle); 
  } 
  return $arr; 
} 

function getData($feed) {

    // Arrays we'll use later
    $keys = array();
    $newArray = array();

    // Do it
    $data = csvToArray($feed, ',');

    // Set number of elements (minus 1 because we shift off the first row)
    $count = count($data) - 1;
      
    //Use first row for names  
    $labels = array_shift($data);  

    foreach ($labels as $label) {
      $keys[] = $label;
    }

    // Add Ids, just in case we want them later
    $keys[] = 'id';

    for ($i = 0; $i < $count; $i++) {
      $data[$i][] = $i;
    }
      
    // Bring it all together
    for ($j = 0; $j < $count; $j++) {
      $d = array_combine($keys, $data[$j]);
      $newArray[$j] = $d;
    }

    return $newArray;
}

function getCalendarWeek($date_string) {
    $my_dateTime = new DateTime($date_string, new DateTimeZone('Europe/Madrid'));
    return $my_dateTime->format("W");
}

// Credits: http://www.velvetcache.org/2007/01/22/simple-bbcode-to-html-function-in-php
function bbc2html($content) {
  $search = array (
    '/(\[b\])(.*?)(\[\/b\])/',
    '/(\[i\])(.*?)(\[\/i\])/',
    '/(\[u\])(.*?)(\[\/u\])/',
    '/(\[ul\])(.*?)(\[\/ul\])/',
    '/(\[li\])(.*?)(\[\/li\])/',
    '/(\[url=)(.*?)(\])(.*?)(\[\/url\])/',
    '/(\[url\])(.*?)(\[\/url\])/'
  );
  $replace = array (
    '<strong>$2</strong>',
    '<em>$2</em>',
    '<u>$2</u>',
    '<ul>$2</ul>',
    '<li>$2</li>',
    '<a href="$2" target="_blank">$4</a>',
    '<a href="$2" target="_blank">$2</a>'
  );
  return preg_replace($search, $replace, $content);
}

// needs bbcode extension installed
function bbcode2html($bbcode_text) {
    $arrayBBCode = array(
        ''=>    array('type'=>BBCODE_TYPE_ROOT,  'childs'=>'!i'),
        'i'=>   array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<i>',
                    'close_tag'=>'</i>', 'childs'=>'b'),
        'url'=> array('type'=>BBCODE_TYPE_OPTARG,
                    'open_tag'=>'<a href="{PARAM}">', 'close_tag'=>'</a>',
                    'default_arg'=>'{CONTENT}',
                    'childs'=>'b,i'),
        'img'=> array('type'=>BBCODE_TYPE_NOARG,
                    'open_tag'=>'<img src="', 'close_tag'=>'" />',
                    'childs'=>''),
        'b'=>   array('type'=>BBCODE_TYPE_NOARG, 'open_tag'=>'<b>',
                    'close_tag'=>'</b>'),
    );
    $BBHandler = bbcode_create($arrayBBCode);
    return bbcode_parse($BBHandler, $bbcode_text);
}

function checkAbsoluteURL(&$url_string) {
    $valid_url = strpos($url_string, "http");
    if ($valid_url === false) {
        $url_string = "http://" . $url_string;
    }
}

?>
