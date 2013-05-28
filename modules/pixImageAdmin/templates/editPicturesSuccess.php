<?php include_partial('pixImageAdmin/form', array(
    'image_form'  => $image_form,
    'image_types' => $image_types,
    'object'      => $object,
    'helper'      => $helper,
    'occurence'   => $occurence,
)); ?>
<ul class="sf_admin_actions">
    <li class="sf_admin_action_delete"><?php echo link_to('Delete', $sf_request->getParameter('module').'/deletePictures?id='.$image_form->getObject()->getObjectId().'&occurence='.$occurence, array('confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete'))?></li>
    <li class="sf_admin_action_list"><?php echo link_to('Back to pictures list', $sf_request->getParameter('module').'/listPictures?id='.$image_form->getObject()->getObjectId())?></li>
    <?php echo $helper->linkToList(array(  'params' =>   array(  ),  'class_suffix' => 'list',  'label' => 'Back to product list',)) ?>
</ul>
