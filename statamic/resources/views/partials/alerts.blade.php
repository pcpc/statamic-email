<div class="flashdance" v-cloak>
	@if ($license_issue)
	<div class="alert alert-danger alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <p>You are using unlicensed software. <a href="{{ route('licensing') }}">More details</a></p>
	</div>
	@endif

	@if ($errors->count())
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			@foreach($errors->all() as $error){{ $error }}@endforeach
		</div>
	@endif

    <div class="alert alert-success alert-dismissible" role="alert" v-if="flashSuccess" v-cloak>
	  <button type="button" class="close" aria-label="Close" @click="flashSuccess = null"><span aria-hidden="true">&times;</span></button>
	  @{{ flashSuccess }}
	</div>

    <div class="alert alert-danger alert-dismissible" role="alert" v-if="flashError" v-cloak>
	  <button type="button" class="close" aria-label="Close" @click="flashError = null"><span aria-hidden="true">&times;</span></button>
	  @{{ flashError }}
	</div>

</div>
