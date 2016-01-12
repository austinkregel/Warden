@extends(config('kregel.warden.views.base-layout'))

@section('errors')
	@include('warden::shared.errors')
@stop

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				@include('warden::shared.menu')
			</div>
			<div class="col-md-8">
				<div class="panel panel-default ">
					<div class="panel-heading">
						<h3>You're upserting a {{ ucwords($model_name) }}</h3>
					</div>
					<div class="panel-body">
						<div id="page">
							{!! $form !!}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@stop