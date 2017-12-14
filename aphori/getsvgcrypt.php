<?php
ini_set('max_execution_time', 300); 
/*** OUR STORY SO FAR:
	require_once  '../php-cryptlib/lib/CryptLib/CryptLib.php';

	use CryptLib\Cipher\Factory as CipherFactory;
	$factory = new \CryptLib\Random\Factory;
	$generator = $factory->getLowStrengthGenerator();
	$hash = $generator->generateString(32,'0123456789ABCDEF');
	$iv = $generator->generateString(32,'0123456789ABCDEF');	
	session_start();    
	$hashs = array();
	for ($i=0; $i<5; $i++) {
		$hashs[] = $generator->generateString(32,'0123456789ABCDEF');
	}

	$_SESSION['hashs'] = $hashs;
	$_SESSION['hash'] = $hash;
	$_SESSION['iv'] = $iv;

***/

	require_once  '../php-cryptlib/lib/CryptLib/CryptLib.php';

	use CryptLib\Cipher\Factory as CipherFactory;

	function to_hex($data){return strtoupper(bin2hex($data));};

class Svg_loader {
 var $paths_total = 0;
 var $points_total = 0;
 var $svg_array;
 var $svg_index;
 var $hash;
 var $iv;
 var $cipf;
 var $aes;
 var $aesccm;

 function Svg_loader( $svg_file="", $hashs, $iv ) {
  if ($svg_file !="" ) { // HUH? WHAT IS IT SUPPOSED TO DO IF NO FILE??	
   $file_name = getcwd()."/".$svg_file;
   $parser = xml_parser_create();
   $fp = fopen($file_name,'r');
   $xmldata = fread($fp,64696); 
   xml_parse_into_struct($parser,$xmldata,$this->svg_array,$this->svg_index);
   xml_parser_free($parser);
  }
  $this->hashs = $hashs;
  $this->iv = $iv;

  $this->cipf = new CipherFactory();
  $this->aes = $this->cipf->getBlockCipher('aes-128');

 }//end constructor Svg_loader


function setNewCipher($key) {
  $this->aes = $this->cipf->getBlockCipher('aes-128');
  $this->aes->setKey( pack('H*',$key) );
  $adata = "copyright_2014_Roberta_Bennett";
  $options = array('adata'=>'copyright_2014_Roberta_Bennett','lSize'=>2,'aSize'=>8);
  $this->aesccm = $this->cipf->getMode('ccm',$this->aes,pack('H*',$this->iv),$options);
}


/****
*convert the xml parsed array into
*svg format
*with statistics,counting,  and encryption
****/
function unwrap( ) {
  
 $ndivision = count($this->hashs);
 $outs = "";
 $accumulates = "";
 $accumulateStripeds = "";
 $finishing = "";
 $encrypt_me = "";
 $division = 0;
 $newDivision = 0;
 if (!is_array($this->svg_array)) { return; }

 $npaths = $this->count_paths($this->svg_array);			
 $this->paths_total = $npaths;
 $nper_division = round( $npaths / $ndivision);
 $path_count = 0;

 foreach($this->svg_array as $index=>$el) {

  switch ($el['type']) {
   case 'OPEN': case 'open':
    if  (($el['tag'] !='G')&&($el['tag'] !='g')) {
     $accumulates .= "<".$el['tag'] . $this->explode_attributes($el['attributes']). ">\n";
     $accumulateStripeds .="<".$el['tag'] . $this->explode_attributes($el['attributes']). ">\n";
    }
   break;

   case 'CLOSE': case 'close':
    switch ($el['tag']) {
     case 'G': case 'g': // ignore group tags
     break;

     case 'SVG': case 'svg':
      $finishing .= "<statistics><np>".$this->paths_total."</np><npt>".$this->points_total."</npt></statistics>";  
      $finishing .= "<crypto><iv>".$this->iv."</iv></crypto>"; 
      $finishing .= "</".$el['tag'].">\n";
     break;

     default:
     $accumulates .= "</".$el['tag'].">\n";
     $accumulateStripeds .= "</".$el['tag'].">\n";
    }
   break;

   case 'COMPLETE': case 'complete':
    $accumulates .=  "<" . $el['tag'] . $this->explode_attributes($el['attributes']) . " />";
    $accumulateStripeds .= "<" . $el['tag'] . $this->explode_attributes($el['attributes'],false) . " />";
    if ($el['tag']=='PATH') { 
     $path_count +=1; 
    }
   break; 

  }//end switch

  $newDivision  = floor($path_count / $nper_division);
  if (($newDivision != $division ) | ($finishing !="")) {
    if ($division == 0 ) {
      $outs .= $accumulates; 
    } else {
      $division_zb = $division - 1;
      $key = $this->hashs[ $division_zb ];
      $this->setNewCipher($key);
      $this->aesccm->reset();
      $this->aesccm->encrypt($accumulates);
      $ciphertext_base64 =  base64_encode($this->aesccm->finish());
      $outs .= '<cipher id="cipher-'.$division_zb.'">'.$ciphertext_base64."</cipher>\n";
      $outs .= '<cipher-key>'.$key."</cipher-key>\n";
      $outs .= $accumulateStripeds;
    }
    $accumulates = "";  
    $accumulateStripeds = "";
    $division = $newDivision; 
  }

 }//end foreach
 
 $outs .= $finishing;

 return $outs;
}//end function unwrap


/****
* count paths in the element array 
****/
function count_paths( $svg_array) {
 $npa = 0;
 foreach($svg_array as $el) {
    if ($el['tag']=='PATH') { 
	$npa +=1; 
   }
 }
 return $npa;
}


/****
* convert attributes array to format
* tag="data"
* if tag is 'D', run the data through explode_path_d
****/
function explode_attributes($attr,$keepD = true ) {
 $outs = "";
 if (gettype($attr) !== "array") return '';

 foreach($attr as $ak=>$av) {
  switch($ak) {
   case 'stroke':
   case 'STROKE':
    $outs .= " ".$ak.'="'.$av.'" '."\n";
   break;
   case 'd':
   case 'D':
     $this->points_total += $this->count_path_d($av);
     if (!$keepD) $av = "";
     $outs .= " ".$ak.'="'.$av.'" '."\n";
   break;
   default:
    $outs .= " ".$ak.'="'.$av.'" '."\n";
  }//end switch
 }//end foreach

 return $outs;
}//end function explode_attributes


function count_path_d($val) {
 $newvs = $this->split_path_d($val);
 return  count($newvs);
}//end function count_path_d


function split_path_d($val) {
 $newvs = preg_split( 
   '/([A-Ya-y][^A-Ya-y]+)/',
   $val,
   -1,
   PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
  );
 return $newvs;
}//end function split_path_d


}//end class Svg_loader


function clear_svg( $svg_array ) {
 $outs = "";
 if (!is_array($svg_array)) { return ''; }
 foreach($svg_array as $el) {

  switch ($el['type']) {
   case 'OPEN':
   case 'open':
    if ($el['tag'] !='G') 
      $outs .= "<".$el['tag'] . explode_attributes($el).  ">";
   break;

   case 'CLOSE':
   case 'close':
    if ($el['tag'] !='G') 
       $outs .= "</".$el['tag'].">";
   break;

   case 'COMPLETE':
   case 'complete':
    $completed_tag_text =  "<". explode_attributes($el). "/>";
    $outs .=  $completed_tag_text;
   break; 

  }//end switch
 }//end foreach
 return $outs;
}


/****
* convert attributes array to format
* tag="data"
****/
function explode_attributes($el) {
 $outs = "";
 if (! isset($el['attributes'])) return '';
 $attr = $el['attributes'];
 if (gettype($attr) !== "array") return '';
 foreach($attr as $ak=>$av) {
    $outs .= " ".$ak.'="'.$av.'" '."\n";
 }//end foreach
 return $outs;
}//end function explode_attributes

session_start();

$svg_file = "svg/GoogleSvgEdit/Poverty-is-hard.svg";
$svg_file = "svg/AI/NowIsL.svg";
if (isset($_GET['svg'])) { $svg_file =$_GET['svg'];} // AHHHHGHHH! SANE IT! PLEASE!!!

if (isset($_GET['crypt_text'])) {
	$crt = $_GET['crypt_text'];

	$hash =  $_SESSION['hash']; 
	$key = pack('H*',$hash);
	$ivh =  $_SESSION['iv']; 
	$iv = pack('H*',$ivh);

	$cipf = new CipherFactory();
	$aes = $cipf->getBlockCipher('aes-128');
	$aes->setKey( $key);
	$adata = "copyright_2014_Roberta_Bennett";
	$options = array ('adata'=>$adata,'lSize'=>2,'aSize'=>8);

	$aesccm = $cipf->getMode('ccm',$aes,$iv,$options);
	$aesccm->encrypt($crt);
	$ciphertext_base64 = base64_encode($aesccm->finish());

	$rets = "<svg>";
	$rets .= "<key>".$hash."</key>";
	$rets .=  "<iv>".$ivh."</iv>";
	$rets .= "<coded>".$ciphertext_base64."</coded>";	
	$rets .= "<adata>".$adata."</adata>";		
	$rets .= "</svg>";
	echo $rets;
	die();
}


if ( isset($_SESSION['hash']) && isset($_SESSION['iv'])  ) {
	$session_hashs = $_SESSION['hashs'];
	$session_iv = $_SESSION['iv'];
} else {
	// use CryptLib\Cipher\Factory as CipherFactory;
	$factory = new \CryptLib\Random\Factory;
	$generator = $factory->getLowStrengthGenerator();
	$session_iv = $generator->generateString(32,'0123456789ABCDEF');	
	$session_hashs = array();
	for ($i=0; $i<5; $i++) {
		$newk = $generator->generateString(32,'0123456789ABCDEF');
		$session_hashs[$i] = $newk;
	}
}


$loader = new Svg_loader(
	$svg_file,
	$session_hashs,
	$session_iv
);

$enchilada = $loader->unwrap();
echo $enchilada;

// END PHP HERE
?>
