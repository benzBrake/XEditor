<?php

namespace TypechoPlugin\AAEditor;

use Typecho\Common;
use Typecho\Db;
use Typecho\Plugin\Exception;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Widget\Helper\Form;
use Utils\Helper;

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 虾米皮皮乐的编辑器 <span style="color: #fff; background-color: green; font-weight: bold; padding: 3px 5px; margin: 0 5px;">预览版</span>
 *
 * @package AAEditor
 * @author Ryan
 * @version 0.1.4
 * @link https://doufu.ru
 *
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return string
     * @throws Exception
     */
    public static function activate()
    {
        return Util::activate();
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return string
     */
    public static function deactivate()
    {
        return Util::deactivate();
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Form $form 配置面板
     * @return void
     */
    public static function config(Form $form)
    {
// 增加数据库字段
        $db = Db::get();
        $notice = Widget::widget('Widget_Notice');
        $archive = Widget::widget('Widget_Archive');
        $request = $archive->request;
        $response = $archive->response;
        $plugin = "AAEditor";
        $pluginDataRow = $db->fetchRow($db->select()->from('table.options')->where('name = ?', "plugin:${plugin}"));
        $pluginData_backupRow = $db->fetchRow($db->select()->from('table.options')->where('name = ?', "plugin:${plugin}Backup"));
        $pluginData = empty($pluginDataRow) ? null : $pluginDataRow['value'];
        $pluginData_backup = empty($pluginData_backupRow) ? null : $pluginData_backupRow['value'];
        if (isset($request->type)) {
            if ($request->type == 'backup') {
                if ($db->fetchRow($db->select()->from('table.options')->where('name = ?', "plugin:${plugin}Backup"))) {
                    $updateQuery = $db->update('table.options')->rows(array('value' => $pluginData))->where('name = ?', "plugin:${plugin}Backup");
                    $db->query($updateQuery);
                    $notice->set(_t('备份已更新!'), 'success');
                    $response->goBack();
                } else {
                    if ($pluginData) {
                        $insertQuery = $db->insert('table.options')->rows(array('name' => "plugin:${plugin}Backup", 'user' => '0', 'value' => $pluginData));
                        $db->query($insertQuery);
                        $notice->set(_t('备份完成!'), 'success');
                        $response->goBack();
                    }
                }
            } elseif ($request->type == 'restore') {
                if ($pluginData_backup) {
                    $updateQuery = $db->update('table.options')->rows(array('value' => $pluginData_backup))->where('name = ?', "plugin:${plugin}");
                    $db->query($updateQuery);
                    $notice->set(_t('检测到模板备份数据，恢复完成'), 'success');
                } else {
                    $notice->set(_t('没有模板备份数据，恢复不了哦！'), 'error');
                }
                $response->goBack();
            } elseif ($request->type == 'delete') {
                if ($pluginData_backup) {
                    $deleteQuery = $db->delete('table.options')->where('name = ?', "plugin:${plugin}Backup");
                    $db->query($deleteQuery);
                    $notice->set(_t('删除成功！！！'), 'success');
                } else {
                    $notice->set_t(_t('不用删了！备份不存在！！！'), 'error');
                }
                $response->goBack();
            }
        }
        /** @var Array $errorMessage */
        $errorMessage = [];
        if (!$pluginData_backup) {
            $errorMessage[] = _t('检测到设置备份不存在，<a href="%s">点此</a>备份设置', Common::url('/options-plugin.php?config=AAEditor&type=backup', Helper::options()->adminUrl));
        } ?>
        <link rel="stylesheet" href="<?php echo Util::pluginUrl('/assets/dist/css/config.css'); ?>">
        <script src="<?php echo Util::pluginUrl('/assets/dist/js/config.js'); ?>"></script>
        <div class="x-config">
            <?php if (count($errorMessage)): ?>
                <div class="x-warning">
                    <?php foreach ($errorMessage as $msg): ?>
                        <div class="warning-item"><?php echo $msg; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="x-sticky">
                <div class="x-logo"><?php echo Helper::options()->theme . ' ' . Plugin::version(); ?></div>
                <ul class="x-tabs">
                    <li class="item" data-class="x-notice"><?php _e("最新公告"); ?></li>
                    <li class="item" data-class="x-basic"><?php _e("基础设置"); ?></li>
                    <li class="item" data-class="x-warn"><?php _e("慎重选择"); ?></li>
                </ul>
                <ul class="x-backup">
                    <span class="backup"
                          onclick="window.location.href='<?php Helper::options()->adminUrl('/options-plugin.php?config=AAEditor&type=backup') ?>'"><?php _e("备份设置"); ?></span>
                    <span class="restore"
                          onclick="window.location.href='<?php Helper::options()->adminUrl('/options-plugin.php?config=AAEditor&type=restore') ?>'"><?php _e("还原设置"); ?></span>
                    <span class="delete"
                          onclick="window.location.href='<?php Helper::options()->adminUrl('/options-plugin.php?config=AAEditor&type=delete') ?>'"><?php _e("删除备份"); ?></span>
                </ul>
            </div>
            <div class="x-content">

            </div>
        </div>
        <?php
        $edit = new Label('<ul class="x-item x-notice"><h2 class="title" data-version="' . Plugin::version() . '"><span class="loading">' . _t("加载中...") . '</span><span class="latest">' . _t("最新版本") . '</span><span class="latest found">' . _t("发现新版本：") . '</span><span class="latest version"></span></span></h2><div class="message"></div></ul>');
        $form->addItem($edit);
        $edit = new Form\Element\Select(
            'XShortCodeParse',
            array(
                'off' => _t('关闭'),
                'on' => _t('开启（默认）')
            ),
            'on',
            _t('是否开启短代码转换'),
            _t('介绍：如果短代码转换功能与你当前主题冲突，请关闭此功能！！!')
        );
        $edit->setAttribute('class', 'x-item x-basic');
        $form->addInput($edit->multiMode());

        $edit = new Form\Element\Select(
            'XLoadFontAwesome',
            array(
                'on' => _t('开启（默认）'),
                'off' => _t('关闭')
            ), 'on',
            _t('前台载入FontAwesome'),
            _t('说明：关闭后需要自行载入相关字体图片'));
        $edit->setAttribute('class', 'x-item x-basic');
        $form->addInput($edit);

        $edit = new Form\Element\Select('XAutoTags',
            array(
                'off' => _t('关闭'),
                'on' => _t('开启（默认）')
            ),
            'on',
            _t('是否启用标签自动提取功能（源自 AutoTags 插件）'),
            _t('说明：自动提取功能在文章已存在标签时不生效.'));
        $edit->setAttribute('class', 'x-item x-basic');
        $form->addInput($edit->multiMode());

        $edit = new Form\Element\Select('XInsertALlImages',
            array(
                'on' => _t('开启（默认）'),
                'off' => _t('关闭')
            ),
            'on',
            _t('图片一键插入全部'),
            _t('说明：开启后可以在附件列表中一键插入所有图片'));
        $edit->setAttribute('class', 'x-item x-basic');
        $form->addInput($edit->multiMode());

        $edit = new Form\Element\Select('XAutoSlugType',
            array(
                'pinyin' => _t('拼音（默认）'),
                'baidu' => _t('百度翻译'),
                'none' => _t("关闭")
            ),
            'pinyin',
            _t('SLUG 翻译模式'),
            _t('说明：英文翻译需要 配置 API KEY'));
        $edit->setAttribute('class', 'x-item x-basic');
        $form->addInput($edit->multiMode());

        $edit = new Form\Element\Text('XAutoSlugBaiduAppId', null, null, _t('SLUG 翻译：百度翻译 AppId'));
        $edit->setAttribute('class', 'x-item x-basic');
        $form->addInput($edit->multiMode());

        $edit = new Form\Element\Text('XAutoSlugBaiduKey', null, null, _t('SLUG 翻译：百度翻译 API KEY'), _t('<a href="https://api.fanyi.baidu.com/api/trans/product/index">获取 API Key</a>'));
        $edit->setAttribute('class', 'x-item x-basic');
        $form->addInput($edit->multiMode());

        $edit = new Form\Element\Radio('XCleanDatabase', array('clean' => _t('清理'), 'none' => _t('保留')), 'none', _t('禁用插件后是否保留数据'), _t('注意：如果打开了此开关，禁用插件时自动清理插件产生的数据（包括插件选项备份）'));
        $edit->setAttribute('class', 'x-item x-warn');
        $form->addInput($edit);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Form $form
     * @return void
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 获取主题版本
     * @return string
     */
    public static function version(): string
    {
        $info = \Typecho\Plugin::parseInfo(__FILE__);
        return $info['version'];
    }
}
