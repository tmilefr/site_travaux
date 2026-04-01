<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
 <div class="nicdark_space50"></div>
<!--start section-->
<section id="nicdark_parallax_title" class="nicdark_section nicdark_imgparallax nicdark_parallaxx_img3">

    <div class="nicdark_filter greydark">
        <!--start nicdark_container-->
        <div class="nicdark_container nicdark_clearfix">
        <div class="grid grid_12">
        <div class="nicdark_space100"></div>
                <div class="nicdark_space100"></div>
            <h1 class="white subtitle"><?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_orga_title');?></h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle white"><?php echo count($commissions);?> <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_orga_subtitle');?></h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_white nicdark_radius"></span></div>
            <div class="nicdark_space40"></div>
        </div>
        <!--end nicdark_container-->

    </div>

     
</section>
<!--end section-->

<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix ">
    <div class="nicdark_space50"></div>
    <p><?php echo lang('PAGE_ORGA_INTRO');?></p>
                <div class="nicdark_space20"></div>
        <?php 
            //echo debug($RTS);
            foreach($commissions AS $commission){ 
                $colors = $this->render_object->GetColors($commission->color);
                //echo debug($RT);
                ?>
                <div class="grid grid_3">
                    <div class="nicdark_archive1 hRT nicdark_bg_grey nicdark_radius nicdark_shadow">
                        <div class="nicdark_textevidence nicdark_bg_greydark center">
                            <h4 class="white nicdark_margin10"><?php echo $commission->short;?></h4>
                        </div>
                        <div class="nicdark_textevidence <?php echo $colors->color;?> center">
                            <h5 class="<?php echo $colors->txt;?> nicdark_margin20"><?php echo (( isset($commission->RT->name) ) ? $commission->RT->name .' '.$commission->RT->surname  :' Personne encore ');?></h5>
                            <i class="icon-doc-text-1 nicdark_iconbg right medium <?php echo $colors->icon;?>"></i>
                        </div>                                
                        <div class="nicdark_textevidence ">
                            <div class="nicdark_margin20">
                                <h3 class=""><?php echo $commission->title;?></h3>
                                <div class="nicdark_space20"></div>
                                <p><?php echo nl2br($commission->intro);?></p>
                                <div class="nicdark_space20"></div>
                                <a href="<?php echo  base_url('Orgchart_controller/view_one/'.$commission->id);?>" class="nicdark_btn"><i class="icon-doc-text-1 "></i> <?php echo Lang('lire la suite');?></a>
                            </div>
                        </div>                
                    </div>
                </div>
            <?php
            }
        ?>
        <div class="nicdark_space50"></div>

    </div>
    <!--end nicdark_container-->
            
</section>
<!--end section-->


<!--start section-->
<section id="nicdark_parallax_counter" class="nicdark_section nicdark_imgparallax nicdark_parallax_img1">
    <div class="nicdark_filter greydark">
        <!--start nicdark_container-->
        <div class="nicdark_space30"></div>
        <div class="nicdark_container nicdark_clearfix">
            <?php 
            foreach($stats AS $stat){ ?>
                <div class="grid grid_3">
                    <div class="nicdark_textevidence center">
                        <a href="#" class="white nicdark_btn <?php echo $stat->color.' '.$stat->backhover;?> nicdark_transition nicdark_shadow extrasize nicdark_radius_circle subtitle nicdark_counter" data-to="<?php echo $stat->nb;?>" data-speed="1000"><?php echo $stat->nb;?></a>
                        <div class="nicdark_space20"></div>
                        <h4 class="white"><?php echo $stat->title;?></h4>
                    </div>
                </div>
            <?php
            }
            ?>

            <div class="nicdark_space40"></div>
            <div class="nicdark_space50"></div>
        </div>
        <!--end nicdark_container-->
    </div>          
</section>
<!--end section-->



<!--end nicdark_container-->