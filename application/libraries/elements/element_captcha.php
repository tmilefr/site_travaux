<?php
/*
 * element_captcha.php
 * created Date Object in page
 * 
 */

class element_captcha extends element
{	
	public function __construct()
	{
		parent::__construct();
	}
	//g-recaptcha-response
	public function RenderFormElement(){
		$tmp = '';
		if ($this->captcha == TRUE){
			$tmp .= '<div class="g-recaptcha" data-sitekey="'.SITE_CAPTCHA_KEY.'"></div>';
			$tmp .= '<input type="submit" class="btn btn-primary" value="Submit">';
		}
		$tmp .= '<input type="hidden" id="'.$this->name.'" name="'.$this->name.'">';
		return ($tmp);		
	}
	
	public function PrepareForDBA($value){
		// On prépare l'URL
		$response = '';
		if ($value){
			$response = new StdClass();
			$response->success = false;
			$response->{'error-codes'} = [];

			$url = "https://www.google.com/recaptcha/api/siteverify?secret=".SITE_CAPTCHA_SECRET_KEY."&response={$value}";
			// On vérifie si curl est installé
			if(function_exists('curl_version')){
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_TIMEOUT, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				$response = curl_exec($curl);
			}else{
				// On utilisera file_get_contents
				$response = file_get_contents($url);
			}
		}
		return $response;
	}
}
