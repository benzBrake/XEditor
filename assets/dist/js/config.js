document.addEventListener("DOMContentLoaded",(function(){function t(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:document;n||(n=document);var a=n.querySelectorAll(t);[].forEach.call(a,e)}function e(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:document;return e||(e=document),e.querySelector(t)}var n=e(".x-config"),a=e(".x-content",n),r=n.parentNode.querySelector("form");r.parentNode.removeChild(r),a.appendChild(r);var c=e(".x-tabs",n),i=sessionStorage.getItem("x-active");(!i||""===i&&"undefined"===i)&&(i=".x-notice"),t(i,(function(t){t.classList.add("active")}),a),t('[data-class="'.concat(i.replace(".",""),'"]'),(function(t){t.classList.add("active")}),c),[].forEach.call(c.querySelectorAll(":scope > li"),(function(e){e.addEventListener("click",(function(e){e.preventDefault(),e.stopPropagation(),window.scrollTo(0,0),t(".x-tabs li",(function(t){t.classList.remove("active")})),e.target.classList.add("active");var n="."+e.target.dataset.class;sessionStorage.setItem("x-active",n),[].forEach.call(a.querySelectorAll(".x-item"),(function(t){t.classList.remove("active")})),[].forEach.call(a.querySelectorAll(n),(function(t){t.classList.add("active")}))}))})),fetch("https://api.vvhan.com/api/qqsc?key=95aa93d32e571b1579cfb15134b57dbd").then((function(t){return t.json()})).then((function(t){var a=t.title.substr(0,5),r=e(".x-notice",n),c=e(".title",n);e(".loading",c).remove(),e(".latest.version",c).innerHTML=t.title,e(".message",r).innerHTML=t.content,e(".latest.version",c).classList.add("active"),function(t,e){for(var n=t.split("."),a=e.split("."),r=n.length,c=a.length,i=Math.min(r,c),o=0;o<i;o++){var s=parseInt(n[o]),l=parseInt(a[o]);if(s>l)return 1;if(s<l)return-1}if(r>c){for(var d=o;d<r;d++)if(0!==parseInt(n[d]))return 1;return 0}if(r<c){for(var u=o;u<c;u++)if(0!==parseInt(a[u]))return-1;return 0}return 0}(a,c.dataset.version)>0?e(".latest.found",c).classList.add("active"):e(".latest",c).classList.add("active")})).catch((function(t){return console.log("Request Failed",t)}))}));