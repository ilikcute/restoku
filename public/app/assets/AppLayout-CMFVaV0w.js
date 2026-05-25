import{A as e,D as t,F as n,H as r,I as i,L as a,N as o,P as s,Q as c,U as l,V as u,_ as d,at as f,b as p,g as m,h,i as g,it as _,j as v,k as y,m as b,p as x,r as S,rt as C,st as w,u as T,v as E,x as D}from"./axios-CS7ivfQ2.js";import{a as O,i as k,o as A,s as j,t as M}from"./button-DmM9dxuo.js";import{$ as ee,G as te,O as N,Ot as ne,Q as re,Z as P,b as F,c as ie,d as ae,dt as I,j as oe,kt as se,m as ce,nt as L,pt as le,r as ue,t as de,u as fe,v as R,x as z}from"./index-U9qSGcZK.js";import{t as B}from"./shift-DIKSeDLr.js";import{n as V,t as pe}from"./times-BsDPZpXr.js";import{t as me}from"./check-BYnFx8YE.js";import{t as he}from"./timescircle-CCgGL5uw.js";import{t as ge}from"./dialog-34_4DEsQ.js";import{t as _e}from"./tag-BCSKSsHY.js";import{t as ve}from"./overlayeventbus-BqNZ7eF_.js";var ye=`
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
`;function H(e){"@babel/helpers - typeof";return H=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},H(e)}function U(e,t,n){return(t=be(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function be(e){var t=xe(e,`string`);return H(t)==`symbol`?t:t+``}function xe(e,t){if(H(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(H(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var Se=z.extend({name:`toast`,style:ye,classes:{root:function(e){return[`p-toast p-component p-toast-`+e.props.position]},message:function(e){var t=e.props;return[`p-toast-message`,{"p-toast-message-info":t.message.severity===`info`||t.message.severity===void 0,"p-toast-message-warn":t.message.severity===`warn`,"p-toast-message-error":t.message.severity===`error`,"p-toast-message-success":t.message.severity===`success`,"p-toast-message-secondary":t.message.severity===`secondary`,"p-toast-message-contrast":t.message.severity===`contrast`}]},messageContent:`p-toast-message-content`,messageIcon:function(e){var t=e.props;return[`p-toast-message-icon`,U(U(U(U({},t.infoIcon,t.message.severity===`info`),t.warnIcon,t.message.severity===`warn`),t.errorIcon,t.message.severity===`error`),t.successIcon,t.message.severity===`success`)]},messageText:`p-toast-message-text`,summary:`p-toast-summary`,detail:`p-toast-detail`,closeButton:`p-toast-close-button`,closeIcon:`p-toast-close-icon`},inlineStyles:{root:function(e){var t=e.position;return{position:`fixed`,top:t===`top-right`||t===`top-left`||t===`top-center`?`20px`:t===`center`?`50%`:null,right:(t===`top-right`||t===`bottom-right`)&&`20px`,bottom:(t===`bottom-left`||t===`bottom-right`||t===`bottom-center`)&&`20px`,left:t===`top-left`||t===`bottom-left`?`20px`:t===`center`||t===`top-center`||t===`bottom-center`?`50%`:null}}}}),W={name:`ExclamationTriangleIcon`,extends:O};function Ce(e){return De(e)||Ee(e)||Te(e)||we()}function we(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Te(e,t){if(e){if(typeof e==`string`)return G(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?G(e,t):void 0}}function Ee(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function De(e){if(Array.isArray(e))return G(e)}function G(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function Oe(e,n,r,i,a,o){return v(),d(`svg`,t({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),Ce(n[0]||=[b(`path`,{d:`M13.4018 13.1893H0.598161C0.49329 13.189 0.390283 13.1615 0.299143 13.1097C0.208003 13.0578 0.131826 12.9832 0.0780112 12.8932C0.0268539 12.8015 0 12.6982 0 12.5931C0 12.4881 0.0268539 12.3848 0.0780112 12.293L6.47985 1.08982C6.53679 1.00399 6.61408 0.933574 6.70484 0.884867C6.7956 0.836159 6.897 0.810669 7 0.810669C7.103 0.810669 7.2044 0.836159 7.29516 0.884867C7.38592 0.933574 7.46321 1.00399 7.52015 1.08982L13.922 12.293C13.9731 12.3848 14 12.4881 14 12.5931C14 12.6982 13.9731 12.8015 13.922 12.8932C13.8682 12.9832 13.792 13.0578 13.7009 13.1097C13.6097 13.1615 13.5067 13.189 13.4018 13.1893ZM1.63046 11.989H12.3695L7 2.59425L1.63046 11.989Z`,fill:`currentColor`},null,-1),b(`path`,{d:`M6.99996 8.78801C6.84143 8.78594 6.68997 8.72204 6.57787 8.60993C6.46576 8.49782 6.40186 8.34637 6.39979 8.18784V5.38703C6.39979 5.22786 6.46302 5.0752 6.57557 4.96265C6.68813 4.85009 6.84078 4.78686 6.99996 4.78686C7.15914 4.78686 7.31179 4.85009 7.42435 4.96265C7.5369 5.0752 7.60013 5.22786 7.60013 5.38703V8.18784C7.59806 8.34637 7.53416 8.49782 7.42205 8.60993C7.30995 8.72204 7.15849 8.78594 6.99996 8.78801Z`,fill:`currentColor`},null,-1),b(`path`,{d:`M6.99996 11.1887C6.84143 11.1866 6.68997 11.1227 6.57787 11.0106C6.46576 10.8985 6.40186 10.7471 6.39979 10.5885V10.1884C6.39979 10.0292 6.46302 9.87658 6.57557 9.76403C6.68813 9.65147 6.84078 9.58824 6.99996 9.58824C7.15914 9.58824 7.31179 9.65147 7.42435 9.76403C7.5369 9.87658 7.60013 10.0292 7.60013 10.1884V10.5885C7.59806 10.7471 7.53416 10.8985 7.42205 11.0106C7.30995 11.1227 7.15849 11.1866 6.99996 11.1887Z`,fill:`currentColor`},null,-1)]),16)}W.render=Oe;var K={name:`InfoCircleIcon`,extends:O};function ke(e){return Ne(e)||Me(e)||je(e)||Ae()}function Ae(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function je(e,t){if(e){if(typeof e==`string`)return q(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?q(e,t):void 0}}function Me(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function Ne(e){if(Array.isArray(e))return q(e)}function q(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function Pe(e,n,r,i,a,o){return v(),d(`svg`,t({width:`14`,height:`14`,viewBox:`0 0 14 14`,fill:`none`,xmlns:`http://www.w3.org/2000/svg`},e.pti()),ke(n[0]||=[b(`path`,{"fill-rule":`evenodd`,"clip-rule":`evenodd`,d:`M3.11101 12.8203C4.26215 13.5895 5.61553 14 7 14C8.85652 14 10.637 13.2625 11.9497 11.9497C13.2625 10.637 14 8.85652 14 7C14 5.61553 13.5895 4.26215 12.8203 3.11101C12.0511 1.95987 10.9579 1.06266 9.67879 0.532846C8.3997 0.00303296 6.99224 -0.13559 5.63437 0.134506C4.2765 0.404603 3.02922 1.07129 2.05026 2.05026C1.07129 3.02922 0.404603 4.2765 0.134506 5.63437C-0.13559 6.99224 0.00303296 8.3997 0.532846 9.67879C1.06266 10.9579 1.95987 12.0511 3.11101 12.8203ZM3.75918 2.14976C4.71846 1.50879 5.84628 1.16667 7 1.16667C8.5471 1.16667 10.0308 1.78125 11.1248 2.87521C12.2188 3.96918 12.8333 5.45291 12.8333 7C12.8333 8.15373 12.4912 9.28154 11.8502 10.2408C11.2093 11.2001 10.2982 11.9478 9.23232 12.3893C8.16642 12.8308 6.99353 12.9463 5.86198 12.7212C4.73042 12.4962 3.69102 11.9406 2.87521 11.1248C2.05941 10.309 1.50384 9.26958 1.27876 8.13803C1.05367 7.00647 1.16919 5.83358 1.61071 4.76768C2.05222 3.70178 2.79989 2.79074 3.75918 2.14976ZM7.00002 4.8611C6.84594 4.85908 6.69873 4.79698 6.58977 4.68801C6.48081 4.57905 6.4187 4.43185 6.41669 4.27776V3.88888C6.41669 3.73417 6.47815 3.58579 6.58754 3.4764C6.69694 3.367 6.84531 3.30554 7.00002 3.30554C7.15473 3.30554 7.3031 3.367 7.4125 3.4764C7.52189 3.58579 7.58335 3.73417 7.58335 3.88888V4.27776C7.58134 4.43185 7.51923 4.57905 7.41027 4.68801C7.30131 4.79698 7.1541 4.85908 7.00002 4.8611ZM7.00002 10.6945C6.84594 10.6925 6.69873 10.6304 6.58977 10.5214C6.48081 10.4124 6.4187 10.2652 6.41669 10.1111V6.22225C6.41669 6.06754 6.47815 5.91917 6.58754 5.80977C6.69694 5.70037 6.84531 5.63892 7.00002 5.63892C7.15473 5.63892 7.3031 5.70037 7.4125 5.80977C7.52189 5.91917 7.58335 6.06754 7.58335 6.22225V10.1111C7.58134 10.2652 7.51923 10.4124 7.41027 10.5214C7.30131 10.6304 7.1541 10.6925 7.00002 10.6945Z`,fill:`currentColor`},null,-1)]),16)}K.render=Pe;var Fe={name:`BaseToast`,extends:A,props:{group:{type:String,default:null},position:{type:String,default:`top-right`},autoZIndex:{type:Boolean,default:!0},baseZIndex:{type:Number,default:0},breakpoints:{type:Object,default:null},closeIcon:{type:String,default:void 0},infoIcon:{type:String,default:void 0},warnIcon:{type:String,default:void 0},errorIcon:{type:String,default:void 0},successIcon:{type:String,default:void 0},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},style:Se,provide:function(){return{$pcToast:this,$parentInstance:this}}};function J(e){"@babel/helpers - typeof";return J=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},J(e)}function Ie(e,t,n){return(t=Le(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Le(e){var t=Re(e,`string`);return J(t)==`symbol`?t:t+``}function Re(e,t){if(J(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(J(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var ze={name:`ToastMessage`,hostName:`Toast`,extends:A,emits:[`close`],closeTimeout:null,createdAt:null,lifeRemaining:null,props:{message:{type:null,default:null},templates:{type:Object,default:null},closeIcon:{type:String,default:null},infoIcon:{type:String,default:null},warnIcon:{type:String,default:null},errorIcon:{type:String,default:null},successIcon:{type:String,default:null},closeButtonProps:{type:null,default:null},onMouseEnter:{type:Function,default:void 0},onMouseLeave:{type:Function,default:void 0},onClick:{type:Function,default:void 0}},mounted:function(){this.message.life&&(this.lifeRemaining=this.message.life,this.startTimeout())},beforeUnmount:function(){this.clearCloseTimeout()},methods:{startTimeout:function(){var e=this;this.createdAt=new Date().valueOf(),this.closeTimeout=setTimeout(function(){e.close({message:e.message,type:`life-end`})},this.lifeRemaining)},close:function(e){this.$emit(`close`,e)},onCloseClick:function(){this.clearCloseTimeout(),this.close({message:this.message,type:`close`})},clearCloseTimeout:function(){this.closeTimeout&&=(clearTimeout(this.closeTimeout),null)},onMessageClick:function(e){var t;(t=this.onClick)==null||t.call(this,{originalEvent:e,message:this.message})},handleMouseEnter:function(e){if(this.onMouseEnter){if(this.onMouseEnter({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&(this.lifeRemaining=this.createdAt+this.lifeRemaining-new Date().valueOf(),this.createdAt=null,this.clearCloseTimeout())}},handleMouseLeave:function(e){if(this.onMouseLeave){if(this.onMouseLeave({originalEvent:e,message:this.message}),e.defaultPrevented)return;this.message.life&&this.startTimeout()}}},computed:{iconComponent:function(){return{info:!this.infoIcon&&K,success:!this.successIcon&&me,warn:!this.warnIcon&&W,error:!this.errorIcon&&he}[this.message.severity]},closeAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.close:void 0},dataP:function(){return j(Ie({},this.message.severity,this.message.severity))}},components:{TimesIcon:pe,InfoCircleIcon:K,CheckIcon:me,ExclamationTriangleIcon:W,TimesCircleIcon:he},directives:{ripple:k}};function Y(e){"@babel/helpers - typeof";return Y=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},Y(e)}function Be(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function Ve(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?Be(Object(n),!0).forEach(function(t){He(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):Be(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function He(e,t,n){return(t=Ue(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Ue(e){var t=We(e,`string`);return Y(t)==`symbol`?t:t+``}function We(e,t){if(Y(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(Y(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var Ge=[`data-p`],Ke=[`data-p`],qe=[`data-p`],Je=[`data-p`],Ye=[`aria-label`,`data-p`];function Xe(e,n,r,o,s,c){var u=i(`ripple`);return v(),d(`div`,t({class:[e.cx(`message`),r.message.styleClass],role:`alert`,"aria-live":`assertive`,"aria-atomic":`true`,"data-p":c.dataP},e.ptm(`message`),{onClick:n[1]||=function(){return c.onMessageClick&&c.onMessageClick.apply(c,arguments)},onMouseenter:n[2]||=function(){return c.handleMouseEnter&&c.handleMouseEnter.apply(c,arguments)},onMouseleave:n[3]||=function(){return c.handleMouseLeave&&c.handleMouseLeave.apply(c,arguments)}}),[r.templates.container?(v(),h(a(r.templates.container),{key:0,message:r.message,closeCallback:c.onCloseClick},null,8,[`message`,`closeCallback`])):(v(),d(`div`,t({key:1,class:[e.cx(`messageContent`),r.message.contentStyleClass]},e.ptm(`messageContent`)),[r.templates.message?(v(),h(a(r.templates.message),{key:1,message:r.message},null,8,[`message`])):(v(),d(T,{key:0},[(v(),h(a(r.templates.messageicon?r.templates.messageicon:r.templates.icon?r.templates.icon:c.iconComponent&&c.iconComponent.name?c.iconComponent:`span`),t({class:e.cx(`messageIcon`)},e.ptm(`messageIcon`)),null,16,[`class`])),b(`div`,t({class:e.cx(`messageText`),"data-p":c.dataP},e.ptm(`messageText`)),[b(`span`,t({class:e.cx(`summary`),"data-p":c.dataP},e.ptm(`summary`)),w(r.message.summary),17,qe),r.message.detail?(v(),d(`div`,t({key:0,class:e.cx(`detail`),"data-p":c.dataP},e.ptm(`detail`)),w(r.message.detail),17,Je)):m(``,!0)],16,Ke)],64)),r.message.closable===!1?m(``,!0):(v(),d(`div`,f(t({key:2},e.ptm(`buttonContainer`))),[l((v(),d(`button`,t({class:e.cx(`closeButton`),type:`button`,"aria-label":c.closeAriaLabel,onClick:n[0]||=function(){return c.onCloseClick&&c.onCloseClick.apply(c,arguments)},autofocus:``,"data-p":c.dataP},Ve(Ve({},r.closeButtonProps),e.ptm(`closeButton`))),[(v(),h(a(r.templates.closeicon||`TimesIcon`),t({class:[e.cx(`closeIcon`),r.closeIcon]},e.ptm(`closeIcon`)),null,16,[`class`]))],16,Ye)),[[u]])],16))],16))],16,Ge)}ze.render=Xe;function X(e){"@babel/helpers - typeof";return X=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},X(e)}function Ze(e,t,n){return(t=Qe(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function Qe(e){var t=$e(e,`string`);return X(t)==`symbol`?t:t+``}function $e(e,t){if(X(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(X(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}function et(e){return it(e)||rt(e)||nt(e)||tt()}function tt(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function nt(e,t){if(e){if(typeof e==`string`)return Z(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?Z(e,t):void 0}}function rt(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function it(e){if(Array.isArray(e))return Z(e)}function Z(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var at=0,ot={name:`Toast`,extends:Fe,inheritAttrs:!1,emits:[`close`,`life-end`],data:function(){return{messages:[]}},styleElement:null,mounted:function(){F.on(`add`,this.onAdd),F.on(`remove`,this.onRemove),F.on(`remove-group`,this.onRemoveGroup),F.on(`remove-all-groups`,this.onRemoveAllGroups),this.breakpoints&&this.createStyle()},beforeUnmount:function(){this.destroyStyle(),this.$refs.container&&this.autoZIndex&&N.clear(this.$refs.container),F.off(`add`,this.onAdd),F.off(`remove`,this.onRemove),F.off(`remove-group`,this.onRemoveGroup),F.off(`remove-all-groups`,this.onRemoveAllGroups)},methods:{add:function(e){e.id??=at++,this.messages=[].concat(et(this.messages),[e])},remove:function(e){var t=this.messages.findIndex(function(t){return t.id===e.message.id});t!==-1&&(this.messages.splice(t,1),this.$emit(e.type,{message:e.message}))},onAdd:function(e){this.group==e.group&&this.add(e)},onRemove:function(e){this.remove({message:e,type:`close`})},onRemoveGroup:function(e){this.group===e&&(this.messages=[])},onRemoveAllGroups:function(){var e=this;this.messages.forEach(function(t){return e.$emit(`close`,{message:t})}),this.messages=[]},onEnter:function(){this.autoZIndex&&N.set(`modal`,this.$refs.container,this.baseZIndex||this.$primevue.config.zIndex.modal)},onLeave:function(){var e=this;this.$refs.container&&this.autoZIndex&&ne(this.messages)&&setTimeout(function(){N.clear(e.$refs.container)},200)},createStyle:function(){if(!this.styleElement&&!this.isUnstyled){var e;this.styleElement=document.createElement(`style`),this.styleElement.type=`text/css`,ee(this.styleElement,`nonce`,(e=this.$primevue)==null||(e=e.config)==null||(e=e.csp)==null?void 0:e.nonce),document.head.appendChild(this.styleElement);var t=``;for(var n in this.breakpoints){var r=``;for(var i in this.breakpoints[n])r+=i+`:`+this.breakpoints[n][i]+`!important;`;t+=`
                        @media screen and (max-width: ${n}) {
                            .p-toast[${this.$attrSelector}] {
                                ${r}
                            }
                        }
                    `}this.styleElement.innerHTML=t}},destroyStyle:function(){this.styleElement&&=(document.head.removeChild(this.styleElement),null)}},computed:{dataP:function(){return j(Ze({},this.position,this.position))}},components:{ToastMessage:ze,Portal:V}};function Q(e){"@babel/helpers - typeof";return Q=typeof Symbol==`function`&&typeof Symbol.iterator==`symbol`?function(e){return typeof e}:function(e){return e&&typeof Symbol==`function`&&e.constructor===Symbol&&e!==Symbol.prototype?`symbol`:typeof e},Q(e)}function st(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function ct(e){for(var t=1;t<arguments.length;t++){var n=arguments[t]==null?{}:arguments[t];t%2?st(Object(n),!0).forEach(function(t){lt(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):st(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function lt(e,t,n){return(t=ut(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function ut(e){var t=dt(e,`string`);return Q(t)==`symbol`?t:t+``}function dt(e,t){if(Q(e)!=`object`||!e)return e;var n=e[Symbol.toPrimitive];if(n!==void 0){var r=n.call(e,t);if(Q(r)!=`object`)return r;throw TypeError(`@@toPrimitive must return a primitive value.`)}return(t===`string`?String:Number)(e)}var ft=[`data-p`];function pt(e,i,a,s,c,l){var u=n(`ToastMessage`),f=n(`Portal`);return v(),h(f,null,{default:r(function(){return[b(`div`,t({ref:`container`,class:e.cx(`root`),style:e.sx(`root`,!0,{position:e.position}),"data-p":l.dataP},e.ptmi(`root`)),[D(g,t({name:`p-toast-message`,tag:`div`,onEnter:l.onEnter,onLeave:l.onLeave},ct({},e.ptm(`transition`))),{default:r(function(){return[(v(!0),d(T,null,o(c.messages,function(t){return v(),h(u,{key:t.id,message:t,templates:e.$slots,closeIcon:e.closeIcon,infoIcon:e.infoIcon,warnIcon:e.warnIcon,errorIcon:e.errorIcon,successIcon:e.successIcon,closeButtonProps:e.closeButtonProps,onMouseEnter:e.onMouseEnter,onMouseLeave:e.onMouseLeave,onClick:e.onClick,unstyled:e.unstyled,onClose:i[0]||=function(e){return l.remove(e)},pt:e.pt},null,8,[`message`,`templates`,`closeIcon`,`infoIcon`,`warnIcon`,`errorIcon`,`successIcon`,`closeButtonProps`,`onMouseEnter`,`onMouseLeave`,`onClick`,`unstyled`,`pt`])}),128))]}),_:1},16,[`onEnter`,`onLeave`])],16,ft)]}),_:1})}ot.render=pt;var mt=z.extend({name:`confirmdialog`,style:`
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
`,classes:{root:`p-confirmdialog`,icon:`p-confirmdialog-icon`,message:`p-confirmdialog-message`,pcRejectButton:`p-confirmdialog-reject-button`,pcAcceptButton:`p-confirmdialog-accept-button`}}),ht={name:`ConfirmDialog`,extends:{name:`BaseConfirmDialog`,extends:A,props:{group:String,breakpoints:{type:Object,default:null},draggable:{type:Boolean,default:!0}},style:mt,provide:function(){return{$pcConfirmDialog:this,$parentInstance:this}}},confirmListener:null,closeListener:null,data:function(){return{visible:!1,confirmation:null}},mounted:function(){var e=this;this.confirmListener=function(t){t&&t.group===e.group&&(e.confirmation=t,e.confirmation.onShow&&e.confirmation.onShow(),e.visible=!0)},this.closeListener=function(){e.visible=!1,e.confirmation=null},R.on(`confirm`,this.confirmListener),R.on(`close`,this.closeListener)},beforeUnmount:function(){R.off(`confirm`,this.confirmListener),R.off(`close`,this.closeListener)},methods:{accept:function(){this.confirmation.accept&&this.confirmation.accept(),this.visible=!1},reject:function(){this.confirmation.reject&&this.confirmation.reject(),this.visible=!1},onHide:function(){this.confirmation.onHide&&this.confirmation.onHide(),this.visible=!1}},computed:{appendTo:function(){return this.confirmation?this.confirmation.appendTo:`body`},target:function(){return this.confirmation?this.confirmation.target:null},modal:function(){return this.confirmation?this.confirmation.modal==null?!0:this.confirmation.modal:!0},header:function(){return this.confirmation?this.confirmation.header:null},message:function(){return this.confirmation?this.confirmation.message:null},blockScroll:function(){return this.confirmation?this.confirmation.blockScroll:!0},position:function(){return this.confirmation?this.confirmation.position:null},acceptLabel:function(){if(this.confirmation){var e=this.confirmation;return e.acceptLabel||e.acceptProps?.label||this.$primevue.config.locale.accept}return this.$primevue.config.locale.accept},rejectLabel:function(){if(this.confirmation){var e=this.confirmation;return e.rejectLabel||e.rejectProps?.label||this.$primevue.config.locale.reject}return this.$primevue.config.locale.reject},acceptIcon:function(){var e;return this.confirmation?this.confirmation.acceptIcon:(e=this.confirmation)!=null&&e.acceptProps?this.confirmation.acceptProps.icon:null},rejectIcon:function(){var e;return this.confirmation?this.confirmation.rejectIcon:(e=this.confirmation)!=null&&e.rejectProps?this.confirmation.rejectProps.icon:null},autoFocusAccept:function(){return this.confirmation.defaultFocus===void 0||this.confirmation.defaultFocus===`accept`},autoFocusReject:function(){return this.confirmation.defaultFocus===`reject`},closeOnEscape:function(){return this.confirmation?this.confirmation.closeOnEscape:!0}},components:{Dialog:ge,Button:M}};function gt(e,i,o,c,l,u){var f=n(`Button`),p=n(`Dialog`);return v(),h(p,{visible:l.visible,"onUpdate:visible":[i[2]||=function(e){return l.visible=e},u.onHide],role:`alertdialog`,class:_(e.cx(`root`)),modal:u.modal,header:u.header,blockScroll:u.blockScroll,appendTo:u.appendTo,position:u.position,breakpoints:e.breakpoints,closeOnEscape:u.closeOnEscape,draggable:e.draggable,pt:e.pt,unstyled:e.unstyled},E({default:r(function(){return[e.$slots.container?m(``,!0):(v(),d(T,{key:0},[e.$slots.message?(v(),h(a(e.$slots.message),{key:1,message:l.confirmation},null,8,[`message`])):(v(),d(T,{key:0},[s(e.$slots,`icon`,{},function(){return[e.$slots.icon?(v(),h(a(e.$slots.icon),{key:0,class:_(e.cx(`icon`))},null,8,[`class`])):l.confirmation.icon?(v(),d(`span`,t({key:1,class:[l.confirmation.icon,e.cx(`icon`)]},e.ptm(`icon`)),null,16)):m(``,!0)]}),b(`span`,t({class:e.cx(`message`)},e.ptm(`message`)),w(u.message),17)],64))],64))]}),_:2},[e.$slots.container?{name:`container`,fn:r(function(t){return[s(e.$slots,`container`,{message:l.confirmation,closeCallback:t.closeCallback,acceptCallback:u.accept,rejectCallback:u.reject,initDragCallback:t.initDragCallback})]}),key:`0`}:void 0,e.$slots.container?void 0:{name:`footer`,fn:r(function(){return[D(f,t({class:[e.cx(`pcRejectButton`),l.confirmation.rejectClass],autofocus:u.autoFocusReject,unstyled:e.unstyled,text:l.confirmation.rejectProps?.text||!1,onClick:i[0]||=function(e){return u.reject()}},l.confirmation.rejectProps,{label:u.rejectLabel,pt:e.ptm(`pcRejectButton`)}),E({_:2},[u.rejectIcon||e.$slots.rejecticon?{name:`icon`,fn:r(function(n){return[s(e.$slots,`rejecticon`,{},function(){return[b(`span`,t({class:[u.rejectIcon,n.class]},e.ptm(`pcRejectButton`).icon,{"data-pc-section":`rejectbuttonicon`}),null,16)]})]}),key:`0`}:void 0]),1040,[`class`,`autofocus`,`unstyled`,`text`,`label`,`pt`]),D(f,t({label:u.acceptLabel,class:[e.cx(`pcAcceptButton`),l.confirmation.acceptClass],autofocus:u.autoFocusAccept,unstyled:e.unstyled,onClick:i[1]||=function(e){return u.accept()}},l.confirmation.acceptProps,{pt:e.ptm(`pcAcceptButton`)}),E({_:2},[u.acceptIcon||e.$slots.accepticon?{name:`icon`,fn:r(function(n){return[s(e.$slots,`accepticon`,{},function(){return[b(`span`,t({class:[u.acceptIcon,n.class]},e.ptm(`pcAcceptButton`).icon,{"data-pc-section":`acceptbuttonicon`}),null,16)]})]}),key:`0`}:void 0]),1040,[`label`,`class`,`autofocus`,`unstyled`,`pt`])]}),key:`1`}]),1032,[`visible`,`class`,`modal`,`header`,`blockScroll`,`appendTo`,`position`,`breakpoints`,`closeOnEscape`,`draggable`,`onUpdate:visible`,`pt`,`unstyled`])}ht.render=gt;var _t={class:`flex h-16 shrink-0 items-center justify-between px-4 border-b border-slate-100`},vt={key:0,class:`flex items-center gap-3 overflow-hidden`},yt={class:`w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center shrink-0 shadow-sm`},bt=[`src`],xt={key:1,class:`text-white font-black text-sm leading-none`},St={class:`text-base font-extrabold text-slate-800 tracking-tight leading-tight truncate`},Ct={key:1,class:`w-9 h-9 rounded-xl bg-orange-500 flex items-center justify-center mx-auto shadow-sm`},wt={class:`text-white font-black text-sm leading-none`},Tt={class:`flex-1 overflow-y-auto py-4 px-3 space-y-0.5`},Et={key:0,class:`px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400`},Dt={key:1,class:`pt-3 pb-1 flex justify-center`},Ot={key:0,class:`whitespace-nowrap`},kt={key:1,class:`absolute left-full ml-3 px-2.5 py-1.5 bg-slate-800 text-white text-xs font-semibold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 pointer-events-none`},At=[`onClick`],jt={class:`flex items-center gap-3`},Mt={key:0,class:`whitespace-nowrap`},Nt={key:0,class:`whitespace-nowrap`},Pt={key:1,class:`absolute left-full ml-3 px-2.5 py-1.5 bg-slate-800 text-white text-xs font-semibold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 pointer-events-none`},Ft={class:`shrink-0 border-t border-slate-100 p-3`},It={key:0,class:`flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer`},Lt=[`src`],Rt={class:`flex-1 overflow-hidden`},zt={class:`text-sm font-semibold text-slate-800 truncate leading-tight`},Bt={class:`text-[11px] text-slate-400 capitalize leading-tight`},Vt={key:1,class:`flex justify-center`},Ht=[`src`],Ut=de({__name:`AppSidebar`,props:{modelValue:{type:Boolean,default:!1}},emits:[`update:modelValue`],setup(e,{emit:t}){let i=fe(),s=ie(),l=x(()=>s.user?.attributes?.permissions||[]),f=x(()=>s.user?.relationships?.tenant?.data?.attributes||{}),p=c(!1),g=c(new Set),S=()=>{p.value=!p.value,p.value?g.value.clear():M()},E=e=>{p.value&&=!1,g.value.has(e)?g.value.delete(e):g.value.add(e)},D=e=>g.value.has(e),O={"common.master_data":`Master`,"common.inventory":`Inventory`,"common.sales":`Sales`,"common.purchasing":`Purchasing`,"common.finance":`Finance`,"common.reports":`Reports`,"common.settings":`Settings`},k=e=>O[e]||e,A=[{label:`common.dashboard`,icon:`pi pi-layout`,permission:`view-dashboard`,items:[{to:`/dashboard`,icon:`pi pi-layout`,label:`common.overview`}]},{label:`common.master_data`,icon:`pi pi-database`,permission:`view-master-data`,items:[{to:`/master/products`,icon:`pi pi-box`,label:`sidebar.products`,permission:`view-products`},{to:`/master/categories`,icon:`pi pi-tags`,label:`sidebar.categories`,permission:`view-categories`},{to:`/master/units`,icon:`pi pi-sliders-h`,label:`sidebar.units`,permission:`view-units`},{to:`/master/suppliers`,icon:`pi pi-truck`,label:`sidebar.suppliers`,permission:`view-suppliers`},{to:`/master/customers`,icon:`pi pi-user`,label:`sidebar.customers`,permission:`view-customers`}]},{label:`common.inventory`,icon:`pi pi-warehouse`,permission:`view-inventory`,items:[{to:`/inventory/stocks`,icon:`pi pi-warehouse`,label:`sidebar.stock_levels`,permission:`view-stocks`},{to:`/inventory/movements`,icon:`pi pi-sync`,label:`sidebar.movements`,permission:`view-stock-movements`},{to:`/inventory/adjustments`,icon:`pi pi-pencil`,label:`sidebar.adjustments`,permission:`view-stock-adjustments`}]},{icon:`pi pi-shopping-cart`,label:`common.sales`,permission:`view-sales`,items:[{to:`/sales/shifts`,icon:`pi pi-clock`,label:`sidebar.shift_manager`,permission:`view-shifts`},{to:`/sales/pos`,icon:`pi pi-calculator`,label:`sidebar.pos`,permission:`view-pos`},{to:`/sales/orders`,icon:`pi pi-list`,label:`sidebar.orders`,permission:`view-orders`},{to:`/sales/returns`,icon:`pi pi-plus-circle`,label:`sidebar.create_return`,permission:`view-sales-returns`},{to:`/reports/sales-returns`,icon:`pi pi-history`,label:`sidebar.return_history`,permission:`view-report-returns`},{to:`/menu`,icon:`pi pi-qrcode`,label:`sidebar.digital_menu`}]},{icon:`pi pi-shopping-bag`,label:`common.purchasing`,permission:`view-purchasing`,items:[{to:`/purchasing/purchases`,icon:`pi pi-list`,label:`sidebar.purchases`,permission:`view-purchases`},{to:`/purchasing/returns`,icon:`pi pi-plus-circle`,label:`sidebar.create_return`,permission:`view-purchase-returns`},{to:`/reports/purchase-returns`,icon:`pi pi-history`,label:`sidebar.return_history`,permission:`view-report-returns`},{to:`/purchasing/procurement`,icon:`pi pi-search-plus`,label:`sidebar.procurement`,permission:`view-procurement`},{to:`/inventory/alerts`,icon:`pi pi-bell`,label:`sidebar.alerts`,permission:`view-inventory-alerts`}]},{label:`common.finance`,icon:`pi pi-wallet`,permission:`view-finance`,items:[{to:`/finance/accounts`,icon:`pi pi-wallet`,label:`sidebar.accounts`,permission:`view-accounts`},{to:`/finance/transactions`,icon:`pi pi-money-bill`,label:`sidebar.transactions`,permission:`view-transactions`},{to:`/finance/closings`,icon:`pi pi-check-circle`,label:`sidebar.daily_closings`,permission:`view-closings`}]},{label:`common.reports`,icon:`pi pi-chart-bar`,permission:`view-reports`,items:[{to:`/reports/recap`,icon:`pi pi-chart-bar`,label:`sidebar.recap`,permission:`view-reports`},{to:`/reports/sales`,icon:`pi pi-list`,label:`sidebar.sales_report`,permission:`view-report-sales`},{to:`/reports/purchases`,icon:`pi pi-shopping-bag`,label:`sidebar.purchase_report`,permission:`view-report-purchases`},{to:`/reports/sales-returns`,icon:`pi pi-replay`,label:`sidebar.sales_return_report`,permission:`view-report-returns`},{to:`/reports/purchase-returns`,icon:`pi pi-replay`,label:`sidebar.purchase_return_report`,permission:`view-report-returns`},{to:`/reports/tax`,icon:`pi pi-percentage`,label:`sidebar.tax_report`,permission:`view-report-tax`},{to:`/reports/audit-logs`,icon:`pi pi-history`,label:`sidebar.audit_trail`,permission:`view-reports`}]},{label:`common.settings`,icon:`pi pi-cog`,items:[{to:`/settings/profile`,icon:`pi pi-user-edit`,label:`sidebar.profile`,permission:`view-profile`},{to:`/settings/tenant`,icon:`pi pi-building`,label:`common.business_profile`,permission:`view-business-profile`},{to:`/settings/printer`,icon:`pi pi-print`,label:`sidebar.printer_settings`,permission:`view-business-profile`},{to:`/settings/users`,icon:`pi pi-users`,label:`sidebar.users`,permission:`manage-users`},{to:`/settings/roles`,icon:`pi pi-lock`,label:`settings.roles`,permission:`manage-roles`},{to:`/settings/promotions`,icon:`pi pi-megaphone`,label:`sidebar.promotions`,permission:`view-promotions`},{to:`/settings/database`,icon:`pi pi-database`,label:`sidebar.database`,permission:`manage-tenant-settings`}]}],j=x(()=>A.filter(e=>!e.permission||l.value.includes(e.permission)).map(e=>({...e,items:e.items.filter(e=>!e.permission||l.value.includes(e.permission))})).filter(e=>e.items.length>0)),M=()=>{let e=i.path;j.value.forEach(t=>{t.items.some(t=>e.startsWith(t.to))&&g.value.add(t.label)})};return y(()=>{M()}),u(()=>i.path,()=>{p.value||M()}),(e,t)=>{let c=n(`router-link`);return v(),d(`aside`,{class:_([`flex flex-col bg-white rounded-2xl shadow-sm border border-slate-100 transition-all duration-300 shrink-0 h-full overflow-hidden`,`hidden lg:flex`,p.value?`w-20`:`w-64`])},[b(`div`,_t,[p.value?(v(),d(`div`,Ct,[b(`span`,wt,w((f.value.name||`R`).charAt(0).toUpperCase()),1)])):(v(),d(`div`,vt,[b(`div`,yt,[f.value.logo_url?(v(),d(`img`,{key:0,src:f.value.logo_url,alt:`Logo`,class:`w-7 h-7 object-contain rounded-lg`},null,8,bt)):(v(),d(`span`,xt,w((f.value.name||`R`).charAt(0).toUpperCase()),1))]),b(`span`,St,w(f.value.name||`Restoku`),1)])),p.value?m(``,!0):(v(),d(`button`,{key:2,onClick:S,class:`p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors shrink-0`},[...t[1]||=[b(`i`,{class:`pi pi-bars text-base`},null,-1)]]))]),b(`nav`,Tt,[(v(!0),d(T,null,o(j.value,n=>(v(),d(`div`,{key:n.label},[n.label!==`common.dashboard`&&!p.value?(v(),d(`p`,Et,w(k(n.label)),1)):n.label!==`common.dashboard`&&p.value?(v(),d(`div`,Dt,[...t[2]||=[b(`div`,{class:`h-px w-8 bg-slate-200`},null,-1)]])):m(``,!0),n.label===`common.dashboard`?(v(),h(c,{key:2,to:`/dashboard`,class:_([`flex items-center gap-3 rounded-xl py-2.5 text-sm font-semibold transition-all duration-200`,p.value?`justify-center px-0 mx-auto w-10 h-10`:`px-3`,C(i).path===`/dashboard`?`bg-orange-500 text-white shadow-sm`:`text-slate-500 hover:bg-slate-50 hover:text-slate-800`]),onClick:t[0]||=t=>e.$emit(`update:modelValue`,!1)},{default:r(()=>[t[3]||=b(`i`,{class:`pi pi-layout text-base shrink-0`},null,-1),p.value?m(``,!0):(v(),d(`span`,Ot,w(e.$t(`common.overview`)),1)),p.value?(v(),d(`div`,kt,w(e.$t(`common.overview`)),1)):m(``,!0)]),_:1},8,[`class`])):m(``,!0),n.label===`common.dashboard`?m(``,!0):(v(),d(`button`,{key:3,onClick:e=>E(n.label),class:_([`w-full flex items-center gap-3 rounded-xl py-2.5 text-sm font-semibold transition-all duration-200`,p.value?`justify-center px-0`:`px-3 justify-between`,D(n.label)?`text-slate-800`:`text-slate-400 hover:text-slate-700 hover:bg-slate-50`])},[b(`div`,jt,[b(`i`,{class:_([n.icon||`pi pi-folder`,`text-base shrink-0`])},null,2),p.value?m(``,!0):(v(),d(`span`,Mt,w(e.$t(n.label)),1))]),p.value?m(``,!0):(v(),d(`i`,{key:0,class:_([`pi pi-chevron-down text-[10px] transition-transform duration-300`,D(n.label)?`rotate-180`:``])},null,2))],10,At)),n.label!==`common.dashboard`&&D(n.label)?(v(),d(`div`,{key:4,class:_([`space-y-0.5 mt-0.5`,p.value?``:`pl-3`])},[(v(!0),d(T,null,o(n.items,t=>(v(),h(a(t.external?`a`:`router-link`),{key:t.to,to:t.external?void 0:t.to,href:t.external?t.to:void 0,target:t.external?`_blank`:void 0,class:_([`flex items-center gap-3 rounded-xl py-2.5 text-sm font-medium transition-all duration-200 group relative`,p.value?`justify-center px-0 w-10 h-10 mx-auto`:`px-3`,!t.external&&(C(i).path===t.to||C(i).path.startsWith(t.to+`/`))?`bg-orange-500 text-white shadow-sm`:`text-slate-500 hover:bg-slate-50 hover:text-slate-800`]),onClick:n=>t.external?null:e.$emit(`update:modelValue`,!1)},{default:r(()=>[b(`i`,{class:_([t.icon,`text-base shrink-0`])},null,2),p.value?m(``,!0):(v(),d(`span`,Nt,w(e.$t(t.label)),1)),p.value?(v(),d(`div`,Pt,w(e.$t(t.label)),1)):m(``,!0)]),_:2},1032,[`to`,`href`,`target`,`class`,`onClick`]))),128))],2)):m(``,!0)]))),128))]),b(`div`,Ft,[p.value?(v(),d(`div`,Vt,[b(`img`,{src:C(s).user?.attributes?.avatar_url||`https://ui-avatars.com/api/?name=${C(s).user?.attributes?.name||`Admin`}&background=f97316&color=fff&size=64`,alt:`User`,class:`w-9 h-9 rounded-full border-2 border-orange-100 cursor-pointer`,onClick:S},null,8,Ht)])):(v(),d(`div`,It,[b(`img`,{src:C(s).user?.attributes?.avatar_url||`https://ui-avatars.com/api/?name=${C(s).user?.attributes?.name||`Admin`}&background=f97316&color=fff&size=64`,alt:`User`,class:`w-9 h-9 rounded-full shrink-0 border-2 border-orange-100`},null,8,Lt),b(`div`,Rt,[b(`p`,zt,w(C(s).user?.attributes?.name||`Admin`),1),b(`p`,Bt,w(C(s).user?.attributes?.role||`Administrator`),1)]),b(`button`,{onClick:S,class:`p-1 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors`},[...t[4]||=[b(`i`,{class:`pi pi-ellipsis-v text-sm`},null,-1)]])]))])],2)}}},[[`__scopeId`,`data-v-d4b5fe45`]]),Wt=z.extend({name:`menu`,style:`
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
`,classes:{root:function(e){return[`p-menu p-component`,{"p-menu-overlay":e.props.popup}]},start:`p-menu-start`,list:`p-menu-list`,submenuLabel:`p-menu-submenu-label`,separator:`p-menu-separator`,end:`p-menu-end`,item:function(e){var t=e.instance;return[`p-menu-item`,{"p-focus":t.id===t.focusedOptionId,"p-disabled":t.disabled()}]},itemContent:`p-menu-item-content`,itemLink:`p-menu-item-link`,itemIcon:`p-menu-item-icon`,itemLabel:`p-menu-item-label`}}),Gt={name:`BaseMenu`,extends:A,props:{popup:{type:Boolean,default:!1},model:{type:Array,default:null},appendTo:{type:[String,Object],default:`body`},autoZIndex:{type:Boolean,default:!0},baseZIndex:{type:Number,default:0},tabindex:{type:Number,default:0},ariaLabel:{type:String,default:null},ariaLabelledby:{type:String,default:null}},style:Wt,provide:function(){return{$pcMenu:this,$parentInstance:this}}},Kt={name:`Menuitem`,hostName:`Menu`,extends:A,inheritAttrs:!1,emits:[`item-click`,`item-mousemove`],props:{item:null,templates:null,id:null,focusedOptionId:null,index:null},methods:{getItemProp:function(e,t){return e&&e.item?se(e.item[t]):void 0},getPTOptions:function(e){return this.ptm(e,{context:{item:this.item,index:this.index,focused:this.isItemFocused(),disabled:this.disabled()}})},isItemFocused:function(){return this.focusedOptionId===this.id},onItemClick:function(e){var t=this.getItemProp(this.item,`command`);t&&t({originalEvent:e,item:this.item.item}),this.$emit(`item-click`,{originalEvent:e,item:this.item,id:this.id})},onItemMouseMove:function(e){this.$emit(`item-mousemove`,{originalEvent:e,item:this.item,id:this.id})},visible:function(){return typeof this.item.visible==`function`?this.item.visible():this.item.visible!==!1},disabled:function(){return typeof this.item.disabled==`function`?this.item.disabled():this.item.disabled},label:function(){return typeof this.item.label==`function`?this.item.label():this.item.label},getMenuItemProps:function(e){return{action:t({class:this.cx(`itemLink`),tabindex:`-1`},this.getPTOptions(`itemLink`)),icon:t({class:[this.cx(`itemIcon`),e.icon]},this.getPTOptions(`itemIcon`)),label:t({class:this.cx(`itemLabel`)},this.getPTOptions(`itemLabel`))}}},computed:{dataP:function(){return j({focus:this.isItemFocused(),disabled:this.disabled()})}},directives:{ripple:k}},qt=[`id`,`aria-label`,`aria-disabled`,`data-p-focused`,`data-p-disabled`,`data-p`],Jt=[`data-p`],Yt=[`href`,`target`],Xt=[`data-p`],Zt=[`data-p`];function Qt(e,n,r,o,s,c){var u=i(`ripple`);return c.visible()?(v(),d(`li`,t({key:0,id:r.id,class:[e.cx(`item`),r.item.class],role:`menuitem`,style:r.item.style,"aria-label":c.label(),"aria-disabled":c.disabled(),"data-p-focused":c.isItemFocused(),"data-p-disabled":c.disabled()||!1,"data-p":c.dataP},c.getPTOptions(`item`)),[b(`div`,t({class:e.cx(`itemContent`),onClick:n[0]||=function(e){return c.onItemClick(e)},onMousemove:n[1]||=function(e){return c.onItemMouseMove(e)},"data-p":c.dataP},c.getPTOptions(`itemContent`)),[r.templates.item?r.templates.item?(v(),h(a(r.templates.item),{key:1,item:r.item,label:c.label(),props:c.getMenuItemProps(r.item)},null,8,[`item`,`label`,`props`])):m(``,!0):l((v(),d(`a`,t({key:0,href:r.item.url,class:e.cx(`itemLink`),target:r.item.target,tabindex:`-1`},c.getPTOptions(`itemLink`)),[r.templates.itemicon?(v(),h(a(r.templates.itemicon),{key:0,item:r.item,class:_(e.cx(`itemIcon`))},null,8,[`item`,`class`])):r.item.icon?(v(),d(`span`,t({key:1,class:[e.cx(`itemIcon`),r.item.icon],"data-p":c.dataP},c.getPTOptions(`itemIcon`)),null,16,Xt)):m(``,!0),b(`span`,t({class:e.cx(`itemLabel`),"data-p":c.dataP},c.getPTOptions(`itemLabel`)),w(c.label()),17,Zt)],16,Yt)),[[u]])],16,Jt)],16,qt)):m(``,!0)}Kt.render=Qt;function $t(e){return rn(e)||nn(e)||tn(e)||en()}function en(){throw TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function tn(e,t){if(e){if(typeof e==`string`)return $(e,t);var n={}.toString.call(e).slice(8,-1);return n===`Object`&&e.constructor&&(n=e.constructor.name),n===`Map`||n===`Set`?Array.from(e):n===`Arguments`||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?$(e,t):void 0}}function nn(e){if(typeof Symbol<`u`&&e[Symbol.iterator]!=null||e[`@@iterator`]!=null)return Array.from(e)}function rn(e){if(Array.isArray(e))return $(e)}function $(e,t){(t==null||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var an={name:`Menu`,extends:Gt,inheritAttrs:!1,emits:[`show`,`hide`,`focus`,`blur`],data:function(){return{overlayVisible:!1,focused:!1,focusedOptionIndex:-1,selectedOptionIndex:-1}},target:null,outsideClickListener:null,scrollHandler:null,resizeListener:null,container:null,list:null,mounted:function(){this.popup||(this.bindResizeListener(),this.bindOutsideClickListener())},beforeUnmount:function(){this.unbindResizeListener(),this.unbindOutsideClickListener(),this.scrollHandler&&=(this.scrollHandler.destroy(),null),this.target=null,this.container&&this.autoZIndex&&N.clear(this.container),this.container=null},methods:{itemClick:function(e){var t=e.item;this.disabled(t)||(t.command&&t.command(e),this.overlayVisible&&this.hide(),!this.popup&&this.focusedOptionIndex!==e.id&&(this.focusedOptionIndex=e.id))},itemMouseMove:function(e){this.focused&&(this.focusedOptionIndex=e.id)},onListFocus:function(e){this.focused=!0,!this.popup&&this.changeFocusedOptionIndex(0),this.$emit(`focus`,e)},onListBlur:function(e){this.focused=!1,this.focusedOptionIndex=-1,this.$emit(`blur`,e)},onListKeyDown:function(e){switch(e.code){case`ArrowDown`:this.onArrowDownKey(e);break;case`ArrowUp`:this.onArrowUpKey(e);break;case`Home`:this.onHomeKey(e);break;case`End`:this.onEndKey(e);break;case`Enter`:case`NumpadEnter`:this.onEnterKey(e);break;case`Space`:this.onSpaceKey(e);break;case`Escape`:this.popup&&(L(this.target),this.hide());case`Tab`:this.overlayVisible&&this.hide();break}},onArrowDownKey:function(e){var t=this.findNextOptionIndex(this.focusedOptionIndex);this.changeFocusedOptionIndex(t),e.preventDefault()},onArrowUpKey:function(e){if(e.altKey&&this.popup)L(this.target),this.hide(),e.preventDefault();else{var t=this.findPrevOptionIndex(this.focusedOptionIndex);this.changeFocusedOptionIndex(t),e.preventDefault()}},onHomeKey:function(e){this.changeFocusedOptionIndex(0),e.preventDefault()},onEndKey:function(e){this.changeFocusedOptionIndex(P(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`).length-1),e.preventDefault()},onEnterKey:function(e){var t=le(this.list,`li[id="${`${this.focusedOptionIndex}`}"]`),n=t&&le(t,`a[data-pc-section="itemlink"]`);this.popup&&L(this.target),n?n.click():t&&t.click(),e.preventDefault()},onSpaceKey:function(e){this.onEnterKey(e)},findNextOptionIndex:function(e){var t=$t(P(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`)).findIndex(function(t){return t.id===e});return t>-1?t+1:0},findPrevOptionIndex:function(e){var t=$t(P(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`)).findIndex(function(t){return t.id===e});return t>-1?t-1:0},changeFocusedOptionIndex:function(e){var t=P(this.container,`li[data-pc-section="item"][data-p-disabled="false"]`),n=e>=t.length?t.length-1:e<0?0:e;n>-1&&(this.focusedOptionIndex=t[n].getAttribute(`id`))},toggle:function(e,t){this.overlayVisible?this.hide():this.show(e,t)},show:function(e,t){this.overlayVisible=!0,this.target=t??e.currentTarget},hide:function(){this.overlayVisible=!1,this.target=null},onEnter:function(e){te(e,{position:`absolute`,top:`0`}),this.alignOverlay(),this.bindOutsideClickListener(),this.bindResizeListener(),this.bindScrollListener(),this.autoZIndex&&N.set(`menu`,e,this.baseZIndex||this.$primevue.config.zIndex.menu),this.popup&&L(this.list),this.$emit(`show`)},onLeave:function(){this.unbindOutsideClickListener(),this.unbindResizeListener(),this.unbindScrollListener(),this.$emit(`hide`)},onAfterLeave:function(e){this.autoZIndex&&N.clear(e)},alignOverlay:function(){oe(this.container,this.target),I(this.target)>I(this.container)&&(this.container.style.minWidth=I(this.target)+`px`)},bindOutsideClickListener:function(){var e=this;this.outsideClickListener||(this.outsideClickListener=function(t){var n=e.container&&!e.container.contains(t.target),r=!(e.target&&(e.target===t.target||e.target.contains(t.target)));e.overlayVisible&&n&&r?e.hide():!e.popup&&n&&r&&(e.focusedOptionIndex=-1)},document.addEventListener(`click`,this.outsideClickListener,!0))},unbindOutsideClickListener:function(){this.outsideClickListener&&=(document.removeEventListener(`click`,this.outsideClickListener,!0),null)},bindScrollListener:function(){var e=this;this.scrollHandler||=new ce(this.target,function(){e.overlayVisible&&e.hide()}),this.scrollHandler.bindScrollListener()},unbindScrollListener:function(){this.scrollHandler&&this.scrollHandler.unbindScrollListener()},bindResizeListener:function(){var e=this;this.resizeListener||(this.resizeListener=function(){e.overlayVisible&&!re()&&e.hide()},window.addEventListener(`resize`,this.resizeListener))},unbindResizeListener:function(){this.resizeListener&&=(window.removeEventListener(`resize`,this.resizeListener),null)},visible:function(e){return typeof e.visible==`function`?e.visible():e.visible!==!1},disabled:function(e){return typeof e.disabled==`function`?e.disabled():e.disabled},label:function(e){return typeof e.label==`function`?e.label():e.label},onOverlayClick:function(e){ve.emit(`overlay-click`,{originalEvent:e,target:this.target})},containerRef:function(e){this.container=e},listRef:function(e){this.list=e}},computed:{focusedOptionId:function(){return this.focusedOptionIndex===-1?null:this.focusedOptionIndex},dataP:function(){return j({popup:this.popup})}},components:{PVMenuitem:Kt,Portal:V}},on=[`id`,`data-p`],sn=[`id`,`tabindex`,`aria-activedescendant`,`aria-label`,`aria-labelledby`],cn=[`id`];function ln(e,i,a,c,l,u){var f=n(`PVMenuitem`),g=n(`Portal`);return v(),h(g,{appendTo:e.appendTo,disabled:!e.popup},{default:r(function(){return[D(S,t({name:`p-anchored-overlay`,onEnter:u.onEnter,onLeave:u.onLeave,onAfterLeave:u.onAfterLeave},e.ptm(`transition`)),{default:r(function(){return[!e.popup||l.overlayVisible?(v(),d(`div`,t({key:0,ref:u.containerRef,id:e.$id,class:e.cx(`root`),onClick:i[3]||=function(){return u.onOverlayClick&&u.onOverlayClick.apply(u,arguments)},"data-p":u.dataP},e.ptmi(`root`)),[e.$slots.start?(v(),d(`div`,t({key:0,class:e.cx(`start`)},e.ptm(`start`)),[s(e.$slots,`start`)],16)):m(``,!0),b(`ul`,t({ref:u.listRef,id:e.$id+`_list`,class:e.cx(`list`),role:`menu`,tabindex:e.tabindex,"aria-activedescendant":l.focused?u.focusedOptionId:void 0,"aria-label":e.ariaLabel,"aria-labelledby":e.ariaLabelledby,onFocus:i[0]||=function(){return u.onListFocus&&u.onListFocus.apply(u,arguments)},onBlur:i[1]||=function(){return u.onListBlur&&u.onListBlur.apply(u,arguments)},onKeydown:i[2]||=function(){return u.onListKeyDown&&u.onListKeyDown.apply(u,arguments)}},e.ptm(`list`)),[(v(!0),d(T,null,o(e.model,function(n,r){return v(),d(T,{key:u.label(n)+r.toString()},[n.items&&u.visible(n)&&!n.separator?(v(),d(T,{key:0},[n.items?(v(),d(`li`,t({key:0,id:e.$id+`_`+r,class:[e.cx(`submenuLabel`),n.class],role:`none`},{ref_for:!0},e.ptm(`submenuLabel`)),[s(e.$slots,e.$slots.submenulabel?`submenulabel`:`submenuheader`,{item:n},function(){return[p(w(u.label(n)),1)]})],16,cn)):m(``,!0),(v(!0),d(T,null,o(n.items,function(i,a){return v(),d(T,{key:i.label+r+`_`+a},[u.visible(i)&&!i.separator?(v(),h(f,{key:0,id:e.$id+`_`+r+`_`+a,item:i,templates:e.$slots,focusedOptionId:u.focusedOptionId,unstyled:e.unstyled,onItemClick:u.itemClick,onItemMousemove:u.itemMouseMove,pt:e.pt},null,8,[`id`,`item`,`templates`,`focusedOptionId`,`unstyled`,`onItemClick`,`onItemMousemove`,`pt`])):u.visible(i)&&i.separator?(v(),d(`li`,t({key:`separator`+r+a,class:[e.cx(`separator`),n.class],style:i.style,role:`separator`},{ref_for:!0},e.ptm(`separator`)),null,16)):m(``,!0)],64)}),128))],64)):u.visible(n)&&n.separator?(v(),d(`li`,t({key:`separator`+r.toString(),class:[e.cx(`separator`),n.class],style:n.style,role:`separator`},{ref_for:!0},e.ptm(`separator`)),null,16)):(v(),h(f,{key:u.label(n)+r.toString(),id:e.$id+`_`+r,item:n,index:r,templates:e.$slots,focusedOptionId:u.focusedOptionId,unstyled:e.unstyled,onItemClick:u.itemClick,onItemMousemove:u.itemMouseMove,pt:e.pt},null,8,[`id`,`item`,`index`,`templates`,`focusedOptionId`,`unstyled`,`onItemClick`,`onItemMousemove`,`pt`]))],64)}),128))],16,sn),e.$slots.end?(v(),d(`div`,t({key:1,class:e.cx(`end`)},e.ptm(`end`)),[s(e.$slots,`end`)],16)):m(``,!0)],16,on)):m(``,!0)]}),_:3},16,[`onEnter`,`onLeave`,`onAfterLeave`])]}),_:3},8,[`appendTo`,`disabled`])}an.render=ln;var un={class:`flex min-h-[4rem] py-3 shrink-0 items-center justify-between px-4 md:px-6 bg-white rounded-2xl shadow-sm border border-slate-100 transition-all duration-300`},dn={class:`flex items-center gap-4 flex-1`},fn={class:`lg:hidden`},pn={id:`app-header-greeting`,class:`hidden lg:flex flex-col items-start`},mn={class:`flex items-center gap-1.5`},hn={class:`text-sm font-medium text-slate-500`},gn={class:`text-sm font-bold text-slate-800`},_n={class:`text-sm font-medium text-slate-500`},vn={class:`font-bold text-orange-500`},yn={class:`text-[10px] text-slate-400 mt-0.5 flex items-center gap-2`},bn={class:`flex items-center`},xn={class:`flex items-center font-mono font-bold text-slate-500`},Sn={class:`italic text-slate-400`},Cn={class:`flex items-center gap-2`},wn=[`src`],Tn={class:`text-left hidden md:block`},En={class:`text-xs font-semibold text-slate-800 leading-tight`},Dn={class:`text-[10px] text-orange-500 font-bold capitalize leading-tight`},On={__name:`AppHeader`,emits:[`toggle-sidebar`],setup(t){let n=ae(),r=ie(),i=B(),{t:a}=ue(),o=c(),s=c(``),l=null,u=x(()=>new Intl.DateTimeFormat(`id-ID`,{day:`numeric`,month:`long`,year:`numeric`}).format(new Date)),f=x(()=>r.user?.attributes?.permissions||[]),g=x(()=>r.user?.relationships?.tenant?.data?.attributes||{}),_=x(()=>[{label:a(`user_menu.profile`),icon:`pi pi-user`,command:()=>n.push(`/settings/profile`)},{label:a(`user_menu.users`),icon:`pi pi-users`,command:()=>n.push(`/settings/users`),visible:f.value.includes(`manage-users`)},{separator:!0},{label:a(`user_menu.logout`),icon:`pi pi-sign-out`,command:async()=>{await r.logout(),n.push(`/login`)}}]);function S(e){o.value?.toggle(e)}function T(){s.value=new Date().toLocaleTimeString(`id-ID`,{hour:`2-digit`,minute:`2-digit`,second:`2-digit`})}return y(()=>{T(),l=setInterval(T,1e3)}),e(()=>{l&&clearInterval(l)}),(e,t)=>(v(),d(`header`,un,[b(`div`,dn,[b(`div`,fn,[b(`button`,{class:`p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors`,onClick:t[0]||=t=>e.$emit(`toggle-sidebar`)},[...t[2]||=[b(`i`,{class:`pi pi-bars text-lg`},null,-1)]])]),t[7]||=b(`div`,{id:`app-header-title`,class:`flex-1 min-w-0`},null,-1),b(`div`,pn,[b(`div`,mn,[b(`span`,hn,w(C(a)(`header.greeting`))+`,`,1),b(`span`,gn,w(C(r).user?.attributes?.name?.split(` `)[0]||`Admin`),1),b(`span`,_n,[p(` ! `+w(C(a)(`header.welcome_back`))+` `+w(g.value.name?`di `:``),1),b(`span`,vn,w(g.value.name),1),t[3]||=p(`. `,-1)])]),b(`p`,yn,[b(`span`,bn,[t[4]||=b(`i`,{class:`pi pi-calendar text-[10px] mr-1`},null,-1),p(` `+w(u.value),1)]),t[6]||=b(`span`,{class:`w-px h-2 bg-slate-200`},null,-1),b(`span`,xn,[t[5]||=b(`i`,{class:`pi pi-clock text-[10px] mr-1`},null,-1),p(` `+w(s.value),1)]),b(`span`,Sn,`. `+w(C(a)(`header.nice_day`)),1)])])]),b(`div`,Cn,[t[9]||=b(`div`,{id:`app-header-actions`,class:`flex items-center gap-2 mr-1 md:mr-2`},null,-1),C(i).activeShift?(v(),h(C(_e),{key:0,icon:`pi pi-clock`,value:C(a)(`header.shift_open`),severity:`success`,class:`hidden sm:flex !rounded-xl px-3 py-1.5 !bg-orange-50 !text-orange-600 !border-orange-100`},null,8,[`value`])):m(``,!0),t[10]||=b(`button`,{class:`relative p-2.5 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors`},[b(`i`,{class:`pi pi-bell text-lg`}),b(`span`,{class:`absolute top-2 right-2 w-2 h-2 bg-orange-500 rounded-full`})],-1),b(`button`,{class:`p-2.5 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors`,onClick:t[1]||=()=>C(n).push(`/settings/tenant`)},[...t[8]||=[b(`i`,{class:`pi pi-cog text-lg`},null,-1)]]),t[11]||=b(`div`,{class:`h-8 w-px bg-slate-100 mx-1 hidden md:block`},null,-1),b(`button`,{class:`flex items-center gap-2.5 hover:bg-slate-50 p-1.5 pr-3 rounded-2xl transition-colors`,onClick:S},[b(`img`,{src:C(r).user?.attributes?.avatar_url||`https://ui-avatars.com/api/?name=${C(r).user?.attributes?.name||`Admin`}&background=f97316&color=fff&size=64`,alt:`User`,class:`w-9 h-9 rounded-full border-2 border-orange-100`},null,8,wn),b(`div`,Tn,[b(`p`,En,w(C(r).user?.attributes?.name?.split(` `)[0]||`Admin`),1),b(`p`,Dn,w(C(r).user?.attributes?.role||`Administrator`),1)])]),D(C(an),{ref_key:`userMenu`,ref:o,model:_.value,popup:``,class:`!rounded-xl !shadow-xl !border-slate-100`},null,8,[`model`])])]))}},kn={class:`flex h-screen bg-slate-50 font-sans text-slate-800 p-2 gap-2`},An={class:`flex flex-1 flex-col gap-2 overflow-hidden min-w-0`},jn={class:`flex-1 overflow-y-auto`},Mn=de({__name:`AppLayout`,setup(e){let t=B(),r=c(!1);return y(()=>{t.fetchCurrentShift()}),(e,t)=>{let i=n(`router-view`);return v(),d(T,null,[b(`div`,kn,[r.value?(v(),d(`div`,{key:0,class:`fixed inset-0 z-40 bg-black/50 lg:hidden`,onClick:t[0]||=e=>r.value=!1})):m(``,!0),D(Ut,{modelValue:r.value,"onUpdate:modelValue":t[1]||=e=>r.value=e},null,8,[`modelValue`]),b(`div`,An,[D(On,{onToggleSidebar:t[2]||=e=>r.value=!r.value}),b(`main`,jn,[D(i)])])]),D(C(ot),{position:`top-right`}),D(C(ht))],64)}}},[[`__scopeId`,`data-v-81f26228`]]);export{Mn as default};