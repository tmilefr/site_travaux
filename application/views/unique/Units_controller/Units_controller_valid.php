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
        <div class="overflow_scroll">   
        <?php
	        echo form_open(base_url($this->render_object->_getCi('_controller_name').'/valids'), array('class' => '', 'id' => 'edit') , array('form_mod'=>'edit','id'=>'') );
            ?>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><?php echo $this->lang->line('VALIDS_EDITION');?></button>
            </div>
            <?php foreach($units->sessions AS $id_travaux=>$works){  ?>                     
            <table class="table table-striped table-sm ">
                <thead>
                    <tr>
                        <th scope="col" class=""><input type="checkbox" id="checkall" class="checkall" data-target="checkbox<?php echo $id_travaux;?>"></th>
                        <th scope="col" class="col-md-1"><?php echo $this->render_object->render_link('date_travaux', 'valid', 'Admwork_model' );?></th>
                        <th scope="col" class="col-md-2"><?php echo $this->render_object->render_link('id_famille', 'valid', 'Infos_model' );?></th>
                        <th scope="col" class="col-md-1"><?php echo $this->render_object->render_link('heure_debut_prevue', 'valid', 'Infos_model' );?></th>
                        <th scope="col" class="col-md-1"><?php echo $this->render_object->render_link('heure_fin_prevue', 'valid', 'Infos_model' );?></th>
                        <th scope="col" class="col-md-3"><?php echo $this->render_object->RenderElement('titre', $units->works[$id_travaux]->titre , null, 'Admwork_model')?></th>
                        <th scope="col" class="col-md-1"><?php echo $this->render_object->render_link('nb_units', 'valid', 'Admwork_model' );?></th>
                        <th scope="col" class="col-md-1"><?php echo $this->render_object->RenderElement('type_session',  $units->works[$id_travaux]->type_session, null, 'Admwork_model');?></th>
                        <th scope="col" class="col-md-2"><?php echo $this->render_object->RenderElement('referent_travaux',  $units->works[$id_travaux]->referent_travaux, null, 'Admwork_model');?></th>
                    </tr>
                </thead>
                <tbody>            
                <?php 
                //ATTENTION au model à utiliser en fonction du champ (voir les json)
                foreach($works AS $unit){ ?>
                <tr>
                    <th scope="row"><input type="checkbox" class="checkbox<?php echo $id_travaux;?>" value="<?php echo $unit->id;?>" name="elements[]"></th>    
                    <td><p><?php echo $this->render_object->RenderElement('date_travaux', $unit->date_travaux, null, 'Admwork_model');?></p></td>
                    <td><p><?php echo $this->render_object->RenderElement('id_famille', $unit->id_famille, null, 'Infos_model');?></p></td>
                    <td><p><?php echo $this->render_object->RenderElement('heure_debut_prevue', $unit->heure_debut_prevue, null, 'Infos_model');?></p></td>
                    <td><p><?php echo $this->render_object->RenderElement('heure_fin_prevue', $unit->heure_fin_prevue, null, 'Infos_model');?></p></td>
                    <td><a href="<?php echo base_url("Admwork_controller/register_one/".$unit->id_travaux);?>"><?php echo $this->render_object->RenderElement('titre', $unit->titre, null, 'Admwork_model');?></a></td>
                    <td><p><?php echo $this->render_object->RenderElement('nb_units', $unit->nb_units, null, 'Admwork_model');?></p></td>
                    <td><p><?php echo $this->render_object->RenderElement('type_session', $unit->type_session, null, 'Admwork_model');?></p></td>
                    <td><p><?php echo $this->render_object->RenderElement('referent_travaux', $unit->referent_travaux, null, 'Admwork_model');?></p></td>
                </tr>
                <?php }  ?>
                </tbody>
            </table>
            <?php } ?>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><?php echo $this->lang->line('VALIDS_EDITION');?></button>
            </div>  
            <?php
            	echo form_close();
            ?>
        </div>
        <div class="nicdark_space20"></div>
    <!--end nicdark_container-->
    <?php                 //echo debug($units); ?>
    </div>
</section>    

