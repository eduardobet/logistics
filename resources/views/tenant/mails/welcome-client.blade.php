@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ __('Welcome', [], $lang) }} {{ $client->full_name }}
        @endcomponent
@endslot

{{ __('Hello :who welcome to :what', ['who' => $client->full_name, 'what' => $tenant->name ] ) }}. {{ __('Below, your box information') }}: <br>
{{ __('Box number') }}: <b>{{ $box_code }}{{ $client->id }}</b> <br><br>

{{ __('This is the address you should use when making your purchases:') }} <br>

<h3>{{ __('For Aerial Shipments') }}:</h3>
{{ $client->first_name }} {{ $box_code }}{{ $client->id }} {{ $client->last_name }} <br>
{{ $air->address }} <br>
{{ $air->telephones }} <br> <br>

<h3>{{ __('For Maritime Shipments') }}:</h3>
{{ $client->first_name }} {{ $box_code }}{{ $client->id }} {{ $client->last_name }} <br>
{{ $maritime->address }} <br>
{{ $maritime->telephones }} <br><br><br>

{{ __('Remember your purchases must always contain your box') }}: <b>{{ $box_code }}{{ $client->id }}</b>

@isset($subcopy)
    @slot('subcopy')
        @component('mail::subcopy')
            {!! $subcopy !!}
        @endcomponent
    @endslot
@endisset

@slot('footer')
    @component('mail::footer')
        &copy; {{ date('Y') }} {{ config('app.name') }}
    @endcomponent
@endslot

@endcomponent