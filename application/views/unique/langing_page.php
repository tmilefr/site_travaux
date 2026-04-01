<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_5">
            <h1 class="subtitle greydark"><?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action'));?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action').$this->acl->getType().'_subtitle');?>
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>
            <?php echo Lang('INFO_UNITS');?>
        </div>

    </div>
</section>

<!--start section-->
<section class="nicdark_section nicdark_margintop45_negative">

    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
		
		<div class="grid grid_12 percentage nomargin">    
			<div class="nicdark_textevidence center">
			    <div class="nicdark_textevidence nicdark_width_percentage25 nicdark_bg_blue nicdark_shadow nicdark_radius_left">
			        <div class="nicdark_textevidence">
			            <div class="nicdark_margin30">
			                <h2 class="white subtitle"><a class="white" href="courses.html">COURSES</a></h2>
			           </div>
			            <i class="nicdark_zoom icon-pencil-2 nicdark_iconbg left extrabig blue nicdark_displaynone_ipadland nicdark_displaynone_ipadpotr"></i>
			        </div>
			    </div>
			    <div class="nicdark_textevidence nicdark_width_percentage25 nicdark_bg_yellow nicdark_shadow">
			        <div class="nicdark_textevidence">
			            <div class="nicdark_margin30">
			                <h2 class="white subtitle"><a class="white" href="prices.html">PRICES</a></h2>
			           </div>
			            <i class="nicdark_zoom icon-money-1 nicdark_iconbg left extrabig yellow nicdark_displaynone_ipadland nicdark_displaynone_ipadpotr"></i>
			        </div>
			    </div>
			    <div class="nicdark_textevidence nicdark_width_percentage25 nicdark_bg_orange nicdark_shadow">
			        <div class="nicdark_textevidence">
			            <div class="nicdark_margin30">
			                <h2 class="white subtitle"><a class="white" href="events.html">EVENTS</a></h2>
			           </div>
			            <i class="nicdark_zoom icon-music-2 nicdark_iconbg left extrabig orange nicdark_displaynone_ipadland nicdark_displaynone_ipadpotr"></i>
			        </div>
			    </div>
			    <div class="nicdark_textevidence nicdark_width_percentage25 nicdark_bg_green nicdark_shadow nicdark_radius_right">
			        <div class="nicdark_textevidence">
			            <div class="nicdark_margin30">
			                <h2 class="white subtitle"><a class="white" href="teachers.html">TEACHERS</a></h2>
			           </div>
			            <i class="nicdark_zoom icon-graduation-cap-1 nicdark_iconbg left extrabig green nicdark_displaynone_ipadland nicdark_displaynone_ipadpotr"></i>
			        </div>
			    </div>
			    <div class="nicdark_space5"></div>
			</div>
		</div>

	</div>
    <!--end nicdark_container-->
     
</section>