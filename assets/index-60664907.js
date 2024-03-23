import{_ as B,o as a,c as l,a as Q,u as A,b as C,r as z,d as J,e as K,f as E,g as e,h as n,w as G,i as D,t as k,n as _,T as Z,p as H,j as W,k as q,l as U,F as M,m as O,q as V,s as N,v as ee,x as T,y as F,z as S,A as j,B as se,C as te,D as oe,E as X,G as ae,H as ne,I as le}from"./index-c443dcd7.js";const ie={},re={class:"footer"},ce=Q('<div class="container-fluid"><div class="row justify-content-center"><div class="col-md-auto px-1"><p class="text-center">Copyright © 2022 <b>Innovation Team TR VII</b>.</p></div><div class="col-md-auto px-1"><p class="text-center">All rights reserved.</p></div></div></div>',1),de=[ce];function ue(p,v){return a(),l("footer",re,de)}const Y=B(ie,[["render",ue]]),_e="/assets/img/user1.png";const I=p=>(H("data-v-4c3c1f06"),p=p(),W(),p),pe={class:"sidebar-user text-center"},ve={class:"f-w-700 m-2 text-center tw-px-[15px]"},he=I(()=>e("span",{class:"f-w-700 ms-2"},"Update Profil",-1)),me=I(()=>e("span",{class:"f-w-700 ms-2"},"Ganti Password",-1)),ge=I(()=>e("span",{class:"f-w-700 ms-2"},"Log Out",-1)),fe=I(()=>e("img",{class:"img-90 rounded-circle mx-auto",src:_e,alt:""},null,-1)),be=I(()=>e("div",{class:"badge-bottom"},[e("span",{class:"badge badge-primary"},"New")],-1)),ye={class:"mt-3 mb-1 f-14 f-w-600 font-primary"},we={class:"mb-0 font-roboto text-uppercase f-w-700"},ke={class:"mb-0 font-roboto text-capitalize"},$e={__name:"DashboardSidebarUser",emits:["updateUser","updatePassword","logout"],setup(p,{emit:v}){const d=A(),h=C(()=>d.name),r=C(()=>d.location),w=C(()=>d.role),b=z(!1),u=()=>b.value=!1;J(()=>{document.body.addEventListener("click",u)}),K(()=>{document.body.removeEventListener("click",u)});const x=C(()=>d.userData),P=z(!1),m=()=>{P.value=!0,d.fetchUserData(!1,()=>{P.value=!1,v("updateUser",x.value)})};return(y,c)=>{const o=E("vue-feather"),t=E("VueFeather");return a(),l("div",pe,[e("a",{class:"setting-primary",role:"button",title:"setting",onClick:c[0]||(c[0]=G(i=>b.value=!b.value,["stop"]))},[n(o,{type:"settings"})]),(a(),D(Z,{to:"body"},[e("ul",{class:_([{show:b.value},"setting-wrapper"])},[e("li",null,[e("p",ve,k(h.value),1)]),e("li",null,[e("a",{role:"button",title:"update profil",onClick:m},[P.value?(a(),D(t,{key:0,type:"loader",animation:"spin",size:"1.5em"})):(a(),D(t,{key:1,type:"user",size:"1.5em"})),he])]),e("li",null,[e("a",{role:"button",title:"ganti password",onClick:c[1]||(c[1]=i=>y.$emit("updatePassword"))},[n(t,{type:"key",size:"1.5em"}),me])]),e("li",null,[e("a",{role:"button",onClick:c[2]||(c[2]=G(i=>y.$emit("logout"),["stop"])),title:"log out"},[n(t,{type:"lock",size:"1.5em"}),ge])])],2)])),fe,be,e("h6",ye,k(h.value),1),e("p",we,k(r.value),1),e("p",ke,"role "+k(w.value),1)])}}},xe=B($e,[["__scopeId","data-v-4c3c1f06"]]);const R=p=>(H("data-v-b5e334da"),p=p(),W(),p),Se={class:"main-navbar"},Pe=R(()=>e("div",{class:"left-arrow",id:"left-arrow"},[e("i",{"data-feather":"arrow-left"})],-1)),Ce={id:"mainnav"},De={class:"nav-menu custom-scrollbar"},Ue=R(()=>e("li",{class:"back-btn"},[e("div",{class:"mobile-back text-end"},[e("span",null,"Back"),e("i",{class:"fa fa-angle-right ps-2","aria-hidden":"true"})])],-1)),Ve={key:0,class:"sidebar-main-title"},Le=R(()=>e("div",null,[e("h6",{class:"ms-2"},"Menu GePEE")],-1)),ze=[Le],Ee=["onClick"],Ne={class:"according-menu"},Me={key:2,class:"nav-submenu menu-content"},Oe={key:1,class:"sidebar-main-title"},Re=R(()=>e("div",null,[e("h6",{class:"ms-2"},"Menu OX ISP")],-1)),Ie=[Re],Be=["onClick"],Ae={class:"according-menu"},Te={key:2,class:"nav-submenu menu-content"},Fe={key:2,class:"sidebar-main-title"},je=R(()=>e("div",null,[e("h6",{class:"ms-2"},"Menu Management")],-1)),Ge=[je],He=["onClick"],We={class:"according-menu"},qe={key:2,class:"nav-submenu menu-content"},Je=R(()=>e("div",{class:"right-arrow",id:"right-arrow"},[e("i",{"data-feather":"arrow-right"})],-1)),Ke={__name:"DashboardSidebarMenu",setup(p){const v=A();C(()=>v.role);const d=q(),h=o=>({gepee:o.filter(t=>t.category=="gepee"),oxisp:o.filter(t=>t.category=="oxisp"),management:o.filter(t=>t.category=="management")}),r=C(()=>{const o=v.level,t=v.locationId,i=v.role,g=d.menuItems.filter(s=>s.roles.indexOf(i)<0?!1:(Array.isArray(s.child)&&(s.child=s.child.filter($=>Array.isArray($.roles)?$.roles.indexOf(i)>=0:!0)),!0)).map(s=>{if(!o||o=="nasional"||!t)return s;if(s.key=="gepee_evidence"){const f=JSON.parse(JSON.stringify(s));return f.to=`${s.to}/${o}/${t}`,f}return s});return h(g)}),w=C(()=>d.menuActKeys),b=(o,t)=>{const i=["gepee","oxisp","management"],g=i.indexOf(o);if(g<=0)return t;for(let s=0;s<g;s++)t+=r.value[i[s]].length;return t},u=z([]),x=()=>{Object.entries(r.value).forEach(([o,t])=>{let i=-1;for(let g=0;g<t.length;g++)i=b(o,g),t[g]&&w.value.length>0&&t[g].key==w.value[0]&&(u.value=[i])})},P=(o,t)=>{t=b(o,t),u.value.indexOf(t)<0?u.value=[...u.value,t]:u.value=u.value.filter(s=>s!==t)};x();const m=(o,t,i)=>(i=b(o,i),{dropdown:t,expand:u.value.indexOf(i)>=0}),y=(o,t)=>({active:o==w.value[0],"on-development-menu":t}),c=o=>({active:o===w.value[1]});return(o,t)=>{const i=E("vue-feather"),g=E("RouterLink");return a(),l("nav",null,[e("div",Se,[Pe,e("div",Ce,[e("ul",De,[Ue,r.value.gepee.length>0?(a(),l("li",Ve,ze)):U("",!0),(a(!0),l(M,null,O(r.value.gepee,(s,$)=>(a(),l("li",{class:_([m("gepee",s.child,$),"py-1"])},[s.child?(a(),l("a",{key:1,onClick:f=>P("gepee",$),class:_([y(s.key,s.isDev),"nav-link menu-title"]),role:"button"},[n(i,{type:s.icon,size:"1.2rem",class:"me-2"},null,8,["type"]),e("span",null,k(s.title),1),e("div",Ne,[n(i,{type:"chevron-right"})])],10,Ee)):(a(),D(g,{key:0,to:s.to,class:_([y(s.key,s.isDev),"nav-link"])},{default:V(()=>[n(i,{type:s.icon,size:"1.2rem",class:"me-2"},null,8,["type"]),e("span",null,k(s.title),1)]),_:2},1032,["to","class"])),s.child?(a(),l("ul",Me,[(a(!0),l(M,null,O(s.child,f=>(a(),l("li",null,[n(g,{to:f.to,class:_(c(f.key))},{default:V(()=>[N(k(f.title),1)]),_:2},1032,["to","class"])]))),256))])):U("",!0)],2))),256)),r.value.oxisp.length>0?(a(),l("li",Oe,Ie)):U("",!0),(a(!0),l(M,null,O(r.value.oxisp,(s,$)=>(a(),l("li",{class:_([m("oxisp",s.child,$),"py-1"])},[s.child?(a(),l("a",{key:1,onClick:f=>P("oxisp",$),class:_([y(s.key,s.isDev),"nav-link menu-title"]),role:"button"},[n(i,{type:s.icon,size:"1.2rem",class:"me-2"},null,8,["type"]),e("span",null,k(s.title),1),e("div",Ae,[n(i,{type:"chevron-right"})])],10,Be)):(a(),D(g,{key:0,to:s.to,class:_([y(s.key,s.isDev),"nav-link"])},{default:V(()=>[n(i,{type:s.icon,size:"1.2rem",class:"me-2"},null,8,["type"]),e("span",null,k(s.title),1)]),_:2},1032,["to","class"])),s.child?(a(),l("ul",Te,[(a(!0),l(M,null,O(s.child,f=>(a(),l("li",null,[n(g,{to:f.to,class:_(c(f.key))},{default:V(()=>[N(k(f.title),1)]),_:2},1032,["to","class"])]))),256))])):U("",!0)],2))),256)),r.value.management.length>0?(a(),l("li",Fe,Ge)):U("",!0),(a(!0),l(M,null,O(r.value.management,(s,$)=>(a(),l("li",{class:_([m("management",s.child,$),"py-1"])},[s.child?(a(),l("a",{key:1,onClick:f=>P("management",$),class:_([y(s.key,s.isDev),"nav-link menu-title"]),role:"button"},[n(i,{type:s.icon,size:"1.2rem",class:"me-2"},null,8,["type"]),e("span",null,k(s.title),1),e("div",We,[n(i,{type:"chevron-right"})])],10,He)):(a(),D(g,{key:0,to:s.to,class:_([y(s.key,s.isDev),"nav-link"])},{default:V(()=>[n(i,{type:s.icon,size:"1.2rem",class:"me-2"},null,8,["type"]),e("span",null,k(s.title),1)]),_:2},1032,["to","class"])),s.child?(a(),l("ul",qe,[(a(!0),l(M,null,O(s.child,f=>(a(),l("li",null,[n(g,{to:f.to,class:_(c(f.key))},{default:V(()=>[N(k(f.title),1)]),_:2},1032,["to","class"])]))),256))])):U("",!0)],2))),256))])]),Je])])}}},Xe=B(Ke,[["__scopeId","data-v-b5e334da"]]),Ye=["onSubmit"],Qe={class:"mb-4"},Ze=e("label",{for:"inputOldPass"},[N("Masukkan password lama "),e("span",{class:"text-danger"},"*")],-1),es={class:"mb-4"},ss=e("label",{for:"inputNewPass1"},[N("Masukkan password baru "),e("span",{class:"text-danger"},"*")],-1),ts={class:"mb-5"},os=e("label",{for:"inputNewPass2"},[N("Masukkan ulang password baru "),e("span",{class:"text-danger"},"*")],-1),as={key:0,class:"mb-0 font-danger ms-4"},ns={class:"d-flex justify-content-between align-items-end"},ls={__name:"DialogUpdatePassword",emits:["close"],setup(p,{emit:v}){const d=z(!0),h=()=>{d.value=!1,v("close")},{data:r,v$:w}=ee({oldPass:{required:T},newPass1:{required:T},newPass2:{required:T}}),b=z(!1),u=z(!1),x=C(()=>u.value?r.newPass1===r.newPass2:!0),P=A(),m=q(),y=async()=>{if(u.value=!0,!await w.value.$validate()||!x.value)return!1;const o={old_password:r.oldPass,new_password:r.newPass1};b.value=!0,P.updatePassword(o,t=>{b.value=!1,t.success&&(m.showToast("Update Password","Berhasil menyimpan password baru.",!0),d.value=!1)})};return(c,o)=>(a(),D(S(se),{header:"Update Password",visible:d.value,"onUpdate:visible":o[4]||(o[4]=t=>d.value=t),modal:"",draggable:"",onAfterHide:h},{default:V(()=>[e("form",{onSubmit:G(y,["prevent"]),class:"p-4"},[e("div",Qe,[Ze,F(e("input",{type:"password","onUpdate:modelValue":o[0]||(o[0]=t=>S(r).oldPass=t),id:"inputOldPass",class:_([{"is-invalid":u.value&&S(w).oldPass.$invalid},"form-control"]),autofocus:""},null,2),[[j,S(r).oldPass]])]),e("div",es,[ss,F(e("input",{type:"password","onUpdate:modelValue":o[1]||(o[1]=t=>S(r).newPass1=t),id:"inputNewPass1",class:_([{"is-invalid":u.value&&S(w).newPass1.$invalid},"form-control"])},null,2),[[j,S(r).newPass1]])]),e("div",ts,[os,F(e("input",{type:"password","onUpdate:modelValue":o[2]||(o[2]=t=>S(r).newPass2=t),id:"inputNewPass2",class:_([{"is-invalid":u.value&&S(w).newPass2.$invalid},"form-control"])},null,2),[[j,S(r).newPass2]]),x.value?U("",!0):(a(),l("p",as,"Password baru tidak cocok"))]),e("div",ns,[e("button",{type:"submit",class:_([{"btn-loading":b.value},"btn btn-success btn-lg"])},"Update Password",2),e("button",{type:"button",onClick:o[3]||(o[3]=t=>d.value=!1),class:"btn btn-secondary"},"Batalkan")])],40,Ye)]),_:1},8,["visible"]))}};const L=p=>(H("data-v-a17a54f7"),p=p(),W(),p),is={class:"page-wrapper compact-wrapper viho-theme",id:"pageWrapper"},rs={class:"main-header-right row m-0"},cs={class:"main-header-left"},ds={class:"logo-wrapper"},us=L(()=>e("img",{src:X,class:"img-fluid brand-logo",alt:""},null,-1)),_s={class:"dark-logo-wrapper"},ps=L(()=>e("img",{src:X,class:"img-fluid brand-logo",alt:""},null,-1)),vs={class:"left-menu-header col"},hs={class:"form-inline search-form"},ms={class:"search-bg"},gs=L(()=>e("input",{class:"form-control-plaintext",placeholder:"Search here....."},null,-1)),fs={class:"d-sm-none mobile-search search-bg"},bs={class:"nav-right col pull-right right-menu p-0"},ys={class:"nav-menus"},ws={class:"text-dark",href:"#!",onclick:"javascript:toggleFullScreen()"},ks={class:"onhover-dropdown"},$s={class:"notification-box"},xs=L(()=>e("span",{class:"dot-animated"},null,-1)),Ss={class:"notification-dropdown onhover-show-div"},Ps=L(()=>e("li",null,[e("p",{class:"f-w-700 mb-0"},[N("You have 3 Notifications"),e("span",{class:"pull-right badge badge-primary badge-pill"},"4")])],-1)),Cs={class:"noti-primary"},Ds={class:"media"},Us={class:"notification-bg bg-light-primary"},Vs=L(()=>e("div",{class:"media-body"},[e("p",null,"Delivery processing "),e("span",null,"10 minutes ago")],-1)),Ls={class:"noti-secondary"},zs={class:"media"},Es={class:"notification-bg bg-light-secondary"},Ns=L(()=>e("div",{class:"media-body"},[e("p",null,"Order Complete"),e("span",null,"1 hour ago")],-1)),Ms={class:"noti-success"},Os={class:"media"},Rs={class:"notification-bg bg-light-success"},Is=L(()=>e("div",{class:"media-body"},[e("p",null,"Tickets Generated"),e("span",null,"3 hour ago")],-1)),Bs={class:"noti-danger"},As={class:"media"},Ts={class:"notification-bg bg-light-danger"},Fs=L(()=>e("div",{class:"media-body"},[e("p",null,"Delivery Complete"),e("span",null,"6 hour ago")],-1)),js={class:"onhover-dropdown p-0"},Gs={class:"d-lg-none mobile-toggle pull-right w-auto"},Hs={class:"page-body-wrapper"},Ws={class:"page-body"},qs={__name:"Dashboard",setup(p){const v=q(),d=C(()=>v.hideSidebar),h=te();h.beforeEach(()=>{v.resetSidebarVisibility()});const r=()=>v.resetSidebarVisibility();J(()=>window.addEventListener("resize",r)),K(()=>window.addEventListener("resize",r)),v.resetSidebarVisibility();const w=A(),b=()=>{confirm("Anda akan keluar dari DENSUS. Lanjutkan?")&&(w.logout(),h.push("/login"))},u=z(!1),x=z(null);return(P,m)=>{const y=E("RouterLink"),c=E("vue-feather"),o=E("RouterView");return a(),l("div",is,[e("div",{class:_([{close_icon:d.value},"page-main-header"])},[e("div",rs,[e("div",cs,[e("div",ds,[n(y,{to:"/",title:"Dashboard"},{default:V(()=>[us]),_:1})]),e("div",_s,[n(y,{to:"/",title:"Dashboard"},{default:V(()=>[ps]),_:1})]),e("div",{onClick:m[0]||(m[0]=t=>S(v).toggleSidebarVisibility()),class:"toggle-sidebar",tabindex:"0",role:"button","aria-pressed":"false",title:"Toggle Sidebar"},[n(c,{type:"align-center",class:"status_toggle middle"})])]),e("div",vs,[e("ul",null,[e("li",null,[e("form",hs,[e("div",ms,[n(c,{type:"search",strokeWidth:"3.8",style:{width:"0.9rem"}}),gs])]),e("span",fs,[n(c,{type:"search",strokeWidth:"3.8",style:{width:"0.9rem"}})])])])]),e("div",bs,[e("ul",ys,[e("li",null,[e("a",ws,[n(c,{type:"maximize"})])]),e("li",ks,[e("div",$s,[n(c,{type:"bell"}),xs]),e("ul",Ss,[Ps,e("li",Cs,[e("div",Ds,[e("span",Us,[n(c,{type:"activity"})]),Vs])]),e("li",Ls,[e("div",zs,[e("span",Es,[n(c,{type:"check-circle"})]),Ns])]),e("li",Ms,[e("div",Os,[e("span",Rs,[n(c,{type:"file-text"})]),Is])]),e("li",Bs,[e("div",As,[e("span",Ts,[n(c,{type:"user-check"})]),Fs])])])]),e("li",js,[e("button",{onClick:b,class:"btn btn-primary-light btn-icon"},[n(c,{type:"log-out"}),N("Log out")])])])]),e("div",Gs,[n(c,{type:"more-horizontal"})])])],2),e("div",Hs,[e("header",{class:_([{close_icon:!d.value},"main-nav"])},[n(xe,{onUpdateUser:m[1]||(m[1]=t=>x.value=t),onUpdatePassword:m[2]||(m[2]=t=>u.value=!0),onLogout:b}),n(Xe)],2),e("main",Ws,[n(o)]),n(Y)]),u.value?(a(),D(ls,{key:0,onClose:m[3]||(m[3]=t=>u.value=!1)})):U("",!0),x.value?(a(),D(oe,{key:1,isCurrUser:"",data:x.value,onDie:m[4]||(m[4]=t=>x.value=null)},null,8,["data"])):U("",!0)])}}},Js=B(qs,[["__scopeId","data-v-a17a54f7"]]),Ks={class:"viho-theme"},Xs={__name:"SinglePage",setup(p){return(v,d)=>{const h=E("RouterView");return a(),l("div",Ks,[n(h),n(Y)])}}},Qs={__name:"index",setup(p){const v=ae(),d=C(()=>{const h=v.name;return["login","e404"].indexOf(h)<0?Js:Xs});return ne(()=>{const h=document.getElementById("loader");h&&(h.classList.add("hide"),setTimeout(function(){h.remove()},700))}),(h,r)=>(a(),D(le(d.value)))}};export{Qs as default};