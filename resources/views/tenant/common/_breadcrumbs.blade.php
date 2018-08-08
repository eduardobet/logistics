@if ($breadcrumbs->count())
    <ol class="breadcrumb slim-breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($breadcrumb->url() && $loop->remaining)
                <li class="breadcrumb-item"><a href="{{ $breadcrumb->url() }}">{{ $breadcrumb->title() }}</a></li>
            @else
                <li class="breadcrumb-item active">{{ $breadcrumb->title() }}</li>
            @endif
        @endforeach
    </ol>
@endif