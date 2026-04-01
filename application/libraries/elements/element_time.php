<?php
/*
 * element_date.php
 * Date Object in page
 * 
 */

class element_time extends element
{	
	
	protected $minTime;
	protected $maxHour;
	protected $maxMinutes;
	protected $startTime;
	

	public function __construct(){
		parent::__construct();
		if (isset($this->RenderTools))
		{
			$this->RenderTools->_SetHead('assets/vendor/jquery-timepicker/jquery.timepicker.js','js');
			$this->RenderTools->_SetHead('assets/vendor/jquery-timepicker/jquery.timepicker.css','css');		
		}
	}

	public function RenderFormElement(){
		$js = "<script>
			$('#input".$this->GetName()."').timepicker({
				timeFormat: 'HH:mm:ss',
				minTime: '".$this->minTime."',
				maxHour: ".$this->maxHour.",
				maxMinutes: ".$this->maxMinutes.",
				startTime: new Date(0,0,0,".$this->startTime.",0,0),
				interval: 15".(($this->change) ? ",change: ".$this->change.",":"")."
			});
		</script>";

		$this->RenderTools->_SetHead($js , 'txt');		
		return $this->RenderTools->input_time($this->GetName(),$this->value,$this->datatarget);
	}

}

