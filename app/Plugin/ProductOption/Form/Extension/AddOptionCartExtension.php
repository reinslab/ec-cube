<?php
/*
 * Plugin Name : ProductOption
 *
 * Copyright (C) 2015 BraTech Co., Ltd. All Rights Reserved.
 * http://www.bratech.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductOption\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

class AddOptionCartExtension extends AbstractTypeExtension
{

    public $app;
    public $ProductOptions = null;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;
        $ProductOptions = $options['product_option'];
        $this->ProductOptions = $ProductOptions;

        if ('POST' === $app['request']->getMethod() && $ProductOptions === null) {
            $Product = $options['product'];
            $ProductOptions = $app['eccube.productoption.repository.product_option']->getListByProductId($Product->getId());
        }

        if (is_array($ProductOptions)) {
            foreach ($ProductOptions as $ProductOption) {
                $Option = $ProductOption->getOption();
                $type_id = $Option->getType()->getId();
                $options = array(
                    'label' => $Option->getName(),
                );
                if ($Option->getIsRequired()) {
                    $options['required'] = true;
                    $options['constraints'] = array(
                        new Assert\NotBlank(),
                    );
                } else {
                    $options['required'] = false;
                }
                if ($type_id == 1) {
                    $options['expanded'] = false;
                    $options['multiple'] = false;
                    $options['choices'] = $Option->getOptionCategoriesSelect();
                    $options['empty_value'] = false;
                    if ($options['required'] === true) {
                        if($Option->getDisableCategory()){
                            $options['constraints'][] = new Assert\NotEqualTo(array(
                                'value' => $Option->getDisableCategory()->getId(),
                                'message' => 'This value should not be blank.',
                            )); 
                        }
                    }
                    if($Option->getDefaultCategory()){
                        $options['data'] = $Option->getDefaultCategory()->getId();
                    }
                    $form_type = 'choice';
                } elseif ($type_id == 2) {
                    $options['expanded'] = true;
                    $options['multiple'] = false;
                    $options['choices'] = $Option->getOptionCategoriesSelect();
                    $options['empty_value'] = false;
                    if ($options['required'] === true) {
                        if($Option->getDisableCategory()){
                            $options['constraints'][] = new Assert\NotEqualTo(array(
                                'value' => $Option->getDisableCategory()->getId(),
                                'message' => 'This value should not be blank.',
                            ));                
                        }
                    }
                    if($Option->getDefaultCategory()){
                        $options['data'] = $Option->getDefaultCategory()->getId();
                    }
                    $form_type = 'choice';
                } elseif ($type_id == 3) {
                    $form_type = 'text';
                } elseif ($type_id == 4) {
                    $form_type = 'textarea';
                }
                $builder->add('productoption' . $Option->getId(), $form_type, $options);
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'product_option' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'add_cart';
    }

}
