<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>
		<div class="card">
			<div class="card-header">
				<span class="card-title"><?php echo $this->render_object->RenderElement('name')?></span>
			</div>
			<div class="card-body">
				<h5 class="card-title">
					<?php 
						echo $this->render_object->RenderElement('path').' '.$this->render_object->RenderElement('type'); 
					?>
				</h5>
				<p class="card-text">
					<?php
						echo $this->render_object->RenderElement('memo');
					?>
				</p>		
				<?php
					echo $this->render_object->render_element_menu();
				?>
			</div>
		</div>	
	</div>
<!--end nicdark_container-->
</section>
<!--end section-->
