# AAEditor

## 功能自定义

### 自定义按钮

修改 'assets/json/editor.json'，以插入按钮这个自带功能说明。
> `name`是按钮标记
>
> `insertBefore`是指在那个 DOM 前边插入可写 #id .class
>
> `icon`是图标，建议使用SVG，可以上 https://iconfont.cn 找图标
> 
> `template` 填入编辑器的模板
>
> `preview`是预览替换内容，这个功能是给短代码用的，主要是实时预览时自动替换短代码为`preview`的模板，`$3`是短代码属性，`$5`是短代码内容，具体可以看 https://regex101.com/r/ja0b1p/1
>
> `previewArea` 是弹窗按钮用的，修改参数后实时是否实时预览，值`c`时预览框内容居中，为`n`时不显示，其余值都显示
>
> `replacement` 是 PHP 前台文章渲染处理的正则替换表达式，和`preview`一样
>
> `params`是弹出对话框的参数
>
> `onclick`是点击响应方法

```
    "x-btn": {
        "name": "插入按钮",
        "insertBefore": "#wmd-spacer5",
        "template": "[x-btn type=\"{type}\" icon=\"{icon}\" href=\"{href}\" content=\"{content}\" /]\n",
        "preview": "<div class=\"shortcode shortcode-btn\"$3><p>按钮</p></div>",
        "replacement": "<x-btn$3>$5</x-btn>",
        "params": {
            "type": {
                "label": "按钮类型",
                "tag": "select",
                "options": {
                    "primary": "primary",
                    "secondary": "secondary",
                    "light": "light",
                    "dark": "dark",
                    "info": "info",
                    "success": "success",
                    "warning": "warning"
                }
            },
            "icon": {
                "label": "<a href='https://fontawesome.dashgame.com/' target='_blank' title='点此查找图标Class'>按钮图标</a>"
            },
            "href": {
                "label": "按钮链接",
                "required": true
            },
            "content": {
                "label": "按钮文字",
                "default": "按钮"
            }
        },
        "icon": "<svg viewBox=\"0 0 1024 1024\" xmlns=\"http://www.w3.org/2000/svg\" width=\"22\" height=\"22\"><path d=\"M856.73 796.7h-690c-57.9 0-105-47.1-105-105v-360c0-57.9 47.1-105 105-105h690c57.9 0 105 47.1 105 105v360c0 57.89-47.1 105-105 105zm-690-500.01c-19.3 0-35 15.7-35 35v360c0 19.3 15.7 35 35 35h690c19.3 0 35-15.7 35-35v-360c0-19.3-15.7-35-35-35h-690z\"/><path d=\"M233.16 431.69H790.3v160H233.16z\"/></svg>"
    }
```

### 自定义短代码渲染

修改 `assets/src/short.js`

## 编译JS
1.需要 nodejs 环境
2.在插件目录运行 CMD
```
npm install # 安装依赖
npm run prod # 编译
```
3.如果需要实时编译
```
npm run watch
```


## 授权

学习可以，禁止直接改名商用！！！
