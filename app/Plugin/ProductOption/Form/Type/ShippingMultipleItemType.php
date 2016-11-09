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

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class ShippingMultipleItemType extends AbstractType
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
            ->add('quantity', 'integer', array(
                'attr' => array(
                    'min' => 1,
                    'maxlength' => $this->app['config']['int_len'],
                ),
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual(array(
                        'value' => 1,
                    )),
                    new Assert\Regex(array('pattern' => '/^\d+$/')),
                ),
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($app) {
                $form = $event->getForm();

                if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
                    // 会員の場合、CustomerAddressを設定
                    $Customer = $app->user();
                    $form->add('customer_address', 'entity', array(
                        'class' => 'Eccube\Entity\CustomerAddress',
                        'property' => 'shippingMultipleDefaultName',
                        'query_builder' => function (EntityRepository $er) use ($Customer) {
                            return $er->createQueryBuilder('ca')
                                ->where('ca.Customer = :Customer')
                                ->orderBy("ca.id", "ASC")
                                ->setParameter('Customer', $Customer);
                        },
                        'constraints' => array(
                            new Assert\NotBlank(),
                        ),
                    ));
                } else {
                    // 非会員の場合、セッションに設定されたCustomerAddressを設定
                    if ($app['session']->has('eccube.front.shopping.nonmember.customeraddress')) {
                        $customerAddresses = $app['session']->get('eccube.front.shopping.nonmember.customeraddress');
                        $customerAddresses = unserialize($customerAddresses);

                        $addresses = array();
                        $i = 0;
                        /** @var \Eccube\Entity\CustomerAddress $CustomerAddress */
                        foreach ($customerAddresses as $CustomerAddress) {
                            $addresses[$i] = $CustomerAddress->getShippingMultipleDefaultName();
                            $i++;
                        }
                        $form->add('customer_address', 'choice', array(
                            'choices' => $addresses,
                            'constraints' => array(
                                new Assert\NotBlank(),
                            ),
                        ));
                    }
                }
            })
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($app) {
                /** @var \Eccube\Entity\Shipping $data */
                $data = $event->getData();
                /** @var \Symfony\Component\Form\Form $form */
                $form = $event->getForm();

                if (is_null($data)) {
                    return;
                }

                $form['quantity']->setData(1);

                if ($app->isGranted('IS_AUTHENTICATED_FULLY')) {
                    $selectAddress = $app['eccube.repository.customer_address']
                            ->findOneBy(array(
                                          'name01' => $data->getName01(),
                                          'name02' => $data->getName02(),
                                          'zip01' => $data->getZip01(),
                                          'zip02' => $data->getZip02(),
                                          'addr01' => $data->getAddr01(),
                                          'addr02' => $data->getAddr02(),
                                       ));
                    $form['customer_address']->setData($selectAddress);
                }else{
                    if ($app['session']->has('eccube.front.shopping.nonmember.customeraddress')) {
                        $customerAddresses = $app['session']->get('eccube.front.shopping.nonmember.customeraddress');
                        $customerAddresses = unserialize($customerAddresses);

                        $addresses = array();
                        $i = 0;
                        /** @var \Eccube\Entity\CustomerAddress $CustomerAddress */
                        foreach ($customerAddresses as $CustomerAddress) {
                            if($CustomerAddress->getName01() == $data->getName01() &&
                               $CustomerAddress->getName02() == $data->getName02() &&
                               $CustomerAddress->getZip01() == $data->getZip01() &&
                               $CustomerAddress->getZip02() == $data->getZip02() &&
                               $CustomerAddress->getAddr01() == $data->getAddr01() &&
                               $CustomerAddress->getAddr02() == $data->getAddr02()
                              ){
                                $form['customer_address']->setData($i);
                                break;
                            }
                            $i++;
                        }
                    }
                }


            })
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'plg_shipping_multiple_item';
    }
}
