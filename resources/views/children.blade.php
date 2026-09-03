@extends('layouts.basic')

@section('content')
<section class="public-page-shell">
	<div class="public-container public-container-wide">
		<div class="modal fade" id="registrationRulesModal" tabindex="-1" role="dialog" aria-labelledby="registrationRulesTitle" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="registrationRulesTitle">რეგისტრაციის წესები</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						{!! nl2br(e($registrationText['rules'])) !!}
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">დახურვა</button>
					</div>
				</div>
			</div>
		</div>
		<article class="public-article">
						<header class="public-article-header">
								@if(!empty($registrationText['subtitle']))
									<h1>{{ $registrationText['subtitle'] }}</h1>
								@endif
								@if(!empty($registrationText['description']))
									<p>{{ $registrationText['description'] }}</p>
								@endif
						</header>
			<div class="public-form">
				<div class="public-card">
					<div id="children-form" bla="19"></div>
				</div>
			</div>
		</article>
	</div>
</section>
@endsection
