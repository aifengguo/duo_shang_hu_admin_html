(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-9562c0ba","chunk-3693c920"],{3701:function(t,e,n){},b7be:function(t,e,n){"use strict";n.d(e,"l",(function(){return o})),n.d(e,"j",(function(){return c})),n.d(e,"k",(function(){return a})),n.d(e,"m",(function(){return u})),n.d(e,"t",(function(){return i})),n.d(e,"u",(function(){return d})),n.d(e,"s",(function(){return s})),n.d(e,"E",(function(){return l})),n.d(e,"c",(function(){return m})),n.d(e,"b",(function(){return f})),n.d(e,"a",(function(){return g})),n.d(e,"d",(function(){return p})),n.d(e,"g",(function(){return h})),n.d(e,"h",(function(){return b})),n.d(e,"A",(function(){return O})),n.d(e,"f",(function(){return j})),n.d(e,"e",(function(){return w})),n.d(e,"i",(function(){return k})),n.d(e,"q",(function(){return v})),n.d(e,"x",(function(){return y})),n.d(e,"w",(function(){return _})),n.d(e,"v",(function(){return x})),n.d(e,"y",(function(){return P})),n.d(e,"o",(function(){return E})),n.d(e,"p",(function(){return T})),n.d(e,"z",(function(){return C})),n.d(e,"r",(function(){return S})),n.d(e,"n",(function(){return U})),n.d(e,"F",(function(){return R})),n.d(e,"D",(function(){return V})),n.d(e,"B",(function(){return $})),n.d(e,"C",(function(){return D}));var r=n("b6bd");function o(t){return Object(r["a"])({url:"marketing/coupon/list",method:"get",params:t})}function c(t){return Object(r["a"])({url:"marketing/coupon/create/".concat(t),method:"get"})}function a(t){return Object(r["a"])({url:"marketing/coupon/".concat(t,"/edit"),method:"get"})}function u(t){return Object(r["a"])({url:"marketing/coupon/issue/".concat(t),method:"get"})}function i(t){return Object(r["a"])({url:"marketing/coupon/released",method:"get",params:t})}function d(t){return Object(r["a"])({url:"marketing/coupon/released/issue_log/".concat(t),method:"get"})}function s(t){return Object(r["a"])({url:"marketing/coupon/released/".concat(t,"/status"),method:"get"})}function l(t){return Object(r["a"])({url:"/marketing/coupon/user",method:"get",params:t})}function m(t){return Object(r["a"])({url:"marketing/bargain",method:"get",params:t})}function f(t){return Object(r["a"])({url:"marketing/bargain/".concat(t),method:"get"})}function g(t){return Object(r["a"])({url:"marketing/bargain/".concat(t.id),method:"POST",data:t})}function p(t){return Object(r["a"])({url:"marketing/bargain/set_status/".concat(t.id,"/").concat(t.status),method:"PUT"})}function h(t){return Object(r["a"])({url:"marketing/combination",method:"get",params:t})}function b(t){return Object(r["a"])({url:"marketing/combination/set_status/".concat(t.id,"/").concat(t.status),method:"PUT"})}function O(){return Object(r["a"])({url:"marketing/combination/statistics",method:"GET"})}function j(t){return Object(r["a"])({url:"marketing/combination/".concat(t),method:"get"})}function w(t){return Object(r["a"])({url:"marketing/combination/".concat(t.id),method:"POST",data:t})}function k(t){return Object(r["a"])({url:"marketing/combination/combine/list",method:"GET",params:t})}function v(t){return Object(r["a"])({url:"marketing/combination/order_pink/".concat(t),method:"GET"})}function y(t){return Object(r["a"])({url:"marketing/seckill",method:"GET",params:t})}function _(t){return Object(r["a"])({url:"marketing/seckill/".concat(t),method:"GET"})}function x(t){return Object(r["a"])({url:"marketing/seckill/".concat(t.id),method:"post",data:t})}function P(t){return Object(r["a"])({url:"marketing/seckill/set_status/".concat(t.id,"/").concat(t.status),method:"put"})}function E(t){return Object(r["a"])({url:"marketing/integral",method:"GET",params:t})}function T(t){return Object(r["a"])({url:"marketing/integral/statistics",method:"GET",params:t})}function C(){return Object(r["a"])({url:"marketing/seckill/time_list",method:"GET"})}function S(t,e){return Object(r["a"])({url:"product/product/attrs/".concat(t,"/").concat(e),method:"GET"})}function U(t){return Object(r["a"])({url:"marketing/coupon/released/".concat(t),method:"DELETE"})}function R(t){return Object(r["a"])({url:"export/userPoint",method:"get",params:t})}function V(t){return Object(r["a"])({url:"export/storeBargain",method:"get",params:t})}function $(t){return Object(r["a"])({url:"export/storeCombination",method:"get",params:t})}function D(t){return Object(r["a"])({url:"export/storeSeckill",method:"get",params:t})}},c4ad:function(t,e,n){"use strict";n.r(e);var r=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"goodList"},[n("Form",{ref:"formValidate",staticClass:"tabform",attrs:{model:t.formValidate,"label-width":t.labelWidth,"label-position":t.labelPosition}},[n("Row",{attrs:{type:"flex",gutter:24}},[n("Col",t._b({},"Col",t.grid,!1),[n("FormItem",{attrs:{label:"商品分类：","label-for":"pid"}},[n("Select",{staticStyle:{width:"200px"},attrs:{clearable:""},on:{"on-change":t.userSearchs},model:{value:t.formValidate.cate_id,callback:function(e){t.$set(t.formValidate,"cate_id",e)},expression:"formValidate.cate_id"}},t._l(t.treeSelect,(function(e){return n("Option",{key:e.id,attrs:{value:e.id}},[t._v(t._s(e.html+e.cate_name)+"\n                        ")])})),1)],1)],1),n("Col",t._b({},"Col",t.grid,!1),[n("FormItem",{attrs:{label:"商品搜索：","label-for":"store_name"}},[n("Input",{staticStyle:{width:"80%"},attrs:{search:"","enter-button":"",placeholder:"请输入商品名称,关键字,编号"},on:{"on-search":t.userSearchs},model:{value:t.formValidate.store_name,callback:function(e){t.$set(t.formValidate,"store_name",e)},expression:"formValidate.store_name"}})],1)],1)],1)],1),n("Table",{ref:"table",attrs:{"no-data-text":"暂无数据","no-filtered-data-text":"暂无筛选结果","max-height":"400",columns:t.columns4,data:t.tableList,loading:t.loading},on:{"on-selection-change":t.changeCheckbox},scopedSlots:t._u([{key:"image",fn:function(t){var e=t.row;t.index;return[n("viewer",[n("div",{staticClass:"tabBox_img"},[n("img",{directives:[{name:"lazy",rawName:"v-lazy",value:e.image,expression:"row.image"}]})])])]}}])}),n("div",{staticClass:"acea-row row-right page"},[n("Page",{attrs:{total:t.total,"show-elevator":"","show-total":"","page-size":t.formValidate.limit},on:{"on-change":t.pageChange}})],1),"many"===t.many?n("div",{staticClass:"footer",attrs:{slot:"footer"},slot:"footer"},[n("Button",{attrs:{type:"primary",size:"large",loading:t.modal_loading,long:""},on:{click:t.ok}},[t._v("提交")])],1):t._e()],1)},o=[],c=n("a34a"),a=n.n(c),u=n("2f62"),i=n("c4c8");function d(t,e,n,r,o,c,a){try{var u=t[c](a),i=u.value}catch(d){return void n(d)}u.done?e(i):Promise.resolve(i).then(r,o)}function s(t){return function(){var e=this,n=arguments;return new Promise((function(r,o){var c=t.apply(e,n);function a(t){d(c,r,o,a,u,"next",t)}function u(t){d(c,r,o,a,u,"throw",t)}a(void 0)}))}}function l(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function m(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?l(n,!0).forEach((function(e){f(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):l(n).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function f(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}var g={name:"index",data:function(){return{modal_loading:!1,treeSelect:[],formValidate:{page:1,limit:15,cate_id:"",store_name:""},total:0,modals:!1,loading:!1,grid:{xl:10,lg:10,md:12,sm:24,xs:24},tableList:[],currentid:0,productRow:{},columns4:[{title:"商品ID",key:"id"},{title:"图片",slot:"image"},{title:"商品名称",key:"store_name",minWidth:250},{title:"商品分类",key:"cate_name",minWidth:150}],images:[],many:""}},computed:m({},Object(u["e"])("admin/layout",["isMobile"]),{labelWidth:function(){return this.isMobile?void 0:120},labelPosition:function(){return this.isMobile?"top":"right"}}),created:function(){var t=this,e={width:60,align:"center",render:function(e,n){var r=n.row.id,o=!1;o=t.currentid===r;var c=t;return e("div",[e("Radio",{props:{value:o},on:{"on-change":function(){if(c.currentid=r,t.productRow=n.row,t.$emit("getProductId",t.productRow),t.productRow.id){if("image"===t.$route.query.fodder){var e={image:t.productRow.image,product_id:t.productRow.id};form_create_helper.set("image",e),form_create_helper.close("image")}}else t.$Message.warning("请先选择商品")}}})])}},n={type:"selection",width:60,align:"center"},r=this.$route.query.type;this.many=r,"many"===r?this.columns4.unshift(n):this.columns4.unshift(e)},mounted:function(){this.goodsCategory(),this.getList()},methods:{changeCheckbox:function(t){var e=[];t.forEach((function(t){var n={image:t.image,product_id:t.id};e.push(n)})),this.images=e},goodsCategory:function(){var t=this;Object(i["z"])().then((function(e){t.treeSelect=e.data})).catch((function(e){t.$Message.error(e.msg)}))},pageChange:function(t){this.formValidate.page=t,this.getList()},getList:function(){var t=this;this.loading=!0,Object(i["b"])(this.formValidate).then(function(){var e=s(a.a.mark((function e(n){var r;return a.a.wrap((function(e){while(1)switch(e.prev=e.next){case 0:r=n.data,t.tableList=r.list,t.total=n.data.count,t.loading=!1;case 4:case"end":return e.stop()}}),e)})));return function(t){return e.apply(this,arguments)}}()).catch((function(e){t.loading=!1,t.$Message.error(e.msg)}))},ok:function(){this.images.length>0?"image"===this.$route.query.fodder&&(console.log("this.images"),console.log(this.images),form_create_helper.set("image",this.images),form_create_helper.close("image")):this.$Message.warning("请先选择商品")},userSearchs:function(){this.getList()},clear:function(){this.productRow.id="",this.currentid=""}}},p=g,h=(n("e471"),n("2877")),b=Object(h["a"])(p,r,o,!1,null,"52c2389e",null);e["default"]=b.exports},c4c8:function(t,e,n){"use strict";n.d(e,"h",(function(){return o})),n.d(e,"i",(function(){return c})),n.d(e,"a",(function(){return a})),n.d(e,"r",(function(){return u})),n.d(e,"z",(function(){return i})),n.d(e,"p",(function(){return d})),n.d(e,"j",(function(){return s})),n.d(e,"q",(function(){return l})),n.d(e,"k",(function(){return m})),n.d(e,"l",(function(){return f})),n.d(e,"x",(function(){return g})),n.d(e,"b",(function(){return p})),n.d(e,"s",(function(){return h})),n.d(e,"w",(function(){return b})),n.d(e,"d",(function(){return O})),n.d(e,"e",(function(){return j})),n.d(e,"g",(function(){return w})),n.d(e,"v",(function(){return k})),n.d(e,"t",(function(){return v})),n.d(e,"u",(function(){return y})),n.d(e,"f",(function(){return _})),n.d(e,"m",(function(){return x})),n.d(e,"o",(function(){return P})),n.d(e,"n",(function(){return E})),n.d(e,"y",(function(){return T})),n.d(e,"c",(function(){return C}));var r=n("b6bd");function o(){return Object(r["a"])({url:"product/product/type_header",method:"get"})}function c(t){return Object(r["a"])({url:"product/product",method:"get",params:t})}function a(t,e){return Object(r["a"])({url:"product/product/set_show/".concat(t,"/").concat(e),method:"put"})}function u(t){return Object(r["a"])({url:"product/product/product_show",method:"put",data:t})}function i(){return Object(r["a"])({url:"product/category/tree/1",method:"get"})}function d(t){return Object(r["a"])({url:"product/product/".concat(t),method:"get"})}function s(t){return Object(r["a"])({url:"product/product/".concat(t.id),method:"POST",data:t})}function l(t){return Object(r["a"])({url:"product/category",method:"get",params:t})}function m(){return Object(r["a"])({url:"product/category/create",method:"get"})}function f(t){return Object(r["a"])({url:"product/category/".concat(t,"/edit"),method:"get"})}function g(t){return Object(r["a"])({url:"product/category/set_show/".concat(t.id,"/").concat(t.is_show),method:"PUT"})}function p(t){return Object(r["a"])({url:"product/product/list",method:"GET",params:t})}function h(t){return Object(r["a"])({url:"product/reply",method:"get",params:t})}function b(t,e){return Object(r["a"])({url:"product/reply/set_reply/".concat(e),method:"PUT",data:t})}function O(t){return Object(r["a"])({url:"product/crawl",method:"POST",data:t})}function j(t){return Object(r["a"])({url:"product/crawl/save",method:"POST",data:t})}function w(t,e){return Object(r["a"])({url:"product/generate_attr/".concat(e),method:"POST",data:t})}function k(t){return Object(r["a"])({url:"product/product/rule",method:"GET",params:t})}function v(t,e){return Object(r["a"])({url:"product/product/rule/".concat(e),method:"POST",data:t})}function y(t){return Object(r["a"])({url:"product/product/rule/".concat(t),method:"get"})}function _(t){return Object(r["a"])({url:"product/reply/fictitious_reply/".concat(t),method:"get"})}function x(){return Object(r["a"])({url:"product/product/get_rule",method:"get"})}function P(){return Object(r["a"])({url:"product/product/get_template",method:"get"})}function E(){return Object(r["a"])({url:"product/product/get_temp_keys",method:"get"})}function T(t){return Object(r["a"])({url:"export/storeProduct",method:"get",params:t})}function C(t){return Object(r["a"])({url:"product/product/check_activity/".concat(t),method:"get"})}},e471:function(t,e,n){"use strict";var r=n("3701"),o=n.n(r);o.a},ef0d:function(t,e,n){"use strict";var r=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("vue-ueditor-wrap",{staticStyle:{width:"90%"},attrs:{config:t.myConfig},on:{beforeInit:t.addCustomDialog},model:{value:t.contents,callback:function(e){t.contents=e},expression:"contents"}})],1)},o=[],c=n("6625"),a=n.n(c),u={name:"index",components:{VueUeditorWrap:a.a},props:{content:""},watch:{content:{handler:function(t){console.log(t)},deep:!0}},data:function(){return{contents:this.content,myConfig:{autoHeightEnabled:!1,initialFrameHeight:200,initialFrameWidth:"100%",UEDITOR_HOME_URL:"/admin/UEditor/",serverUrl:""}}},methods:{addCustomDialog:function(t){window.UE.registerUI("test-dialog",(function(t,e){var n=new window.UE.ui.Dialog({iframeUrl:"/admin/widget.images/index.html?fodder=dialog",editor:t,name:e,title:"上传图片",cssRules:"width:1200px;height:500px;padding:20px;"});this.dialog=n;var r=new window.UE.ui.Button({name:"dialog-button",title:"上传图片",cssRules:"background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;",onclick:function(){n.render(),n.open()}});return r}),37,t)}},created:function(){}},i=u,d=n("2877"),s=Object(d["a"])(i,r,o,!1,null,"c76d99ca",null);e["a"]=s.exports}}]);