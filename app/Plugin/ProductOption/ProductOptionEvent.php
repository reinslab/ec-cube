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

namespace Plugin\ProductOption;

use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;
use Plugin\ProductOption\Event\WorkPlace\AdminProduct;
use Plugin\ProductOption\Event\WorkPlace\AdminOrderEdit;
use Plugin\ProductOption\Event\WorkPlace\AdminOrderEditSearchProduct;
use Plugin\ProductOption\Event\WorkPlace\AdminOrderMail;
use Plugin\ProductOption\Event\WorkPlace\AdminOrderMailAll;
use Plugin\ProductOption\Event\WorkPlace\FrontProductDetail;
use Plugin\ProductOption\Event\WorkPlace\FrontCart;
use Plugin\ProductOption\Event\WorkPlace\FrontShopping;
use Plugin\ProductOption\Event\WorkPlace\FrontShoppingMultiple;
use Plugin\ProductOption\Event\WorkPlace\FrontShoppingComplete;
use Plugin\ProductOption\Event\WorkPlace\FrontMypage;
use Plugin\ProductOption\Event\WorkPlace\FrontMypageHistory;
use Plugin\ProductOption\Event\WorkPlace\ServiceExportOrder;
use Plugin\ProductOption\Event\WorkPlace\ServiceExportShipping;
use Plugin\ProductOption\Event\WorkPlace\ServiceMail;
use Plugin\ProductOption\Event\WorkPlace\ServiceAdminMail;
use Plugin\ProductOption\Event\WorkPlace\FrontCartPoint;
use Plugin\ProductOption\Event\WorkPlace\FrontShoppingPoint;
use Plugin\ProductOption\Event\WorkPlace\FrontShoppingCompletePoint;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductOptionEvent
{
        
    public function onRenderProductDetail(TemplateEvent $event)
    {
        $helper = new FrontProductDetail();
        $helper->createTwig($event);
    }
    
    public function addCart(EventArgs $event)
    {   
        $helper = new FrontProductDetail();
        $helper->execute($event);
    }

    public function onRenderCart(TemplateEvent $event)
    {
        $helper = new FrontCart();
        $helper->createTwig($event);
    }
    
    public function onRenderCartPoint(TemplateEvent $event)
    {
        $helper = new FrontCartPoint();
        $helper->createTwig($event);
    }
    
    public function onRenderShopping(TemplateEvent $event)
    {
        $helper = new FrontShopping();
        $helper->createTwig($event);
    }
    
    public function onRenderShoppingPoint(TemplateEvent $event)
    {
        $helper = new FrontShoppingPoint();
        $helper->createTwig($event);
    }
    
    public function onRenderShoppingMultiple(TemplateEvent $event)
    {
        $helper = new FrontShoppingMultiple();
        $helper->createTwig($event);
    }
    
    public function onRenderMypage(TemplateEvent $event)
    {
        $helper = new FrontMypage();
        $helper->createTwig($event);
    }
    
    public function onRenderMypageHistory(TemplateEvent $event)
    {
        $helper = new FrontMypageHistory();
        $helper->createTwig($event);
    }
    
    public function onRenderAdminProduct(TemplateEvent $event)
    {
        $helper = new AdminProduct();
        $helper->createTwig($event);
    }
    
    public function onRenderAdminOrderEdit(TemplateEvent $event)
    {
        $helper = new AdminOrderEdit();
        $helper->createTwig($event);
    }
    
    public function changePrice(FilterResponseEvent $event)
    {
        $helper = new AdminOrderEdit();
        $helper->render($event);
    }
    
    public function onRenderAdminOrderSearchProduct(TemplateEvent $event)
    {
        $helper = new AdminOrderEditSearchProduct();
        $helper->createTwig($event);
    }
    
    public function onRenderAdminOrderMailConfirm(TemplateEvent $event)
    {
        $helper = new AdminOrderMail();
        $helper->createTwig($event);
    }
    
    public function onRenderAdminOrderMailAllConfirm(TemplateEvent $event)
    {
        $helper = new AdminOrderMailAll();
        $helper->createTwig($event);
    }
    
    public function customOrder(EventArgs $event)
    {
        $helper = new FrontShopping();
        $helper->execute($event);
    }
    
    public function mypageOrder(EventArgs $event)
    {
        $helper= new FrontMypageHistory();
        $helper->execute($event);
    }
    
    public function multipleShippingEdit(EventArgs $event)
    {
        $helper = new FrontShoppingMultiple();
        $helper->execute($event);
    }
    
    public function completeShopping(EventArgs $event)
    {    
        $helper = new FrontShoppingComplete();
        $helper->execute($event);
    }
    
    public function completeShoppingPoint(EventArgs $event)
    {    
        $helper = new FrontShoppingCompletePoint();
        $helper->save($event);
    }
    
    public function onServiceShoppingNotifyComplete(EventArgs $event)
    {    
        $helper = new FrontShoppingCompletePoint();
        $helper->save($event);
    }
    
    public function registOrder(EventArgs $event)
    {
        $helper = new AdminOrderEdit();
        $helper->save($event);
    }
    
    public function onSendOrderMail(EventArgs $event)
    {
        $helper = new ServiceMail();
        $helper->execute($event);
    }
    
    public function onSendAdminOrderMail($event)
    {
        $helper = new ServiceAdminMail();
        $helper->execute($event);
    }
    
    public function completeSendOrderMail(EventArgs $event)
    {
        $helper = new FrontShoppingComplete();
        $helper->save($event);
    }
    
    public function copmleteSendAdminOrderMail(EventArgs $event)
    {
        $helper = new AdminOrderMail();
        $helper->save($event);
    }
    
    public function exportOrder()
    {
        $helper = new ServiceExportOrder();
        $helper->execute();
    }
    
    public function exportShipping()
    {
        $helper = new ServiceExportShipping();
        $helper->execute();
    }    
}
