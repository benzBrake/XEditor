<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * XEditor Plugin
 *
 * @copyright  Copyright (c) 2021 虾米皮皮乐 (https://doufu.ru)
 * @license    GNU General Public License 2.0
 *
 */
class XEditor_Action extends XEditor_Helper_Action_Admin
{
    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
    }

    /**
     * 插件数据备份接口
     * @param string $operate 操作
     * @throws Typecho_Db_Exception
     */
    public function backup($operate)
    {
        $response = $this->response;
        $db = $this->db;
        $notice = $this->notice;
        $array = explode("_", __CLASS__);
        $plugin = array_shift($array);
        // 查询配置数据
        $pluginDataRow = $db->fetchRow($db->select()->from('table.options')->where('name = ?', "plugin:${plugin}"));
        $pluginDataBackupRow = $db->fetchRow($db->select()->from('table.options')->where('name = ?', "plugin:${plugin}Backup"));
        $pluginData = empty($pluginDataRow) ? null : $pluginDataRow['value'];
        $pluginDataBackup = empty($pluginDataBackupRow) ? null : $pluginDataBackupRow['value'];
        if ($operate === _t("备份设置")) {
            if ($db->fetchRow($db->select()->from('table.options')->where('name = ?', "plugin:${plugin}Backup"))) {
                $updateQuery = $db->update('table.options')->rows(array('value' => $pluginData))->where('name = ?', "plugin:${plugin}Backup");
                $db->query($updateQuery);
                $notice->set('备份已更新!', 'success');
                $response->goBack();
            } else {
                if ($pluginData) {
                    $insertQuery = $db->insert('table.options')->rows(array('name' => "plugin:${plugin}Backup", 'user' => '0', 'value' => $pluginData));
                    $db->query($insertQuery);
                    $notice->set(_t('备份完成!'), 'success');
                    $response->goBack();
                }
            }
        } elseif ($operate === _t("还原备份")) {
            if ($pluginDataBackup) {
                $updateQuery = $db->update('table.options')->rows(array('value' => $pluginDataBackup))->where('name = ?', "plugin:${plugin}");
                $db->query($updateQuery);
                $notice->set(_t('检测到插件备份数据，恢复完成'), 'success');
            } else {
                $notice->set(_t('没有插件备份数据，恢复不了哦！'), 'error');
            }
            $response->goBack();
        } elseif ($operate === _t("删除备份")) {
            if ($pluginDataBackup) {
                $deleteQuery = $db->delete('table.options')->where('name = ?', "plugin:${plugin}Backup");
                $db->query($deleteQuery);
                $notice->set(_t('删除成功！！！'), 'success');
            } else {
                $notice->set(_t('不用删了！备份不存在！！！'), 'error');
            }
            $response->goBack();
        }
    }

    /**
     * SLUG 翻译接口
     *
     * @param mixed $word
     * @return void
     */
    public function slug($word)
    {
        $this->transform($word);
    }

    /**
     * 转换为英文或拼音
     *
     * @access public
     * @return void
     */
    public function transform($word)
    {
        if (empty($word)) {
            return;
        }
        $type = XEditor_Util::xPlugin('XAutoSlugType', 'pinyin');
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
    public function buildSignForBaidu($query, $appID, $salt, $secKey)
    {
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }

    /**
     * 百度翻译
     *
     * @access public
     * @param string $word 待翻译的字符串
     * @return Array|string
     */
    public function baidu($word)
    {
        $appid = XEditor_Util::xPlugin('XAutoSlugBaiduAppId');
        $key = XEditor_Util::xPlugin('XAutoSlugBaiduKey');
        if (empty($key) || empty($appid)) {
            return '';
        }
        $data = array('appid' => $appid, 'q' => $word, 'from' => 'zh', 'to' => 'en', 'salt' => rand(10000, 99999));
        $data['sign'] = $this->buildSignForBaidu($word, $appid, $data['salt'], $key);
        $data = http_build_query($data);
        $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate' . '?' . $data;
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
     */
    public function curl($url)
    {
        $client = Typecho_Http_Client::get();
        $client->setTimeout(50)->send($url);

        if (200 === $client->getResponseStatus()) {
            return Json::decode($client->getResponseBody(), true);
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
    public function pinyin($word)
    {
        $pinyin = new XEditor_Libs_Pinyin();
        return $pinyin->stringToPinyin($word);
    }

    /**
     * 目录修改
     *
     * @return void
     */
    public function changeCategory($posts, $category)
    {
        if (empty($posts)) {
            $this->throwJson(['type' => 'error', 'msg' => _t('大佬，至少选择一篇文章！')]);
        } else if (empty($category)) {
            $this->throwJson(['code' => -1, 'msg' => _t('大佬，请选择一个分类！')]);
        } else {
            $posts = implode(',', $posts);
            $selectCids = 'SELECT cid FROM ' . $this->db->getPrefix() . 'contents where cid in(' . $posts . ') and type="post"';
            $rows = $this->db->fetchAll($selectCids);
            if (count($rows) === 0) {
                $this->throwJson(['type' => 'error', 'msg' => _t('f**k,别特么瞎jb搞！')]);
            }
            $cids = array_column($rows, 'cid');
            $cids = implode(",", $cids);
            $select = $this->db->select('table.metas.mid, table.metas.type')->from('table.metas')->where('table.metas.type = ?', 'category');
            $rows = $this->db->fetchAll($select);
            $mids = array_column($rows, 'mid');
            $mids = implode(",", $mids);
            $update = $this->db->update($this->db->getPrefix() . 'relationships')->rows(array('mid' => $category))->where('cid in (' . $cids . ') AND mid IN (' . $mids . ')');
            $row = @$this->db->query($update);
            if ($row) {
                $this->throwJson(['type' => 'success', 'msg' => _t('本次成功更新 %s 篇文章！', $row)]);
            } else {
                $this->throwJson(['type' => 'error', 'msg' => _t('更新失败')]);
            }
        }
    }

    /**
     * 查询文章信息
     * @param int $cid 文章 ID
     */
    public function queryPost($cid)
    {
        $widget = Helper::widgetById('contents', $cid);
        $this->throwMsg(array('title' => $widget->title, 'permalink' => $widget->permalink, 'content' => XEditor_Util::subStr($widget->content), 'thumb' => XEditor_Util::xThumbs($widget, 1, true)));
    }

    /**
     * 接口路由
     *
     * @return void
     */
    public function action()
    {
        parent::action();
        $this->on($this->request->is('slug'))->slug($this->request->filter('strip_tags', 'trim', 'xss')->word);
        $this->on($this->request->is('backup'))->backup($this->request->filter('strip_tags', 'trim', 'xss')->operate);
        $this->on($this->request->is('query-post'))->queryPost($this->request->filter('int')->get('cid'));
        $this->on($this->request->is('change-category'))->changeCategory($this->request->filter('int')->getArray('cid'), $this->request->filter('int')->get('category'));
    }
}
