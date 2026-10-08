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
			echo open_form_multipart(base_url($render_object->_getCi('_controller_name').'/'.$render_object->_get('form_mod')), array('class' => '', 'id' => 'edit') , array('form_mod'=>$render_object->_get('form_mod'),'id'=>$id,'hit'=>$render_object->RenderElement('hit')) );
		?>
		<?php //echo tr($render_object->_getCi('_controller_name').'_header_'.$render_object->_get('form_mod'));?>
		<?php
		//champ obligatoire
		foreach($required_field AS $name){
			echo validation_show_error($name, 'alert');
		}
		?>	
		<div class="card">
			<div class="">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="profile-tab" data-toggle="tab" data-target="#groupinfo" type="button" role="tab" aria-controls="profile" aria-selected="false"><?php echo tr('GROUP_INFO');?></button>
					</li>				
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="home-tab" data-toggle="tab" data-target="#member" type="button" role="tab" aria-controls="home" aria-selected="true"><?php echo tr('GROUP_MEMBER');?></button>
					</li>
				</ul>
			</div>
			<div class="card-body tab-content">
				<div class="tab-pane fade show active" id="groupinfo" role="tabpanel" aria-labelledby="profile-tab">
					<div class="form-row">
						<div class="form-group col-md-2">
							<?php 
								echo $render_object->label('short');
								echo $render_object->RenderFormElement('short');
							?>
						</div>
						<div class="form-group col-md-4">
							<?php 
								echo $render_object->label('title');
								echo $render_object->RenderFormElement('title');
							?>
						</div>
						<div class="form-group col-md-2">
							<?php 
								echo $render_object->label('type');
								echo $render_object->RenderFormElement('type');
							?>
						</div>					
						<div class="form-group col-md-2">
							<?php 
								echo $render_object->label('color');
								echo $render_object->RenderFormElement('color');
							?>
						</div>					
						<div class="form-group col-md-2">
							<?php 
								echo $render_object->label('listorder');
								echo $render_object->RenderFormElement('listorder');
							?>
						</div>						
					</div>
					<div class="form-row">
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('intro');
								echo $render_object->RenderFormElement('intro'); 
							?>
						</div>	
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('mission');
								echo $render_object->RenderFormElement('mission'); 
							?>
						</div>						
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('actions');
								echo $render_object->RenderFormElement('actions'); 
							?>
						</div>
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('role');
								echo $render_object->RenderFormElement('role'); 
							?>
						</div>
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('needs');
								echo $render_object->RenderFormElement('needs'); 
							?>
						</div>	
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('search');
								echo $render_object->RenderFormElement('search'); 
							?>
						</div>						
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('related');
								echo $render_object->RenderFormElement('related'); 
							?>
						</div>																		
					</div>					
				</div>
				<div class="tab-pane fade" id="member" role="tabpanel" aria-labelledby="profile-tab">
					<div class="form-row">
						<div class="form-group col-md-12">
							<?php 
								echo $render_object->label('acteursOrgchart');
								echo $render_object->RenderFormElement('acteurs');
							?>
						</div>								
					</div>				
				</div>
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
<!--end nicdark_container-->
</section>
<!--end section-->
