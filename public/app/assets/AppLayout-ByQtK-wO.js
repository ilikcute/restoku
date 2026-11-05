import{C as e,F as t,G as n,I as r,L as i,M as a,N as o,R as s,S as c,W as l,_ as u,at as d,b as f,c as p,et as m,f as h,g,h as _,j as v,k as y,lt as b,n as x,ot as S,r as C,st as w,u as ee,v as T,x as E,y as D,z as O}from"./vue.runtime.esm-bundler-CEQQ6xgQ.js";import{a as k,i as A,o as j,s as M,t as te}from"./button-DuJ5kZ4A.js";import{$ as ne,At as re,K as ie,M as ae,Q as N,S as P,c as oe,d as se,et as F,f as ce,ft as I,h as le,k as L,kt as ue,mt as de,r as fe,rt as R,t as pe,x as z,y as B}from"./index-C91iR5W3.js";import{t as me}from"./shift-Bxvr2VNr.js";import{n as he,t as ge}from"./times-CN7mH7cF.js";import{t as _e}from"./check-CsA2CFWi.js";import{t as ve}from"./timescircle-BSZFQv9P.js";import{t as ye}from"./dialog-DGWqvGDH.js";import{t as be}from"./overlayeventbus-COuQkGjf.js";var xe=`
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
`;function V(e){"@babel/helpers - typeof";return V=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},V(e)}function H(e,t,n){return(t=Se(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Se(e){var t=Ce(e,`string`);return V(t)==`symbol`?t:t+``}function Ce(e,t){if(V(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(V(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var we=P.extend({name:`toast`,style:xe,classes:{root:function(e){return[`p-toast p-component p-toast-`+e.props.position]},message:function(e){var t=e.props;return[`p-toast-message`,{"p-toast-message-info":t.message.severity===`info`||t.message.severity===void 0,"p-toast-message-warn":t.message.severity===`warn`,"p-toast-message-error":t.message.severity===`error`,"p-toast-message-success":t.message.severity===`success`,"p-toast-message-secondary":t.message.severity===`secondary`,"p-toast-message-contrast":t.message.severity===`contrast`}]},messageContent:`p-toast-message-content`,messageIcon:function(e){var t=e.props;return[`p-toast-message-icon`,H(H(H(H({},t.infoIcon,t.message.severity===`info`),t.warnIcon,t.message.severity===`warn`),t.errorIcon,t.message.severity===`error`),t.successIcon,t.message.severity===`success`)]},messageText:`p-toast-message-text`,summary:`p-toast-summary`,detail:`p-toast-detail`,closeButton:`p-toast-close-button`,closeIcon:`p-toast-close-icon`},inlineStyles:{root:function(e){var t=e.position;return{position:`fixed`,top:t===`top-right`||t===`top-left`||t===`top-center`?`20px`:t===`center`?`50%`:null,right:(t===`top-right`||t===`bottom-right`)&&`20px`,bottom:(t===`bottom-left`||t===`bottom-right`||t===`bottom-center`)&&`20px`,left:t===`top-left`||t===`bottom-left`?`20px`:t===`center`||t===`top-center`||t===`bottom-center`?`50%`:null}}}}),U={name:`ExclamationTriangleIcon`,extends:k};function Te(e){return ke(e)||Oe(e)||De(e)||Ee()}function Ee(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function De(e,t){if(e){if(typeof e==`string`)return W(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?W(e,t):void 0}}function Oe(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function ke(e){if(Array.isArray(e))return W(e)}function W(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function Ae(e,t,n,r,i,a){return o(),D(`svg`,y({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),Te(t[0]||=[g(`path`,{d:`M13.4018 13.1893H0.598161C0.49329 13.189 0.390283 13.1615 0.299143 13.1097C0.208003 13.0578 0.131826 12.9832 0.0780112 12.8932C0.0268539 12.8015 0 12.6982 0 12.5931C0 12.4881 0.0268539 12.3848 0.0780112 12.293L6.47985 1.08982C6.53679 1.00399 6.61408 0.933574 6.70484 0.884867C6.7956 0.836159 6.897 0.810669 7 0.810669C7.103 0.810669 7.2044 0.836159 7.29516 0.884867C7.38592 0.933574 7.46321 1.00399 7.52015 1.08982L13.922 12.293C13.9731 12.3848 14 12.4881 14 12.5931C14 12.6982 13.9731 12.8015 13.922 12.8932C13.8682 12.9832 13.792 13.0578 13.7009 13.1097C13.6097 13.1615 13.5067 13.189 13.4018 13.1893ZM1.63046 11.989H12.3695L7 2.59425L1.63046 11.989Z`,fill:`currentColor`},null,-1),g(`path`,{d:`M6.99996 8.78801C6.84143 8.78594 6.68997 8.72204 6.57787 8.60993C6.46576 8.49782 6.40186 8.34637 6.39979 8.18784V5.38703C6.39979 5.22786 6.46302 5.0752 6.57557 4.96265C6.68813 4.85009 6.84078 4.78686 6.99996 4.78686C7.15914 4.78686 7.31179 4.85009 7.42435 4.96265C7.5369 5.0752 7.60013 5.22786 7.60013 5.38703V8.18784C7.59806 8.34637 7.53416 8.49782 7.42205 8.60993C7.30995 8.72204 7.15849 8.78594 6.99996 8.78801Z`,fill:`currentColor`},null,-1),g(`path`,{d:`M6.99996 11.1887C6.84143 11.1866 6.68997 11.1227 6.57787 11.0106C6.46576 10.8985 6.40186 10.7471 6.39979 10.5885V10.1884C6.39979 10.0292 6.46302 9.87658 6.57557 9.76403C6.68813 9.65147 6.84078 9.58824 6.99996 9.58824C7.15914 9.58824 7.31179 9.65147 7.42435 9.76403C7.5369 9.87658 7.60013 10.0292 7.60013 10.1884V10.5885C7.59806 10.7471 7.53416 10.8985 7.42205 11.0106C7.30995 11.1227 7.15849 11.1866 6.99996 11.1887Z`,fill:`currentColor`},null,-1)]),16)}U.render=Ae;var G={name:`InfoCircleIcon`,extends:k};function je(e){return Fe(e)||Pe(e)||Ne(e)||Me()}function Me(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Ne(e,t){if(e){if(typeof e==`string`)return K(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?K(e,t):void 0}}function Pe(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Fe(e){if(Array.isArray(e))return K(e)}function K(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function Ie(e,t,n,r,i,a){return o(),D(`svg`,y({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),je(t[0]||=[g(`path`,{"fill-rule":`evenodd`,"clip-rule":`evenodd`,d:`M3.11101 12.8203C4.26215 13.5895 5.61553 14 7 14C8.85652 14 10.637 13.2625 11.9497 11.9497C13.2625 10.637 14 8.85652 14 7C14 5.61553 13.5895 4.26215 12.8203 3.11101C12.0511 1.95987 10.9579 1.06266 9.67879 0.532846C8.3997 0.00303296 6.99224 -0.13559 5.63437 0.134506C4.2765 0.404603 3.02922 1.07129 2.05026 2.05026C1.07129 3.02922 0.404603 4.2765 0.134506 5.63437C-0.13559 6.99224 0.00303296 8.3997 0.532846 9.67879C1.06266 10.9579 1.95987 12.0511 3.11101 12.8203ZM3.75918 2.14976C4.71846 1.50879 5.84628 1.16667 7 1.16667C8.5471 1.16667 10.0308 1.78125 11.1248 2.87521C12.2188 3.96918 12.8333 5.45291 12.8333 7C12.8333 8.15373 12.4912 9.28154 11.8502 10.2408C11.2093 11.2001 10.2982 11.9478 9.23232 12.3893C8.16642 12.8308 6.99353 12.9463 5.86198 12.7212C4.73042 12.4962 3.69102 11.9406 2.87521 11.1248C2.05941 10.309 1.50384 9.26958 1.27876 8.13803C1.05367 7.00647 1.16919 5.83358 1.61071 4.76768C2.05222 3.70178 2.79989 2.79074 3.75918 2.14976ZM7.00002 4.8611C6.84594 4.85908 6.69873 4.79698 6.58977 4.68801C6.48081 4.57905 6.4187 4.43185 6.41669 4.27776V3.88888C6.41669 3.73417 6.47815 3.58579 6.58754 3.4764C6.69694 3.367 6.84531 3.30554 7.00002 3.30554C7.15473 3.30554 7.3031 3.367 7.4125 3.4764C7.52189 3.58579 7.58335 3.73417 7.58335 3.88888V4.27776C7.58134 4.43185 7.51923 4.57905 7.41027 4.68801C7.30131 4.79698 7.1541 4.85908 7.00002 4.8611ZM7.00002 10.6945C6.84594 10.6925 6.69873 10.6304 6.58977 10.5214C6.48081 10.4124 6.4187 10.2652 6.41669 10.1111V6.22225C6.41669 6.06754 6.47815 5.91917 6.58754 5.80977C6.69694 5.70037 6.84531 5.63892 7.00002 5.63892C7.15473 5.63892 7.3031 5.70037 7.4125 5.80977C7.52189 5.91917 7.58335 6.06754 7.58335 6.22225V10.1111C7.58134 10.2652 7.51923 10.4124 7.41027 10.5214C7.30131 10.6304 7.1541 10.6925 7.00002 10.6945Z`,fill:`currentColor`},null,-1)]),16)}G.render=Ie;var Le={name:`BaseToast`,extends:j,props:{group:{type:String,default:null},position:{type:String,default:`top-right`},autoZIndex:{type:Boolean,default:!0},baseZIndex:{type:Number,default:0},breakpoints:{type:Object,default:null},closeIcon:{type:String,default:void 0},infoIcon:{type:String,default:void 0},warnIcon:{type:String,default:void 0},errorIcon:{type:String,default:void 0},successIcon:{type:String,default:void 0},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},style:we,provide:function(){return{$pcToast:this,$parentInstance:this}}};function q(e){"@babel/helpers - typeof";return q=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},q(e)}function Re(e,t,n){return(t=ze(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function ze(e){var t=Be(e,`string`);return q(t)==`symbol`?t:t+``}function Be(e,t){if(q(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(q(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var Ve={name:`ToastMessage`,hostName:`Toast`,extends:j,emits:[`close`],closeTimeout:null,createdAt:null,lifeRemaining:null,props:{message:{type:null,default:null},templates:{type:Object,default:null},closeIcon:{type:String,default:null},infoIcon:{type:String,default:null},warnIcon:{type:String,default:null},errorIcon:{type:String,default:null},successIcon:{type:String,default:null},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},mounted:function(){this.message.life&&(this.lifeRemaining=this.message.life,this.startTimeout())},beforeUnmount:function(){this.clearCloseTimeout()},methods:{startTimeout:function(){var e=this;this.createdAt=new Date().valueOf(),this.closeTimeout=setTimeout(function(){e.close({message:e.message,type:`life-end`})},this.lifeRemaining)},close:function(e){this.$emit(`close`,e)},onCloseClick:function(){this.clearCloseTimeout(),this.close({message:this.message,type:`close`})},clearCloseTimeout:function(){this.closeTimeout&&=(clearTimeout(this.closeTimeout),null)},onMessageClick:function(e){var t;(t=this.onClick)==null||t.call(this,{originalEvent:e,message:this.message})},handleMouseEnter:function(e){if(this.onMouseEnter){if(this.onMouseEnter({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&(this.lifeRemaining=this.createdAt+this.lifeRemaining-new Date().valueOf(),this.createdAt=null,this.clearCloseTimeout())}},handleMouseLeave:function(e){if(this.onMouseLeave){if(this.onMouseLeave({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&this.startTimeout()}}},computed:{iconComponent:function(){return{info:!this.infoIcon&&G,success:!this.successIcon&&_e,warn:!this.warnIcon&&U,error:!this.errorIcon&&ve}[this.message.severity]},closeAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.close:void 0},dataP:function(){return M(Re({},this.message.severity,this.message.severity))}},components:{TimesIcon:ge,InfoCircleIcon:G,CheckIcon:_e,ExclamationTriangleIcon:U,TimesCircleIcon:ve},directives:{ripple:A}};function J(e){"@babel/helpers - typeof";return J=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},J(e)}function He(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function Ue(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?He(Object(n),!0).forEach(function(t){We(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):He(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function We(e,t,n){return(t=Ge(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Ge(e){var t=Ke(e,`string`);return J(t)==`symbol`?t:t+``}function Ke(e,t){if(J(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(J(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var qe=[`data-p`],Je=[`data-p`],Ye=[`data-p`],Xe=[`data-p`],Ze=[`aria-label`,`data-p`];function Qe(e,t,r,i,a,c){var l=s(`ripple`);return o(),D(`div`,y({class:[e.cx(`message`),r.message.styleClass],role:`alert`,"aria-live":`assertive`,"aria-atomic":`true`,"data-p":c.dataP},e.ptm(`message`),{onClick:t[1]||=function(){return c.onMessageClick&&c.onMessageClick.apply(c,arguments)},onMouseenter:t[2]||=function(){return c.handleMouseEnter&&c.handleMouseEnter.apply(c,arguments)},onMouseleave:t[3]||=function(){return c.handleMouseLeave&&c.handleMouseLeave.apply(c,arguments)}}),[r.templates.container?(o(),u(O(r.templates.container),{key:0,message:r.message,closeCallback:c.onCloseClick},null,8,[`message`,`closeCallback`])):(o(),D(`div`,y({key:1,class:[e.cx(`messageContent`),r.message.contentStyleClass]},e.ptm(`messageContent`)),[r.templates.message?(o(),u(O(r.templates.message),{key:1,message:r.message},null,8,[`message`])):(o(),D(h,{key:0},[(o(),u(O(r.templates.messageicon?r.templates.messageicon:r.templates.icon?r.templates.icon:c.iconComponent&&c.iconComponent.name?c.iconComponent:`span`),y({class:e.cx(`messageIcon`)},e.ptm(`messageIcon`)),null,16,[`class`])),g(`div`,y({class:e.cx(`messageText`),"data-p":c.dataP},e.ptm(`messageText`)),[g(`span`,y({class:e.cx(`summary`),"data-p":c.dataP},e.ptm(`summary`)),b(r.message.summary),17,Ye),r.message.detail?(o(),D(`div`,y({key:0,class:e.cx(`detail`),"data-p":c.dataP},e.ptm(`detail`)),b(r.message.detail),17,Xe)):T(``,!0)],16,Je)],64)),r.message.closable===!1?T(``,!0):(o(),D(`div`,w(y({key:2},e.ptm(`buttonContainer`))),[n((o(),D(`button`,y({class:e.cx(`closeButton`),type:`button`,"aria-label":c.closeAriaLabel,onClick:t[0]||=function(){return c.onCloseClick&&c.onCloseClick.apply(c,arguments)},autofocus:``,"data-p":c.dataP},Ue(Ue({},r.closeButtonProps),e.ptm(`closeButton`))),[(o(),u(O(r.templates.closeicon||`TimesIcon`),y({class:[e.cx(`closeIcon`),r.closeIcon]},e.ptm(`closeIcon`)),null,16,[`class`]))],16,Ze)),[[l]])],16))],16))],16,qe)}Ve.render=Qe;function Y(e){"@babel/helpers - typeof";return Y=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},Y(e)}function $e(e,t,n){return(t=et(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function et(e){var t=tt(e,`string`);return Y(t)==`symbol`?t:t+``}function tt(e,t){if(Y(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(Y(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}function nt(e){return ot(e)||at(e)||it(e)||rt()}function rt(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function it(e,t){if(e){if(typeof e==`string`)return X(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?X(e,t):void 0}}function at(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function ot(e){if(Array.isArray(e))return X(e)}function X(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var st=0,ct={name:`Toast`,extends:Le,inheritAttrs:!1,emits:[`close`,`life-end`],data:function(){return{messages:[]}},styleElement:null,mounted:function(){z.on(`add`,this.onAdd),z.on(`remove`,this.onRemove),z.on(`remove-group`,this.onRemoveGroup),z.on(`remove-all-groups`,this.onRemoveAllGroups),this.breakpoints&&this.createStyle()},beforeUnmount:function(){this.destroyStyle(),this.$refs.container&&this.autoZIndex&&L.clear(this.$refs.container),z.off(`add`,this.onAdd),z.off(`remove`,this.onRemove),z.off(`remove-group`,this.onRemoveGroup),z.off(`remove-all-groups`,this.onRemoveAllGroups)},methods:{add:function(e){e.id??=st++,this.messages=[].concat(nt(this.messages),[e])},remove:function(e){var t=this.messages.findIndex(function(t){return t.id===e.message.id});t!==-1&&(this.messages.splice(t,1),this.$emit(e.type,{message:e.message}))},onAdd:function(e){this.group==e.group&&this.add(e)},onRemove:function(e){this.remove({message:e,type:`close`})},onRemoveGroup:function(e){this.group===e&&(this.messages=[])},onRemoveAllGroups:function(){var e=this;this.messages.forEach(function(t){return e.$emit(`close`,{message:t})}),this.messages=[]},onEnter:function(){this.autoZIndex&&L.set(`modal`,this.$refs.container,this.baseZIndex||this.$primevue.config.zIndex.modal)},onLeave:function(){var e=this;this.$refs.container&&this.autoZIndex&&ue(this.messages)&&setTimeout(function(){L.clear(e.$refs.container)},200)},createStyle:function(){if(!this.styleElement&&!this.isUnstyled){var e;this.styleElement=document.createElement(`style`),this.styleElement.type=`text/css`,F(this.styleElement,`nonce`,(e=this.$primevue)==null||(e=e.config)==null||(e=e.csp)==null?void 0:e.nonce),document.head.appendChild(this.styleElement);var t=``;for(var n in this.breakpoints){var r=``;for(var i in this.breakpoints[n])r+=i+`:`+this.breakpoints[n][i]+`!important;`;t+=`
                        @media screen and (max-width: ${n}) {
                            .p-toast[${this.$attrSelector}] {
                                ${r}
                            }
                        }
                    `}this.styleElement.innerHTML=t}},destroyStyle:function(){this.styleElement&&=(document.head.removeChild(this.styleElement),null)}},computed:{dataP:function(){return M($e({},this.position,this.position))}},components:{ToastMessage:Ve,Portal:he}};function Z(e){"@babel/helpers - typeof";return Z=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},Z(e)}function lt(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function ut(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?lt(Object(n),!0).forEach(function(t){dt(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):lt(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function dt(e,t,n){return(t=ft(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function ft(e){var t=pt(e,`string`);return Z(t)==`symbol`?t:t+``}function pt(e,t){if(Z(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(Z(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var mt=[`data-p`];function ht(n,r,a,s,c,d){var f=i(`ToastMessage`),p=i(`Portal`);return o(),u(p,null,{default:l(function(){return[g(`div`,y({ref:`container`,class:n.cx(`root`),style:n.sx(`root`,!0,{position:n.position}),"data-p":d.dataP},n.ptmi(`root`)),[e(C,y({name:`p-toast-message`,tag:`div`,onEnter:d.onEnter,onLeave:d.onLeave},ut({},n.ptm(`transition`))),{default:l(function(){return[(o(!0),D(h,null,t(c.messages,function(e){return o(),u(f,{key:e.id,message:e,templates:n.$slots,closeIcon:n.closeIcon,infoIcon:n.infoIcon,warnIcon:n.warnIcon,errorIcon:n.errorIcon,successIcon:n.successIcon,closeButtonProps:n.closeButtonProps,onMouseEnter:n.onMouseEnter,onMouseLeave:n.onMouseLeave,onClick:n.onClick,unstyled:n.unstyled,onClose:r[0]||=function(e){return d.remove(e)},pt:n.pt},null,8,[`message`,`templates`,`closeIcon`,`infoIcon`,`warnIcon`,`errorIcon`,`successIcon`,`closeButtonProps`,`onMouseEnter`,`onMouseLeave`,`onClick`,`unstyled`,`pt`])}),128))]}),_:1},16,[`onEnter`,`onLeave`])],16,mt)]}),_:1})}ct.render=ht;var gt=P.extend({name:`confirmdialog`,style:`
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
`,classes:{root:`p-confirmdialog`,icon:`p-confirmdialog-icon`,message:`p-confirmdialog-message`,pcRejectButton:`p-confirmdialog-reject-button`,pcAcceptButton:`p-confirmdialog-accept-button`}}),_t={name:`ConfirmDialog`,extends:{name:`BaseConfirmDialog`,extends:j,props:{group:String,breakpoints:{type:Object,default:null},draggable:{type:Boolean,default:!0}},style:gt,provide:function(){return{$pcConfirmDialog:this,$parentInstance:this}}},confirmListener:null,closeListener:null,data:function(){return{visible:!1,confirmation:null}},mounted:function(){var e=this;this.confirmListener=function(t){t&&t.group===e.group&&(e.confirmation=t,e.confirmation.onShow&&e.confirmation.onShow(),e.visible=!0)},this.closeListener=function(){e.visible=!1,e.confirmation=null},B.on(`confirm`,this.confirmListener),B.on(`close`,this.closeListener)},beforeUnmount:function(){B.off(`confirm`,this.confirmListener),B.off(`close`,this.closeListener)},methods:{accept:function(){this.confirmation.accept&&this.confirmation.accept(),this.visible=!1},reject:function(){this.confirmation.reject&&this.confirmation.reject(),this.visible=!1},onHide:function(){this.confirmation.onHide&&this.confirmation.onHide(),this.visible=!1}},computed:{appendTo:function(){return this.confirmation?this.confirmation.appendTo:`body`},target:function(){return this.confirmation?this.confirmation.target:null},modal:function(){return this.confirmation?this.confirmation.modal==null?!0:this.confirmation.modal:!0},header:function(){return this.confirmation?this.confirmation.header:null},message:function(){return this.confirmation?this.confirmation.message:null},blockScroll:function(){return this.confirmation?this.confirmation.blockScroll:!0},position:function(){return this.confirmation?this.confirmation.position:null},acceptLabel:function(){if(this.confirmation){var e=this.confirmation;return e.acceptLabel||e.acceptProps?.label||this.$primevue.config.locale.accept}return this.$primevue.config.locale.accept},rejectLabel:function(){if(this.confirmation){var e=this.confirmation;return e.rejectLabel||e.rejectProps?.label||this.$primevue.config.locale.reject}return this.$primevue.config.locale.reject},acceptIcon:function(){var e;return this.confirmation?this.confirmation.acceptIcon:(e=this.confirmation)!=null&&e.acceptProps?this.confirmation.acceptProps.icon:null},rejectIcon:function(){var e;return this.confirmation?this.confirmation.rejectIcon:(e=this.confirmation)!=null&&e.rejectProps?this.confirmation.rejectProps.icon:null},autoFocusAccept:function(){return this.confirmation.defaultFocus===void 0||this.confirmation.defaultFocus===`accept`},autoFocusReject:function(){return this.confirmation.defaultFocus===`reject`},closeOnEscape:function(){return this.confirmation?this.confirmation.closeOnEscape:!0}},components:{Dialog:ye,Button:te}};function vt(t,n,a,s,c,d){var p=i(`Button`),m=i(`Dialog`);return o(),u(m,{visible:c.visible,"onUpdate:visible":[n[2]||=function(e){return c.visible=e},d.onHide],role:`alertdialog`,class:S(t.cx(`root`)),modal:d.modal,header:d.header,blockScroll:d.blockScroll,appendTo:d.appendTo,position:d.position,breakpoints:t.breakpoints,closeOnEscape:d.closeOnEscape,draggable:t.draggable,pt:t.pt,unstyled:t.unstyled},f({default:l(function(){return[t.$slots.container?T(``,!0):(o(),D(h,{key:0},[t.$slots.message?(o(),u(O(t.$slots.message),{key:1,message:c.confirmation},null,8,[`message`])):(o(),D(h,{key:0},[r(t.$slots,`icon`,{},function(){return[t.$slots.icon?(o(),u(O(t.$slots.icon),{key:0,class:S(t.cx(`icon`))},null,8,[`class`])):c.confirmation.icon?(o(),D(`span`,y({key:1,class:[c.confirmation.icon,t.cx(`icon`)]},t.ptm(`icon`)),null,16)):T(``,!0)]}),g(`span`,y({class:t.cx(`message`)},t.ptm(`message`)),b(d.message),17)],64))],64))]}),_:2},[t.$slots.container?{name:`container`,fn:l(function(e){return[r(t.$slots,`container`,{message:c.confirmation,closeCallback:e.closeCallback,acceptCallback:d.accept,rejectCallback:d.reject,initDragCallback:e.initDragCallback})]}),key:`0`}:void 0,t.$slots.container?void 0:{name:`footer`,fn:l(function(){return[e(p,y({class:[t.cx(`pcRejectButton`),c.confirmation.rejectClass],autofocus:d.autoFocusReject,unstyled:t.unstyled,text:c.confirmation.rejectProps?.text||!1,onClick:n[0]||=function(e){return d.reject()}},c.confirmation.rejectProps,{label:d.rejectLabel,pt:t.ptm(`pcRejectButton`)}),f({_:2},[d.rejectIcon||t.$slots.rejecticon?{name:`icon`,fn:l(function(e){return[r(t.$slots,`rejecticon`,{},function(){return[g(`span`,y({class:[d.rejectIcon,e.class]},t.ptm(`pcRejectButton`).icon,{"data-pc-section":`rejectbuttonicon`}),null,16)]})]}),key:`0`}:void 0]),1040,[`class`,`autofocus`,`unstyled`,`text`,`label`,`pt`]),e(p,y({label:d.acceptLabel,class:[t.cx(`pcAcceptButton`),c.confirmation.acceptClass],autofocus:d.autoFocusAccept,unstyled:t.unstyled,onClick:n[1]||=function(e){return d.accept()}},c.confirmation.acceptProps,{pt:t.ptm(`pcAcceptButton`)}),f({_:2},[d.acceptIcon||t.$slots.accepticon?{name:`icon`,fn:l(function(e){return[r(t.$slots,`accepticon`,{},function(){return[g(`span`,y({class:[d.acceptIcon,e.class]},t.ptm(`pcAcceptButton`).icon,{"data-pc-section":`acceptbuttonicon`}),null,16)]})]}),key:`0`}:void 0]),1040,[`label`,`class`,`autofocus`,`unstyled`,`pt`])]}),key:`1`}]),1032,[`visible`,`class`,`modal`,`header`,`blockScroll`,`appendTo`,`position`,`breakpoints`,`closeOnEscape`,`draggable`,`onUpdate:visible`,`pt`,`unstyled`])}_t.render=vt;var yt={class:`flex h-20 items-center justify-between px-5 border-b border-slate-50/60`},bt={key:0,class:`flex items-center gap-3 overflow-hidden animate-fade-in`},xt={key:1,class:`w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center mx-auto shadow-md shadow-emerald-500/10`},St={class:`flex-1 overflow-y-auto px-3 py-6 space-y-1.5 custom-scrollbar`},Ct={key:0,class:`whitespace-nowrap transition-all duration-300 font-display`},wt={key:1,class:`ml-auto w-5 h-5 rounded-full bg-orange-500 text-[10px] font-bold text-white flex items-center justify-center shadow-sm`},Tt={key:2,class:`absolute left-16 top-1/2 -translate-y-1/2 ml-2 px-3 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-xl shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 whitespace-nowrap`},Et={key:0,class:`px-4 py-2 pb-6 space-y-5 shrink-0 border-t border-slate-50/60 mt-auto bg-white`},Dt={__name:`AppSidebar`,props:{modelValue:{type:Boolean,default:!1}},emits:[`update:modelValue`],setup(e,{emit:n}){let r=n,i=se(),a=ce(),s=oe(),c=m(!1),d=_(()=>s.user?.attributes?.permissions||[]);_(()=>s.user?.relationships?.tenant?.data?.attributes||{});let f=[{to:`/dashboard`,icon:`pi pi-chart-pie`,label:`Dashboard`,permission:`view-dashboard`},{to:`/sales/pos`,icon:`pi pi-calculator`,label:`POS`,permission:`view-pos`},{to:`/sales/orders`,icon:`pi pi-list`,label:`Orders`,badge:12,permission:`view-orders`},{to:`/tables`,icon:`pi pi-th-large`,label:`Tables`,permission:`view-pos`},{to:`/kitchen`,icon:`pi pi-clock`,label:`Kitchen`,permission:`view-orders`},{to:`/inventory/stocks`,icon:`pi pi-box`,label:`Inventory`,badge:5,permission:`view-stocks`},{to:`/master/customers-crm`,icon:`pi pi-users`,label:`Customers`,permission:`view-customers`},{to:`/settings/users`,icon:`pi pi-user`,label:`Staff`,permission:`manage-users`},{to:`/reports/recap`,icon:`pi pi-chart-bar`,label:`Reports`,permission:`view-reports`},{to:`/settings/tenant`,icon:`pi pi-cog`,label:`Settings`,permission:`view-business-profile`}],p=_(()=>f.filter(e=>!e.permission||d.value.includes(e.permission)));function v(){c.value=!c.value}function y(e){let t=i.path;return e.hash?t===e.to&&i.hash===e.hash:t===e.to||e.to!==`/dashboard`&&t.startsWith(e.to+`/`)}function x(e){if(r(`update:modelValue`,!1),e.hash)if(i.path!==e.to)a.push({path:e.to,hash:e.hash});else{let t=document.querySelector(e.hash);t&&t.scrollIntoView({behavior:`smooth`,block:`start`})}else a.push(e.to)}return(n,r)=>(o(),D(h,null,[e.modelValue?(o(),D(`div`,{key:0,class:`fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm lg:hidden transition-opacity duration-300`,onClick:r[0]||=e=>n.$emit(`update:modelValue`,!1)})):T(``,!0),g(`aside`,{class:S([`fixed inset-y-0 left-0 z-50 flex flex-col h-full bg-white border-r border-slate-100 shadow-sm transition-all duration-300 lg:static lg:z-auto`,c.value?`w-20`:`w-64`,e.modelValue?`translate-x-0`:`-translate-x-full lg:translate-x-0`])},[g(`div`,yt,[c.value?(o(),D(`div`,xt,[...r[2]||=[g(`svg`,{class:`w-5 h-5 text-white`,viewBox:`0 0 24 24`,fill:`none`,stroke:`currentColor`,"stroke-width":`2.5`,"stroke-linecap":`round`,"stroke-linejoin":`round`},[g(`path`,{d:`M12 2v20M17 5v14M7 5v14`})],-1)]])):(o(),D(`div`,bt,[...r[1]||=[E(`<div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/10"><svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5v14M7 5v14"></path></svg></div><div class="flex flex-col"><span class="text-base font-extrabold text-slate-800 tracking-tight leading-tight font-display"> FoodiePOS </span><span class="text-[9px] font-bold text-slate-400 tracking-wider uppercase mt-0.5">F&amp;B Management</span></div>`,2)]])),c.value?(o(),D(`button`,{key:3,onClick:v,class:`hidden lg:flex p-1.5 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors mx-auto shrink-0`},[...r[4]||=[g(`i`,{class:`pi pi-chevron-right text-xs`},null,-1)]])):(o(),D(`button`,{key:2,onClick:v,class:`hidden lg:flex p-1.5 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-colors shrink-0`},[...r[3]||=[g(`i`,{class:`pi pi-chevron-left text-xs`},null,-1)]]))]),g(`nav`,St,[(o(!0),D(h,null,t(p.value,e=>(o(),D(`div`,{key:e.label,class:`relative group`},[(o(),u(O(e.hash?`a`:`router-link`),{to:e.hash?void 0:e.to,href:e.hash?`${e.to}${e.hash}`:void 0,class:S([`flex items-center gap-3.5 rounded-2xl py-3 px-3.5 text-sm font-semibold transition-all duration-200 cursor-pointer`,c.value?`justify-center w-12 h-12 mx-auto px-0`:``,y(e)?`bg-emerald-500 text-white shadow-lg shadow-emerald-500/15`:`text-slate-500 hover:bg-slate-50 hover:text-slate-800`]),onClick:t=>x(e)},{default:l(()=>[g(`i`,{class:S([e.icon,`text-lg shrink-0`,y(e)?`scale-110`:``])},null,2),c.value?T(``,!0):(o(),D(`span`,Ct,b(e.label),1)),e.badge&&!c.value?(o(),D(`span`,wt,b(e.badge),1)):T(``,!0),c.value?(o(),D(`div`,Tt,b(e.label),1)):T(``,!0)]),_:2},1032,[`to`,`href`,`class`,`onClick`]))]))),128))]),c.value?T(``,!0):(o(),D(`div`,Et,[...r[5]||=[E(`<div class="space-y-3 pt-2"><h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest font-display">Today&#39;s Summary</h4><div class="space-y-1"><div class="flex justify-between items-center text-[10px] font-bold"><span class="text-slate-500">Sales Goal</span><span class="text-slate-800">$8,000 <span class="text-slate-400 font-semibold">(72%)</span></span></div><div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:72%;"></div></div></div><div class="space-y-1"><div class="flex justify-between items-center text-[10px] font-bold"><span class="text-slate-500">Orders Goal</span><span class="text-slate-800">120 <span class="text-slate-400 font-semibold">(68%)</span></span></div><div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:68%;"></div></div></div><div class="space-y-1"><div class="flex justify-between items-center text-[10px] font-bold"><span class="text-slate-500">Customers Goal</span><span class="text-slate-800">150 <span class="text-slate-400 font-semibold">(80%)</span></span></div><div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:80%;"></div></div></div></div><div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-4 text-white relative overflow-hidden shadow-md shadow-emerald-500/10 flex flex-col gap-3 group"><div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full bg-white/10 blur-xl"></div><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0"><i class="pi pi-sparkles text-sm text-white"></i></div><div class="flex flex-col"><span class="text-xs font-bold font-display">Upgrade to Premium</span><span class="text-[9px] text-emerald-100 font-semibold leading-tight mt-0.5">Unlock advanced features and analytics</span></div></div><button class="w-full bg-white text-emerald-600 hover:bg-emerald-50 text-[10px] font-bold py-2 rounded-xl transition-all shadow-sm shadow-emerald-700/5 cursor-pointer"> Upgrade Now </button></div>`,2)]]))],2)],64))}},Ot=P.extend({name:`menu`,style:`
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
`,classes:{root:function(e){return[`p-menu p-component`,{"p-menu-overlay":e.props.popup}]},start:`p-menu-start`,list:`p-menu-list`,submenuLabel:`p-menu-submenu-label`,separator:`p-menu-separator`,end:`p-menu-end`,item:function(e){var t=e.instance;return[`p-menu-item`,{"p-focus":t.id===t.focusedOptionId,"p-disabled":t.disabled()}]},itemContent:`p-menu-item-content`,itemLink:`p-menu-item-link`,itemIcon:`p-menu-item-icon`,itemLabel:`p-menu-item-label`}}),kt={name:`BaseMenu`,extends:j,props:{popup:{type:Boolean,default:!1},model:{type:Array,default:null},appendTo:{type:[String,Object],default:`body`},autoZIndex:{type:Boolean,default:!0},baseZIndex:{type:Number,default:0},tabindex:{type:Number,default:0},ariaLabel:{type:String,default:null},ariaLabelledby:{type:String,default:null}},style:Ot,provide:function(){return{$pcMenu:this,$parentInstance:this}}},At={name:`Menuitem`,hostName:`Menu`,extends:j,inheritAttrs:!1,emits:[`item-click`,`item-mousemove`],props:{item:null,templates:null,id:null,focusedOptionId:null,index:null},methods:{getItemProp:function(e,t){return e&&e.item?re(e.item[t]):void 0},getPTOptions:function(e){return this.ptm(e,{context:{item:this.item,index:this.index,focused:this.isItemFocused(),disabled:this.disabled()}})},isItemFocused:function(){return this.focusedOptionId===this.id},onItemClick:function(e){var t=this.getItemProp(this.item,`command`);t&&t({originalEvent:e,item:this.item.item}),this.$emit(`item-click`,{originalEvent:e,item:this.item,id:this.id})},onItemMouseMove:function(e){this.$emit(`item-mousemove`,{originalEvent:e,item:this.item,id:this.id})},visible:function(){return typeof this.item.visible==`function`?this.item.visible():this.item.visible!==!1},disabled:function(){return typeof this.item.disabled==`function`?this.item.disabled():this.item.disabled},label:function(){return typeof this.item.label==`function`?this.item.label():this.item.label},getMenuItemProps:function(e){return{action:y({class:this.cx(`itemLink`),tabindex:`-1`},this.getPTOptions(`itemLink`)),icon:y({class:[this.cx(`itemIcon`),e.icon]},this.getPTOptions(`itemIcon`)),label:y({class:this.cx(`itemLabel`)},this.getPTOptions(`itemLabel`))}}},computed:{dataP:function(){return M({focus:this.isItemFocused(),disabled:this.disabled()})}},directives:{ripple:A}},jt=[`id`,`aria-label`,`aria-disabled`,`data-p-focused`,`data-p-disabled`,`data-p`],Mt=[`data-p`],Nt=[`href`,`target`],Pt=[`data-p`],Ft=[`data-p`];function It(e,t,r,i,a,c){var l=s(`ripple`);return c.visible()?(o(),D(`li`,y({key:0,id:r.id,class:[e.cx(`item`),r.item.class],role:`menuitem`,style:r.item.style,"aria-label":c.label(),"aria-disabled":c.disabled(),"data-p-focused":c.isItemFocused(),"data-p-disabled":c.disabled()||!1,"data-p":c.dataP},c.getPTOptions(`item`)),[g(`div`,y({class:e.cx(`itemContent`),onClick:t[0]||=function(e){return c.onItemClick(e)},onMousemove:t[1]||=function(e){return c.onItemMouseMove(e)},"data-p":c.dataP},c.getPTOptions(`itemContent`)),[r.templates.item?r.templates.item?(o(),u(O(r.templates.item),{key:1,item:r.item,label:c.label(),props:c.getMenuItemProps(r.item)},null,8,[`item`,`label`,`props`])):T(``,!0):n((o(),D(`a`,y({key:0,href:r.item.url,class:e.cx(`itemLink`),target:r.item.target,tabindex:`-1`},c.getPTOptions(`itemLink`)),[r.templates.itemicon?(o(),u(O(r.templates.itemicon),{key:0,item:r.item,class:S(e.cx(`itemIcon`))},null,8,[`item`,`class`])):r.item.icon?(o(),D(`span`,y({key:1,class:[e.cx(`itemIcon`),r.item.icon],"data-p":c.dataP},c.getPTOptions(`itemIcon`)),null,16,Pt)):T(``,!0),g(`span`,y({class:e.cx(`itemLabel`),"data-p":c.dataP},c.getPTOptions(`itemLabel`)),b(c.label()),17,Ft)],16,Nt)),[[l]])],16,Mt)],16,jt)):T(``,!0)}At.render=It;function Lt(e){return Vt(e)||Bt(e)||zt(e)||Rt()}function Rt(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function zt(e,t){if(e){if(typeof e==`string`)return Q(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?Q(e,t):void 0}}function Bt(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Vt(e){if(Array.isArray(e))return Q(e)}function Q(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var $={name:`Menu`,extends:kt,inheritAttrs:!1,emits:[`show`,`hide`,`focus`,`blur`],data:function(){return{overlayVisible:!1,focused:!1,focusedOptionIndex:-1,selectedOptionIndex:-1}},target:null,outsideClickListener:null,scrollHandler:null,resizeListener:null,container:null,list:null,mounted:function(){this.popup||(this.bindResizeListener(),this.bindOutsideClickListener())},beforeUnmount:function(){this.unbindResizeListener(),this.unbindOutsideClickListener(),this.scrollHandler&&=(this.scrollHandler.destroy(),null),this.target=null,this.container&&this.autoZIndex&&L.clear(this.container),this.container=null},methods:{itemClick:function(e){var t=e.item;this.disabled(t)||(t.command&&t.command(e),this.overlayVisible&&this.hide(),!this.popup&&this.focusedOptionIndex!==e.id&&(this.focusedOptionIndex=e.id))},itemMouseMove:function(e){this.focused&&(this.focusedOptionIndex=e.id)},onListFocus:function(e){this.focused=!0,!this.popup&&this.changeFocusedOptionIndex(0),this.$emit(`focus`,e)},onListBlur:function(e){this.focused=!1,this.focusedOptionIndex=-1,this.$emit(`blur`,e)},onListKeyDown:function(e){switch(e.code){case`ArrowDown`:this.onArrowDownKey(e);break;case`ArrowUp`:this.onArrowUpKey(e);break;case`Home`:this.onHomeKey(e);break;case`End`:this.onEndKey(e);break;case`Enter`:case`NumpadEnter`:this.onEnterKey(e);break;case`Space`:this.onSpaceKey(e);break;case`Escape`:this.popup&&(R(this.target),this.hide());case`Tab`:this.overlayVisible&&this.hide();break}},onArrowDownKey:function(e){var t=this.findNextOptionIndex(this.focusedOptionIndex);this.changeFocusedOptionIndex(t),e.preventDefault()},onArrowUpKey:function(e){if(e.altKey&&this.popup)R(this.target),this.hide(),e.preventDefault();else{var t=this.findPrevOptionIndex(this.focusedOptionIndex);this.changeFocusedOptionIndex(t),e.preventDefault()}},onHomeKey:function(e){this.changeFocusedOptionIndex(0),e.preventDefault()},onEndKey:function(e){this.changeFocusedOptionIndex(N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`).length-1),e.preventDefault()},onEnterKey:function(e){var t=de(this.list,`li[id="${`${this.focusedOptionIndex}`}"]`),n=t&&de(t,`a[data-pc-section="itemlink"]`);this.popup&&R(this.target),n?n.click():t&&t.click(),e.preventDefault()},onSpaceKey:function(e){this.onEnterKey(e)},findNextOptionIndex:function(e){var t=Lt(N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`)).findIndex(function(t){return t.id===e});return t>-1?t+1:0},findPrevOptionIndex:function(e){var t=Lt(N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`)).findIndex(function(t){return t.id===e});return t>-1?t-1:0},changeFocusedOptionIndex:function(e){var t=N(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`),n=e>=t.length?t.length-1:e<0?0:e;n>-1&&(this.focusedOptionIndex=t[n].getAttribute(`id`))},toggle:function(e,t){this.overlayVisible?this.hide():this.show(e,t)},show:function(e,t){this.overlayVisible=!0,this.target=t??e.currentTarget},hide:function(){this.overlayVisible=!1,this.target=null},onEnter:function(e){ie(e,{position:`absolute`,top:`0`}),this.alignOverlay(),this.bindOutsideClickListener(),this.bindResizeListener(),this.bindScrollListener(),this.autoZIndex&&L.set(`menu`,e,this.baseZIndex||this.$primevue.config.zIndex.menu),this.popup&&R(this.list),this.$emit(`show`)},onLeave:function(){this.unbindOutsideClickListener(),this.unbindResizeListener(),this.unbindScrollListener(),this.$emit(`hide`)},onAfterLeave:function(e){this.autoZIndex&&L.clear(e)},alignOverlay:function(){ae(this.container,this.target),I(this.target)>I(this.container)&&(this.container.style.minWidth=I(this.target)+`px`)},bindOutsideClickListener:function(){var e=this;this.outsideClickListener||(this.outsideClickListener=function(t){var n=e.container&&!e.container.contains(t.target),r=!(e.target&&(e.target===t.target||e.target.contains(t.target)));e.overlayVisible&&n&&r?e.hide():!e.popup&&n&&r&&(e.focusedOptionIndex=-1)},document.addEventListener(`click`,this.outsideClickListener,!0))},unbindOutsideClickListener:function(){this.outsideClickListener&&=(document.removeEventListener(`click`,this.outsideClickListener,!0),null)},bindScrollListener:function(){var e=this;this.scrollHandler||=new le(this.target,function(){e.overlayVisible&&e.hide()}),this.scrollHandler.bindScrollListener()},unbindScrollListener:function(){this.scrollHandler&&this.scrollHandler.unbindScrollListener()},bindResizeListener:function(){var e=this;this.resizeListener||(this.resizeListener=function(){e.overlayVisible&&!ne()&&e.hide()},window.addEventListener(`resize`,this.resizeListener))},unbindResizeListener:function(){this.resizeListener&&=(window.removeEventListener(`resize`,this.resizeListener),null)},visible:function(e){return typeof e.visible==`function`?e.visible():e.visible!==!1},disabled:function(e){return typeof e.disabled==`function`?e.disabled():e.disabled},label:function(e){return typeof e.label==`function`?e.label():e.label},onOverlayClick:function(e){be.emit(`overlay-click`,{originalEvent:e,target:this.target})},containerRef:function(e){this.container=e},listRef:function(e){this.list=e}},computed:{focusedOptionId:function(){return this.focusedOptionIndex===-1?null:this.focusedOptionIndex},dataP:function(){return M({popup:this.popup})}},components:{PVMenuitem:At,Portal:he}},Ht=[`id`,`data-p`],Ut=[`id`,`tabindex`,`aria-activedescendant`,`aria-label`,`aria-labelledby`],Wt=[`id`];function Gt(n,a,s,d,f,p){var m=i(`PVMenuitem`),_=i(`Portal`);return o(),u(_,{appendTo:n.appendTo,disabled:!n.popup},{default:l(function(){return[e(x,y({name:`p-anchored-overlay`,onEnter:p.onEnter,onLeave:p.onLeave,onAfterLeave:p.onAfterLeave},n.ptm(`transition`)),{default:l(function(){return[!n.popup||f.overlayVisible?(o(),D(`div`,y({key:0,ref:p.containerRef,id:n.$id,class:n.cx(`root`),onClick:a[3]||=function(){return p.onOverlayClick&&p.onOverlayClick.apply(p,arguments)},"data-p":p.dataP},n.ptmi(`root`)),[n.$slots.start?(o(),D(`div`,y({key:0,class:n.cx(`start`)},n.ptm(`start`)),[r(n.$slots,`start`)],16)):T(``,!0),g(`ul`,y({ref:p.listRef,id:n.$id+`_list`,class:n.cx(`list`),role:`menu`,tabindex:n.tabindex,"aria-activedescendant":f.focused?p.focusedOptionId:void 0,"aria-label":n.ariaLabel,"aria-labelledby":n.ariaLabelledby,onFocus:a[0]||=function(){return p.onListFocus&&p.onListFocus.apply(p,arguments)},onBlur:a[1]||=function(){return p.onListBlur&&p.onListBlur.apply(p,arguments)},onKeydown:a[2]||=function(){return p.onListKeyDown&&p.onListKeyDown.apply(p,arguments)}},n.ptm(`list`)),[(o(!0),D(h,null,t(n.model,function(e,i){return o(),D(h,{key:p.label(e)+i.toString()},[e.items&&p.visible(e)&&!e.separator?(o(),D(h,{key:0},[e.items?(o(),D(`li`,y({key:0,id:n.$id+`_`+i,class:[n.cx(`submenuLabel`),e.class],role:`none`},{ref_for:!0},n.ptm(`submenuLabel`)),[r(n.$slots,n.$slots.submenulabel?`submenulabel`:`submenuheader`,{item:e},function(){return[c(b(p.label(e)),1)]})],16,Wt)):T(``,!0),(o(!0),D(h,null,t(e.items,function(t,r){return o(),D(h,{key:t.label+i+`_`+r},[p.visible(t)&&!t.separator?(o(),u(m,{key:0,id:n.$id+`_`+i+`_`+r,item:t,templates:n.$slots,focusedOptionId:p.focusedOptionId,unstyled:n.unstyled,onItemClick:p.itemClick,onItemMousemove:p.itemMouseMove,pt:n.pt},null,8,[`id`,`item`,`templates`,`focusedOptionId`,`unstyled`,`onItemClick`,`onItemMousemove`,`pt`])):p.visible(t)&&t.separator?(o(),D(`li`,y({key:`separator`+i+r,class:[n.cx(`separator`),e.class],style:t.style,role:`separator`},{ref_for:!0},n.ptm(`separator`)),null,16)):T(``,!0)],64)}),128))],64)):p.visible(e)&&e.separator?(o(),D(`li`,y({key:`separator`+i.toString(),class:[n.cx(`separator`),e.class],style:e.style,role:`separator`},{ref_for:!0},n.ptm(`separator`)),null,16)):(o(),u(m,{key:p.label(e)+i.toString(),id:n.$id+`_`+i,item:e,index:i,templates:n.$slots,focusedOptionId:p.focusedOptionId,unstyled:n.unstyled,onItemClick:p.itemClick,onItemMousemove:p.itemMouseMove,pt:n.pt},null,8,[`id`,`item`,`index`,`templates`,`focusedOptionId`,`unstyled`,`onItemClick`,`onItemMousemove`,`pt`]))],64)}),128))],16,Ut),n.$slots.end?(o(),D(`div`,y({key:1,class:n.cx(`end`)},n.ptm(`end`)),[r(n.$slots,`end`)],16)):T(``,!0)],16,Ht)):T(``,!0)]}),_:3},16,[`onEnter`,`onLeave`,`onAfterLeave`])]}),_:3},8,[`appendTo`,`disabled`])}$.render=Gt;var Kt={class:`flex min-h-[4.5rem] py-3 shrink-0 items-center justify-between px-4 md:px-6 bg-white rounded-2xl shadow-sm border border-slate-100/60 transition-all duration-300`},qt={class:`flex items-center gap-4 flex-1`},Jt={class:`lg:hidden`},Yt={class:`relative max-w-sm w-full hidden md:block group`},Xt={class:`flex items-center gap-3`},Zt={class:`relative`},Qt={key:0,class:`absolute right-0 mt-3 w-80 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden fade-in-up`},$t={class:`divide-y divide-slate-50 max-h-64 overflow-y-auto custom-scrollbar`},en=[`onClick`],tn={class:`flex-1 min-w-0`},nn={class:`text-xs font-semibold text-slate-700 leading-snug`},rn={class:`text-[10px] text-slate-400 mt-1 font-medium`},an={key:0,class:`py-8 text-center text-slate-400 text-xs`},on={class:`relative hidden sm:block`},sn={class:`relative hidden sm:block`},cn={class:`hidden lg:flex flex-col items-end text-right`},ln={class:`text-xs font-bold text-slate-800 font-mono tracking-tight`},un={class:`text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-0.5`},dn={__name:`AppHeader`,emits:[`toggle-sidebar`],setup(r){let i=ce(),s=oe();me();let{t:c}=fe(),l=m(``),u=m(!1),f=m(),y=m(),x=m(),C=m(``),w=null,O=m([{id:1,title:`Table 4 requested billing`,time:`2 mins ago`,icon:`pi pi-receipt`,color:`text-orange-500`,bg:`bg-orange-50`,read:!1},{id:2,title:`Low Stock: Espresso Beans (500g left)`,time:`12 mins ago`,icon:`pi pi-exclamation-triangle`,color:`text-red-500`,bg:`bg-red-50`,read:!1},{id:3,title:`Order #1024 ready for pickup at kitchen`,time:`25 mins ago`,icon:`pi pi-check-circle`,color:`text-emerald-500`,bg:`bg-emerald-50`,read:!0}]),k=_(()=>new Intl.DateTimeFormat(`en-US`,{month:`short`,day:`numeric`,year:`numeric`}).format(new Date)),A=_(()=>s.user?.attributes?.permissions||[]),j=[{label:`Downtown Branch`},{label:`Uptown Branch`},{label:`Suburban Branch`}],M=[{label:`Current Shift Info`},{label:`End Shift / Close Cashier`}],te=_(()=>[{label:c(`user_menu.profile`),icon:`pi pi-user`,command:()=>i.push(`/settings/profile`)},{label:c(`user_menu.users`),icon:`pi pi-users`,command:()=>i.push(`/settings/users`),visible:A.value.includes(`manage-users`)},{separator:!0},{label:c(`user_menu.logout`),icon:`pi pi-sign-out`,command:async()=>{await s.logout(),i.push(`/login`)}}]);function ne(){l.value&&i.push({path:`/master/products`,query:{search:l.value}})}function re(){u.value=!u.value}function ie(e){f.value?.toggle(e)}function ae(e){y.value?.toggle(e)}function N(e){x.value?.toggle(e)}function P(e){e.read=!0}function se(){O.value.forEach(e=>e.read=!0)}function F(){C.value=new Date().toLocaleTimeString(`en-US`,{hour:`2-digit`,minute:`2-digit`,hour12:!0})}function I(e){if((e.ctrlKey||e.metaKey)&&e.key===`k`){e.preventDefault();let t=document.querySelector(`input[placeholder*="Search orders"]`);t&&t.focus()}}return v(()=>{F(),w=setInterval(F,1e3),window.addEventListener(`keydown`,I)}),a(()=>{w&&clearInterval(w),window.removeEventListener(`keydown`,I)}),(r,i)=>(o(),D(`header`,Kt,[g(`div`,qt,[g(`div`,Jt,[g(`button`,{class:`p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors cursor-pointer`,onClick:i[0]||=e=>r.$emit(`toggle-sidebar`)},[...i[2]||=[g(`i`,{class:`pi pi-bars text-lg`},null,-1)]])]),g(`div`,Yt,[i[3]||=g(`span`,{class:`absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none`},[g(`i`,{class:`pi pi-search text-slate-400 group-focus-within:text-emerald-500 transition-colors text-sm`})],-1),n(g(`input`,{"onUpdate:modelValue":i[1]||=e=>l.value=e,type:`text`,placeholder:`Search orders, customers, items...`,class:`w-full bg-slate-50 border border-slate-50 text-slate-800 text-xs rounded-xl pl-10 pr-12 py-3 outline-none focus:bg-white focus:border-emerald-500/30 focus:ring-4 focus:ring-emerald-500/5 transition-all font-medium placeholder-slate-400`,onKeyup:ee(ne,[`enter`])},null,544),[[p,l.value]]),i[4]||=g(`span`,{class:`absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none`},[g(`kbd`,{class:`hidden sm:inline-block px-1.5 py-0.5 text-[9px] font-bold text-slate-400 bg-white border border-slate-200 rounded-md font-sans`},`⌘K`)],-1)]),i[5]||=g(`div`,{id:`app-header-title`,class:`flex-1 min-w-0 md:hidden`},null,-1)]),g(`div`,Xt,[i[12]||=g(`div`,{id:`app-header-actions`},null,-1),g(`div`,Zt,[g(`button`,{onClick:re,class:`p-2.5 rounded-xl hover:bg-slate-50 text-slate-500 hover:text-slate-700 transition-colors relative cursor-pointer`},[...i[6]||=[g(`i`,{class:`pi pi-bell text-lg`},null,-1),g(`span`,{class:`absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[8px] font-bold text-white shadow-sm ring-2 ring-white`},` 8 `,-1)]]),u.value?(o(),D(`div`,Qt,[g(`div`,{class:`flex items-center justify-between px-4 py-3.5 border-b border-slate-50/60 bg-slate-50/50`},[i[7]||=g(`h4`,{class:`text-xs font-bold text-slate-800`},`Notification Center`,-1),g(`button`,{onClick:se,class:`text-[10px] font-bold text-emerald-500 hover:text-emerald-600 cursor-pointer`},` Mark all read `)]),g(`div`,$t,[(o(!0),D(h,null,t(O.value,e=>(o(),D(`div`,{key:e.id,class:S([`p-3.5 flex gap-3 hover:bg-slate-50 transition-colors cursor-pointer`,e.read?``:`bg-emerald-500/[0.01]`]),onClick:t=>P(e)},[g(`div`,{class:S([`w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm`,e.bg])},[g(`i`,{class:S([e.icon,e.color])},null,2)],2),g(`div`,tn,[g(`p`,nn,b(e.title),1),g(`p`,rn,b(e.time),1)])],10,en))),128)),O.value.length===0?(o(),D(`div`,an,[...i[8]||=[g(`i`,{class:`pi pi-bell-slash text-2xl mb-2 text-slate-300`},null,-1),g(`p`,null,`No notifications yet`,-1)]])):T(``,!0)])])):T(``,!0)]),g(`div`,on,[g(`button`,{onClick:ae,class:`flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-100 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors cursor-pointer`},[...i[9]||=[g(`span`,{class:`w-1.5 h-1.5 rounded-full bg-emerald-500`},null,-1),g(`span`,null,`Shift Open`,-1),g(`i`,{class:`pi pi-angle-down text-[10px] text-slate-400`},null,-1)]]),e(d($),{ref_key:`shiftMenu`,ref:y,model:M,popup:``,class:`!rounded-xl !shadow-xl !border-slate-100/60`},null,512)]),g(`div`,sn,[g(`button`,{onClick:ie,class:`flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-100 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors cursor-pointer`},[...i[10]||=[g(`i`,{class:`pi pi-building text-slate-400`},null,-1),g(`span`,null,`Downtown Branch`,-1),g(`i`,{class:`pi pi-angle-down text-[10px] text-slate-400`},null,-1)]]),e(d($),{ref_key:`branchMenu`,ref:f,model:j,popup:``,class:`!rounded-xl !shadow-xl !border-slate-100/60`},null,512)]),i[13]||=g(`div`,{class:`h-8 w-px bg-slate-100 mx-1 hidden sm:block`},null,-1),g(`button`,{class:`flex items-center gap-2.5 hover:bg-slate-50 text-slate-400 p-1 rounded-xl transition-all cursor-pointer`,onClick:N},[...i[11]||=[E(`<img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&amp;fit=facearea&amp;facepad=2&amp;w=256&amp;h=256&amp;q=80" alt="User" class="w-9 h-9 rounded-full border border-slate-100 shadow-sm"><div class="text-left hidden md:block"><p class="text-xs font-bold text-slate-800 leading-tight"> John Smith </p><p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5 tracking-wider leading-tight"> Cashier </p></div><i class="pi pi-angle-down text-[10px] text-slate-400 ml-0.5"></i>`,3)]]),e(d($),{ref_key:`userMenu`,ref:x,model:te.value,popup:``,class:`!rounded-xl !shadow-xl !border-slate-100/60`},null,8,[`model`]),i[14]||=g(`div`,{class:`h-8 w-px bg-slate-100 mx-1 hidden lg:block`},null,-1),g(`div`,cn,[g(`span`,ln,b(C.value),1),g(`span`,un,b(k.value),1)])])]))}},fn={class:`flex h-screen bg-slate-50 font-sans text-slate-800 p-2 gap-2`},pn={class:`flex flex-1 flex-col gap-2 overflow-hidden min-w-0`},mn={class:`flex-1 overflow-y-auto`},hn=pe({__name:`AppLayout`,setup(t){let n=me(),r=m(!1);return v(()=>{n.fetchCurrentShift()}),(t,n)=>{let a=i(`router-view`);return o(),D(h,null,[g(`div`,fn,[r.value?(o(),D(`div`,{key:0,class:`fixed inset-0 z-40 bg-black/50 lg:hidden`,onClick:n[0]||=e=>r.value=!1})):T(``,!0),e(Dt,{modelValue:r.value,"onUpdate:modelValue":n[1]||=e=>r.value=e},null,8,[`modelValue`]),g(`div`,pn,[e(dn,{onToggleSidebar:n[2]||=e=>r.value=!r.value}),g(`main`,mn,[e(a)])])]),e(d(ct),{position:`top-right`}),e(d(_t))],64)}}},[[`__scopeId`,`data-v-81f26228`]]);export{hn as default};