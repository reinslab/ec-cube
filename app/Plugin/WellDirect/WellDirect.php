<?php

/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\WellDirect;

use Eccube\Util\EntityUtil;
use Eccube\Common\Constant;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class WellDirect {

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * カート情報見積保存ボタン表示
     * @param Event $event
     * @return type
     */
    public function insertCartSaveButton(FilterResponseEvent $event)
    {
        $app = $this->app;

        $request = $event->getRequest();
        $response = $event->getResponse();
        $html = $response->getContent();
        
        $crawler = new Crawler($html);

        
        $oldCrawler = $crawler
            ->filter('div#total_box__user_action_menu')
            ->eq(0);
        $html = $this->getHtml($crawler);
        $oldHtml = '';
        $newHtml = '';

        if (count($oldCrawler) > 0) {
            $oldHtml = $oldCrawler->html();
            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');

            $twig = $app->renderView(
                'WellDirect/Resource/template/default/Cart/cart_save_button.twig'
            );

			if ( strpos($oldHtml, $twig) === false ) {
	            $newHtml = $twig . $oldHtml;
			} else {
				$newHtml = $oldHtml;
			}
        }

        $html = str_replace($oldHtml, $newHtml, $html);
        
        $response->setContent($html);
        $event->setResponse($response);
    }
    

    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
    

    /**
     * 注文履歴詳細に削除ボタン表示
     * @param Event $event
     * @return type
     */
    public function insertDeleteButton(FilterResponseEvent $event)
    {
        $app = $this->app;

        $request = $event->getRequest();
        $response = $event->getResponse();
        $html = $response->getContent();
        
        $order_id = $request->get('id');

        $crawler = new Crawler($html);
        
        $oldCrawler = $crawler
            ->filter('div#shopping_confirm')
            ->eq(0);
        $html = $this->getHtml($crawler);
        $oldHtml = '';
        $newHtml = '';

        if (count($oldCrawler) > 0) {
            $oldHtml = $oldCrawler->html();
            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');

            $twig = $app->renderView(
                'WellDirect/Resource/template/default/Mypage/order_delete_button.twig'
            );
// TODO:適正な方法に修正が必要
            $twig = str_replace('#order_id#', $order_id, $twig);

			if ( strpos($oldHtml, $twig) === false ) {
	            $newHtml = $oldHtml . $twig;
			} else {
				$newHtml = $oldHtml;
			}
        }

        $html = str_replace($oldHtml, $newHtml, $html);
        
        $response->setContent($html);
        $event->setResponse($response);
    }

    /**
     * 利用規約同意チェックボックス表示
     * @param TemplateEvent $event
     * @return type
     */
//    public function onFormInitializeEntry(TemplateEvent $event)
    public function insertAddEntryItemButton(FilterResponseEvent $event)
    {

        $response = $event->getResponse();
        $html = $response->getContent();

        $crawler = new Crawler($html);
        
        $oldCrawler = $crawler
            //->filter('dl#top_box__company_name')
            ->filter('div#top_wrap')
            ->eq(0);


        $html = $this->getHtml($crawler);


        $oldHtml = '';
        $newHtml = '';

        if (count($oldCrawler) > 0) {
            $oldHtml = $oldCrawler->html();
            $oldHtml = html_entity_decode($oldHtml, ENT_NOQUOTES, 'UTF-8');

            $twig = $app->renderView(
                'WellDirect/Resource/template/default/Entry/entry_text_section_name.twig'
            );

			if ( strpos($oldHtml, $twig) === false ) {
	            $newHtml = $twig . $oldHtml;
			} else {
				$newHtml = $oldHtml;
			}
        }

        $html = str_replace($oldHtml, $newHtml, $html);

    }
}
