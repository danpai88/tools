<?php
namespace danpai\net;

class Http
{
	public static function multiGet($urls = [])
	{
		$mh = curl_multi_init();
		$handles = [];
		foreach ($urls as $key => $url) {
			$handles[$key] = curl_init($url);
			curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, true);
			curl_multi_add_handle($mh, $handles[$key]);
		}

		$running = null;
	  	do {
	     	curl_multi_exec($mh, $running);
	   	} while ($running);

	   	$responses = [];
	   	foreach ($handles as $key => $handle) {
	   		$responses[] = curl_multi_remove_handle($mh, $handle);
	   	}

	   	return $responses;
	}

	public static function get()
	{

	}

	public static function curlPost($url, $data){
        $ch = curl_init();   //1.初始化

        curl_setopt($ch, CURLOPT_URL, $url); //2.请求地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');//3.请求方式

        //4.参数如下
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//https
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');//模拟浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Accept-Encoding: gzip, deflate'));//gzip解压内容
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        //5.post方式的时候添加数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);//6.执行

        if (curl_errno($ch)) {//7.如果出错
            return curl_error($ch);
        }
        curl_close($ch);//8.关闭
        return $tmpInfo;
    }
}