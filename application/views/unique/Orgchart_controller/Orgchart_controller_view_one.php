<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$colors = $this->render_object->GetColors($group->color);

?>

<!--start section-->
<section class="nicdark_section">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <?php echo $msg;?>

        <div class="nicdark_space50"></div>
        <div class="grid grid_12">
            <h1 class="subtitle greydark"><?php echo $group->title;?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey"><?php echo $this->render_object->RenderElement('mission', $group->mission, null, 'Orgchart_model'); ?></h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="<?php echo $colors->color;?> nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>
        </div>
       
        <div class="grid grid_8">

        
            <h5 class="nicdark_toogle_header grey nicdark_textevidence nicdark_bg_grey big nicdark_radius nicdark_shadow">
                <?php echo Lang('COM_TTILE_ACTIONS');?>
                <i class="icon-info-outline nicdark_iconbg right medium grey"></i>
            </h5>
            <div class="nicdark_toogle_content nicdark_bg_grey nicdark_radius_bottom nicdark_shadow">
                <div class="nicdark_space20"></div>
                <p><?php echo $this->render_object->RenderElement('actions', $group->actions, null, 'Orgchart_model'); ?></p>
            </div>
            <div class="nicdark_space20"></div>
           
            <!--start nicdark_toogle-->
            <div class="nicdark_toogle">
                <h5 class="nicdark_toogle_header grey nicdark_textevidence nicdark_bg_grey big nicdark_radius nicdark_shadow">
                <?php echo Lang('COM_TTILE_ROLE');?>
                    <i class="icon-info-outline nicdark_iconbg right medium grey"></i>
                </h5>
                <div class="nicdark_toogle_content nicdark_bg_grey nicdark_radius_bottom nicdark_shadow">
                <p><?php echo $this->render_object->RenderElement('role', $group->role, null, 'Orgchart_model'); ?></p>
                </div>
            </div>
            <!--end toogle-->
            <div class="nicdark_space20"></div>
            <!--start nicdark_toogle-->
            <div class="nicdark_toogle">
                <h5 class="nicdark_toogle_header grey nicdark_textevidence nicdark_bg_grey big nicdark_radius nicdark_shadow">
                    <?php echo Lang('COM_TTILE_NEEDS');?> 
                    <i class="icon-info-outline nicdark_iconbg right medium grey"></i>
                </h5>
                <div class="nicdark_toogle_content nicdark_bg_grey nicdark_radius_bottom nicdark_shadow">
                <p><?php echo $this->render_object->RenderElement('needs', $group->needs, null, 'Orgchart_model'); ?></p>
                </div>
            </div>
            <!--end toogle-->

            <div class="nicdark_space20"></div>
        </div>
        <div class="grid grid_4">
            <div class="nicdark_archive1 nicdark_bg_grey nicdark_radius nicdark_shadow">
                <div class="nicdark_textevidence <?php echo $colors->color;?> nicdark_radius_top">
                    <h4 class="white nicdark_margin20">L'équipe</h4>
                    <i class="icon-user-1 nicdark_iconbg right medium <?php echo $colors->icon;?>"></i>
                </div>
                <ul class="nicdark_list border">
                    <?php if (isset($group->RT->name)){ ?>
                    <li class="nicdark_border_grey">
                        <div class="nicdark_margin20 nicdark_relative">
                        <?php 
                        if (isset($group->RT->thumbnail)){
                            echo $this->render_object->RenderElement('picture',$group->RT->thumbnail,null, 'GroupesMembers_model', 'nicdark_absolute nicdark_radius w60');
                        } else {
                            echo '<img alt="" class="nicdark_absolute nicdark_radius" style="width:60px;" src="<?php echo base_url();?>assets/img/team/videm.jpg">';
                        }
                        ?>
                        <div class="nicdark_activity nicdark_marginleft80">
                            <h5 class="grey"><?php echo $this->render_object->RenderElement('classif',$group->RT->classif, null, 'Trombi_model'); ?></h5>                        
                            <div class="nicdark_space10"></div>
                            <p><?php echo $this->render_object->RenderElement('name',$group->RT->name, null, 'GroupesMembers_model'); ?> <?php echo $this->render_object->RenderElement('surname',$group->RT->surname, null, 'GroupesMembers_model'); ?></p>
                        </div></div>
                    </li>
                    <?php } ?>
                    <?php if(($group->acteurs) && count($group->acteurs)){ 
                        foreach($group->acteurs AS $acteur){ 
                            ?>
                            <li class="nicdark_border_grey">
                                <div class="nicdark_margin20 nicdark_relative">
                                <?php 
                                echo $this->render_object->RenderElement('picture',$acteur->details->thumbnail,null, 'GroupesMembers_model', 'nicdark_absolute nicdark_radius w60');
                                ?>
                                <div class="nicdark_activity nicdark_marginleft80">
                                    <h5 class="grey"><?php echo $this->render_object->RenderElement('classif',$acteur->classif, null, 'Trombi_model'); ?></h5>                        
                                    <div class="nicdark_space10"></div>
                                    <p><?php echo $this->render_object->RenderElement('name',$acteur->details->name, null, 'GroupesMembers_model'); ?> <?php echo $this->render_object->RenderElement('surname',$acteur->details->surname, null, 'GroupesMembers_model'); ?></p>
                                </div></div>
                            </li>
                    <?php }
                        } ?>
                    <?php if (($group->search)){ ?>
                    <li class="nicdark_border_grey">
                        <div class="nicdark_margin20 nicdark_relative">
                        <img alt="" class="nicdark_absolute nicdark_radius" style="width:60px;" src="<?php echo base_url();?>assets/img/team/videf.jpg">
                        <div class="nicdark_activity nicdark_marginleft80">
                            <h5 class="grey"><?php echo LANG('NEED_YOU');?></h5>                        
                            <div class="nicdark_space10"></div>
                            <p><?php echo $this->render_object->RenderElement('search',$group->search,'Grprelated_model');?></p>
                        </div></div>

                    </li>
                    <li class="nicdark_margin20">
                        <a href="#" class="CondidateModal nicdark_press nicdark_btn nicdark_bg_green white nicdark_radius nicdark_shadow medium center"><?php echo ((($candidature)) ?  Lang('EDIT_CANDIDATE'): Lang('CANDIDATE'));?></a>
                        

                        <?php if (($candidature)){ ?>
                            <div class="nicdark_space10"></div>
                            <?php
                                echo form_open( base_url('Orgchart_controller/view_one/'.$group->id.'/cancel') , array('class' => '', 'id' => ''), array('form_mod'=>'add','id'=>((($candidature)) ? $candidature->id:'') ));
                            ?>
                                <button type="submit" class="btn btn-danger nicdark_bg_orangedark white nicdark_radius nicdark_shadow center"><?php echo Lang('CANCEL_CANDIDATE');?></button>
                            <?php
                                echo form_close();
                            ?>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="nicdark_space20"></div>
            <!--start nicdark_toogle-->
            <div class="nicdark_toogle">
                <h5 class="nicdark_toogle_header grey nicdark_textevidence nicdark_bg_grey big nicdark_radius nicdark_shadow">
                    Liens avec les commissions
                    <i class="icon-info-outline nicdark_iconbg right medium grey"></i>
                </h5>
                <div class="nicdark_toogle_content nicdark_bg_grey nicdark_radius_bottom nicdark_shadow">
                    <p><?php echo $this->render_object->RenderElement('related',$group->related, 'Grprelated_model');?> </p>
                </div>
            </div>
            <!--end toogle-->
            <div class="nicdark_space20"></div>
        </div>
    </div>
    <!--end nicdark_container-->
            
</section>
<!--end section-->

<!-- partie candidature -->
<div class="modal fade" id="CondidateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?php echo Lang('CANDIDATE_COM');?> <?php echo $group->title;?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="modal-body">
            <?php
            echo form_open( base_url('Orgchart_controller/view_one/'.$group->id) , array('class' => '', 'id' => ''), array('form_mod'=>((($candidature)) ? 'edit':'add'),'id_fam'=>$id_fam,'id_grp'=>$group->id  ,'id'=>((($candidature)) ? $candidature->id:'')) );
            //champ obligatoire
            foreach($required_field AS $name){
                echo form_error($name, 	'<div class="alert alert-danger">', '</div>');
            }
            ?>
            <div class="form-row">
				<div class="form-group col-md-6">
					<?php 
						echo $this->render_object->label('name');
						echo $this->render_object->RenderFormElement('name',  ((isset($candidature->name)) ? $candidature->name:'') , 'Candidatures_model');
					?>
				</div>
				<div class="form-group col-md-6">
					<?php 
						echo $this->render_object->label('surname');
						echo $this->render_object->RenderFormElement('surname', ((isset($candidature->surname)) ? $candidature->surname:''), 'Candidatures_model');
					?>
				</div>
												
			</div>
			<div class="form-row">	
				<div class="form-group col-md-6">
					<?php 
						echo $this->render_object->label('phone');
						echo $this->render_object->RenderFormElement('phone', ((isset($candidature->phone)) ? $candidature->phone:''), 'Candidatures_model'); 
					?>
				</div>	
				<div class="form-group col-md-6">														
					<?php 
						echo $this->render_object->label('email');
						echo $this->render_object->RenderFormElement('email',((isset($candidature->email)) ? $candidature->email:''), 'Candidatures_model');
					?>
				</div>			
            </div>
            <div class="form-row">		
				<div class="form-group col-md-12">
					<?php 
						echo $this->render_object->label('memo');
						echo $this->render_object->RenderFormElement('memo', ((isset($candidature->memo)) ? $candidature->memo:''), 'Candidatures_model');
					?>
				</div>
			</div>	
            <div class="modal-footer">
                <button type="submit" class="btn btn-success nicdark_bg_greendark  white nicdark_radius nicdark_shadow"><?php echo Lang('REGISTER_CHANGE');?></button>
            </div>
            <?php
            echo $this->render_object->RenderFormElement('created'); 
            echo $this->render_object->RenderFormElement('updated'); 
            echo form_close();
            ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i>Close</i></button>
        </div>
        </div>
    </div>
</div>
