<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class XEditor_Helper_Action_Admin extends Typecho_Widget implements Widget_Interface_Do
{
    public $db;
    public $options;
    public $notice;

    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
        $this->db = Typecho_Db::get();
        $this->options = $this->getOptions();
        $this->notice = Typecho_Widget::widget('Widget_Notice');
    }

    /**
     * 获取配置
     *
     * @return Widget_Options
     */
    public function getOptions()
    {
        $values = $this->db->fetchAll($this->db->select('name', 'value')->from('table.options')->where('user = 0'));
        $options = array();
        foreach ($values as $value) {
            if (strpos($value['name'], "plugin:") === 0) {
                continue;
            }

            $options[$value['name']] = $value['value'];
        }
        /** 主题变量重载 */
        if (!empty($options['theme:' . $options['theme']])) {
            $themeOptions = null;

            /** 解析变量 */
            if ($themeOptions = unserialize($options['theme:' . $options['theme']])) {
                /** 覆盖变量 */
                $options = array_merge($options, $themeOptions);
            }
        }
        $options['rootUrl'] = defined('__TYPECHO_ROOT_URL__') ? __TYPECHO_ROOT_URL__ : $this->request->getRequestRoot();
        if (defined('__TYPECHO_ADMIN__')) {
            /** 识别在admin目录中的情况 */
            $adminDir = '/' . trim(defined('__TYPECHO_ADMIN_DIR__') ? __TYPECHO_ADMIN_DIR__ : '/admin/', '/');
            $options['rootUrl'] = substr($options['rootUrl'], 0, -strlen($adminDir));
        }
        if (defined('__TYPECHO_SITE_URL__')) {
            $options['siteUrl'] = __TYPECHO_SITE_URL__;
        } else if (defined('__TYPECHO_DYNAMIC_SITE_URL__') && __TYPECHO_DYNAMIC_SITE_URL__) {
            $options['siteUrl'] = $options['rootUrl'];
        }
        $options['originalSiteUrl'] = $options['siteUrl'];
        $options['siteUrl'] = Typecho_Common::url(null, $options['siteUrl']);

        return Typecho_Config::factory($options);
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
    }
}
