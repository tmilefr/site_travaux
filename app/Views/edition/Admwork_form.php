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

		<div class="card" >
			<?php
			echo form_open(base_url($this->render_object->_getCi('_controller_name').'/'.$this->render_object->_get('form_mod')), array('class' => '', 'id' => 'edit') , array('form_mod'=>$this->render_object->_get('form_mod'),'id'=>$id,'archived'=>0) );
			?>
			<div class="card-header">
				<?php
				//champ obligatoire
				foreach($required_field AS $name){
					echo form_error($name, 	'<div class="alert alert-danger">', '</div>');
				}
				//echo validation_errors();
				?>
			</div>	
			<div class="card-body">
				<div class="form-row">
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('type');
							echo $this->render_object->RenderFormElement('type'); 
						?>
					</div>
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('date_travaux');
							echo $this->render_object->RenderFormElement('date_travaux'); 
						?>
					</div>	
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('nb_inscrits_max');
							echo $this->render_object->RenderFormElement('nb_inscrits_max'); 
						?>
					</div>						
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('ecole');
							echo $this->render_object->RenderFormElement('ecole'); 
						?>
					</div>	
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('accespar');
							echo $this->render_object->RenderFormElement('accespar'); 
						?>
					</div>						
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('statut');
							echo $this->render_object->RenderFormElement('statut'); 
						?>
					</div>	
				</div>	
				<div class="form-row">
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('type_session');
							echo $this->render_object->RenderFormElement('type_session'); 
						?>
					</div>									
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('heure_deb_trav');
							echo $this->render_object->RenderFormElement('heure_deb_trav');
						?>
					</div>
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('heure_fin_trav');
							echo $this->render_object->RenderFormElement('heure_fin_trav');
						?>
					</div>		
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('nb_units');
							echo $this->render_object->RenderFormElement('nb_units'); 
						?>
					</div>
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('civil_year');
							echo $this->render_object->RenderFormElement('civil_year'); 
						?>
					</div>						
				</div>				
				<div class="form-row">
					<div class="form-group col-md-7">
						<?php 
							echo $this->render_object->label('titre');
							echo $this->render_object->RenderFormElement('titre'); 
						?>
					</div>
					<div class="form-group col-md-2">
						<?php 
							echo $this->render_object->label('txtmodel');
							echo $this->render_object->RenderFormElement('txtmodel'); 
						?>
					</div>					
					<div class="form-group col-md-3">
						<?php 
							echo $this->render_object->label('referent_travaux');
							echo $this->render_object->RenderFormElement('referent_travaux'); 
						?>
					</div>					
				</div>		
				<div class="form-row">
					<div class="form-group col-md-12">
						<?php 
							echo $this->render_object->label('description');
							echo $this->render_object->RenderFormElement('description'); 
						?>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><?php echo $this->render_object->_get('_ui_rules')[$this->render_object->_get('form_mod')]->name;?></button>
				</div>
			<?php
			echo $this->render_object->RenderFormElement('created'); 
			echo $this->render_object->RenderFormElement('updated'); 

			echo form_close();
			?>
			</div>
		</div>
	</div>
<!--end nicdark_container-->
</section>
<!--end section-->
