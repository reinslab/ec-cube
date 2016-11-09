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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionCategoryType extends AbstractType
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
        $Option = $options['data']['Option'];

        $builder
                // 基本情報
                ->add('name', 'text', array(
                    'label' => '選択肢名',
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                ))
                ->add('description', 'textarea', array(
                    'label' => '説明文',
                    'required' => false,
                ))
                ->add('option_image', 'file', array(
                    'label' => '画像',
                    'required' => false,
                    'mapped' => false,
                ))
                ->add('disable_flg', 'checkbox', array(
                    'label' => '未選択設定',
                ))
                ->add('init_flg', 'checkbox', array(
                    'label' => '初期値設定',
                ))
                // 画像
                ->add('images', 'collection', array(
                    'type' => 'hidden',
                    'prototype' => true,
                    'mapped' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                ))
                ->add('add_images', 'collection', array(
                    'type' => 'hidden',
                    'prototype' => true,
                    'mapped' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                ))
                ->add('delete_images', 'collection', array(
                    'type' => 'hidden',
                    'prototype' => true,
                    'mapped' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                ))
                ->add('value', 'price', array(
                    'label' => '金額',
                    'required' => false,
                    'constraints' => array(
                        new Assert\Length(array('max' => $this->app['config']['price_len'])),
                    )
                ))
                ->add('delivery_free_flg', 'choice', array(
                    'label' => '送料無料',
                    'choices' => array('0' => '設定なし', '1' => '無料設定'),
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
        return 'admin_product_option_category';
    }

}
