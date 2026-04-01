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

        <!--title--> 
        <?php
	        echo form_open(base_url($this->render_object->_getCi('_controller_name').'/valids'), array('class' => '', 'id' => 'edit') , array('form_mod'=>'valid','id'=>'') );
            ?>
            <?php 
                //todo : création d'un objet Session + participant
                foreach($units->sessions AS $id_travaux=>$works){  ?>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <h4 class=""><?php echo $this->render_object->RenderElement('titre', $units->works[$id_travaux]->titre , null, 'Admwork_model').' '.$this->lang->line('DU').' '.$this->render_object->RenderElement('date_travaux',  $units->works[$id_travaux]->date_travaux, null, 'Admwork_model'); ?></h4>
                        <i class="icon-info-outline"></i> <?php echo $this->lang->line('TITRE_TYPE_SESSION').' '.$this->render_object->RenderElement('type_session',  $units->works[$id_travaux]->type_session, null, 'Admwork_model').' '.$this->lang->line('PAR').' '.$this->render_object->RenderElement('referent_travaux',  $units->works[$id_travaux]->referent_travaux, null, 'Admwork_model'); ?>    
                    </div>
                    <div class="form-group col-md-2">
                        <?php echo $this->render_object->label('heure_debut_prevue'); ?>
                    </div>
                    <div class="form-group col-md-2">
                        <?php  echo $this->render_object->label('heure_fin_prevue'); ?>
                    </div>
                    <div class="form-group col-md-2">
                        <?php  echo $this->render_object->label('nb_units'); ?>
                    </div>
                </div>
                <?php
                foreach($works AS $key => $unit){ ?>
                <div class="form-row row <?php echo ((is_float($key/2)) ? 'bg-light':'');?>">
                    <div class="form-group col-md-6">
                        <label><?php echo $this->lang->line('FAMILY');?> <?php echo $this->render_object->RenderElement('id_famille', $unit->id_famille, null, 'Infos_model');?></label>
                    </div>
                    <div class="form-group col-md-2">
                        <?php 
                        echo '<input type="hidden" name="elements[]" value="'.$unit->id.'">';
                        echo $this->render_object->RenderFormElementTimeWithLink('Infos_model','heure_debut_prevue', $unit->id,  $unit->heure_debut_effective, $unit->heure_debut_prevue, ['heure_debut_prevue','heure_fin_prevue','nb_units'] );
                       ?>
                    </div>
                    <div class="form-group col-md-2">
                    <?php 

                        echo $this->render_object->RenderFormElementTimeWithLink('Infos_model','heure_fin_prevue', $unit->id,  $unit->heure_fin_effective, $unit->heure_fin_prevue, ['heure_debut_prevue','heure_fin_prevue','nb_units'] );

                    ?>
                    </div>
                    <div class="form-group col-md-2">
                    <?php 
                    
                        if ($unit->nb_unites_valides_effectif != 0)
                            echo $this->render_object->RenderFormElement('nb_units', $unit->nb_unites_valides_effectif,  'Admwork_model', false, 'nb_units'.$unit->id ); 
                        else
                            echo $this->render_object->RenderFormElement('nb_units', $unit->nb_units,  'Admwork_model', false, 'nb_units'.$unit->id ); 
                    ?>
                    </div> 
                </div>                               
                <?php
                }
            }
            ?>

        <div class="modal-footer">
                <button type="submit" class="btn btn-success"><?php echo $this->lang->line('VALID_UNIT');?></button>
            </div>  
            <?php
            	echo form_close();
            ?>
        <div class="nicdark_space20"></div>
    <!--end nicdark_container-->
    <?php                 //echo debug($units); ?>
    </div>
</section>    

