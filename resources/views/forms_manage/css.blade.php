<style>
    :root {
        --color-default: grey;
        --color-invalid: red;
        --color-valid: green;
    }

    input {
        border: 2px solid var(--color-default);
        /*text-align: center;*/
    }

    .error_div {
        background: rgb(240, 89, 83);
        color: white;
        height: 70px;
        text-align: center;
        margin: 0;
        top: 50%;
        width: 100%;
        margin-top: 0;
        margin-left: 0;
        position: absolute;
        z-index: 999999;
        opacity: 0.89;
        display: none;
    }

    .error-rounded-bg {
        background-clip: padding-box;
        border-radius: 20px;
        background-color: #F0F0F0;
        color: #000000;
        padding: 5px 10px;
        display: inline;
    }



    .error-message {
        display: none;
        font-weight: bold;
        color: var(--color-invalid);
    }

    /* To demonstrate that the validations only happen on blur, we apply this malpractice */
    input:focus {
        outline: none;
    }

    /*fucus empty color*/
    /*input:required:focus:invalid {
                                                                                                                                                                                                                                            background: url("https://assets.digitalocean.com/labs/icons/exclamation-triangle-fill.svg") no-repeat 95% 40% rgb(247, 213, 213);
                                                                                                                                                                                                                                            background-size: 15px;
                                                                                                                                                                                                                                        }*/

    input:required:focus:invalid~.form-error-message {
        display: block;
    }

    /* Only show invalid ring while not focused
                                                                                                                                                                                                                                        input:not(:focus):not(:placeholder-shown):invalid {
                                                                                                                                                                                                                                            border-color: var(--color-invalid);
                                                                                                                                                                                                                                        }*/

    /*input:not(:focus):not(:placeholder-shown):invalid~.error-message {
                                                                                                                                                                                                                                            display: block;
                                                                                                                                                                                                                                        }*/

    /* Only show valid ring while not focused and if a value is entered */
    /* :empty won't work here as that targets elements that have no childeren. Therefore we abuse :placeholder-shown */
    input:not(:focus):not(:placeholder-shown):valid {
        border-color: var(--color-valid);
    }

    body {
        background-color: #112d53;
    }

    .form_header {
        color: #082d5f;
    }

    .form_sub_header {
        color: #12458D;
    }

    hr {
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 0;
        border-top: 1px solid #112d53;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -5px;
        margin-left: -5px;
        margin-bottom: 10px;
    }

    .form-group {
        margin-bottom: 10px;
        padding-left: 15px;
        padding-right: 15px;
        padding-top: 15px;
        padding-bottom: 15px;
    }

    .form-group>[class*=col-] {
        padding-right: 5px;
        padding-left: 5px;
    }

    .form-row>.col,
    .form-row>[class*=col-] {
        padding-right: 5px;
        padding-left: 5px
    }

    .div_center {
        margin: auto;

    }

    .form-sub-label {
        color: #224779;
    }

    .form-sub-label {
        font-size: .75em;
        margin-top: 11px;
        margin-left: 2px;
        display: block;
        word-break: break-word;
    }

    .form-label.form-label-auto {
        display: block;
        float: none;
        text-align: left;
        width: 100%;
        color: #082d5f;
    }

    .form-label-top {
        width: 100%;
        margin-left: 2px;
        margin-top: 10px;
        margin-bottom: 14px;
    }

    .form-label {
        display: inline-block;
        font-weight: 500;
        word-break: break-word;
    }

    .form-required {
        color: #f23a3c;
    }

    .form-required {
        margin-left: 4px;
    }


    .form-group:focus-within {
        background-color: white;

    }

    .form-focus-error {
        background-color: #f8cccd;
    }

    .bg-light2 {
        background-color: #e4efff;
    }

    .form-error-message {
        color: #fff;
        background-color: #f23a3c;
        background-image: url(data:image/svg+xml;charset=utf-8,%3Csvg width='14' height='15' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M7 14.515a7 7 0 110-14 7 7 0 010 14zm-.814-5.86h1.628v-5.21H6.186v5.21zM7 11.259a.82.82 0 00.814-.824A.816.816 0 007 9.631a.813.813 0 100 1.628z' fill='%23fff'/%3E%3C/svg%3E);
        font-size: .75em;
        margin-top: 8px;
        border-radius: 4px;
        background-size: 0.875em;
        background-position: 0.375em;
        background-repeat: no-repeat;
        display: none;
        width: 200px;
        padding: 0.25em 0.5em 0.25em 1.625em;
    }

    .form_none {
        display: none;
    }

    .dropzone {
        width: 100%;
        height: 180px;
        min-height: 0px !important;
    }



    .dropzonex,
    .dropzonex * {
        box-sizing: border-box;
    }

    .dropzonex {
        min-height: 150px;
        background: #e4efff;
        padding: 20px 20px;
        display: none;
    }

    .dropzonex.dz-clickable {
        cursor: pointer;
    }

    .dropzonex.dz-clickable * {
        cursor: default;
    }

    .dropzonex.dz-clickable .dz-message,
    .dropzonex.dz-clickable .dz-message * {
        cursor: pointer;
    }

    .dropzonex.dz-started .dz-message {
        display: none;
    }

    .dropzonex.dz-drag-hover {
        border-style: solid;
    }

    .dropzonex.dz-drag-hover .dz-message {
        opacity: 0.5;
    }

    .dropzonex .dz-message {
        text-align: center;
        margin: 2em 0;
    }

    .dropzonex .dz-preview {
        position: relative;
        display: inline-block;
        vertical-align: top;
        margin: 16px;
        min-height: 100px;
    }

    .dropzonex .dz-preview:hover {
        z-index: 1000;
    }

    .dropzonex .dz-preview:hover .dz-details {
        opacity: 1;
    }

    .dropzonex .dz-preview.dz-file-preview .dz-image {
        border-radius: 20px;
        background: #999;
        background: linear-gradient(to bottom, #eee, #ddd);
    }

    .dropzonex .dz-preview.dz-file-preview .dz-details {
        opacity: 1;
    }

    .dropzonex .dz-preview.dz-image-preview {
        background: #e4efff;
    }

    .dropzonex .dz-preview.dz-image-preview .dz-details {
        -webkit-transition: opacity 0.2s linear;
        -moz-transition: opacity 0.2s linear;
        -ms-transition: opacity 0.2s linear;
        -o-transition: opacity 0.2s linear;
        transition: opacity 0.2s linear;
    }

    .dropzonex .dz-preview .dz-remove {
        font-size: 14px;
        text-align: center;
        display: block;
        cursor: pointer;
        border: none;
    }

    .dropzonex .dz-preview .dz-remove:hover {
        text-decoration: underline;
    }

    .dropzonex .dz-preview:hover .dz-details {
        opacity: 1;
    }

    .dropzonex .dz-preview .dz-details {
        z-index: 20;
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        font-size: 13px;
        min-width: 100%;
        max-width: 100%;
        padding: 2em 1em;
        text-align: center;
        color: rgba(0, 0, 0, 0.9);
        line-height: 150%;
    }

    .dropzonex .dz-preview .dz-details .dz-size {
        margin-bottom: 1em;
        font-size: 16px;
    }

    .dropzonex .dz-preview .dz-details .dz-filename {
        white-space: nowrap;
    }

    .dropzonex .dz-preview .dz-details .dz-filename:hover span {
        border: 1px solid rgba(200, 200, 200, 0.8);
        background-color: rgba(255, 255, 255, 0.8);
    }

    .dropzonex .dz-preview .dz-details .dz-filename:not(:hover) {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dropzonex .dz-preview .dz-details .dz-filename:not(:hover) span {
        border: 1px solid transparent;
    }

    .dropzonex .dz-preview .dz-details .dz-filename span,
    .dropzonex .dz-preview .dz-details .dz-size span {
        background-color: rgba(255, 255, 255, 0.4);
        padding: 0 0.4em;
        border-radius: 3px;
    }

    .dropzonex .dz-preview:hover .dz-image img {
        -webkit-transform: scale(1.05, 1.05);
        -moz-transform: scale(1.05, 1.05);
        -ms-transform: scale(1.05, 1.05);
        -o-transform: scale(1.05, 1.05);
        transform: scale(1.05, 1.05);
        -webkit-filter: blur(8px);
        filter: blur(8px);
    }

    .dropzonex .dz-preview .dz-image {
        border-radius: 20px;
        overflow: hidden;
        width: 120px;
        height: 120px;
        position: relative;
        display: block;
        z-index: 10;
    }

    .form-actions-footer {
        position: relative;
        padding-bottom: 120px;
    }

    .form-submit-actions {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        padding: 12px 24px;
        border-radius: 15px;
        background: rgba(228, 239, 255, 0.95);
        box-shadow: 0px 12px 30px rgba(0, 0, 0, 0.15);
        z-index: 1050;
        max-width: calc(100% - 32px);
        flex-wrap: wrap;
    }

    .form-submit-actions .btn {
        min-width: 180px;
    }

    @media (max-width: 576px) {
        .form-actions-footer {
            padding-bottom: 160px;
        }

        .form-submit-actions {
            flex-direction: column;
            gap: 12px;
            padding: 16px 20px;
            bottom: 16px;
        }

        .form-submit-actions .btn {
            width: 100%;
            min-width: 0;
        }
    }

    .dropzonex .dz-preview .dz-image img {
        display: block;
    }

    .dropzonex .dz-preview.dz-success .dz-success-mark {
        -webkit-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
        -moz-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
        -ms-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
        -o-animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
        animation: passing-through 3s cubic-bezier(0.77, 0, 0.175, 1);
    }

    .dropzonex .dz-preview.dz-error .dz-error-mark {
        opacity: 1;
        -webkit-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
        -moz-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
        -ms-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
        -o-animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
        animation: slide-in 3s cubic-bezier(0.77, 0, 0.175, 1);
    }

    .dropzonex .dz-preview .dz-success-mark,
    .dropzonex .dz-preview .dz-error-mark {
        pointer-events: none;
        opacity: 0;
        z-index: 500;
        position: absolute;
        display: block;
        top: 50%;
        left: 50%;
        margin-left: -27px;
        margin-top: -27px;
    }

    .dropzonex .dz-preview .dz-success-mark svg,
    .dropzonex .dz-preview .dz-error-mark svg {
        display: block;
        width: 54px;
        height: 54px;
    }

    .dropzonex .dz-preview.dz-processing .dz-progress {
        opacity: 1;
        -webkit-transition: all 0.2s linear;
        -moz-transition: all 0.2s linear;
        -ms-transition: all 0.2s linear;
        -o-transition: all 0.2s linear;
        transition: all 0.2s linear;
    }

    .dropzonex .dz-preview.dz-complete .dz-progress {
        opacity: 0;
        -webkit-transition: opacity 0.4s ease-in;
        -moz-transition: opacity 0.4s ease-in;
        -ms-transition: opacity 0.4s ease-in;
        -o-transition: opacity 0.4s ease-in;
        transition: opacity 0.4s ease-in;
    }

    .dropzonex .dz-preview:not(.dz-processing) .dz-progress {
        -webkit-animation: pulse 6s ease infinite;
        -moz-animation: pulse 6s ease infinite;
        -ms-animation: pulse 6s ease infinite;
        -o-animation: pulse 6s ease infinite;
        animation: pulse 6s ease infinite;
    }

    .dropzonex .dz-preview .dz-progress {
        opacity: 1;
        z-index: 1000;
        pointer-events: none;
        position: absolute;
        height: 16px;
        left: 50%;
        top: 50%;
        margin-top: -8px;
        width: 80px;
        margin-left: -40px;
        background: rgba(255, 255, 255, 0.9);
        -webkit-transform: scale(1);
        border-radius: 8px;
        overflow: hidden;
    }

    .dropzonex .dz-preview .dz-progress .dz-upload {
        background: #333;
        background: linear-gradient(to bottom, #666, #444);
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 0;
        -webkit-transition: width 300ms ease-in-out;
        -moz-transition: width 300ms ease-in-out;
        -ms-transition: width 300ms ease-in-out;
        -o-transition: width 300ms ease-in-out;
        transition: width 300ms ease-in-out;
    }

    .dropzonex .dz-preview.dz-error .dz-error-message {
        display: block;
    }

    .dropzonex .dz-preview.dz-error:hover .dz-error-message {
        opacity: 1;
        pointer-events: auto;
    }

    .dropzonex .dz-preview .dz-error-message {
        pointer-events: none;
        z-index: 1000;
        position: absolute;
        display: block;
        display: none;
        opacity: 0;
        -webkit-transition: opacity 0.3s ease;
        -moz-transition: opacity 0.3s ease;
        -ms-transition: opacity 0.3s ease;
        -o-transition: opacity 0.3s ease;
        transition: opacity 0.3s ease;
        border-radius: 8px;
        font-size: 13px;
        top: 130px;
        left: -10px;
        width: 140px;
        background: #be2626;
        background: linear-gradient(to bottom, #be2626, #a92222);
        padding: 0.5em 1.2em;
        color: white;
    }

    .dropzonex .dz-preview .dz-error-message:after {
        content: '';
        position: absolute;
        top: -6px;
        left: 64px;
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-bottom: 6px solid #be2626;
    }

    .form-control {
        width: 100%;
    }

    /* #loader {
                                                                                                            display: none;
                                                                                                            position: fixed;
                                                                                                            top: 0;
                                                                                                            left: 0;
                                                                                                            right: 0;
                                                                                                            bottom: 0;
                                                                                                            width: 100%;
                                                                                                            background: rgba(0, 0, 0, 0.75) url(images/loading2.gif) no-repeat center center;
                                                                                                            z-index: 10000;
                                                                                                        } */
</style>
<style>
    /* .custom-image {
                                                                                                                                                                                        width: 395px;
                                                                                                                                                                                        height: 395px;
                                                                                                                                                                                    } */

    .custom-image {
        width: 100%;
        height: auto;
    }

    @media (max-width: 767px) {
        .custom-image {
            width: 100%;
            height: auto;
        }
    }

    #loader-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Semi-transparent background */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        z-index: 1000;
    }

    .spinner {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
        margin-bottom: 10px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    #loading-message {
        font-size: 18px;
        color: #fff;
        /* White color for the message to contrast with the dark background */
    }
</style>
