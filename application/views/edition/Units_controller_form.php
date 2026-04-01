<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>

		<div class="grid grid_12">
		<h1 class="subtitle greydark"><?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_get('form_mod'));?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
            <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_subtitle');?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="<?php echo $this->render_object->_getCi('_bg_color');?> nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>
	</div>
	<?php
	echo form_open($this->render_object->_getCi('_controller_name').'/'.$this->render_object->_get('form_mod'), array('class' => '', 'id' => 'edit') , array('form_mod'=>$this->render_object->_get('form_mod'),'id'=>$id,'civil_year'=>$this->config->item('civil_year'),'archived'=>0) );

	//champ obligatoire
	foreach($required_field AS $name){
		echo form_error($name, 	'<div class="alert alert-danger">', '</div>');
	}
	?>
	<div class="card" >
		<div class="card-header">
			<?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_get('form_mod'));?>
		</div>	
	<div class="card-body">
		<div class="form-row">
			<div class="form-group col-md-3">
				<?php 
					echo $this->render_object->label('unites_id_famille');
					echo $this->render_object->RenderFormElement('unites_id_famille'); 
				?>
			</div>
			<div class="form-group col-md-3">
				<?php 
					echo $this->render_object->label('unites_valides');
					echo $this->render_object->RenderFormElement('unites_valides'); 
				?>
			</div>
			<div class="form-group col-md-3">
				<?php 
					echo $this->render_object->label('type_session');
					echo $this->render_object->RenderFormElement('type_session'); 
				?>
			</div>	
			<div class="form-group col-md-3">
				<?php 
					echo $this->render_object->label('civil_year');
					echo $this->render_object->RenderFormElement('civil_year'); 
				?>
			</div>			
		</div>
		<div class="form-row">		
			<div class="form-group col-md-4">
				<?php 
					echo $this->render_object->label('unites_date');
					echo $this->render_object->RenderFormElement('unites_date');
				?>
			</div>
			<div class="form-group col-md-4">
				<?php 
					echo $this->render_object->label('unites_heure_debut');
					echo $this->render_object->RenderFormElement('unites_heure_debut'); 
				?>
			</div>	
			<div class="form-group col-md-4">
				<?php 
					echo $this->render_object->label('unites_heure_fin');
					echo $this->render_object->RenderFormElement('unites_heure_fin'); 
				?>
			</div>	
		</div>
		<div class="form-row">	
			<div class="form-group col-md-6">
				<?php 
					echo $this->render_object->label('unites_desc');
					echo $this->render_object->RenderFormElement('unites_desc'); 
				?>
			</div>	
			<div class="form-group col-md-6">
				<?php 
					echo $this->render_object->label('unites_comm');
					echo $this->render_object->RenderFormElement('unites_comm'); 
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