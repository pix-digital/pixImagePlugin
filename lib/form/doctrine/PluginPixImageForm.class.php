<?php

/**
 * PluginPixImage form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginPixImageForm extends BasePixImageForm
{
    public function setup()
    {
        parent::setup();

        unset(
        $this['type'],
        $this['location'],
        $this['created_at'],
        $this['updated_at']
        );

        $this->widgetSchema['classname'] = new sfWidgetFormInputHidden;
        $this->widgetSchema['object_id'] = new sfWidgetFormInputHidden;
        $this->widgetSchema['occurence'] = new sfWidgetFormInputHidden;

        /*if(!$this->isNew()){
            $this->widgetSchema['occurence'] = new sfWidgetFormInputText;
        }*/

        if ($this->isNew()) {
            $this->widgetSchema['binary_content'] = new sfWidgetFormInputFile;
            $this->validatorSchema['binary_content'] = new sfValidatorFile(array(
                'mime_types' => 'web_images',
                'required' => false
            ));
            $this->widgetSchema['binary_content']->setLabel('Image');
        }

        $related_object = $this->getObject()->getObject();

        $this->formats = $related_object->getConfig(strtolower($this->getObject()->getClassname()));

        if ($this->getObject()->exists()) {
            $this->embedFormForEach('ImageFormats', new PixImageFormatsForm, count($this->formats));
        }

        /*$this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'checkOccurence')))
        );*/

    }

    public function updateDefaultsFromObject()
    {
        parent::updateDefaultsFromObject();

        if ($this->getObject()->exists()) {
            $file_formats = $this->getObject()->getRelatedFormats();

            // object exists populate format form
            $values = array();
            $pos = 0;
            foreach ($this->formats as $index => $format) {
                foreach ($file_formats as $fileFormat) {
                    if ($fileFormat->type == $index) {
                        $file_format = $fileFormat;
                    }
                }


                $values[$pos] = array(
                    'use_custom' => $file_format->getIsCustomFormat(),
                    'format' => $file_format->getType(),
                    'binary_content' => null
                );

                $pos++;
            }

            $this->setDefault('ImageFormats', $values);
        }

    }

    public function updateObject($values = null)
    {
        parent::updateObject($values);

        $binary_content = $this->getValue('binary_content');

        if ($binary_content instanceof sfValidatedFile) {
            // copy reference image to location
            $this->getObject()->setLocation($binary_content->getOriginalName());
            $this->getObject()->setType('reference');
            $this->getObject()->setTempLocation($binary_content->getTempName());
        }
    }

    public function save($con = null)
    {

        $object = parent::save($con);

        $values = $this->getValues();

        $update_files = false;
        $customs = array();
        if (isset($values['ImageFormats'])) {
            foreach ($values['ImageFormats'] as $format_infos) {
                $format = $format_infos['format'];

                $customs[$format]['use_custom'] = $format_infos['use_custom'];
                $customs[$format]['binary_content'] = $format_infos['binary_content'];
                $update_files = true;
            }
        }

        Doctrine::getTable('PixImage')->generateFormats($object, $customs);



        $location =  'reference-' . $object->getObject()->getSlug() . '-' . $values['occurence'] . '.jpg' ;
        $object->setLocation($location);
        $object->setOccurence($values['occurence']);
        $object->save($con);

        return $object;
    }

    /*protected function doSave($con = null)
    {
        $object = $this->getObject();

        $values = $this->getValues();

        $update_files = false;
        $customs = array();
        if (isset($values['ImageFormats'])) {
            foreach ($values['ImageFormats'] as $format_infos) {
                $format = $format_infos['format'];

                $customs[$format]['use_custom'] = $format_infos['use_custom'];
                $customs[$format]['binary_content'] = $format_infos['binary_content'];
                $update_files = true;
            }
        }

        Doctrine::getTable('PixImage')->generateFormats($object, $customs);

        $location = $object->getLocation();

        $object->setLocation($location);
        $object->setOccurence($this->getValue('occurence'));
        $object->save($con);

        // update occurence for all related records if modified
        if(!$this->isNew()){
            $reference_object = Doctrine::getTable('PixImage')->find($values['id']);
            if ($reference_object->getOccurence() != $values['occurence']) {
                $related_objects = Doctrine::getTable('PixImage')->findByObjectId($values['object_id']);
                foreach ($related_objects as $related_object) {
                    $related_object->setOccurence($values['occurence']);
                    $related_object->save();
                }
            }
        }
    }   */

    /*public function checkOccurence($validator, $values)
    {
        $occurence = $values['occurence'];
        $other_objects = Doctrine::getTable('PixImage')->findBy($values['id']);
        if (!$category->getNode()->isLeaf()) {
            $error = new sfValidatorError($validator, 'Merci de sélectionner une catégorie enfant');
            throw new sfValidatorErrorSchema($validator, array('category_id' => $error));
        }

        return $values;
    }    */

}
