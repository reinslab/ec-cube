<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Util;

use Eccube\Application;

class ErrorUtil
{
    public $error = null;
    private $const = null;

    function __construct(Application $app)
    {
        $this->const = $app['config']['GmoPaymentGateway']['const'];
        $this->_loadErrors(__DIR__ . '/../../' . $this->const['PG_MULPAY_ERROR_CODE_MSG_FILE']);
    }

    function lfGetErrorInformation($code)
    {
        if (!$code) return false;
        if (!$this->error) return false;
        if (!isset($this->error[$code])) return false;
        return $this->error[$code];
    }

    function _loadErrors($filename)
    {
        if ($this->error) return;
        $this->error = $this->_getErrors($filename);
        if (!$this->error) echo $pdf_filename . ' かデータの作成が行えませんでした。';
    }

    function _getErrors($filename)
    {
        $error = array();

        $text = file_get_contents($filename);
        $arrText = explode("\n", $text);
        foreach ($arrText as $line) {
            $arrLine = explode("\t", $line);
            $struct = $this->_setStruct($arrLine);
            $code = $struct['code'];
            $error[$code] = $struct;
        }
        return $error;
    }

    function _setStruct($arrLine = null)
    {
        $array = array();
        $array['code'] = (isset($arrLine[0])) ? $arrLine[0] : "";
        $array['no'] = (isset($arrLine[1])) ? $arrLine[1] : "";
        $array['s_code'] = (isset($arrLine[2])) ? $arrLine[2] : "";
        $array['d_code'] = (isset($arrLine[3])) ? $arrLine[3] : "";
        $array['status'] = (isset($arrLine[4])) ? $arrLine[4] : "";
        $array['payment'] = (isset($arrLine[5])) ? $arrLine[5] : "";
        $array['context'] = (isset($arrLine[6])) ? $arrLine[6] : "";
        $array['message'] = (isset($arrLine[7])) ? $arrLine[7] : "";
        return $array;
    }
}
