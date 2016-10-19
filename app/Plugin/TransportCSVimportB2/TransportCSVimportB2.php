<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\TransportCSVimportB2;

use Eccube\Common\Constant;
use Eccube\Event\RenderEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;
use Eccube\Util\Str;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;

class TransportCSVimportB2
{

    private $app;

    private $em;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onRenderAdminOrderEditBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $response->setContent($this->getHtmlInvoiceNumber($request, $response));
        $event->setResponse($response);
    }

    public function onRenderAdminOrderBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $response->setContent($this->getHtmlMailAll($request, $response));
        $event->setResponse($response);
    }

    public function onControllerAdminOrderEditBefore()
    {
        $app = $this->app;
        $order_id = $app['request']->get('id');
        
        if (!is_null($order_id)) {
            $TargetOrder = $app['eccube.repository.order']->find($order_id);
            if (!is_null($TargetOrder)) {
                $invoiceNumber = $app['orm.em']->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2')->findBy(array('order_id' => $order_id));

                $formData = array();

                $builder = $this->getBuilder($TargetOrder, $invoiceNumber);
                
                $form = $builder->getForm();
                
                $form->handleRequest($app['request']);

                if ('POST' === $app['request']->getMethod()) {

                    $formData = $form->getData();
/*
                    // 編集前の受注情報を保持
                    $OriginOrder = clone $TargetOrder;
                    $OriginalOrderDetails = new ArrayCollection();

                    foreach ($TargetOrder->getOrderDetails() as $OrderDetail) {
                        $OriginalOrderDetails->add($OrderDetail);
                    }
*/
                    $builderOrder = $app['form.factory']
                        ->createBuilder('order', $TargetOrder);
/*
                    $event = new EventArgs(
                        array(
                            'builder' => $builderOrder,
                            'OriginOrder' => $OriginOrder,
                            'TargetOrder' => $TargetOrder,
                            'OriginOrderDetails' => $OriginalOrderDetails,
                        ),
                        $app['request']
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_INITIALIZE, $event);
*/
                    $formOrder = $builderOrder->getForm();
                    $formOrder->handleRequest($app['request']);

                    switch ($app['request']->get('mode')) {
                        case 'register':
                            if ($form->isValid() && $formOrder->isValid()) {
                                $Shippings = $TargetOrder->getShippings();

                                $this->em = $app['orm.em'];
//                                $this->em->getConnection()->beginTransaction();

                                foreach ($Shippings as $Shipping) {
                                    $TransportCSVimportB2 = $app['orm.em']->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2')
                                    ->findOneBy(array('order_id' => $order_id, 'shipping_id' => $Shipping->getId()));
                                    if (is_null($TransportCSVimportB2)) {
                                        $TransportCSVimportB2 = new \Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2();
                                    }
                                    $TransportCSVimportB2->setShippingId(Str::trimAll($Shipping->getId()));
                                    $TransportCSVimportB2->setOrderId(Str::trimAll($order_id));
                                    $invoice_number = '';
                                    foreach ($formData as $key => $val) {
                                        if ($key == 'invoice_number'.$Shipping->getId()) {
                                            $invoice_number = $val;
                                        }
                                    }
                                    $TransportCSVimportB2->setInvoiceNumber(Str::trimAll($invoice_number));
                                    $this->em->persist($TransportCSVimportB2);
                                }
                                
//                                $this->em->flush();
//                                $this->em->getConnection()->commit();
                            }
                            break;
                    }
                }
            }
        }
    }

    public function getHtmlInvoiceNumber($request, $response)
    {
        // HTMLを取得し、DOM化
        $crawler = new Crawler($response->getContent());
        $html  = $crawler->html();

        $order_id = $request->get('id');
        $TargetOrder = $this->app['eccube.repository.order']->find($order_id);
        $invoiceNumber = $this->app['orm.em']->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2')->findBy(array('order_id' => $order_id));

        $builder = $this->getBuilder($TargetOrder, $invoiceNumber);
        
        $form = $builder->getForm();
        
        $form->handleRequest($request);

        $formData = array();
        
        if ('POST' === $request->getMethod()) {
            switch ($request->get('mode')) {
                case 'register':
                    $formData = $form->getData();
                    break;
            }
        }

        $Shippings = $TargetOrder->getShippings();
        $arrShippingB2 = array();
        
        foreach ($Shippings as $Shipping) {
            $in = '';
            if ($formData) {
                foreach ($formData as $key => $val) {
                    if ($key == 'invoice_number'.$Shipping->getId()) {
                        $in = $val;
                    }
                }
            } else {
                foreach ($invoiceNumber as $val) {
                    if ($val->getShippingId() == $Shipping->getId()) {
                        $in = $val->getInvoiceNumber();
                    }
                }
            }
            if ($Shipping->getId()) {
                $arrShippingB2[$Shipping->getId()] = array(
                    'id'             => $Shipping->getId(),
                    'name'           => 'invoice_number' . $Shipping->getId(),
                    'sippping_name'  => $Shipping->getName01(). $Shipping->getName02(),
                    'sippping_adrr'  => $Shipping->getAddr01(). $Shipping->getAddr02(),
                    'invoice_number' => $in,
                );
            }
        }

        try {
            $parts = $this->app->renderView(
                'TransportCSVimportB2/View/admin/transport_csv_import_b2_invoice_number.twig',
                array('form' => $form->createView(), 'arrShippingB2' => $arrShippingB2)
            );

            $parts_item_code = $crawler->filter('#shop_info_box')->first();
            $parts_item_code->parents()->first()->getNode(0)->insertBefore(new \DOMText('self::INSERT_STRING'), $parts_item_code->getNode(0));
            $html = str_replace('self::INSERT_STRING', $parts, $crawler->html());
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
        } catch (\InvalidArgumentException $e) {
            // no-op
        }

        return $html;
    }

    public function getBuilder($TargetOrder, $invoiceNumber)
    {
        $app = $this->app;
        $builder = $app['form.factory']
            ->createBuilder('admin_import_b2_nvoice_number');

        $Shippings = $TargetOrder->getShippings();
        
        foreach ($Shippings as $Shipping) {
            if ($Shipping->getId()) {
                $builder->add('invoice_number' . $Shipping->getId(), 'text', array(
                    'label' => 'B2送り状番号',
                    'required' => false,
                ));
            }
        }

        return $builder;
    }

    public function getHtmlMailAll($request, $response)
    {
        // HTMLを取得し、DOM化
        $crawler = new Crawler($response->getContent());
        $html  = $crawler->html();

        try {
            $parts = $this->app->renderView(
                'TransportCSVimportB2/View/admin/transport_csv_import_b2_mail.twig'
            );
            $parts_item_code = $crawler->filter('#dropmenu .dropdown-menu > li');
            $parts_item_code->parents()->first()->getNode(0)->insertBefore(new \DOMText('self::INSERT_STRING'), $parts_item_code->getNode(0));
            $html = str_replace('self::INSERT_STRING', $parts, $crawler->html());
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
        } catch (\InvalidArgumentException $e) {
            // no-op
        }

        return $html;
    }

    public function onControllerAdminOrderMailBefore(FilterResponseEvent $event)
    {
        $app = $this->app;
        $request = $event->getRequest();

        if ('POST' === $app['request']->getMethod()) {
            switch ($request->get('mode')) {
                case 'change':
                    $order_id = $request->attributes->get('id');
                    $TargetOrder = $this->app['eccube.repository.order']->find($order_id);
                    
                    $builder = $app['form.factory']->createBuilder('mail');
                    $form = $builder->getForm();
                    $form->handleRequest($request);
                    $MailTemplate = $form->get('template')->getData();
                    
                    $Shippings = $TargetOrder->getShippings();
                    $arrShippingB2 = array();
                    foreach ($Shippings as $Shipping) {
                        $TransportCSVimportB2 = $app['orm.em']
                            ->getRepository('Plugin\TransportCSVimportB2\Entity\TransportCSVimportB2')
                            ->findOneBy(array('order_id' => $order_id, 'shipping_id' => $Shipping->getId()));
                        if ($TransportCSVimportB2) {
                            $arrShippingB2[] = 'お名前　　：'. $Shipping->getName01(). $Shipping->getName02(). '様';
                            $arrShippingB2[] = '送り状番号：'. $TransportCSVimportB2->getInvoiceNumber();
                        }
                    }
                    
                    if ($arrShippingB2 && $MailTemplate->getName() == 'ヤマトB2発送メール') {
                        $response = $event->getResponse();
                        $shippingInfo = str_replace('送り状番号：', '', $response);
                        $shippingInfo = str_replace('お名前　　：', implode("\n", $arrShippingB2), $shippingInfo);
                        $response->setContent($shippingInfo);
                        $event->setResponse($response);
                    }
                    break;
            }
        }
    }

}
