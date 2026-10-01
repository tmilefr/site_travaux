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
	echo open_form(base_url($render_object->_getCi('_controller_name').'/myaccount'), array('class' => '', 'id' => 'edit') , array('form_mod'=>'edit','id'=>$id) );
	//champ obligatoire
	foreach($required_field AS $name){
		echo field_error($name, 	'<div class="alert alert-danger">', '</div>');
	}
	?>


	<div class="card account-card">
		<div class="account-msg">
			<?php echo $msg;?>
		</div>
		<div class="card-header bg-white border-bottom-0 pt-3 pb-0">
			<ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">
						<span class="oi oi-people" aria-hidden="true"></span>&nbsp;
						<?php echo tr('YOUR_FAMILY_MEMBER');?>
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">
						<span class="oi oi-cog" aria-hidden="true"></span>&nbsp;
						<?php echo tr('YOUR_FAMILY_DATA');?>
					</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="contact-tab" data-toggle="tab" data-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
						<span class="oi oi-key" aria-hidden="true"></span>&nbsp;
						<?php echo tr('YOUR_DELTA_ENFANCE_DATA');?>
					</button>
				</li>
			</ul>
		</div>

		<div class="card-body tab-content" id="myTabContent">

			<!-- =========================================================== -->
			<!-- Onglet 1 : Membres de la famille                              -->
			<!-- =========================================================== -->
			<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

				<p class="text-muted small mb-3">
					<span class="oi oi-info" aria-hidden="true"></span>
					<?php echo tr('INFO_FAMILLE');?>
				</p>

				<div class="form-row">
					<div class="form-group col-md-12">
						<?php
							echo $render_object->label('members');
							echo $render_object->RenderFormElement('members', null, 'Familys_model', false);
						?>
					</div>
				</div>
			</div>

			<!-- =========================================================== -->
			<!-- Onglet 2 : Vos données complémentaires                        -->
			<!-- =========================================================== -->
			<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

				<!-- Section : E-mails de contact -->
				<fieldset class="account-fieldset mb-4">
					<legend class="account-legend">
						<span class="oi oi-envelope-closed" aria-hidden="true"></span>
						<?php echo tr('ACCOUNT_SECTION_CONTACT') ?: 'Vos e-mails de contact'; ?>
					</legend>

					<div class="form-row">
						<div class="form-group col-md-6">
							<?php
								echo $render_object->label('e_mail');
								echo $render_object->RenderFormElement('e_mail', null, 'Familys_model', true);
							?>
						</div>
						<div class="form-group col-md-6">
							<?php
								echo $render_object->label('e_mail_comp');
								echo $render_object->RenderFormElement('e_mail_comp', null, 'Familys_model', false);
							?>
						</div>
					</div>
				</fieldset>

				<!-- Section : Compétences -->
				<div class="grid">
					<div class="grid_6">
						<fieldset class="account-fieldset mb-4">
							<legend class="account-legend">
								<span class="oi oi-wrench" aria-hidden="true"></span>
								<?php echo tr('ACCOUNT_SECTION_SKILLS') ?: 'Vos compétences'; ?>
							</legend>

							<p class="text-muted small mb-2">
								<?php echo tr('ACCOUNT_SKILLS_HELP') ?: "Cochez les domaines dans lesquels vous pouvez aider. Cela nous permet de vous solliciter prioritairement sur les sessions correspondantes."; ?>
							</p>

							<div class="form-row">
								<div class="form-group col-md-12">
									<?php
										// On affiche la liste sans le label par défaut "capacity"
										// (le legend du fieldset joue ce rôle).
										echo $render_object->RenderFormElement('capacity', null, 'Familys_model', false);
									?>
								</div>
							</div>
						</fieldset>
					</div>
					<div class="grid_5">
						<!-- Section : Préférences d'alerte e-mail -->
						<fieldset class="account-fieldset account-fieldset-highlight">
							<legend class="account-legend">
								<span class="oi oi-bell" aria-hidden="true"></span>
								<?php echo tr('ALERT_PREFS_TITLE'); ?>
							</legend>

							<p class="text-muted small mb-2">
								<?php echo tr('ALERT_PREFS_HELP'); ?>
							</p>

							<div class="form-row">
								<div class="form-group col-md-12 mb-0">
									<?php
										echo $render_object->RenderFormElement('alert_types', null, 'Familys_model', false);
									?>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</div>

			<!-- =========================================================== -->
			<!-- Onglet 3 : Données dans Delta Enfance                         -->
			<!-- =========================================================== -->
			<div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">

				<!-- Encart d'info Delta Enfance, en bandeau pleine largeur -->
				<div class="alert alert-info account-alert-delta" role="alert">
					<h5 class="alert-heading mb-1">
						<span class="oi oi-info" aria-hidden="true"></span>
						<?php echo tr('INFO_ACCOUNT_FORM_TITLE');?>
					</h5>
					<p class="mb-1 small"><?php echo tr('INFO_ACCOUNT_FORM_BODY');?></p>
					<p class="mb-0 small"><em><?php echo tr('INFO_ACCOUNT_FORM_FOOTER');?></em></p>
				</div>

				<!-- Section : Identifiants de connexion -->
				<fieldset class="account-fieldset mb-4">
					<legend class="account-legend">
						<span class="oi oi-lock-locked" aria-hidden="true"></span>
						<?php echo tr('ACCOUNT_SECTION_LOGIN') ?: 'Identifiants de connexion'; ?>
					</legend>

					<div class="form-row">
						<div class="form-group col-md-6">
							<?php
								echo $render_object->label('login');
								echo $render_object->RenderFormElement('login', null, 'Familys_model', true);
							?>
						</div>
						<div class="form-group col-md-6">
							<?php
								echo $render_object->label('password');
								echo $render_object->RenderFormElement('password', null, 'Familys_model', false);
							?>
						</div>
					</div>
				</fieldset>

				<!-- Section : Identité famille -->
				<fieldset class="account-fieldset mb-4">
					<legend class="account-legend">
						<span class="oi oi-person" aria-hidden="true"></span>
						<?php echo tr('ACCOUNT_SECTION_IDENTITY') ?: 'Identité de la famille'; ?>
					</legend>

					<div class="form-row">
						<div class="form-group col-md-3">
							<?php
								echo $render_object->label('idfamille');
								echo $render_object->RenderFormElement('idfamille', null, 'Familys_model', true);
							?>
						</div>
						<div class="form-group col-md-5">
							<?php
								echo $render_object->label('nom');
								echo $render_object->RenderFormElement('nom', null, 'Familys_model', true);
							?>
						</div>
						<div class="form-group col-md-2">
							<?php
								echo $render_object->label('nb_enfants');
								echo $render_object->RenderFormElement('nb_enfants', null, 'Familys_model', true);
							?>
						</div>
						<div class="form-group col-md-2">
							<?php
								echo $render_object->label('ecole');
								echo $render_object->RenderFormElement('ecole', null, 'Familys_model', true);
							?>
						</div>
					</div>
				</fieldset>

				<!-- Section : Adresse postale -->
				<fieldset class="account-fieldset mb-2">
					<legend class="account-legend">
						<span class="oi oi-map-marker" aria-hidden="true"></span>
						<?php echo tr('ACCOUNT_SECTION_ADDRESS') ?: 'Adresse postale'; ?>
					</legend>

					<div class="form-row">
						<div class="form-group col-md-8">
							<?php
								echo $render_object->label('adresse');
								echo $render_object->RenderFormElement('adresse', null, 'Familys_model', true);
							?>
						</div>
						<div class="form-group col-md-2">
							<?php
								echo $render_object->label('cp');
								echo $render_object->RenderFormElement('cp', null, 'Familys_model', true);
							?>
						</div>
						<div class="form-group col-md-2">
							<?php
								echo $render_object->label('ville');
								echo $render_object->RenderFormElement('ville', null, 'Familys_model', true);
							?>
						</div>
					</div>
				</fieldset>
			</div>

		</div><!-- /.card-body -->

		<!-- Pied de carte : message + bouton de sauvegarde -->
		<div class="card-footer bg-light account-card-footer d-flex flex-wrap align-items-center justify-content-between">
			<button type="submit" class="btn btn-success">
				<span class="oi oi-check" aria-hidden="true"></span>&nbsp;
				<?php echo tr('MY_FAMILY_EDITION');?>
			</button>
		</div>
	</div><!-- /.card -->

	<?php
	echo $render_object->RenderFormElement('created', null, 'Familys_model');
	echo $render_object->RenderFormElement('updated', null, 'Familys_model');
	echo form_close();
	?>
</div>
<!--end nicdark_container-->
</section>
<!--end section-->
