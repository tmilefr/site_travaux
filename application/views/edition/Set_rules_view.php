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
			<?php echo $this->lang->line('Acl_roles_controller_rules');?>
		</div>
		<div class="card-body">
			<?php 
			echo form_open('Acl_roles_controller/set_rules/'.$id, array('class' => '', 'id' => 'edit') , array('form_mod'=>'roles','id'=>$id) );
			?>
			<table class="table table-striped table-sm">
			<thead>
				<tr>			
					<th scope="col">&nbsp;</th>
					<th scope="col"><?php echo $this->lang->line('controller');?></th>
					<th scope="col"><?php echo $this->lang->line('actions');?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
			foreach($ctrls AS $key => $ctrl){
				echo '<tr>';
				echo '<td>';
					
				echo '</td>';	
				echo '<td>'.$ctrl->controller.'</td>';
				echo '<td><div class="nicdark_clearfix">';
				foreach($ctrl->actions AS $action){ 
					?>
					<div class="grid grid_2">
						<div class="custom-control custom-switch form-check-inline">
							<input type="checkbox" <?php echo (($action->allow ) ? 'checked="checked"':'');?> class="custom-control-input" name="rules[]" id="customSwitch<?php echo $ctrl->id.'_'.$action->id;?>" value="<?php echo $ctrl->id.'_'.$action->id;?>">
							<label class="custom-control-label" for="customSwitch<?php echo $ctrl->id.'_'.$action->id;?>"><?php echo $action->action;?></label>
						</div>
					</div>
				<?php 
					
				}
				echo '</div></td>';
				echo '</tr>';
			}
			?>
			</tbody>
			</table>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary "><?php echo $this->lang->line('VALIDER');?></button>
			</div>
			<?php
				echo form_close();
			?>
		</div>
	</div>

	</div>
<!--end nicdark_container-->
</section>
<!--end section-->
