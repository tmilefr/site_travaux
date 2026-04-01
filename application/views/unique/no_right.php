<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
		<h1 class="subtitle greydark"><?php echo $this->lang->line('NO_RIGHT');?></h1>
        <div class="nicdark_space20"></div>
        <h3 class="subtitle grey">
            <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action').'_subtitle');?>
        </h3>
        <div class="nicdark_space20"></div>
        <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
        <div class="nicdark_space10"></div>
		
		<?php echo '<pre>'.print_r($routes_history, TRUE).'</pre>';?>

	</div>
<!--end nicdark_container-->
</section>
<!--end section-->