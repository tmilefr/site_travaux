<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>

		<div class="grid grid_12">
		<h1 class="subtitle greydark"><?php echo tr($render_object->_getCi('_controller_name').'_'.$render_object->_get('form_mod'));?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
            <?php echo tr($render_object->_getCi('_controller_name').'_subtitle');?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="<?php echo $render_object->_getCi('_bg_color');?> nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>
	</div>
	<?php
	echo open_form($render_object->_getCi('_controller_name').'/'.$render_object->_get('form_mod'), array('class' => '', 'id' => 'edit') , array('form_mod'=>$render_object->_get('form_mod'),'id'=>$id) );

	//champ obligatoire
	foreach($required_field AS $name){
		echo field_error($name, 	'<div class="alert alert-danger">', '</div>');
	}
	?>
	<div class="card" >
		<div class="card-header">
			<?php echo tr($render_object->_getCi('_controller_name').'_'.$render_object->_get('form_mod'));?>
		</div>	
	<div class="card-body">
		<div class="form-row">
			<div class="form-group col-md-4">
				<?php 
					echo $render_object->label('id_type');
					echo $render_object->RenderFormElement('id_type'); 
				?>
			</div>
			<div class="form-group col-md-6">
				<?php 
					echo $render_object->label('name');
					echo $render_object->RenderFormElement('name'); 
				?>
			</div>		
		</div>
		<div class="form-row">		
		<div class="form-group col-md-12">
				<?php 
					echo $render_object->label('text');
					echo $render_object->RenderFormElement('text'); 
				?>
			</div>	
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary"><?php echo $render_object->_get('_ui_rules')[$render_object->_get('form_mod')]->name;?></button>
		</div>
	</div>
	<?php
		echo $render_object->RenderFormElement('created'); 
		echo $render_object->RenderFormElement('updated'); 
	echo form_close();
	?>
	</div>
</div>
<!--end nicdark_container-->
</section>
<!--end section-->