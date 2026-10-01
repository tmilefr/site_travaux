
 <!--start section-->
 <section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>

		<div class="grid grid_12">
		<h1 class="subtitle greydark"><?php echo $this->lang->line('Acl_controllers_controller_'.$this->render_object->_get('form_mod'));?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
            <?php echo $this->lang->line('Acl_controllers_controller_subtitle');?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="nicdark_bg_red nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>
	</div>
	<div class="card" >
		<div class="card-header">
			<?php echo $this->lang->line('Acl_users_controller_'.$this->render_object->_get('form_mod'));?>
		</div>	
		<div class="card-body">
			<?php
			echo form_open('Acl_users_controller/'.$this->render_object->_get('form_mod'), array('class' => '', 'id' => '') , array('form_mod'=>$this->render_object->_get('form_mod'),'id'=>$id) );

			//champ obligatoire
			foreach($required_field AS $name){
				echo form_error($name, 	'<div class="alert alert-danger">', '</div>');
			}
			?>
			<div class="form-row">
				<div class="form-group col-md-4">
					<?php 
						echo $this->render_object->label('login');
						echo $this->render_object->RenderFormElement('login'); 
					?>
				</div>
				<div class="form-group col-md-4">
					<?php 
						echo $this->render_object->label('password');
						echo $this->render_object->RenderFormElement('password');
					?>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-4">
					<?php 
						echo $this->render_object->label('name');
						echo $this->render_object->RenderFormElement('name'); 
					?>
				</div>
				<div class="form-group col-md-4">
					<?php 
						echo $this->render_object->label('role_id');
						echo $this->render_object->RenderFormElement('role_id'); 
					?>
				</div>	
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><?php echo $this->render_object->_get('_ui_rules')[$this->render_object->_get('form_mod')]->name;?></button>
			</div>
		</div>
		<?php
			echo $this->render_object->RenderFormElement('created'); 
			echo $this->render_object->RenderFormElement('updated'); 
		echo form_close();
		?>
	</div>
	</div>
<!--end nicdark_container-->
</section>
<!--end section-->
