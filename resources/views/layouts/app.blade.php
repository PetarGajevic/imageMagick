<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">


    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        
	<!-- favicon --> 
	 <link rel="icon" href="http://propeller.in/assets/images/favicon.ico" type="image/x-icon"> 
	
	<!-- Bootstrap --> 
{{-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" rel="stylesheet" />   --}}
	
	<!-- Example docs (CSS for helping component example file)-->
 <link href="https://propeller.in/docs/css/example-docs.css" type="text/css" rel="stylesheet" />

	<!-- Propeller card (CSS for helping component example file) -->
	<link href="https://propeller.in/components/card/css/card.css" type="text/css" rel="stylesheet" /> 

	<!-- Propeller typography -->
	<link href="/css/typography.css" type="text/css" rel="stylesheet" />

	<!-- Google Icon Font -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="/css/google-icons.css" type="text/css" rel="stylesheet" />

	<!-- Propeller dropdown -->
	<link href="/css/dropdown.css" type="text/css" rel="stylesheet"/>

	<!-- Propeller navbar -->
	<link href="/css/navbar.css" type="text/css" rel="stylesheet"/>
	
	<!-- Propeller button  -->
	<link href="/css/button.css" type="text/css" rel="stylesheet"/>
	
	<!-- Propeller list  -->
	<link href="/css/list.css" type="text/css" rel="stylesheet"/>

	<!-- Propeller sidebar  -->
    <link rel="stylesheet" type="text/css" href="/css/sidebar.css">
    
    	<!-- sidebar  -->
	<link rel="stylesheet" type="text/css" href="/css/mysidebar.css">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> 
    
 {{--  <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>  --}}
</head>
<body>
   
        @include('layouts.header')
        <main class="py-4" style="margin-top:100px;">
           
        </main>
       
    
</body>
<script
  src="https://code.jquery.com/jquery-2.2.4.min.js"
  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
  crossorigin="anonymous"></script>
{{-- <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>  --}}

<!-- Bootstrap js -->
<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<!-- Propeller Global js --> 
<script src="/js/global.js"></script>
	
<!-- Propeller Sidebar js -->
<script type="text/javascript" language="javascript" src="/js/sidebar.js"></script>

<!-- Propeller Dropdown js -->
<script type="text/javascript" language="javascript" src="/js/dropdown.js"></script>
</html>