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

namespace Plugin\ProductOption\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class ShippingMultipleType extends AbstractType
{

    public $app;

    public function __construct(\Eccube\Application $app)
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
            ->addEventListener(FormEvents::POST_SET_DATA, function ($event) use ($app) {
                /** @var \Eccube\Entity\ShipmentItem $data */
                $data = $event->getData();
                /** @var \Symfony\Component\Form\Form $form */
                $form = $event->getForm();

                if (is_null($data)) {
                    return;
                }

                $shippings = $app['eccube.productoption.service.shopping']->findShippingsProduct($data);

                $form
                    ->add('shipping', 'collection', array(
                        'type' => 'plg_shipping_multiple_item',
                        'data' => $shippings,
                        'allow_add' => true,
                        'allow_delete' => true,
                    ));

            })
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'plg_shipping_multiple';
    }
}