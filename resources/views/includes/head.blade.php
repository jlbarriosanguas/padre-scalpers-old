<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Padre Producción</title>
<link rel="shortcut icon" href="{{ asset('images/logo.ico') }}">
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Raleway:100,400,600" rel="stylesheet" type="text/css">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">

<!-- Bootstrap -->
<link href="{!! asset('assets/bootstrap4/css/bootstrap.min.css') !!}" rel="stylesheet" type="text/css">

{{--
<!-- Polaris -->
<link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@4.0.0/styles.min.css"/>
--}}

<!-- Styles -->
<style>
    html,
    body {
        color: #636b6f;
        font-family: 'Raleway', sans-serif;
        font-weight: 100;
        height: 100vh;
        margin: 0;
    }

    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: left;
    }

    .title {
        font-size: 84px;
    }

    .links>a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .version {
        color: #636b6f;
        padding: 0 25px;
        font-size: 20px;
        font-weight: 600;
        letter-spacing: .1rem;
    }

    .m-b-md {
        margin-bottom: 30px;
    }

    .logo {
        margin-bottom: 20px;
    }

    .logo svg {
        height: 150px;
    }

    #feed-alerts,
    #create-offer-alerts {
        font-weight: 400;
        text-align: left;
    }

    #fb-feed table td,
    #offer-view table td{
        font-weight: 400;
    }

    #feed-alerts p.checkbox,
    #feed-alerts p,
    #create-offer-alerts p{
        margin: 0;
    }

    .stack-trace,
    .debug-info {
        display: none;
    }

    .trigger-debug {
        position: absolute;
        top: 0;
        right: 0;
        margin: 1em;
    }

    .checkbox label:after,
    .radio label:after {
        content: '';
        display: table;
        clear: both;
    }

    .checkbox label,
    .radio label {
        cursor: pointer;
    }

    .checkbox .cr,
    .radio .cr {
        position: relative;
        display: inline-block;
        border: 1px solid #a9a9a9;
        border-radius: .25em;
        width: 1.3em;
        height: 1.3em;
        float: left;
        margin-right: .5em;
    }

    .radio .cr {
        border-radius: 50%;
    }

    .checkbox .cr .cr-icon,
    .radio .cr .cr-icon {
        position: absolute;
        font-size: .8em;
        line-height: 0;
        top: 50%;
        left: 20%;
    }

    .radio .cr .cr-icon {
        margin-left: 0.04em;
    }

    .checkbox label input[type="checkbox"],
    .radio label input[type="radio"] {
        display: none;
    }

    .checkbox label input[type="checkbox"] + .cr > .cr-icon,
    .radio label input[type="radio"] + .cr > .cr-icon {
        transform: scale(3) rotateZ(-20deg);
        opacity: 0;
        transition: all .3s ease-in;
    }

    .checkbox label input[type="checkbox"]:checked + .cr > .cr-icon,
    .radio label input[type="radio"]:checked + .cr > .cr-icon {
        transform: scale(1) rotateZ(0deg);
        opacity: 1;
    }

    .checkbox label input[type="checkbox"]:disabled + .cr,
    .radio label input[type="radio"]:disabled + .cr {
        opacity: .5;
    }

    #create-offer label {
        font-weight: 400;
    }

    @media (min-width: 900px) {
        #create-offer .form-group {
           padding: 0;
        }
    }

    .tox-statusbar__branding {
        display: none;
    }

    ul.pagination {
        font-weight: 400;
    }
</style>