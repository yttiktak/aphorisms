<?php
// developed on a php4 machine!
// finally moved over to 5 or so, to get openssl

/*** OUR STORY SO FAR:
	require_once  '../php-cryptlib/lib/CryptLib/CryptLib.php';

	use CryptLib\Cipher\Factory as CipherFactory;
	$factory = new \CryptLib\Random\Factory;
	$generator = $factory->getLowStrengthGenerator();
	$hash = $generator->generateString(32,'0123456789ABCDEF');
	$iv = $generator->generateString(32,'0123456789ABCDEF');	
	session_start();    
	$_SESSION['hash'] = $hash;
	$_SESSION['iv'] = $iv;

***/

	require_once  '../php-cryptlib/lib/CryptLib/CryptLib.php';

	use CryptLib\Cipher\Factory as CipherFactory;

	function to_hex($data){return strtoupper(bin2hex($data));};

class Svg_loader {
 var $paths_stroked = 0;
 var $points_stroked = 0;
 var $paths_total = 0;
 var $points_total = 0;
 var $paths_requested = 10000; // $_GET['np'];
 var $points_requested = 10000; // $_GET['npt'];
 var $svg_array;
 var $hash;
 var $iv;
 var $aesccm;


 function Svg_loader( $svg_file="",$np=10000,$npt=10000,$hash,$iv ) {

  if ($svg_file !="" ) { // HUH? WHAT IS IT SUPPOSED TO DO IF NO FILE??	
   $file_name = getcwd()."/".$svg_file;
   $parser = xml_parser_create();
   $fp = fopen($file_name,'r');
   $xmldata = fread($fp,64696);
   xml_parse_into_struct($parser,$xmldata,$this->svg_array);
   xml_parser_free($parser);
  }
  $this->hash = $hash;
  $this->iv = $iv;
  $this->paths_requested = $np;
  $this->points_requested = $npt; 

  $cipf = new CipherFactory();
  $aes = $cipf->getBlockCipher('aes-128');
  $aes->setKey( pack('H*',$hash) );
  $adata = "copyright_2014_Roberta_Bennett";
  $options = array('adata'=>'copyright_2014_Roberta_Bennett','lSize'=>2,'aSize'=>8);
  $this->aesccm = $cipf->getMode('ccm',$aes,pack('H*',$iv),$options);

 }//end constructor Svg_loader


/****
*convert the xml parsed array into
*svg format
*with statistics,counting, limits, and encryption
****/
function unwrap() {
 $outs = "";
 if (!is_array($this->svg_array)) {
  return;
 }
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
    switch ($el['tag']) {
     case 'SVG':
     case 'svg':
      $outs .= "<statistics><np>".$this->paths_total."</np><npt>".$this->points_total."</npt></statistics>";  
      $outs .= "<crypto><key>".$this->hash."</key><iv>".$this->iv."</iv></crypto>"; 
 	$this->aesccm->encrypt($outs."</SVG>");
 	$ciphertext_base64 = base64_encode($this->aesccm->finish());
 	$outs .= "<cipher>".$ciphertext_base64."</cipher> \n";
     default:
     $outs .= "</".$el['tag'].">\n";
    }
   break;

   case 'COMPLETE':
   case 'complete':
    $completed_tag_text =  "<". $el['tag']. $this->explode_attributes($el['attributes']). "\n />";
    if (($this->paths_stroked < $this->paths_requested) | ($this->paths_requested==0) ) {
      $outs .=  $completed_tag_text;
    }
    if ($el['tag']=='PATH') {
     $this->paths_stroked +=1;
     $this->paths_total +=1;
    }
   break; 

  }//end switch
 }//end foreach
 return $outs;
}//end function unwrap

/****
* convert attributes array to format
* tag="data"
* aaand, if tag is 'D', run the data through explode_path_d
* and include the encrypted data
****/
function explode_attributes($attr) {
 $outs = "";
 if (gettype($attr) !== "array") return '';
 foreach($attr as $ak=>$av) {
  switch($ak) {
   case 'd':
   case 'D':
    $datas = $this->explode_path_d($av);
    $outs .= " " .$ak. '="' .$datas. '"' . "\n";
 //   $this->aesccm->encrypt($datas);
 //   $ciphertext_base64 = base64_encode($this->aesccm->finish());
 //   $outs .= " data-cipher=".$ciphertext_base64." \n";
   break;

   default:
    $outs .= " ".$ak.'="'.$av.'" '."\n";
  }//end switch
 }//end foreach
 return $outs;
}//end function explode_attributes


/****
* take an svg path data, like 'c #* C #* c #*'
* deliver as many parts of it as allowed by points_total
****/
function explode_path_d($val) {
 $newvs = preg_split('/([A-Ya-y][^A-Ya-y]+)/',$val,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
 // z terminator is folded into prior element

 $npt = count($newvs);

 $this->points_total += $npt;

 $remaining = $this->points_requested - $this->points_stroked;
 if (($remaining <=0) && ($this->points_requested!=0) ) return ""; // set 0 for no limit

 if ( ( $remaining  < $npt  ) && ($this->points_requested!=0) ) {
   $remove = $npt - $remaining;
   $newvs = array_slice($newvs,0,-$remove);
 }
 $this->points_stroked += count($newvs) ;

 $newv = implode("\n",$newvs);
 return $newv;
}//end function explode_path_d


}//end class Svg_loader


session_start();

$paths_requested = $_GET['np'];
$points_requested = $_GET['npt'];
$svg_file = "svg/AI/NowIsL.svg";
$svg_file = "svg/GoogleSvgEdit/Poverty-is-hard.svg";
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


$loader = new Svg_loader(
	$svg_file,
	$paths_requested,
	$points_requested,
	$_SESSION['hash'],
	$_SESSION['iv']
);

$enchilada = $loader->unwrap();
echo $enchilada;

// END PHP HERE
?>
