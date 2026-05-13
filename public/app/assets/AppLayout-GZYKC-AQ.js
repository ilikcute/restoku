import{A as e,B as t,D as n,F as r,H as i,I as a,L as o,N as s,P as c,V as l,Z as u,_ as d,b as f,g as p,h as m,i as h,it as g,j as _,k as v,m as y,nt as b,ot as x,p as S,r as C,rt as w,u as T,v as E,x as D}from"./axios-CqGGTvEC.js";import{a as O,i as k,o as A,s as j,t as ee}from"./button-DGcB8frD.js";import{$ as te,G as ne,O as M,Ot as re,Q as ie,Z as N,b as P,c as F,d as ae,dt as I,j as oe,kt as se,m as ce,nt as L,pt as R,r as le,u as ue,v as z,x as B}from"./index-CshvtP54.js";import{t as V}from"./shift-ojR6zpp9.js";import{n as de,t as fe}from"./times-DAfgdswQ.js";import{t as pe}from"./check-C3TmPo9V.js";import{t as me}from"./timescircle-DdbSdjKt.js";import{t as he}from"./dialog-C5GrPYfe.js";import{t as ge}from"./tag-CBObYtbF.js";import{t as _e}from"./overlayeventbus-PEMufZ45.js";var ve=`
    .p-toast {
        width: dt('toast.width');
        white-space: pre-line;
        word-break: break-word;
    }

    .p-toast-message {
        margin: 0 0 1rem 0;
        display: grid;
        grid-template-rows: 1fr;
    }

    .p-toast-message-icon {
        flex-shrink: 0;
        font-size: dt('toast.icon.size');
        width: dt('toast.icon.size');
        height: dt('toast.icon.size');
    }

    .p-toast-message-content {
        display: flex;
        align-items: flex-start;
        padding: dt('toast.content.padding');
        gap: dt('toast.content.gap');
        min-height: 0;
        overflow: hidden;
        transition: padding 250ms ease-in;
    }

    .p-toast-message-text {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: dt('toast.text.gap');
    }

    .p-toast-summary {
        font-weight: dt('toast.summary.font.weight');
        font-size: dt('toast.summary.font.size');
    }

    .p-toast-detail {
        font-weight: dt('toast.detail.font.weight');
        font-size: dt('toast.detail.font.size');
    }

    .p-toast-close-button {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        background: transparent;
        transition:
            background dt('toast.transition.duration'),
            color dt('toast.transition.duration'),
            outline-color dt('toast.transition.duration'),
            box-shadow dt('toast.transition.duration');
        outline-color: transparent;
        color: inherit;
        width: dt('toast.close.button.width');
        height: dt('toast.close.button.height');
        border-radius: dt('toast.close.button.border.radius');
        margin: -25% 0 0 0;
        right: -25%;
        padding: 0;
        border: none;
        user-select: none;
    }

    .p-toast-close-button:dir(rtl) {
        margin: -25% 0 0 auto;
        left: -25%;
        right: auto;
    }

    .p-toast-message-info,
    .p-toast-message-success,
    .p-toast-message-warn,
    .p-toast-message-error,
    .p-toast-message-secondary,
    .p-toast-message-contrast {
        border-width: dt('toast.border.width');
        border-style: solid;
        backdrop-filter: blur(dt('toast.blur'));
        border-radius: dt('toast.border.radius');
    }

    .p-toast-close-icon {
        font-size: dt('toast.close.icon.size');
        width: dt('toast.close.icon.size');
        height: dt('toast.close.icon.size');
    }

    .p-toast-close-button:focus-visible {
        outline-width: dt('focus.ring.width');
        outline-style: dt('focus.ring.style');
        outline-offset: dt('focus.ring.offset');
    }

    .p-toast-message-info {
        background: dt('toast.info.background');
        border-color: dt('toast.info.border.color');
        color: dt('toast.info.color');
        box-shadow: dt('toast.info.shadow');
    }

    .p-toast-message-info .p-toast-detail {
        color: dt('toast.info.detail.color');
    }

    .p-toast-message-info .p-toast-close-button:focus-visible {
        outline-color: dt('toast.info.close.button.focus.ring.color');
        box-shadow: dt('toast.info.close.button.focus.ring.shadow');
    }

    .p-toast-message-info .p-toast-close-button:hover {
        background: dt('toast.info.close.button.hover.background');
    }

    .p-toast-message-success {
        background: dt('toast.success.background');
        border-color: dt('toast.success.border.color');
        color: dt('toast.success.color');
        box-shadow: dt('toast.success.shadow');
    }

    .p-toast-message-success .p-toast-detail {
        color: dt('toast.success.detail.color');
    }

    .p-toast-message-success .p-toast-close-button:focus-visible {
        outline-color: dt('toast.success.close.button.focus.ring.color');
        box-shadow: dt('toast.success.close.button.focus.ring.shadow');
    }

    .p-toast-message-success .p-toast-close-button:hover {
        background: dt('toast.success.close.button.hover.background');
    }

    .p-toast-message-warn {
        background: dt('toast.warn.background');
        border-color: dt('toast.warn.border.color');
        color: dt('toast.warn.color');
        box-shadow: dt('toast.warn.shadow');
    }

    .p-toast-message-warn .p-toast-detail {
        color: dt('toast.warn.detail.color');
    }

    .p-toast-message-warn .p-toast-close-button:focus-visible {
        outline-color: dt('toast.warn.close.button.focus.ring.color');
        box-shadow: dt('toast.warn.close.button.focus.ring.shadow');
    }

    .p-toast-message-warn .p-toast-close-button:hover {
        background: dt('toast.warn.close.button.hover.background');
    }

    .p-toast-message-error {
        background: dt('toast.error.background');
        border-color: dt('toast.error.border.color');
        color: dt('toast.error.color');
        box-shadow: dt('toast.error.shadow');
    }

    .p-toast-message-error .p-toast-detail {
        color: dt('toast.error.detail.color');
    }

    .p-toast-message-error .p-toast-close-button:focus-visible {
        outline-color: dt('toast.error.close.button.focus.ring.color');
        box-shadow: dt('toast.error.close.button.focus.ring.shadow');
    }

    .p-toast-message-error .p-toast-close-button:hover {
        background: dt('toast.error.close.button.hover.background');
    }

    .p-toast-message-secondary {
        background: dt('toast.secondary.background');
        border-color: dt('toast.secondary.border.color');
        color: dt('toast.secondary.color');
        box-shadow: dt('toast.secondary.shadow');
    }

    .p-toast-message-secondary .p-toast-detail {
        color: dt('toast.secondary.detail.color');
    }

    .p-toast-message-secondary .p-toast-close-button:focus-visible {
        outline-color: dt('toast.secondary.close.button.focus.ring.color');
        box-shadow: dt('toast.secondary.close.button.focus.ring.shadow');
    }

    .p-toast-message-secondary .p-toast-close-button:hover {
        background: dt('toast.secondary.close.button.hover.background');
    }

    .p-toast-message-contrast {
        background: dt('toast.contrast.background');
        border-color: dt('toast.contrast.border.color');
        color: dt('toast.contrast.color');
        box-shadow: dt('toast.contrast.shadow');
    }
    
    .p-toast-message-contrast .p-toast-detail {
        color: dt('toast.contrast.detail.color');
    }

    .p-toast-message-contrast .p-toast-close-button:focus-visible {
        outline-color: dt('toast.contrast.close.button.focus.ring.color');
        box-shadow: dt('toast.contrast.close.button.focus.ring.shadow');
    }

    .p-toast-message-contrast .p-toast-close-button:hover {
        background: dt('toast.contrast.close.button.hover.background');
    }

    .p-toast-top-center {
        transform: translateX(-50%);
    }

    .p-toast-bottom-center {
        transform: translateX(-50%);
    }

    .p-toast-center {
        min-width: 20vw;
        transform: translate(-50%, -50%);
    }

    .p-toast-message-enter-active {
        animation: p-animate-toast-enter 300ms ease-out;
    }

    .p-toast-message-leave-active {
        animation: p-animate-toast-leave 250ms ease-in;
    }

    .p-toast-message-leave-to .p-toast-message-content {
        padding-top: 0;
        padding-bottom: 0;
    }

    @keyframes p-animate-toast-enter {
        from {
            opacity: 0;
            transform: scale(0.6);
        }
        to {
            opacity: 1;
            grid-template-rows: 1fr;
        }
    }

     @keyframes p-animate-toast-leave {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
            margin-bottom: 0;
            grid-template-rows: 0fr;
            transform: translateY(-100%) scale(0.6);
        }
    }
`;function H(e){"@babel/helpers - typeof";return H=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},H(e)}function U(e,t,n){return(t=ye(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function ye(e){var t=be(e,`string`);return H(t)==`symbol`?t:t+``}function be(e,t){if(H(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(H(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var xe=B.extend({name:`toast`,style:ve,classes:{root:function(e){return[`p-toast p-component p-toast-`+e.props.position]},message:function(e){var t=e.props;return[`p-toast-message`,{"p-toast-message-info":t.message.severity===`info`||t.message.severity===void 0,"p-toast-message-warn":t.message.severity===`warn`,"p-toast-message-error":t.message.severity===`error`,"p-toast-message-success":t.message.severity===`success`,"p-toast-message-secondary":t.message.severity===`secondary`,"p-toast-message-contrast":t.message.severity===`contrast`}]},messageContent:`p-toast-message-content`,messageIcon:function(e){var t=e.props;return[`p-toast-message-icon`,U(U(U(U({},t.infoIcon,t.message.severity===`info`),t.warnIcon,t.message.severity===`warn`),t.errorIcon,t.message.severity===`error`),t.successIcon,t.message.severity===`success`)]},messageText:`p-toast-message-text`,summary:`p-toast-summary`,detail:`p-toast-detail`,closeButton:`p-toast-close-button`,closeIcon:`p-toast-close-icon`},inlineStyles:{root:function(e){var t=e.position;return{position:`fixed`,top:t===`top-right`||t===`top-left`||t===`top-center`?`20px`:t===`center`?`50%`:null,right:(t===`top-right`||t===`bottom-right`)&&`20px`,bottom:(t===`bottom-left`||t===`bottom-right`||t===`bottom-center`)&&`20px`,left:t===`top-left`||t===`bottom-left`?`20px`:t===`center`||t===`top-center`||t===`bottom-center`?`50%`:null}}}}),W={name:`ExclamationTriangleIcon`,extends:O};function Se(e){return Ee(e)||Te(e)||we(e)||Ce()}function Ce(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function we(e,t){if(e){if(typeof e==`string`)return G(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?G(e,t):void 0}}function Te(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Ee(e){if(Array.isArray(e))return G(e)}function G(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function De(e,t,r,i,a,o){return _(),d(`svg`,n({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),Se(t[0]||=[y(`path`,{d:`M13.4018 13.1893H0.598161C0.49329 13.189 0.390283 13.1615 0.299143 13.1097C0.208003 13.0578 0.131826 12.9832 0.0780112 12.8932C0.0268539 12.8015 0 12.6982 0 12.5931C0 12.4881 0.0268539 12.3848 0.0780112 12.293L6.47985 1.08982C6.53679 1.00399 6.61408 0.933574 6.70484 0.884867C6.7956 0.836159 6.897 0.810669 7 0.810669C7.103 0.810669 7.2044 0.836159 7.29516 0.884867C7.38592 0.933574 7.46321 1.00399 7.52015 1.08982L13.922 12.293C13.9731 12.3848 14 12.4881 14 12.5931C14 12.6982 13.9731 12.8015 13.922 12.8932C13.8682 12.9832 13.792 13.0578 13.7009 13.1097C13.6097 13.1615 13.5067 13.189 13.4018 13.1893ZM1.63046 11.989H12.3695L7 2.59425L1.63046 11.989Z`,fill:`currentColor`},null,-1),y(`path`,{d:`M6.99996 8.78801C6.84143 8.78594 6.68997 8.72204 6.57787 8.60993C6.46576 8.49782 6.40186 8.34637 6.39979 8.18784V5.38703C6.39979 5.22786 6.46302 5.0752 6.57557 4.96265C6.68813 4.85009 6.84078 4.78686 6.99996 4.78686C7.15914 4.78686 7.31179 4.85009 7.42435 4.96265C7.5369 5.0752 7.60013 5.22786 7.60013 5.38703V8.18784C7.59806 8.34637 7.53416 8.49782 7.42205 8.60993C7.30995 8.72204 7.15849 8.78594 6.99996 8.78801Z`,fill:`currentColor`},null,-1),y(`path`,{d:`M6.99996 11.1887C6.84143 11.1866 6.68997 11.1227 6.57787 11.0106C6.46576 10.8985 6.40186 10.7471 6.39979 10.5885V10.1884C6.39979 10.0292 6.46302 9.87658 6.57557 9.76403C6.68813 9.65147 6.84078 9.58824 6.99996 9.58824C7.15914 9.58824 7.31179 9.65147 7.42435 9.76403C7.5369 9.87658 7.60013 10.0292 7.60013 10.1884V10.5885C7.59806 10.7471 7.53416 10.8985 7.42205 11.0106C7.30995 11.1227 7.15849 11.1866 6.99996 11.1887Z`,fill:`currentColor`},null,-1)]),16)}W.render=De;var K={name:`InfoCircleIcon`,extends:O};function Oe(e){return Me(e)||je(e)||Ae(e)||ke()}function ke(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Ae(e,t){if(e){if(typeof e==`string`)return q(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?q(e,t):void 0}}function je(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Me(e){if(Array.isArray(e))return q(e)}function q(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function Ne(e,t,r,i,a,o){return _(),d(`svg`,n({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),Oe(t[0]||=[y(`path`,{"fill-rule":`evenodd`,"clip-rule":`evenodd`,d:`M3.11101 12.8203C4.26215 13.5895 5.61553 14 7 14C8.85652 14 10.637 13.2625 11.9497 11.9497C13.2625 10.637 14 8.85652 14 7C14 5.61553 13.5895 4.26215 12.8203 3.11101C12.0511 1.95987 10.9579 1.06266 9.67879 0.532846C8.3997 0.00303296 6.99224 -0.13559 5.63437 0.134506C4.2765 0.404603 3.02922 1.07129 2.05026 2.05026C1.07129 3.02922 0.404603 4.2765 0.134506 5.63437C-0.13559 6.99224 0.00303296 8.3997 0.532846 9.67879C1.06266 10.9579 1.95987 12.0511 3.11101 12.8203ZM3.75918 2.14976C4.71846 1.50879 5.84628 1.16667 7 1.16667C8.5471 1.16667 10.0308 1.78125 11.1248 2.87521C12.2188 3.96918 12.8333 5.45291 12.8333 7C12.8333 8.15373 12.4912 9.28154 11.8502 10.2408C11.2093 11.2001 10.2982 11.9478 9.23232 12.3893C8.16642 12.8308 6.99353 12.9463 5.86198 12.7212C4.73042 12.4962 3.69102 11.9406 2.87521 11.1248C2.05941 10.309 1.50384 9.26958 1.27876 8.13803C1.05367 7.00647 1.16919 5.83358 1.61071 4.76768C2.05222 3.70178 2.79989 2.79074 3.75918 2.14976ZM7.00002 4.8611C6.84594 4.85908 6.69873 4.79698 6.58977 4.68801C6.48081 4.57905 6.4187 4.43185 6.41669 4.27776V3.88888C6.41669 3.73417 6.47815 3.58579 6.58754 3.4764C6.69694 3.367 6.84531 3.30554 7.00002 3.30554C7.15473 3.30554 7.3031 3.367 7.4125 3.4764C7.52189 3.58579 7.58335 3.73417 7.58335 3.88888V4.27776C7.58134 4.43185 7.51923 4.57905 7.41027 4.68801C7.30131 4.79698 7.1541 4.85908 7.00002 4.8611ZM7.00002 10.6945C6.84594 10.6925 6.69873 10.6304 6.58977 10.5214C6.48081 10.4124 6.4187 10.2652 6.41669 10.1111V6.22225C6.41669 6.06754 6.47815 5.91917 6.58754 5.80977C6.69694 5.70037 6.84531 5.63892 7.00002 5.63892C7.15473 5.63892 7.3031 5.70037 7.4125 5.80977C7.52189 5.91917 7.58335 6.06754 7.58335 6.22225V10.1111C7.58134 10.2652 7.51923 10.4124 7.41027 10.5214C7.30131 10.6304 7.1541 10.6925 7.00002 10.6945Z`,fill:`currentColor`},null,-1)]),16)}K.render=Ne;var Pe={name:`BaseToast`,extends:A,props:{group:{type:String,default:null},position:{type:String,default:`top-right`},autoZIndex:{type:Boolean,default:!0},baseZIndex:{type:Number,default:0},breakpoints:{type:Object,default:null},closeIcon:{type:String,default:void 0},infoIcon:{type:String,default:void 0},warnIcon:{type:String,default:void 0},errorIcon:{type:String,default:void 0},successIcon:{type:String,default:void 0},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},style:xe,provide:function(){return{$pcToast:this,$parentInstance:this}}};function J(e){"@babel/helpers - typeof";return J=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},J(e)}function Fe(e,t,n){return(t=Ie(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Ie(e){var t=Le(e,`string`);return J(t)==`symbol`?t:t+``}function Le(e,t){if(J(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(J(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var Re={name:`ToastMessage`,hostName:`Toast`,extends:A,emits:[`close`],closeTimeout:null,createdAt:null,lifeRemaining:null,props:{message:{type:null,default:null},templates:{type:Object,default:null},closeIcon:{type:String,default:null},infoIcon:{type:String,default:null},warnIcon:{type:String,default:null},errorIcon:{type:String,default:null},successIcon:{type:String,default:null},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},mounted:function(){this.message.life&&(this.lifeRemaining=this.message.life,this.startTimeout())},beforeUnmount:function(){this.clearCloseTimeout()},methods:{startTimeout:function(){var e=this;this.createdAt=new Date().valueOf(),this.closeTimeout=setTimeout(function(){e.close({message:e.message,type:`life-end`})},this.lifeRemaining)},close:function(e){this.$emit(`close`,e)},onCloseClick:function(){this.clearCloseTimeout(),this.close({message:this.message,type:`close`})},clearCloseTimeout:function(){this.closeTimeout&&=(clearTimeout(this.closeTimeout),null)},onMessageClick:function(e){var t;(t=this.onClick)==null||t.call(this,{originalEvent:e,message:this.message})},handleMouseEnter:function(e){if(this.onMouseEnter){if(this.onMouseEnter({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&(this.lifeRemaining=this.createdAt+this.lifeRemaining-new Date().valueOf(),this.createdAt=null,this.clearCloseTimeout())}},handleMouseLeave:function(e){if(this.onMouseLeave){if(this.onMouseLeave({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&this.startTimeout()}}},computed:{iconComponent:function(){return{info:!this.infoIcon&&K,success:!this.successIcon&&pe,warn:!this.warnIcon&&W,error:!this.errorIcon&&me}[this.message.severity]},closeAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.close:void 0},dataP:function(){return j(Fe({},this.message.severity,this.message.severity))}},components:{TimesIcon:fe,InfoCircleIcon:K,CheckIcon:pe,ExclamationTriangleIcon:W,TimesCircleIcon:me},directives:{ripple:k}};function Y(e){"@babel/helpers - typeof";return Y=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},Y(e)}function ze(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function Be(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?ze(Object(n),!0).forEach(function(t){Ve(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):ze(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function Ve(e,t,n){return(t=He(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function He(e){var t=Ue(e,`string`);return Y(t)==`symbol`?t:t+``}function Ue(e,t){if(Y(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(Y(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var We=[`data-p`],Ge=[`data-p`],Ke=[`data-p`],qe=[`data-p`],Je=[`aria-label`,`data-p`];function Ye(e,t,r,s,c,l){var u=a(`ripple`);return _(),d(`div`,n({class:[e.cx(`message`),r.message.styleClass],role:`alert`,"aria-live":`assertive`,"aria-atomic":`true`,"data-p":l.dataP},e.ptm(`message`),{onClick:t[1]||=function(){return l.onMessageClick&&l.onMessageClick.apply(l,arguments)},onMouseenter:t[2]||=function(){return l.handleMouseEnter&&l.handleMouseEnter.apply(l,arguments)},onMouseleave:t[3]||=function(){return l.handleMouseLeave&&l.handleMouseLeave.apply(l,arguments)}}),[r.templates.container?(_(),m(o(r.templates.container),{key:0,message:r.message,closeCallback:l.onCloseClick},null,8,[`message`,`closeCallback`])):(_(),d(`div`,n({key:1,class:[e.cx(`messageContent`),r.message.contentStyleClass]},e.ptm(`messageContent`)),[r.templates.message?(_(),m(o(r.templates.message),{key:1,message:r.message},null,8,[`message`])):(_(),d(T,{key:0},[(_(),m(o(r.templates.messageicon?r.templates.messageicon:r.templates.icon?r.templates.icon:l.iconComponent&&l.iconComponent.name?l.iconComponent:`span`),n({class:e.cx(`messageIcon`)},e.ptm(`messageIcon`)),null,16,[`class`])),y(`div`,n({class:e.cx(`messageText`),"data-p":l.dataP},e.ptm(`messageText`)),[y(`span`,n({class:e.cx(`summary`),"data-p":l.dataP},e.ptm(`summary`)),x(r.message.summary),17,Ke),r.message.detail?(_(),d(`div`,n({key:0,class:e.cx(`detail`),"data-p":l.dataP},e.ptm(`detail`)),x(r.message.detail),17,qe)):p(``,!0)],16,Ge)],64)),r.message.closable===!1?p(``,!0):(_(),d(`div`,g(n({key:2},e.ptm(`buttonContainer`))),[i((_(),d(`button`,n({class:e.cx(`closeButton`),type:`button`,"aria-label":l.closeAriaLabel,onClick:t[0]||=function(){return l.onCloseClick&&l.onCloseClick.apply(l,arguments)},autofocus:``,"data-p":l.dataP},Be(Be({},r.closeButtonProps),e.ptm(`closeButton`))),[(_(),m(o(r.templates.closeicon||`TimesIcon`),n({class:[e.cx(`closeIcon`),r.closeIcon]},e.ptm(`closeIcon`)),null,16,[`class`]))],16,Je)),[[u]])],16))],16))],16,We)}Re.render=Ye;function X(e){"@babel/helpers - typeof";return X=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},X(e)}function Xe(e,t,n){return(t=Ze(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Ze(e){var t=Qe(e,`string`);return X(t)==`symbol`?t:t+``}function Qe(e,t){if(X(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(X(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}function $e(e){return rt(e)||nt(e)||tt(e)||et()}function et(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function tt(e,t){if(e){if(typeof e==`string`)return Z(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?Z(e,t):void 0}}function nt(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function rt(e){if(Array.isArray(e))return Z(e)}function Z(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var it=0,at={name:`Toast`,extends:Pe,inheritAttrs:!1,emits:[`close`,`life-end`],data:function(){return{messages:[]}},styleElement:null,mounted:function(){P.on(`add`,this.onAdd),P.on(`remove`,this.onRemove),P.on(`remove-group`,this.onRemoveGroup),P.on(`remove-all-groups`,this.onRemoveAllGroups),this.breakpoints&&this.createStyle()},beforeUnmount:function(){this.destroyStyle(),this.$refs.container&&this.autoZIndex&&M.clear(this.$refs.container),P.off(`add`,this.onAdd),P.off(`remove`,this.onRemove),P.off(`remove-group`,this.onRemoveGroup),P.off(`remove-all-groups`,this.onRemoveAllGroups)},methods:{add:function(e){e.id??=it++,this.messages=[].concat($e(this.messages),[e])},remove:function(e){var t=this.messages.findIndex(function(t){return t.id===e.message.id});t!==-1&&(this.messages.splice(t,1),this.$emit(e.type,{message:e.message}))},onAdd:function(e){this.group==e.group&&this.add(e)},onRemove:function(e){this.remove({message:e,type:`close`})},onRemoveGroup:function(e){this.group===e&&(this.messages=[])},onRemoveAllGroups:function(){var e=this;this.messages.forEach(function(t){return e.$emit(`close`,{message:t})}),this.messages=[]},onEnter:function(){this.autoZIndex&&M.set(`modal`,this.$refs.container,this.baseZIndex||this.$primevue.config.zIndex.modal)},onLeave:function(){var e=this;this.$refs.container&&this.autoZIndex&&re(this.messages)&&setTimeout(function(){M.clear(e.$refs.container)},200)},createStyle:function(){if(!this.styleElement&&!this.isUnstyled){var e;this.styleElement=document.createElement(`style`),this.styleElement.type=`text/css`,te(this.styleElement,`nonce`,(e=this.$primevue)==null||(e=e.config)==null||(e=e.csp)==null?void 0:e.nonce),document.head.appendChild(this.styleElement);var t=``;for(var n in this.breakpoints){var r=``;for(var i in this.breakpoints[n])r+=i+`:`+this.breakpoints[n][i]+`!important;`;t+=`
                        @media screen and (max-width: ${n}) {
                            .p-toast[${this.$attrSelector}] {
                                ${r}
                            }
                        }
                    `}this.styleElement.innerHTML=t}},destroyStyle:function(){this.styleElement&&=(document.head.removeChild(this.styleElement),null)}},computed:{dataP:function(){return j(Xe({},this.position,this.position))}},components:{ToastMessage:Re,Portal:de}};function Q(e){"@babel/helpers - typeof";return Q=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},Q(e)}function ot(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function st(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?ot(Object(n),!0).forEach(function(t){ct(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):ot(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function ct(e,t,n){return(t=lt(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function lt(e){var t=ut(e,`string`);return Q(t)==`symbol`?t:t+``}function ut(e,t){if(Q(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(Q(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var dt=[`data-p`];function ft(e,t,i,a,o,c){var u=r(`ToastMessage`),f=r(`Portal`);return _(),m(f,null,{default:l(function(){return[y(`div`,n({ref:`container`,class:e.cx(`root`),style:e.sx(`root`,!0,{position:e.position}),"data-p":c.dataP},e.ptmi(`root`)),[D(h,n({name:`p-toast-message`,tag:`div`,onEnter:c.onEnter,onLeave:c.onLeave},st({},e.ptm(`transition`))),{default:l(function(){return[(_(!0),d(T,null,s(o.messages,function(n){return _(),m(u,{key:n.id,message:n,templates:e.$slots,closeIcon:e.closeIcon,infoIcon:e.infoIcon,warnIcon:e.warnIcon,errorIcon:e.errorIcon,successIcon:e.successIcon,closeButtonProps:e.closeButtonProps,onMouseEnter:e.onMouseEnter,onMouseLeave:e.onMouseLeave,onClick:e.onClick,unstyled:e.unstyled,onClose:t[0]||=function(e){return c.remove(e)},pt:e.pt},null,8,[`message`,`templates`,`closeIcon`,`infoIcon`,`warnIcon`,`errorIcon`,`successIcon`,`closeButtonProps`,`onMouseEnter`,`onMouseLeave`,`onClick`,`unstyled`,`pt`])}),128))]}),_:1},16,[`onEnter`,`onLeave`])],16,dt)]}),_:1})}at.render=ft;var pt=B.extend({name:`confirmdialog`,style:`
    .p-confirmdialog .p-dialog-content {
        display: flex;
        align-items: center;
        gap: dt('confirmdialog.content.gap');
    }

    .p-confirmdialog-icon {
        color: dt('confirmdialog.icon.color');
        font-size: dt('confirmdialog.icon.size');
        width: dt('confirmdialog.icon.size');
        height: dt('confirmdialog.icon.size');
    }
`,classes:{root:`p-confirmdialog`,icon:`p-confirmdialog-icon`,message:`p-confirmdialog-message`,pcRejectButton:`p-confirmdialog-reject-button`,pcAcceptButton:`p-confirmdialog-accept-button`}}),mt={name:`ConfirmDialog`,extends:{name:`BaseConfirmDialog`,extends:A,props:{group:String,breakpoints:{type:Object,default:null},draggable:{type:Boolean,default:!0}},style:pt,provide:function(){return{$pcConfirmDialog:this,$parentInstance:this}}},confirmListener:null,closeListener:null,data:function(){return{visible:!1,confirmation:null}},mounted:function(){var e=this;this.confirmListener=function(t){t&&t.group===e.group&&(e.confirmation=t,e.confirmation.onShow&&e.confirmation.onShow(),e.visible=!0)},this.closeListener=function(){e.visible=!1,e.confirmation=null},z.on(`confirm`,this.confirmListener),z.on(`close`,this.closeListener)},beforeUnmount:function(){z.off(`confirm`,this.confirmListener),z.off(`close`,this.closeListener)},methods:{accept:function(){this.confirmation.accept&&this.confirmation.accept(),this.visible=!1},reject:function(){this.confirmation.reject&&this.confirmation.reject(),this.visible=!1},onHide:function(){this.confirmation.onHide&&this.confirmation.onHide(),this.visible=!1}},computed:{appendTo:function(){return this.confirmation?this.confirmation.appendTo:`body`},target:function(){return this.confirmation?this.confirmation.target:null},modal:function(){return this.confirmation?this.confirmation.modal==null?!0:this.confirmation.modal:!0},header:function(){return this.confirmation?this.confirmation.header:null},message:function(){return this.confirmation?this.confirmation.message:null},blockScroll:function(){return this.confirmation?this.confirmation.blockScroll:!0},position:function(){return this.confirmation?this.confirmation.position:null},acceptLabel:function(){if(this.confirmation){var e=this.confirmation;return e.acceptLabel||e.acceptProps?.label||this.$primevue.config.locale.accept}return this.$primevue.config.locale.accept},rejectLabel:function(){if(this.confirmation){var e=this.confirmation;return e.rejectLabel||e.rejectProps?.label||this.$primevue.config.locale.reject}return this.$primevue.config.locale.reject},acceptIcon:function(){var e;return this.confirmation?this.confirmation.acceptIcon:(e=this.confirmation)!=null&&e.acceptProps?this.confirmation.acceptProps.icon:null},rejectIcon:function(){var e;return this.confirmation?this.confirmation.rejectIcon:(e=this.confirmation)!=null&&e.rejectProps?this.confirmation.rejectProps.icon:null},autoFocusAccept:function(){return this.confirmation.defaultFocus===void 0||this.confirmation.defaultFocus===`accept`},autoFocusReject:function(){return this.confirmation.defaultFocus===`reject`},closeOnEscape:function(){return this.confirmation?this.confirmation.closeOnEscape:!0}},components:{Dialog:he,Button:ee}};function ht(e,t,i,a,s,u){var f=r(`Button`),h=r(`Dialog`);return _(),m(h,{visible:s.visible,"onUpdate:visible":[t[2]||=function(e){return s.visible=e},u.onHide],role:`alertdialog`,class:w(e.cx(`root`)),modal:u.modal,header:u.header,blockScroll:u.blockScroll,appendTo:u.appendTo,position:u.position,breakpoints:e.breakpoints,closeOnEscape:u.closeOnEscape,draggable:e.draggable,pt:e.pt,unstyled:e.unstyled},E({default:l(function(){return[e.$slots.container?p(``,!0):(_(),d(T,{key:0},[e.$slots.message?(_(),m(o(e.$slots.message),{key:1,message:s.confirmation},null,8,[`message`])):(_(),d(T,{key:0},[c(e.$slots,`icon`,{},function(){return[e.$slots.icon?(_(),m(o(e.$slots.icon),{key:0,class:w(e.cx(`icon`))},null,8,[`class`])):s.confirmation.icon?(_(),d(`span`,n({key:1,class:[s.confirmation.icon,e.cx(`icon`)]},e.ptm(`icon`)),null,16)):p(``,!0)]}),y(`span`,n({class:e.cx(`message`)},e.ptm(`message`)),x(u.message),17)],64))],64))]}),_:2},[e.$slots.container?{name:`container`,fn:l(function(t){return[c(e.$slots,`container`,{message:s.confirmation,closeCallback:t.closeCallback,acceptCallback:u.accept,rejectCallback:u.reject,initDragCallback:t.initDragCallback})]}),key:`0`}:void 0,e.$slots.container?void 0:{name:`footer`,fn:l(function(){return[D(f,n({class:[e.cx(`pcRejectButton`),s.confirmation.rejectClass],autofocus:u.autoFocusReject,unstyled:e.unstyled,text:s.confirmation.rejectProps?.text||!1,onClick:t[0]||=function(e){return u.reject()}},s.confirmation.rejectProps,{label:u.rejectLabel,pt:e.ptm(`pcRejectButton`)}),E({_:2},[u.rejectIcon||e.$slots.rejecticon?{name:`icon`,fn:l(function(t){return[c(e.$slots,`rejecticon`,{},function(){return[y(`span`,n({class:[u.rejectIcon,t.class]},e.ptm(`pcRejectButton`).icon,{"data-pc-section":`rejectbuttonicon`}),null,16)]})]}),key:`0`}:void 0]),1040,[`class`,`autofocus`,`unstyled`,`text`,`label`,`pt`]),D(f,n({label:u.acceptLabel,class:[e.cx(`pcAcceptButton`),s.confirmation.acceptClass],autofocus:u.autoFocusAccept,unstyled:e.unstyled,onClick:t[1]||=function(e){return u.accept()}},s.confirmation.acceptProps,{pt:e.ptm(`pcAcceptButton`)}),E({_:2},[u.acceptIcon||e.$slots.accepticon?{name:`icon`,fn:l(function(t){return[c(e.$slots,`accepticon`,{},function(){return[y(`span`,n({class:[u.acceptIcon,t.class]},e.ptm(`pcAcceptButton`).icon,{"data-pc-section":`acceptbuttonicon`}),null,16)]})]}),key:`0`}:void 0]),1040,[`label`,`class`,`autofocus`,`unstyled`,`pt`])]}),key:`1`}]),1032,[`visible`,`class`,`modal`,`header`,`blockScroll`,`appendTo`,`position`,`breakpoints`,`closeOnEscape`,`draggable`,`onUpdate:visible`,`pt`,`unstyled`])}mt.render=ht;var gt={class:`flex h-20 shrink-0 items-center justify-between px-4 pt-4`},_t={key:0,class:`flex items-center gap-3 overflow-hidden`},vt=[`src`],yt={class:`flex flex-col`},bt={class:`text-lg font-extrabold text-slate-900 tracking-tight leading-tight`},xt={class:`flex-1 space-y-2 overflow-y-auto px-4 py-6 mt-4 pb-24`},St=[`onClick`],Ct={class:`flex items-center gap-3 overflow-hidden`},wt={key:0,class:`whitespace-nowrap uppercase tracking-wider text-[11px]`},Tt={key:0},Et={key:1,class:`whitespace-nowrap`},Dt={key:2,class:`absolute left-full ml-4 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 shadow-lg border border-slate-700 pointer-events-none`},Ot={key:0,class:`mt-auto p-4 text-xs text-slate-400 border-t border-slate-100 flex justify-center`},kt={key:1,class:`mt-auto p-6 text-xs text-slate-400 border-t border-slate-100`},At={__name:`AppSidebar`,props:{modelValue:{type:Boolean,default:!1}},emits:[`update:modelValue`],setup(e,{emit:n}){let i=ue(),a=F(),c=S(()=>a.user?.attributes?.permissions||[]),f=S(()=>a.user?.relationships?.tenant?.data?.attributes||{}),h=u(!1),g=u(new Set),C=()=>{h.value=!h.value,h.value?g.value.clear():A()},E=e=>{h.value&&=!1,g.value.has(e)?g.value.delete(e):g.value.add(e)},D=e=>g.value.has(e),O=[{label:`common.dashboard`,icon:`pi pi-home`,permission:`view-dashboard`,items:[{to:`/dashboard`,icon:`pi pi-home`,label:`common.overview`}]},{label:`common.master_data`,icon:`pi pi-database`,permission:`view-master-data`,items:[{to:`/master/products`,icon:`pi pi-box`,label:`sidebar.products`,permission:`view-products`},{to:`/master/categories`,icon:`pi pi-tags`,label:`sidebar.categories`,permission:`view-categories`},{to:`/master/units`,icon:`pi pi-sliders-h`,label:`sidebar.units`,permission:`view-units`},{to:`/master/suppliers`,icon:`pi pi-truck`,label:`sidebar.suppliers`,permission:`view-suppliers`},{to:`/master/customers`,icon:`pi pi-user`,label:`sidebar.customers`,permission:`view-customers`}]},{label:`common.inventory`,icon:`pi pi-warehouse`,permission:`view-inventory`,items:[{to:`/inventory/stocks`,icon:`pi pi-warehouse`,label:`sidebar.stock_levels`,permission:`view-stocks`},{to:`/inventory/movements`,icon:`pi pi-sync`,label:`sidebar.movements`,permission:`view-stock-movements`},{to:`/inventory/adjustments`,icon:`pi pi-pencil`,label:`sidebar.adjustments`,permission:`view-stock-adjustments`}]},{icon:`pi pi-shopping-cart`,label:`common.sales`,permission:`view-sales`,items:[{to:`/sales/shifts`,icon:`pi pi-clock`,label:`sidebar.shift_manager`,permission:`view-shifts`},{to:`/sales/pos`,icon:`pi pi-calculator`,label:`sidebar.pos`,permission:`view-pos`},{to:`/sales/orders`,icon:`pi pi-list`,label:`sidebar.orders`,permission:`view-orders`},{to:`/sales/returns`,icon:`pi pi-plus-circle`,label:`sidebar.create_return`,permission:`view-sales-returns`},{to:`/reports/sales-returns`,icon:`pi pi-history`,label:`sidebar.return_history`,permission:`view-report-returns`},{to:`/menu`,icon:`pi pi-qrcode`,label:`sidebar.digital_menu`}]},{icon:`pi pi-shopping-bag`,label:`common.purchasing`,permission:`view-purchasing`,items:[{to:`/purchasing/purchases`,icon:`pi pi-list`,label:`sidebar.purchases`,permission:`view-purchases`},{to:`/purchasing/returns`,icon:`pi pi-plus-circle`,label:`sidebar.create_return`,permission:`view-purchase-returns`},{to:`/reports/purchase-returns`,icon:`pi pi-history`,label:`sidebar.return_history`,permission:`view-report-returns`},{to:`/purchasing/procurement`,icon:`pi pi-search-plus`,label:`sidebar.procurement`,permission:`view-procurement`},{to:`/inventory/alerts`,icon:`pi pi-bell`,label:`sidebar.alerts`,permission:`view-inventory-alerts`}]},{label:`common.finance`,icon:`pi pi-wallet`,permission:`view-finance`,items:[{to:`/finance/accounts`,icon:`pi pi-wallet`,label:`sidebar.accounts`,permission:`view-accounts`},{to:`/finance/transactions`,icon:`pi pi-money-bill`,label:`sidebar.transactions`,permission:`view-transactions`},{to:`/finance/closings`,icon:`pi pi-check-circle`,label:`sidebar.daily_closings`,permission:`view-closings`}]},{label:`common.reports`,icon:`pi pi-chart-bar`,permission:`view-reports`,items:[{to:`/reports/recap`,icon:`pi pi-chart-bar`,label:`sidebar.recap`,permission:`view-reports`},{to:`/reports/sales`,icon:`pi pi-list`,label:`sidebar.sales_report`,permission:`view-report-sales`},{to:`/reports/purchases`,icon:`pi pi-shopping-bag`,label:`sidebar.purchase_report`,permission:`view-report-purchases`},{to:`/reports/sales-returns`,icon:`pi pi-replay`,label:`sidebar.sales_return_report`,permission:`view-report-returns`},{to:`/reports/purchase-returns`,icon:`pi pi-replay`,label:`sidebar.purchase_return_report`,permission:`view-report-returns`},{to:`/reports/tax`,icon:`pi pi-percentage`,label:`sidebar.tax_report`,permission:`view-report-tax`},{to:`/reports/audit-logs`,icon:`pi pi-history`,label:`sidebar.audit_trail`,permission:`view-reports`}]},{label:`common.settings`,icon:`pi pi-cog`,items:[{to:`/settings/profile`,icon:`pi pi-user-edit`,label:`sidebar.profile`,permission:`view-profile`},{to:`/settings/tenant`,icon:`pi pi-building`,label:`common.business_profile`,permission:`view-business-profile`},{to:`/settings/printer`,icon:`pi pi-print`,label:`sidebar.printer_settings`,permission:`view-business-profile`},{to:`/settings/users`,icon:`pi pi-users`,label:`sidebar.users`,permission:`manage-users`},{to:`/settings/roles`,icon:`pi pi-lock`,label:`settings.roles`,permission:`manage-roles`},{to:`/settings/promotions`,icon:`pi pi-megaphone`,label:`sidebar.promotions`,permission:`view-promotions`}]}],k=S(()=>O.filter(e=>!e.permission||c.value.includes(e.permission)).map(e=>({...e,items:e.items.filter(e=>!e.permission||c.value.includes(e.permission))})).filter(e=>e.items.length>0)),A=()=>{let e=i.path;k.value.forEach(t=>{t.items.some(t=>e.startsWith(t.to))&&g.value.add(t.label)})};return v(()=>{A()}),t(()=>i.path,()=>{h.value||A()}),(t,n)=>{let a=r(`router-link`);return _(),d(`aside`,{class:w([`fixed inset-y-0 left-0 z-50 flex flex-col transform bg-white shadow-xl lg:static lg:shadow-none lg:border-r lg:border-slate-200 transition-all duration-300`,e.modelValue?`translate-x-0`:`-translate-x-full lg:translate-x-0`,h.value?`w-20`:`w-64`])},[y(`div`,gt,[h.value?p(``,!0):(_(),d(`div`,_t,[y(`img`,{src:f.value.logo_url||`/images/logo-restoku.png`,alt:`Restoku`,class:`h-10 w-10 object-contain rounded-lg`},null,8,vt),y(`div`,yt,[y(`span`,bt,x(f.value.name||`Restoku`),1),n[0]||=y(`span`,{class:`text-[10px] text-slate-500 uppercase tracking-widest font-semibold`},`Dashboard`,-1)])])),y(`button`,{onClick:C,class:`p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors mx-auto`},[y(`i`,{class:w([`pi`,h.value?`pi-align-left`:`pi-bars`,`text-xl`])},null,2)])]),y(`nav`,xt,[(_(!0),d(T,null,s(k.value,e=>(_(),d(`div`,{key:e.label,class:`space-y-1`},[e.label===`common.dashboard`?(_(),m(a,{key:1,to:`/dashboard`,class:w([`flex items-center gap-4 rounded-xl py-3 px-4 text-sm font-semibold transition-all duration-200 group`,b(i).path===`/dashboard`?`bg-emerald-50 text-emerald-600 shadow-sm`:`text-slate-500 hover:bg-slate-50 hover:text-slate-900`])},{default:l(()=>[n[1]||=y(`i`,{class:`pi pi-home text-lg`},null,-1),h.value?p(``,!0):(_(),d(`span`,Tt,x(t.$t(`common.overview`)),1))]),_:1},8,[`class`])):(_(),d(`button`,{key:0,onClick:t=>E(e.label),class:w([`w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200`,D(e.label)?`text-slate-900 bg-slate-50`:`text-slate-400 hover:text-slate-700 hover:bg-slate-50`])},[y(`div`,Ct,[y(`i`,{class:w([e.icon||`pi pi-folder`,`text-lg`])},null,2),h.value?p(``,!0):(_(),d(`span`,wt,x(t.$t(e.label)),1))]),h.value?p(``,!0):(_(),d(`i`,{key:0,class:w([`pi pi-chevron-down text-[10px] transition-transform duration-300`,D(e.label)?`rotate-180`:``])},null,2))],10,St)),e.label===`common.dashboard`||D(e.label)?(_(),d(`div`,{key:2,class:w([`space-y-1 transition-all duration-300 overflow-hidden`,e.label!==`common.dashboard`&&!h.value?`pl-4 border-l-2 border-slate-100 ml-6 mt-1`:``])},[e.label===`common.dashboard`?p(``,!0):(_(!0),d(T,{key:0},s(e.items,e=>(_(),m(o(e.external?`a`:`router-link`),{key:e.to,to:e.external?void 0:e.to,href:e.external?e.to:void 0,target:e.external?`_blank`:void 0,class:w([`flex items-center gap-4 rounded-xl py-2.5 text-sm font-medium transition-all duration-200 group relative`,h.value?`justify-center px-0`:`px-4`,!e.external&&(b(i).path===e.to||b(i).path.startsWith(e.to+`/`))?`text-emerald-600 font-bold`:`text-slate-500 hover:text-slate-900`]),onClick:n=>e.external?null:t.$emit(`update:modelValue`,!1)},{default:l(()=>[h.value?(_(),d(`i`,{key:0,class:w([e.icon,`text-lg transition-transform duration-200 group-hover:scale-110`])},null,2)):p(``,!0),h.value?p(``,!0):(_(),d(`span`,Et,x(t.$t(e.label)),1)),h.value?(_(),d(`div`,Dt,x(t.$t(e.label)),1)):p(``,!0)]),_:2},1032,[`to`,`href`,`target`,`class`,`onClick`]))),128))],2)):p(``,!0)]))),128))]),h.value?(_(),d(`div`,Ot,[...n[2]||=[y(`i`,{class:`pi pi-info-circle text-lg`},null,-1)]])):(_(),d(`div`,kt,[...n[3]||=[y(`p`,{class:`font-semibold text-slate-500 mb-1`},`Restoku POS v2.0`,-1),y(`p`,null,`© 2026 All Rights Reserved`,-1)]]))],2)}}},jt=B.extend({name:`menu`,style:`
    .p-menu {
        background: dt('menu.background');
        color: dt('menu.color');
        border: 1px solid dt('menu.border.color');
        border-radius: dt('menu.border.radius');
        min-width: 12.5rem;
    }

    .p-menu-list {
        margin: 0;
        padding: dt('menu.list.padding');
        outline: 0 none;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: dt('menu.list.gap');
    }

    .p-menu-item-content {
        transition:
            background dt('menu.transition.duration'),
            color dt('menu.transition.duration');
        border-radius: dt('menu.item.border.radius');
        color: dt('menu.item.color');
        overflow: hidden;
    }

    .p-menu-item-link {
        cursor: pointer;
        display: flex;
        align-items: center;
        text-decoration: none;
        overflow: hidden;
        position: relative;
        color: inherit;
        padding: dt('menu.item.padding');
        gap: dt('menu.item.gap');
        user-select: none;
        outline: 0 none;
    }

    .p-menu-item-label {
        line-height: 1;
    }

    .p-menu-item-icon {
        color: dt('menu.item.icon.color');
    }

    .p-menu-item.p-focus .p-menu-item-content {
        color: dt('menu.item.focus.color');
        background: dt('menu.item.focus.background');
    }

    .p-menu-item.p-focus .p-menu-item-icon {
        color: dt('menu.item.icon.focus.color');
    }

    .p-menu-item:not(.p-disabled) .p-menu-item-content:hover {
        color: dt('menu.item.focus.color');
        background: dt('menu.item.focus.background');
    }

    .p-menu-item:not(.p-disabled) .p-menu-item-content:hover .p-menu-item-icon {
        color: dt('menu.item.icon.focus.color');
    }

    .p-menu-overlay {
        box-shadow: dt('menu.shadow');
    }

    .p-menu-submenu-label {
        background: dt('menu.submenu.label.background');
        padding: dt('menu.submenu.label.padding');
        color: dt('menu.submenu.label.color');
        font-weight: dt('menu.submenu.label.font.weight');
    }

    .p-menu-separator {
        border-block-start: 1px solid dt('menu.separator.border.color');
    }
`,classes:{root:function(e){return[`p-menu p-component`,{"p-menu-overlay":e.props.popup}]},start:`p-menu-start`,list:`p-menu-list`,submenuLabel:`p-menu-submenu-label`,separator:`p-menu-separator`,end:`p-menu-end`,item:function(e){var t=e.instance;return[`p-menu-item`,{"p-focus":t.id===t.focusedOptionId,"p-disabled":t.disabled()}]},itemContent:`p-menu-item-content`,itemLink:`p-menu-item-link`,itemIcon:`p-menu-item-icon`,itemLabel:`p-menu-item-label`}}),Mt={name:`BaseMenu`,extends:A,props:{popup:{type:Boolean,default:!1},model:{type:Array,default:null},appendTo:{type:[String,Object],default:`body`},autoZIndex:{type:Boolean,default:!0},baseZIndex:{type:Number,default:0},tabindex:{type:Number,default:0},ariaLabel:{type:String,default:null},ariaLabelledby:{type:String,default:null}},style:jt,provide:function(){return{$pcMenu:this,$parentInstance:this}}},Nt={name:`Menuitem`,hostName:`Menu`,extends:A,inheritAttrs:!1,emits:[`item-click`,`item-mousemove`],props:{item:null,templates:null,id:null,focusedOptionId:null,index:null},methods:{getItemProp:function(e,t){return e&&e.item?se(e.item[t]):void 0},getPTOptions:function(e){return this.ptm(e,{context:{item:this.item,index:this.index,focused:this.isItemFocused(),disabled:this.disabled()}})},isItemFocused:function(){return this.focusedOptionId===this.id},onItemClick:function(e){var t=this.getItemProp(this.item,`command`);t&&t({originalEvent:e,item:this.item.item}),this.$emit(`item-click`,{originalEvent:e,item:this.item,id:this.id})},onItemMouseMove:function(e){this.$emit(`item-mousemove`,{originalEvent:e,item:this.item,id:this.id})},visible:function(){return typeof this.item.visible==`function`?this.item.visible():this.item.visible!==!1},disabled:function(){return typeof this.item.disabled==`function`?this.item.disabled():this.item.disabled},label:function(){return typeof this.item.label==`function`?this.item.label():this.item.label},getMenuItemProps:function(e){return{action:n({class:this.cx(`itemLink`),tabindex:`-1`},this.getPTOptions(`itemLink`)),icon:n({class:[this.cx(`itemIcon`),e.icon]},this.getPTOptions(`itemIcon`)),label:n({class:this.cx(`itemLabel`)},this.getPTOptions(`itemLabel`))}}},computed:{dataP:function(){return j({focus:this.isItemFocused(),disabled:this.disabled()})}},directives:{ripple:k}},Pt=[`id`,`aria-label`,`aria-disabled`,`data-p-focused`,`data-p-disabled`,`data-p`],Ft=[`data-p`],It=[`href`,`target`],Lt=[`data-p`],Rt=[`data-p`];function zt(e,t,r,s,c,l){var u=a(`ripple`);return l.visible()?(_(),d(`li`,n({key:0,id:r.id,class:[e.cx(`item`),r.item.class],role:`menuitem`,style:r.item.style,"aria-label":l.label(),"aria-disabled":l.disabled(),"data-p-focused":l.isItemFocused(),"data-p-disabled":l.disabled()||!1,"data-p":l.dataP},l.getPTOptions(`item`)),[y(`div`,n({class:e.cx(`itemContent`),onClick:t[0]||=function(e){return l.onItemClick(e)},onMousemove:t[1]||=function(e){return l.onItemMouseMove(e)},"data-p":l.dataP},l.getPTOptions(`itemContent`)),[r.templates.item?r.templates.item?(_(),m(o(r.templates.item),{key:1,item:r.item,label:l.label(),props:l.getMenuItemProps(r.item)},null,8,[`item`,`label`,`props`])):p(``,!0):i((_(),d(`a`,n({key:0,href:r.item.url,class:e.cx(`itemLink`),target:r.item.target,tabindex:`-1`},l.getPTOptions(`itemLink`)),[r.templates.itemicon?(_(),m(o(r.templates.itemicon),{key:0,item:r.item,class:w(e.cx(`itemIcon`))},null,8,[`item`,`class`])):r.item.icon?(_(),d(`span`,n({key:1,class:[e.cx(`itemIcon`),r.item.icon],"data-p":l.dataP},l.getPTOptions(`itemIcon`)),null,16,Lt)):p(``,!0),y(`span`,n({class:e.cx(`itemLabel`),"data-p":l.dataP},l.getPTOptions(`itemLabel`)),x(l.label()),17,Rt)],16,It)),[[u]])],16,Ft)],16,Pt)):p(``,!0)}Nt.render=zt;function Bt(e){return Wt(e)||Ut(e)||Ht(e)||Vt()}function Vt(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Ht(e,t){if(e){if(typeof e==`string`)return $(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?$(e,t):void 0}}function Ut(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Wt(e){if(Array.isArray(e))return $(e)}function $(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var Gt={name:`Menu`,extends:Mt,inheritAttrs:!1,emits:[`show`,`hide`,`focus`,`blur`],data:function(){return{overlayVisible:!1,focused:!1,focusedOptionIndex:-1,selectedOptionIndex:-1}},target:null,outsideClickListener:null,scrollHandler:null,resizeListener:null,container:null,list:null,mounted:function(){this.popup||(this.bindResizeListener(),this.bindOutsideClickListener())},beforeUnmount:function(){this.unbindResizeListener(),this.unbindOutsideClickListener(),this.scrollHandler&&=(this.scrollHandler.destroy(),null),this.target=null,this.container&&this.autoZIndex&&M.clear(this.container),this.container=null},methods:{itemClick:function(e){var t=e.item;this.disabled(t)||(t.command&&t.command(e),this.overlayVisible&&this.hide(),!this.popup&&this.focusedOptionIndex!==e.id&&(this.focusedOptionIndex=e.id))},itemMouseMove:function(e){this.focused&&(this.focusedOptionIndex=e.id)},onListFocus:function(e){this.focused=!0,!this.popup&&this.changeFocusedOptionIndex(0),this.$emit(`focus`,e)},onListBlur:function(e){this.focused=!1,this.focusedOptionIndex=-1,this.$emit(`blur`,e)},onListKeyDown:function(e){switch(e.code){case`ArrowDown`:this.onArrowDownKey(e);break;case`ArrowUp`:this.onArrowUpKey(e);break;case`Home`:this.onHomeKey(e);break;case`End`:this.onEndKey(e);break;case`Enter`:case`NumpadEnter`:this.onEnterKey(e);break;case`Space`:this.onSpaceKey(e);break;case`Escape`:this.popup&&(L(this.target),this.hide());case`Tab`:this.overlayVisible&&this.hide();break}},onArrowDownKey:function(e){var t=this.findNextOptionIndex(this.focusedOptionIndex);this.changeFocusedOptionIndex(t),e.preventDefault()},onArrowUpKey:function(e){if(e.altKey&&this.popup)L(this.target),this.hide(),e.preventDefault();else{var t=this.findPrevOptionIndex(this.focusedOptionIndex);this.changeFocusedOptionIndex(t),e.preventDefault()}},onHomeKey:function(e){this.changeFocusedOptionIndex(0),e.preventDefault()},onEndKey:function(e){this.changeFocusedOptionIndex(N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`).length-1),e.preventDefault()},onEnterKey:function(e){var t=R(this.list,`li[id="${`${this.focusedOptionIndex}`}"]`),n=t&&R(t,`a[data-pc-section="itemlink"]`);this.popup&&L(this.target),n?n.click():t&&t.click(),e.preventDefault()},onSpaceKey:function(e){this.onEnterKey(e)},findNextOptionIndex:function(e){var t=Bt(N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`)).findIndex(function(t){return t.id===e});return t>-1?t+1:0},findPrevOptionIndex:function(e){var t=Bt(N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`)).findIndex(function(t){return t.id===e});return t>-1?t-1:0},changeFocusedOptionIndex:function(e){var t=N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`),n=e>=t.length?t.length-1:e<0?0:e;n>-1&&(this.focusedOptionIndex=t[n].getAttribute(`id`))},toggle:function(e,t){this.overlayVisible?this.hide():this.show(e,t)},show:function(e,t){this.overlayVisible=!0,this.target=t??e.currentTarget},hide:function(){this.overlayVisible=!1,this.target=null},onEnter:function(e){ne(e,{position:`absolute`,top:`0`}),this.alignOverlay(),this.bindOutsideClickListener(),this.bindResizeListener(),this.bindScrollListener(),this.autoZIndex&&M.set(`menu`,e,this.baseZIndex||this.$primevue.config.zIndex.menu),this.popup&&L(this.list),this.$emit(`show`)},onLeave:function(){this.unbindOutsideClickListener(),this.unbindResizeListener(),this.unbindScrollListener(),this.$emit(`hide`)},onAfterLeave:function(e){this.autoZIndex&&M.clear(e)},alignOverlay:function(){oe(this.container,this.target),I(this.target)>I(this.container)&&(this.container.style.minWidth=I(this.target)+`px`)},bindOutsideClickListener:function(){var e=this;this.outsideClickListener||(this.outsideClickListener=function(t){var n=e.container&&!e.container.contains(t.target),r=!(e.target&&(e.target===t.target||e.target.contains(t.target)));e.overlayVisible&&n&&r?e.hide():!e.popup&&n&&r&&(e.focusedOptionIndex=-1)},document.addEventListener(`click`,this.outsideClickListener,!0))},unbindOutsideClickListener:function(){this.outsideClickListener&&=(document.removeEventListener(`click`,this.outsideClickListener,!0),null)},bindScrollListener:function(){var e=this;this.scrollHandler||=new ce(this.target,function(){e.overlayVisible&&e.hide()}),this.scrollHandler.bindScrollListener()},unbindScrollListener:function(){this.scrollHandler&&this.scrollHandler.unbindScrollListener()},bindResizeListener:function(){var e=this;this.resizeListener||(this.resizeListener=function(){e.overlayVisible&&!ie()&&e.hide()},window.addEventListener(`resize`,this.resizeListener))},unbindResizeListener:function(){this.resizeListener&&=(window.removeEventListener(`resize`,this.resizeListener),null)},visible:function(e){return typeof e.visible==`function`?e.visible():e.visible!==!1},disabled:function(e){return typeof e.disabled==`function`?e.disabled():e.disabled},label:function(e){return typeof e.label==`function`?e.label():e.label},onOverlayClick:function(e){_e.emit(`overlay-click`,{originalEvent:e,target:this.target})},containerRef:function(e){this.container=e},listRef:function(e){this.list=e}},computed:{focusedOptionId:function(){return this.focusedOptionIndex===-1?null:this.focusedOptionIndex},dataP:function(){return j({popup:this.popup})}},components:{PVMenuitem:Nt,Portal:de}},Kt=[`id`,`data-p`],qt=[`id`,`tabindex`,`aria-activedescendant`,`aria-label`,`aria-labelledby`],Jt=[`id`];function Yt(e,t,i,a,o,u){var h=r(`PVMenuitem`),g=r(`Portal`);return _(),m(g,{appendTo:e.appendTo,disabled:!e.popup},{default:l(function(){return[D(C,n({name:`p-anchored-overlay`,onEnter:u.onEnter,onLeave:u.onLeave,onAfterLeave:u.onAfterLeave},e.ptm(`transition`)),{default:l(function(){return[!e.popup||o.overlayVisible?(_(),d(`div`,n({key:0,ref:u.containerRef,id:e.$id,class:e.cx(`root`),onClick:t[3]||=function(){return u.onOverlayClick&&u.onOverlayClick.apply(u,arguments)},"data-p":u.dataP},e.ptmi(`root`)),[e.$slots.start?(_(),d(`div`,n({key:0,class:e.cx(`start`)},e.ptm(`start`)),[c(e.$slots,`start`)],16)):p(``,!0),y(`ul`,n({ref:u.listRef,id:e.$id+`_list`,class:e.cx(`list`),role:`menu`,tabindex:e.tabindex,"aria-activedescendant":o.focused?u.focusedOptionId:void 0,"aria-label":e.ariaLabel,"aria-labelledby":e.ariaLabelledby,onFocus:t[0]||=function(){return u.onListFocus&&u.onListFocus.apply(u,arguments)},onBlur:t[1]||=function(){return u.onListBlur&&u.onListBlur.apply(u,arguments)},onKeydown:t[2]||=function(){return u.onListKeyDown&&u.onListKeyDown.apply(u,arguments)}},e.ptm(`list`)),[(_(!0),d(T,null,s(e.model,function(t,r){return _(),d(T,{key:u.label(t)+r.toString()},[t.items&&u.visible(t)&&!t.separator?(_(),d(T,{key:0},[t.items?(_(),d(`li`,n({key:0,id:e.$id+`_`+r,class:[e.cx(`submenuLabel`),t.class],role:`none`},{ref_for:!0},e.ptm(`submenuLabel`)),[c(e.$slots,e.$slots.submenulabel?`submenulabel`:`submenuheader`,{item:t},function(){return[f(x(u.label(t)),1)]})],16,Jt)):p(``,!0),(_(!0),d(T,null,s(t.items,function(i,a){return _(),d(T,{key:i.label+r+`_`+a},[u.visible(i)&&!i.separator?(_(),m(h,{key:0,id:e.$id+`_`+r+`_`+a,item:i,templates:e.$slots,focusedOptionId:u.focusedOptionId,unstyled:e.unstyled,onItemClick:u.itemClick,onItemMousemove:u.itemMouseMove,pt:e.pt},null,8,[`id`,`item`,`templates`,`focusedOptionId`,`unstyled`,`onItemClick`,`onItemMousemove`,`pt`])):u.visible(i)&&i.separator?(_(),d(`li`,n({key:`separator`+r+a,class:[e.cx(`separator`),t.class],style:i.style,role:`separator`},{ref_for:!0},e.ptm(`separator`)),null,16)):p(``,!0)],64)}),128))],64)):u.visible(t)&&t.separator?(_(),d(`li`,n({key:`separator`+r.toString(),class:[e.cx(`separator`),t.class],style:t.style,role:`separator`},{ref_for:!0},e.ptm(`separator`)),null,16)):(_(),m(h,{key:u.label(t)+r.toString(),id:e.$id+`_`+r,item:t,index:r,templates:e.$slots,focusedOptionId:u.focusedOptionId,unstyled:e.unstyled,onItemClick:u.itemClick,onItemMousemove:u.itemMouseMove,pt:e.pt},null,8,[`id`,`item`,`index`,`templates`,`focusedOptionId`,`unstyled`,`onItemClick`,`onItemMousemove`,`pt`]))],64)}),128))],16,qt),e.$slots.end?(_(),d(`div`,n({key:1,class:e.cx(`end`)},e.ptm(`end`)),[c(e.$slots,`end`)],16)):p(``,!0)],16,Kt)):p(``,!0)]}),_:3},16,[`onEnter`,`onLeave`,`onAfterLeave`])]}),_:3},8,[`appendTo`,`disabled`])}Gt.render=Yt;var Xt={class:`flex h-20 shrink-0 items-center justify-between px-6 lg:px-10 bg-transparent`},Zt={class:`flex items-center gap-4 flex-1`},Qt={class:`lg:hidden`},$t={class:`hidden lg:flex flex-col items-start px-2`},en={class:`flex items-center gap-1.5`},tn={class:`text-sm font-medium text-slate-500`},nn={class:`text-sm font-bold text-slate-800`},rn={class:`text-sm font-medium text-slate-500`},an={class:`font-bold text-emerald-600`},on={class:`text-[10px] text-slate-400 mt-0.5 flex items-center gap-2`},sn={class:`flex items-center`},cn={class:`flex items-center font-mono font-bold text-slate-500`},ln={class:`ml-1 italic text-slate-400`},un={class:`flex items-center gap-4 lg:gap-6`},dn={class:`text-right hidden md:block`},fn={class:`text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5`},pn={class:`text-xs text-emerald-600 font-bold capitalize`},mn=[`src`],hn={__name:`AppHeader`,emits:[`toggle-sidebar`],setup(t){let n=ae(),r=F(),i=V(),a=u(),o=u(``),s=null,c=S(()=>new Intl.DateTimeFormat(`id-ID`,{day:`numeric`,month:`long`,year:`numeric`}).format(new Date)),l=S(()=>r.user?.attributes?.permissions||[]),h=S(()=>r.user?.relationships?.tenant?.data?.attributes||{}),g=S(()=>[{label:C(`user_menu.profile`),icon:`pi pi-user`,command:()=>n.push(`/settings/profile`)},{label:C(`user_menu.users`),icon:`pi pi-users`,command:()=>n.push(`/settings/users`),visible:l.value.includes(`manage-users`)},{separator:!0},{label:C(`user_menu.logout`),icon:`pi pi-sign-out`,command:async()=>{await r.logout(),n.push(`/login`)}}]),{t:C}=le();function w(e){a.value?.toggle(e)}function T(){o.value=new Date().toLocaleTimeString(`id-ID`,{hour:`2-digit`,minute:`2-digit`,second:`2-digit`})}return v(()=>{T(),s=setInterval(T,1e3)}),e(()=>{s&&clearInterval(s)}),(e,t)=>(_(),d(`header`,Xt,[y(`div`,Zt,[y(`div`,Qt,[D(b(ee),{icon:`pi pi-bars`,text:``,rounded:``,class:`!text-slate-800 bg-white shadow-sm`,onClick:t[0]||=t=>e.$emit(`toggle-sidebar`)})]),y(`div`,$t,[y(`div`,en,[y(`span`,tn,x(b(C)(`header.greeting`))+`,`,1),y(`span`,nn,x(b(r).user?.attributes?.name?.split(` `)[0]||`Admin`),1),y(`span`,rn,[f(`! `+x(b(C)(`header.welcome_back`))+` `+x(h.value.name?`di `:``),1),y(`span`,an,x(h.value.name),1),t[1]||=f(`.`,-1)])]),y(`p`,on,[y(`span`,sn,[t[2]||=y(`i`,{class:`pi pi-calendar text-[10px] mr-1`},null,-1),f(` `+x(c.value),1)]),t[4]||=y(`span`,{class:`w-px h-2 bg-slate-300`},null,-1),y(`span`,cn,[t[3]||=y(`i`,{class:`pi pi-clock text-[10px] mr-1`},null,-1),f(` `+x(o.value),1)]),y(`span`,ln,`. `+x(b(C)(`header.nice_day`)),1)])])]),y(`div`,un,[b(i).activeShift?(_(),m(b(ge),{key:0,icon:`pi pi-clock`,value:b(C)(`header.shift_open`),severity:`success`,class:`hidden sm:flex shadow-sm !rounded-xl px-3 py-1.5`},null,8,[`value`])):p(``,!0),t[5]||=y(`div`,{class:`h-8 w-px bg-slate-200 hidden md:block`},null,-1),y(`button`,{class:`flex items-center gap-3 hover:bg-white/50 p-1.5 rounded-2xl transition-colors`,onClick:w},[y(`div`,dn,[y(`p`,fn,x(b(C)(`header.logged_in_as`)),1),y(`p`,pn,x(b(r).user?.attributes?.role||`Administrator`),1)]),y(`img`,{src:b(r).user?.attributes?.avatar_url||`https://ui-avatars.com/api/?name=${b(r).user?.attributes?.name||`Admin`}&background=10b981&color=fff`,alt:`User`,class:`w-10 h-10 rounded-full border-2 border-white shadow-sm`},null,8,mn)]),D(b(Gt),{ref_key:`userMenu`,ref:a,model:g.value,popup:``,class:`!rounded-xl !shadow-xl !border-slate-100`},null,8,[`model`])])]))}},gn={class:`flex h-screen bg-slate-50 font-sans text-slate-800`},_n={class:`flex flex-1 flex-col overflow-hidden`},vn={class:`flex-1 overflow-y-auto p-4 lg:p-8`},yn={__name:`AppLayout`,setup(e){let t=V(),n=u(!1);return v(()=>{t.fetchCurrentShift()}),(e,t)=>{let i=r(`router-view`);return _(),d(T,null,[y(`div`,gn,[n.value?(_(),d(`div`,{key:0,class:`fixed inset-0 z-40 bg-black/50 lg:hidden`,onClick:t[0]||=e=>n.value=!1})):p(``,!0),D(At,{modelValue:n.value,"onUpdate:modelValue":t[1]||=e=>n.value=e},null,8,[`modelValue`]),y(`div`,_n,[D(hn,{onToggleSidebar:t[2]||=e=>n.value=!n.value}),y(`main`,vn,[D(i)])])]),D(b(at),{position:`top-right`}),D(b(mt))],64)}}};export{yn as default};