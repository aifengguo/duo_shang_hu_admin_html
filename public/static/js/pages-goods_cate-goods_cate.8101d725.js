(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-goods_cate-goods_cate"],{"04f9":function(t,e,r){var i=r("24fb");e=i(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.productSort .header[data-v-f439f60a]{width:100%;height:%?96?%;background-color:#fff;position:fixed;left:0;right:0;top:0;z-index:9;border-bottom:%?1?% solid #f5f5f5}.productSort .header .input[data-v-f439f60a]{width:%?700?%;height:%?60?%;background-color:#f5f5f5;border-radius:%?50?%;box-sizing:border-box;padding:0 %?25?%}.productSort .header .input .iconfont[data-v-f439f60a]{font-size:%?35?%;color:#555}.productSort .header .input .placeholder[data-v-f439f60a]{color:#999}.productSort .header .input uni-input[data-v-f439f60a]{font-size:%?26?%;height:100%;width:%?597?%}.productSort .aside[data-v-f439f60a]{position:fixed;width:%?180?%;left:0;bottom:0;top:0;background-color:#f7f7f7;overflow-y:auto;overflow-x:hidden;margin-top:%?96?%}.productSort .aside .item[data-v-f439f60a]{height:%?100?%;width:100%;font-size:%?26?%;color:#424242}.productSort .aside .item.on[data-v-f439f60a]{background-color:#fff;border-left:%?4?% solid #fc4141;width:100%;text-align:center;color:#fc4141;font-weight:700}.productSort .conter[data-v-f439f60a]{margin:%?96?% 0 0 %?180?%;padding:0 %?14?%;background-color:#fff}.productSort .conter .listw[data-v-f439f60a]{padding-top:%?20?%}.productSort .conter .listw .title[data-v-f439f60a]{height:%?90?%}.productSort .conter .listw .title .line[data-v-f439f60a]{width:%?100?%;height:%?2?%;background-color:#f0f0f0}.productSort .conter .listw .title .name[data-v-f439f60a]{font-size:%?28?%;color:#333;margin:0 %?30?%;font-weight:700}.productSort .conter .list[data-v-f439f60a]{-webkit-flex-wrap:wrap;flex-wrap:wrap}.productSort .conter .list .item[data-v-f439f60a]{width:%?177?%;margin-top:%?26?%}.productSort .conter .list .item .picture[data-v-f439f60a]{width:%?120?%;height:%?120?%;border-radius:50%}.productSort .conter .list .item .picture uni-image[data-v-f439f60a]{width:100%;height:100%;border-radius:50%}.productSort .conter .list .item .name[data-v-f439f60a]{font-size:%?24?%;color:#333;height:%?56?%;line-height:%?56?%;width:%?120?%;text-align:center}',""]),t.exports=e},"0a33":function(t,e,r){var i=r("04f9");"string"===typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);var o=r("4f06").default;o("cfa9acb0",i,!0,{sourceMap:!1,shadowMode:!1})},"0e23":function(t,e,r){"use strict";var i,o=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("v-uni-view",{staticClass:"productSort"},[r("v-uni-view",{staticClass:"header acea-row row-center-wrapper"},[r("v-uni-view",{staticClass:"acea-row row-between-wrapper input"},[r("v-uni-text",{staticClass:"iconfont icon-sousuo"}),r("v-uni-input",{attrs:{type:"text",placeholder:"点击搜索商品信息","confirm-type":"search",name:"search","placeholder-class":"placeholder"},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.searchSubmitValue.apply(void 0,arguments)}}})],1)],1),r("v-uni-view",{staticClass:"aside"},t._l(t.productList,(function(e,i){return r("v-uni-view",{key:i,staticClass:"item acea-row row-center-wrapper",class:i==t.navActive?"on":"",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.tap(i,"b"+i)}}},[r("v-uni-text",[t._v(t._s(e.cate_name))])],1)})),1),r("v-uni-view",{staticClass:"conter"},[r("v-uni-scroll-view",{style:"height:"+t.height+"rpx;",attrs:{"scroll-y":"true","scroll-into-view":t.toView,"scroll-with-animation":"true"},on:{scroll:function(e){arguments[0]=e=t.$handleEvent(e),t.scroll.apply(void 0,arguments)}}},[t._l(t.productList,(function(e,i){return[r("v-uni-view",{key:i+"_0",staticClass:"listw",attrs:{id:"b"+i}},[r("v-uni-view",{staticClass:"title acea-row row-center-wrapper"},[r("v-uni-view",{staticClass:"line"}),r("v-uni-view",{staticClass:"name"},[t._v(t._s(e.cate_name))]),r("v-uni-view",{staticClass:"line"})],1),r("v-uni-view",{staticClass:"list acea-row"},[t._l(e.children,(function(e,i){return[r("v-uni-navigator",{key:i+"_0",staticClass:"item acea-row row-column row-middle",attrs:{"hover-class":"none",url:"/pages/goods_list/index?sid="+e.id+"&title="+e.cate_name}},[r("v-uni-view",{staticClass:"picture"},[r("v-uni-image",{attrs:{src:e.pic}})],1),r("v-uni-view",{staticClass:"name line1"},[t._v(t._s(e.cate_name))])],1)]}))],2)],1)]})),t.number<15?r("v-uni-view",{style:"height:"+(t.height-300)+"rpx;"}):t._e()],2)],1)],1)},n=[];r.d(e,"b",(function(){return o})),r.d(e,"c",(function(){return n})),r.d(e,"a",(function(){return i}))},"3d7d":function(t,e,r){"use strict";r.r(e);var i=r("0e23"),o=r("9d46");for(var n in o)"default"!==n&&function(t){r.d(e,t,(function(){return o[t]}))}(n);r("4a62");var a,c=r("f0c5"),u=Object(c["a"])(o["default"],i["b"],i["c"],!1,null,"f439f60a",null,!1,i["a"],a);e["default"]=u.exports},"4a62":function(t,e,r){"use strict";var i=r("0a33"),o=r.n(i);o.a},"85ef":function(t,e,r){"use strict";r("ac1f"),r("498a"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i=r("a96f"),o={data:function(){return{navlist:[],productList:[],navActive:0,number:"",height:0,hightArr:[],toView:""}},onLoad:function(t){this.getAllCategory()},methods:{infoScroll:function(){var t=this,e=t.productList.length;this.number=t.productList[e-1].children.length,uni.getSystemInfo({success:function(e){t.height=e.windowHeight*(750/e.windowWidth)-98}});for(var r=[],i=0;i<e;i++){var o=uni.createSelectorQuery().in(this),n="#b"+i;o.select(n).boundingClientRect(),o.exec((function(e){var i=e[0].top;r.push(i),t.hightArr=r}))}},tap:function(t,e){this.toView=e,this.navActive=t},getAllCategory:function(){var t=this;(0,i.getCategoryList)().then((function(e){t.productList=e.data,setTimeout((function(){t.infoScroll()}),500)}))},scroll:function(t){for(var e=t.detail.scrollTop,r=this.hightArr,i=0;i<r.length;i++)e>=0&&e<r[1]-r[0]?this.navActive=0:e>=r[i]-r[0]&&e<r[i+1]-r[0]?this.navActive=i:e>=r[r.length-1]-r[0]&&(this.navActive=r.length-1)},searchSubmitValue:function(t){if(!(this.$util.trim(t.detail.value).length>0))return this.$util.Tips({title:"请填写要搜索的产品信息"});uni.navigateTo({url:"/pages/goods_list/index?searchValue="+t.detail.value})}}};e.default=o},"9d46":function(t,e,r){"use strict";r.r(e);var i=r("85ef"),o=r.n(i);for(var n in i)"default"!==n&&function(t){r.d(e,t,(function(){return i[t]}))}(n);e["default"]=o.a},a96f:function(t,e,r){"use strict";var i=r("ee27");Object.defineProperty(e,"__esModule",{value:!0}),e.getProductDetail=n,e.getProductCode=a,e.collectAdd=c,e.collectDel=u,e.postCartAdd=s,e.getCategoryList=l,e.getProductslist=d,e.getProductHot=f,e.collectAll=p,e.getGroomList=h,e.getCollectUserList=v,e.getReplyList=g,e.getReplyConfig=w,e.getSearchKeyword=m,e.storeListApi=b;var o=i(r("4ca0"));function n(t){return o.default.get("product/detail/"+t,{},{noAuth:!0})}function a(t){return o.default.get("product/code/"+t,{})}function c(t,e){return o.default.post("collect/add",{id:t,product:void 0===e?"product":e})}function u(t,e){return o.default.post("collect/del",{id:t,category:void 0===e?"product":e})}function s(t){return o.default.post("cart/add",t)}function l(){return o.default.get("category",{},{noAuth:!0})}function d(t){return o.default.get("products",t,{noAuth:!0})}function f(t,e){return o.default.get("product/hot",{page:void 0===t?1:t,limit:void 0===e?4:e},{noAuth:!0})}function p(t,e){return o.default.post("collect/all",{id:t,category:void 0===e?"product":e})}function h(t,e){return o.default.get("groom/list/"+t,e,{noAuth:!0})}function v(t){return o.default.get("collect/user",t)}function g(t,e){return o.default.get("reply/list/"+t,e)}function w(t){return o.default.get("reply/config/"+t)}function m(){return o.default.get("search/keyword",{},{noAuth:!0})}function b(t){return o.default.get("store_list",t)}}}]);