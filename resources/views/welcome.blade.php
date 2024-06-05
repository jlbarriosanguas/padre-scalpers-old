<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
	@include('includes.head')
	<style>
	.content {
		text-align: center;
	}
	</style>
</head>

<body>
	<div class="flex-center position-ref full-height">
		@if (Route::has('login'))
		<div class="top-right links">
			@auth
			<a href="{{ url('/home') }}">Home</a>
			@else
			<a href="{{ route('login') }}">Login</a>
			<a href="{{ route('register') }}">Register</a>
			@endauth
		</div>
		@endif

		<div class="content">
			<div class="logo">
				@svg('scp_padre_logo')
			</div>
			<div class="title m-b-md">
				PADRE PRODUCCIÓN
			</div>

			{{--
			<div class="links">
				<a href="https://laravel.com/docs">Documentation</a>
				<a href="https://laracasts.com">Laracasts</a>
				<a href="https://laravel-news.com">News</a>
				<a href="https://forge.laravel.com">Forge</a>
				<a href="https://github.com/laravel/laravel">GitHub</a>
			</div>
			--}}
		</div>
	</div>

	@include('includes.appbridge')
    <script>
    var myTitleBar = TitleBar.create(app, {
        buttons: {
            secondary: [feedsButton, importerGroupButton, rrhhGroupButton],
        },
    });
	</script>

	@include('includes.scripts')
</body>

</html>
