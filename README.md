# XEditor

一款基于 Vditor 开发的 Typecho 编辑器插件

## 功能自定义

### 自定义按钮

修改 'assets/js/toolbar.js'，以插入按钮这个自带功能说明。
> `name`是按钮标记
>
> `tip`是按钮说明
>
> `tipPosition`是提示位置，具体定义参照 Vditor 官方文档，
>
> `icon`是图标，建议使用SVG，可以上 https://iconfont.cn 找图标
> `preview`是预览替换内容，这个功能是给短代码用的，主要是实时预览时自动替换短代码为 `preview`的模板
>
> `previewArea` 是弹窗按钮用的，修改参数后实时是否实时预览，值`c`时预览框内容居中，为`n`时不显示，其余值都显示
>
> `params`是弹出对话框的参数
>
> `click()`是按钮点击后的响应函数

```
    {
        "name": "x-btn",
        "tip": "插入按钮",
        "tipPosition": "n",
        "icon": "<svg viewBox=\"0 0 1024 1024\" xmlns=\"http://www.w3.org/2000/svg\" width=\"22\" height=\"22\"><path d=\"M856.73 796.7h-690c-57.9 0-105-47.1-105-105v-360c0-57.9 47.1-105 105-105h690c57.9 0 105 47.1 105 105v360c0 57.89-47.1 105-105 105zm-690-500.01c-19.3 0-35 15.7-35 35v360c0 19.3 15.7 35 35 35h690c19.3 0 35-15.7 35-35v-360c0-19.3-15.7-35-35-35h-690z\"/><path d=\"M233.16 431.69H790.3v160H233.16z\"/></svg>",
        "template": "[x-btn type='{type}' icon='{icon}' href='{href}' content='{content}' /]\n",
        "preview": "<x-btn$3>$5</x-btn>",
        "previewArea": "c",
        "params":
            {
                "type": {
                    "label": "按钮类型",
                    "tag": 'select',
                    "options": {
                        "primary": "primary",
                        "secondary": "secondary",
                        "light": "light",
                        "dark": "dark",
                        "info": "info",
                        "success": "success",
                        "warning": "warning",
                    }
                },
                "icon": {
                    "label": "<a href='https://fontawesome.dashgame.com/' target='_blank' title='点此查找图标Class'>按钮图标</a>",
                },
                "href": {
                    "label": "按钮链接",
                    "required": true
                }
                ,
                "content": {
                    "label": "按钮文字",
                    "default": "按钮",
                }
            },
        click() {
            window.XEditor.paramsPrompt('x-btn');
        }
    },
```

### 自定义短代码渲染

修改 `assets/js/x.short.js`

## 感谢

感谢 [Vditor](https://github.com/Vanessa219/vditor )，本插件基于 Vditor 构建

感谢 [Joe](https://github.com/HaoOuBa/Joe ) ，本插件大量使用 Joe 主题的各种图标

## 授权

学习可以，禁止直接改名商用！！！
