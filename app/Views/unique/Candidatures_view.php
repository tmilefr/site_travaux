<?php
/**
 * Vue de détail d'une candidature (action view).
 * La ligne courante est posée dans $render_object (dba_data).
 */
$dba = $render_object->_get('dba_data');
?>
<!--start section-->
<section class="nicdark_section">
    <!--start nicdark_container-->
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>
        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    <?php echo esc(trim(($dba->surname ?? '') . ' ' . ($dba->name ?? ''))); ?>
                </span>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3"><?php echo $render_object->label('id_fam'); ?></dt>
                    <dd class="col-sm-9"><?php echo $render_object->RenderElement('id_fam'); ?></dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('id_grp'); ?></dt>
                    <dd class="col-sm-9"><?php echo $render_object->RenderElement('id_grp'); ?></dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('name'); ?></dt>
                    <dd class="col-sm-9"><?php echo esc($dba->name ?? ''); ?></dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('surname'); ?></dt>
                    <dd class="col-sm-9"><?php echo esc($dba->surname ?? ''); ?></dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('email'); ?></dt>
                    <dd class="col-sm-9">
                        <?php if (!empty($dba->email)) : ?>
                            <a href="mailto:<?php echo esc($dba->email, 'attr'); ?>"><?php echo esc($dba->email); ?></a>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('phone'); ?></dt>
                    <dd class="col-sm-9"><?php echo esc($dba->phone ?? ''); ?></dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('memo'); ?></dt>
                    <dd class="col-sm-9"><?php echo nl2br(esc($dba->memo ?? '')); ?></dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('created'); ?></dt>
                    <dd class="col-sm-9"><?php echo esc($dba->created ?? ''); ?></dd>

                    <dt class="col-sm-3"><?php echo $render_object->label('updated'); ?></dt>
                    <dd class="col-sm-9"><?php echo esc($dba->updated ?? ''); ?></dd>
                </dl>
                <?php echo $render_object->render_element_menu(); ?>
            </div>
        </div>
    </div>
    <!--end nicdark_container-->
</section>
<!--end section-->
