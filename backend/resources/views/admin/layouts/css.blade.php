@php
    $adminSiteName = \App\Services\WebsiteSettingsService::getSiteName();
    $adminSiteLogo = \App\Services\WebsiteSettingsService::getSiteLogo();
    $adminFavicon = \App\Services\WebsiteSettingsService::getSiteFavicon();
    $adminFaviconHref = $adminFavicon ?: asset('admin/dist/assets/images/favicon.ico');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dashboard') | {{ $adminSiteName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="{{ \App\Services\WebsiteSettingsService::getMetaDescription() }}" name="description">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ $adminFaviconHref }}">

    <!-- plugin css -->
    <link href="{{ asset('admin/dist/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet" type="text/css">

    <!-- preloader css -->
    <link rel="stylesheet" href="{{asset('admin/dist/assets/css/preloader.min.css')}}" type="text/css">

    <!-- Bootstrap Css -->
    <link href="{{asset('admin/dist/assets/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <!-- Icons Css -->
    <link href="{{asset('admin/dist/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css">
    <!-- App Css-->
    <link href="{{asset('admin/dist/assets/css/app.min.css')}}" id="app-style" rel="stylesheet" type="text/css">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
