@extends('layouts.tenant')

@section('title')
  {{ __('Dashboard') }}  {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])

<div class="slim-mainpanel">

    <div class="container">

        <div class="slim-pageheader">
            {{ Breadcrumbs::render() }}
            <h6 class="slim-pagetitle"> {{ $branch->name }} </h6>
         </div><!-- slim-pageheader -->

         <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">
            {!! Form::model($client, ['route' => ['tenant.client.update', $tenant->domain, $client->id], 'method' => 'PATCH', ]) !!}
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <input type="hidden" name="branch_code" value="{{ $branch->code }}">
            @include('tenant.client._fields', [
                'departments' => $departments->toArray(),
                'zones' => $zones->toArray(),
                'mode' => 'edit',
            ])
            </form>
         </div>

         <div class="section-wrapper mg-t-15">
            <div class="mg-b-15">
                <label class="section-title">{{ __('Activity Log') }}</label>
            </div>
            <div class="col-lg-12">
                @if ($client->creator)
                    <p>{{ __('Created by') }} <b>{{ $client->creator->full_name }}</b> | <b>{{ $client->created_at->format('d/m/Y') }}</b> | {{ $client->created_at->format('g:i A') }} </p>
                @endif    
                @if ($client->editor)
                    <p>{{ __('Edited by') }} <b>{{ $client->editor->full_name }}</b> | <b>{{ $client->updated_at->format('d/m/Y') }}</b> | {{ $client->updated_at->format('g:i A') }} </p>
                @endif
                
            </div>

            <div class="col-lg-12">
                <ul>
                    @forelse ($client->audits as $audit)
                    <li>
                        @lang('client.updated.metadata',array_except( $audit->getMetadata(), ['user_permissions']))

                        @foreach ($audit->getModified() as $attribute => $modified)
                        <ul>
                            <li>
                                {!! __('client.'.$audit->event.'.modified.'.$attribute, $modified) !!} 
                            </li>
                        </ul>
                        @endforeach
                    </li>
                    @empty
                    <p>@lang('client.unavailable_audits')</p>
                    @endforelse
                </ul>
            </div>
          </div>

     </div> <!-- container -->     
</div> <!-- slim-mainpanel -->   

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
    @include('common._select2ize')
    @include('common._add_more', ['identifier' => "cl-{$client->id}", 'no_preserve' => true,]))
    @include('common._toogle-for-text')

    <script>
      $(function() {
          $("input[name='email']").blur(function() {
              if ($.trim(this.value)) this.value = this.value.toLowerCase();
          });

          $("#pay_volume").click(function(){
              var $self = $(this);
              var $tmpVP = $("#vol_price");
              if (!this.checked) {
                  $tmpVP.val('');
              } else {
                 $tmpVP.val($self.data('volprice')); 
              }
          });

          $("#pay_first_lbs_price").click(function(){
              var $self = $(this);
              var $tmpVP = $("#first_lbs_price");
              if (!this.checked) {
                  $tmpVP.val('');
              } else {
                 $tmpVP.val($self.data('firstlbsprice')); 
              }
          });
      });
    </script> 
@endsection