<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="container-fluid">

	<div class="row">
		<div class="form-group col-md-2">
			<?php
				echo $this->bootstrap_tools->label('app_name');
				echo $this->render_object->RenderFormElement('app_name');
			?>
		</div>
		<div class="form-group col-md-2">
			<?php
				echo $this->bootstrap_tools->label('slogan');
				echo $this->render_object->RenderFormElement('slogan');
			?>
		</div>
		<div class="form-group col-md-2">
			<?php
				echo $this->bootstrap_tools->label('debug_app');
				echo $this->render_object->RenderFormElement('debug_app');
			?>
		</div>

	</div>
</div>

<?php

echo '<pre><code>'.print_r($this->Parameters_model->_get('defs') , 1).'</code></pre>';
?>
