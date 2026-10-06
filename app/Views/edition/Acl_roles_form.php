 <!--start section-->
 <section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>

		<div class="grid grid_12">
		<h1 class="subtitle greydark"><?php echo tr('Acl_controllers_controller_'.$render_object->_get('form_mod'));?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
            <?php echo tr('Acl_controllers_controller_subtitle');?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="nicdark_bg_red nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>
	</div>

	<div class="card" >
		<div class="card-header">
			<?php echo tr('Acl_roles_controller_'.$render_object->_get('form_mod'));?>
		</div>
		<div class="card-body">
		<?php
		echo open_form('Acl_roles_controller/'.$render_object->_get('form_mod'), array('class' => '', 'id' => 'edit') , array('form_mod'=>$render_object->_get('form_mod'),'id'=>$id) );

		//champ obligatoire
		foreach($required_field AS $name){
			echo field_error($name, 	'<div class="alert alert-danger">', '</div>');
		}
		?>
		<div class="form-row">
			<div class="form-group col-md-4">
				<?php 
					echo $bootstrap_tools->label('role_name');
					echo $render_object->RenderFormElement('role_name'); 
				?>
			</div>
			<div class="form-group col-md-8">
				<?php 
					echo $bootstrap_tools->label('role_description');
					echo $render_object->RenderFormElement('role_description');
				?>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary"><?php echo $render_object->_get('_ui_rules')[$render_object->_get('form_mod')]->name;?></button>
		</div>
		<?php
		echo $render_object->RenderFormElement('created'); 
		echo $render_object->RenderFormElement('updated'); 
		echo form_close();
		?>
		</div>
	</div>
</div>
<!--end nicdark_container-->
</section>
<!--end section-->