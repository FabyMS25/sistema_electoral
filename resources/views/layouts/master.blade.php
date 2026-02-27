<!doctype html >
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="horizontal" data-sidebar-visibility="show" data-topbar="dark" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" >

<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    
    <link rel="shortcut icon" href="{{ URL::asset('build/images/logo_elections.png')}}">
    @include('layouts.head-css')
</head>

@section('body')
    @include('layouts.body')
@show

    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
    
    @include('layouts.vendor-scripts')
</body>

</html>
