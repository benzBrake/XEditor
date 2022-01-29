<?php

namespace TypechoPlugin\AAEditor;
use Typecho\Db;
use Typecho\Http\Client;
use Typecho\Plugin\Exception;
use Typecho\Widget;
use TypechoPlugin\AAEditor\Libs\Pinyin;
use Utils\Helper;
use Widget\ActionInterface;

class Action extends Widget implements ActionInterface
{
    private $db;
    private $options;
    private $plugin;

    /**
     * AutoBackup_Action constructor.
     * @param $request
     * @param $response
     * @param null $params
     * @throws Db\Exception|Exception
     */
    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
        $this->db = Db::get();
        $this->options = Helper::options();
        $this->plugin = Helper::options()->plugin('AAEditor');
    }
    /**
     * 这个函数不能少
     */
    public function execute()
    {

    }

    /**
     * slug 转换为英文或拼音
     *
     * @access public
     * @return void
     * @throws Exception
     */
    public function slug($word)
    {
        if (empty($word)) {
            return;
        }
        $type = Util::xPlugin('XAutoSlugType', 'pinyin');
        if ($type !== 'none') {
            $result = call_user_func(array($this, $type), $word);
            $result = preg_replace('/[[:punct:]]/', '', $result);
            $result = str_replace(array('  ', ' '), '-', strtolower(trim($result)));
            $message = array('result' => $result);
        } else {
            $message = array('result' => '', $type => $type);
        }
        $this->response->throwJson($message);
    }
    /**
     * 百度加密
     *
     * @param string $query 请求
     * @param string $appID APPID
     * @param string $salt 加盐
     * @param string $secKey API Token
     * @return string
     */
    public function buildSignForBaidu(string $query, string $appID, string $salt, string $secKey): string
    {
        $str = $appID . $query . $salt . $secKey;
        return md5($str);
    }

    /**
     * 百度翻译
     *
     * @access public
     * @param string $word 待翻译的字符串
     * @return Array|string
     * @throws Exception
     */
    public function baidu(string $word)
    {
        if (empty(Util::xPlugin('XAutoSlugBaiduAppId')) || empty(Util::xPlugin('XAutoSlugBaiduKey'))) {
            return '';
        }
        $data = array('appid' => Util::xPlugin('XAutoSlugBaiduAppId'), 'q' => $word, 'from' => 'zh', 'to' => 'en', 'salt' => rand(10000, 99999));
        $data['sign'] = $this->buildSignForBaidu($word, Util::xPlugin('XAutoSlugBaiduAppId'), $data['salt'], Util::xPlugin('XAutoSlugBaiduKey'));
        $data = http_build_query($data);
        $url = 'https://api.fanyi.baidu.com/api/trans/vip/translate' . '?' . $data;
        $result = $this->curl($url);
        if (isset($result['error_code'])) {
            return $result;
        }
        return $result['trans_result'][0]['dst'];
    }

    /**
     * 发送API请求
     *
     * @access public
     * @param string $url 请求地址
     * @return array
     * @throws Client\Exception
     */
    public function curl(string $url): array
    {
        $client = Client::get();
        $client->setTimeout(50)->send($url);

        if (200 === $client->getResponseStatus()) {
            return \Json::decode($client->getResponseBody(), true);
        }
        return [];
    }

    /**
     * 转换成拼音
     *
     * @access public
     * @param string $word 待转换的字符串
     * @return string
     */
    public function pinyin(string $word): string
    {
        $pinyin = new Pinyin();
        return $pinyin->stringToPinyin($word);
    }


    /**
     * 输出 JSON
     *
     * @param mixed $content
     * @return void
     */
    public function throwJson($content)
    {
        $this->response->throwJson($content);
    }

    /**
     * 输出 JSON 消息
     * @param $msg
     */
    public function throwMsg($msg)
    {
        $baseMsg = array('status' => 200);
        if (!is_array($msg))
            $msg = array('msg' => $msg);
        $this->throwJson(array_merge($baseMsg, $msg));
    }

    public function action()
    {
        $this->widget('Widget_User')->pass('administrator');
        $this->on($this->request->is('slug'))->slug($this->request->filter('strip_tags', 'trim', 'xss')->word);
    }
}
