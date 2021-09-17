<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class XEditor_Util
{
    /**
     * 激活插件
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function activate(): string
    {
        if (false == Typecho_Http_Client::get()) {
            throw new Typecho_Plugin_Exception(_t('对不起, 您的主机不支持 php-curl 扩展而且没有打开 allow_url_fopen 功能, 无法正常使用此功能'));
        }
        // 添加公共内容
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'archiveFooter');
        // 添加文章编辑选项

        Typecho_Plugin::factory('admin/write-post.php')->option = array(__CLASS__, 'addPostOption');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array(__CLASS__, 'addFooter');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array(__CLASS__, 'addFooter');
        // 修改编辑器
        Typecho_Plugin::factory('admin/write-post.php')->richEditor = array(__CLASS__, 'richEditor');
        Typecho_Plugin::factory('admin/write-page.php')->richEditor = array(__CLASS__, 'richEditor');
        // 短代码
        Typecho_Plugin::factory('admin/common.php')->begin = [__Class__, 'shortCodeInit'];
        Typecho_Plugin::factory('Widget_Archive')->handleInit = [__Class__, 'shortCodeInit'];
        // 内容替换处理
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = ['XEditor_Plugin', 'contentEx'];
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = ['XEditor_Plugin', 'excerptEx'];
        // 路由
        Helper::addAction('editor', 'XEditor_Action');
        return _t("插件启用成功");
    }

    /**
     * 禁用插件
     *
     * @return mixed
     */
    public static function deactivate()
    {
        $db = Typecho_DB::get();
        // 清理数据库
        Helper::removeAction('editor');
        if (XEditor_Util::xPlugin('XCleanDatabase', 'none') === 'clean') {
            $db->delete('table.options')->where('name = ?', "plugin:XEditorBackup");
            return _t("数据清理成功，插件已禁用！");
        } else {
            $db->query($db->update('table.options')->rows(array('name' => '_plugin:XEditor_backup'))->where('name = ?', '_plugin:XEditor'));
            return _t("插件已禁用，但数据未清理。");
        }
    }

    /**
     * 初始化短代码
     *
     * @return void
     */
    public static function shortCodeInit()
    {
        if (!class_exists('ShortCode')) {
            require_once 'Libs/ShortCode.php';
        }
        if (XEditor_Util::xPlugin('XEnableShortCodeParse', 1) == 1) {
            $toolbarStr = file_get_contents(dirname(__FILE__) . '/assets/json/replacement.json');
            $toolbarJson = json_decode($toolbarStr, true);
            foreach ($toolbarJson as $key => $value) {
                if (!array_key_exists($key, ShortCode::$ShortCodeReplacement)) {
                    ShortCode::$ShortCodeReplacement[$key] = $value;
                }
                ShortCode::set($key, function ($name, $attr, $text, $code) {
                    if (array_key_exists($name, ShortCode::$ShortCodeReplacement)) {
                        $html = str_replace(array('{name}', '{attr}', '{text}', '{code}'), array($name, $attr, $text, $code), ShortCode::$ShortCodeReplacement[$name]);
                    } else {
                        $html = `<div class="shortcode shortcode-${name}" $attr>${text}</div>`;
                    }
                    return str_replace(array('{pluginUrl}'), array(Helper::options()->pluginUrl), $html);
                });
            }

            ShortCode::set(['hide', 'post'], function ($name, $attr, $text, $code) {
                $db = Typecho_Db::get();
                $archive = Typecho_Widget::widget('Widget_Archive');
                switch ($name) {
                    case 'hide':
                        $user = Typecho_Widget::widget('Widget_User');
                        $mail = $user->hasLogin() ? $user->mail : $archive->remember('mail', true);
                        $select = $db->select()->from('table.comments')
                            ->where('cid = ?', $archive->cid)
                            ->where('mail = ?', $mail)
                            ->where('status = ?', 'approved')
                            ->limit(1);

                        $result = $db->fetchAll($select);
                        if ($user->pass('administrator', true) || $result) {
                            return '<div class="shortcode shortcode-hide show" ' . $attr . '>' . $text . '</div>';
                        } else {
                            return '<div class="shortcode shortcode-hide hidden">此处内容已隐藏，<a href="#comments">回复后(需要填写邮箱)</a>可见</div>';
                        }
                    case 'post':
                        $widget = XEditor_Util::widgetById('contents', $text);
                        $abstract = XEditor_Util::subStr($widget->excerpt, 120);
                        $thumb = XEditor_Util::xThumbs($widget, 1, true);
                        $template = '<div class="shortcode shortcode-post"><div class="text-content"><div class="title"><a href="{permalink}">{title}</a></div><div class="content">' . $abstract . '</div></div><div class="media-content"><a href="{permalink}" title="{title}"><img alt="{title}" src="' . $thumb . '"/></a></div></div>';
                        return XEditor_Util::xParse($widget, $template);
                }
                return $code;
            });
        }
        if (function_exists("themeShortCode")) {
            call_user_func('themeShortCode');
        }
    }


    public static function archiveFooter()
    {
        if (self::xPlugin('XShortCodeProcess', 1)) { ?>
            <link rel="stylesheet"
                  href="<?php echo Typecho_Common::url('XEditor/assets/css/x.theme.min.css', Helper::options()->pluginUrl); ?>">
            <script>
                XConf = {
                    'options': {
                        'pluginUrl': '<?php Helper::options()->pluginUrl("XEditor/") ?>',
                        'XPlayerUrl': '<?php Helper::options()->pluginUrl("XEditor/Libs/Player.php?url=") ?>',
                        'XActions': {
                            'query': {
                                'post': '<?php Helper::options()->index('/action/editor?query-post'); ?>'
                            }
                        }
                    }
                };
            </script>
            <script src="<?php echo XEditor_Util::pluginUrl('/assets/js/x.short.min.js?v=202104200955'); ?>"></script>
            <?php
        }
        if (self::xPlugin('XLoadFontAwesome', 'on') === 'on') {
            echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">';
        }
    }

    /**
     * 增加撰写文章选项
     * @param $post
     * @throws Typecho_Exception
     * @throws ReflectionException
     */
    public static function addPostOption($post)
    {
        if (XEditor_Util::xPlugin('XTagsSelector', 'on') === 'on') {
            // 标签选择器
            echo '<div id="tags-selector" style="min-height: 32px;max-height: 15em;overflow: auto;border: 1px solid #d9d9d6; background-color: #FFF;">';
            echo '<div class="auto-insert x-btn x-btn-success full-line" onclick="window.XEditor._autoInsertTag();">' . _t("自动插入标签") . '</div>';
            /* @var Widget_Metas_Tag_Cloud $tags */
            Typecho_Widget::widget('Widget_Metas_Tag_Cloud')->to($tags);
            $stack = self::reflectGetValue($tags, 'stack');
            $i = 0;
            while (isset($stack[$i])) {
                echo '<span data-tag="', $stack[$i]['name'], '">', $stack[$i]['name'], '</span>';
                $i++;
            }
            echo "</div>";
        }
    }

    /**
     * 增加尾部代码
     */
    public static function addFooter()
    {
        ?>
        <link rel="stylesheet" href="<?php Helper::options()->pluginUrl("XEditor/assets/css/config.min.css"); ?>">
    <?php }

    public
    static function richEditor()
    {
        $options = Helper::options();
        ?>
        <link rel="stylesheet" href="<?php echo XEditor_Util::pluginUrl('/assets/css/font-awesome.min.css?v=4.7'); ?>">
        <link rel="stylesheet"
              href="<?php echo XEditor_Util::pluginUrl('/assets/css/x.theme.min.css?v=202104250955'); ?>">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vditor@3.8.4/dist/index.css"/>
        <script>
            window.XConf = {
                'options': {
                    'pluginUrl': '<?php $options->pluginUrl("XEditor/") ?>',
                    'siteUrl': '<?php $options->siteUrl(); ?>',
                    'XTagsSelector': <?php echo XEditor_Util::xPlugin('XTagsSelector', 'on') === 'on' ? 'true' : 'false'; ?>,
                    'XAutoInsertTag': <?php echo XEditor_Util::xPlugin('XAutoInsertTag', 'on') === 'on' ? 'true' : 'false'; ?>,
                    'XAutoSlug': {
                        'type': '<?php echo XEditor_Util::xPlugin('XAutoSlugType', 'pinyin'); ?>',
                        'apiUrl': '<?php $options->index('/action/editor?slug&word='); ?>'
                    },
                    'XDefaultCover': '<?php $options->pluginUrl('/XEditor/assets/images/thumbs/' . mt_rand(1, 42) . '.jpg'); ?>',
                    'XUploadApi': '<?php Helper::security()->getToken('/action/upload'); ?>',
                    'XPlayerUrl': '<?php $options->pluginUrl("XEditor/Libs/Player.php?url=") ?>',
                    'XInsertALlImages': '<?php echo XEditor_Util::xPlugin('XInsertALlImages', 'on') === 'on' ? 'true' : 'false'; ?>',
                    'XActions': {
                        'query': {
                            'post': '<?php $options->index('/action/editor?query-post'); ?>'
                        }
                    }
                },
                'i18n': {
                    'ok': '<?php _e("确定"); ?>',
                    'cancel': '<?php _e("取消"); ?>',
                    'toolbar': '<?php _e("工具栏"); ?>',
                    'markdownDisabled': '<?php _e('本文Markdown解析已禁用！'); ?>',
                    'insertAllImages': '<?php _e("插入所有图片"); ?>',
                    'required': '<?php _e("必须填写"); ?>',
                    'clickToDownload': '<?php _e("【点击下载 [{file}]】"); ?>',
                    'button': '<?php _e("按钮"); ?>',
                    'XCard': {
                        'title': '<?php _e("卡片标题"); ?>',
                        'content': '<?php _e("卡片内容"); ?>'
                    },
                    'XMarkdown': {
                        'enable': '<?php _e('启用'); ?>',
                        'disable': '<?php _e('禁用'); ?>',
                        'keepEnabled': '<?php _e('保持启用'); ?>',
                        'keepDisabled': '<?php _e("保持禁用"); ?>',
                        'enabled': '<?php _e('本文Markdown解析已启用！'); ?>',
                        "disabled": '<?php _e('本文Markdown解析已禁用！'); ?>'
                    },
                    'InsertAllImages': '<?php _e("插入所有图片附件"); ?>'
                }
            };
        </script>
        <script src="https://cdn.jsdelivr.net/npm/vditor@3.8.4/dist/index.min.js"></script>
        <script src="<?php echo XEditor_Util::pluginUrl('/assets/js/jquery-resizeEnd.min.js'); ?>"></script>
        <script src="<?php echo XEditor_Util::pluginUrl('/assets/js/x.preview.min.js?v=202104271604'); ?>"></script>
        <script src="<?php echo XEditor_Util::pluginUrl('/assets/js/x.toolbar.min.js?v=202104271604'); ?>"></script>
        <script src="<?php echo XEditor_Util::pluginUrl('/assets/js/x.editor.min.js?v=202104271604'); ?>"></script>
        <script src="<?php echo XEditor_Util::pluginUrl('/assets/js/x.short.min.js?v=202104271604'); ?>"></script>
        <?php
    }

    /**
     * 获取插件配置
     *
     * @param String $key 关键字
     * @param mixed $default 默认值
     * @return mixed
     */
    public
    static function xPlugin($key, $default = null)
    {
        $value = Helper::options()->plugin('XEditor')->$key;
        $value = $value ? $value : $default;
        return $value;
    }

    /**
     * 获取附件 SLUG
     *
     * @return string
     */
    public
    static function attachSlug()
    {
        $route = Typecho_Router::get('attachment');
        return preg_replace("/\[.*\]/i", '', $route['url']);
    }

    /**
     * 获取请求对象
     * @return Typecho_Request|null
     */
    public
    static function request()
    {
        return Typecho_Request::getInstance();
    }

    /**
     * 获取插件 URL
     * @param string $uri URI
     * @return string
     */
    public static function pluginUrl($uri = "")
    {
        return Typecho_Common::url($uri, Helper::options()->pluginUrl . '/XEditor');
    }

    /**
     * 根据ID获取单个Widget对象
     *
     * @param string $table 表名, 支持 contents, comments, metas, users
     * @param int $pkId 列ID, 必须是存在的 ID
     * @return Widget_Abstract
     */
    public static function widgetById($table, $pkId)
    {
        if (class_exists('\Utils\Helper'))
            return \Utils\Helper::widgetById($table, $pkId);
        $table = ucfirst($table);
        if (!in_array($table, array('Contents', 'Comments', 'Metas', 'Users'))) {
            return NULL;
        }

        $keys = array(
            'Contents' => 'cid',
            'Comments' => 'coid',
            'Metas' => 'mid',
            'Users' => 'uid'
        );

        $className = "Widget_Abstract_{$table}";
        $key = $keys[$table];
        $db = Typecho_Db::get();
        $widget = new $className(Typecho_Request::getInstance(), Typecho_Widget_Helper_Empty::getInstance(), null);

        $db->fetchRow(
            $widget->select()->where("{$key} = ?", $pkId)->limit(1),
            array($widget, 'push')
        );

        return $widget;
    }

    /**
     * 截断文本
     * @param string $text 需要截断的文本
     * @param int $length 长度
     * @param string $trim 结尾
     * @return string
     */
    public static function subStr($text, $length = 120, $trim = '...')
    {
        return Typecho_Common::fixHtml(Typecho_Common::subStr($text, 0, $length, $trim));
    }

    /**
     * 对象转 HTML
     *
     * @param mixed $widget
     * @param String $template
     * @return string
     */
    public static function xParse($widget, $template)
    {
        return preg_replace_callback(
            "/\{([_a-z0-9]+)\}/i",
            function ($matches) use ($widget) {
                return $widget->{$matches[1]};
            },
            $template
        );
    }

    /**
     * 从 Widget_Archive 对象中获取焦点图
     * @param mixed $archive 文章对象
     * @param int $quantity 数量
     * @param boolean $return 是否返回，默认直接输出
     * @param boolean $parse 是否根据模板转换
     * @param string $template 模板
     * @return mixed
     */
    public static function xThumbs($archive, $quantity = 3, $return = false, $parse = false, $template = '<img src="%s" />')
    {
        $thumbs = array();
        $quantity = intval($quantity);
        $fields = unserialize($archive->fields);
        $options = Helper::options();

        // 首先使用自定义字段 thumb
        if (array_key_exists('thumb', $fields) && (!empty($fields['thumb'])) && $quantity > 0) {
            if (!in_array($fields['thumb'], $thumbs)) {
                $fieldThumbs = explode("\n", $fields['thumb']);
                foreach ($fieldThumbs as $thumb) {
                    if ($quantity > 0 && !empty(trim($thumb))) {
                        $thumbs[] = $thumb;
                        $quantity -= 1;
                    }
                }
            }
        }

        // 然后是正文匹配
        preg_match_all("/<img(?<images>[^>]*?)>/i", $archive->content, $matches);
        foreach ($matches['images'] as $index => $value) {
            if ($quantity <= 0) {
                break;
            }
            preg_match('/src="(?<src>.*?)"/i', $value, $srcMatch);
            preg_match('/data-src="(?<src>.*?)"/i', $value, $dataSrcMatch);
            $match = empty($dataSrcMatch['src']) ? $srcMatch['src'] : $dataSrcMatch['src'];
            // 2020.03.29 修正输出插件图标的BUG
            if (strpos($match, __TYPECHO_PLUGIN_DIR__ . "/") !== false) {
                continue;
            }
            if (strpos($match, "//") === false) {
                continue;
            }
            if (!in_array($match, $thumbs)) {
                $thumbs[] = $match;
                $quantity -= 1;
            }
        }

        // 接着是附件匹配
        /* @var $attachments */
        Typecho_Widget::widget('Widget_Contents_Attachment_Related@content-' . $archive->cid, 'parentId=' . $archive->cid)->to($attachments);
        while ($attachments->next()) {
            if ($quantity <= 0) {
                break;
            }
            if (isset($attachments->isImage) && $attachments->isImage == 1) {
                if (!in_array($attachments->url, $thumbs)) {
                    $thumbs[] = $attachments->url;
                    $quantity -= 1;
                }
            }
        }

        // 最后是随机
        while ($quantity-- > 0) {
            $thumbs[] = $options->pluginUrl . '/XEditor/assets/images/thumbs/' . mt_rand(1, 42) . '.jpg';
        }

        // 转换
        if ($parse && (!empty($template))) {
            for ($i = 0; $i < count($thumbs); $i++) {
                $thumbs[$i] = str_replace("%s", $thumbs[$i], $template);
            }
        }

        // 输出或返回
        if ($return) {
            if (count($thumbs) == 1) {
                return $thumbs[0];
            }
            return $thumbs;
        } else {
            foreach ($thumbs as $thumb) {
                echo $thumb;
            }
            return true;
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function reflectGetValue($object, $name)
    {
        $reflect = new ReflectionClass($object);
        $property = $reflect->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
