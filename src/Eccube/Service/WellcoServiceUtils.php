<?php
/*
 * Copyright(c) 2016 wellco CO.,LTD. All Rights Reserved.
 * 
 * MS用の改行コード変換フィルター
 * 
 */

namespace Eccube\Service;

define('MS_LINE_FEED_FILTER', 'MSLineFeedFilter by willco');

/**
 * Description of CsvStreamFilter
 *
 * @author Hiroyuki Tsunoya
 */
class MSLineFeedFilter extends \php_user_filter 
{
    public function filter($in, $out, &$consumed, $closing) {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = preg_replace("/\n$/", "", $bucket->data);
            $bucket->data = preg_replace("/\r$/", "", $bucket->data);
            $bucket->data = $bucket->data . "\r\n";
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}
