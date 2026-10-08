
 <!--start section-->
 <section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>

		<div class="grid grid_12">
		<h1 class="subtitle greydark"><?php echo tr('Parameters_'.$render_object->_get('form_mod'));?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
            <?php echo tr('Parameters_subtitle');?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="nicdark_bg_red nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>
	</div>
	<?php
	echo open_form( base_url($render_object->_getCi('_controller_name').'/list') , array('class' => '', 'id' => 'edit') , array('form_mod'=>'','id'=>'') );

	$bloc = '';
			foreach(model('Parameters_model')->_get('defs') AS $field => $def){
			if ($def->bloc != $bloc){
				if ($bloc != ''){
					echo '</div></div><br/>';
				}
				echo '<div class="card"><div class="card-header">'.$def->bloc.'</div><div class="card-body">';
				$bloc = $def->bloc;
			}

			?>
			<div class="form-row">
				<div class="col">
					<?php 
						echo validation_show_error($field , 'alert');
						echo $render_object->label($field);
						echo $render_object->RenderFormElement($field); 
					?>
				</div>
			</div>
			<?php } ?>
			<br/>
			<div class="form-row">
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><?php echo tr('valid');?></button>
				</div>
			</div>
		</div>
	</div>
		
	<?php
	echo form_close();
	?>

</div>
<!--end nicdark_container-->
</section>
<!--end section-->