Hello {{ $client->full_name }}, welcome to {{ $tenant->name }}. Below, your box information: <br>
Box number: <b>{{ $box_code }}{{ $client->id }}</b> <br><br>

{{ __('This is the address you should use when making your purchases:') }} <br>
<h3>For Aerial Shipments:<h3> <br>
{{ $client->first_name }} {{ $box_code }}{{ $client->id }} {{ $client->last_name }} <br>
{{ $air->address }} <br>
{{ $air->telephones }} <br> <br>

<h3>For Maritime Shipments:<h3> <br>
{{ $client->first_name }} {{ $box_code }}{{ $client->id }} {{ $client->last_name }} <br>
{{ $maritime->address }} <br>
{{ $maritime->telephones }} <br><br>

Remember your purchases must always contain your box <b>{{ $box_code }}{{ $client->id }}</b> number.