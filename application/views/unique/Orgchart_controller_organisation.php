<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$colors = $this->render_object->GetColors($featured->color);

?>
 <div class="nicdark_space50"></div>
<!--start section-->
<section id="nicdark_parallax_title" class="nicdark_section nicdark_imgparallax nicdark_parallaxx_img7">

    <div class="nicdark_filter greydark">

        <!--start nicdark_container-->
        <div class="nicdark_container nicdark_clearfix">

            <div class="grid grid_12">
                <div class="nicdark_space100"></div>
                <div class="nicdark_space100"></div>
                <h1 class="white subtitle"><?php echo $this->render_object->RenderElement('title', $featured->title, null, 'Orgchart_model'); ?></h1>
                <div class="nicdark_space10"></div>
                <h3 class="subtitle white"><?php echo $this->render_object->RenderElement('intro', $featured->intro, null, 'Orgchart_model'); ?></h3>
                <div class="nicdark_space20"></div>
                <div class="nicdark_divider left big"><span class="nicdark_bg_white nicdark_radius"></span></div>
                <div class="nicdark_space40"></div>
                <div class="nicdark_space50"></div>
            </div>

        </div>
        <!--end nicdark_container-->

    </div>
     
</section>
<!--end section-->


<!--start section-->
<section class="nicdark_section">

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space50"></div>

        <div class="grid grid_6">
            <p><?php echo $this->render_object->RenderElement('actions', $featured->actions, null, 'Orgchart_model'); ?></p>
        </div>

        <div class="grid grid_6">
            <!--TABLE-->
            <div class="nicdark_textevidence nicdark_bg_grey nicdark_shadow nicdark_radius left overflow_scroll">   
                <table class="nicdark_table extrabig nicdark_bg_yellow nicdark_radius ">
                    <thead class="nicdark_border_yellow">
                        <tr>
                            <td class="white"><h4 class="white"><?php echo LANG('CA_DOCUMENTS');?></h4></td>
                            <td class="nicdark_width_percentage1"></td>
                        </tr>
                    </thead>
                    <tbody class="nicdark_bg_grey nicdark_border_grey">
                        <?php 
                        if (is_array($pvca) AND count($pvca)){
                            foreach($pvca AS $file){ ?>                            
                                <tr>
                                    <td><p><?php echo $this->render_object->RenderElement('memo',$file->name, null, 'Files_model'); ?></p></td>
                                    <td><p><a class="grey" target='_new' href="<?php echo $this->render_object->RenderElement('path',$file->path, null, 'Files_model'); ?>"><?php echo LANG('CA_DOWNLOAD');?></a></p></td>
                                </tr>
                            <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div> 
            <!--TABLE-->

        </div>

    </div>
    <!--end nicdark_container-->
            
</section>
<!--end section-->


<!--start section-->
<section class="nicdark_section">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">

    <div class="nicdark_space50"></div>
    <div class="grid grid_12">
        <h1 class="subtitle greydark">Trombinoscope</h1>
        <div class="nicdark_space20"></div>
        <h3 class="subtitle grey">les acteurs du conseil d'administration</h3>
        <div class="nicdark_space20"></div>
        <div class="nicdark_divider left big"><span class="nicdark_bg_orange nicdark_radius"></span></div>
        <div class="nicdark_space10"></div>
    </div>

    <?php foreach($organisations AS $organisation){ 
            $colors = $this->render_object->GetColors($organisation->color); ?>
            <div class="nicdark_space50"></div>    
            <div class="grid grid_12">
                <h1 class="subtitle greydark"><?php echo $this->render_object->RenderElement('title', $organisation->title, null, 'Orgchart_model'); ?></h1>
                <div class="nicdark_space10"></div>
                <h3 class="subtitle grey"><?php echo $this->render_object->RenderElement('mission', $organisation->mission, null, 'Orgchart_model'); ?></h3>
                <div class="nicdark_space10"></div>
                <div class="nicdark_divider left big"><span class="<?php echo $colors->color;?> nicdark_radius"></span></div>
                <div class="nicdark_space10"></div>
            </div>

            <?php 
                if(count($organisation->acteurs)){
                foreach($organisation->acteurs as $key=>$acteur){ 
                //echo debug($acteur);
                ?>
                <div class="grid grid_3">
                    <div class="nicdark_archive1 nicdark_bg_grey nicdark_radius nicdark_shadow center">
                        <div class="nicdark_textevidence nicdark_bg_greydark nicdark_radius_top">
                            <h4 class="white nicdark_margin20"><?php echo $this->render_object->RenderElement('surname',$acteur->details->surname, null, 'GroupesMembers_model'); ?> <?php echo $this->render_object->RenderElement('name',$acteur->details->name, null, 'GroupesMembers_model'); ?></h4>
                        </div>
                        <?php echo $this->render_object->RenderElement('picture', $acteur->details->picture,null, 'GroupesMembers_model', 'nicdark_opacity height135');?>
                        <div class="nicdark_textevidence <?php echo $colors->color;?>">
                            <h5 class="white nicdark_margin20"><?php echo $this->render_object->RenderElement('classif',$acteur->classif, null, 'Trombi_model'); ?></h5>
                            <i class="icon-brush nicdark_iconbg right medium <?php echo $colors->icon;?>"></i>
                        </div>
                        <div class="nicdark_textevidence blockTxt">
                            <div class="nicdark_margin10 ">
                                <p>
                                <?php 
                                if(count($acteur->groups)){
                                    foreach($acteur->groups AS $group){
                                        if ($group->short != $organisation->short)
                                         echo '<a href="'.base_url('Orgchart_controller/view_one/'.$group->id).'" class="nicdark_btn "><i class="icon-doc-text-1 "></i>'.$group->title.'</a> ';
                                    }
                                } else {
                                    echo '&nbsp;';
                                }
                                ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } 
            
            }}?>
                            
        </div>
</section>
<!--end section-->
<?php if ((is_array($reubur) && count($reubur) || is_array($reuca) && count($reuca))) { ?>
<!--start section-->
<section class="nicdark_section">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space40"></div>
        <div class="grid grid_12">
            <h1 class="subtitle greydark">AGENDA</h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">L'agenda de nos activités</h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_blue nicdark_radius"></span></div> 
        </div>
    </div>
    <!--end nicdark_container-->
</section>
<!--end section-->

<!--start section-->
<section class="nicdark_section">

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space10"></div>

        <div class="grid grid_6">
            <!--start badges-->
            <ul class="nicdark_list ">
                <?php 
                foreach($reubur AS $event){ ?>
                    <li class="nicdark_border_grey">
                        <p><?php echo $this->render_object->RenderElement('title',$event->title, null, 'Event_model'); ?><span href="#" class="nicdark_btn <?php echo $event->color;?> extrasmall nicdark_radius nicdark_shadow white right"><?php echo $this->render_object->RenderElement('date',$event->date, null, 'Event_model'); ?> <?php echo $this->render_object->RenderElement('date',$event->time, null, 'Event_model'); ?></span></p> 
                        <div class="nicdark_space15"></div>
                    </li>
                    <?php
                }
                ?>            
            </ul>
            <!--end badges-->
        </div>

        <div class="grid grid_6">
            <!--start badges-->
            <ul class="nicdark_list ">
            <?php 
                foreach($reuca AS $event){ ?>
                    <li class="nicdark_border_grey">
                        <p><?php echo $this->render_object->RenderElement('title',$event->title, null, 'Event_model'); ?><span class="nicdark_btn <?php echo $event->color;?> extrasmall nicdark_radius nicdark_shadow white right"><?php echo $this->render_object->RenderElement('date',$event->date, null, 'Event_model'); ?> <?php echo $this->render_object->RenderElement('date',$event->time, null, 'Event_model'); ?></span></p> 
                        <div class="nicdark_space15"></div>
                    </li>
                    <?php
                }
                ?>   
                    
            </ul>
            <!--end badges-->
        </div>
        <div class="nicdark_space50"></div>

    </div>
    <!--end nicdark_container-->
            
</section>
<!--end section-->
<?php } ?>