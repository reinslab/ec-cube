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

class OptionType extends AbstractType
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
                // 基本情報
                ->add('name', 'text', array(
                    'label' => 'オプション表示タイトル',
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                ))
                ->add('manage_name', 'text', array(
                    'label' => 'オプション管理名',
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                ))
                ->add('description', 'textarea', array(
                    'label' => '説明文',
                    'required' => false,
                ))
                ->add('Type', 'type', array(
                    'label' => 'タイプ',
                ))
                ->add('description_flg', 'choice', array(
                    'label' => 'オプション説明',
                    'choices' => array('0' => '表示しない', '1' => '表示する'),
                ))
                ->add('pricedisp_flg', 'checkbox', array(
                    'label' => '価格・送料無料条件の表示',
                ))
                ->add('is_required', 'checkbox', array(
                    'label' => '必須チェック',
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
        return 'admin_product_option';
    }

}
