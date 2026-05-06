<section class="nicdark_section">
	<div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>

		<div class="grid grid_12">
			<h1 class="subtitle greydark">
				<?php echo $this->lang->line('Acl_controllers_controller_bulk_add_action'); ?>
			</h1>
			<div class="nicdark_space20"></div>
			<h3 class="subtitle grey">
				<?php echo $this->lang->line('Acl_controllers_controller_bulk_subtitle'); ?>
			</h3>
			<div class="nicdark_space20"></div>
			<div class="nicdark_divider left big"><span class="nicdark_bg_red nicdark_radius"></span></div>
			<div class="nicdark_space10"></div>
		</div>

		<?php if ($this->session->flashdata('bulk_error')): ?>
			<div class="alert alert-danger"><?php echo $this->session->flashdata('bulk_error'); ?></div>
		<?php endif; ?>

		<div class="card">
			<div class="card-header">
				<?php echo $this->lang->line('Acl_controllers_controller_bulk_add_action'); ?>
			</div>
			<div class="card-body">

				<?php
				echo form_open('Acl_controllers_controller/bulk_add_action', array('id' => 'bulk_add_action_form'));
				?>

				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="action_name"><?php echo $this->lang->line('Acl_controllers_controller_bulk_action_name'); ?></label>
						<input
							type="text"
							class="form-control"
							id="action_name"
							name="action_name"
							value="<?php echo htmlspecialchars($action_name, ENT_QUOTES, 'UTF-8'); ?>"
							placeholder="ex: JsonData, export, bulk"
							required
							pattern="[A-Za-z][A-Za-z0-9_]{0,254}"
						>
						<small class="form-text text-muted">
							<?php echo $this->lang->line('Acl_controllers_controller_bulk_action_help'); ?>
						</small>
					</div>
				</div>

				<?php if ($action_name === ''): ?>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">
							<?php echo $this->lang->line('Acl_controllers_controller_bulk_preview'); ?>
						</button>
					</div>
				<?php else: ?>

					<div class="row">
						<div class="col-md-6">
							<h4 class="text-success">
								<?php echo sprintf(
									$this->lang->line('Acl_controllers_controller_bulk_to_add_x'),
									count($preview['to_add'])
								); ?>
							</h4>
							<?php if (count($preview['to_add'])): ?>
								<ul class="list-group">
									<?php foreach ($preview['to_add'] as $row): ?>
										<li class="list-group-item list-group-item-success">
											<?php echo htmlspecialchars($row['controller'], ENT_QUOTES, 'UTF-8'); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else: ?>
								<p class="text-muted">
									<?php echo $this->lang->line('Acl_controllers_controller_bulk_nothing_to_add'); ?>
								</p>
							<?php endif; ?>
						</div>

						<div class="col-md-6">
							<h4 class="text-muted">
								<?php echo sprintf(
									$this->lang->line('Acl_controllers_controller_bulk_existing_x'),
									count($preview['existing'])
								); ?>
							</h4>
							<?php if (count($preview['existing'])): ?>
								<ul class="list-group">
									<?php foreach ($preview['existing'] as $row): ?>
										<li class="list-group-item list-group-item-light">
											<?php echo htmlspecialchars($row['controller'], ENT_QUOTES, 'UTF-8'); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>

					<div class="modal-footer mt-3">
						<a href="<?php echo site_url('Acl_controllers_controller/bulk_add_action'); ?>"
						   class="btn btn-secondary">
							<?php echo $this->lang->line('CANCEL'); ?>
						</a>

						<?php if (count($preview['to_add'])): ?>
							<input type="hidden" name="confirm" value="1">
							<button type="submit" class="btn btn-primary">
								<?php echo sprintf(
									$this->lang->line('Acl_controllers_controller_bulk_confirm_x'),
									count($preview['to_add'])
								); ?>
							</button>
						<?php endif; ?>
					</div>

				<?php endif; ?>

				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</section>