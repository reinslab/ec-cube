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

namespace Plugin\TransportCSVexportB2;

use Eccube\Common\Constant;
use Eccube\Event\RenderEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;

class TransportCSVexportB2
{

    const INSERT_STRING = '#####CUSTOM_TRADELAW_INSERT#####';
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminOrderBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // B2CSV
        $response->setContent($this->getHtmlDownload($request, $response));
        $event->setResponse($response);

    }

    public function getHtmlDownload($request, $response)
    {
        // HTMLを取得し、DOM化
        $crawler = new Crawler($response->getContent());
        $html  = $crawler->html();

        $form = $this->app['form.factory']
            ->createBuilder('admin_search_order')
            ->getForm();
            
        $form->handleRequest($request);

        try {
            $parts = $this->app->renderView(
                'TransportCSVexportB2/View/admin/transport_csv_export_b2_download.twig',
                array('form' => $form->createView())
            );
            $parts_item_code  = $crawler->filter('#dropmenu')->previousAll()->html();
            $new_html = $parts_item_code . $parts;
            $html = str_replace($parts_item_code, $new_html, $html);
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
        } catch (\InvalidArgumentException $e) {
            // no-op
        }

        return $html;
    }

}
