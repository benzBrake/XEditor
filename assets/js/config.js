document.addEventListener("DOMContentLoaded",
    function () {
        var e = document.querySelectorAll(".x_config__aside .item"),
            t = document.querySelector(".x_config__notice"),
            s = document.querySelector(".x_config > form"),
            n = document.querySelectorAll(".x_content");
        if (e.forEach(function (o) {
            o.addEventListener("click",
                function () {
                    e.forEach(function (e) {
                        e.classList.remove("active")
                    }),
                        o.classList.add("active");
                    var c = o.getAttribute("data-current");
                    sessionStorage.setItem("x_config_current", c),
                        "x_notice" === c ? (t.style.display = "block", s.style.display = "none") : (t.style.display = "none", s.style.display = "block"),
                        n.forEach(function (e) {
                            e.style.display = "none";
                            var t = e.classList.contains(c);
                            t && (e.style.display = "block")
                        })
                })
        }), sessionStorage.getItem("x_config_current")) {
            var o = sessionStorage.getItem("x_config_current");
            "x_notice" === o ? (t.style.display = "block", s.style.display = "none") : (s.style.display = "block", t.style.display = "none"),
                e.forEach(function (e) {
                    var t = e.getAttribute("data-current");
                    t === o && e.classList.add("active")
                }),
                n.forEach(function (e) {
                    e.classList.contains(o) && (e.style.display = "block")
                })
        } else e[0].classList.add("active"),
            t.style.display = "block",
            s.style.display = "none";
        var c = new XMLHttpRequest;
        c.onreadystatechange = function () {
            if (4 === c.readyState) if (200 <= c.status && 300 > c.status || 304 === c.status) {
                var e = JSON.parse(c.responseText);
                t.innerHTML = e.success ? '<p class="title">最新版本：' + e.title + "</p>" + e.content : "请求失败！"
            } else t.innerHTML = "请求失败！"
        },
            c.open("get", "https://api.vvhan.com/api/qqsc?key=d8551631eeb7ecad47034a8b8c242c6c", !0),
            c.send(null)
    });