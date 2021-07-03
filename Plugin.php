<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 虾米皮皮乐的编辑器 感谢 <a href="https://b3log.org/vditor/" target="_blank">Vditor</a>
 *
 * @package XEditor
 * @author Ryan
 * @version 1.1.1
 * @dependence 14.10.10-*
 * @link https://doufu.ru
 *
 */
class XEditor_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        return XEditor_Util::activate();
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     */
    public static function deactivate()
    {
        XEditor_Util::deactivate();
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // Fix Only Pass by reference
        $_arr = explode("_", __CLASS__);
        $pluginName = array_shift($_arr);
        ?>
        <script src="<?php Helper::options()->pluginUrl("$pluginName/assets/js/config.js") ?>"></script>
        <link rel="stylesheet" href="<?php Helper::options()->pluginUrl("$pluginName/assets/css/config.min.css") ?>">
        <!-- 样式来自 Joe 主题-->
        <div class="x_config">
            <div>
                <div class="x_config__aside">
                    <div class="logo"><?php _e($pluginName); ?></div>
                    <ul class="tabs">
                        <li class="item" data-current="x_notice"><?php _e("最新公告"); ?></li>
                        <li class="item" data-current="x_basic"><?php _e("基本设置"); ?></li>
                        <li class="item" data-current="x_warn"><?php _e("慎重选择"); ?></li>
                    </ul>
                    <form class="backup" action="<?php Helper::options()->index('/action/editor?backup'); ?>"
                          method="<?php echo Typecho_Widget_Helper_Form::POST_METHOD; ?>">
                        <input type="submit" name="operate" value="<?php _e("备份设置"); ?>"/>
                        <input type="submit" name="operate" value="<?php _e("还原备份"); ?>"/>
                        <input type="submit" name="operate" value="<?php _e("删除备份"); ?>"/>
                    </form>
                </div>
            </div>
            <div class="x_config__notice"><?php _e("请求数据中..."); ?></div>
        <?php

        $edit = new Typecho_Widget_Helper_Form_Element_Select('XTagsSelector', array('on' => _t('开启（默认）'), 'off' => _t('关闭')), 'on', _t('标签选择器开关'), _t('说明：关闭后无法在编辑文章时快捷选择标签'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);

        $edit = new Typecho_Widget_Helper_Form_Element_Select('XAutoInsertTag', array('on' => _t('开启'), 'off' => _t('关闭（默认）')), 'off', _t('自动插入标签'), _t('说明：开启后每 10s 自动插入标签'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);

        $edit = new Typecho_Widget_Helper_Form_Element_Select('XInsertALlImages', array('on' => _t('开启（默认）'), 'off' => _t('关闭')), 'on', _t('图片一键插入全部'), _t('说明：开启后可以在附件列表中一键插入所有图片'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);


        $edit = new Typecho_Widget_Helper_Form_Element_Select('XAutoSlugType', array('pinyin' => _t('拼音（默认）'), 'baidu' => _t('百度翻译'), 'none' => _t("关闭")), 'pinyin', _t('SLUG 翻译模式'), _t('说明：英文翻译需要 配置 API KEY'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);

        $edit = new Typecho_Widget_Helper_Form_Element_Text('XAutoSlugBaiduAppId', null, null, _t('SLUG 翻译：百度翻译 AppId'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);

        $edit = new Typecho_Widget_Helper_Form_Element_Text('XAutoSlugBaiduKey', null, null, _t('SLUG 翻译：百度翻译 API KEY'), _t('<a href="https://api.fanyi.baidu.com/api/trans/product/index">获取 API Key</a>'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);

        $edit = new Typecho_Widget_Helper_Form_Element_Select('XShortCodeProcess', array('on' => _t('开启'), 'off' => _t('关闭（默认）')), 'off', _t('前台短代码处理开关'), _t('说明：关闭后需要主题自行渲染短代码'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);

        $edit = new Typecho_Widget_Helper_Form_Element_Select('XLoadFontAwesome', array('on' => _t('开启（默认）'), 'off' => _t('关闭')), 'on', _t('前台载入FontAwesome'), _t('说明：关闭后需要自行载入相关字体图片'));
        $edit->setAttribute('class', 'x_content x_basic');
        $form->addInput($edit);

        $edit = new Typecho_Widget_Helper_Form_Element_Radio('XCleanDatabase', array('clean' => _t('清理'), 'none' => _t('保留')), 'none', _t('禁用插件后是否保留数据'), _t('注意：如果打开了此开关，禁用插件时自动清理插件产生的数据（包括插件选项备份）'));
        $edit->setAttribute('class', 'x_content x_warn');
        $form->addInput($edit);

    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function parseEmoji($text)
    {
        // 表情处理 来自 JOE 主题的 EMOJI
        $text = preg_replace_callback(
            '/\:\:\(\s*(呵呵|哈哈|吐舌|太开心|笑眼|花心|小乖|乖|捂嘴笑|滑稽|你懂的|不高兴|怒|汗|黑线|泪|真棒|喷|惊哭|阴险|鄙视|酷|啊|狂汗|what|疑问|酸爽|呀咩爹|委屈|惊讶|睡觉|笑尿|挖鼻|吐|犀利|小红脸|懒得理|勉强|爱心|心碎|玫瑰|礼物|彩虹|太阳|星星月亮|钱币|茶杯|蛋糕|大拇指|胜利|haha|OK|沙发|手纸|香蕉|便便|药丸|红领巾|蜡烛|音乐|灯泡|开心|钱|咦|呼|冷|生气|弱|吐血|狗头)\s*\)/is',
            function ($match) {
                return '<img class="owo" src="' . Helper::options()->pluginUrl . '/XEditor/assets/images/owo/paopao/' . str_replace('%', '', urlencode($match[1])) . '_2x.png" alt="表情"/>';
            },
            $text
        );
        return preg_replace_callback(
            '/\:\@\(\s*(高兴|小怒|脸红|内伤|装大款|赞一个|害羞|汗|吐血倒地|深思|不高兴|无语|亲亲|口水|尴尬|中指|想一想|哭泣|便便|献花|皱眉|傻笑|狂汗|吐|喷水|看不见|鼓掌|阴暗|长草|献黄瓜|邪恶|期待|得意|吐舌|喷血|无所谓|观察|暗地观察|肿包|中枪|大囧|呲牙|抠鼻|不说话|咽气|欢呼|锁眉|蜡烛|坐等|击掌|惊喜|喜极而泣|抽烟|不出所料|愤怒|无奈|黑线|投降|看热闹|扇耳光|小眼睛|中刀)\s*\)/is',
            function ($match) {
                return '<img class="owo" src="' . Helper::options()->pluginUrl . '/XEditor/assets/images/owo/aru/' . str_replace('%', '', urlencode($match[1])) . '_2x.png" alt="表情"/>';
            },
            $text
        );
    }

    public static function contentEx($text, $archive, $last)
    {
        if ($last) $text = $last;
        return XEditor_Plugin::parseEmoji($text);
    }

    public static function excerptEx($text, $archive, $last)
    {
        if ($last) $text = $last;
        return XEditor_Plugin::parseEmoji($text);
    }
}
