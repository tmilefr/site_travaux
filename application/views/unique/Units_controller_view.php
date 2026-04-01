<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		<div class="nicdark_space30"></div>
		<div class="card">
			<div class="card-header">
				<span class="card-title"><?php echo $this->render_object->RenderElement('unites_id_famille')?></span>
			</div>
			<div class="card-body">
				<h5 class="card-title">
					<?php 
						echo $this->render_object->RenderElement('unites_valides').' '.$this->render_object->RenderElement('type_session'); 
					?>
				</h5>
				<p class="card-text">
                    <h3 class="subtitle grey">
                        <i class="icon-calendar"></i> <?php echo $this->render_object->RenderElement('unites_date')?>
                        <i class="icon-clock-1"></i><?php echo $this->render_object->RenderElement('unites_heure_debut');?> à <?php $this->render_object->RenderElement('unites_heure_fin');?>
                    </h3>                    
                    <?php 
						echo $this->render_object->RenderElement('unites_desc');
                        echo '<br/>';
                        echo $this->render_object->RenderElement('unites_comm');
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
