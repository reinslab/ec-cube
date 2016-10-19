<?php
/*
 * Copyright(c) 2015 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway\Controller\Util;

use Symfony\Component\HttpFoundation\Session\Session;
use Eccube\Util\EntityUtil;

class CommonUtil
{
    public static function isBlank($val, $greedy = true)
    {
        if (is_array($val)) {
            if ($greedy) {
                if (empty($val)) {
                    return true;
                }
                $array_result = true;
                foreach ($val as $in) {
                    /*
                     * Utils_Ex への再帰は無限ループやメモリリークの懸念
                     * 自クラスへ再帰する.
                     */
                    $array_result = CommonUtil::isBlank($in, $greedy);
                    if (!$array_result) {
                        return false;
                    }
                }

                return $array_result;
            } else {
                return empty($val);
            }
        }

        if ($greedy) {
            $val = preg_replace('/　/', '', $val);
        }

        $val = trim($val);
        if (strlen($val) > 0) {
            return false;
        }

        return true;
    }

    /**
     *  INT型の数値チェック
     *  ・FIXME: マイナス値の扱いが不明確
     *  ・XXX: INT_LENには収まるが、INT型の範囲を超えるケースに対応できないのでは?
     *
     * @param mixed $value
     * @return bool
     */
    public static function isInt($value)
    {
        if (strlen($value) >= 1 && strlen($value) <= 9 && is_numeric($value)) {
            return true;
        }

        return false;
    }

    /**
     * ログの出力を行う
     *
     * エラー・警告は trigger_error() を経由して利用すること。(補足の出力は例外。)
     * @param string $message
     * @param string $path
     * @param bool $verbose 冗長な出力を行うか
     */
    public static function printLog($app, $message, $path = '', $verbose = false)
    {
        // 日付の取得
        $today = date('Y/m/d H:i:s');
        // 出力パスの作成
        if (strlen($path) === 0) {
            $path = self::isAdminFunction() ?: $app['config']['log_realfile'];
        }

        if (empty($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }

        $msg = $today . '[{' . $_SERVER['SCRIPT_NAME'] . '}]' . $message;
        $msg .= 'from {' . $_SERVER['REMOTE_ADDR'] . '}\n';
        if ($verbose) {
            if (self::isFrontFunction()) {
                $msg .= 'customer_id = ' . $_SESSION['customer']['customer_id'] . "\n";
            }
            if (self::isAdminFunction()) {
                $msg .= 'login_id = ' . $_SESSION['login_id'] . '(' . $_SESSION['authority'] . ')' . '[' . session_id() . ']' . "\n";
            }
            $msg .= self::toStringBacktrace(self::getDebugBacktrace());
        }

        error_log($msg, 3, $path);

        // ログテーション
        // self::gfLogRotation($app['config']['max_log_quantity'], $app['config']['max_log_size'], $path);
    }

    /**
     * 管理機能かを判定
     *
     * @return bool 管理機能か
     */
    public static function isAdminFunction()
    {
        return defined('ADMIN_FUNCTION') && ADMIN_FUNCTION === true;
    }

    /**
     * インストール機能かを判定
     *
     * @return bool インストール機能か
     */
    public static function isInstallFunction()
    {
        return defined('INSTALL_FUNCTION') && INSTALL_FUNCTION === true;
    }

    /**
     * フロント機能かを判定
     *
     * @return bool フロント機能か
     */
    public static function isFrontFunction()
    {
        return defined('FRONT_FUNCTION') && FRONT_FUNCTION === true;
    }

    /**
     * バックトレースをテキスト形式で出力する
     *
     * 現状スタックトレースの形で出力している。
     * @param  array $arrBacktrace バックトレース
     * @return string テキストで表現したバックトレース
     */
    public static function toStringBacktrace($arrBacktrace)
    {
        $string = '';

        foreach (array_reverse($arrBacktrace) as $backtrace) {
            if (!empty($backtrace['class'])) {
                if (strlen($backtrace['class']) >= 1) {
                    $func = $backtrace['class'] . $backtrace['type'] . $backtrace['function'];
                } else {
                    $func = $backtrace['function'];
                }
            }

            if (!empty($backtrace['file'])) {
                $string .= $backtrace['file'] . '(' . $backtrace['line'] . '): ' . $func . "\n";
            }
        }

        return $string;
    }

    /**
     * デバッグ情報として必要な範囲のバックトレースを取得する
     *
     * エラーハンドリングに関わる情報を切り捨てる。
     */
    public static function getDebugBacktrace($arrBacktrace = null)
    {
        if (is_null($arrBacktrace)) {
            $arrBacktrace = debug_backtrace(false);
        }
        $arrReturn = array();
        foreach (array_reverse($arrBacktrace) as $arrLine) {
            // 言語レベルの致命的エラー時。発生元の情報はトレースできない。(エラーハンドリング処理のみがトレースされる)
            // 実質的に何も返さない(空配列を返す)意図。
            if (!empty($arrLine['file'])) {
                if (strlen($arrLine['file']) === 0
                    && ($arrLine['class'] === 'Helper_HandleError' || $arrLine['class'] === 'Helper_HandleError_Ex')
                    && ($arrLine['function'] === 'handle_error' || $arrLine['function'] === 'handle_warning')
                ) {
                    break 1;
                }
            }

            $arrReturn[] = $arrLine;

            // エラーハンドリング処理に引き渡した以降の情報は通常不要なので含めない。
            if (!isset($arrLine['class']) && $arrLine['function'] === 'trigger_error') {
                break 1;
            }

            if (!empty($arrLine['class'])) {
                if (($arrLine['class'] === 'Helper_HandleError' || $arrLine['class'] === 'Helper_HandleError_Ex')
                    && ($arrLine['function'] === 'handle_error' || $arrLine['function'] === 'handle_warning')
                ) {
                    break 1;
                }
                if (($arrLine['class'] === 'Utils' || $arrLine['class'] === 'Utils_Ex')
                    && $arrLine['function'] === 'sfDispException'
                ) {
                    break 1;
                }
                if (($arrLine['class'] === 'GC_Utils' || $arrLine['class'] === 'GC_Utils_Ex')
                    && ($arrLine['function'] === 'gfDebugLog' || $arrLine['function'] === 'gfPrintLog')
                ) {
                    break 1;
                }
            }
        }

        return array_reverse($arrReturn);
    }

    /**
     * ログローテーション機能
     *
     * XXX この類のローテーションは通常 0 開始だが、本実装は 1 開始である。
     * この中でログ出力は行なわないこと。(無限ループの懸念あり)
     * @param  integer $max_log 最大ファイル数
     * @param  integer $max_size 最大サイズ
     * @param  string $path ファイルパス
     * @return void
     */
    public static function gfLogRotation($max_log, $max_size, $path)
    {
        // ファイルが存在しない場合、終了
        if (!file_exists($path)) return;

        // ファイルが最大サイズを超えていない場合、終了
        if (filesize($path) <= $max_size) return;

        // Windows 版 PHP への対策として明示的に事前削除
        $path_max = "$path.$max_log";
        if (file_exists($path_max)) {
            $res = unlink($path_max);
            // 削除に失敗時した場合、ログローテーションは見送り
            if (!$res) return;
        }

        // アーカイブのインクリメント
        for ($i = $max_log; $i >= 2; $i--) {
            $path_old = "$path." . ($i - 1);
            $path_new = "$path.$i";
            if (file_exists($path_old)) {
                rename($path_old, $path_new);
            }
        }

        // 現在ファイルのアーカイブ
        rename($path, "$path.1");
    }

    /**
     * 二回以上繰り返されているスラッシュ[/]を一つに変換する。
     *
     * @param string $istr
     * @return string
     */
    public static function rmDupSlash($istr)
    {
        if (preg_match('|^http://|', $istr)) {
            $str = substr($istr, 7);
            $head = 'http://';
        } elseif (preg_match('|^https://|', $istr)) {
            $str = substr($istr, 8);
            $head = 'https://';
        } else {
            $str = $istr;
            $head = '';
        }
        $str = preg_replace('|[/]+|', '/', $str);
        $ret = $head . $str;

        return $ret;
    }


    public static function unSerializeData($data)
    {
        $returnData = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($match) {
            return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
        }, $data);

        return unserialize($returnData);
    }

    /**
     * Get full URL root path
     * @return string URL
     */
    public static function getUrl(){
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') 
                    === FALSE ? 'http' : 'https';
        $host     = $_SERVER['HTTP_HOST'];
        $script   = $_SERVER['SCRIPT_NAME'];
        $currentUrl = $protocol . '://' . $host . $script;
        return $currentUrl;
    }
    
    /**
     * Create gmo member id 
     * @param type $customerId
     * @param type $app
     * @param boolean $isNewAlgorithm use new or old algorithm
     * @return boolean/string
     */
    public static function createGmoMemberId($customerId, $app, $isNewAlgorithm = false)
    {
        // Get customer data
        $app['orm.em']->getFilters()->disable("soft_delete");
        $Customer = $app['eccube.repository.customer']->findOneBy(array('id' => $customerId));
        $app['orm.em']->getFilters()->enable("soft_delete");
        // Get create date
        $createDate = $Customer->getCreateDate()->format('YmdHis');
        $raw = $customerId . '_' . $createDate;
        if ($isNewAlgorithm) {
            do {
                $flgStop = false;
                $raw = $raw. '_'. rand();
                $newGmoMemberId = sha1($raw);
                // Check duplicate at database if existed repeat action to create new gmo member id
                $data = $app['eccube.plugin.gmo_pg.repository.gmo_member']->findOneBy(array('new_member_id' => $newGmoMemberId));
                if(!empty($data)){
                   $flgStop = true; 
                }
            } while ($flgStop);
            
            return $newGmoMemberId;
        }
        $GmoMemberId = hash($app['config']['GmoPaymentGateway']['const']['GMO_MEMBER_ID_ENCRYPTION'], $raw);
        return $GmoMemberId;
    }
    
    /**
     * Get gmo member id
     * @param type $customerId
     * @param type $app
     * @return type
     */
    public static function getGmoMemberId($customerId, $app) 
    {
        $Customer = $app['eccube.repository.customer']->findOneBy(array('id' => $customerId));
        if (empty($Customer)) {
            return null;
        }
        $GmoMember = $app['eccube.plugin.gmo_pg.repository.gmo_member']->findOneBy(array('customer_id'=>$customerId, 'customer_create_date'=>$Customer->getCreateDate()));
        if (!is_null($GmoMember) && !is_null($GmoMember->GetNewMemberId())) {
            return $GmoMember->getNewMemberId();
        }
        return null;
    }
    /**
     * Check duplicate gmo member id
     * @param type $gmoMemberID
     * @return boolean true or false: true if existed
     */
    public static function isExistGmoMemberID($gmoMemberID, $app) 
    {
        $GmoMember = $app['eccube.plugin.gmo_pg.repository.gmo_member']->findOneBy(array('new_member_id'=>$gmoMemberID));
        if (!is_null($GmoMember)) {
            return true;
        }
        return false;
    }
}
