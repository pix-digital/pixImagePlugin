<?php
/**
 * Created by JetBrains PhpStorm.
 * Author: Nicolas R.
 * Date: 31/05/2012
 * Time: 11:26
 */

class PixImageTemplate extends Doctrine_Template
{

    public function setTableDefinition()
    {
    }

    public function setUp()
    {
    }

    public function getConfig($name)
    {
        $config = sfConfig::get('app_pixImage_data');

        return isset($config[$name]) ? $config[$name] : null;
    }

    public function getImage($type, $force = false, $occurence = 1)
    {
        $model = $this->getInvoker();

        $model->loadImages($force, $occurence);

        foreach (@$model->images as $image) {

            if (!$image instanceof pixImage) {
                continue;
            }

            if ($image->type != $type) {
                continue;
            }

            if ($image->getLocation() && file_exists($image->getFullPath())) {
                return $image;
            }
        }


        return $model->getDefaultImage($type);
    }

    public function getAbsoluteImageUrl($type, $force = false, $occurence = 1)
    {
        $model = $this->getInvoker();
        $image = $model->getImage($type, $force, $occurence);

        return sfConfig::get('sf_media_url') . '/' . $image;
    }

    public function getDefaultImage($type)
    {
        $model = $this->getInvoker();
        return sfConfig::get('sf_media_medias') . '/' . $model->getClassName() . '-' . $type . '-default.jpg';
    }

    public function getClassName()
    {
        $model = $this->getInvoker();
        return strtolower(get_class($model));
    }

    /**
     * return all related images
     *
     * @return array
     */
    public function getImages($formats = array(), $force = false)
    {

        $model = $this->getInvoker();
        $model->loadImages($force, null, $formats);

        $images = array();

        foreach (@$model->images as $image) {

            if (!$image instanceof pixImage) {
                continue;
            }

            if (!in_array($image->type, $formats)) {
                continue;
            }

            if ($image->getLocation() && file_exists($image->getFullPath())) {
                $images[$image->occurence][$image->type] = $image;
            } else {
                $images[$image->occurence][$image->type] = $model->getDefaultImage($image->type);
            }
        }

        return $images;
    }

    public function setImages($images)
    {
        $model = $this->getInvoker();
        $model->images = $images;
    }

    /**
     * preload object's images
     *
     */
    public function loadImages($force = false, $occurence = null, $formats = array())
    {
        $model = $this->getInvoker();
        if (empty($model->images) || $force) {
            $types = empty($formats) ? array_merge(array('reference'), array_keys($model->getConfig('product'))) : $formats;
            $model->images = Doctrine::getTable('pixImage')->getFromObject($model, $types, $occurence);
        }
    }

    public function getNextOccurence()
    {
        $model = $this->getInvoker();
        $last_object = Doctrine::getTable('pixImage')->getLastForObject($model);
        if (!$last_object instanceof pixImage) {
            return 1;
        }

        return $last_object->occurence + 1;
    }
}