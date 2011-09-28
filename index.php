<?php
// define the consumer key CK="xxxx";
include("ck.php");
  $title = $_POST['title']; 
 echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Mendeley Usage information</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript" type="text/javascript" src="niceforms.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="niceforms-default.css" />
</head>
<body>
<div id="container">
EOF;
if ($_FILES['uploadedfile']['tmp_name']) {
$uploaddir = '/home1/fireisbo/www/retract/upload/input/';
$newfile = str_replace(" ", "_",basename($_FILES['uploadedfile']['name']));
$uploadfile = $uploaddir . $newfile;
$email = $_POST['email'];
if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";
    $fh = fopen($uploadfile,"r+");
    while ( ($buffer = fgets($fh)) !== false) {
      $data .= $buffer;
    }
    $data = "#__email:$email\n" . $data;
    echo "data is $data";
    fclose($fh);
    $fh = fopen($uploadfile,"w+");
    fwrite($fh,$data);
    fclose($fh);
} else {
    echo "Possible file upload attack!\n";
}
exit(0);

}
if($title) {
  $doi = $_POST['doi']; 
  $pmid = $_POST['pmid']; 
  echo "<h2>" . "Duplication information for \"$title\""  . "</h2><br/>\n";
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
         if ($doi == $fdoi) { $matched_on = "matched doi $fdoi"; } 
         if ($pmid !='' && $pmid == $fpmid) { $matched_on = "matched pmid $fpmid"; } 
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
 echo <<<EOF
<form enctype="multipart/form-data" action="index.php" method="POST" class="niceform">
       <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
	<fieldset>
    	<legend>Enter Mendeley Information</legend>
        <dl>
            <dt><label for="title">Title:*</label></dt>
            <dd><input type="text" value="$otitle" name="title" id="title" size="80" maxlength="128" /></dd>
        </dl>
        <dl>
            <dt><label for="pmid">PMID:</label></dt>
            <dd><input type="text" value="$pmid" name="pmid" id="pmid" size="32" maxlength="128" /></dd>
        </dl>
        <dl>
            <dt><label for="doi">DOI:</label></dt>
            <dd><input type="text" value="$doi" name="doi" id="doi" size="32" maxlength="128" /></dd>
        </dl>
        <dl><dt>OR You can upload a file to process.  Supply an email address here.</dt><dd>
        <input type="text" name="email" id="email" size="32" maxlength="128" />
        </dd></dl>
        <dl><dt><label>Choose a file to upload:</label></dt>
        <dd><input name="uploadedfile" type="file" /></dd>
        </dl>
        <dl><dt><dd>
    	<input type="submit" name="submit" id="submit" value="SUBMIT" />
        </dd></dt>
        </dl>
        <dl><dt>*:</dt><dd>Required</dd></dl>
        <dl><dt>Try:</dt><dd>"Why most published research findings are false."</dd></dl>
    </fieldset>
    </fieldset>

</form>
EOF;

if ($ureaders) {
         echo "<fieldset>\n";
         echo "<legend> Similar Titles</legend>\n";
         echo "<br/><ul>$ureaders </ul>";
         echo "<br/><b>Total Readers $tot_readers </b>";
         echo "<br/><i>Totals increased by one to count </i>";
         echo "<br/><i>original submitter.</i>";
         echo "</fieldset>\n";
}
 echo <<<EOF
<h3 class="s navLicense"><i>License</i></h3> 
            <p class="list gray">Niceforms by <a href="http://www.emblematiq.com/">Lucian Slatineanu</a><br />is licensed under a <a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-Share Alike 3.0 Unported License</a><br />In other words, Niceforms is completely free for both personal and commercial use as long as credits remain intact within the source files</p> 
</body>
</html>
</p>
</div>
</body>
</html>
EOF;
 exit (0);

function mend_api($uuid) {
 global $CK;
 $UP="http://api.mendeley.com/oapi/documents/details/$uuid?consumer_key=$CK";
 $utext = file_get_contents($UP);
 $uobj = json_decode($utext);
 return $uobj;
}


