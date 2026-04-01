<?php  
defined('BASEPATH') || exit('No direct script access allowed');

if ( ! function_exists('PassWordGenerator'))
{
	function PassWordGenerator($numAlpha=6){
		$listAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$listNonAlpha = ':!.*-+&@_+$-!';
		return substr(str_shuffle($listNonAlpha),0,1).substr(str_shuffle($listAlpha),0,$numAlpha).substr(str_shuffle($listNonAlpha),0,1);
	}
}

if ( ! function_exists('debug'))
{
	function debug($inc,$line = null){
		return '<div class="offset-3">'.$line.' <pre><code>'.print_r($inc, TRUE).'</code></pre></div>';
	}
}

if ( ! function_exists('Compare'))
{
	function Compare($type,$in1 = null, $in2 = null){
		switch($type){
			case 'date':
				$origin = new DateTimeImmutable($in1); //2009-10-13
				$target = new DateTimeImmutable($in2);
				$interval = $origin->diff($target);
				return $interval->format('%R%a');
			break;
		}
		return false;
	}
}

/**
 * Returns a human readable filesize
 *
 * @author      wesman20 (php.net)
 * @author      Jonas John
 * @version     0.3
 * @link        http://www.jonasjohn.de/snippets/php/readable-filesize.htm
 */
if ( ! function_exists('HumanReadableFilesize'))
{
	function HumanReadableFilesize($size, $unit = true) {
	 
		// Adapted from: http://www.php.net/manual/en/function.filesize.php
	
		$mod = 1024;
	 
		$units = explode(' ','B KB MB GB TB PB');
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
	 
		return round($size, 2) .(($unit) ? ' ' . $units[$i]:'');
	}
}

if ( ! function_exists('NameToFilename'))
{
	function NameToFilename($name) {
		return str_replace(['\\',' ','/',','],['_','_','_','_'] ,$name);
	}
}

if ( ! function_exists('UnicodeProcess'))
{

	function UnicodeProcess($name) {
		return str_replace(['\u00e0','\u00e2','\u00e4','\u00e7','\u00e8','\u00e9','\u00ea','\u00eb','\u00ee','\u00ef','\u00f4','\u00f6','\u00f9','\u00fb','\u00fc','\u00c8'],['&agrave;','&acirc;','&auml;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&icirc;','&iuml;','&ocirc;','&ouml;ù','&ugrave;','&ucirc;','&uuml;','&egrave;'] ,$name);
	}
}

if ( ! function_exists('GetFormatDate'))
{
	function GetFormatDate($date,$mode = 'view',$notime = true){ //TODO : helper or in FormElement ? # Add by nL for WideVoip : 2013-04-19
		if ($date != '0000-00-00'){
			$regex_fr  = '`([0-9]{1,2})[-\/ \.]?([0-9]{1,2})[-\/ \.]?([0-9]{4})(.*)`';
			$regex_eng = '`([0-9]{4})[-\/ \.]?([0-9]{1,2})[-\/ \.]?([0-9]{1,2})(.*)`';

			$regex = $regex_fr;
			$format_bdd = '\\3-\\2-\\1 \\4';
			if ($notime){
				$format_vue = '\\1/\\2/\\3';
			} else {
				$format_vue = '\\1/\\2/\\3 \\4';
			}
			
			if (preg_match($regex_eng, $date, $array_date)){
				$regex = $regex_eng;
				$format_bdd = '\\1-\\2-\\3 \\4';
				if ($notime){
					$format_vue = '\\3/\\2/\\1';
				} else {
					$format_vue = '\\3/\\2/\\1 \\4';
				}
			}
			switch($mode){
				case 'bdd':
					return  preg_replace($regex, $format_bdd, $date);
				break;
				case 'view':
					return  preg_replace($regex, $format_vue, $date);
				break;
			}
		}	
	}	
}
?>
