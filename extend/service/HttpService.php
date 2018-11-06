<?php
/**
 * Created by PhpStorm.
 * User: 张亮亮
 * Date: 2018/10/16
 * Time: 17:07
 */
namespace service;

class HttpService
{
    /**
     * get模拟网络请求
     * @param string $url
     * @param array $query
     * @param array $options
     * @return bool
     */
    public static function get($url, $query = [], $options = [])
    {
        $options['query']   = $query;
        return HttpService::request('get', $url, $options);
    }

    /**
     * post模拟网络请求
     * @param string $url
     * @param array $data
     * @param array $options
     * @return bool
     */
    public static function post($url, $data = [], $options = [])
    {
        $options['data']    = $data;
        return HttpService::request('post', $url, $options);
    }

    /**
     * curl模拟网络请求
     * @param string $method
     * @param string $url
     * @param array $options
     * @return bool
     */
    public static function request($method, $url, $options = [])
    {
        $curl = curl_init();

        //get参数设置
        if (!empty($options['query'])) {
            $url .= stripos($url, '?') !== false ? '&' : '?' . http_build_query($options['query']);
        }

        //post参数设置
        if (strtolower($method) === 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, self::build($options['data']));
        }

        //请求超时设置
        $options['timeout'] = isset($options['timeout']) ? $options['timeout'] : 60;
        curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout']);

        //curl头信息设置
        if (!empty($options['header'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['header']);
        }

        //证书文件设置
        if (!empty($options['ssl_cer']) && file_exists($options['ssl_cer'])) {
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $options['ssl_cer']);
        }
        if (!empty($options['ssl_key']) && file_exists($options['ssl_key'])) {
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $options['ssl_key']);
        }

        //通用设置
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        list($content, $status) = [curl_exec($curl), curl_getinfo($curl), curl_close($curl)];
        return (intval($status['http_code']) === 200) ? $content : false;
    }

    /**
     * post数据过滤处理
     * @param array $data
     * @param bool $needBuild
     * @return array|string
     */
    private static function build($data, $needBuild = true)
    {
        if (!is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $value) {
            if (is_string($value) && class_exists('CURLFile', false) && stripos($value, '@') === 0) {
                if (($filename = realpath(trim($value, '@'))) && file_exists($filename)) {
                    list($needBuild, $data[$key]) = [false, new \CURLFile($filename)];
                }
            }
        }
        return $needBuild ? http_build_query($data) : $data;
    }
}