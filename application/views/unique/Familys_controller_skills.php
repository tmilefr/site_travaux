<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
            <h1 class="subtitle greydark"><?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action'));?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action').'_subtitle');?>
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>

            <div class="nicdark_masonry_btns right">
                <a data-filter="*" class="nicdark_bg_grey2_hover nicdark_transition nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey">Tous</a>    
                <?php
                foreach($capacitys AS $key=>$capacity){
                    ?>
                    <a data-filter=".<?php echo $capacity->cle;?>" class="nicdark_bg_grey2_hover nicdark_transition nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey"><?php echo $capacity->value;?></a>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="nicdark_masonry_container">
        <?php 
            $colored_ecole = ['M'=>'nicdark_bg_blue','L'=>'nicdark_bg_violet'];
            $colored_skil  = [];
            foreach($familys AS $family){ ?>

                <div class="grid grid_3 nicdark_archive1 center <?php echo $colored_ecole[$family->ecole];?> nicdark_shadow nicdark_radius nicdark_masonry_item <?php echo implode(" ",$family->skill);?>">
                    <div class="nicdark_textevidence nicdark_bg_greydark nicdark_radius_top">
                        <h4 class="white nicdark_margin5"><?php echo $this->lang->line('FAMILY').' '.$family->nom;?></h4>
                    </div>
                    <div class="nicdark_archive1">
                        <div class="nicdark_filter ">
                            <div class="nicdark_space10"></div>
                            <h3 class="white subtitle"><?php echo $this->render_object->RenderElement('ecole', $family->ecole , null, 'Familys_model');?></h3>
                            <div class="nicdark_space10"></div>
                        </div>
                    </div>
                    <div class="nicdark_textevidence nicdark_bg_grey">
                        <div class="nicdark_margin20">
                            <ul class="nicdark_list ">
                                <?php foreach($family->skill AS $skill){ ?>
                                <li class="nicdark_border_grey">
                                    <p><?php echo $capacitys[$skill]->value;?></p>
                                    <div class="nicdark_space10"></div>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <!--end nicdark_container-->
</section>    

