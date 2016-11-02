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


namespace Plugin\WellDirect\Controller\Admin;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class WellDirectAdminController extends AbstractController
{

    /**
     * PDFダウンロード
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function pdfDownload(Application $app, Request $request, $id)
    {
    
    	$arrOids = array();
    	
    	//ダウンロード対象の受注番号
    	if ( is_numeric($id) ) {
    		$arrOids[] = $id;
    	} else {
    		$namekey = 'ids';
    		$arr_req_keys = array_keys($_REQUEST);
    		foreach($arr_req_keys as $key) {
    			if ( strpos($key, $namekey) > -1 ) {
    				$arrOids[] = str_replace($namekey, '', $key);
    			}
    		}
    	}
        $zip = new \ZipArchive();
        $now = new \DateTime();

        $zip_filename = 'order_uploadfile_' . $now->format('YmdHis') . '.zip';
        $zip_filepath = $app['config']['image_temp_realdir'] . '/' . $zip_filename;
        $result = $zip->open($zip_filepath, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE);
    
    	//PDFダウンロード
    	foreach($arrOids as $oid) {
            $TargetOrder = $app['eccube.repository.order']->find($oid);
            if (is_null($TargetOrder)) {
                throw new NotFoundHttpException();
            }
            
            //PDFファイルが無い場合はスキップする
            if ( $TargetOrder->getPdfFileName() == '' ) {
            	continue;
            }
            
            $custom_order_id = $TargetOrder->getCustomOrderId();
            $zip->addFromString($custom_order_id . '_' . $TargetOrder->getPdfFileName(), file_get_contents($app['config']['image_save_realdir'] . '/' . $TargetOrder->getPdfFileName()));
            
            //受注ステータス更新
            $OrderStatus = $app['eccube.repository.order_status']->find($app['config']['order_data_check_now']);
            $orderRepository = $app['orm.em']->getRepository('Eccube\Entity\Order');
            $orderRepository->changeStatus($oid, $OrderStatus);
    	}
    	
    	//クローズ
    	$zip->close();

        return $app
            ->sendFile($zip_filepath)
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zip_filename);


    }

}
