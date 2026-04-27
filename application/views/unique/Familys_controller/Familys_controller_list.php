<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* GENERIC LIST VIEW */
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
    <div class="nicdark_space30"></div>
	
	<div class="grid grid_7">
		<h1 class="subtitle greydark"><?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action'));?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
		<?php  
			if ($this->render_object->_get('_ui_rules') AND !$this->render_object->_get('form_mod')){  
				foreach($this->render_object->_get('_ui_rules') AS $rule){
					if (in_array($rule->term , $this->render_object->_get('_not_link_list')) AND $rule->autorize ){
						echo '<a class="" href="'.$rule->url.'"><span class="'.$rule->icon.'"></span> '.$rule->name.'</a>&nbsp;';
					}
				}
			} 
			?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="<?php echo $this->render_object->_getCi('_bg_color');?> nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>
	</div>
	<div class="grid grid_5">
		
		<ul class="nav nav-pills">
			<?php 
			foreach($civil_year AS $key=>$value){
				echo '<li class="nav-item" ><a  class="nav-link '.(($filter_ec == $key) ? 'active':'').'" href="'.base_url($this->render_object->_getCi('_controller_name').'/'.$this->render_object->_getCi('_action')).'/filter/civil_year/filter_value/'.$key.'">'.$value.'</a></li>';
			}
			?>
		</ul>
	</div>



	<table class="table table-striped table-sm">
	<thead>
		<tr>			
			<th scope="col">&nbsp;</th>
			<?php
			foreach($this->{$_model_name}->_get('defs') AS $field=>$defs){
				if ($defs->list === true){
					echo '<th scope="col">'.$this->render_object->render_link($field).'</a></th>';
				}
			}
			?>

		</tr>
	</thead>
	<tbody>
	<?php 
	foreach($datas AS $key => $data){
		echo '<tr>';
		echo '<td>';
			echo $this->render_object->render_element_menu($data, ((isset($data->blocked)) ?$data->blocked:null));
		echo '</td>';	

		foreach($this->{$_model_name}->_get('defs') AS $field=>$defs){
			if ($defs->list === true){
				echo '<td>'.$this->render_object->RenderElement($field, $data->{$field}, $data->{$this->{$_model_name}->_get('key')}).'</td>';
			}
		}
		echo '</tr>';
	}
	?>
	</tbody>
	</table>
		<!-- footer bar -->
		<footer class="footer mt-auto py-3">
			<nav class="navbar navbar-expand-lg navbar-light bg-light"> 
				<ul class="navbar-nav mr-auto"> 
					<li class="nav-item">
					<?php echo ((isset($this->pagination)) ? $this->pagination->create_links():'');?>
					</li>
					<li class="nav-item">
						
					</li>
					<?php /* //TODO : perpage<li class="nav-item">
						<?php echo ((isset($this->pagination)) ? $this->pagination->create_perpage():'');?>
					</li> */ ?>
					<li class="nav-item">
					<?php echo $footer_line;?>
					</li> 
				</ul>
				<span class="navbar-text"></span>
			</nav>
		</footer>
		<?php //echo $this->_render_debug(); ?>
	</div>
</section>


