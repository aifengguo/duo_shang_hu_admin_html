(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-5b560456"],{"28bd":function(e,t,a){"use strict";a.r(t);var i=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("div",{staticClass:"i-layout-page-header"},[a("PageHeader",{staticClass:"product_tabs",attrs:{"hidden-breadcrumb":""}},[a("div",{staticClass:"ivu-mt ivu-mb",attrs:{slot:"title"},slot:"title"},[a("router-link",{attrs:{to:{path:"/admin/marketing/store_combination/index"}}},[a("Button",{staticClass:"mr20",attrs:{icon:"ios-arrow-back",size:"small"}},[e._v("返回")])],1),a("span",{staticClass:"mr20",domProps:{textContent:e._s(e.$route.params.id?"编辑拼团商品":"添加拼团商品")}})],1)])],1),a("Card",{staticClass:"ivu-mt",attrs:{bordered:!1,"dis-hover":""}},[a("Row",{staticClass:"mt30 acea-row row-middle row-center",attrs:{type:"flex"}},[a("Col",{attrs:{span:"20"}},[a("Steps",{attrs:{current:e.current}},[a("Step",{attrs:{title:"选择拼团商品"}}),a("Step",{attrs:{title:"填写基础信息"}}),a("Step",{attrs:{title:"修改商品详情"}})],1)],1),a("Col",{attrs:{span:"23"}},[a("Form",{ref:"formValidate",staticClass:"form mt30",attrs:{model:e.formValidate,rules:e.ruleValidate,"label-width":e.labelWidth,"label-position":e.labelPosition},on:{"on-validate":e.validate},nativeOn:{submit:function(e){e.preventDefault()}}},[0===e.current?a("FormItem",{attrs:{label:"选择商品：",prop:"image_input"}},[a("div",{staticClass:"picBox",on:{click:e.changeGoods}},[e.formValidate.image?a("div",{staticClass:"pictrue"},[a("img",{directives:[{name:"lazy",rawName:"v-lazy",value:e.formValidate.image,expression:"formValidate.image"}]})]):a("div",{staticClass:"upLoad acea-row row-center-wrapper"},[a("Icon",{staticClass:"iconfont",attrs:{type:"ios-camera-outline",size:"26"}})],1)])]):e._e(),a("Row",{directives:[{name:"show",rawName:"v-show",value:1===e.current,expression:"current === 1"}],attrs:{type:"flex"}},[a("Col",{attrs:{span:"24"}},[a("FormItem",{attrs:{label:"商品主图：",prop:"image"}},[a("div",{staticClass:"picBox",on:{click:function(t){return e.modalPicTap("dan","danFrom")}}},[e.formValidate.image?a("div",{staticClass:"pictrue"},[a("img",{directives:[{name:"lazy",rawName:"v-lazy",value:e.formValidate.image,expression:"formValidate.image"}]})]):a("div",{staticClass:"upLoad acea-row row-center-wrapper"},[a("Icon",{staticClass:"iconfont",attrs:{type:"ios-camera-outline",size:"26"}})],1)])])],1),a("Col",{attrs:{span:"24"}},[a("FormItem",{attrs:{label:"商品轮播图：",prop:"images"}},[a("div",{staticClass:"acea-row"},[e._l(e.formValidate.images,(function(t,i){return a("div",{key:i,staticClass:"pictrue",attrs:{draggable:"true"},on:{dragstart:function(a){return e.handleDragStart(a,t)},dragover:function(a){return a.preventDefault(),e.handleDragOver(a,t)},dragenter:function(a){return e.handleDragEnter(a,t)},dragend:function(a){return e.handleDragEnd(a,t)}}},[a("img",{directives:[{name:"lazy",rawName:"v-lazy",value:t,expression:"item"}]}),a("Button",{staticClass:"btndel",attrs:{shape:"circle",icon:"md-close"},nativeOn:{click:function(t){return e.handleRemove(i)}}})],1)})),a("div",{staticClass:"upLoad acea-row row-center-wrapper",on:{click:function(t){return e.modalPicTap("duo")}}},[a("Icon",{staticClass:"iconfont",attrs:{type:"ios-camera-outline",size:"26"}})],1)],2)])],1),a("Col",{attrs:{span:"24"}},[a("Col",e._b({},"Col",e.grid,!1),[a("FormItem",{attrs:{label:"拼团名称：",prop:"title","label-for":"title"}},[a("Input",{attrs:{placeholder:"请输入拼团名称","element-id":"title"},model:{value:e.formValidate.title,callback:function(t){e.$set(e.formValidate,"title",t)},expression:"formValidate.title"}})],1)],1)],1),a("Col",{attrs:{span:"24"}},[a("Col",e._b({},"Col",e.grid,!1),[a("FormItem",{attrs:{label:"拼团简介：",prop:"info","label-for":"info"}},[a("Input",{attrs:{placeholder:"请输入拼团简介",type:"textarea",rows:4,"element-id":"info"},model:{value:e.formValidate.info,callback:function(t){e.$set(e.formValidate,"info",t)},expression:"formValidate.info"}})],1)],1)],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"拼团时间：",prop:"section_time"}},[a("DatePicker",{directives:[{name:"width",rawName:"v-width",value:"100%",expression:"'100%'"}],attrs:{type:"datetimerange",format:"yyyy-MM-dd HH:mm",placeholder:"请选择活动时间",value:e.formValidate.section_time},on:{"on-change":e.onchangeTime},model:{value:e.formValidate.section_time,callback:function(t){e.$set(e.formValidate,"section_time",t)},expression:"formValidate.section_time"}})],1)],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"单位：",prop:"unit_name","label-for":"unit_name"}},[a("Input",{attrs:{placeholder:"请输入单位","element-id":"unit_name"},model:{value:e.formValidate.unit_name,callback:function(t){e.$set(e.formValidate,"unit_name",t)},expression:"formValidate.unit_name"}})],1)],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"拼团时效(单位 小时)：",prop:"effective_time"}},[a("InputNumber",{directives:[{name:"width",rawName:"v-width",value:"100%",expression:"'100%'"}],attrs:{placeholder:"请输入拼团时效(单位 小时)","element-id":"effective_time"},model:{value:e.formValidate.effective_time,callback:function(t){e.$set(e.formValidate,"effective_time",t)},expression:"formValidate.effective_time"}})],1)],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"拼团人数：",prop:"people"}},[a("InputNumber",{directives:[{name:"width",rawName:"v-width",value:"100%",expression:"'100%'"}],attrs:{placeholder:"请输入拼团人数",precision:0,"element-id":"people"},model:{value:e.formValidate.people,callback:function(t){e.$set(e.formValidate,"people",t)},expression:"formValidate.people"}})],1)],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"排序："}},[a("InputNumber",{directives:[{name:"width",rawName:"v-width",value:"100%",expression:"'100%'"}],attrs:{placeholder:"请输入排序","element-id":"sort",precision:0},model:{value:e.formValidate.sort,callback:function(t){e.$set(e.formValidate,"sort",t)},expression:"formValidate.sort"}})],1)],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"运费模板：",prop:"temp_id"}},[a("div",[a("Select",{model:{value:e.formValidate.temp_id,callback:function(t){e.$set(e.formValidate,"temp_id",t)},expression:"formValidate.temp_id"}},e._l(e.templateList,(function(t){return a("Option",{key:t.id,attrs:{value:t.id}},[e._v(e._s(t.name))])})),1)],1)])],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"热门推荐：",props:"is_hot","label-for":"is_hot"}},[a("RadioGroup",{attrs:{"element-id":"is_hot"},model:{value:e.formValidate.is_host,callback:function(t){e.$set(e.formValidate,"is_host",t)},expression:"formValidate.is_host"}},[a("Radio",{staticClass:"radio",attrs:{label:1}},[e._v("开启")]),a("Radio",{attrs:{label:0}},[e._v("关闭")])],1)],1)],1),a("Col",e._b({},"Col",e.grid2,!1),[a("FormItem",{attrs:{label:"活动状态：",props:"is_show","label-for":"is_show"}},[a("RadioGroup",{attrs:{"element-id":"status"},model:{value:e.formValidate.is_show,callback:function(t){e.$set(e.formValidate,"is_show",t)},expression:"formValidate.is_show"}},[a("Radio",{staticClass:"radio",attrs:{label:1}},[e._v("开启")]),a("Radio",{attrs:{label:0}},[e._v("关闭")])],1)],1)],1),a("Col",{attrs:{span:"24"}},[a("FormItem",{attrs:{label:"规格选择："}},[a("Table",{attrs:{data:e.specsData,columns:e.columns,border:""},on:{"on-selection-change":e.changeCheckbox},scopedSlots:e._u([{key:"pic",fn:function(t){var i=t.row,r=t.index;return[a("div",{staticClass:"acea-row row-middle row-center-wrapper",on:{click:function(t){return e.modalPicTap("dan","danTable",r)}}},[i.pic?a("div",{staticClass:"pictrue pictrueTab"},[a("img",{directives:[{name:"lazy",rawName:"v-lazy",value:i.pic,expression:"row.pic"}]})]):a("div",{staticClass:"upLoad pictrueTab acea-row row-center-wrapper"},[a("Icon",{staticClass:"iconfont",attrs:{type:"ios-camera-outline",size:"21"}})],1)])]}}])})],1)],1)],1),a("Row",{directives:[{name:"show",rawName:"v-show",value:2===e.current,expression:"current === 2"}]},[a("Col",{attrs:{span:"24"}},[a("FormItem",{attrs:{label:"内容："}},[a("vue-ueditor-wrap",{staticStyle:{width:"90%"},attrs:{config:e.myConfig},on:{beforeInit:e.addCustomDialog},model:{value:e.formValidate.description,callback:function(t){e.$set(e.formValidate,"description",t)},expression:"formValidate.description"}})],1)],1)],1),a("FormItem",[a("Button",{directives:[{name:"show",rawName:"v-show",value:0!==e.current,expression:"current!==0"}],staticClass:"submission mr15",attrs:{disabled:e.$route.params.id&&1===e.current},on:{click:e.step}},[e._v("上一步")]),a("Button",{staticClass:"submission",attrs:{type:"primary"},domProps:{textContent:e._s(2===e.current?"提交":"下一步")},on:{click:function(t){return e.next("formValidate")}}})],1)],1),e.spinShow?a("Spin",{attrs:{size:"large",fix:""}}):e._e()],1)],1)],1),a("Modal",{staticClass:"paymentFooter",attrs:{title:"商品列表",footerHide:"",scrollable:"",width:"900"},on:{"on-cancel":e.cancel},model:{value:e.modals,callback:function(t){e.modals=t},expression:"modals"}},[e.modals?a("goods-list",{ref:"goodslist",on:{getProductId:e.getProductId}}):e._e()],1),a("Modal",{attrs:{width:"60%",scrollable:"","footer-hide":"",closable:"",title:"上传商品图","mask-closable":!1,"z-index":1},model:{value:e.modalPic,callback:function(t){e.modalPic=t},expression:"modalPic"}},[e.modalPic?a("uploadPictures",{attrs:{isChoice:e.isChoice,gridBtn:e.gridBtn,gridPic:e.gridPic},on:{getPic:e.getPic,getPicD:e.getPicD}}):e._e()],1)],1)},r=[],o=a("a34a"),s=a.n(o),n=a("2f62"),l=a("c4ad"),c=a("ef0d"),d=a("6625"),m=a.n(d),u=a("b0e7"),p=a("b7be"),f=a("c4c8");function g(e){return v(e)||b(e)||h()}function h(){throw new TypeError("Invalid attempt to spread non-iterable instance")}function b(e){if(Symbol.iterator in Object(e)||"[object Arguments]"===Object.prototype.toString.call(e))return Array.from(e)}function v(e){if(Array.isArray(e)){for(var t=0,a=new Array(e.length);t<e.length;t++)a[t]=e[t];return a}}function w(e,t,a,i,r,o,s){try{var n=e[o](s),l=n.value}catch(c){return void a(c)}n.done?t(l):Promise.resolve(l).then(i,r)}function _(e){return function(){var t=this,a=arguments;return new Promise((function(i,r){var o=e.apply(t,a);function s(e){w(o,i,r,s,n,"next",e)}function n(e){w(o,i,r,s,n,"throw",e)}s(void 0)}))}}function C(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);t&&(i=i.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,i)}return a}function y(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?C(a,!0).forEach((function(t){V(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):C(a).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}function V(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var x={name:"storeCombinationCreate",components:{UeditorWrap:c["a"],goodsList:l["default"],uploadPictures:u["a"],VueUeditorWrap:m.a},data:function(){return{spinShow:!1,isChoice:"",current:0,modalPic:!1,grid:{xl:12,lg:20,md:24,sm:24,xs:24},grid2:{xl:8,lg:8,md:12,sm:24,xs:24},gridPic:{xl:6,lg:8,md:12,sm:12,xs:12},gridBtn:{xl:4,lg:8,md:8,sm:8,xs:8},myConfig:{autoHeightEnabled:!1,initialFrameHeight:500,initialFrameWidth:"100%",UEDITOR_HOME_URL:"/admin/UEditor/",serverUrl:""},modals:!1,modal_loading:!1,images:[],templateList:[],columns:[],specsData:[],picTit:"",tableIndex:0,formValidate:{images:[],info:"",title:"",image:"",unit_name:"",price:0,effective_time:24,stock:1,sales:0,sort:0,postage:0,is_postage:0,is_host:0,is_show:0,section_time:[],description:"",id:0,product_id:0,people:2,temp_id:"",attrs:[],items:[]},ruleValidate:{image:[{required:!0,message:"请选择主图",trigger:"change"}],images:[{required:!0,type:"array",message:"请选择主图",trigger:"change"},{type:"array",min:1,message:"Choose two hobbies at best",trigger:"change"}],title:[{required:!0,message:"请输入拼团名称",trigger:"blur"}],info:[{required:!0,message:"请输入拼团简介",trigger:"blur"}],section_time:[{required:!0,type:"array",message:"请选择活动时间",trigger:"change"}],unit_name:[{required:!0,message:"请输入单位",trigger:"blur"}],price:[{required:!0,type:"number",message:"请输入拼团价",trigger:"blur"}],cost:[{required:!0,type:"number",message:"请输入成本价",trigger:"blur"}],stock:[{required:!0,type:"number",message:"请输入库存",trigger:"blur"}],give_integral:[{required:!0,type:"number",message:"请输入赠送积分",trigger:"blur"}],effective_time:[{required:!0,type:"number",message:"请输入拼团时效(单位 小时)",trigger:"blur"}],people:[{required:!0,type:"number",message:"请输入拼团人数",trigger:"blur"}],temp_id:[{required:!0,message:"请选择运费模板",trigger:"change",type:"number"}]}}},computed:y({},Object(n["e"])("admin/layout",["isMobile"]),{labelWidth:function(){return this.isMobile?void 0:155},labelPosition:function(){return this.isMobile?"top":"right"}}),mounted:function(){this.$route.params.id&&(this.current=1,this.getInfo()),this.productGetTemplate()},methods:{productAttrs:function(e){var t=this;Object(p["r"])(e.id,3).then((function(e){var a=e.data.info,i={type:"selection",width:60,align:"center"};t.specsData=a.attrs,t.specsData.forEach((function(e,a){t.$set(t.specsData[a],"id",a)})),t.formValidate.items=a.items,t.columns=a.header,t.columns.unshift(i),t.inputChange(a)})).catch((function(e){t.$Message.error(e.msg)}))},inputChange:function(e){var t=this,a=[];e.header.forEach((function(e,t){1===e.type&&a.push({index:t,key:e.key,title:e.title})})),a.forEach((function(e,a){var i=e.title,r=e.key,o={title:i,key:r,align:"center",minWidth:100,render:function(e,a){return e("div",[e("InputNumber",{props:{min:0,value:"price"===r?a.row.price:a.row.quota},on:{"on-change":function(e){"price"===r?a.row.price=e:a.row.quota=e,t.specsData[a.index]=a.row,t.formValidate.attrs&&t.formValidate.attrs.length&&t.formValidate.attrs.forEach((function(e,i){e.id===a.row.id&&t.formValidate.attrs.splice(i,1,a.row)}))}}})])}};t.columns.splice(e.index,1,o)}))},changeCheckbox:function(e){this.formValidate.attrs=e},productGetTemplate:function(){var e=this;Object(f["o"])().then((function(t){e.templateList=t.data}))},validate:function(e,t,a){!1===t&&this.$Message.error(a)},getProductId:function(e){var t=this;this.modal_loading=!1,this.modals=!1,setTimeout((function(){t.formValidate={images:e.slider_image,info:e.store_info,title:e.store_name,image:e.image,unit_name:e.unit_name,price:0,effective_time:24,stock:e.stock,sales:e.sales,sort:e.sort,postage:e.postage,is_postage:e.is_postage,is_host:e.is_hot,is_show:0,section_time:[],description:e.description,id:0,people:2,product_id:e.id,temp_id:e.temp_id},t.productAttrs(e)}),500)},cancel:function(){this.modals=!1},onchangeTime:function(e){this.formValidate.section_time=e},getInfo:function(){var e=this;this.spinShow=!0,Object(p["f"])(this.$route.params.id).then(function(){var t=_(s.a.mark((function t(a){var i,r,o,n,l,c;return s.a.wrap((function(t){while(1)switch(t.prev=t.next){case 0:for(c in i=e,r=a.data.info,o={type:"selection",width:60,align:"center"},e.formValidate=r,e.$set(e.formValidate,"items",r.attrs.items),e.columns=r.attrs.header,e.columns.unshift(o),e.specsData=r.attrs.value,i.specsData.forEach((function(e,t){i.$set(i.specsData[t],"id",t)})),n=r.attrs,l=[],r.attrs.value)r.attrs.value[c]._checked&&l.push(r.attrs.value[c]);i.formValidate.attrs=l,i.inputChange(n),e.spinShow=!1;case 15:case"end":return t.stop()}}),t)})));return function(e){return t.apply(this,arguments)}}()).catch((function(t){e.spinShow=!1,e.$Message.error(t.msg)}))},next:function(e){var t=this,a=this;2===this.current?this.$refs[e].validate((function(e){if(!e)return!1;if(!a.formValidate.attrs)return a.$Message.error("请选择属性规格");for(var i in a.formValidate.attrs)if(a.formValidate.attrs[i].quota<=0)return a.$Message.error("拼团限量必须大于0");t.formValidate.id=Number(t.$route.params.id)||0,Object(p["e"])(t.formValidate).then(function(){var e=_(s.a.mark((function e(a){return s.a.wrap((function(e){while(1)switch(e.prev=e.next){case 0:t.$Message.success(a.msg),setTimeout((function(){t.$router.push({path:"/admin/marketing/store_combination/index"})}),500);case 2:case"end":return e.stop()}}),e)})));return function(t){return e.apply(this,arguments)}}()).catch((function(e){t.$Message.error(e.msg)}))})):this.formValidate.image?this.current+=1:this.$Message.warning("请选择商品")},step:function(){this.current--},getContent:function(e){this.formValidate.description=e},modalPicTap:function(e,t,a){this.modalPic=!0,this.isChoice="dan"===e?"单选":"多选",this.picTit=t,this.tableIndex=a},getPic:function(e){switch(this.picTit){case"danFrom":this.formValidate.image=e.att_dir;break;default:this.formValidate.attrs&&this.formValidate.attrs.length&&this.$set(this.specsData[this.tableIndex],"_checked",!0),this.specsData[this.tableIndex].pic=e.att_dir}this.modalPic=!1},getPicD:function(e){var t=this;this.images=e,this.images.map((function(e){t.formValidate.images.push(e.att_dir)})),this.modalPic=!1},handleRemove:function(e){this.images.splice(e,1),this.formValidate.images.splice(e,1)},changeGoods:function(){this.modals=!0},handleDragStart:function(e,t){this.dragging=t},handleDragEnd:function(e,t){this.dragging=null},handleDragOver:function(e){e.dataTransfer.dropEffect="move"},handleDragEnter:function(e,t){if(e.dataTransfer.effectAllowed="move",t!==this.dragging){var a=g(this.formValidate.images),i=a.indexOf(this.dragging),r=a.indexOf(t);a.splice.apply(a,[r,0].concat(g(a.splice(i,1)))),this.formValidate.images=a}},addCustomDialog:function(e){window.UE.registerUI("test-dialog",(function(e,t){var a=new window.UE.ui.Dialog({iframeUrl:"/admin/widget.images/index.html?fodder=dialog",editor:e,name:t,title:"上传图片",cssRules:"width:1200px;height:500px;padding:20px;"});this.dialog=a;var i=new window.UE.ui.Button({name:"dialog-button",title:"上传图片",cssRules:"background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;",onclick:function(){a.render(),a.open()}});return i}),37)}}},k=x,P=(a("88e3"),a("2877")),I=Object(P["a"])(k,i,r,!1,null,"555d1802",null);t["default"]=I.exports},"88e3":function(e,t,a){"use strict";var i=a("bbdf"),r=a.n(i);r.a},bbdf:function(e,t,a){}}]);