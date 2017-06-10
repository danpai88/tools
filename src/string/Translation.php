<?php
namespace danpai\string;

use danpai\net\Http;

class Translation
{
    const BAIDU_API = BAIDU_API;
    const BAIDU_KEY = BAIDU_KEY;
    const BAIDU_URL = 'http://api.fanyi.baidu.com/api/trans/vip/translate';

    const YOUDAO_API = '';
    const YOUDAO_KEY = '';
    const YOUDAO_URL = 'http://openapi.youdao.com/api';

    const LANG_MAP = [
        'zh' => 'zh-CHS',
        'en' => 'EN',
    ];

    public static function zhToEn($sourceText)
    {
        $toLang = 'en';
        $fromLang = 'zh';
        return static::run($sourceText, $toLang, $fromLang);
    }

    protected static function run($sourceText, $toLang = 'en', $fromLang = 'auto')
    {
        list($imgs, $processedText) = static::deal_data($sourceText);

        $result = static::baidu($processedText, $fromLang, $toLang);

        //百度查询失败，则使用有道再查询一次
        if(!$result){
            $result = static::youdao($processedText, $fromLang, $toLang);
        }

        if($result){
            if($imgs && !empty($imgs[0])){
                foreach ($imgs[0] as $key => $img) {
                    $result = str_replace('@'.$key.'@', $img, $result);
                }
            }
        }
        return $result;
    }

    /**
     * 百度翻译
     * @param $sourceText
     * @param $fromLang
     * @param $toLang
     * @return array|mixed|object
     */
    protected static function baidu($sourceText, $fromLang, $toLang)
    {
        $param = [
            'q'     => $sourceText,
            'from'  => $fromLang,
            'to'    => $toLang,
            'appid' => static::BAIDU_API,
            'salt'  => time(),
        ];

        $param['sign'] = md5($param['appid'].$param['q'].$param['salt'].static::BAIDU_KEY);
        $response = Http::curlPost(static::BAIDU_URL, $param);
        $json = json_decode($response, true);
        if(!$json || (!empty($json['error_code']) && $json['error_code'] != 52000) ){
            static::markLogs($response, 'baidu');
            return false;
        }

        $tmp = '';
        foreach ($json['trans_result'] as $dst) {
            $tmp .= $dst['dst'];
        }
        return $tmp;
    }

    /**
     * 有道翻译
     * @param $sourceText
     * @param $fromLang
     * @param $toLang
     * @return array|mixed|object
     */
    protected static function youdao($sourceText, $fromLang, $toLang)
    {
        $param = [
            'q'      => $sourceText,
            'from'   => $fromLang,
            'to'     => !empty(static::LANG_MAP[$toLang]) ? static::LANG_MAP[$toLang] : $toLang,
            'appKey' => static::YOUDAO_API,
            'salt'   => time(),
        ];

        $param['sign'] = md5($param['appid'].$param['q'].$param['salt'].static::YOUDAO_KEY);
        $response = Http::curlPost(static::YOUDAO_URL, $param);
        $json = json_decode($response, true);

        if(!$json || $json['errorCode']){
            static::markLogs($response, 'youdao');
            return false;
        }
        return $json['translation'];
    }

    protected static function markLogs($msg = '', $type = '')
    {
        $logDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'logs';
        if(!file_exists($logDir)){
            mkdir($logDir, 777, true);
        }
        $file = $logDir.DIRECTORY_SEPARATOR.date('Y-m-d').'.txt';
        $msg = date('Y-m-d H:i:s').' [translation notice] '.$type.' '.$msg."\n\r";
        file_put_contents($file, $msg, FILE_APPEND);
    }

    protected static function deal_data($string)
    {
        $string = trim(preg_replace('/<p>\s*<\/p>/', '', $string));
        $p = '/<img(.*?)>/';
        preg_match_all($p, $string, $matchs);
        if($matchs && !empty($matchs[0])){
            foreach ($matchs[0] as $key => $match) {
                $string = str_replace($match, '@'.$key.'@', $string);
            }
        }
        return [$matchs, $string];
    }
}