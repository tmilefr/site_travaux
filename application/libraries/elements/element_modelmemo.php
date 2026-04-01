<?php
/*
 * element.php
 * Object in page
 * 
 */

class element_modelmemo extends element
{

	protected $dst_field = '';
	protected $CI = '';
	protected $api = '';

	public function __construct(){
		parent::__construct();
        $this->CI =& get_instance();
	}

    public function __destruct()
    {
        unset($this->CI);
    }


	public function RenderFormElement(){
		/* injection de js pour le */
		$js = "<script>
			var elt = document.querySelector('#input".$this->name."');
				elt.addEventListener('change', function () {
				
				var settings = {
					'cache': false,
					'dataType': 'json',
					'async': false,
					'crossDomain': true,
					'url': '".base_url('api/'.$this->api)."/'+ this.value,
					'method': 'GET',
					'headers': {
						'accept': 'application/json',
						'Access-Control-Allow-Origin':'".base_url()."',
						'Authorization' : 'Bearer ".$this->CI->auth->_get('connected_user')->token."'
					}
				}
				if (this.value != '...'){
					$.ajax(settings).done(function (response) {
						console.log(response);
						txt = editor.getData() + response.text.render;
						editor.setData( txt );
					});
				}
				
			})
		</script>";

		$this->RenderTools->_SetHead($js , 'txt');
		//$this->values = ['Choix 1'=>'TEXT','Choix 2'=>'TEXT2','Choix 3'=>'TEXT3'];
		$txt = $this->RenderTools->input_select($this->name, $this->values, $this->value);
		
		return $txt;
	}
	

	public function Render(){ 
		return '';
	}

}

