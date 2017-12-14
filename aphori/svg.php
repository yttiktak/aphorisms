<html> 
<head>
   <META http-equiv="Content-Type" content="text/html; charset=utf-8">
<title> svg </title>
<script type="text/javascript" src="Scripts/jquery-1.10.2.js"></script>
<script>
console.log('beam me up, scotty');
$(window).load(function(){
console.log('loaded');

});

</script>

<style>

</style>
</head>

 <body>

<?php
// developed on a php4 machine!

class Svg_loader {
 var $paths_stroked = 0;
 var $points_stroked = 0;
 var $paths_requested = 10000; // $_GET['np'];
 var $points_requested = 10000; // $_GET['npt'];
 var $svg_array;

 function Svg_loader( $svg_file="",$np=10000,$npt=10000 ) {
  if ($svg_file !="" ) {
   $file_name = getcwd()."/".$svg_file;
   $parser = xml_parser_create();
   $fp = fopen($file_name,r);
   $xmldata = fread($fp,64696);
   xml_parse_into_struct($parser,$xmldata,$this->svg_array);
   xml_parser_free($parser);
  }

  $this->paths_requested = $np;
  $this->points_requested = $npt; 
 }//end constructor Svg_loader


function unwrap() {
 $outs = "";
 foreach($this->svg_array as $el) {
  switch ($el['type']) {
   case 'OPEN':
   case 'open':
    $outs .= "<".$el['tag'];
    $outs .= $this->explode_attributes($el['attributes']);
    $outs .= ">\n";
   break;

   case 'CLOSE':
   case 'close':
    $outs .= "</".$el['tag'].">\n";
   break;

   case 'COMPLETE':
   case 'complete':
    if ($this->paths_stroked < $this->paths_requested) {
      if ($el['tag']=='PATH') $this->paths_stroked +=1;
      $outs .= "<".$el['tag'];
      $outs .= $this->explode_attributes($el['attributes']);
      $outs .= "\n />";
    }
   break; 

  }//end switch
 }//end foreach
 return $outs;
}//end function unwrap


function explode_attributes($attr) {
 $outs = "";
 if (gettype($attr) !== "array") return '';
 foreach($attr as $ak=>$av) {
  switch($ak) {
   case 'd':
   case 'D':
    $outs .= " ".$ak.'="'.$this->explode_path_d($av).'"'."\n";
   break;

   default:
    $outs .= " ".$ak.'="'.$av.'" '."\n";
  }//end switch
 }//end foreach
 return $outs;
}//end function explode_attributes


function explode_path_d($val) {
 $remaining = $this->points_requested - $this->points_stroked;
 if ($remaining <=0) return "";

 // z terminator is folded into prior element
 $newvs = preg_split('/([A-Ya-y][^A-Ya-y]+)/',$val,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

 $npt = count($newvs);
 if ( $remaining  < $npt  ) {
   $remove = $npt - $remaining;
   $newvs = array_slice($newvs,0,-$remove);
 }
 $this->points_stroked += count($newvs) ;

 $newv = implode("\n",$newvs);
 // $newvrr = strrev($newv);

 return $newv;
}//end function explode_path_d


}//end class Svg_loader



$paths_requested = $_GET['np'];
$points_requested = $_GET['npt'];
$svg_file = "svg/AI/NowIsN.svg";
$loader = new Svg_loader($svg_file,$paths_requested,$points_requested);


echo $loader->unwrap();


// END PHP HERE
?>




 </body>
</html>