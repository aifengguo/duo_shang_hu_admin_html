(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-e817c0c8"],{"03ec":function(t,a,e){t.exports=e.p+"view_admin/img/mobilehead.1c931282.png"},"19a68":function(t,a,e){"use strict";var r=e("9875"),i=e.n(r);i.a},3137:function(t,a,e){"use strict";e.r(a);var r=function(){var t=this,a=t.$createElement,r=t._self._c||a;return r("div",[r("div",{staticClass:"i-layout-page-header"},[r("PageHeader",{staticClass:"product_tabs",attrs:{"hidden-breadcrumb":""}},[r("div",{staticClass:"ivu-mt ivu-mb",attrs:{slot:"title"},slot:"title"},[r("router-link",{attrs:{to:{path:"/admin/app/wechat/reply/keyword"}}},[r("Button",{directives:[{name:"show",rawName:"v-show",value:t.$route.params.id,expression:"$route.params.id"}],staticClass:"mr20",attrs:{icon:"ios-arrow-back",size:"small"}},[t._v("返回")])],1),r("span",{staticClass:"mr20",domProps:{textContent:t._s(t.$route.meta.title)}})],1)])],1),r("Card",{staticClass:"ivu-mt",attrs:{bordered:!1,"dis-hover":""}},[r("Row",{attrs:{gutter:24,type:"flex"}},[r("Col",{staticClass:"ml40",attrs:{span:"24"}},[r("Col",{attrs:{span:24}},[r("Col",{staticClass:"left mb15",attrs:{xl:7,lg:7,md:22,sm:22,xs:22}},[r("img",{staticClass:"top",attrs:{src:e("03ec")}}),r("img",{staticClass:"bottom",attrs:{src:e("58b1")}}),r("div",{staticClass:"centent"},[r("div",{staticClass:"time-wrapper"},[r("span",{staticClass:"time"},[t._v("9:36")])]),"news"!==t.formValidate.type?r("div",{staticClass:"view-item text-box clearfix"},[r("div",{staticClass:"avatar fl"},[r("img",{attrs:{src:e("47952")}})]),r("div",{staticClass:"box-content fl"},["text"===t.formValidate.type?r("span",{domProps:{textContent:t._s(t.formValidate.data.content)}}):t._e(),t.formValidate.data.src?r("div",{staticClass:"box-content_pic"},[r("img",{attrs:{src:t.formValidate.data.src?t.imgUrl+t.formValidate.data.src:""}})]):t._e()])]):t._e(),"news"===t.formValidate.type?r("div",t._l(t.formValidate.data.list,(function(a,e){return r("div",{key:e},[0===e?r("div",[r("div",{staticClass:"news_pic",style:{backgroundImage:"url("+a.image_input[0]+")",backgroundSize:"100% 100%"}}),r("span",{staticClass:"news_sp"},[t._v(t._s(a.title))])]):r("div",{staticClass:"news_cent"},[a.synopsis?r("span",{staticClass:"news_sp1"},[t._v(t._s(a.title))]):t._e(),0!==a.image_input.length?r("div",{staticClass:"news_cent_img"},[r("img",{attrs:{src:a.image_input[0]}})]):t._e()])])})),0):t._e()])]),r("Col",{attrs:{xl:11,lg:12,md:22,sm:22,xs:22}},[r("Col",{staticClass:"userAlert",attrs:{span:"24"}},[r("div",{staticClass:"box-card right"},[r("Form",{ref:"formValidate",staticClass:"mt20",attrs:{model:t.formValidate,rules:t.ruleValidate,"label-width":100},nativeOn:{submit:function(t){t.preventDefault()}}},[t.$route.params.id?r("FormItem",{attrs:{label:"关键字：",prop:"val"}},[r("div",{staticClass:"arrbox"},[t._l(t.labelarr,(function(a,e){return r("Tag",{key:e,attrs:{name:a,closable:"0"===t.$route.params.id},on:{"on-close":t.handleClose}},[t._v(t._s(a)+"\n                                            ")])})),r("input",{directives:[{name:"model",rawName:"v-model",value:t.val,expression:"val"}],staticClass:"arrbox_ip",staticStyle:{width:"90%"},attrs:{placeholder:"输入后回车",readonly:"0"!==t.$route.params.id},domProps:{value:t.val},on:{keyup:function(a){return!a.type.indexOf("key")&&t._k(a.keyCode,"enter",13,a.key,"Enter")?null:t.addlabel(a)},input:function(a){a.target.composing||(t.val=a.target.value)}}})],2)]):t._e(),r("FormItem",{attrs:{label:"规则状态："}},[r("RadioGroup",{model:{value:t.formValidate.status,callback:function(a){t.$set(t.formValidate,"status",a)},expression:"formValidate.status"}},[r("Radio",{attrs:{label:1}},[t._v("启用")]),r("Radio",{attrs:{label:0}},[t._v("禁用")])],1)],1),r("FormItem",{attrs:{label:"消息类型：",prop:"type"}},[r("Select",{staticStyle:{width:"90%"},attrs:{placeholder:"请选择规则状态"},on:{"on-change":function(a){return t.RuleFactor(t.formValidate.type)}},model:{value:t.formValidate.type,callback:function(a){t.$set(t.formValidate,"type",a)},expression:"formValidate.type"}},[r("Option",{attrs:{value:"text"}},[t._v("文字消息")]),r("Option",{attrs:{value:"image"}},[t._v("图片消息")]),r("Option",{attrs:{value:"news"}},[t._v("图文消息")]),r("Option",{attrs:{value:"voice"}},[t._v("声音消息")])],1)],1),"text"===t.formValidate.type?r("FormItem",{attrs:{label:"规则内容：",prop:"content"}},[r("Input",{staticStyle:{width:"90%"},attrs:{placeholder:"请填写规则内容"},model:{value:t.formValidate.data.content,callback:function(a){t.$set(t.formValidate.data,"content",a)},expression:"formValidate.data.content"}})],1):t._e(),"news"===t.formValidate.type?r("FormItem",{attrs:{label:"选取图文："}},[r("Button",{attrs:{type:"info"},on:{click:t.changePic}},[t._v("选择图文消息")])],1):t._e(),"image"===t.formValidate.type||"voice"===t.formValidate.type?r("FormItem",{attrs:{label:"image"===t.formValidate.type?"图片地址：":"语音地址：",prop:"src"}},[r("div",{staticClass:"acea-row row-middle"},[r("Input",{staticClass:"mr15",staticStyle:{width:"75%"},attrs:{readonly:"readonly",placeholder:"default size"},model:{value:t.formValidate.data.src,callback:function(a){t.$set(t.formValidate.data,"src",a)},expression:"formValidate.data.src"}}),r("Upload",{staticClass:"mr20",staticStyle:{"margin-top":"1px"},attrs:{"show-upload-list":!1,action:t.fileUrl,"on-success":t.handleSuccess,format:"image"===t.formValidate.type?t.formatImg:t.formatVoice,"max-size":2048,headers:t.header,"on-format-error":t.handleFormatError,"on-exceeded-size":t.handleMaxSize}},[r("Button",{attrs:{type:"primary"}},[t._v("上传")])],1)],1),r("span",{directives:[{name:"show",rawName:"v-show",value:"image"===t.formValidate.type,expression:"formValidate.type === 'image'"}]},[t._v("文件最大2Mb，支持bmp/png/jpeg/jpg/gif格式")]),r("span",{directives:[{name:"show",rawName:"v-show",value:"voice"===t.formValidate.type,expression:"formValidate.type === 'voice'"}]},[t._v("文件最大2Mb，支持mp3/wma/wav/amr格式,播放长度不超过60s")])]):t._e()],1)],1)]),r("Col",{attrs:{span:24}},[r("div",{staticClass:"acea-row row-center"},[r("Button",{staticClass:"mr20",attrs:{type:"primary"},on:{click:function(a){return t.submenus("formValidate")}}},[t._v("保存并发布\n                                ")])],1)])],1)],1)],1)],1)],1),r("Modal",{staticClass:"modelBox",attrs:{scrollable:"",title:"发送消息",width:"1200",height:"800","footer-hide":""},model:{value:t.modals,callback:function(a){t.modals=a},expression:"modals"}},[t.modals?r("news-category",{attrs:{scrollerHeight:t.scrollerHeight,contentTop:t.contentTop,contentWidth:t.contentWidth,maxCols:t.maxCols},on:{getCentList:t.getCentList}}):t._e()],1)],1)},i=[],s=e("a34a"),o=e.n(s),n=e("d708"),l=e("b562"),c=e("2f62"),d=e("c42b"),m=e("c276");function u(t,a,e,r,i,s,o){try{var n=t[s](o),l=n.value}catch(c){return void e(c)}n.done?a(l):Promise.resolve(l).then(r,i)}function p(t){return function(){var a=this,e=arguments;return new Promise((function(r,i){var s=t.apply(a,e);function o(t){u(s,r,i,o,n,"next",t)}function n(t){u(s,r,i,o,n,"throw",t)}o(void 0)}))}}function f(t,a){var e=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);a&&(r=r.filter((function(a){return Object.getOwnPropertyDescriptor(t,a).enumerable}))),e.push.apply(e,r)}return e}function h(t){for(var a=1;a<arguments.length;a++){var e=null!=arguments[a]?arguments[a]:{};a%2?f(e,!0).forEach((function(a){g(t,a,e[a])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(e)):f(e).forEach((function(a){Object.defineProperty(t,a,Object.getOwnPropertyDescriptor(e,a))}))}return t}function g(t,a,e){return a in t?Object.defineProperty(t,a,{value:e,enumerable:!0,configurable:!0,writable:!0}):t[a]=e,t}var v={name:"follow",components:{newsCategory:d["a"]},data:function(){var t=this,a=function(a,e,r){"text"===t.formValidate.type&&(""===t.formValidate.data.content?r(new Error("请填写规则内容")):r())},e=function(a,e,r){"image"===t.formValidate.type&&""===t.formValidate.data.src?r(new Error("请上传")):r()},r=function(a,e,r){0===t.labelarr.length?r(new Error("请输入后回车")):r()};return{delfromData:{},isShow:!1,maxCols:4,scrollerHeight:"600",contentTop:"130",contentWidth:"98%",modals:!1,val:"",formatImg:["jpg","jpeg","png","bmp","gif"],formatVoice:["mp3","wma","wav","amr"],header:{},formValidate:{status:1,type:"",key:this.$route.params.key||"",data:{content:"",src:"",list:[]},id:0},fileUrl:n["a"].apiBaseURL+"file/upload/1",ruleValidate:{val:[{required:!0,validator:r,trigger:"change"}],type:[{required:!0,message:"请选择消息类型",trigger:"change"}],content:[{required:!0,validator:a,trigger:"blur"}],src:[{required:!0,validator:e,trigger:"change"}]},labelarr:[]}},watch:{$route:function(t,a){this.$route.params.key||"0"!==this.$route.params.id?(this.formValidate.key=this.$route.params.key,this.details()):(this.labelarr=[],this.$refs["formValidate"].resetFields())}},computed:{imgUrl:function(){var t="/adminapi/",a=n["a"].apiBaseURL.indexOf(t);return n["a"].apiBaseURL.substring(0,a)}},mounted:function(){this.getToken(),(this.$route.params.key||this.$route.params.id&&"0"!==this.$route.params.id)&&this.details()},methods:h({},Object(c["b"])("admin/page",["close"]),{getCentList:function(t){this.formValidate.data.list=t.new,this.modals=!1},addlabel:function(){var t=this.labelarr.indexOf(this.val);-1===t&&this.labelarr.push(this.val),this.val=""},handleClose:function(t,a){var e=this.labelarr.indexOf(a);this.labelarr.splice(e,1)},details:function(){var t=this,a="",e={};this.$route.params.id?(a="app/wechat/keyword/"+this.$route.params.id,e={}):(a="app/wechat/reply",e={key:{key:this.formValidate.key}}),Object(l["e"])(a,e).then(function(){var a=p(o.a.mark((function a(e){var r,i;return o.a.wrap((function(a){while(1)switch(a.prev=a.next){case 0:r=e.data.info||{},i=r.data||{},t.formValidate={status:r.status,type:r.type,key:r.key,data:{content:i.content,src:i.src,list:i.list},id:r.id},t.$route.params.id&&(t.labelarr=t.formValidate.key.split(",")||[]);case 4:case"end":return a.stop()}}),a)})));return function(t){return a.apply(this,arguments)}}()).catch((function(a){t.$Message.error(a.msg)}))},changePic:function(){this.modals=!0},RuleFactor:function(t){switch(t){case"text":this.formValidate.data.src="",this.formValidate.data.list=[];break;case"news":this.formValidate.data.src="",this.formValidate.data.content="";break;default:this.formValidate.data.list=[],this.formValidate.data.content="",this.formValidate.data.src=""}},getToken:function(){this.header["Authori-zation"]="Bearer "+m["a"].cookies.get("token")},handleSuccess:function(t,a){200===t.status?(this.formValidate.data.src=t.data.src,this.$Message.success(t.msg)):this.$Message.error(t.msg)},handleFormatError:function(t){"image"===this.formValidate.type?this.$Message.warning("请上传bmp/png/jpeg/jpg/gif格式的图片"):this.$Message.warning("请上传mp3/wma/wav/amr格式的语音")},handleMaxSize:function(t){this.$Message.warning("请上传文件2M以内的文件")},submenus:function(t){var a=this;this.$refs[t].validate((function(t){if(!t)return!1;var e={};a.$route.params.id?(a.formValidate.key=a.labelarr.join(","),e={url:"app/wechat/keyword/"+a.$route.params.id,key:a.formValidate}):e={url:"app/wechat/keyword/"+a.formValidate.id,key:a.formValidate},Object(l["f"])(e).then(function(){var t=p(o.a.mark((function t(e){return o.a.wrap((function(t){while(1)switch(t.prev=t.next){case 0:a.operation(),a.$Message.success(e.msg);case 2:case"end":return t.stop()}}),t)})));return function(a){return t.apply(this,arguments)}}()).catch((function(t){a.$Message.error(t.msg)}))}))},operation:function(){var t=this;this.$route.params.id&&this.$Modal.confirm({title:"提示",content:"<p>是否继续添加？</p>",okText:"是",cancelText:"否",loading:!0,onOk:function(){setTimeout((function(){t.$Modal.remove(),t.labelarr=[],t.val="",t.$refs["formValidate"].resetFields()}),1e3)},onCancel:function(){setTimeout((function(){t.$Modal.remove(),t.$router.push({path:"/admin/app/wechat/reply/keyword"})}),500)}})}})},b=v,w=(e("19a68"),e("2877")),y=Object(w["a"])(b,r,i,!1,null,"7a8ba523",null);a["default"]=y.exports},47952:function(t,a,e){t.exports=e.p+"view_admin/img/head.cfd4b538.gif"},"58b1":function(t,a){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUAAAAAyCAIAAACib5WDAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjE1NEJCMUE0NzZGNDExRTVBOTBBQTZFOEFEMjc4NTkzIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjE1NEJCMUE1NzZGNDExRTVBOTBBQTZFOEFEMjc4NTkzIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MTU0QkIxQTI3NkY0MTFFNUE5MEFBNkU4QUQyNzg1OTMiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MTU0QkIxQTM3NkY0MTFFNUE5MEFBNkU4QUQyNzg1OTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4yWLBJAAABuklEQVR42uzcu0ocURzA4XWxMIWiQhJwtVhxMW0wEkWj+AwWgm9gJfgggpVPoElEUwUCKRNFJaQWsygWXvAKXlBZGw8KIiIJmWFnGPg+pjiryMIffpxzRLemUqnkUlUul0ulUg74f3kjAAEDAgYEDAIGBAwIGBAwCBgQMCBgEHAMlZub8BglJK825s/vHxzOfl4Ii9GR4devXhooZGYHPjo+mfk0f3l5FZ6wCC8NFDKzA+fz+aHB/scvDRQyE3BzU2N4DBEyeYQGBAxU5wi9sbm1+ut3W2shznucnp296Sx1tBeNGxINeG39z+jIcPy3+Tj3RcCQ9BG6ob7+fjE5NR2eaOugtdBi1pD0Dvzg6vo68hpIOeAXdXWR10CV1Pz9c6F/LC4P9PfGf5ufSysf+nqe/ZbPhYZq3YGfiHD7BdI/Qrv9QuYDdvsFd2B3YEjjDgxk+Aidu/sd1T9vueEUPTE+ZrhgBwai7sA7u3tPvhJtaz0/vzBrSDrg7ndvv377/vAX0dFs7+y+7+4ya0g64I72ov8iAndgQMCAgEHAgIABAYOAAQEDAgYEDAIGBAwIGBAwCBhIy60AAwBiy5esmSYLKgAAAABJRU5ErkJggg=="},9875:function(t,a,e){}}]);