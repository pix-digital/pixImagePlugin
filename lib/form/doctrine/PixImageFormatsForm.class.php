<?php
/*
 * This file is part of the trShopPlugin package.
 * (c) 2008 Thomas Rabaix <thomas.rabaix@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 *
 * @package    pixImagePlugin
 * @subpackage lib
 * @author     Thomas Rabaix <thomas.rabaix@gmail.com>
 */
class PixImageFormatsForm extends sfForm
{
    public function configure()
    {
        $this->widgetSchema['binary_content'] = new sfWidgetFormInputFile;
        $this->validatorSchema['binary_content'] = new sfValidatorFile(array(
            'mime_types' => 'web_images',
            'required' => false
        ));
        $this->widgetSchema['binary_content']->setLabel('Image');

        $this->widgetSchema['format'] = new sfWidgetFormInputHidden;
        $this->validatorSchema['format'] = new sfValidatorString(array(
            'required' => true
        ));

        $this->widgetSchema['use_custom'] = new sfWidgetFormInputCheckbox;
        $this->validatorSchema['use_custom'] = new sfValidatorBoolean(array(
            'required' => false
        ));

        $this->setDefaults(array(
            'binary_content' => null,
            'use_custom' => false,
            'format' => null
        ));
    }
}