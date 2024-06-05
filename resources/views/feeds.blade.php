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
			<div id="fb-feed">
				<p class="h4">Feed Facebook <span class="badge badge-primary" style="font-size: 50%; vertical-align: middle;">v1.2</span></p>
				<hr class="my-3">
				<p class="mb-4" style="font-weight: 400;">Generador de feeds de Facebook (RSS/XML).</p>
				<div class="select-buttons">
					<div class="btn-group" role="group" aria-label="Avaliable stores">
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='es'>ES</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='fr'>FR</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='pt'>PT</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='uk'>UK</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='eu'>EU</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='de'>DE</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='ww'>WW</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='mx'>MX</button>
						<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='cl'>CL</button>
						{{-- <button type="button" class="btn btn-dark" data-merchant='facebook' data-store='t1'>TEST</button> --}}
					</div>
					<button type="button" class="btn btn-dark" data-merchant='facebook' data-store='all'>TODO</button>
					<span id="fb-feed-options" class="checkbox" style="margin-left: 1rem;">
						<label style="font-size: .8em; font-weight: 400;">
							<input type="checkbox" value="" name="option[without-stock]">
							<span class="cr"><i class="cr-icon fa fa-check"></i></span>
							Incluir productos sin stock
						</label>
					</span>
				</div>

				<div id="feed-alerts" class="mt-4"></div>
				<table class="table mt-4 text-center">
					<thead class="thead-dark">
						<tr>
							<th scope="col">Tienda</th>
							<th scope="col">Feed FB URL</th>
							<th scope="col">Actualizado (GMT)</th>
						</tr>
					</thead>
					<tbody style="font-size: .85em">
						<tr>
							<th scope="row">ES</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_es_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_es_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_es_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">FR</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_fr_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_fr_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_fr_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">PT</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_pt_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_pt_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_pt_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">UK</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_uk_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_uk_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_uk_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">EU</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_eu_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_eu_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_eu_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">DE</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_de_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_de_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_de_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">WW</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_ww_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_ww_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_ww_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">MX</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_mx_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_mx_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_mx_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">CL</th>
							<td><a href="https://padre.scalpers.es/feeds/facebook/fb_cl_feed.xml" target="_blank">https://padre.scalpers.es/feeds/facebook/fb_cl_feed.xml</a></td>
							<td>
								<?php
									$ts = Storage::disk('feeds')->lastModified('/facebook/fb_cl_feed.xml');
									$date = new DateTime("@$ts");
									echo $date->format('Y-m-d H:i:s') . "\n";
								?>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="mt-4" style="font-weight: 400; font-size: .8em;">
					<p>Los feeds se regeneran automáticamente entre las 3:00 AM y las 3:10 AM (GMT) cada día. Por defecto se ignoran los productos sin stock.</p>
				</div>
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
        title: "Feeds",
        buttons: {
            secondary: [feedsButton, rrhhGroupButton],
        },
    });
	</script>

	@include('includes.scripts')
	<script>
		$('#fb-feed button').click(function() {
			var merchant = $(this).attr("data-merchant");
			var store = $(this).attr("data-store");

			var options = {};
			options.withoutStock = $('#fb-feed-options input[name="option[without-stock]"]').is(':checked') ? "true" : "false";

			var endpoint = merchant + '/generate/' + store + '?options=' + btoa(JSON.stringify(options));
			var waitAlert = '#fb-feed > #feed-alerts div[data-store="' + store + '"]';
			var storeButton = '#fb-feed .select-buttons button[data-store="' + store + '"]';

			if (store == 'all') {
				$(storeButton).removeClass('btn-dark').addClass('btn-warning');
				$('#fb-feed > #feed-alerts').empty();
				$('#fb-feed > #feed-alerts').append('<div class="alert alert-warning" role="alert" data-store="' + store + '">Función no disponible por el momento.</div>');
				return false;
			}

			$('#fb-feed button').prop("disabled", true);
			$('#fb-feed > #feed-alerts').empty();
			$('#fb-feed > #feed-alerts').append('<div class="alert alert-dark" role="alert" data-store="' + store + '">Regenerando Feed Facebook ' + store.toUpperCase() + '...</div>');
			$.getJSON(endpoint, {}, function(data, textStatus, jqXHR) {
					if (data.status == "COMPLETED") {
						$('#fb-feed > #feed-alerts').append('<div class="alert alert-primary" role="alert"><p><b>¡Feed Facebook ' + store.toUpperCase() + ' regenerado!</b></p><p><b>Productos:</b> ' + data.objectCount + '</p><p><b>URL:</b> <a href="' + data.feedUrl + '" target="_blank">' + data.feedUrl + '</a></p><div class="debug-info"><p><b>ProcessTime:</b> ' + data.processTime + '</p><p><b>PeakMemoryUsage:</b> ' + data.peakMemoryUsage + '</p></div><a href="#" class="badge badge-info trigger-debug" aria-expanded="false">Debug</span></a>');
					}
					$('#fb-feed .trigger-debug').click(function() {
						if ($(this).attr('aria-expanded') == "false") {
							$(this).text('Ocultar').siblings('div.debug-info').show();
							$(this).attr('aria-expanded', 'true');
						} else {
							$(this).text('Debug').siblings('div.debug-info').hide();
							$(this).attr('aria-expanded', 'false');
						}
					});
				})
				.done(function() {
					$(waitAlert).remove();
					$(storeButton).removeClass('btn-dark').removeClass('btn-danger').addClass('btn-success');
					$('#fb-feed button').prop("disabled", false);
				})
				.fail(function(jqxhr, settings, ex) {
					var data = jqxhr.responseJSON;
					console.log(data);
					$(waitAlert).remove();
					$(storeButton).removeClass('btn-dark').removeClass('btn-success').addClass('btn-danger');
					$('#fb-feed > #feed-alerts').append('<div class="alert alert-danger" role="alert" data-store="' + store + '">Error de Servidor. Inténtalo más tarde. Si persiste, envía error y StackTrace a emelero@scalperscompany.com<hr class="my-2"><b>Message:</b> ' + data.message + '<br><b>Exception:</b> ' + data.exception + '.<br><b>File:</b> ' + data.file + '.<br><b>Line:</b> ' + data.line + '<br><b>StackTrace:</b> <a href="#" class="trigger-stack badge badge-info" aria-expanded="false">Mostrar</a><br><pre class="stack-trace">' + JSON.stringify(data.trace, undefined, 2) + '</pre></div>');
					$('#fb-feed .trigger-stack').click(function() {
						if ($(this).attr('aria-expanded') == "false") {
							$(this).text('Ocultar').siblings('pre.stack-trace').show();
							$(this).attr('aria-expanded', 'true');
						} else {
							$(this).text('Mostrar').siblings('pre.stack-trace').hide();
							$(this).attr('aria-expanded', 'false');
						}
					});
					$('#fb-feed button').prop("disabled", false);
				});
		});

	</script>
</body>

</html>
