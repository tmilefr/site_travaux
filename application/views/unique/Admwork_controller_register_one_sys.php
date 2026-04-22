<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
Vue inscription aux travaux, mode utilisateur SYS
*/

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
            <?php
                echo form_open(base_url('Units_controller/valids'), array('class' => '', 'id' => 'edit') , array('form_mod'=>'','id'=>'') );
            ?>
            <h1 class="subtitle greydark"><?php echo  $this->lang->line('REGISTRED');?></h1>
            <div class="nicdark_space20"></div>
            <table class="nicdark_table extrabig <?php echo $design->color;?> nicdark_radius ">
                <thead class="<?php echo $design->bordercolor;?>">
                    <tr>
                        <th><h4 class="white"><?php echo $this->lang->line('type_participant');?></h4></th>
                        <th><h4 class="white"><?php echo $this->lang->line('nom');?></h4></th>    
                        <th><h4 class="white"><?php echo $this->lang->line('nb_unites_valides');?></h4></th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody class="nicdark_bg_grey nicdark_border_grey">            
                <?php 
                //ATTENTION au model à utiliser en fonction du champ (voir les json)
                if($work->registred){
                foreach($work->registred AS $unit){ //nicdark_bg_blue nicdark_bg_green nicdark_bg_yellow nicdark_bg_orange nicdark_bg_red 
                ?>
                <tr>
                    <td><p>
                        <input type="hidden" name="elements[]" value="<?php echo $unit->id;?>">
                        <?php echo $this->render_object->RenderElement('type_participant', $unit->type_participant, null, 'Infos_model');?>
                    </p></td>
                    <td><p><?php echo $this->render_object->RenderElement('nom', ((isset($unit->family)) ? $unit->family->nom : ''), null, 'Familys_model');?></p>
                    <p><?php 
                        if ($work->type_session == 1){
                            echo 'de '.$this->render_object->RenderElement('heure_debut_prevue', $unit->heure_debut_prevue, null, 'Infos_model').' à '.$this->render_object->RenderElement('heure_fin_prevue', $unit->heure_fin_prevue, null, 'Infos_model');
                        }?>
                    </p></td>   
                    <td>
                        <p><?php echo $this->render_object->RenderElement('nb_unites_valides', $unit->nb_unites_valides, null, 'Infos_model');?></p>
                        <?php if (!empty($unit->valide_par_ref) && !empty($unit->valide_ref_at)) { ?>
                            <p class="small text-success">
                                <i class="icon-ok"></i>
                                <?php echo $this->lang->line('VALIDATED_BY_REF'); ?>
                                <?php echo date('d/m/Y H:i', strtotime($unit->valide_ref_at)); ?>
                            </p>
                        <?php } ?>
                        <?php if (!empty($unit->commentaire_ref)) { ?>
                            <p class="small text-muted" style="font-style:italic;">
                                « <?php echo htmlspecialchars($unit->commentaire_ref, ENT_QUOTES); ?> »
                            </p>
                        <?php } ?>
                    </td>
                    <td><a class="btn btn-sm btn-danger confirmModalLink " href="<?php echo base_url('Admwork_controller/managed_one/').$work->id.'/'.$unit->id;?>"><span class="oi oi-circle-x"></span></a></td>
                    
                </tr>
                <?php }
                    echo '<tr><td colspan="6" class="right">
                    <a class="nicdark_btn nicdark_bg_red white small nicdark_radius" href="'.base_url('Admwork_controller/MakePdf/'.$work->id).'"><i class="icon-download-outline"></i>&nbsp;&nbsp;&nbsp;'.$this->lang->line('DO_PDF').'</a>
                    <button type="submit" class="btn btn-success">'.$this->lang->line('EDIT_VALID_UNIT').'</button>
                    </td></tr>';

                } else {
                    echo '<tr><td colspan="6">'.$this->lang->line('REGISTRED_NONE').'</td></tr>';
                } ?>
                </tbody>
            </table>
            
            <?php
                echo form_close();
            ?>
            <div class="nicdark_space50"></div>
            <?php echo $msg;?>
            <div class="card" >
                <div class="card-header">
                    <?php echo $this->lang->line('MANAGED_REGISTER_WORK');?>
                </div>
                <div class="card-body">
                <?php
                    echo form_open( base_url('Admwork_controller/managed_one/'.$work->id) , array('class' => '', 'id' => ''), array('form_mod'=>'add' ,'id_travaux'=>$work->id, 'nb_unites_valides'=>$work->nb_units ) );
                    //champ obligatoire
                    foreach($required_field AS $name){
                        echo form_error($name, 	'<div class="alert alert-danger">', '</div>');
                    }
                ?>
                <div class="form-row">
                    <div class="form-group col-md-4">
                    <?php 
                        echo $this->render_object->label('id_famille');
                        echo $this->render_object->RenderFormElement('id_famille',null, 'Infos_model'); 
                    ?>
			        </div>                    
                    <div class="form-group col-md-4">
                    <?php 
                        echo $this->bootstrap_tools->label('type_participant');
                        echo $this->render_object->RenderFormElement('type_participant', ((isset($work->already_registred->id)) ? $work->already_registred->type_participant:'') , 'Infos_model'); 
                    ?>
                    </div>   
                </div>
                <div class="form-row">                   
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
        </div>
    </div>
<!--end nicdark_container-->
</section>
<!--end section-->
