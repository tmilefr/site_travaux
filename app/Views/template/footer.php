<?php  ?>
<div class="nicdark_space30"></div>

<div class="nicdark_space3 nicdark_bg_gradient"></div>

<!--start section-->
<section class="nicdark_section nicdark_bg_greydark">

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space30"></div>

        <div class="grid grid_3 nomargin percentage">

		</div>

        <div class="grid grid_3 nomargin percentage">

        </div>

        <div class="grid grid_3 nomargin percentage">
            

        </div>

        <div class="grid grid_3 nomargin percentage">

            
        </div> 

        <div class="nicdark_space50"></div> 

    </div>
    <!--end nicdark_container-->
            
</section>
<!--end section-->

<!--start section-->
<div class="nicdark_section nicdark_bg_greydark2 nicdark_copyrightlogo">

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">

        <div class="grid grid_6 nicdark_aligncenter_iphoneland nicdark_aligncenter_iphonepotr">
            <div class="nicdark_space20"></div>
            <p class="white">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . \CodeIgniter\CodeIgniter::CI_VERSION . '</strong>' : '' ?></p>
        </div>
		<div class="grid grid_6">
			<div class="nicdark_space20"></div>
			<p class="white"><a href="<?php echo base_url('Home/About');?>"><span class="oi oi-browser"></span> <?php echo tr('About');?></a></p>
		</div>
    </div>
    <!--end nicdark_container-->           
</div>
<!--end section-->        
</div>

	<!-- Modal -->
	<div class="modal fade" id="AboutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel"><?php echo config('Travaux')->appName;?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<?php 
				echo config('Travaux')->about;
			?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo tr('Close');?></button>
		  </div>
		</div>
	  </div>
	</div>
	
	<!- modal tools for delete ->
	<div id="confirmModal" class="modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title"><?php echo tr('DELETE_CONFIRMATION');?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<p><?php echo tr('TXT_DELETE_CONFIRMATION');?></p>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-success" data-dismiss="modal"><?php echo tr('CANCEL');?></button>
			<button type="button" class="btn btn-danger" id="confirmModalYes"><?php echo tr('YES');?></button>
		  </div>
		</div>
	  </div>
	</div>	
	
	<!-- Optional JavaScript -->
	<?php $bootstrap_tools->RenderAttachFiles('js');?>
	<?php $bootstrap_tools->RenderAttachFiles('txt');?>
  </body>
</html>
