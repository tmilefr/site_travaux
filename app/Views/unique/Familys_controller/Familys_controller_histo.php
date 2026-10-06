<?php
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_5">
            <h1 class="subtitle greydark"><?php echo tr($render_object->_getCi('_controller_name').'_'.$render_object->_getCi('_action').$acl->getType());?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <?php echo tr($render_object->_getCi('_controller_name').'_'.$render_object->_getCi('_action').$acl->getType().'_subtitle');?>
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>
            <?php echo tr('INFO_UNITS_'.$acl->getType());?>
        </div>
        <div class="grid grid_5">
            <ul class="nav nav-pills">
                <?php 
                foreach($civil_year AS $key=>$value){
                    echo '<li class="nav-item" ><a  class="nav-link '.(($filter_ec == $key) ? 'active':'').'" href="'.base_url($render_object->_getCi('_controller_name').'/'.$render_object->_getCi('_action')).'/filter/civil_year/filter_value/'.$key.'">'.$value.'</a></li>';
                }
                ?>
            </ul>            
            
        </div>


        <div class="grid grid_12">
            <?php 
            if ($acl->getType() == "sys") { 
                echo open_form(base_url($render_object->_getCi('_controller_name').'/histo'), array('class' => 'form-inline', 'id' => 'search') , array('form_mod'=>'search') );
            }
            ?>                
            <div class="nicdark_archive1 nicdark_bg_grey nicdark_radius nicdark_shadow">
                <div class="nicdark_textevidence nicdark_bg_violet nicdark_radius_top">
                    <h4 class="white nicdark_margin20"><?php echo tr('UNIT_TITLE');?></h4>
                    <?php 
                    if ($acl->getType() == "sys") { 
                        echo $familys->RenderFormElement();
                        ?>
                        <button type="submit" class="btn btn-primary"><?php echo  tr('See');?></button>
                        <?php
                    }
                    ?>                   
                    <i class="icon-clipboard nicdark_iconbg right medium violet"></i>

                </div>
                <ul class="nicdark_list border">  
                    <li class="nicdark_border_grey">
                        <div class="nicdark_margin20">
                            <div class="nicdark_activity">
                                <p><i class="icon-right-open-outline"></i>&nbsp;<?php echo tr('UNIT_TODO');?> <b><?php echo config('Travaux')->unitTodo; ?></b></p>
                            </div>
                        </div>
                    </li>
                    <li class="nicdark_border_grey">
                        <div class="nicdark_margin20">
                            <div class="nicdark_activity">
                                <p><i class="icon-right-open-outline"></i>&nbsp;<?php echo tr('UNIT_RAF');?> <b><?php echo $units['raf']; ?></b> <i class="icon-right-open-outline"></i>&nbsp;<?php echo tr('UNIT_TOVALID');?> <b><?php echo $units['tovalid']; ?></b></p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <?php  if ($acl->getType() == "sys") { 
                echo form_close();
            } ?>
         </div>


        <div class="grid grid_12">
            <!--title-->
            <h3 class="subtitle greydark"><?php echo tr('COMING_'.$acl->getType());?></h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left small"><span class="nicdark_bg_blue nicdark_radius"></span></div>
            <div class="nicdark_space20"></div>
            <!--title--> 
            <div class="nicdark_textevidence nicdark_bg_grey nicdark_shadow nicdark_radius left overflow_scroll">   
                <table class="nicdark_table extrabig nicdark_bg_blue nicdark_radius ">
                    <thead class="nicdark_border_blue">
                        <tr>
                            <td class="nicdark_width_percentage10" ><h4 class="white"><?php echo tr('date_travaux');?></h4></td>
                            <td class="nicdark_width_percentage30"><h4 class="white"><?php echo tr('referent_travaux');?></h4></td>
                            <td class="nicdark_width_percentage25"><h4 class="white"><?php echo tr('titre');?></h4></td>                       
                            <td class="nicdark_width_percentage25"><h4 class="white"><?php echo tr('type_session');?></h4></td>
                            <td class="nicdark_width_percentage10"><h4 class="white"><?php echo tr('nb_units');?></h4></td>
                        </tr>
                    </thead>
                    <tbody class="nicdark_bg_grey nicdark_border_grey">            
                    <?php 
                    //ATTENTION au model à utiliser en fonction du champ (voir les json)
                    if ($units['coming'])
                    foreach($units['coming'] AS $unit){ //nicdark_bg_blue nicdark_bg_green nicdark_bg_yellow nicdark_bg_orange nicdark_bg_red 
                    ?>
                    <tr class='clickable-row' data-href='<?php echo base_url('Admwork_controller/register_one/'.$unit->id_travaux);?>'>
                        <td><p><?php echo $render_object->RenderElement('date_travaux', $unit->date_travaux, null, 'Admwork_model');?></p></td>
                        <td><p><?php echo $render_object->RenderElement('referent_travaux', $unit->referent_travaux, null, 'Admwork_model');?></p></td>
                        <td><p><?php echo $render_object->RenderElement('titre', $unit->titre, null, 'Admwork_model');?></p></td>
                        <td><p><?php 
                        echo $render_object->RenderElement('type_session', $unit->type_session, null, 'Admwork_model');
                        if ($unit->type_session == 1){
                            echo ' [ '.$render_object->RenderElement('heure_debut_prevue', $unit->heure_debut_prevue, null, 'Infos_model').' / '.$render_object->RenderElement('heure_fin_prevue', $unit->heure_fin_prevue, null, 'Infos_model').' ]';
                        }
                        ?></p></td>
                        <td><p><?php echo $render_object->RenderElement('nb_units', $unit->nb_units, null, 'Admwork_model');?></p></td>

                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="nicdark_space20"></div>
            <!--title-->
            <h3 class="subtitle greydark">
                <?php echo tr('VALID_'.$acl->getType());?> 
                
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left small"><span class="nicdark_bg_green nicdark_radius"></span></div>
            <div class="nicdark_space20"></div>
            <!--title--> 
            <div class="nicdark_textevidence nicdark_bg_grey nicdark_shadow nicdark_radius left overflow_scroll">   
                <?php
                echo open_form(base_url('Units_controller/valids'), array('class' => '', 'id' => 'edit') , array('form_mod'=>'edit','id'=>'') );
                ?>
                <table class="nicdark_table extrabig nicdark_bg_green nicdark_radius ">
                    <thead class="nicdark_border_blue">
                        <tr>
                            <th scope="col"><input type="checkbox" id="checkall"></th>
                            <th class="nicdark_width_percentage10" ><h4 class="white"><?php echo tr('date_travaux');?></h4></th>
                            <th class="nicdark_width_percentage30"><h4 class="white"><?php echo tr('referent_travaux');?></h4></th>
                            <th class="nicdark_width_percentage25"><h4 class="white"><?php echo tr('titre');?></h4></th>                       
                            <th class="nicdark_width_percentage25"><h4 class="white"><?php echo tr('type_session');?></h4></th>
                            <th class="nicdark_width_percentage10"><h4 class="white"><?php echo tr('nb_units');?></h4></th>
                        </tr>
                    </thead>
                    <tbody class="nicdark_bg_grey nicdark_border_grey">            
                    <?php 
                    //ATTENTION au model à utiliser en fonction du champ (voir les json)
                    if ($units['valid'])
                    foreach($units['valid'] AS $unit){ //nicdark_bg_blue nicdark_bg_green nicdark_bg_yellow nicdark_bg_orange nicdark_bg_red ?>
                    <tr>
                        <th scope="row"><input type="checkbox" class="checkbox" value="<?php echo $unit->id;?>" name="elements[]"></th>    
                        <td><p><?php echo $render_object->RenderElement('date_travaux', $unit->date_travaux, null, 'Admwork_model');?></p></td>
                        <td><p><?php echo $render_object->RenderElement('referent_travaux', $unit->referent_travaux, null, 'Admwork_model');?></p></td>
                        <td><p><?php echo $render_object->RenderElement('titre', $unit->titre, null, 'Admwork_model');?></p></td>
                        <td><p><?php 
                        echo $render_object->RenderElement('type_session', $unit->type_session, null, 'Admwork_model');
                        if ($unit->type_session == 1){
                            echo ' [ '.$render_object->RenderElement('heure_debut_prevue', $unit->heure_debut_effective, null, 'Infos_model').' / '.$render_object->RenderElement('heure_fin_prevue', $unit->heure_fin_effective, null, 'Infos_model').' ]';
                        }
                        ?></p></td>
                        <td><p><?php echo $render_object->RenderElement('nb_units', $unit->nb_unites_valides_effectif, null, 'Admwork_model');?></p></td>

                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><?php echo tr('VALIDS_EDITION');?></button>
                </div>
                <?php
            	    echo form_close();
                ?>
            </div>
            <div class="nicdark_space20"></div>
            <!--title-->
            <h3 class="subtitle greydark"><?php echo tr('ADDED_'.$acl->getType());?></h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left small"><span class="nicdark_bg_greendark nicdark_radius"></span></div>
            <div class="nicdark_space20"></div>
            <!--title--> 
            <div class="nicdark_textevidence nicdark_bg_grey nicdark_shadow nicdark_radius left overflow_scroll">   
                <table class="nicdark_table extrabig nicdark_bg_greendark nicdark_radius ">
                    <thead class="nicdark_border_greendark">
                        <tr>
                            <td class="nicdark_width_percentage10"><h4 class="white"><?php echo tr('unites_date');?></h4></td>
                            <td class="nicdark_width_percentage30"><h4 class="white"><?php echo tr('unites_desc');?></h4></td>
                            <td class="nicdark_width_percentage25"><h4 class="white"><?php echo tr('unites_comm');?></h4></td>
                            <td class="nicdark_width_percentage25"><h4 class="white"><?php echo tr('type_session');?></h4></td>
                            <td class="nicdark_width_percentage10"><h4 class="white"><?php echo tr('unites_valides');?></h4></td>
                        </tr>
                    </thead>
                    <tbody class="nicdark_bg_grey nicdark_border_grey">            
                    <?php 
                     if ($units['addition'])
                    foreach($units['addition'] AS $unit){ //nicdark_bg_blue nicdark_bg_green nicdark_bg_yellow nicdark_bg_orange nicdark_bg_red ?>
                    <tr>
                        <td><p><?php echo $render_object->RenderElement('unites_date', $unit->unites_date, null, 'Units_model');?></p></td>
                        <td><p><?php echo $render_object->RenderElement('unites_desc', $unit->unites_desc, null, 'Units_model');?></p></td>
                        <td><p><?php echo $render_object->RenderElement('unites_comm', $unit->unites_comm, null, 'Units_model');?></p></td>
                        <td>   
                            <p>
                            <?php echo $render_object->RenderElement('type_session', $unit->type_session, null, 'Units_model');
                            if ($unit->type_session == 1){
                            echo ' [ '.$render_object->RenderElement('unites_heure_debut', $unit->unites_heure_debut, null, 'Units_model').' / '.$render_object->RenderElement('unites_heure_fin', $unit->unites_heure_fin, null, 'Units_model').' ]';
                            }         
                            ?>                   
                            </p>
                        </td>
                        <td><p><?php echo $render_object->RenderElement('unites_valides', $unit->unites_valides, null, 'Units_model');?></p></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <!--end nicdark_container-->
        </div>
    </div>
</section>    

