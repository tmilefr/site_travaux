<?php
defined('BASEPATH') OR exit('No direct script access allowed');
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
                <h1 class="white subtitle"><?php echo lang('PUBLICS_FILES'); ?></h1>
                <div class="nicdark_space10"></div>
                <h3 class="subtitle white"><?php echo lang('PUBLICS_FILES_subtitle'); ?></h3>
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

        <div class="grid">
            <!--TABLE-->
            <div class="nicdark_textevidence nicdark_bg_grey nicdark_shadow nicdark_radius left overflow_scroll">   
                <table class="nicdark_table extrabig nicdark_bg_yellow nicdark_radius ">
                    <thead class="nicdark_border_yellow">
                        <tr>
                            <td class="white"><h4 class="white"><?php echo LANG('DOCUMENTS');?></h4></td>
                            <td class="nicdark_width_percentage20"></td>
                        </tr>
                    </thead>
                    <tbody class="nicdark_bg_grey nicdark_border_grey">
                        <?php 
                        if (is_array($pvca) AND count($pvca)){
                            foreach($pvca AS $file){ ?>                            
                            <tr>
                                    <td><p><?php echo $this->render_object->RenderElement('memo',$file->memo, null, 'Files_model'); ?></p></td>
                                    <td><p><a class="grey nicdark_btn nicdark_bg_grey2  medium nicdark_radius nicdark_shadow" target='_new' href="<?php echo $this->render_object->RenderElement('path',$file->path, null, 'Files_model'); ?>"><i class="icon-download-outline"></i> <?php echo $this->render_object->RenderElement('name',$file->name, null, 'Files_model'); ?></a></p></td>
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
