(this["webpackJsonpmoodle-hw-grade"]=this["webpackJsonpmoodle-hw-grade"]||[]).push([[0],{44:function(e,t,a){e.exports=a(74)},49:function(e,t,a){},50:function(e,t,a){e.exports=a.p+"static/media/logo.25bf045c.svg"},51:function(e,t,a){},74:function(e,t,a){"use strict";a.r(t);var n=a(0),r=a.n(n),i=a(16),c=a.n(i),l=(a(49),a(50),a(51),a(8)),o=a.n(l),u=a(13),s=a(14),m=a(94),p=a(104),d=a(101),f=a(103),h=a(102),v=a(96),g=a(97),b=a(100),E=a(99),w=a(98),y=a(39),O=a(34),x=a(35),j=a.n(x);function k(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function _(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?k(a,!0).forEach((function(t){Object(O.a)(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):k(a).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}var W="http://moodle-test.bankdabrabyt.by",N=function(){var e=Object(u.a)(o.a.mark((function e(t){var a,n,r=arguments;return o.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return a=r.length>1&&void 0!==r[1]?r[1]:{},e.prev=1,e.next=4,j.a.get("".concat(W,"/blocks/reports/hw_grade/api.php"),{params:_({},a,{action:t})});case 4:if(!(n=e.sent)||!n.data){e.next=7;break}return e.abrupt("return",n.data);case 7:e.next=12;break;case 9:e.prev=9,e.t0=e.catch(1),console.log("error: ",e.t0);case 12:case"end":return e.stop()}}),e,null,[[1,9]])})));return function(t){return e.apply(this,arguments)}}(),C=function(){var e=Object(u.a)(o.a.mark((function e(t,a,n){var r;return o.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,N("getCourses",n);case 2:(r=e.sent)&&a(r);case 4:case"end":return e.stop()}}),e)})));return function(t,a,n){return e.apply(this,arguments)}}(),q=function(){var e=Object(u.a)(o.a.mark((function e(t,a){var n;return o.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,N("getHeaderData",a);case 2:(n=e.sent)&&t({total:n.total,attention:n.attention});case 4:case"end":return e.stop()}}),e)})));return function(t,a){return e.apply(this,arguments)}}(),S=function(){var e=Object(u.a)(o.a.mark((function e(t,a,n){var r;return o.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,N("getTableData",n);case 2:if(!(r=e.sent)){e.next=6;break}return e.next=6,a(_({},t,{items:Object(y.a)(r)}));case 6:case"end":return e.stop()}}),e)})));return function(t,a,n){return e.apply(this,arguments)}}();function T(e,t){t||(t=window.location.href),e=e.replace(/[\[\]]/g,"\\$&");var a=new RegExp("[?&]"+e+"(=([^&#]*)|&|#|$)").exec(t);return a?a[2]?decodeURIComponent(a[2].replace(/\+/g," ")):"":null}var D=function(){var e=Object(u.a)(o.a.mark((function e(t,a,n,r){var i,c,l;return o.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:i=T("courseid"),c=T("quizid"),l=T("search_query"),i&&(a([i]),t(i,!0),c&&n([c])),l&&r(l);case 5:case"end":return e.stop()}}),e)})));return function(t,a,n,r){return e.apply(this,arguments)}}(),P=Object(m.a)((function(e){return{root:{width:"100%",marginTop:e.spacing(3),alignItems:"flex-start"},table:{minWidth:500},marginLeft:{marginLeft:10},tableWrapper:{overflowX:"auto"},grader:{color:"orange"},directionColumn:{direction:"column"},directionRow:{direction:"row"},buttonMarginTop:{marginLeft:"20px",marginTop:"-5px"},boxSearch:{"flex-direction":"row",justifyContent:"center",marginTop:"10px",marginBottom:"10px"},textField:{width:"800px !important",maxWidth:"inherit !important",display:"inline-block"},select:{height:"35px",width:"400px !important",maxWidth:"inherit !important",display:"inline-block",marginBottom:"10px"},selectTitle:{width:"80px ",display:"inline-block"}}})),z=[{id:"quiz",label:"\u0417\u0430\u0434\u0430\u043d\u0438\u0435",minWidth:270},{id:"student",label:"\u0418\u043c\u044f/\u0424\u0430\u043c\u0438\u043b\u0438\u044f",minWidth:100},{id:"company",label:"\u041a\u043e\u043c\u043f\u0430\u043d\u0438\u044f",minWidth:100},{id:"attempt",label:"\u041f\u043e\u043f\u044b\u0442\u043e\u043a",align:"center",minWidth:60},{id:"hoursPassed",label:"\u041f\u0440\u043e\u0448\u043b\u043e \u0447\u0430\u0441\u043e\u0432",align:"center",minWidth:60},{id:"status",label:"\u0421\u0442\u0430\u0442\u0443\u0441",align:"center",minWidth:100},{id:"message",label:"\u0421\u043e\u043e\u0431\u0449\u0435\u043d\u0438\u0435",align:"center",minWidth:100},{id:"plagiat",label:"\u041f\u043b\u0430\u0433\u0438\u0430\u0442",align:"center",minWidth:100}],R=function(e){var t=P(),a=Object(n.useState)([]),i=Object(s.a)(a,2),c=i[0],l=i[1],m=Object(n.useState)(["0"]),y=Object(s.a)(m,2),O=y[0],x=y[1],j=Object(n.useState)([]),k=Object(s.a)(j,2),_=k[0],W=k[1],N=Object(n.useState)(["0"]),T=Object(s.a)(N,2),R=T[0],U=T[1],I=Object(n.useState)({total:0,attention:0}),A=Object(s.a)(I,2),B=A[0],F=A[1],L=Object(n.useState)({items:[],selected:null}),$=Object(s.a)(L,2),G=$[0],H=$[1],J=Object(n.useState)(""),M=Object(s.a)(J,2),V=M[0],X=M[1];Object(n.useEffect)((function(){var e=function(){var e=Object(u.a)(o.a.mark((function e(){return o.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,S(G,H);case 2:return e.next=4,D(K,x,U,X);case 4:case"end":return e.stop()}}),e)})));return function(){return e.apply(this,arguments)}}();C(c,l),q(F),e()}),[]);var K=function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1],a=[];if(G.items.map((function(t){e===t.course_id&&-1===a.findIndex((function(e){return e.id===t.quiz_id}))&&a.push({id:t.quiz_id,name:t.quiz_name})})),0===a.length)return W([{id:0,name:"\u0412\u0441\u0435 \u0437\u0430\u0434\u0430\u043d\u0438\u044f"}]),void(t||U(["0"]));var n=a.sort((function(e,t){return e.name<t.name?-1:1}));n.unshift({id:0,name:"\u0412\u0441\u0435 \u0437\u0430\u0434\u0430\u043d\u0438\u044f"}),W(n),t||U(["0"])};return r.a.createElement(r.a.Fragment,null,r.a.createElement(d.a,{className:t.root},r.a.createElement(f.a,{className:t.marginLeft},r.a.createElement(h.a,{variant:"h4",align:"left"},"\u0421\u043f\u0438\u0441\u043e\u043a \u043d\u0435\u043f\u0440\u043e\u0432\u0435\u0440\u0435\u043d\u043d\u044b\u0445 \u0437\u0430\u0434\u0430\u043d\u0438\u0439 \u0432 \u0432\u0438\u0434\u0435 \u0442\u0435\u0441\u0442\u043e\u0432."),r.a.createElement(h.a,{variant:"h5",align:"left"},"\u0412\u0441\u0435\u0433\u043e \u043d\u0435\u043f\u0440\u043e\u0432\u0435\u0440\u0435\u043d\u043d\u044b\u0445 \u0440\u0430\u0431\u043e\u0442: ",r.a.createElement(h.a,{display:"inline",variant:"h5",component:"div"},B.total),", \u0438\u0437 \u043d\u0438\u0445 \u0442\u0440\u0435\u0431\u0443\u044e\u0442 \u0432\u043d\u0438\u043c\u0430\u043d\u0438\u044f: ",r.a.createElement(h.a,{display:"inline",variant:"h5",component:"div",color:"error"},B.attention)),r.a.createElement(f.a,{className:t.boxSearch},r.a.createElement("input",{id:"outlined-search",type:"search",className:t.textField,value:V,onChange:function(e){X(e.target.value)}}),r.a.createElement(p.a,{className:t.buttonMarginTop,variant:"contained",onClick:function(){var e={search_query:V};C(c,l,e),q(F,e),S(G,H,e)}},"\u041e\u0431\u043d\u043e\u0432\u0438\u0442\u044c")),r.a.createElement("div",null,r.a.createElement("div",{className:t.selectTitle},"\u041a\u0443\u0440\u0441\u044b: "),r.a.createElement("select",{className:t.select,value:O[0],onChange:function(e){x([e.target.value]),K(e.target.value)}},c.map((function(e){return r.a.createElement("option",{key:e.id,value:e.id},e.fullname)})))),r.a.createElement("div",null,r.a.createElement("div",{className:t.selectTitle},"\u0417\u0430\u0434\u0430\u043d\u0438\u044f: "),r.a.createElement("select",{className:t.select,value:R[0],onChange:function(e){U([e.target.value])}},_.map((function(e){return r.a.createElement("option",{key:e.id,value:e.id},e.name)}))))),function(){if(!G.items)return null;var e=O.includes("0"),a=R.includes("0");return r.a.createElement(v.a,{stickyHeader:!0,className:t.table,"aria-label":"sticky table"},r.a.createElement(g.a,null,r.a.createElement(w.a,null,z.map((function(e){return r.a.createElement(E.a,{key:e.id,align:e.align,style:{minWidth:e.minWidth}},e.label)})))),r.a.createElement(b.a,null,G.items.map((function(n){if((e||O.includes(n.course_id))&&(a||R.includes(n.quiz_id))&&(""===V||n.user_name.toUpperCase().includes(V.toUpperCase())||n.user_email.toUpperCase().includes(V.toUpperCase())))return r.a.createElement(w.a,{key:n.quiz_attempt_id},r.a.createElement(E.a,null,n.quiz_name),r.a.createElement(E.a,null,r.a.createElement("a",{href:n.user_link},n.user_name,"(",n.user_email,")")),r.a.createElement(E.a,null,n.user_company),r.a.createElement(E.a,{align:"center"},n.attempt),r.a.createElement(E.a,{align:"center"},n.time_spent),r.a.createElement(E.a,{align:"center"},function(e,a,n){var i=e+"&courseid="+O[0]+"&quizid="+R[0]+"&search_query="+V;return r.a.createElement(r.a.Fragment,null,"NEED_GRADE"===a&&r.a.createElement(p.a,{variant:"contained",color:"primary",href:i},"\u041f\u0440\u043e\u0432\u0435\u0440\u0438\u0442\u044c"),"OPEN"===a&&r.a.createElement(p.a,{variant:"contained",color:"primary",href:i},"\u041e\u0442\u043a\u0440\u044b\u0442\u044c"),"ON_REVIEW"===a&&r.a.createElement("a",{href:i},r.a.createElement("div",{className:[t.directionColumn,t.grader]},r.a.createElement("div",null,"\u043d\u0430 \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0435"),r.a.createElement("div",{className:t.grader},"(",n,")"))),"GRADED"===a&&r.a.createElement("a",{href:i},n),"AUTOTEST"===a&&r.a.createElement("a",{href:i},"\u0430\u0432\u0442\u043e\u0442\u0435\u0441\u0442"))}(n.onreview_link,n.onreview_status,n.onreview_grader)),r.a.createElement(E.a,{align:"center"},n.posts),r.a.createElement(E.a,{align:"center"},n.plagiat_percent))}))))}()))};var U=function(){return r.a.createElement("div",{className:"App"},r.a.createElement(R,null))};Boolean("localhost"===window.location.hostname||"[::1]"===window.location.hostname||window.location.hostname.match(/^127(?:\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$/));c.a.render(r.a.createElement(U,null),document.getElementById("root")),"serviceWorker"in navigator&&navigator.serviceWorker.ready.then((function(e){e.unregister()}))}},[[44,1,2]]]);
//# sourceMappingURL=main.fa5b9dc9.chunk.js.map