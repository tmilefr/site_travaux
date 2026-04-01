<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space10"></div>
          <div class="grid grid_4">
              <div class="nicdark_archive1 hRT2 nicdark_bg_yellow nicdark_bg_yellowdark_hover nicdark_transition nicdark_radius nicdark_shadow">
                  <div class="nicdark_margin20 nicdark_relative">  
                      <a href="#" class="nicdark_displaynone_ipadpotr nicdark_btn_icon nicdark_bg_yellowdark medium nicdark_radius_circle white nicdark_absolute nicdark_shadow"><i class="icon-attach-outline"></i></a>
          
                      <div class="nicdark_activity nicdark_marginleft70 nicdark_disable_marginleft_ipadpotr">
                      <h4 class="white"><?php echo LANG('TITLE_BOX_REGIO');?></h4>                        
                          <div class="nicdark_space20"></div>
                          <p class="white"><?php echo LANG('TEXT_BOX_REGIO_CONNECTED');?></p>
                      </div>
                  </div>
              </div>
          </div>
          <div class="grid grid_4">
            <div class="nicdark_archive1 hRT2 nicdark_bg_green nicdark_bg_greendark_hover nicdark_transition nicdark_radius nicdark_shadow">
                <div class="nicdark_margin20 nicdark_relative">  
                    <a href="<?php echo lang('DELTA_LINK');?>" class="nicdark_displaynone_ipadpotr nicdark_btn_icon nicdark_bg_greendark medium nicdark_radius_circle white nicdark_absolute nicdark_shadow"><i class="icon-attach-outline"></i></a>
        
                    <div class="nicdark_activity nicdark_marginleft70 nicdark_disable_marginleft_ipadpotr">
                        <h4 class="white"><?php echo LANG('TITLE_BOX_DELTA');?></h4>                        
                        <div class="nicdark_space20"></div>
                        <p class="white"><?php echo LANG('TEXT_BOX_DELTA');?> <a href="<?php echo lang('DELTA_LINK');?>" target='_new'> <?php echo LANG('TITLE_LINK');?></a></p>
                    </div>
                </div>
            </div>
          </div>

          <div class="grid grid_4">    
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
    <!--end nicdark_container-->
     
</section>


<!--start section-->
<section class="nicdark_section">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space50"></div>
        <div class="grid <?php echo (($this->acl->getType() == "sys") ? 'grid_12':'grid_6'); ?>">
            <h1 class="subtitle greydark">Bienvenue &agrave; la Regio Schule !</h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                Les actions des acteurs de la R&eacute;gio
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_yellow nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>
        </div>
        <?php if ($this->acl->getType() != "sys") { ?>
        <div class="grid grid_6">
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading"><?php echo $this->lang->line('INFO_HOME_TITLE');?></h4>
                <p><?php echo $this->lang->line('INFO_HOME');?></p>
                <hr>
                <p class="mb-0"><?php echo $this->lang->line('INFO_HOME_FOOTER');?></p>
            </div>
        </div> 
        <?php } ?>       
        <div class="grid grid_4 nicdark_relative">
            <div class="nicdark_btn_iconbg nicdark_bg_yellow nicdark_absolute extrabig nicdark_shadow nicdark_radius">
                <div>
                    <i class="icon-leaf-1 nicdark_iconbg left big white"></i> 
                </div>
            </div>
            <div class="nicdark_activity nicdark_marginleft100">
                <h4>CONSOLIDER LE PERISCOLAIRE</h4>                        
                <div class="nicdark_space20"></div>
                <p>Encadrement de la cantine<br/>Ateliers et bricolages périscolaire</p>
                <div class="nicdark_space20"></div>
            </div>
        </div>
        <div class="grid grid_4 nicdark_relative">
            
            <div class="nicdark_btn_iconbg nicdark_bg_green nicdark_absolute extrabig nicdark_shadow nicdark_radius">
                <div>
                    <i class="icon-cab nicdark_iconbg left big white"></i> 
                </div>
            </div>
            
            <div class="nicdark_activity nicdark_marginleft100">
                <h4><a href="Orgchart_controller/orga">ANIMER LA VIE DE L’ECOLE </a></h4>                        
                <div class="nicdark_space20"></div>
                <p>Comité des fêtes, Commission communication, Commission unités associatives, Commission finances</p>
                <div class="nicdark_space20"></div>
            </div>    
        
        </div>        
        <div class="grid grid_4 nicdark_relative">
            <div class="nicdark_btn_iconbg nicdark_bg_orange nicdark_absolute extrabig nicdark_shadow nicdark_radius">
                <div>
                    <i class="icon-stopwatch nicdark_iconbg left big white"></i> 
                </div>
            </div>
            <div class="nicdark_activity nicdark_marginleft100">
                <h4>ENTRETENIR L’ECOLE </h4>                        
                <div class="nicdark_space20"></div>
                <p>Ménages, Travaux d’entretien,Travaux d’extension et d’embellissement,Informatique, travaux administratifs</p>
                <div class="nicdark_space20"></div>
            </div>           
        </div>


        <div class="grid grid_4 nicdark_relative">

            <div class="nicdark_btn_iconbg nicdark_bg_blue nicdark_absolute extrabig nicdark_shadow nicdark_radius">
                <div>
                    <i class="icon-headphones-1 nicdark_iconbg left big white"></i> 
                </div>
            </div>
            
            <div class="nicdark_activity nicdark_marginleft100">
                <h4>PARTICIPER A LA VIE ASSOCIATIVE </h4>                        
                <div class="nicdark_space20"></div>
                <p>Conseil d’administration et bureau, associatif, Portes ouvertes, Accueil des nouvelles familles</p>
                <div class="nicdark_space20"></div>
            </div>
        
        </div>

        <div class="grid grid_4 nicdark_relative">
            
            <div class="nicdark_btn_iconbg nicdark_bg_violet nicdark_absolute extrabig nicdark_shadow nicdark_radius">
                <div>
                    <i class="icon-map nicdark_iconbg left big white"></i> 
                </div>
            </div>
            
            <div class="nicdark_activity nicdark_marginleft100">
                <h4>EXCURSIONS</h4>                        
                <div class="nicdark_space20"></div>
                <p>Lorem ipsum dolor sit amet, consec adipiscing elit. Pellentesque tincidunt rutrum sapien, sed ultricies diam.</p>
                <div class="nicdark_space20"></div>
            </div>   
        
        </div>

        <div class="grid grid_4 nicdark_relative">
            
            <div class="nicdark_btn_iconbg nicdark_bg_red nicdark_absolute extrabig nicdark_shadow nicdark_radius">
                <div>
                    <i class="icon-globe-2 nicdark_iconbg left big white"></i> 
                </div>
            </div>
            
            <div class="nicdark_activity nicdark_marginleft100">
                <h4>En faire plus ...</h4>                        
                <div class="nicdark_space20"></div>
                <p>...</p>
                <div class="nicdark_space20"></div>
            </div>    
        
        </div>

        <div class="nicdark_space50"></div>

    </div>
    <!--end nicdark_container-->
            
</section>
<!--end section-->

<!--start section-->
<div id="nicdark_parallax_2_btns" class="nicdark_section nicdark_imgparallax nicdark_parallax_img2">

    <div class="nicdark_filter greydark">

        <!--start nicdark_container-->
        <div class="nicdark_container nicdark_clearfix">

            <div class="nicdark_space40"></div>
            <div class="nicdark_space50"></div>



            <div class="nicdark_space40"></div>
            <div class="nicdark_space50"></div>

        </div>
        <!--end nicdark_container-->

    </div>
            
</div>
<!--end section-->






