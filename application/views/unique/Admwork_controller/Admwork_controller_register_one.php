<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_6">
            <h1 class="subtitle greydark"><?php echo $this->render_object->RenderElement('titre',$work->titre);?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <i class="icon-calendar"></i> <?php echo $this->render_object->RenderElement('date_travaux', $work->date_travaux);?>
                <i class="icon-info-outline"></i>  <?php echo $this->lang->line('TITRE_TYPE_SESSION');?> <?php echo $this->render_object->RenderElement('type_session', $work->type_session);?>
                <br/><div class="nicdark_space20"></div>
                <?php if ($work->type_session == 1){ ?>
                <i class="icon-clock-1"></i><?php echo $this->render_object->RenderElement('heure_deb_trav',$work->heure_deb_trav);?> à <?php echo $this->render_object->RenderElement('heure_fin_trav',$work->heure_fin_trav);?>
                <?php } ?>
                <i class="icon-pin-outline"></i> <?php echo $this->render_object->RenderElement('ecole', $work->ecole ) ;?>
            </h3>
           
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="<?php echo $design->color;?> nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>

            <?php echo $this->render_object->RenderElement('description',$work->description);?>
        
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="<?php echo $design->color;?> nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>

            <h3 class="blue">
                <?php echo $this->lang->line('INFO_TYPE_SESSION');?>
            </h3>
            <div class="nicdark_space10"></div>
            <p><?php echo $this->lang->line('INFO_NB_UNIT');?> <b><?php echo $this->render_object->RenderElement('nb_units',$work->nb_units);?> <?php echo $this->lang->line('INFO_UNIT');?></b></p>
            <p><?php echo $this->lang->line('INFO_TYPE_SESSION'.$work->type_session);?></p>
            <?php if (isset($work->pilot)){ ?>
            <?php echo $this->lang->line('INFO_GENE_SESSION');?>
            <div class="nicdark_space10"></div>
            <h4 class="blue">
                <?php echo $this->lang->line('INFO_WHO_MANAGE');?> : <?php echo $work->pilot->title;?> 
            </h4>
            <div class="nicdark_space10"></div>
            <p><?php echo $this->lang->line('PILOT_IS');?><b><?php echo $work->pilot->name.' '.$work->pilot->surname; ?></b></p>
            <p><?php echo $this->lang->line('PILOT_CONTACT');?> <?php echo $work->pilot->email;?> / <?php echo $work->pilot->phone;?></p>
            <?php } ?>
        </div>
        <div class="grid grid_6">
            <?php echo $msg;?>
            <div class="card" >
                <div class="card-header">
                    <?php echo ((isset($work->already_registred->id)) ?  $this->lang->line('MOD_REGISTER_WORK'): $this->lang->line('REGISTER_WORK'));?>
                </div>
                <div class="card-body">
                <?php
                    echo form_open( base_url('Admwork_controller/register_one/'.$work->id) , array('class' => '', 'id' => ''), array('form_mod'=>((isset($work->already_registred->id)) ? 'edit':'add') ,'id'=>((isset($work->already_registred->id)) ? $work->already_registred->id:''),'id_travaux'=>$work->id,'id_famille'=>$id_fam, 'nb_unites_valides'=>$work->nb_units ) );
                    //champ obligatoire
                    foreach($required_field AS $name){
                        echo form_error($name, 	'<div class="alert alert-danger">', '</div>');
                    }
                ?>
                <div class="form-row">
                    <div class="form-group col-md-4">
                    <?php 
                        echo $this->bootstrap_tools->label('type_participant');
                        echo $this->render_object->RenderFormElement('type_participant', ((isset($work->already_registred->id)) ? $work->already_registred->type_participant:'') , 'Infos_model'); 
                    ?>
                    </div>                      
                    <div class="form-group col-md-4">
                    <?php 
                        echo $this->bootstrap_tools->label('heure_debut_prevue');
                        echo $this->render_object->RenderFormElement('heure_debut_prevue', ((isset($work->already_registred->id)) ? $work->already_registred->heure_debut_prevue:$work->heure_deb_trav), 'Infos_model'); 
                    ?>
                    </div>
                    <div class="form-group col-md-4">
                    <?php 
                        echo $this->bootstrap_tools->label('heure_fin_prevue');
                        echo $this->render_object->RenderFormElement('heure_fin_prevue', ((isset($work->already_registred->id)) ? $work->already_registred->heure_fin_prevue:$work->heure_fin_trav) , 'Infos_model'); 
                    ?>
                    </div>                    
                </div>  
                <div class="form-row">                 
                    <div class="form-group col-md-12">
                    <?php 
                        echo $this->bootstrap_tools->label('remarque');
                        echo $this->render_object->RenderFormElement('remarque', ((isset($work->already_registred->id)) ? $work->already_registred->remarque:'') , 'Infos_model'); 
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
            </div>
            <?php if ($work->already_registred){ ?>
                <div class="nicdark_space10"></div>
                <?php
                    echo form_open( base_url('Admwork_controller/register_one/'.$work->id.'/cancel') , array('class' => '', 'id' => ''), array('form_mod'=>((isset($work->already_registred->id)) ? 'edit':'add') ,'id'=>((isset($work->already_registred->id)) ? $work->already_registred->id:''),'id_travaux'=>$work->id,'id_famille'=>$id_fam, 'nb_unites_valides'=>$work->nb_units ) );
                ?>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger nicdark_bg_orangedark white nicdark_radius nicdark_shadow"><?php echo Lang('REGISTER_CANCEL');?></button>
                </div>
                <?php
                    echo form_close();
                ?>
            <?php } ?>
        </div>
    </div>
<!--end nicdark_container-->
</section>
<!--end section-->
