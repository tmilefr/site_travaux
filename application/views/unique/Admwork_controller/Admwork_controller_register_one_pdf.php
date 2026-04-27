<?php
$work = $datas;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
body {
	margin: 0;
	padding: 0;
	font-family: Arial, Verdana, Geneva, Sans-serif;
	font-size: 14px;
	color: #4F5155;
}

@page { margin: 90px 10px 0px 20px; }

#header { position: fixed; left: 0px; top: -80px; right: 0px; height: 80px; text-align: left; }
#footer { position: fixed; left: 0px; bottom: 0px; right: 0px; height: 100px; text-align: center;  }
#footer .page:after { content: counter(page, upper-roman); }
#content {text-align:left; padding-bottom:40px; }


table{
	width:100%;
	background-color:#FFF;
	margin: auto;
}

.table_page{
	width:80%;
}

.table_border{
	border: 0.5px solid #000000;
}

table td{
	padding: 2px;
	margin : 0px;
	font-family: Arial, Verdana, Geneva, Sans-serif;
	font-size: 14px;
	vertical-align: top;
}

.nowrap{
	white-space:nowrap;
}

table .small{
	font-size: 10px;
}

.souligne{
	border-top: 0.5px solid #000000;
}

.sep_dashed{
	border-bottom: 1px dotted #000000;
	padding-top:5px;
}

.text-right{
	text-align:	right;
}

.text-center{
	text-align:	center;
}


table th {
	font-weight: bold;
	font-size: 14px;
	background-color: #666;
	color: #fff;
	padding: 0px 2px 0px 2px;
	white-space:nowrap;
	vertical-align: top;
}


h1{
	color: #444;
	font-size: 22px;
	margin:0;
	padding:0;
}

h2{
	color: #444;
	font-size: 16px;
	margin:0;
	padding:0;
}


h3{
	color: #444;
	font-size: 14px;
	margin:0;
	padding:0;
}

h4{
	color: #444;
	font-size: 12px;
	margin:0;
	padding:0;
}

p{
	font-size: 11px;
	margin:0;
	padding:0;
}

#footer {

}

#footer p{
	font-size:10px;
	text-align:left;
	padding-left:100px;
}


.pair{
	background-color:#F4FAFF;
	color:#515252;
}

.red{
	color:#E0212F;
}
.blue{
	color:#72B1D7;
}
.violet{
	color:#AF5C91;
}

.underline{
	text-decoration:underline;
}

.nowrap{
	white-space:nowrap;
}
</style>
</head>
<body>
	<div id="header">
		<table>
		<tr>
			<td width="80"><?php echo $logo;?></td>
			<td>
				<h1 class="subtitle greydark"><?php echo $this->render_object->RenderElement('titre',$work->titre);?></h1>
				<h3 class="subtitle grey">
					<i class="icon-calendar"></i> <?php echo $this->render_object->RenderElement('date_travaux', $work->date_travaux);?><br/>
					<i class="icon-info-outline"></i>  <?php echo $this->lang->line('TITRE_TYPE_SESSION');?> <?php echo $this->render_object->RenderElement('type_session', $work->type_session);?><br/>
					<?php if ($work->type_session == 1){ ?>
						<i class="icon-clock-1"></i><?php echo $this->render_object->RenderElement('heure_deb_trav',$work->heure_deb_trav);?> à <?php echo $this->render_object->RenderElement('heure_fin_trav',$work->heure_fin_trav);?><br/>
					<?php } ?>
					<i class="icon-pin-outline"></i> <?php echo $this->render_object->RenderElement('ecole', $work->ecole ) ;?>
				</h3>
			</td>
		</tr>
		</table>
	<div id="footer">
			
	</div>
	<div id="content">

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
            <div class="nicdark_divider left big"><span class="<?php echo $work->design->color;?> nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>

            <?php echo $this->render_object->RenderElement('description',$work->description);?>
        
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="<?php echo $work->design->color;?> nicdark_radius"></span></div>
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
			<h1 class="subtitle greydark"><?php echo  $this->lang->line('REGISTRED');?></h1>
			<div class="nicdark_space20"></div>
			<table class="nicdark_table extrabig <?php echo $work->design->color;?> nicdark_radius ">
				<thead class="<?php echo $work->design->bordercolor;?>">
					<tr>
						<td><h4 class="white"><?php echo $this->lang->line('type_participant');?></h4></td>
						<td><h4 class="white"><?php echo $this->lang->line('nom');?></h4></td>    
						<td><h4 class="white"><?php echo $this->lang->line('heure_debut_prevue');?></h4></td>
						<td><h4 class="white"><?php echo $this->lang->line('heure_fin_prevue');?></h4></td>
						<td><h4 class="white"><?php echo $this->lang->line('nb_unites_valides');?></h4></td>
						<?php /*<td><h4 class="white"><?php echo $this->lang->line('type_session');?></h4></td>*/?>
						
					</tr>
				</thead>
				<tbody class="nicdark_bg_grey nicdark_border_grey">            
				<?php 
				//ATTENTION au model à utiliser en fonction du champ (voir les json)
				if($work->registred){
				foreach($work->registred AS $unit){ //nicdark_bg_blue nicdark_bg_green nicdark_bg_yellow nicdark_bg_orange nicdark_bg_red ?>
				<tr>
					<td class="sep_dashed"><p><?php echo $this->render_object->RenderElement('type_participant', $unit->type_participant, null, 'Infos_model');?></p></td>
					<td class="sep_dashed"><p><?php echo $this->render_object->RenderElement('nom', $unit->family->nom, null, 'Familys_model');?></p></td>    
					<td class="sep_dashed"><p></p></td>
					<td class="sep_dashed"><p></p></td>
					<td class="sep_dashed"><p></p></td>
					<?php /*<td><p><?php echo $this->render_object->RenderElement('type_session', $unit->type_session, null, 'Admwork_model');?></p></td>*/?>
					
				</tr>
				<?php }
				} else {
					echo '<tr><td colspan="5">'.$this->lang->line('REGISTRED_NONE').'</td></tr>';
				} ?>
				</tbody>
			</table>
		</div>
	</div>
</body>
</html>

