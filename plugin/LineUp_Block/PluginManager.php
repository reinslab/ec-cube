<?php

/*
 * This file is part of the SampleBlock
 *
 * Copyright (C) 2016 kurozumi
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\LineUpBlock;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Common\Constant;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\PageLayout;
use Eccube\Util\Cache;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    const BLOCKNAME = "ラインナップブロック";

    const BLOCKFILENAME = "lineup_block";

    private $Block;

    public function __construct()
    {
        $this->Block = sprintf("%s/Resource/template/default/Block/%s.twig", __DIR__, self::BLOCKFILENAME);
    }

    /**
     * プラグインインストール時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function install($config, $app)
    {
    }

    /**
     * プラグイン削除時の処理
     *
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        $this->removeBlock($app);
    }

    /**
     * プラグイン有効時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function enable($config, $app)
    {
        $this->copyBlock($app);
    }

    /**
     * プラグイン無効時の処理
     *
     * @param $config
     * @param $app
     */
    public function disable($config, $app)
    {
        $this->removeBlock($app);
    }

    public function update($config, $app)
    {
    }

    /**
     * ブロックファイルをブロックディレクトリにコピーしてDBに登録
     *
     * @param $app
     * @throws \Exception
     */
    private function copyBlock($app)
    {
        $this->app = $app;

        $file = new Filesystem();
        $file->copy($this->Block, sprintf("%s/%s.twig", $app['config']['block_realdir'], self::BLOCKFILENAME));

        $this->app['orm.em']->getConnection()->beginTransaction();
        try {
            // ブロックの登録
            $Block = $this->registerBlock();

            // BlockPositionの登録
            $this->registerBlockPosition($Block);

            $this->app['orm.em']->getConnection()->commit();

        } catch (\Exception $e) {
            $this->app['orm.em']->getConnection()->rollback();
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
        // ブロックファイルを削除
        $file = new Filesystem();
        $file->remove(sprintf("%s/%s.twig", $app['config']['block_realdir'], self::BLOCKFILENAME));

        // Blockの取得(file_nameはアプリケーションの仕組み上必ずユニーク)
        /** @var \Eccube\Entity\Block $Block */
        $Block = $app['eccube.repository.block']->findOneBy(array('file_name' => self::BLOCKFILENAME));
        if ($Block)
        {
            $em = $app['orm.em'];
            $em->getConnection()->beginTransaction();
            try {
                // BlockPositionの削除
                $blockPositions = $Block->getBlockPositions();
                /** @var \Eccube\Entity\BlockPosition $BlockPosition */
                foreach ($blockPositions as $BlockPosition)
                {
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

    /**
     * ブロックの登録
     *
     * @return \Eccube\Entity\Block
     */
    private function registerBlock()
    {
        $DeviceType = $this->app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_PC);
        /** @var \Eccube\Entity\Block $Block */
        $Block = $this->app['eccube.repository.block']->findOrCreate(null, $DeviceType);

        $Block->setName(self::BLOCKNAME);
        $Block->setFileName(self::BLOCKFILENAME);
        $Block->setDeletableFlg(Constant::DISABLED);
        $Block->setLogicFlg(1);
        $this->app['orm.em']->persist($Block);
        $this->app['orm.em']->flush($Block);

        return $Block;
    }

    /**
     * BlockPositionの登録
     *
     * @param $Block
     */
    private function registerBlockPosition($Block)
    {
        $blockPos = $this->app['orm.em']->getRepository('Eccube\Entity\BlockPosition')->findOneBy(
            array('page_id' => 1, 'target_id' => PageLayout::TARGET_ID_UNUSED),
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

        $PageLayout = $this->app['eccube.repository.page_layout']->find(1);
        $BlockPosition->setPageLayout($PageLayout);
        $BlockPosition->setPageId($PageLayout->getId());
        $BlockPosition->setTargetId(PageLayout::TARGET_ID_UNUSED);
        $BlockPosition->setBlock($Block);
        $BlockPosition->setBlockId($Block->getId());
        $BlockPosition->setAnywhere(Constant::ENABLED);
        $this->app['orm.em']->persist($BlockPosition);
        $this->app['orm.em']->flush($BlockPosition);
    }

}
