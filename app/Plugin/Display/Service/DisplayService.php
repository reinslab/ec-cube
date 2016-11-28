<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Plugin\Display\Service;

use Eccube\Application;
use Eccube\Common\Constant;

class DisplayService
{
    /** @var \Eccube\Application */
    public $app;

    /** @var \Eccube\Entity\BaseInfo */
    public $BaseInfo;

    /**
     * コンストラクタ
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->BaseInfo = $app['eccube.repository.base_info']->get();
    }

    /**
     * 商品展開情報を新規登録する
     * @param $data
     * @return bool
     */
    public function createDisplay($data) {
        // 商品展開詳細情報を生成する
        $Display = $this->newDisplay($data);

        $em = $this->app['orm.em'];

        // 商品展開情報を登録する
        $em->persist($Display);

        $em->flush();

        return true;
    }

    /**
     * 商品展開情報を更新する
     * @param $data
     * @return bool
     */
    public function updateDisplay($data) {
        $dateTime = new \DateTime();
        $em = $this->app['orm.em'];

        // 商品展開情報を取得する
        $Display =$this->app['eccube.plugin.display.repository.display_product']->find($data['id']);
        if(is_null($Display)) {
            false;
        }

        // 商品展開情報を書き換える
        $Display->setComment($data['comment']);
        $Display->setProduct($data['Product']);
        $Display->setUpdateDate($dateTime);

        // 商品展開情報を更新する
        $em->persist($Display);

        $em->flush();

        return true;
    }

    /**
     * 商品展開情報を削除する
     * @param $displayId
     * @return bool
     */
    public function deleteDisplay($displayId) {
        $currentDateTime = new \DateTime();
        $em = $this->app['orm.em'];

        // 商品展開情報を取得する
        $Display =$this->app['eccube.plugin.display.repository.display_product']->find($displayId);
        if(is_null($Display)) {
            false;
        }
        // 商品展開情報を書き換える
        $Display->setDelFlg(Constant::ENABLED);
        $Display->setUpdateDate($currentDateTime);

        // 商品展開情報を登録する
        $em->persist($Display);

        $em->flush();

        return true;
    }

    /**
     * 商品展開情報の順位を上げる
     * @param $displayId
     * @return bool
     */
    public function rankUp($displayId) {
        $currentDateTime = new \DateTime();
        $em = $this->app['orm.em'];

        // 商品展開情報を取得する
        $Display =$this->app['eccube.plugin.display.repository.display_product']->find($displayId);
        if(is_null($Display)) {
            false;
        }
        // 対象ランクの上に位置する商品展開を取得する
        $TargetDisplay =$this->app['eccube.plugin.display.repository.display_product']
                                ->findByRankUp($Display->getRank());
        if(is_null($TargetDisplay)) {
            false;
        }
        
        // ランクを入れ替える
        $rank = $TargetDisplay->getRank();
        $TargetDisplay->setRank($Display->getRank());
        $Display->setRank($rank);
        
        // 更新日設定
        $Display->setUpdateDate($currentDateTime);
        $TargetDisplay->setUpdateDate($currentDateTime);
        
        // 更新
        $em->persist($Display);
        $em->persist($TargetDisplay);

        $em->flush();

        return true;
    }

    /**
     * 商品展開情報の順位を下げる
     * @param $displayId
     * @return bool
     */
    public function rankDown($displayId) {
        $currentDateTime = new \DateTime();
        $em = $this->app['orm.em'];

        // 商品展開情報を取得する
        $Display =$this->app['eccube.plugin.display.repository.display_product']->find($displayId);
        if(is_null($Display)) {
            false;
        }
        // 対象ランクの上に位置する商品展開を取得する
        $TargetDisplay =$this->app['eccube.plugin.display.repository.display_product']
                                ->findByRankDown($Display->getRank());
        if(is_null($TargetDisplay)) {
            false;
        }
        
        // ランクを入れ替える
        $rank = $TargetDisplay->getRank();
        $TargetDisplay->setRank($Display->getRank());
        $Display->setRank($rank);
        
        // 更新日設定
        $Display->setUpdateDate($currentDateTime);
        $TargetDisplay->setUpdateDate($currentDateTime);
        
        // 更新
        $em->persist($Display);
        $em->persist($TargetDisplay);

        $em->flush();

        return true;
    }

    /**
     * 商品展開情報を生成する
     * @param $data
     * @return \Plugin\Display\Entity\DisplayProduct
     */
    protected function newDisplay($data) {
        $dateTime = new \DateTime();

        $rank = $this->app['eccube.plugin.display.repository.display_product']->getMaxRank();

        $Display = new \Plugin\Display\Entity\DisplayProduct();
        $Display->setComment($data['comment']);
        $Display->setProduct($data['Product']);
        $Display->setRank(($rank ? $rank : 0) + 1);
        $Display->setDelFlg(Constant::DISABLED);
        $Display->setCreateDate($dateTime);
        $Display->setUpdateDate($dateTime);

        return $Display;
    }

}
