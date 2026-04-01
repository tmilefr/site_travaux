<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>




<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
    <div class="nicdark_space30"></div>

    <div class="grid grid_12">
		<h1 class="subtitle greydark"><?php echo $this->lang->line('work_planned');?></h1>
		<div class="nicdark_space20"></div>
		<h3 class="subtitle grey">
            <?php echo $this->lang->line('work_planned_subtitle');?>
		</h3>
		<div class="nicdark_space20"></div>
		<div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
		<div class="nicdark_space10"></div>
	</div>
    <div class="nicdark_masonry_btns right">
        <a data-filter="*" class="nicdark_bg_grey2_hover nicdark_transition nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey"><?php echo Lang('All');?></a>
        <a data-filter=".dispo" class="nicdark_bg_grey2_hover nicdark_transition nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey"><?php echo Lang('NotFull');?></a> 
        <a data-filter=".archived" class="nicdark_bg_grey2_hover nicdark_transition nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey"><?php echo Lang('Archived');?></a>      
        <?php
        foreach($WorkType AS $key=>$value){
            ?>
            <a data-filter=".<?php echo $key;?>" class="nicdark_bg_grey2_hover nicdark_transition nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey"><?php echo $value;?></a>
            <?php
        } ?>
    </div>
    
    <div class="nicdark_masonry_container">
    <?php //echo debug($works);
        foreach($works AS $work){
            $design = $this->render_object->GetDesign($work->type);
        ?>
        <div class="grid grid_3 nicdark_masonry_item <?php echo $work->type;?> <?php echo (($work->delay > 0) ? 'archived':'');?> <?php echo (($work->register && $work->delay < 0) ? "dispo":"");?>">
            <!--archive1-->
            <div class="nicdark_archive1 hEvent <?php echo $design->color;?> nicdark_radius nicdark_shadow">
                <a href="#" class="nicdark_btn nicdark_bg_greydark white medium nicdark_radius nicdark_absolute_left"><?php echo $this->render_object->RenderElement('date_travaux', $work->date_travaux);?></a>
                <a href="#" class="nicdark_btn <?php echo (($work->register) ? $design->color:'nicdark_bg_greydark');?> white medium nicdark_radius nicdark_absolute_right"><?php echo (($work->participant) ? $work->participant:0)."/".$work->nb_inscrits_max;	?></a>
                <?php echo $this->render_object->RenderImg($design->img);?>
                <div class="nicdark_textevidence nicdark_bg_greydark">
                    <h4 class="white nicdark_margin20"><?php echo $this->render_object->RenderElement('titre',$work->titre);?></h4>
                </div>
                <div class="nicdark_margin20">
                    <h5 class="white"><i class="icon-pin-outline"></i> <?php echo $this->render_object->RenderElement('ecole', $work->ecole ) ;?></h5>
                    <div class="nicdark_space10"></div>
                    <h5 class="white"><i class="icon-info-outline"></i>  <?php echo $this->lang->line('TITRE_TYPE_SESSION');?> <?php echo $this->render_object->RenderElement('type_session', $work->type_session);?></h5>
                
                    <div class="nicdark_space10"></div>
                    <?php if ($work->type_session == 1){ ?>
                    <h5 class="white"><i class="icon-clock-1"></i><?php echo $this->render_object->RenderElement('heure_deb_trav',$work->heure_deb_trav);?> à <?php echo $this->render_object->RenderElement('heure_fin_trav',$work->heure_fin_trav);?></h5>
                    <?php } else {
                        echo '<h5 class="white">&nbsp;</h5>';  
                    } ?>
                    <div class="nicdark_space20"></div>
                        <?php
                        if ($this->acl->getType() == "sys"){ ?>
                            &nbsp;<a href="<?php echo base_url('Admwork_controller/managed_one/'.$work->id);?>" class="nicdark_press nicdark_btn <?php echo $design->btn;?> white nicdark_radius nicdark_shadow medium right"><?php echo $this->lang->line('ADM_WORK');?></a>
                        <?php }
                        if ($work->delay < 0){  
                            if ($work->register) {   ?>
                            <a href="<?php echo base_url('Admwork_controller/register_one/'.$work->id);?>" class="nicdark_press nicdark_btn <?php echo $design->btn;?> white nicdark_radius nicdark_shadow medium right"><?php echo (($work->already_registred) ? $this->lang->line('SEE_YOUR_REGISTRED_WORK'):$this->lang->line('REGISTER_WORK'));?></a>
                        <?php } else {
                            echo '<span class="nicdark_press nicdark_btn '.$design->btn.' white nicdark_radius nicdark_shadow medium right">'.Lang('REGISTER_WORK_CLOSED').'</span>';
                            }
                        } else {
                            echo '<span class="nicdark_press nicdark_btn '.$design->btn.' white nicdark_radius nicdark_shadow medium right">'.Lang('REGISTER_WORK_CLOSED').'</span>';
                        } ?>
                </div>
            </div>
            <!--archive1-->
        </div>
        <?php
        }    
    ?>
    </div>

</div>
<!--end nicdark_container-->
</section>
<!--end section-->