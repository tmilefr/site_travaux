<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>
        <div class="grid grid_4">
            <h1 class="subtitle greydark"><?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action').$this->acl->getType());?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action').$this->acl->getType().'_subtitle');?>
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>
        </div>

        <div class="grid grid_8">
            <ul class="nav nav-pills">
                <?php 
                foreach($civil_years AS $key=>$value){
                    echo '<li class="nav-item" ><a  class="nav-link '.(($filter_ec == $key) ? 'active':'').'" href="'.base_url($this->render_object->_getCi('_controller_name').'/'.$this->render_object->_getCi('_action')).'/filter/civil_year/filter_value/'.$key.'">'.$value.'</a></li>';
                }
                ?>
                <li class="nav-item"><a class="nav-link <?php echo (($filter_ec == 'resume') ? 'active':'');?>" href="<?php echo base_url($this->render_object->_getCi('_controller_name').'/'.$this->render_object->_getCi('_action')).'/filter/civil_year/filter_value/resume';?>">Synthèse</a></li>
                <li class="nav-item"><a class="nav-link nicdark_bg_red white " href="<?php echo base_url($this->render_object->_getCi('_controller_name').'/stats_export');?>"><?php echo Lang('Export');?></a></li>
            </ul>            
            
        </div>
        <div class="grid grid_12"> 
            <?php 
            switch($filter_ec){
                case 'resume':
                    $totaux  = [];
                    echo '<table class="table table-sm table-striped">';
                    echo '<tr><td>'.Lang('type').'</td>';
                    foreach($civil_years AS $key=>$value){
                        echo '<th>'.$value.'</th>';
                    }
                    echo '</tr>';
                    foreach($ConsolidatedStats AS $type=>$years){
                        echo '<tr><td>'.$this->render_object->RenderElement('type', $type, null, 'Admwork_model').'</td>';
                        foreach($civil_years AS $key=>$value){
                            if (!isset($totaux[$value]))
                                $totaux[$value] = 0;

                            if (isset($years[$value])) {
                                $data = $years[$value];
                                $sum = $data->tovalid + $data->valid;
                                $totaux[$value] += $sum;
                                echo "<td>".number_format($sum ,2,"."," ")."</td>";
                            } else {
                                echo '<td>&nbsp;</td>';
                            }                            
                        }
                        echo '</tr>';

                    }
                    echo '<tr>';
                    echo '<td>&nbsp;</td>';
                    foreach($civil_years AS $key=>$value){
                        echo "<td>".number_format($totaux[$value] ,2,"."," ")."</td>";
                    }
                    echo '</tr>';
                    echo '</table>';
                break;
                default: ?>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th><?php echo LANG('_title_family');?></th>
                                <th><?php echo LANG('_title_ecole');?></th>
                                <th><?php echo LANG('_title_raf');?></th>
                                <th><?php echo LANG('_title_tovalid');?></th>                       
                                <th><?php echo LANG('_title_valid');?></th>
                                <th><?php echo LANG('_title_addition');?></th>
                            </tr>
                        </thead>
                        <tbody>            
                        <?php 
                        if ($units)
                            foreach($units AS $id_fam=>$stats){ 
                            ?>
                            <tr>
                                <td><p><?php echo $stats->family->nom; ?> </p></td>
                                <td><p><?php echo $stats->family->ecole;?></p></td>
                                <td><p><?php echo $stats->raf;?></p></td>
                                <td><p><?php echo $stats->coming;?></p></td>
                                <td><p><?php echo $stats->valid;?></p></td>
                                <td><p><?php echo $stats->addition;?></p></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php
            }
            ?>
        <!--end nicdark_container-->
        </div>
    </div>
</section>    
<?php //echo debug($units); ?>
