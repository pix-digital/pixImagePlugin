<?php

/**
 * PluginPixImage
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginPixImage extends BasePixImage
{
    protected
        $object,
        $temp_location;


    /**
     * Create required folder to save current object
     * update 04/07/14: to avoid open_base_dir restriction we define the sf_web_dir in the initial path
     *
     */
    public function initFolders()
    {
        $path = sfConfig::get('sf_web_dir');

        foreach (explode('/', $this->getPublicPath()) as $folder) {
            if (empty($folder)) {
                continue;
            }
            $path .= '/' . $folder;
            if (!is_dir($path)) {
                if (!mkdir($path, 0777, true)) {

                    throw new sfException('unable to create path = ' . $path);
                }
            }
        }
    }

    public function __toString()
    {
        if (!$this->exists()) {

            return '';
        }

        return $this->getPublicPath();
    }

    public function getRelatedFormats()
    {
        return $this->getTable()->getFromId($this->classname, $this->object_id, null, $this->occurence);
    }

    /**
     * return the full path of the picture
     *
     * @return string
     */
    public function getFullBasePath()
    {
        $picture_configs = sfConfig::get('app_pixImage_config');

        return implode("/", array(
            $picture_configs['upload_dir'],
            $this->getClassname(),
            $this->getObjectId()
        ));
    }

    /**
     * return the full path of the picture
     *
     * @return string
     */
    public function getFullPath()
    {

        return $this->getFullBasePath() . '/' . $this->getLocation();
    }

    /**
     * return the relative path of the picture
     *
     * @return string
     */
    public function getRelativePath()
    {
        $picture_configs = sfConfig::get('app_pixImage_config');

        return implode("/", array(
            "",
            sfConfig::get('sf_upload_dir_name'),
            $picture_configs['upload_dir'],
            $this->getClassname(),
            $this->getObjectId(),
            $this->getLocation(),
        ));
    }

    /**
     * return the relative path of the picture
     *
     * @return string
     */
    public function getPublicPath()
    {
        $picture_configs = sfConfig::get('app_pixImage_config');

        return implode("/", array(
            $picture_configs['public_path'],
            $this->getClassname(),
            $this->getObjectId(),
            $this->getLocation(),
        ));
    }

    /**
     * resize the picture, see sfThumbnail for more options
     *
     * WARNING : not possible to revert the action
     *
     * @param int $maxWidth
     * @param int $maxHeight
     * @param boolean $scale
     * @param boolean $inflate
     * @param int $quality
     * @param string $adapterClass
     * @param array $adapterOptions
     */
    public function resizeTo($maxWidth = null, $maxHeight = null, $scale = true, $inflate = true, $quality = 75, $adapterClass = null, $adapterOptions = array())
    {
        $thumbnail = new sfThumbnail($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterClass, $adapterOptions);
        $thumbnail->loadFile($this->getFullPath());
        $thumbnail->save($this->getFullPath());

    }

    /**
     * define the related object to the current image, object cannot be new
     *
     * @param object $object
     */
    public function setObject($object, $occurence)
    {

        if (!$object->exists()) {

            throw new sfException('[PixImage::setObject] the object cannot be null');
        }

        $this->setClassname(get_class($object));
        $this->setObjectId($object->getId());
        $this->setOccurence($occurence);

        $this->object = $object;
    }

    public function isFileExists($format = 'reference')
    {
        return is_file($this->getFullPath());
    }

    public function getObject()
    {
        if (is_null($this->object)) {
            $this->object = Doctrine::getTable($this->getClassname())->find($this->getObjectId());
        }

        return $this->object;
    }

    /**
     * Copy the object to a new object
     *
     * @param boolean $deepCopy
     * @return Image
     */
    public function copy($deepCopy = false)
    {
        $obj = parent::copy($deepCopy);

        // only copy the picture if the file exists
        if (is_file($this->getFullPath())) {
            $temp_name = tempnam(sys_get_temp_dir(), 'copy_product_');

            $obj->setTempLocation($temp_name);
            copy($this->getFullPath(), $temp_name);
        }

        return $obj;
    }

    public function setTempLocation($location)
    {
        $this->temp_location = $location;
    }

    public function getFilename()
    {

        return $this->getType() . '-' . $this->getObject()->getSlug() . '-' . $this->occurence . '.' . $this->getExtension();
    }


    public function getExtension()
    {
        $info = pathinfo($this->getLocation());

        if (array_key_exists('extension', $info)) {

            return $info['extension'];
        }

        return null;
    }

    public function postSave($event)
    {
        if ($this->temp_location) {
            $this->initFolders();

            copy($this->temp_location, $this->getFullPath());
        }


        if ($this->getType() == 'reference' && $this->isFileExists('reference')) {
            Doctrine::getTable('PixImage')->updateFormats($this);
        }
    }

    public function getFirstOccurenceForObject($object)
    {
        $image = Doctrine::getTable('PixImage')->getFirstForObject($object);
        if ($image instanceof PixImage) {
            return $image->occurence;
        }
        return 1;
    }

    public function getLastOccurenceForObject($object)
    {
        $image = Doctrine::getTable('PixImage')->getLastForObject($object);
        if ($image instanceof PixImage) {
            return $image->occurence;
        }
        return 1;
    }
}