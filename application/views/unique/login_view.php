<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

</section>
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
        <h1 class="subtitle greydark"><?php echo $this->lang->line('TITLE_PAGE_LOGIN');?></h1>
        <div class="nicdark_space20"></div>
        <h3 class="subtitle grey">
            <?php echo $this->lang->line('SUBTITLE_PAGE_LOGIN');?>
        </h3>
        </div>
    <!--end nicdark_container-->
    <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
        <div class="nicdark_space10"></div>
</section>  


<!--start section-->
<section class="nicdark_section">
        <?php
          if (isset($_SERVER['CI_ENV']) && $_SERVER['CI_ENV']){
            switch($_SERVER['CI_ENV']){  
              case 'development':
              case "testing":
                echo Lang('ALERT_TEST').' <a href="https://mulhouse-travaux.abcmzwei.eu/" class="badge badge-success">'.Lang('PARENT_SITE').'</a>';
              break;
            }
          }
        ?>

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space10"></div>
          <div class="grid grid_6">
              <div class="nicdark_archive1 hRT2 nicdark_bg_yellow nicdark_bg_yellowdark_hover nicdark_transition nicdark_radius nicdark_shadow">
                  <div class="nicdark_margin20 nicdark_relative">  
                      <a href="#" target='_new' class="nicdark_displaynone_ipadpotr nicdark_btn_icon nicdark_bg_yellowdark medium nicdark_radius_circle white nicdark_absolute nicdark_shadow"><i class="icon-attach-outline"></i></a>
          
                      <div class="nicdark_activity nicdark_marginleft70 nicdark_disable_marginleft_ipadpotr">
                          <h4 class="white"><?php echo LANG('TITLE_BOX_REGIO');?></h4>                        
                          <div class="nicdark_space20"></div>
                          <p class="white"><?php echo LANG('TEXT_BOX_REGIO');?></p>
                      </div>
                  </div>
              </div>
          </div>
          <div class="grid grid_6">
            <div class="nicdark_archive1 hRT2 nicdark_bg_green nicdark_bg_greendark_hover nicdark_transition nicdark_radius nicdark_shadow">
                <div class="nicdark_margin20 nicdark_relative">  
                    <a href="<?php echo lang('DELTA_LINK');?>" target='_new' class="nicdark_displaynone_ipadpotr nicdark_btn_icon nicdark_bg_greendark medium nicdark_radius_circle white nicdark_absolute nicdark_shadow"><i class="icon-attach-outline"></i></a>
        
                    <div class="nicdark_activity nicdark_marginleft70 nicdark_disable_marginleft_ipadpotr">
                        <h4 class="white"><?php echo LANG('TITLE_BOX_DELTA');?></h4>                        
                        <div class="nicdark_space20"></div>
                        <p class="white"><?php echo LANG('TEXT_BOX_DELTA');?> <a href="<?php echo lang('DELTA_LINK');?>" target='_new'> <?php echo LANG('TITLE_LINK');?></a></p>
                    </div>
                </div>
            </div>
          </div>
    </div>
    <!--end nicdark_container-->
     
</section>

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space20"></div>
          <div class="grid grid_6">
            <h5 class="nicdark_toogle_header grey nicdark_textevidence nicdark_bg_grey big nicdark_radius nicdark_shadow">
                Connection site regio
                <i class="icon-info-outline nicdark_iconbg right medium grey"></i>
            </h5>
            <div class="nicdark_space10"></div>
            <div class="nicdark_toogle_content nicdark_bg_grey nicdark_radius_bottom nicdark_shadow">
                <?php
                echo form_open(base_url('/Home/login'), array('class' => 'login', 'id' => 'login-form') , array('form_mod'=>'','type_cnx'=>'DELTA') );
                echo $this->session->flashdata('message');
                ?>
                <div class="card-body">
                  <div class="form-group">
                    <?php echo form_label('Login', 'login'); ?>
                    <?php echo form_input('login', '', 'class="form-control" aria-describedby="emailHelp" placeholder="E-mail"'); ?>
                    <?php echo form_error('login', 	'<div class="alert alert-danger">', '</div>'); ?>
                  </div>
                  <div class="form-group">
                    <?php echo form_label('Password', 'password'); ?>
                    <?php echo form_password('password', 'password', 'class="form-control" aria-describedby="passwordHelp" placeholder="Mot de passe"'); ?>
                    <?php echo form_error('password', 	'<div class="alert alert-danger">', '</div>'); ?>	  
                  </div>	
                  <div class="form-group">
                    <div class="modal-footer">
                      <?php 
                      if ($this->config->item('captcha')){
                        echo $this->render_object->label('recaptchaResponse');
                        echo $this->render_object->RenderFormElement('recaptchaResponse'); 
                        if ($captcha_error){
                          $this->bootstrap_tools->render_msg($captcha_error);
                        }
                      } else { ?>
                        <button type="submit" class="btn nicdark_btn nicdark_bg_yellow medium nicdark_radius white"><?php echo $this->lang->line('CNX_ME');?></button>
                      <?php }
                      if ($login_error){
                        $this->bootstrap_tools->render_msg($login_error);
                      }
                      ?>
                    </div>
                  </div>          			
                </div>
                <?php
                echo form_close();
                ?>
              </div>
          </div>
          <div class="grid grid_6">    
            <div class="nicdark_archive1 hRT2 nicdark_bg_blue nicdark_bg_bluedark_hover nicdark_transition nicdark_radius nicdark_shadow">
                <div class="nicdark_margin20 nicdark_relative">  
                    <a href="<?php echo lang('ABCM_LINK');?>" class="nicdark_displaynone_ipadpotr nicdark_btn_icon nicdark_bg_bluedark medium nicdark_radius_circle white nicdark_absolute nicdark_shadow"><i class="icon-attach-outline"></i></a></a>
        
                    <div class="nicdark_activity nicdark_marginleft70 nicdark_disable_marginleft_ipadpotr">
                        <h4 class="white"><?php echo LANG('TITLE_BOX_ABCM');?></h4>                        
                        <div class="nicdark_space20"></div>
                        <p class="white"><?php echo LANG('TEXT_BOX_ABCM');?> <a href="<?php echo lang('ABCM_LINK');?>" target='_new'><?php echo LANG('TITLE_LINK');?></a></p>
                    </div>
                </div>
            </div>
          </div>
      </div>
    </div>
    <!--end nicdark_container-->
</section>  
	  


<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
grecaptcha.ready(function() {
    grecaptcha.execute('<?php echo SITE_CAPTCHA_KEY;?>', {action: 'homepage'}).then(function(token) {
        document.getElementById('recaptchaResponse').value = token
    });
});
</script>
