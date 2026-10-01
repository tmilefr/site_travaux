<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>
		<div class="card">
			<div class="card-header">
				<span class="card-title"><?php echo $this->render_object->RenderElement('reference')?></span>
			</div>
			<div class="card-body">
				<h5 class="card-title">
					<?php 
						echo $this->render_object->RenderElement('email').' '.$this->render_object->RenderElement('object'); 
					?>
				</h5>
				<p class="card-text">
                    <span class="subtitle grey">
                        <i class="icon-calendar"></i> <?php echo $this->render_object->RenderElement('created')?>
                        <i class="icon-clock-1"></i><?php echo $this->render_object->RenderElement('updated');?>
					</span>                    
                    <?php 
					 	echo '<br/>';
						echo $this->render_object->RenderElement('message');
						echo '<br/>';
						echo $this->render_object->RenderElement('statut');
					?>
				</p>
                <?php 
					echo $this->render_object->label('detail_statut');
					echo $this->render_object->RenderElement('detail_statut');
                ?>
						
				<?php
				
					echo $this->render_object->render_element_menu();
				?>
			</div>
		</div>	
	</div>
<!--end nicdark_container-->
</section>
<!--end section-->
