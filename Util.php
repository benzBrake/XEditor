<?php

namespace TypechoPlugin\AAEditor;

use Typecho\Common;
use Typecho\Http\Client;
use Typecho\Plugin;
use Typecho\Plugin\Exception;
use Typecho\Db;
use Typecho\Router;
use Typecho\Widget;
use Utils\Helper;
use Utils\Markdown;

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Util
{
    private static $jsonCache;
    private static $replacement;

    /**
     * 激活插件
     * @return string
     * @throws Exception
     */
    public static function activate(): string
    {
        if (false == Client::get()) {
            throw new Exception(_t('对不起, 您的主机不支持 php-curl 扩展而且没有打开 allow_url_fopen 功能, 无法正常使用此功能'));
        }
        // 添加公共内容
        Plugin::factory('Widget_Archive')->footer = [__CLASS__, 'archiveFooter'];

        // 添加文章编辑选项
        Plugin::factory('admin/write-post.php')->richEditor = [__CLASS__, 'richEditor'];
        Plugin::factory('admin/write-page.php')->richEditor = [__CLASS__, 'richEditor'];
        Plugin::factory('admin/write-post.php')->bottom = [__CLASS__, 'editorFooter'];
        Plugin::factory('admin/write-page.php')->bottom = [__CLASS__, 'editorFooter'];

        // 自动拾取标签
        Plugin::factory('Widget_Contents_Post_Edit')->write = array(__CLASS__, 'write');

        // 短代码
        Plugin::factory('admin/common.php')->begin = [__CLASS__, 'shortCodeInit'];
        Plugin::factory('Widget_Archive')->handleInit = [__CLASS__, 'shortCodeInit'];

        // 内容替换处理
        Plugin::factory('Widget_Abstract_Contents')->contentEx = [__CLASS__, 'contentEx'];
        Plugin::factory('Widget_Abstract_Contents')->excerptEx = [__CLASS__, 'excerptEx'];

        // 路由
        Helper::addAction('editor', Action::class);
        return _t("插件启用成功");
    }

    /**
     * 禁用插件
     *
     * @return mixed
     * @throws Exception|Db\Exception
     */
    public static function deactivate()
    {
        $db = Db::get();
        // 清理数据库
        Helper::removeAction('editor');
        if (Util::xPlugin('XCleanDatabase', 'none') === 'clean') {
            $db->query($db->delete('table.options')->where('name = ?', "plugin:AAEditorBackup"));
            return _t("数据清理成功，插件已禁用！");
        } else {
            return _t("插件已禁用，但备份数据未清理。");
        }
    }

    /**
     * 前台附加 CSS JS
     * @return void
     * @throws Exception
     */
    public static function archiveFooter()
    {
        if (Util::xPlugin('XShortCodeParse', 'on') === 'on'): ?>
            <link rel="stylesheet" href="<?php echo Util::pluginUrl('assets/dist/css/front.css'); ?>">
            <script>
                XConf = {
                    'options': {
                        'pluginUrl': '<?php Helper::options()->pluginUrl("AAEditor/") ?>',
                        'playerUrl': '<?php Helper::options()->pluginUrl("AAEditor/Libs/Player.php?url=") ?>',
                    }
                };
            </script>
            <script src="<?php echo Util::pluginUrl('assets/dist/js/short.js'); ?>"></script>
        <?php endif;
        if (Util::xPlugin('XLoadFontAwesome', 'on') === 'on'): ?>
            <link rel="stylesheet" href="https://gcore.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
        <?php endif;
    }

    /**
     * 编辑页面附加 CSS JS
     * @return void
     * @throws Exception
     */
    public static function editorFooter()
    {
        ?>
        <script>

            document.addEventListener('DOMContentLoaded', function () {
                <?php if (Util::xPlugin('XAutoSlugType', 'pinyin') !== 'none'): ?>
                const baiduSlug = function () {
                    let title = $('#title');
                    let slug = $('#slug');
                    if (slug.val().length > 0 || title.val().length == 0) {
                        return;
                    }
                    $.ajax({
                        url: '<?php Helper::options()->index('/action/editor?slug&word='); ?>' + title.val(),
                        success: function (data) {
                            if (data.result.length > 0) {
                                slug.val(data.result).focus();
                                slug.siblings('pre').text(data.result);
                            }
                        }
                    });
                };
                $('#title').blur(baiduSlug);
                $('#slug').blur(baiduSlug);
                <?php endif; ?>
                <?php if (Util::xPlugin('XInsertALlImages', 'on') === 'on'): ?>
                if ($('#ph-insert-images').length === 0) {
                    $('#upload-panel').append('<span id="ph-insert-images" class="ph-btn"><?php _e("插入所有图片附件"); ?></span>');
                    $('#ph-insert-images').on('click', function () {
                        let fileList = $('#file-list').children('li'),
                            text = "";
                        fileList.each((num, el) => {
                            let item = $(el);
                            if (item.data('image')) {
                                text += "\n" + `![${item.find('.insert').text()}](${item.data('url')})`;
                            }
                        });
                        window.XEditor.replaceSelection(text);
                    });
                }
                <?php endif; ?>
            });
        </script>
        <?php

    }

    /**
     * 自定义编辑器
     * @param $content
     * @return void
     * @throws Exception
     */
    public static function richEditor($content)
    {
        $options = Helper::options();
        ?>
        <script src="<?php $options->adminStaticUrl('js', 'hyperdown.js'); ?>"></script>
        <script src="<?php $options->adminStaticUrl('js', 'pagedown.js'); ?>"></script>
        <script src="<?php $options->adminStaticUrl('js', 'paste.js'); ?>"></script>
        <script src="<?php $options->adminStaticUrl('js', 'purify.js'); ?>"></script>
        <link rel="stylesheet" href="<?php echo Util::pluginUrl('assets/dist/css/main.css'); ?>">
        <script>
            window.XConf = {
                options: {
                    siteUrl: '<?php $options->siteUrl(); ?>',
                    pluginUrl: '<?php $options->pluginUrl("AAEditor") ?>',
                    playerUrl: '<?php $options->pluginUrl("AAEditor/Libs/Player.php?url=") ?>',
                    emoji: {
                        path: '<?php echo $options->pluginUrl; ?>',
                    },
                    toolbarJson: <?php echo file_get_contents(Util::pluginUrl('assets/json/editor.json')); ?>
                },
                i18n: {
                    cancel: '<?php _e("取消"); ?>',
                    content: '<?php _e("内容"); ?>',
                    dialog: '<?php _e("对话框"); ?>',
                    ok: '<?php _e("确定"); ?>',
                    title: '<?php _e("标题"); ?>',
                }
            }
        </script>
        <script src="<?php echo Util::pluginUrl('assets/dist/js/preview.js'); ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var textarea = $('#text'),
                    isFullScreen = false,
                    toolbar = $('<div class="editor" id="wmd-button-bar" />').insertBefore(textarea.parent()),
                    preview = $('<div id="wmd-preview" />').insertAfter('#text');

                textarea.parent().addClass('edit-area'); // 修正预览框定位

                var options = {}, isMarkdown = <?php echo intval($content->isMarkdown || !$content->have()); ?>;

                options.strings = {
                    bold: '<?php _e('加粗'); ?> <strong> Ctrl+B',
                    boldexample: '<?php _e('加粗文字'); ?>',

                    italic: '<?php _e('斜体'); ?> <em> Ctrl+I',
                    italicexample: '<?php _e('斜体文字'); ?>',

                    link: '<?php _e('链接'); ?> <a> Ctrl+L',
                    linkdescription: '<?php _e('请输入链接描述'); ?>',

                    quote: '<?php _e('引用'); ?> <blockquote> Ctrl+Q',
                    quoteexample: '<?php _e('引用文字'); ?>',

                    code: '<?php _e('代码'); ?> <pre><code> Ctrl+K',
                    codeexample: '<?php _e('请输入代码'); ?>',

                    image: '<?php _e('图片'); ?> <img> Ctrl+G',
                    imagedescription: '<?php _e('请输入图片描述'); ?>',

                    olist: '<?php _e('数字列表'); ?> <ol> Ctrl+O',
                    ulist: '<?php _e('普通列表'); ?> <ul> Ctrl+U',
                    litem: '<?php _e('列表项目'); ?>',

                    heading: '<?php _e('标题'); ?> <h1>/<h2> Ctrl+H',
                    headingexample: '<?php _e('标题文字'); ?>',

                    hr: '<?php _e('分割线'); ?> <hr> Ctrl+R',
                    more: '<?php _e('摘要分割线'); ?> <!--more--> Ctrl+M',

                    undo: '<?php _e('撤销'); ?> - Ctrl+Z',
                    redo: '<?php _e('重做'); ?> - Ctrl+Y',
                    redomac: '<?php _e('重做'); ?> - Ctrl+Shift+Z',

                    fullscreen: '<?php _e('全屏'); ?> - Ctrl+J',
                    exitFullscreen: '<?php _e('退出全屏'); ?> - Ctrl+E',
                    fullscreenUnsupport: '<?php _e('此浏览器不支持全屏操作'); ?>',

                    imagedialog: '<p><b><?php _e('插入图片'); ?></b></p><p><?php _e('请在下方的输入框内输入要插入的远程图片地址'); ?></p><p><?php _e('您也可以使用附件功能插入上传的本地图片'); ?></p>',
                    linkdialog: '<p><b><?php _e('插入链接'); ?></b></p><p><?php _e('请在下方的输入框内输入要插入的链接地址'); ?></p>',

                    ok: '<?php _e('确定'); ?>',
                    cancel: '<?php _e('取消'); ?>',

                    help: '<?php _e('Markdown语法帮助'); ?>'
                };


                var converter = new HyperDown();

                // 增加白名单
                // converter.commonWhiteList += '|' + Object.keys(window.XPreview.shortcodes).join('|')
                // converter.blockHtmlTags += '|' + Object.keys(window.XPreview.shortcodes).join('|');

                var editor = new Markdown.Editor(converter, '', options);

                // 自动跟随
                converter.enableHtml(true);
                converter.enableLine(true);

                reloadScroll = scrollableEditor(textarea, preview);

                // 修正白名单
                converter.hook('makeHtml', function (html) {

                    html = html.replace('<p><!--more--></p>', '<!--more-->');

                    if (html.indexOf('<!--more-->') > 0) {
                        var parts = html.split(/\s*<\!\-\-more\-\->\s*/),
                            summary = parts.shift(),
                            details = parts.join('');

                        html = '<div class="summary">' + summary + '</div>'
                            + '<div class="details">' + details + '</div>';
                    }

                    // 处理短代码
                    html = window.XPreview.makeHtml(html);

                    // 替换block
                    html = html.replace(/<(iframe|embed)\s+([^>]*)>/ig, function (all, tag, src) {
                        if (src[src.length - 1] == '/') {
                            src = src.substring(0, src.length - 1);
                        }

                        return '<div class="embed"><strong>'
                            + tag + '</strong> : ' + $.trim(src) + '</div>';
                    });
                    return html;
                    // return DOMPurify.sanitize(html, {USE_PROFILES: {html: true}});
                });

                editor.hooks.chain('onPreviewRefresh', function () {
                    var images = $('img', preview), count = images.length;

                    if (count == 0) {
                        reloadScroll(true);
                    } else {
                        images.bind('load error', function () {
                            count--;

                            if (count == 0) {
                                reloadScroll(true);
                            }
                        });
                    }

                    window.XPreview.convertTag(preview);
                });

                <?php \Typecho\Plugin::factory('admin/editor-js.php')->markdownEditor($content); ?>

                var th = textarea.height(), ph = preview.height(),
                    uploadBtn = $('<button type="button" id="btn-fullscreen-upload" class="btn btn-link">'
                        + '<i class="i-upload"><?php _e('附件'); ?></i></button>')
                        .prependTo('.submit .right')
                        .click(function () {
                            $('a', $('.typecho-option-tabs li').not('.active')).trigger('click');
                            return false;
                        });

                $('.typecho-option-tabs li').click(function () {
                    uploadBtn.find('i').toggleClass('i-upload-active',
                        $('#tab-files-btn', this).length > 0);
                });

                editor.hooks.chain('enterFakeFullScreen', function () {
                    th = textarea.height();
                    ph = preview.height();
                    $(document.body).addClass('fullscreen');
                    var h = $(window).height() - toolbar.outerHeight();

                    textarea.css('height', h);
                    preview.css('height', h);
                    isFullScreen = true;
                });

                editor.hooks.chain('enterFullScreen', function () {
                    $(document.body).addClass('fullscreen');

                    var h = window.screen.height - toolbar.outerHeight();
                    textarea.css('height', h);
                    preview.css('height', h);
                    isFullScreen = true;
                });

                editor.hooks.chain('exitFullScreen', function () {
                    $(document.body).removeClass('fullscreen');
                    textarea.height(th);
                    preview.height(ph);
                    isFullScreen = false;
                });

                editor.hooks.chain('commandExecuted', function () {
                    textarea.trigger('input');
                });

                function initMarkdown() {
                    editor.run();

                    // 优化图片及文件附件插入 Thanks to Markxuxiao
                    Typecho.insertFileToEditor = function (file, url, isImage) {
                        html = isImage ? '![' + file + '](' + url + ')'
                            : '[' + file + '](' + url + ')';
                        textarea.replaceSelection(html);
                    };

                    Typecho.uploadComplete = function (file) {
                        Typecho.insertFileToEditor(file.title, file.url, file.isImage);
                    };

                    // 剪贴板复制图片
                    textarea.pastableTextarea().on('pasteImage', function (e, data) {
                        var name = data.name ? data.name.replace(/[\(\)\[\]\*#!]/g, '') : (new Date()).toISOString().replace(/\..+$/, '');
                        if (!name.match(/\.[a-z0-9]{2,}$/i)) {
                            var ext = data.blob.type.split('/').pop();
                            name += '.' + ext;
                        }

                        Typecho.uploadFile(new File([data.blob], name), name);
                    });
                }

                if (isMarkdown) {
                    initMarkdown();
                } else {
                    var notice = $('<div class="message notice"><?php _e('这篇文章不是由Markdown语法创建的, 继续使用Markdown编辑它吗?'); ?> '
                        + '<button class="btn btn-xs primary yes"><?php _e('是'); ?></button> '
                        + '<button class="btn btn-xs no"><?php _e('否'); ?></button></div>')
                        .hide().insertBefore(textarea).slideDown();

                    $('.yes', notice).click(function () {
                        notice.remove();
                        $('<input type="hidden" name="markdown" value="1" />').appendTo('.submit');
                        initMarkdown();
                    });

                    $('.no', notice).click(function () {
                        notice.remove();
                    });
                }
                // 感谢 LREditor
                setInterval("$('#wmd-preview').css('height', (parseInt($('#text').outerHeight())) +'px');", 500);
                window._editor = editor;
            });
        </script>
        <script src="<?php echo Util::pluginUrl('assets/dist/js/main.js'); ?>"></script>
        <script src="<?php echo Util::pluginUrl('assets/dist/js/short.js'); ?>"></script>
        <link rel="stylesheet" href="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/font-awesome/4.7.0/css/font-awesome.min.css">
        <?php
    }

    /**
     * 缓存短代码替换规则
     *
     * @return void
     */
    public static function shortCodeInit()
    {
        if (empty(Util::$jsonCache)) {
            $toolbarStr = file_get_contents(dirname(__FILE__) . '/assets/json/editor.json');
            Util::$jsonCache = json_decode($toolbarStr, true);
        }
        if (empty(Util::$replacement)) {
            Util::$replacement = [];
            foreach (Util::$jsonCache as $key => $value) {
                if (!array_key_exists($key, Util::$replacement) && array_key_exists('replacement', $value)) {
                    Util::$replacement[$key] = $value['replacement'];
                }
            }
        }
    }

    /**
     * 内容处理
     * @param $text
     * @param $archive
     * @param $last
     * @return string
     */
    public static function contentEx($text, $archive, $last): string
    {
        if ($last) $text = $last;
        if (Util::xPlugin('XShortCodeParse', 'on') === 'on') {
            // Steal from wordpress
            if (false !== strpos($text, '[')) {
                if (false !== strpos($text, '[post')) {
                    $pattern = Util::get_shortcode_regex(array('post'));
                    $text = preg_replace_callback("/$pattern/", function ($m) {
                        return Util::postCallback($m);
                    }, $text);
                }
                if (false !== strpos($text, '[x-post')) {
                    $pattern = Util::get_shortcode_regex(array('x-post'));
                    $text = preg_replace_callback("/$pattern/", function ($m) {
                        return Util::postCallback($m);
                    }, $text);
                }
                if (false !== strpos($text, '[hide')) {
                    $pattern = Util::get_shortcode_regex(array('hide'));
                    $text = preg_replace_callback("/$pattern/", function ($m) use ($archive) {
                        $content = $m[5] ?? null;
                        return Util::hideCallback($m, $content, $archive);
                    }, $text);
                }
                if (!empty(Util::$replacement)) {
                    foreach (Util::$replacement as $tag => $replacement) {
                        if (false !== strpos($text, '[' . $tag)) {
                            $pattern = self::get_shortcode_regex([$tag]);
                            $text = preg_replace("/$pattern/", $replacement, $text);
                        }
                    }
                }
            }
        }
        return Util::parseEmoji($text);
    }

    /**
     * 摘要处理
     */
    public static function excerptEx($text, $archive, $last): string
    {
        if ($last) $text = $last;
        if (Util::xPlugin('XShortCodeParse', 'on') === 'on') {
            if (false !== strpos($text, '[post')) {
                $pattern = Util::get_shortcode_regex(array('post'));
                $text = preg_replace_callback("/$pattern/", function ($m) {
                    // Allow [[foo]] syntax for escaping a tag.
                    if ('[' === $m[1] && ']' === $m[6]) {
                        return substr($m[0], 1, -1);
                    }
                    $attrs = Util::shortcode_parse_atts($m[3]);
                    if (is_array($attrs) && !array_key_exists('cid', $attrs) && !empty($attrs['cid'])) {
                        $post = Helper::widgetById('Contents', $attrs['cid']);
                        if ($post->have()) {
                            $template = _t('参考文章《{title}》');
                            return Util::xParse($post, $template);
                        }
                    }
                    // 去除非法 ID
                    return '';
                }, $text);
            }
            if (false !== strpos($text, '[x-post')) {
                $pattern = Util::get_shortcode_regex(array('x-post'));
                $text = preg_replace_callback("/$pattern/", function ($m) {
                    // Allow [[foo]] syntax for escaping a tag.
                    if ('[' === $m[1] && ']' === $m[6]) {
                        return substr($m[0], 1, -1);
                    }
                    $attrs = Util::shortcode_parse_atts($m[3]);
                    if (is_array($attrs) && !array_key_exists('cid', $attrs)) {
                        $post = Helper::widgetById('Contents', $attrs['cid']);
                        if ($post->have()) {
                            $template = _t('参考文章《{title}》');
                            return Util::xParse($post, $template);
                        }
                    }
                    // 去除非法 ID
                    return '';
                }, $text);
            }
            $pattern = Util::get_shortcode_regex(['hide']);
            $text = preg_replace("/$pattern/", '', $text);
            foreach (Util::$replacement as $tag => $replacement) {
                if (false !== strpos($text, '[' . $tag)) {
                    $pattern = self::get_shortcode_regex([$tag]);
                    $text = preg_replace("/$pattern/", $replacement, $text);
                }
            }
        }
        return Util::parseEmoji($text);
    }

    /**
     * 引用文章回调
     * @param $m 匹配内容
     * @return false|string
     */
    public static function postCallback($m)
    {
        // Allow [[foo]] syntax for escaping a tag.
        if ('[' === $m[1] && ']' === $m[6]) {
            return substr($m[0], 1, -1);
        }
        $attrs = Util::shortcode_parse_atts($m[3]);
        if (is_array($attrs) && array_key_exists('cid', $attrs)) {
            $post = Helper::widgetById('Contents', $attrs['cid']);
            if ($post->have()) {
                $post->abstract = Util::subStr($post->excerpt, 120);
                $post->thumb = Util::xThumbs($post, 1, true);
                $template = '<div class="shortcode shortcode-post">' .
                    '<div class="text-content">' .
                    '<div class="title"><a href="{permalink}">{title}</a></div>' .
                    '<div class="content">{abstract}</div><a class="mt-2 btn btn-primary x-btn-rounded" href="{permalink}"> ' . _t("查看详情") . '</a></div>' .
                    '<div class="media-content"><a href="{permalink}" title="{title}"><img class="no-parse" alt="{title}" src="{thumb}"/></a></div></div>';
                return Util::xParse($post, $template);
            }
        }
        return '';
    }

    /**
     * 回复可见回调
     * @param $m 匹配内容
     * @param $content 文章内容
     * @param $archive 文章对象
     * @return false|string
     * @throws Db\Exception
     */
    public static function hideCallback($m, $content, $archive)
    {
        // Allow [[foo]] syntax for escaping a tag.
        if ('[' === $m[1] && ']' === $m[6]) {
            return substr($m[0], 1, -1);
        }
        $user = Widget::widget('Widget_User');
        $db = Db::get();
        $mail = $user->hasLogin() ? $user->mail : $archive->remember('mail', true);
        $select = $db->select()->from('table.comments')
            ->where('cid = ?', $archive->cid)
            ->where('mail = ?', $mail)
            ->where('status = ?', 'approved')
            ->limit(1);

        $result = $db->fetchAll($select);
        if ($user->pass('administrator', true) || $result) {
            return '<div class="shortcode shortcode-hide show">' . $content . '</div>';
        } else {
            return '<div class="shortcode shortcode-hide hidden">此处内容已隐藏，<a href="#comments">回复后(需要填写邮箱)</a>可见</div>';
        }
    }

    /**
     * Retrieve the shortcode regular expression for searching.
     *
     * The regular expression combines the shortcode tags in the regular expression
     * in a regex class.
     *
     * The regular expression contains 6 different sub matches to help with parsing.
     *
     * 1 - An extra [ to allow for escaping shortcodes with double [[]]
     * 2 - The shortcode name
     * 3 - The shortcode argument list
     * 4 - The self closing /
     * 5 - The content of a shortcode when it wraps some content.
     * 6 - An extra ] to allow for escaping shortcodes with double [[]]
     *
     * @param array $tagnames Optional. List of shortcodes to find. Defaults to all registered shortcodes.
     * @return string The shortcode search regular expression
     * @global array $shortcode_tags
     *
     * @since 2.5.0
     * @since 4.4.0 Added the `$tagnames` parameter.
     *
     */
    public static function get_shortcode_regex($tagnames = null): string
    {
        global $shortcode_tags;

        if (empty($tagnames)) {
            $tagnames = array_keys($shortcode_tags);
        }
        $tagregexp = implode('|', array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag().
        // Also, see shortcode_unautop() and shortcode.js.

        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        return '\\['                             // Opening bracket.
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
            . "($tagregexp)"                     // 2: Shortcode name.
            . '(?![\\w-])'                       // Not followed by word character or hyphen.
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash.
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag...
            . '\\]'                          // ...and closing bracket.
            . '|'
            . '\\]'                          // Closing bracket.
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
            . '[^\\[]*+'             // Not an opening bracket.
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
            . '[^\\[]*+'         // Not an opening bracket.
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag.
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]].
        // phpcs:enable
    }

    /**
     * Retrieve all attributes from the shortcodes tag.
     *
     * The attributes list has the attribute name as the key and the value of the
     * attribute as the value in the key/value pair. This allows for easier
     * retrieval of the attributes, since all attributes have to be known.
     *
     * @param string $text
     * @return array|string List of attribute values.
     *                      Returns empty array if '""' === trim( $text ).
     *                      Returns empty string if '' === trim( $text ).
     *                      All other matches are checked for not empty().
     * @since 2.5.0
     *
     */
    public static function shortcode_parse_atts($text)
    {
        $atts = array();
        $pattern = Util::get_shortcode_atts_regex();
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) && strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8]) && strlen($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                } elseif (isset($m[9])) {
                    $atts[] = stripcslashes($m[9]);
                }
            }

            // Reject any unclosed HTML elements.
            foreach ($atts as &$value) {
                if (false !== strpos($value, '<')) {
                    if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }

        return $atts;
    }


    /**
     * Retrieve the shortcode attributes regex.
     *
     * @return string The shortcode attribute regular expression
     * @since 4.4.0
     *
     */
    public static function get_shortcode_atts_regex()
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
    }

    /**
     * 表情处理
     * @param $text
     * @return string
     */
    public static function parseEmoji($text)
    {
        // 表情处理 来自 JOE 主题的 EMOJI
        $text = preg_replace_callback(
            '/\:\:\(\s*(呵呵|哈哈|吐舌|太开心|笑眼|花心|小乖|乖|捂嘴笑|滑稽|你懂的|不高兴|怒|汗|黑线|泪|真棒|喷|惊哭|阴险|鄙视|酷|啊|狂汗|what|疑问|酸爽|呀咩爹|委屈|惊讶|睡觉|笑尿|挖鼻|吐|犀利|小红脸|懒得理|勉强|爱心|心碎|玫瑰|礼物|彩虹|太阳|星星月亮|钱币|茶杯|蛋糕|大拇指|胜利|haha|OK|沙发|手纸|香蕉|便便|药丸|红领巾|蜡烛|音乐|灯泡|开心|钱|咦|呼|冷|生气|弱|吐血|狗头)\s*\)/is',
            function ($match) {
                return '<img class="owo" src="' . Helper::options()->pluginUrl . '/AAEditor/assets/images/owo/paopao/' . str_replace('%', '', urlencode($match[1])) . '_2x.png" alt="表情"/>';
            },
            $text
        );
        return preg_replace_callback(
            '/\:\@\(\s*(高兴|小怒|脸红|内伤|装大款|赞一个|害羞|汗|吐血倒地|深思|不高兴|无语|亲亲|口水|尴尬|中指|想一想|哭泣|便便|献花|皱眉|傻笑|狂汗|吐|喷水|看不见|鼓掌|阴暗|长草|献黄瓜|邪恶|期待|得意|吐舌|喷血|无所谓|观察|暗地观察|肿包|中枪|大囧|呲牙|抠鼻|不说话|咽气|欢呼|锁眉|蜡烛|坐等|击掌|惊喜|喜极而泣|抽烟|不出所料|愤怒|无奈|黑线|投降|看热闹|扇耳光|小眼睛|中刀)\s*\)/is',
            function ($match) {
                return '<img class="owo" src="' . Helper::options()->pluginUrl . '/AAEditor/assets/images/owo/aru/' . str_replace('%', '', urlencode($match[1])) . '_2x.png" alt="表情"/>';
            },
            $text
        );
    }

    /**
     * 发布文章时自动提取标签
     * 来自 Keywords 插件
     *
     * @access public
     * @return void
     * @throws Exception
     */
    public static function write($contents, $edit)
    {
        if (Util::xPlugin('', 'on') === 'off') return $contents;
        $html = $contents['text'];
        $isMarkdown = (0 === strpos($html, '<!--markdown-->'));
        if ($isMarkdown) {
            $html = Markdown::convert($html);
        }
        $text = str_replace("\n", '', trim(strip_tags(html_entity_decode($html))));
        //插件启用,且未手动设置标签
        if (!$contents['tags']) {
            Widget::widget('Widget_Metas_Tag_Admin')->to($tags);
            foreach ($tags->stack as $tag) {
                $tagNames[] = $tag['name'];
            }
            //过滤 html 标签等无用内容
            $postString = json_encode($text);
            $ch = curl_init('https://api.bosonnlp.com/tag/analysis?space_mode=0&oov_level=0&t2s=0');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'X-Token: fpm1fDvA.5220.GimJs8QvViSK'
                )
            );
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            $ignoreTag = array('w', 'wkz', 'wky', 'wyz', 'wyy', 'wj', 'ww', 'wt', 'wd', 'wf', 'wn', 'wm', 'ws', 'wp', 'wb', 'wh', 'email', 'tel', 'id', 'ip', 'url', 'o', 'y', 'u', 'uzhe', 'ule', 'ugou', 'ude', 'usou', 'udeng', 'uyy', 'udh', 'uzhi', 'ulian', 'c', 'p', 'pba', 'pbei', 'd', 'dl', 'q', 'm', 'r', 'z', 'b', 'bl', 'a', 'ad', 'an', 'al', 'v', 'vd', 'vshi', 'vyou', 'vl', 'f', 's', 't', 'nl');
            $sourceTags = array();
            foreach ($result[0]->tag as $key => $tag) {
                if (!in_array($tag, $ignoreTag)) {
                    if (in_array($result[0]->word[$key], $tagNames)) {
                        if (in_array($result[0]->word[$key], $sourceTags)) continue;
                        $sourceTags[] = $result[0]->word[$key];
                    }
                }
            }
            $contents['tags'] = implode(',', array_unique($sourceTags));
            if (count($contents['tags']) < 3) {
                $ch = curl_init('https://api.bosonnlp.com/keywords/analysis?top_k=5');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array(
                        'Content-Type: application/json',
                        'Accept: application/json',
                        'X-Token: fpm1fDvA.5220.GimJs8QvViSK'
                    )
                );
                $result = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($result);
                $a = [];
                foreach ($result as $re) {
                    $a[] = $re[1];
                }
                $contents['tags'] = $contents['tags'] ? $contents['tags'] . ',' . implode(',', $a) : implode(',', $a);
            }
        }
        return $contents;
    }

    /**
     * 获取附件 SLUG
     *
     * @return string
     */
    public static function attachSlug(): string
    {
        $route = Router::get('attachment');
        return preg_replace("/\[.*\]/i", '', $route['url']);
    }

    /**
     * 对象转 HTML
     *
     * @param mixed $widget
     * @param String $template
     * @return string
     */
    public static function xParse($widget, string $template): string
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
        foreach ($matches['images'] as $value) {
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
        Widget::widget('Widget_Contents_Attachment_Related@content-' . $archive->cid, 'parentId=' . $archive->cid)->to($attachments);
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
            $thumbs[] = $options->pluginUrl . '/AAEditor/assets/images/thumbs/' . mt_rand(1, 42) . '.jpg';
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
     * 获取插件 URL
     * @param string $uri URI
     * @return string
     */
    public static function pluginUrl(string $uri = ""): string
    {
        return Common::url($uri, Helper::options()->pluginUrl . '/AAEditor');
    }

    /**
     * 获取插件配置
     *
     * @param String $key 关键字
     * @param mixed $default 默认值
     * @return mixed
     * @throws Exception
     */
    public static function xPlugin(string $key, $default = null)
    {
        $value = Helper::options()->plugin('AAEditor')->$key;
        return $value ?: $default;
    }

    /**
     * 截断文本
     * @param string $text 需要截断的文本
     * @param int $length 长度
     * @param string $trim 结尾
     * @return string
     */
    public static function subStr(string $text, int $length = 120, string $trim = '...'): string
    {
        return Common::fixHtml(Common::subStr($text, 0, $length, $trim));
    }
}
