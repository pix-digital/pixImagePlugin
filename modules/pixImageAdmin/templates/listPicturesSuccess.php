<?php foreach ($images as $occurence => $image): ?>
<a href="<?php echo url_for($module.'/editPictures?id=' . $object->getId() . '&occurence=' . $occurence); ?>">
    <img src="<?=$image['thumb']; ?>" border="0"/>
</a>
Position: <?=$occurence; ?>
<br />
<?php endforeach; ?>
<?php echo link_to('Ajouter une nouvelle photo', $module.'/editPictures?id='.$object->getId().'&new=1'); ?>
