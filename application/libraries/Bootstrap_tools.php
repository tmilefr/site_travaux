<?php
defined('BASEPATH') || exit('No direct script access allowed');
Class Bootstrap_tools{

	protected $_head = array();
	protected $_controller_name = '';
	protected $_asset_img = '/assets/img/';
	
	private static $instance;

	/**
	 * Get the Bootstrap_tools singleton
	 *
	 * @static
	 * @return	object
	 */
	public static function &get_instance()
	{
		return self::$instance;
	}


	public function __construct(){

		self::$instance =& $this;

		//echo print_r(self::$instance, TRUE).' <br/>';

		$this->_SetHead('assets/js/jquery-3.3.1.min.js','js');
		$this->_SetHead('assets/js/app.js','js');
		$this->_SetHead('assets/vendor/bootstrap/js/bootstrap.bundle.js','js');

		$this->_SetHead('assets/vendor/bootstrap/css/bootstrap.min.css','css');
		$this->_SetHead('assets/vendor/open-iconic/css/open-iconic-bootstrap.css','css');
		$this->_SetHead('assets/css/app.css','css');

		$this->_SetHead('assets/css/nicdark_style.css','css');
		$this->_SetHead('assets/css/nicdark_responsive.css','css');
		// menu 4 mobile
		$this->_SetHead('assets/js/plugins/menu/tinynav.min.js','js');
		$this->_SetHead('assets/css/mobile.css','css');
		$this->_SetHead('assets/css/mobile-override.css','css');
		$this->_SetHead('assets/js/mobile-menu.js','js');

		// google fonts
		$this->_SetHead('http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's':'').'://fonts.googleapis.com/css?family=Montserrat:400,700','font');//font-family: 'Montserrat', sans-serif;
		$this->_SetHead('http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's':'').'://fonts.googleapis.com/css?family=Raleway','font'); // font-family: 'Raleway', sans-serif;
		$this->_SetHead('http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's':'').'://fonts.googleapis.com/css?family=Montez','font'); //font-family: 'Montez', cursive;

		/* UI TOOLS */
		$this->_SetHead('assets/js/confirm.js','js');
		
	}
	
	public function __destruct()
	{
		//echo debug($this->_head);
	}
	/**
	 * 
	 */
	function _SetHead($file,$type){
		if ($type == "txt")
			$this->_head[$type][] = $file;
		else 
			$this->_head[$type][$file] = $file;
	}
	
	public function RenderImg($file, $alt = ""){
		return '<img src="'.base_url().$this->_asset_img.$file.'" alt="'.$alt.'">';
	}

	function GetColors($name){
		$colors = new Stdclass();
		$colors->txt = 'white';
		switch($name){
			case 'nicdark_bg_white':
				$colors->color = 'nicdark_bg_white';
				$colors->icon ='white';
				$colors->txt = '';
			break;
			case 'nicdark_bg_grey':
				$colors->color = 'nicdark_bg_grey';
				$colors->icon ='grey';
				$colors->txt = '';
			break;
			case 'nicdark_bg_grey2':
				$colors->color = 'nicdark_bg_grey2';
				$colors->icon ='greydark';
				$colors->txt = '';
			break;
			case 'nicdark_bg_greydark':
				$colors->color = 'nicdark_bg_greydark';
				$colors->icon ='grey';
			break;
			case 'nicdark_bg_greydark2':
				$colors->color = 'nicdark_bg_greydark2';
				$colors->icon ='greydark';
			break;
			case 'nicdark_bg_violet':
				$colors->color = 'nicdark_bg_violet';
				$colors->icon ='violet';
			break;
			case 'nicdark_bg_orange':
				$colors->color = 'nicdark_bg_orange';
				$colors->icon ='orange';
			break;
			case 'nicdark_bg_red':
				$colors->color = 'nicdark_bg_reddark';
				$colors->icon ='reddark';
			break;
			case 'nicdark_bg_yellow':
				$colors->color = 'nicdark_bg_yellow';
				$colors->icon ='yellow';
			break;
			case 'nicdark_bg_greendark':
				$colors->color = 'nicdark_bg_greendark';
				$colors->icon ='greendark';
			break;
			case 'nicdark_bg_bluedark':
				$colors->color = 'nicdark_bg_bluedark';
				$colors->icon ='bluedark';
			break;
			case 'nicdark_bg_violetdark':
				$colors->color = 'nicdark_bg_violetdark';
				$colors->icon ='violetdark';
			break;
			case 'nicdark_bg_orangedark':
				$colors->color = 'nicdark_bg_orangedark';
				$colors->icon ='orange';
			break;
			case 'nicdark_bg_yellowdark':
				$colors->color = 'nicdark_bg_yellowdark';
				$colors->icon ='yellowdark';
			break;
			case 'nicdark_bg_green':
				$colors->color = 'nicdark_bg_green';
				$colors->icon ='green';
			break;
			default:
			case 'nicdark_bg_blue':
				$colors->color = 'nicdark_bg_blue';
				$colors->icon ='blue';
			break;
		}
		return $colors;
	}
	
	function GetDesign($type = ""){
		$design = new Stdclass();
		switch($type){
			case 'M':
				$design->color = 'nicdark_bg_blue';
			break;
			case 'L':
				$design->color = 'nicdark_bg_violet';
			break;
			case 'nicdark_bg_yellow':
				$design->color = 'nicdark_bg_yellow';
				$design->icon ='yellow';
			break;
			case 'nicdark_bg_green':
				$design->color = 'nicdark_bg_green';
				$design->icon ='green';
			break;
			case 'nicdark_bg_blue':
				$design->color = 'nicdark_bg_blue';
				$design->icon ='blue';
			break;
			case 'can':
			        $design->color      = 'nicdark_bg_green';
			        $design->bordercolor= 'nicdark_border_green';
			        $design->backhover  = 'nicdark_bg_greendark_hover';
			        $design->img        = 'events/img5.jpg';
			        $design->btn        = 'nicdark_bg_blue';
			        $design->title      = 'Garde du midi';
			break;
			case 'MEN':
				$design->color = 'nicdark_bg_blue';
				$design->bordercolor ='nicdark_border_blue';
				$design->backhover = 'nicdark_bg_bluedark_hover';
				$design->img = 'events/nettoyage.jpg';
				$design->btn = 'nicdark_bg_green';
				$design->title = 'Session de ménage';				
			break;
			case 'INF':
				$design->color = 'nicdark_bg_blue';
				$design->bordercolor ='nicdark_border_blue';
				$design->backhover = 'nicdark_bg_bluedark_hover';
				$design->img = 'events/nettoyage.jpg';
				$design->btn = 'nicdark_bg_green';
				$design->title = 'Session Informatique';				
			break;			
			case 'TRA':
				$design->color =  'nicdark_bg_red';
				$design->bordercolor ='nicdark_border_red';
				$design->backhover = 'nicdark_bg_reddark_hover';
				$design->img = 'events/travaux.jpg';
				$design->btn = 'nicdark_bg_orange';
				$design->title = 'Session de travaux';	
			break;
			case 'GOU':
				$design->color =  'nicdark_bg_orange';
				$design->bordercolor ='nicdark_border_orange';
				$design->backhover = 'nicdark_bg_orangedark_hover';
				$design->img = 'events/gouter.jpg';
				$design->btn = 'nicdark_bg_red';
				$design->title = 'Session Goûter';	
			break;
			case 'LAV':
				$design->color =  'nicdark_bg_violet';
				$design->bordercolor ='nicdark_border_violet';
				$design->backhover = 'nicdark_bg_violetdark_hover';
				$design->img = 'events/lavage.jpg';
				$design->btn = 'nicdark_bg_green';
				$design->title = 'Session de lavage';	
			break;
			case 'DEC':
				$design->color =  'nicdark_bg_green';
				$design->bordercolor ='nicdark_border_green';
				$design->backhover = 'nicdark_bg_greendark_hover';
				$design->img = 'events/dechetterie.jpg';
				$design->btn = 'nicdark_bg_blue';
				$design->title = 'Session de déchetterie';	
			break;					
			default:
				$design->color =  'nicdark_bg_blue';
				$design->bordercolor ='nicdark_border_blue';
				$design->backhover = 'nicdark_bg_bluedark_hover';
				$design->img = 'events/img5.jpg';
				$design->btn = 'nicdark_bg_green';
				$design->title = 'Session';	
		}
		return $design;
	}


	function RenderAttachFiles($opt = 'js'){
		if (isset($this->_head[$opt])){
			foreach($this->_head[$opt] AS $file){
				switch($opt){
					case 'js':
						echo  '<script src="'.base_url().$file.'"></script>'."\n";
					break;
					case 'font':
						echo '<link rel="stylesheet" href="'.$file.'">'."\n";
					break;
					case 'css':
						echo '<link rel="stylesheet" href="'.base_url().$file.'">'."\n";
					break;
					case 'txt':
						echo $file;
				}
			}
		}
	}
	
	public function render_table($head = [],$datas , $table_style = '', $limit = 0){
		$table = '<table class="table '.$table_style.'">';
		if (count($head)){
			$table .= '<head><tr>';
			foreach($head AS $scope=>$name){
				$table .= '<th scope="'.$scope.'">'.Lang($name).'</th>';
			}
			$table .= '</tr></head>';
		}
		if (count($datas)){
			foreach($datas AS $lign=>$data){
				if (($limit AND $lign < $limit) OR !$limit){
					$table .= '<tr>';
					foreach($head AS $scope=>$name){
						$table .= '<td>'.$data->$name.'</td>';
					}
					$table .= '</tr>';
				}
			}
		}
		$table .= '</table>';
		return $table;
	}
	
	public function _set($field,$value){
		$this->$field = $value;
	}

	public function _get($field){
		return $this->$field;
	}	
	
	public function render_icon_link($url,$id,$icon, $color){
		return '<a class="btn btn-sm '.$color.'"  href="'.$url.'/'.$id.'"><span class="oi '.$icon.'"></span></a>&nbsp;';
	}
	
	
	/**
	 * @param mixed $field 
	 * @param mixed $values 
	 * @param mixed $url 
	 * @param string $null_value 
	 * @return string 
	 */
	public function render_dropdown($field,$values, $url, $null_value = ''){
		$string_render_dropdown = '';
		if (is_array($values) AND count($values)){
			$string_render_dropdown .= '<ul class="navbar-nav mr-auto">
			<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" id="navbarDropdownFrom" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></a>
			<div class="dropdown-menu" aria-labelledby="navbarDropdown">';
				foreach($values AS $key => $value){
					$string_render_dropdown .= '<a class="dropdown-item" href="'.$url.'/filter/'.$field.'/filter_value/'.$key.'">'.Lang($value).'</a>';
				}
				$string_render_dropdown .= '<a class="dropdown-item" href="'.$url.'/filter/'.$field.'/filter_value/all">'.Lang('All').'</a>';
				$string_render_dropdown .= '<a class="dropdown-item" href="'.$url.'/filter/'.$field.'/filter_value/'.$null_value.'">'.Lang('N/A').'</a>';
			$string_render_dropdown .= '</div></ul>';
		}
		return $string_render_dropdown;
	}	
	
	function render_debug($messages){
		if (is_array($messages) AND count($messages)){
			echo '<a class="btn btn-warning btn-sm padding160" data-toggle="collapse" href="#collapseDEBUG" role="button" aria-expanded="false" aria-controls="collapseExample">DEBUG</a>';
			echo '<div class="collapse" id="collapseDEBUG">';
			echo '<table class="table table-sm">';
			foreach($messages AS $message){
				echo '<tr><th scope="row">'.$message->from.'</th><td>'.$message->type.'</td><td>'.$message->file.'</td><td>'.$message->line.'</td><td>'.$message->message.'</td></tr>';
			}
			echo '</table></div>';
		}
	}
	
	public function render_head_link($field, $direction, $url, $add_string ){
		return '<a class="nav-link " href="'.$url.'/order/'.$field.'/direction/'.(($direction == 'desc') ? 'asc':'desc').'">'.Lang($field).' '.$add_string.'</a>';
	}

	public function label($name){
		return '<label for="input'.$name.'">'.Lang($name).'</label>';
	}
	
	public function textarea($field, $value, $message = '', $required = false,$rows = 10){
		return '<textarea  rows="'.$rows.'" class="form-control" id="'.$field.'" name="'.$field.'" placeholder="'.$message.'" '.(($required) ? 'required':'').'>'.$value.'</textarea>';
	}

	public function input_checkbox($field, $value){
		return form_checkbox($field, 1 , $value , ' class="form-check-input" id="input'.$field.'" ');
	}
	
	public function input_date($name,$value, $datatarget = null){
		$this->_SetHead('assets/js/datepicker_start.js','js');
		
		if (!$value OR $value == '0000-00-00'){
			$value = date('Y-m-d');
		}
		return '<div class="input-group">
				  <input autocomplete="off" class="form-control datepicker" '.(($datatarget) ? 'data-target="'.$datatarget.'"':'').' name="'.$name.'" id="input'.$name.'" value="'.$value.'" type="text">
				  <div class="input-group-append">
					 <span class="input-group-text"><span class="oi oi-calendar"></span></span>
				  </div>
			  </div>';
	}
	
	public function input_time($name,$value, $datatarget = null){
		return '<div class="input-group">
				  <input autocomplete="off" class="form-control timepicker" '.(($datatarget) ? 'data-target="'.$datatarget.'"':'').' name="'.$name.'" id="input'.$name.'" value="'.$value.'" type="text">
				  <div class="input-group-append">
					 <span class="input-group-text"><span class="oi oi-timer"></span></span>
				  </div>
			  </div>';
	}

	
	
	public function input_text($name, $placeholder = '',$value = '', $label = false, $datatarget = null){
		if ($label){
			return '<div class="form-group"><label for="'.$name.'">'.$placeholder.'</label><input '.(($datatarget) ? 'data-target="'.$datatarget.'"':'').' type="text" class="form-control" name="'.$name.'" id="input'.$name.'" value="'.$value.'"></div>';
		} else {
			return '<input type="text" class="form-control" name="'.$name.'" id="input'.$name.'" placeholder="'.$placeholder.'" value="'.$value.'">';
		}

	}
	public function password_text($name, $placeholder = '',$value = '' , $opt = ''){
		return '<input type="password" class="form-control" '.$opt.' name="'.$name.'" id="input'.$name.'" placeholder="'.$placeholder.'" value="'.$value.'">';
	}

	
	public function input_select($name, $values, $selected = ''){
		$input_select = '<select id="input'.$name.'" name="'.$name.'" class="form-control custom-select">';
		$input_select .= '<option '.(($selected == '') ? 'selected="selected"':'').'></option>';
		foreach($values AS $key=>$value){
			$input_select .= '<option value="'.$key.'" '.(($key == $selected AND $selected) ? 'selected="selected"':'').'>'.$value.'</option>';
		}
		$input_select .= '</select>';
		return $input_select;
	}
	
	function random_color_part() {
		return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
	}

	function random_color() {
		return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
	}
	
	function render_msg($value, $type = 'alert-danger'){
		echo '<div class="alert '.$type.'" role="alert">'.$value.'</div>';
	}
	

}
