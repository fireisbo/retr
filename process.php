<?php
// define the consumer key CK="xxxx";
include("ck.php");
$file=$argv[1];
$fh = fopen($file, 'r');
if (!$fh) { echo "process.php could not access $file"; exit(1);}
while ( ($buffer = fgets($fh)) !== false) { 
  $ureaders = "";
  $pos = strpos($buffer, "|");
  if ($pos === false){ continue;}
  list($title,$author,$doi,$pmid) = explode('|',$buffer);
  if($title) {
  echo "****" . "Duplication information for \"$title\""  . "\n\n";
  $tit_enc = urlencode($title);
  $P="http://api.mendeley.com/oapi/documents/search/$tit_enc?consumer_key=$CK";
  $text=file_get_contents($P);
  if ($text == false) { 
    echo "No details|$title|$doi|$pmid,$enoteid\n" ;  
  }
 $obj = json_decode($text);
 $documents=$obj->documents;
 $otitle = $title;
 $title = str_replace(' ', '', $title);
 $title = str_replace('[', '', $title);
 $title = str_replace(']', '', $title);
 $title = str_replace('.', '', $title);
 $title = str_replace('?', '', $title);
 $tot_readers = 0;
 $uvreaders = 0;
 $found = 0;
 if ($documents) {
    $count = count($obj->documents);
    for ($index=0 ; $index < $count; $index++) {
       $ftitle = $obj->documents[$index]->title;
       $fdoi   = 
          isset($obj->documents[$index]->doi)?
          $obj->documents[$index]->doi:"No doi";
       //$ureaders .= "<br/>examined $ftitle ($fdoi)" 
       //   . print_r($obj->documents[$index]->doi,TRUE);
       $mtitle = str_replace(' ', '', $obj->documents[$index]->title);
       $mtitle = str_replace('[', '', $mtitle);
       $mtitle = str_replace(']', '', $mtitle);
       $mtitle = str_replace('.', '', $mtitle);
       $mtitle = str_replace('?', '', $mtitle);
       $fuuid = $obj->documents[$index]->uuid;
       $fuobj  = mend_api($fuuid);
       if ( (strtolower($title) == strtolower($mtitle))
          || ($doi == $fdoi) )   { 
         $fpmid =$fuobj->identifiers->pmid;
         if ((strtolower($title) == strtolower($mtitle))) 
           { $matched_on = "matched on title  $fdoi"; } 
         if ($doi == $fdoi) { $matched_on = "matched doi $fdoi"; } 
         if ($pmid !='' && $fpmid != '' && $pmid == $fpmid) { $matched_on = "matched pmid $fpmid"; } 
         if ($fuobj ) {
           $ureaders .=  "<li> $ftitle $matched_on ($fuuid)";
           $ureaders .= "<br/><b>Readers:</b>".$fuobj->stats->readers . "";
           $ureaders .= "<br/><a href=".$fuobj->mendeley_url . ">Mendeley URL</a></li>";
           $uvreaders = $fuobj->stats->readers;
           $uvreaders++;
           $tot_readers += $uvreaders;
           $found = 1;
         }
       }
    }
 }
}

if ($ureaders) {
         echo "\n";
         echo "Similar Titles\n";
         echo "$ureaders \n";
         echo "Total Readers $tot_readers \n";
         echo "Totals increased by one to count \n";
         echo "original submitter.\n";
         echo "\n";
}
}

 exit (0);

$pagecount = 0;

function mend_api($uuid) {
 global $CK;
 global $pagecount;
 $pagecount++;
 if ($pagecount > 400) { echo "sleeping for 1 hour" ; sleep(3600); $pagecount = 0; }
 $UP="http://api.mendeley.com/oapi/documents/details/$uuid?consumer_key=$CK";
 $utext = file_get_contents($UP);
 $uobj = json_decode($utext);
 return $uobj;
}


