<?php
/**
 * pixImageAdmin actions.
 *
 * @package    plugins
 * @subpackage pixImagePlugin
 * @author     Nicolas R. <nr@pix-digital.com>
 */
class BasepixImageAdminActions extends sfActions
{

    public function preExecute()
    {
        $configurationClass = $this->getRequest()->getParameter('module') . 'GeneratorConfiguration';
        $this->configuration = new $configurationClass();

        $this->dispatcher->notify(new sfEvent($this, 'admin.pre_execute', array('configuration' => $this->configuration)));

        $helperClass = $this->getRequest()->getParameter('module') . 'GeneratorHelper';
        $this->helper = new $helperClass();

        parent::preExecute();
    }

    public function executeListPictures($request)
    {
        $model_name = $this->configuration->getForm()->getModelName();
        $object = Doctrine::getTable($model_name)->find($request->getParameter('id'));
        $this->forward404Unless($object);

        // get all pictures
        $this->images = $object->getImages(array('thumb'));
        $this->object = $object;
        $this->module = $request->getParameter('module');

    }


    public function executeEditPictures($request)
    {
        $model_name = $this->configuration->getForm()->getModelName();
        $object = Doctrine::getTable($model_name)->find($request->getParameter('id'));
        $this->forward404Unless($object);

        // get the reference picture
        $occurence = $request->getParameter('new') ? $object->getNextOccurence() : $request->getParameter('occurence', 1);
        $image = $object->getImage('reference', false, $occurence);

        if (!$image instanceof PixImage) {
            $image = new PixImage;
            $image->setObject($object, $occurence);
        }

        if (Doctrine::getTable('PixImage')->updateFormats($image) === true) {
            $this->redirect($request->getParameter('module') . '/editPictures?id=' . $object->getId());
        }

        $this->image_form = new PixImageForm($image);
        $this->object = $object;
        $this->image_types = $object->getConfig(strtolower($model_name));
        $this->occurence = $occurence;

    }

    public function executeUpdatePictures($request)
    {

        $model_name = $this->configuration->getForm()->getModelName();
        $object = Doctrine::getTable($model_name)->find($request->getParameter('object_id'));
        $this->forward404Unless($object);

        // get the reference picture
        $pix_image = $request->getParameter('pix_image');
        $occurence = $pix_image['occurence'];
        $image = $object->getImage('reference', false, $occurence);
        if (!$image instanceof PixImage) {
            $image = new PixImage;
            $image->setObject($object, $occurence);
        }

        $image_form = new PixImageForm($image);

        if ($request->isMethod('post')) {
            $image_form->bind($pix_image, swFormHelper::convertFileInformation($request->getFiles('pix_image')));
            if ($image_form->save()) {
                $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $object)));
                $this->getUser()->setFlash('notice', 'The pictures have been updated successfully');
                $this->redirect($request->getParameter('module') . '/editPictures?id=' . $object->getId() . '&occurence=' . $occurence);
            }
        }

        $this->getUser()->setFlash('notice-error', 'Une erreur s\'est produite');

        $this->image_form = $image_form;
        $this->object = $object;
        $this->image_types = $object->getConfig('pictures');

        $this->setTemplate('editPictures', $request->getParameter('module'));
    }

    /*
    * deletes images record from database
    * deletes images from media folders
    */
    public function executeDeletePictures(sfWebRequest $request)
    {
        $images = Doctrine::getTable('PixImage')->findByObjectIdAndOccurence($request->getParameter('id'), $request->getParameter('occurence'));

        foreach ($images as $image) {
            @unlink($image->getFullBasePath() . '/' . $image->location);
            $object_id = $image->object_id;
            $image->delete();
        }


        $this->redirect($request->getParameter('module') . '/listPictures?id=' . $object_id);
    }

}
