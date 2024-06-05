<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
	@include('includes.head')
</head>

<body>
	<div class="container mt-5">
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
			<div id="collections-importer">
				<p class="h4">Importar colecciones <span class="badge badge-primary" style="font-size: 50%; vertical-align: middle;">v1</span> <span class="badge badge-warning" style="font-size: 50%; vertical-align: middle;">Experimental</span></p>
				<hr class="my-3">
                <p class="mb-4" style="font-weight: 400;">Importador de colecciones automáticas. Sincronización entre tiendas</p>

                <div id="feed-alerts" class="mt-4"></div>
            </div>
            <div class="mt-3">
            <form action="import" method="post">
                {{ csrf_field() }}
                <div class="custom-file mb-3">
                <input type="file" class="custom-file-input" id="customFile" name="filename">
                <label class="custom-file-label" for="customFile">Choose file</label>
                </div>
                <div class="mt-3">
                <button type="submit" class="btn btn-dark">Submit</button>
                </div>
            </form>
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
        title: "Importar Colecciones",
        buttons: {
            secondary: [feedsButton, importerGroupButton, rrhhGroupButton],
        },
    });
	</script>

	@include('includes.scripts')
	<script>
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
		$('#collections-importer button').click(function() {

		});
	</script>
</body>

</html>
