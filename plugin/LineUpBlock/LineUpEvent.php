<?php

namespace Plugin\LineUpBlock;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;



class LineUpEvent
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    //フロント：トップ画面に商品表示
    Public function LineUp(FilterResponseEvent $event)
    {
        $app = $this->app;
        $LineUpList = $app['orm.em']->getRepository('\Eccube\Entity\Product')
            ->findBy(
                array('Status' => 1,
                      'del_flg' => 0),
                array('id' => 'DESC')
            );
        if (count($LineUpList) > 0) {
            $twig = $app->renderView(
                'LineUpBlock/Resource/template/default/lineup.twig',
                array(
                    'LineUpList' => $LineUpList,
                )
            );
        }

        $response = $event->getResponse();
        $html = $response->getContent();
        //書き換え処理開始
        $crawler = new Crawler($html);
        $oldElement = $crawler
                ->filter('.item_lineUp');
        $oldHtml = $oldElement->html();
        $newHtml = $oldHtml.$twig;
        $html = $crawler->html();
        $html = str_replace($oldHtml, $newHtml, $html);
        //書き換え処理終了

        $response->setContent($html);
        $event->setResponse($response);
    }
}
