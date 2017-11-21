<div class="flashdance" v-cloak>
    @if ($license_issue)
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <p>You are using unlicensed software. <a href="{{ route('licensing') }}">More details</a></p>
    </div>
    @endif
</div>

@if (count($errors) > 0)
    <div class="page-wrapper">
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
