@if (!$errors->any())
	@if (isset($info))
		<div class="notification{{ isset($info_class) ? " ".$info_class : null }}">
			<b>{!! $info !!}</b>
		</div>
	@endif

	@if ($status = Session::get('status') )
	<div class="notification is-success">
		<button class="delete" onclick="((this).parentNode.remove())"></button>
		{{ $status }}
	</div>
	@endif

@else
    <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <ul>
			@foreach ($errors->all() as $error)
				<li>{!! $error !!}</li>
			@endforeach
		</ul>
    </div>
@endif