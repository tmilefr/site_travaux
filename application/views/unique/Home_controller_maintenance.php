<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
        <h1 class="subtitle greydark"><?php echo $this->lang->line('IN_MAINTENANCE');?></h1>
        <div class="nicdark_space20"></div>
        <h3 class="subtitle grey">
            <?php echo $this->lang->line('IN_MAINTENANCE_subtitle');?>
        </h3>
        <div class="nicdark_space20"></div>
        <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
        <div class="nicdark_space10"></div>
          <?php
          echo form_open(base_url('/Home/maintenance'), array('class' => 'login', 'id' => 'login-form') , array('form_mod'=>'') );
          echo $this->session->flashdata('message');
          ?>
          <div class="grid grid_3"></div>
          <div class="card grid grid_6" ><?php echo $this->lang->line('WHE_ARE_IN_MAINTENANCE');?></div>
        </div>	
        <?php
        echo form_close();
      ?>
    </div>
    <!--end nicdark_container-->
</section>  

