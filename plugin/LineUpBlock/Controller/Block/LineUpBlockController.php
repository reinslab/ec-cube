<?php

/*
 * This file is part of the SampleBlock
 *
 * Copyright (C) 2016 kurozumi
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\LineUpBlock\Controller\Block;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class LineUpBlockController
{

    /**
     * LineUpBlock画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        return $app['view']->render('Block/well_lineup_block.twig', array(
            // add parameter...
        ));
    }

}
