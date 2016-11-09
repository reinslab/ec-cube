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

namespace Plugin\ProductOption\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    public $app;

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

        $builder
            ->add('tpl_option', 'textarea', array(
                'label' => 'オプション選択テンプレート',
                'mapped' => false,
                'required' => true,
                'constraints' => array()
            ))
            ->add('tpl_option_description', 'textarea', array(
                'label' => 'オプション説明テンプレート',
                'mapped' => false,
                'required' => true,
                'constraints' => array()
            ))
            ->add('tpl_option_price', 'textarea', array(
                'label' => 'オプション価格合計表示テンプレート',
                'mapped' => false,
                'required' => true,
                'constraints' => array()
            ))
            ->add('tpl_option_point', 'textarea', array(
                'label' => 'オプションポイント合計表示テンプレート',
                'mapped' => false,
                'required' => true,
                'constraints' => array()
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber())
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_setting_productoption';
    }
}
