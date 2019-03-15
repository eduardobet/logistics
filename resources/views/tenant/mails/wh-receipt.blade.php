<table align='center' style='text-align:center'>
    <tr>
        <td align='center' style='text-align:center'>
            <p>{{ __('Hello', [], $lang) }} {{ $client->full_name }} / {{ $box }}</p>
            <p>{!! __("Please check your warehouse receipt in attachment. If you can't see the attachment, please click the following link:", [], $lang) !!}</p>
            <a href="{{ $path }}">{{ $path }}</a>
        </td>
  </tr>
</table>