(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-users-user_coupon-index"],{"077f":function(t,e,i){"use strict";i.r(e);var n=i("2f3f"),o=i("8a25");for(var a in o)"default"!==a&&function(t){i.d(e,t,(function(){return o[t]}))}(a);i("a86a");var r,c=i("f0c5"),s=Object(c["a"])(o["default"],n["b"],n["c"],!1,null,"399596ad",null,!1,n["a"],r);e["default"]=s.exports},"0d6e":function(t,e,i){var n=i("24fb");e=n(!1),e.push([t.i,".money[data-v-1ac731d8]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.pic-num[data-v-1ac731d8]{color:#fff;font-size:.24rem}.coupon-list .item .text .condition[data-v-1ac731d8]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center}.condition .line-title[data-v-1ac731d8]{width:%?90?%;height:%?40?%!important;line-height:1.5!important;padding:0 %?10?%;-webkit-box-sizing:border-box;box-sizing:border-box;background:#fff7f7;border:1px solid #e83323;opacity:1;border-radius:%?22?%;font-size:%?20?%!important;color:#e83323;margin-right:%?12?%}",""]),t.exports=e},"23f0":function(t,e,i){t.exports=i.p+"static/img/noCoupon.e524084b.png"},"2b03":function(t,e,i){"use strict";var n,o=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",[t.couponsList.length?n("v-uni-view",{staticClass:"coupon-list"},t._l(t.couponsList,(function(e,i){return n("v-uni-view",{key:i,staticClass:"item acea-row row-center-wrapper"},[n("v-uni-view",{staticClass:"money",class:0==e._type?"moneyGray":""},[n("v-uni-view",[t._v("￥"),n("v-uni-text",{staticClass:"num"},[t._v(t._s(e.coupon_price))])],1),n("v-uni-view",{staticClass:"pic-num"},[t._v("满"+t._s(e.use_min_price)+"元可用")])],1),n("v-uni-view",{staticClass:"text"},[n("v-uni-view",{staticClass:"condition line1"},[0===e.applicable_type?n("v-uni-view",{staticClass:"line-title",class:0===e._type?"bg-color-huic":"bg-color-check"},[t._v("通用劵")]):1===e.applicable_type?n("v-uni-view",{staticClass:"line-title",class:0===e._type?"bg-color-huic":"bg-color-check"},[t._v("品类券")]):n("v-uni-view",{staticClass:"line-title",class:0===e._type?"bg-color-huic":"bg-color-check"},[t._v("商品券")]),n("v-uni-view",[t._v(t._s(e.coupon_title))])],1),n("v-uni-view",{staticClass:"data acea-row row-between-wrapper"},[n("v-uni-view",[t._v(t._s(e._add_time)+"-"+t._s(e._end_time))]),0==e._type?n("v-uni-view",{staticClass:"bnt gray"},[t._v(t._s(e._msg))]):n("v-uni-view",{staticClass:"bnt bg-color"},[t._v(t._s(e._msg))])],1)],1)],1)})),1):t._e(),t.couponsList.length||1!=t.loading?t._e():n("v-uni-view",{staticClass:"noCommodity"},[n("v-uni-view",{staticClass:"pictrue"},[n("v-uni-image",{attrs:{src:i("23f0")}})],1)],1),n("home")],1)},a=[];i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return a})),i.d(e,"a",(function(){return n}))},"2f3f":function(t,e,i){"use strict";var n,o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticStyle:{"touch-action":"none"}},[i("v-uni-view",{staticClass:"home",staticStyle:{position:"fixed"},style:{top:t.top+"px",bottom:t.bottom},attrs:{id:"right-nav"},on:{touchmove:function(e){e.stopPropagation(),e.preventDefault(),arguments[0]=e=t.$handleEvent(e),t.setTouchMove.apply(void 0,arguments)}}},[t.homeActive?i("v-uni-view",{staticClass:"homeCon bg-color-red",class:!0===t.homeActive?"on":""},[i("v-uni-navigator",{staticClass:"iconfont icon-shouye-xianxing",attrs:{"hover-class":"none",url:"/pages/index/index","open-type":"switchTab"}}),i("v-uni-navigator",{staticClass:"iconfont icon-caigou-xianxing",attrs:{"hover-class":"none",url:"/pages/order_addcart/order_addcart","open-type":"switchTab"}}),i("v-uni-navigator",{staticClass:"iconfont icon-yonghu1",attrs:{"hover-class":"none",url:"/pages/user/index","open-type":"switchTab"}})],1):t._e(),i("v-uni-view",{staticClass:"pictrueBox",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.open.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"pictrue"},[i("v-uni-image",{staticClass:"image",attrs:{src:!0===t.homeActive?"/static/images/close.gif":"/static/images/open.gif"}})],1)],1)],1)],1)},a=[];i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return a})),i.d(e,"a",(function(){return n}))},"5a1e":function(t,e,i){var n=i("0d6e");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=i("4f06").default;o("f63cd0c8",n,!0,{sourceMap:!1,shadowMode:!1})},"742b":function(t,e,i){"use strict";var n=i("5a1e"),o=i.n(n);o.a},"84cd":function(t,e,i){"use strict";var n=i("ee27");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var o=i("c111"),a=i("dccc"),r=i("2f62"),c=n(i("077f")),s={components:{home:c.default},data:function(){return{couponsList:[],loading:!1,isAuto:!1,isShowAuth:!1}},computed:(0,r.mapGetters)(["isLogin"]),onLoad:function(){this.isLogin?this.getUseCoupons():(0,a.toLogin)()},methods:{onLoadFun:function(){this.getUseCoupons()},authColse:function(t){this.isShowAuth=t},getUseCoupons:function(){var t=this;(0,o.getUserCoupons)(0).then((function(e){t.loading=!0,t.$set(t,"couponsList",e.data)}))}}};e.default=s},"8a25":function(t,e,i){"use strict";i.r(e);var n=i("ffec"),o=i.n(n);for(var a in n)"default"!==a&&function(t){i.d(e,t,(function(){return n[t]}))}(a);e["default"]=o.a},a6be:function(t,e,i){var n=i("b5c8");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var o=i("4f06").default;o("7b994400",n,!0,{sourceMap:!1,shadowMode:!1})},a86a:function(t,e,i){"use strict";var n=i("a6be"),o=i.n(n);o.a},b5c8:function(t,e,i){var n=i("24fb");e=n(!1),e.push([t.i,".pictrueBox[data-v-399596ad]{width:%?130?%;height:%?120?%}\n\n/*返回主页按钮*/.home[data-v-399596ad]{position:fixed;color:#fff;text-align:center;z-index:9999;right:%?15?%;display:-webkit-box;display:-webkit-flex;display:flex}.home .homeCon[data-v-399596ad]{border-radius:%?50?%;opacity:0;height:0;color:#e93323;width:0}.home .homeCon.on[data-v-399596ad]{opacity:1;-webkit-animation:bounceInRight .5s cubic-bezier(.215,.61,.355,1);animation:bounceInRight .5s cubic-bezier(.215,.61,.355,1);width:%?300?%;height:%?86?%;margin-bottom:%?20?%;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;background:#f44939!important}.home .homeCon .iconfont[data-v-399596ad]{font-size:%?48?%;color:#fff;display:inline-block;margin:0 auto}.home .pictrue[data-v-399596ad]{width:%?86?%;height:%?86?%;border-radius:50%;margin:0 auto}.home .pictrue .image[data-v-399596ad]{width:100%;height:100%;border-radius:50%;-webkit-transform:rotate(90deg);transform:rotate(90deg);ms-transform:rotate(90deg);moz-transform:rotate(90deg);webkit-transform:rotate(90deg);o-transform:rotate(90deg)}",""]),t.exports=e},c111:function(t,e,i){"use strict";var n=i("ee27");Object.defineProperty(e,"__esModule",{value:!0}),e.getIndexData=a,e.getLogo=r,e.setFormId=c,e.setCouponReceive=s,e.getCoupons=u,e.getUserCoupons=l,e.getArticleCategoryList=f,e.getArticleList=d,e.getArticleHotList=p,e.getArticleBannerList=v,e.getArticleDetails=g,e.loginMobile=h,e.verifyCode=b,e.registerVerify=m,e.phoneRegister=w,e.phoneRegisterReset=_,e.phoneLogin=y,e.switchH5Login=x,e.bindingPhone=C,e.logout=k,e.getTemlIds=A,e.pink=L,e.getCity=M,e.getLiveList=j;var o=n(i("4ca0"));function a(){return o.default.get("index",{},{noAuth:!0})}function r(){return o.default.get("wechat/get_logo",{},{noAuth:!0})}function c(t){return o.default.post("wechat/set_form_id",{formId:t})}function s(t){return o.default.post("coupon/receive",{couponId:t})}function u(t){return o.default.get("coupons",t,{noAuth:!0})}function l(t){return o.default.get("coupons/user/"+t)}function f(){return o.default.get("article/category/list",{},{noAuth:!0})}function d(t,e){return o.default.get("article/list/"+t,e,{noAuth:!0})}function p(){return o.default.get("article/hot/list",{},{noAuth:!0})}function v(){return o.default.get("article/banner/list",{},{noAuth:!0})}function g(t){return o.default.get("article/details/"+t,{},{noAuth:!0})}function h(t){return o.default.post("login/mobile",t,{noAuth:!0})}function b(){return o.default.get("verify_code",{},{noAuth:!0})}function m(t,e,i,n){return o.default.post("register/verify",{phone:t,type:void 0===e?"reset":e,key:i,code:n},{noAuth:!0})}function w(t){return o.default.post("register",t,{noAuth:!0})}function _(t){return o.default.post("register/reset",t,{noAuth:!0})}function y(t){return o.default.post("login",t,{noAuth:!0})}function x(){return o.default.post("switch_h5",{from:"wechat"})}function C(t){return o.default.post("binding",t)}function k(){return o.default.get("logout")}function A(){return o.default.get("wechat/teml_ids",{},{noAuth:!0})}function L(){return o.default.get("pink",{},{noAuth:!0})}function M(){return o.default.get("city_list",{},{noAuth:!0})}function j(t,e){return o.default.get("wechat/live",{page:t,limit:e},{noAuth:!0})}},ea8f:function(t,e,i){"use strict";i.r(e);var n=i("2b03"),o=i("ea9a");for(var a in o)"default"!==a&&function(t){i.d(e,t,(function(){return o[t]}))}(a);i("742b");var r,c=i("f0c5"),s=Object(c["a"])(o["default"],n["b"],n["c"],!1,null,"1ac731d8",null,!1,n["a"],r);e["default"]=s.exports},ea9a:function(t,e,i){"use strict";i.r(e);var n=i("84cd"),o=i.n(n);for(var a in n)"default"!==a&&function(t){i.d(e,t,(function(){return n[t]}))}(a);e["default"]=o.a},ffec:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=i("2f62"),o={name:"Home",props:{},data:function(){return{top:"",bottom:""}},computed:(0,n.mapGetters)(["homeActive"]),methods:{setTouchMove:function(t){var e=this;t.touches[0].clientY<545&&t.touches[0].clientY>66&&(e.top=t.touches[0].clientY)},open:function(){this.homeActive?this.$store.commit("CLOSE_HOME"):this.$store.commit("OPEN_HOME")}},created:function(){this.bottom="50px"}};e.default=o}}]);