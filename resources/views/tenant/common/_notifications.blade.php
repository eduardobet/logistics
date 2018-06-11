@if (!$errors->any())
	@if (isset($info))
		<div class="notification{{ isset($info_class) ? " ".$info_class : null }}">
			<b>{!! $info !!}</b>
		</div>
	@endif

	@if ($flashError = Session::get('flash_error') )
	<div class="notification is-danger">
		<button class="delete" onclick="((this).parentNode.remove())"></button>
		{{ $flashError }}
	</div>
	@endif

	@if ($flashLockError = Session::get('flash_lock_error') )
	<div class="notification is-danger">
		<button class="delete" onclick="((this).parentNode.remove())"></button>
		{{ $flashLockError }}
	</div>
	@endif

	@if ($flashSuccess = Session::get('flash_success') )
	<div class="notification is-success">
		<button class="delete" onclick="((this).parentNode.remove())"></button>
		{{ $flashSuccess }}
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