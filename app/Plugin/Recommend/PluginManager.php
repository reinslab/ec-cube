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

namespace Plugin\Recommend;

use Eccube\Common\Constant;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;
use Eccube\Util\Cache;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{

    /**
     * @var string コピー元ブロックファイル
     */
    private $originBlock;

    /**
     * @var string ブロック名
     */
    private $blockName = 'おすすめ商品';

    /**
     * @var string ブロックファイル名
     */
    private $blockFileName = 'recommend_product_block';

    public function __construct()
    {
        // コピー元ブロックファイル
        $this->originBlock = __DIR__ . '/Resource/template/Block/' . $this->blockFileName . '.twig';
    }

    public function install($config, $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);

    }

    public function uninstall($config, $app)
    {
        // ブロックの削除
        $this->removeBlock($app);

        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code'], 0);
    }

    public function enable($config, $app)
    {
        // ブロックへ登録
        $this->copyBlock($app);
    }

    public function disable($config, $app)
    {
        // ブロックの削除
        $this->removeBlock($app);
    }

    public function update($config, $app)
    {

    }

    /**
     * ブロックを登録
     *
     * @param $app
     * @throws \Exception
     */
    private function copyBlock($app)
    {

        // ファイルコピー
        $file = new Filesystem();
        // ブロックファイルをコピー
        $file->copy($this->originBlock, $app['config']['block_realdir'] . '/' . $this->blockFileName . '.twig');

        $em = $app['orm.em'];
        $em->getConnection()->beginTransaction();
        try {
            $DeviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_PC);

            /** @var \Eccube\Entity\Block $Block */
            $Block = $app['eccube.repository.block']->findOrCreate(null, $DeviceType);

            // Blockの登録
            $Block->setName($this->blockName);
            $Block->setFileName($this->blockFileName);
            $Block->setDeletableFlg(Constant::DISABLED);
            $Block->setLogicFlg(1);
            $em->persist($Block);
            $em->flush($Block);

            // BlockPositionの登録
            $blockPos = $em->getRepository('Eccube\Entity\BlockPosition')->findOneBy(
                array('page_id' => 1, 'target_id' => PageLayout::TARGET_ID_MAIN_BOTTOM),
                array('block_row' => 'DESC'));

            $BlockPosition = new BlockPosition();

            // ブロックの順序を変更
            if ($blockPos) {
                $blockRow = $blockPos->getBlockRow() + 1;
                $BlockPosition->setBlockRow($blockRow);
            } else {
                // 1番目にセット
                $BlockPosition->setBlockRow(1);
            }

            $PageLayout = $app['eccube.repository.page_layout']->find(1);

            $BlockPosition->setPageLayout($PageLayout);
            $BlockPosition->setPageId($PageLayout->getId());
            $BlockPosition->setTargetId(PageLayout::TARGET_ID_MAIN_BOTTOM);
            $BlockPosition->setBlock($Block);
            $BlockPosition->setBlockId($Block->getId());
            $BlockPosition->setAnywhere(Constant::ENABLED);

            $em->persist($BlockPosition);
            $em->flush($BlockPosition);

            $em->getConnection()->commit();

        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

    }

    /**
     * ブロックを削除
     *
     * @param $app
     * @throws \Exception
     */
    private function removeBlock($app)
    {
        $file = new Filesystem();
        $file->remove($app['config']['block_realdir'] . '/' . $this->blockFileName . '.twig');

        // Blockの取得(file_nameはアプリケーションの仕組み上必ずユニーク)
        /** @var \Eccube\Entity\Block $Block */
        $Block = $app['eccube.repository.block']->findOneBy(array('file_name' => $this->blockFileName));

        if ($Block) {
            $em = $app['orm.em'];
            $em->getConnection()->beginTransaction();

            try {
                // BlockPositionの削除
                $blockPositions = $Block->getBlockPositions();
                /** @var \Eccube\Entity\BlockPosition $BlockPosition */
                foreach ($blockPositions as $BlockPosition) {
                    $Block->removeBlockPosition($BlockPosition);
                    $em->remove($BlockPosition);
                }

                // Blockの削除
                $em->remove($Block);

                $em->flush();
                $em->getConnection()->commit();

            } catch (\Exception $e) {
                $em->getConnection()->rollback();
                throw $e;
            }
        }

        Cache::clear($app, false);

    }
}
