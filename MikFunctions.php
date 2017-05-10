<?php

/**
 * File holding the MikFunctions class
 *
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup MikFunctions
 */

/*
 
 NOTE: This extension currently works only on *NIX servers 
 Defines a subset of parser functions that must clear the cache to be useful.
 
 {{#seqnext:seqname|valpattern|fillchar}} Returns the value of the given sequence. 
	       	The first time the function is used a sequence with the specified 
		seqname will be created. Every time the next function for the same
		seqname is used an incremental value will be returned.
		if valpattern and fillchar value are set the char of fillchar sill be 
		returned before the numeric a number of times to match at least the valpattern. 
		(example if fillchar= X, maxval = 999 and the result is 45 it will be
		returned X45).
		NOTE: In order to work, after installing the extension a directory 
		named "seqences" should be created in the extension folder and write 
		permission shuold be granted for the wiki owner.  
		example on linux: mkdir seqences; chown apache:apache seqences;
		
DEPRECATED: {{#uml:umlcode}} replaced with Extension:MikUML extension.
		
{{#parsenl:text}} process and returns the input text duplicatinc new lines and return carriages (usefull to show user input text exaclty as inserted)  

{{#mikecho:text}} echo raw text

{{#ckusergroup:GROUP}} checks if user belongs to a group

{{#makebutton:text}} makes html button

{{#mikreplace:text|search|replacement}} replace raw text


 Author: Michele Fella [http://meta.wikimedia.org/wiki/User:Michele.Fella]
 Version 1.0 
 
*/
 
# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
   die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}
 
$wgExtensionFunctions[] = 'wfMikFunctions';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'MikFunctions',
	'version' => '1.1',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MikFunctions',
	'author' => 'Michele Fella',   
	'description' => 'Defines an additional set of parser functions.'
);
 
$wgHooks['LanguageGetMagic'][] = 'wfMikFunctionsLanguageGetMagic';
 
function wfMikFunctions() {
	global $wgParser, $wgExtMikFunctions;
 
	$wgExtMikFunctions = new ExtMikFunctions();
  $wgParser->setFunctionHook( 'seqnext', array( &$wgExtMikFunctions, 'seqnext' ) );
  $wgParser->setFunctionHook( 'parsenl', array( &$wgExtMikFunctions, 'parsenl' ) );
  $wgParser->setFunctionHook( 'mikecho', array( &$wgExtMikFunctions, 'mikecho' ) );
  $wgParser->setFunctionHook( 'ckusergroup', array( &$wgExtMikFunctions, 'ckusergroup' ) );
  $wgParser->setFunctionHook( 'makebutton', array( &$wgExtMikFunctions, 'makebutton' ) );
  $wgParser->setFunctionHook( 'mikreplace', array( &$wgExtMikFunctions, 'mikreplace' ) );
}
 
function wfMikFunctionsLanguageGetMagic( &$magicWords, $langCode ) {
	switch ( $langCode ) {
	default:
		$magicWords['seqnext']    = array( 0, 'seqnext' );
		$magicWords['mikecho']    = array( 0, 'mikecho' );
		$magicWords['parsenl']    = array( 0, 'parsenl' );
		$magicWords['ckusergroup']    = array( 0, 'ckusergroup' );
		$magicWords['makebutton']    = array( 0, 'makebutton' );
		$magicWords['mikreplace']    = array( 0, 'mikreplace' );
	}
	return true;
}
 
class ExtMikFunctions {
 
	function arg( &$parser, $name = '', $default = '' ) {
		global $wgRequest;
		$parser->disableCache();
		return $wgRequest->getVal($name, $default);
	}
 
	static function shellexec($cmd){
		$cmd="echo -n \"$(".$cmd.")\"";
// 		print "CMD: ".$cmd."</br>";
// 		print "->RESULT:".shell_exec($cmd)."</br>";
		return shell_exec($cmd);
	}
	
	function seqnext( &$parser, $seqname = "", $valpattern = 0, $fillchar = "" ) {
		$parser->disableCache();
		$seqname = str_replace(' ', '', $seqname);
		$seqdir="sequences";
		$pathname = dirname(__FILE__);
		if(is_null($seqname)||$seqname===''){
			throw new MikFunctionsException('pathname is null or empty');
		}
// 		print "pathname: ".$pathname."</br>";
// 		$cmd="echo -n \"$(ls -1 ".$pathname."|grep -e ".$seqdir.")\"";
		$cmd="ls -1 ".$pathname."|grep -e '".$seqdir."'";
		$seqdirexists = ExtMikFunctions::shellexec($cmd);
// 		print "seqdirexists - |".$seqdirexists."|".$seqdir."|</br>" ;
		if( $seqdirexists !== $seqdir ){
			throw new Exception('MikFunctions: Configuration error: sequence path does not exist! Contact administrator to fix this problem.');
		}
		$pathname = $pathname.DIRECTORY_SEPARATOR.$seqdir;
// 		print "pathname: ".$pathname."</br>";
		$cmd=" if [ -d ".$pathname." ]; then echo 1; else echo 0; fi";
		$seqdirisdir = (int) ExtMikFunctions::shellexec($cmd);
// 		print "seqdirisdir - ".$seqdirisdir."</br>" ;
		if( $seqdirisdir != 1){
			throw new Exception('MikFunctions: Configuration error: sequence dir is not a directory! Contact administrator to fix this problem.');
		}
		$pathname = $pathname.DIRECTORY_SEPARATOR.$seqname;
// 		print "pathname: ".$pathname."</br>";
		$cmd=" if [ -f ".$pathname." ]; then echo 1; else echo 0; fi";
		$seqexists = (int) ExtMikFunctions::shellexec($cmd);
		if( $seqexists != 1){
			$cmd="echo 0 > ".$pathname;
			ExtMikFunctions::shellexec($cmd);
		}
		$cmd=" if [ -w ".$pathname." ]; then echo 1; else echo 0; fi";
		$seqexistsandiswriteble = (int) ExtMikFunctions::shellexec($cmd);
		if( $seqexistsandiswriteble < 1){
			throw new Exception('sequence '.$seqname.' not writable! Contact administrator to fix this problem.');
		}
		$cmd="cat ".$pathname;
		$lastval = (int) ExtMikFunctions::shellexec($cmd);
// 		print "lastval: ".$lastval."</br>";
		if(is_null($lastval)||$lastval===''||!is_numeric($lastval)){
			throw new Exception('lastvalue for '.$seqname.' is null or empty or is not numeric ('.$lastval.')');
		}
		$nextval = $lastval+1;
// 		print "nextval: ".$nextval."</br>";
		if($nextval != ($lastval+1)){
			throw new Exception('internal error 1');
		}
		$cmd="echo ".$nextval." > ".$pathname;
		ExtMikFunctions::shellexec($cmd);
		$cmd="cat ".$pathname;
		ExtMikFunctions::shellexec($cmd);
		$checkval = (int) ExtMikFunctions::shellexec($cmd);
// 		print "checkval: ".$checkval."</br>";
		if($nextval != $checkval){
			$cmd="echo ".$lastval." > ".$pathname;
			ExtMikFunctions::shellexec($cmd);
			throw new Exception('internal error 2');
		}
		if(!is_null($fillchar)&&$fillchar!=''){
			$mlen = strlen($valpattern);
			$nlen = strlen($checkval);
			while($nlen<$mlen){
				$checkval = $fillchar.$checkval;
				$nlen = strlen($checkval);
			}
		}
		return $checkval;
	}
	
	function mikecho( &$parser, $text = "" ) {
		if(is_null($text)||$text===''){
			throw new MikFunctionsException('input text is null or empty');
		}
		return array( $text, 'noparse' => true, 'isHTML' => true );
	}
	
	function parsenl( &$parser, $text = "" ) {
		//print("OK <br>");
		//var_dump($text);
		//print("<br>");
		$text = strtr($text, array("\n" => "\n\n", "\r" => "\r\n"));
		return array( $text, 'noparse' => true, 'isHTML' => true  );
	}

	function ckusergroup( &$parser, $group = "" ) {
		$res=0;
		foreach($GLOBALS['wgUser']->getGroups() as $key => $value)
		{
			if($group == $value){
				$res=1;
				break;
			}
		}
                return array( $res, 'noparse' => false, 'isHTML' => false  );
        }
		
	function makebutton( &$parser, $url= "", $label = "", $options = "" ) {
		$res="<html><button type='button' onclick=\"location.href='".$url."'\" ".$options.">".$label."</button></html>";
                return array( $res, 'noparse' => false, 'isHTML' => false  );
        }

	function mikreplace( &$parser, $text = "", $search = "", $newval = "" ) {
		if(is_null($text)||$text===''){
                        throw new MikFunctionsException('input text is null or empty');
                }
		return str_replace($search, $newval, $text);
	}
	

}
