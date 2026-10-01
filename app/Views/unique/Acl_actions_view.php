<div class="container-fluid">
	<div class="card">
	  <div class="card-header">
	  	<span class="card-title"><?php echo $render_object->RenderElement('action'); ?></span>
	  </div>
	  <div class="card-body">
		
		<p class="card-text">
			<?php 
				echo $bootstrap_tools->label('id_ctrl').' : '.$render_object->RenderElement('id_ctrl').'<br/>'; 
			?>
		</p>
		<?php
			echo $render_object->render_element_menu();
		?>
	  </div>
	</div>	
</div>
