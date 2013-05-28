<div id="sf_admin_container">

    <h1><?php echo __('Edit Picture')?></h1>

    <h3><?php echo $object->getLabel(); ?></h3>

    <?php include_partial($sf_request->getParameter('module') . '/flashes') ?>

    <?php include_partial($sf_request->getParameter('module') . '/form_header', array('object' => $object, 'form' => $image_form)); ?>


    <form enctype='multipart/form-data'
          action="<?php echo url_for($sf_request->getParameter('module') . '/updatePictures') ?>" method="POST">
        <fieldset id="sf_fieldset_none">

            <input type="hidden" name="object_id" value="<?=$object->getId(); ?>"/>
            <?php echo $image_form->renderHiddenFields() ?>

            <?php if ($image_form->isNew()): ?>
            <div class="sf_admin_form_row">
                <div>
                    <?php echo $image_form['binary_content']->renderLabel() ?>
                    <div class="content">
                        <?php echo $image_form['binary_content'] ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </fieldset>

        <ul class="sf_admin_actions">

            <?php echo $helper->linkToSave($image_form->getObject(), array('params' => array(), 'class_suffix' => 'save', 'label' => 'Save',)) ?>

        </ul>



        <?php if (!$image_form->isNew()): ?>
        <?php if (count($image_form['ImageFormats']) > 0): ?>
            <fieldset id="">
                <?php foreach ($image_form['ImageFormats'] as $image_form): ?>
                <?php $type = $image_form['format']->getValue() ?>
                <?php $image = $object->getImage($type, false, $sf_request->getParameter('occurence')) ?>

                <div class="sf_admin_form_row">
                    <div>
                        <label><?php echo $image_types[$type]['description'] ?>
                            <br/>(<?php echo $image_types[$type]['width'] ?>
                            x <?php echo $image_types[$type]['height'] ?> px)</label>

                        <div class="content">
                            <div id="special">
                                <img src="<?php echo $image?>"/>
                            </div>
                            <br/>
                            <strong><?php echo $image->getFilename() ?></strong>

                            <?php echo $image_form; ?>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
            </fieldset>
            <?php endif ?>
        <?php endif ?>

    </form>

</div>

