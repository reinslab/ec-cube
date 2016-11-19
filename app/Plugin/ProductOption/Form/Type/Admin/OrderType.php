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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class OrderType extends AbstractType
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
            ->add('OrderDetailOptions', 'collection', array(
                'type' => 'order_detail_option',
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
            ))
            ->add('ShipmentItemOptions', 'collection', array(
                'type' => 'shipment_item_option',
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
            ))
            ->add('AddProductOptions', 'collection', array(
                'type' => 'option_select',
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'mapped' => false,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'plg_productoption_order';
    }

}
