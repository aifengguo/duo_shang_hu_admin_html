(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-62078cf1"],{"1e97":function(t,e,a){},"2d6e":function(t,e,a){},"31b4":function(t,e,a){"use strict";var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return t.FromData?a("div",[a("Modal",{attrs:{scrollable:"","footer-hide":"",closable:"",title:t.FromData.title,"z-index":1,width:"700"},on:{"on-cancel":t.cancel},model:{value:t.modals,callback:function(e){t.modals=e},expression:"modals"}},[["/marketing/coupon/save.html"===t.FromData.action?a("div",{staticClass:"radio acea-row row-middle"},[a("div",{staticClass:"name ivu-form-item-content"},[t._v("优惠券类型")]),a("Radio-group",{on:{"on-change":t.couponsType},model:{value:t.type,callback:function(e){t.type=e},expression:"type"}},[a("Radio",{attrs:{label:0}},[t._v("通用券")]),a("Radio",{attrs:{label:1}},[t._v("品类券")]),a("Radio",{attrs:{label:2}},[t._v("商品券")])],1)],1):t._e()],a("form-create",{ref:"fc",staticClass:"formBox",attrs:{option:t.config,rule:Array.from(t.FromData.rules),handleIcon:"false"},on:{"on-submit":t.onSubmit}})],2)],1):t._e()},r=[],s=a("9860"),i=a.n(s),o=a("b6bd"),c=a("2f62");function l(t,e){var a=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),a.push.apply(a,n)}return a}function u(t){for(var e=1;e<arguments.length;e++){var a=null!=arguments[e]?arguments[e]:{};e%2?l(a,!0).forEach((function(e){d(t,e,a[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(a)):l(a).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(a,e))}))}return t}function d(t,e,a){return e in t?Object.defineProperty(t,e,{value:a,enumerable:!0,configurable:!0,writable:!0}):t[e]=a,t}var m={name:"edit",components:{formCreate:i.a.$form()},computed:u({},Object(c["e"])("admin/userLevel",["taskId","levelId"])),props:{FromData:{type:Object,default:null}},data:function(){return{modals:!1,type:0,config:{global:{upload:{props:{onSuccess:function(t,e){200===t.status?e.url=t.data.src:this.Message.error(t.msg)}}}}}}},methods:{couponsType:function(){this.$parent.addType(this.type)},onSubmit:function(t){var e=this,a={};a=t,Object(o["a"])({url:this.FromData.action,method:this.FromData.method,data:a}).then((function(t){e.$parent.getList(),e.$Message.success(t.msg),e.modals=!1,setTimeout((function(){e.$emit("submitFail")}),1e3)})).catch((function(t){e.$Message.error(t.msg)}))},cancel:function(){this.type=0}}},f=m,h=(a("c3a4"),a("2877")),p=Object(h["a"])(f,n,r,!1,null,"d7e9212a",null);e["a"]=p.exports},5415:function(t,e,a){"use strict";var n=a("7e38"),r=a.n(n);r.a},"5b9e":function(t,e,a){"use strict";a.r(e);var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("div",{staticClass:"i-layout-page-header"},[a("PageHeader",{staticClass:"product_tabs",attrs:{title:t.$route.meta.title,"hidden-breadcrumb":""}})],1),a("Card",{staticClass:"ivu-mt",attrs:{bordered:!1,"dis-hover":""}},[a("div",{staticClass:"table_box"},[a("Form",{ref:"formValidate",staticClass:"tabform",attrs:{model:t.formValidate,"label-width":t.labelWidth,"label-position":t.labelPosition},nativeOn:{submit:function(t){t.preventDefault()}}},[a("Row",{attrs:{gutter:24,type:"flex",justify:"end"}},[a("Col",{staticClass:"ivu-text-left",attrs:{span:"24"}},[a("FormItem",{attrs:{label:t.fromList.title+"："}},[a("RadioGroup",{staticClass:"mr15",attrs:{type:"button"},on:{"on-change":function(e){return t.selectChange(t.formValidate.data)}},model:{value:t.formValidate.data,callback:function(e){t.$set(t.formValidate,"data",e)},expression:"formValidate.data"}},t._l(t.fromList.fromTxt,(function(e,n){return a("Radio",{key:n,attrs:{label:e.val}},[t._v("\n                                    "+t._s(e.text)+"\n                                ")])})),1),a("DatePicker",{staticStyle:{width:"200px"},attrs:{value:t.timeVal,format:"yyyy/MM/dd",type:"daterange",placement:"bottom-end",placeholder:"自定义时间"},on:{"on-change":t.onchangeTime}})],1)],1),a("Col",{staticClass:"ivu-text-left",attrs:{span:"24"}},[a("FormItem",{attrs:{label:"用户分组："}},[a("RadioGroup",{attrs:{type:"button"},model:{value:t.formValidate.groupid,callback:function(e){t.$set(t.formValidate,"groupid",e)},expression:"formValidate.groupid"}},t._l(t.groupList,(function(e,n){return a("Radio",{key:n,attrs:{label:e.id}},[t._v(t._s(e.name))])})),1)],1)],1),a("Col",{staticClass:"ivu-text-left",attrs:{span:"24"}},[a("FormItem",{attrs:{label:"用户标签："}},[a("TagSelect",{model:{value:t.tagidList,callback:function(e){t.tagidList=e},expression:"tagidList"}},t._l(t.tagList,(function(e,n){return a("TagSelectOption",{key:n,attrs:{name:e.id}},[t._v(t._s(e.name))])})),1)],1)],1),a("Col",{staticClass:"ivu-text-left",attrs:{span:"24"}},[a("Col",{attrs:{xl:7,lg:12,md:12,sm:24,xs:24}},[a("FormItem",{attrs:{label:"用户名称："}},[a("Input",{directives:[{name:"width",rawName:"v-width",value:"90%",expression:"'90%'"}],attrs:{placeholder:"请输入用户名称"},model:{value:t.formValidate.nickname,callback:function(e){t.$set(t.formValidate,"nickname",e)},expression:"formValidate.nickname"}})],1)],1),a("Col",{staticClass:"sex_box",attrs:{xl:7,lg:12,md:12,sm:24,xs:24}},[a("FormItem",{attrs:{label:"性别："}},[a("Select",{staticStyle:{width:"90%"},attrs:{clearable:""},model:{value:t.formValidate.sex,callback:function(e){t.$set(t.formValidate,"sex",e)},expression:"formValidate.sex"}},[a("Option",{attrs:{value:"1"}},[t._v("男")]),a("Option",{attrs:{value:"2"}},[t._v("女")]),a("Option",{attrs:{value:"0"}},[t._v("保密")])],1)],1)],1),a("Col",{staticClass:"subscribe_box",attrs:{xl:7,lg:12,md:12,sm:24,xs:24}},[a("FormItem",{attrs:{label:"是否关注公众号："}},[a("Select",{staticStyle:{width:"90%"},attrs:{clearable:""},model:{value:t.formValidate.subscribe,callback:function(e){t.$set(t.formValidate,"subscribe",e)},expression:"formValidate.subscribe"}},[a("Option",{attrs:{value:"1"}},[t._v("是")]),a("Option",{attrs:{value:"0"}},[t._v("否")])],1)],1)],1),a("Col",{staticClass:"btn_box",attrs:{xl:3,lg:3,md:3,sm:24,xs:24}},[a("FormItem",[a("Button",{staticClass:"userSearch",attrs:{type:"primary",icon:"ios-search",label:"default"},on:{click:t.userSearchs}},[t._v("搜索")])],1)],1)],1),a("Divider",{attrs:{dashed:""}}),a("Col",{attrs:{span:"24"}},[a("Button",{staticClass:"mr20",attrs:{type:"primary"},on:{click:t.onSend}},[t._v("发送优惠券")]),a("Button",{staticClass:"greens mr20",attrs:{size:"default"},on:{click:t.onSendPic}},[a("Icon",{attrs:{type:"md-list"}}),t._v("\n                            发送图文消息\n                        ")],1)],1)],1)],1)],1),a("Table",{ref:"selection",staticClass:"mt25",attrs:{columns:t.columns4,data:t.tabList,loading:t.loading,"no-data-text":"暂无数据","highlight-row":"","no-filtered-data-text":"暂无筛选结果"},on:{"on-selection-change":t.onSelectTab},scopedSlots:t._u([{key:"headimgurl",fn:function(t){var e=t.row;t.index;return[a("viewer",[a("div",{staticClass:"tabBox_img"},[a("img",{directives:[{name:"lazy",rawName:"v-lazy",value:e.headimgurl,expression:"row.headimgurl"}]})])])]}},{key:"sex",fn:function(e){var n=e.row;return[a("span",{directives:[{name:"show",rawName:"v-show",value:1===n.sex,expression:"row.sex === 1"}]},[t._v("男")]),a("span",{directives:[{name:"show",rawName:"v-show",value:2===n.sex,expression:"row.sex === 2"}]},[t._v("女")]),a("span",{directives:[{name:"show",rawName:"v-show",value:0===n.sex,expression:"row.sex === 0"}]},[t._v("保密")])]}},{key:"country",fn:function(e){var n=e.row;return[a("span",[t._v(t._s(n.country+n.province+n.city))])]}},{key:"subscribe",fn:function(e){var n=e.row;return[a("span",{directives:[{name:"show",rawName:"v-show",value:1===n.subscribe,expression:"row.subscribe === 1"}]},[t._v("关注")]),a("span",{directives:[{name:"show",rawName:"v-show",value:0===n.subscribe,expression:"row.subscribe === 0"}]},[t._v("未关注")])]}},{key:"createModalFrame",fn:function(e){var n=e.row;return[[a("Dropdown",{on:{"on-click":function(e){return t.changeMenu(n,e)}}},[a("a",{attrs:{href:"javascript:void(0)"}},[t._v("操作\n                            "),a("Icon",{attrs:{type:"ios-arrow-down"}})],1),a("DropdownMenu",{attrs:{slot:"list"},slot:"list"},[a("DropdownItem",{directives:[{name:"show",rawName:"v-show",value:n.subscribe,expression:"row.subscribe"}],attrs:{name:"1"}},[t._v("修改分组")]),a("DropdownItem",{directives:[{name:"show",rawName:"v-show",value:n.subscribe,expression:"row.subscribe"}],attrs:{name:"2"}},[t._v("修改标签")]),a("DropdownItem",{directives:[{name:"show",rawName:"v-show",value:n.subscribe,expression:"row.subscribe"}],attrs:{name:"3"}},[t._v("同步标签")]),a("DropdownItem",{directives:[{name:"show",rawName:"v-show",value:!n.subscribe,expression:"!row.subscribe"}]},[t._v("无法操作")])],1)],1)]]}}])}),a("div",{staticClass:"acea-row row-right page"},[a("Page",{attrs:{total:t.total,"show-elevator":"","show-total":"","page-size":t.formValidate.limit},on:{"on-change":t.pageChange}})],1)],1),a("edit-from",{ref:"edits",attrs:{FromData:t.FromData},on:{submitFail:t.submitFail}}),a("send-from",{ref:"sends",attrs:{userIds:t.user_ids}}),a("Modal",{staticClass:"modelBox",attrs:{scrollable:"",title:"发送消息","z-index":100,width:"1200",height:"800","footer-hide":""},model:{value:t.modal13,callback:function(e){t.modal13=e},expression:"modal13"}},[t.modal13?a("news-category",{attrs:{isShowSend:t.isShowSend,userIds:t.user_ids,scrollerHeight:t.scrollerHeight,contentTop:t.contentTop,contentWidth:t.contentWidth,maxCols:t.maxCols}}):t._e()],1)],1)},r=[],s=a("a34a"),i=a.n(s),o=a("2f62"),c=a("b562"),l=a("c42b"),u=a("31b4"),d=a("a8e0");function m(t,e,a,n,r,s,i){try{var o=t[s](i),c=o.value}catch(l){return void a(l)}o.done?e(c):Promise.resolve(c).then(n,r)}function f(t){return function(){var e=this,a=arguments;return new Promise((function(n,r){var s=t.apply(e,a);function i(t){m(s,n,r,i,o,"next",t)}function o(t){m(s,n,r,i,o,"throw",t)}i(void 0)}))}}function h(t,e){var a=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),a.push.apply(a,n)}return a}function p(t){for(var e=1;e<arguments.length;e++){var a=null!=arguments[e]?arguments[e]:{};e%2?h(a,!0).forEach((function(e){g(t,e,a[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(a)):h(a).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(a,e))}))}return t}function g(t,e,a){return e in t?Object.defineProperty(t,e,{value:a,enumerable:!0,configurable:!0,writable:!0}):t[e]=a,t}var b={name:"user",components:{newsCategory:l["a"],editFrom:u["a"],sendFrom:d["a"]},data:function(){return{tagidList:[],isShowSend:!0,maxCols:4,scrollerHeight:"600",contentTop:"130",contentWidth:"98%",modal13:!1,timeVal:[],fromList:{title:"选择时间",custom:!0,fromTxt:[{text:"全部",val:""},{text:"今天",val:"today"},{text:"昨天",val:"yesterday"},{text:"最近7天",val:"lately7"},{text:"最近30天",val:"lately30"},{text:"本月",val:"month"},{text:"本年",val:"year"}]},formValidate:{limit:15,page:1,nickname:"",data:"",tagid_list:"",sex:"",groupid:0,subscribe:"",export:2},loading:!1,tabList:[],total:0,value2:"",grid:{xl:8,lg:8,md:8,sm:24,xs:24},columns4:[{type:"selection",min:60,align:"center"},{title:"ID",key:"uid"},{title:"微信用户名称",key:"nickname"},{title:"头像",slot:"headimgurl"},{title:"性别",slot:"sex"},{title:"地区",slot:"country"},{title:"是否关注公众号",slot:"subscribe"},{title:"用户分组",key:"groupid"},{title:"用户标签",key:"tagid_list"},{title:"操作",slot:"createModalFrame",fixed:"right",width:100}],tagList:[],groupList:[],FromData:null,selectionList:[],user_ids:""}},computed:p({},Object(o["e"])("admin/layout",["isMobile"]),{labelWidth:function(){return this.isMobile?void 0:80},labelPosition:function(){return this.isMobile?"top":"right"}}),created:function(){this.getListTag(),this.getList()},methods:{changeMenu:function(t,e){var a=this;switch(e){case"1":this.editGroup("app/wechat/user_group/".concat(t.openid,"/edit"));break;case"2":this.editGroup("app/wechat/user_tag/".concat(t.openid,"/edit"));break;default:var n={title:"同步该用户标签",url:"app/wechat/syn_tag/".concat(t.openid),method:"PUT",ids:""};this.$modalSure(n).then((function(t){a.$Message.success(t.msg),a.getList()})).catch((function(t){a.$Message.error(t.msg)}))}},editGroup:function(t){var e=this;Object(c["b"])(t).then(function(){var t=f(i.a.mark((function t(a){return i.a.wrap((function(t){while(1)switch(t.prev=t.next){case 0:e.FromData=a.data,e.$refs.edits.modals=!0;case 2:case"end":return t.stop()}}),t)})));return function(e){return t.apply(this,arguments)}}()).catch((function(t){e.$Message.error(t.msg)}))},submitFail:function(){this.getList()},submitModel:function(){this.getList()},onSend:function(){0===this.selectionList.length?this.$Message.warning("请选择要发送优惠券的会员"):(this.$refs.sends.modals=!0,this.$refs.sends.getList())},onSendPic:function(){0===this.selectionList.length?this.$Message.warning("请选择要发送图文消息的会员"):this.modal13=!0},onSelectTab:function(t){this.selectionList=t;var e=[];this.selectionList.map((function(t){e.push(t.uid)})),this.user_ids=e.join(",")},onchangeTime:function(t){this.timeVal=t,this.formValidate.data=this.timeVal.join("-"),this.getList()},selectChange:function(t){this.formValidate.data=t,this.timeVal=[],this.getList()},getListTag:function(){var t=this,e={id:"",name:"全部"};Object(c["k"])().then(function(){var a=f(i.a.mark((function a(n){var r;return i.a.wrap((function(a){while(1)switch(a.prev=a.next){case 0:r=n.data,t.tagList=r.tagList,t.groupList=r.groupList,t.groupList.unshift(e);case 4:case"end":return a.stop()}}),a)})));return function(t){return a.apply(this,arguments)}}()).catch((function(e){t.loading=!1,t.$Message.error(e.msg)}))},getList:function(){var t=this;this.loading=!0,this.formValidate.sex=this.formValidate.sex||"",this.formValidate.subscribe=this.formValidate.subscribe||"",this.formValidate.tagid_list=this.tagidList.join(","),Object(c["A"])(this.formValidate).then(function(){var e=f(i.a.mark((function e(a){var n;return i.a.wrap((function(e){while(1)switch(e.prev=e.next){case 0:n=a.data,t.tabList=n.list,t.total=n.count,t.loading=!1;case 4:case"end":return e.stop()}}),e)})));return function(t){return e.apply(this,arguments)}}()).catch((function(e){t.loading=!1,t.$Message.error(e.msg)}))},pageChange:function(t){this.formValidate.page=t,this.getList()},userSearchs:function(){this.getList()}}},v=b,w=(a("5415"),a("2877")),y=Object(w["a"])(v,n,r,!1,null,"64bc8b66",null);e["default"]=y.exports},"7e38":function(t,e,a){},a8e0:function(t,e,a){"use strict";var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("Modal",{attrs:{"z-index":100,scrollable:"","footer-hide":"",closable:"",title:"发送优惠券","mask-closable":!1,width:"900"},model:{value:t.modals,callback:function(e){t.modals=e},expression:"modals"}},[a("div",{staticClass:"acea-row"},[a("span",{staticClass:"sp"},[t._v("优惠券名称：")]),a("Input",{staticStyle:{width:"60%"},attrs:{search:"","enter-button":"",placeholder:"请输入优惠券名称"},on:{"on-search":t.userSearchs},model:{value:t.page.title,callback:function(e){t.$set(t.page,"title",e)},expression:"page.title"}})],1),a("Table",{ref:"table",staticClass:"mt25",attrs:{columns:t.columns,data:t.couponList,loading:t.loading,"highlight-row":"","no-userFrom-text":"暂无数据","no-filtered-userFrom-text":"暂无筛选结果"},scopedSlots:t._u([{key:"action",fn:function(e){var n=e.row,r=e.index;return[a("a",{on:{click:function(e){return t.sendGrant(n,"发送优惠券",r)}}},[t._v("发送")])]}}])}),a("div",{staticClass:"acea-row row-right page"},[a("Page",{attrs:{total:t.total,"show-elevator":"","show-total":"","page-size":t.page.limit},on:{"on-change":t.pageChange}})],1)],1)],1)},r=[],s=a("a34a"),i=a.n(s),o=a("c24f");function c(t,e,a,n,r,s,i){try{var o=t[s](i),c=o.value}catch(l){return void a(l)}o.done?e(c):Promise.resolve(c).then(n,r)}function l(t){return function(){var e=this,a=arguments;return new Promise((function(n,r){var s=t.apply(e,a);function i(t){c(s,n,r,i,o,"next",t)}function o(t){c(s,n,r,i,o,"throw",t)}i(void 0)}))}}var u={name:"send",props:{userIds:{type:String,default:""}},data:function(){return{modals:!1,loading:!1,couponList:[],columns:[{title:"优惠券名称",key:"title",align:"center",minWidth:100},{title:"优惠卷面值",key:"coupon_price",align:"center",minWidth:80},{title:"优惠券最低消费",key:"use_min_price",align:"center",minWidth:150},{title:"优惠券有效期限",key:"coupon_time",align:"center",minWidth:120},{title:"操作",slot:"action",align:"center",width:120}],page:{page:1,limit:15,title:""},total:0}},methods:{getList:function(t){var e=this;this.loading=!0,Object(o["a"])(this.page).then(function(){var t=l(i.a.mark((function t(a){var n;return i.a.wrap((function(t){while(1)switch(t.prev=t.next){case 0:200===a.status?(n=a.data,e.couponList=n.list,e.total=n.count,e.loading=!1):(e.loading=!1,e.$Message.error(a.msg));case 1:case"end":return t.stop()}}),t)})));return function(e){return t.apply(this,arguments)}}()).catch((function(t){e.loading=!1,e.$Message.error(t.msg)}))},userSearchs:function(){this.getList()},pageChange:function(t){this.page.page=t,this.getList()},sendGrant:function(t,e,a){var n=this,r={title:e,num:a,url:"marketing/coupon/user/grant",method:"post",ids:{id:t.id,uid:this.userIds}};this.$modalSure(r).then((function(t){n.$Message.success(t.msg)})).catch((function(t){n.$Message.error(t.msg)}))}}},d=u,m=(a("b3c1"),a("2877")),f=Object(m["a"])(d,n,r,!1,null,"53b45d7c",null);e["a"]=f.exports},b3c1:function(t,e,a){"use strict";var n=a("2d6e"),r=a.n(n);r.a},c24f:function(t,e,a){"use strict";a.d(e,"t",(function(){return r})),a.d(e,"f",(function(){return s})),a.d(e,"j",(function(){return i})),a.d(e,"a",(function(){return o})),a.d(e,"e",(function(){return c})),a.d(e,"d",(function(){return l})),a.d(e,"i",(function(){return u})),a.d(e,"k",(function(){return d})),a.d(e,"l",(function(){return m})),a.d(e,"o",(function(){return f})),a.d(e,"n",(function(){return h})),a.d(e,"m",(function(){return p})),a.d(e,"c",(function(){return g})),a.d(e,"b",(function(){return b})),a.d(e,"g",(function(){return v})),a.d(e,"q",(function(){return w})),a.d(e,"h",(function(){return y})),a.d(e,"p",(function(){return _})),a.d(e,"u",(function(){return x})),a.d(e,"s",(function(){return O})),a.d(e,"r",(function(){return k})),a.d(e,"v",(function(){return j}));var n=a("b6bd");function r(t){return Object(n["a"])({url:"user/user",method:"get",params:t})}function s(t){return Object(n["a"])({url:"user/user/".concat(t,"/edit"),method:"get"})}function i(t){return Object(n["a"])({url:"user/set_status/".concat(t.status,"/").concat(t.id),method:"put"})}function o(t){return Object(n["a"])({url:"marketing/coupon/grant",method:"get",params:t})}function c(t){return Object(n["a"])({url:"user/edit_other/".concat(t),method:"get"})}function l(t){return Object(n["a"])({url:"user/user/".concat(t),method:"get"})}function u(t){return Object(n["a"])({url:"user/one_info/".concat(t.id),method:"get",params:t.datas})}function d(t){return Object(n["a"])({url:"user/user_level/vip_list",method:"get",params:t})}function m(t){return Object(n["a"])({url:"user/user_level/set_show/".concat(t.id,"/").concat(t.is_show),method:"PUT"})}function f(t,e){return Object(n["a"])({url:"user/user_level/task/".concat(t),method:"get",params:e})}function h(t){return Object(n["a"])({url:"user/user_level/set_task_show/".concat(t.id,"/").concat(t.is_show),method:"PUT"})}function p(t){return Object(n["a"])({url:"user/user_level/set_task_must/".concat(t.id,"/").concat(t.is_must),method:"PUT"})}function g(t){return Object(n["a"])({url:"/user/user_level/create_task",method:"get",params:t})}function b(t){return Object(n["a"])({url:"user/user_level/create",method:"get",params:t})}function v(t){return Object(n["a"])({url:"user/give_level/".concat(t),method:"get"})}function w(t){return Object(n["a"])({url:"user/user_group/list",method:"get",params:t})}function y(t){return Object(n["a"])({url:"user/user_group/add/".concat(t),method:"get"})}function _(t){return Object(n["a"])({url:"setting/update_admin",method:"PUT",data:t})}function x(t){return Object(n["a"])({url:"user/set_group",method:"post",data:t})}function O(t){return Object(n["a"])({url:"user/user_label",method:"get",data:t})}function k(t){return Object(n["a"])({url:"user/user_label/add/".concat(t),method:"get"})}function j(t){return Object(n["a"])({url:"user/set_label",method:"post",data:t})}},c3a4:function(t,e,a){"use strict";var n=a("1e97"),r=a.n(n);r.a}}]);