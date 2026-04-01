 <!--start section-->
 <section class="nicdark_section ">
<!--start nicdark_container-->
<div class="nicdark_container nicdark_clearfix">
	<div class="nicdark_space30"></div>
	<div class="grid grid_6">
		<h1 class="subtitle greydark"><?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_myaccount');?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
            <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_myaccount_subtitle');?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="<?php echo $this->render_object->_getCi('_bg_color');?> nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>

		
	</div>
	<div class="grid grid_6">
		<div class="alert alert-success" role="alert">
			<h4 class="alert-heading"><?php echo $this->lang->line('INFO_FAMILLE_TITLE');?></h4>
			<p><?php echo $this->lang->line('INFO_FAMILLE');?></p>
			<hr>
			<p class="mb-0"><?php echo $this->lang->line('INFO_FAMILLE_FOOTER');?></p>
		</div>
	</div>
	<div class="nicdark_space30"></div>
	<?php
	echo form_open(base_url($this->render_object->_getCi('_controller_name').'/myaccount'), array('class' => '', 'id' => 'edit') , array('form_mod'=>'edit','id'=>$id) );
	//champ obligatoire
	foreach($required_field AS $name){
		echo form_error($name, 	'<div class="alert alert-danger">', '</div>');
	}
	?>


	<div class="card">
		
			<div class="">
			<ul class="nav nav-tabs" id="myTab" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true"><?php echo $this->lang->line('YOUR_FAMILY_MEMBER');?></button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false"><?php echo $this->lang->line('YOUR_FAMILY_DATA');?></button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="contact-tab" data-toggle="tab" data-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false"><?php echo $this->lang->line('YOUR_DELTA_ENFANCE_DATA');?></button>
				</li>
			</ul>
		</div>
		<div class="card-body tab-content" id="myTabContent">
			<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
				<h5 class="card-title">Special title treatment</h5>
				<div class="form-row">
					<div class="form-group col-md-12">
						<?php 
							echo $this->render_object->label('members');
							echo $this->render_object->RenderFormElement('members', null, 'Familys_model', false);
						?>
					</div>	
				</div>
			</div>
			<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
				<div class="form-row">
					<div class="form-group col-md-6">
						<?php 
							echo $this->render_object->label('capacity');
							echo $this->render_object->RenderFormElement('capacity', null, 'Familys_model', false);
						?>
					</div>
					<div class="form-group col-md-6">
						<?php 
							echo $this->render_object->label('e_mail');
							echo $this->render_object->RenderFormElement('e_mail', null, 'Familys_model', true);
						?>
															
						<?php 
							echo $this->render_object->label('e_mail_comp');
							echo $this->render_object->RenderFormElement('e_mail_comp', null, 'Familys_model', false);
						?>
					</div>									
				</div>
			</div>
			<div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
				<div class="form-row">	
					<div class="form-group col-md-4">
						<?php 
							echo $this->render_object->label('login');
							echo $this->render_object->RenderFormElement('login', null, 'Familys_model', true); 
						?>
					</div>	
					<div class="form-group col-md-4">
						<?php 
							echo $this->render_object->label('password');
							echo $this->render_object->RenderFormElement('password', null, 'Familys_model', false);
						?>
					</div>
					<div class="form-group col-md-4">
						<div class="alert alert-info" role="alert">
							<h4 class="alert-heading"><?php echo $this->lang->line('INFO_ACCOUNT_FORM_TITLE');?></h4>
							<p><?php echo $this->lang->line('INFO_ACCOUNT_FORM_BODY');?></p>
							<hr>
							<p class="mb-0"><?php echo $this->lang->line('INFO_ACCOUNT_FORM_FOOTER');?></p>
						</div>
					</div> 
				</div>		
				<div class="form-row">
					<div class="form-group col-md-4">
						<?php 
							echo $this->render_object->label('idfamille');
							echo $this->render_object->RenderFormElement('idfamille', null, 'Familys_model', true);
						?>
					</div>			
					<div class="form-group col-md-4">
						<?php 
							echo $this->render_object->label('nom');
							echo $this->render_object->RenderFormElement('nom', null, 'Familys_model', true); 
						?>
					</div>
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('nb_enfants');
							echo $this->render_object->RenderFormElement('nb_enfants', null, 'Familys_model', true);
						?>
					</div>				
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('ecole');
							echo $this->render_object->RenderFormElement('ecole', null, 'Familys_model', true);
						?>
					</div>									
				</div>
				<div class="form-row">
					<div class="form-group col-md-8">
						<?php 
							echo $this->render_object->label('adresse');
							echo $this->render_object->RenderFormElement('adresse', null, 'Familys_model', true); 
						?>
					</div>			
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('cp');
							echo $this->render_object->RenderFormElement('cp', null, 'Familys_model', true); 
						?>
					</div>
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('ville');
							echo $this->render_object->RenderFormElement('ville', null, 'Familys_model', true);
						?>
					</div>				
				</div>					
			</div>
		</div>
	</div>
	<div class="modal-footer">
	<?php echo $msg;?>
		<button type="submit" class="btn btn-success"><?php echo $this->lang->line('MY_FAMILY_EDITION');?></button>
	</div>
	<?php
	echo $this->render_object->RenderFormElement('created', null, 'Familys_model');
	echo $this->render_object->RenderFormElement('updated', null, 'Familys_model');
	echo form_close();
	?>
</div>
<!--end nicdark_container-->
</section>
<!--end section-->