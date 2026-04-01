<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="fr"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="fr"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="fr"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="fr"> <!--<![endif]-->
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!--meta responsive-->
	<?php $this->bootstrap_tools->RenderAttachFiles('css');?>
	<?php $this->bootstrap_tools->RenderAttachFiles('font');?>
	<title
	><?php echo $app_name;?></title>
	<!--[if lt IE 9]>  
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>  
    <![endif]-->  
	
</head>  
<body id="start_nicdark_framework"> <!--class="nicdark_boxed_pattern"-->
	<div class="nicdark_site">
		<div class="nicdark_site_fullwidth nicdark_clearfix"><div class="nicdark_overlay"></div> <!--nicdark_site_boxed -->

	<div class="nicdark_menu_boxed">
		<div class="nicdark_section nicdark_bg_greydark nicdark_displaynone_responsive">
            <div class="nicdark_container nicdark_clearfix">
                <div class="grid grid_6">
                    <div class="nicdark_focus">
						<h4 class="white"><?php echo $app_name;?>
						</h4>
                    </div>
                </div>
                <div class="grid grid_6 right">
                    <div class="nicdark_focus right">
						<h6 class="white right">
							<ul class="nav">
								<li>
								<!-- search box => todo -->
								<?php
								if ($search_object->autorize){
									$attributes = array('class' => 'form-inline', 'id' => 'myform');
									echo form_open($search_object->url, $attributes);?>
									<input class="form-control mr-sm-2" type="search" name='global_search' id='global_search' placeholder="Search" aria-label="Search" value="<?php echo $search_object->global_search;?>">
									<button class="btn btn-success btn-sm" type="submit"><span class="oi oi-magnifying-glass"></span></button>&nbsp;
									<?php if ($search_object->global_search){ ?>
										<a href='<?php echo base_url($search_object->url);?>/search/reset' class='btn btn-warning btn-sm'><span class="oi oi-circle-x"></span></a>
									<?php } ?>
									</form>
									<?php
								}
								?>
								</li>
								<?php echo $this->render_menu->Get('sysmenu');?>						
								<?php echo $this->render_menu->Get('optionmenu');?>	
								<?php if ( $this->acl->Islog() ) { ?>
									<li class="nav-item dropdown">
										<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $this->acl->GetUserName(); ?></span>
										</a>
										<!-- Dropdown - User Information -->
										<div class="dropdown-menu dropdown-menu" aria-labelledby="userDropdown">
											<a class="dropdown-item" href="<?php echo base_url('Home/myaccount');?>"><span class="oi oi-person"></span> <?php echo Lang('Myaccount');?></a>
											<a class="dropdown-item" href="<?php echo base_url('Home/logout');?>"><span class="oi oi-account-logout"></span> <?php echo Lang('Login_out');?></a>
										</div>
										
									</li>
								<?php } ?>							
							</ul>
						</h6>
                    </div>
                </div>
            </div>
		</div>


        <div class="nicdark_space3 nicdark_bg_gradient"></div>
                    
        <div class="nicdark_section nicdark_bg_grey nicdark_shadow nicdark_radius_bottom">
            <div class="nicdark_container nicdark_clearfix">

                <div class="grid grid_12 percentage">
                        
                        <div class="nicdark_space20"></div>

                        <div class="nicdark_logo nicdark_marginleft10">
                            <a href="<?php echo base_url('Home');?>">
								<?php echo $this->render_object->RenderImg('regio.png',$slogan);?>
							</a>
                        </div>
						<?php if ($this->render_object->In_maintenance()){
									echo '<span class="badge badge-warning">'.Lang('Maintenance_in_progress').'</span>';
								} ?>
						<nav>
                            <ul class="nicdark_menu blue nicdark_margin010 nicdark_padding50">
								<?php echo $this->render_menu->Get('mainmenu');?>	
                            </ul>
							
                        </nav>
						
                        <div class="nicdark_space20"></div>

                </div>

            </div>
            <!--end container-->

        </div>
        <!--end header-->

    </div>
	<!--start-->




		

