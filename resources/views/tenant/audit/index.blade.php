@extends('layouts.tenant')

@section('title')
  {{ __('Audits') }} | {{ config('app.name', '') }}
@endsection

@section('content')

@include('tenant.common._header', [])


<div class="slim-mainpanel">
     <div class="container">

        <div class="slim-pageheader">
          
            {{ Breadcrumbs::render() }}

            {{ $branch->name }}
          
        </div><!-- slim-pageheader -->

        <div class="section-wrapper pd-l-10 pd-r-10 pd-t-10 pd-b-10">
                <div id="accordion3" class="accordion-two" role="tablist">
                @forelse ($audits->groupBy('auditable_type') as $type => $groups)
                    
                    <div class="card">
                        <div class="card-header" role="tab" id="headingOne-{{str_slug($type)}}">
                            <a data-toggle="collapse" href="#{{str_slug($type)}}" aria-expanded="false" aria-controls="{{str_slug($type)}}" class="tx-gray-800 transition collapsed">
                            <label class="section-title">{{  __(title_case(snake_case(class_basename($type), ' '))) }}</label>
                            </a>
                        </div><!-- card-header -->

                        <div id="{{str_slug($type)}}" class="collapse" role="tabpanel" aria-labelledby="headingOne-{{str_slug($type)}}" data-parent="#accordion3">
                            <div class="card-body">
                                <ul class="list-group">

                                    @foreach ($groups as $audit)
                                        <li class="list-group-item">
                                            @lang('article.updated.metadata', array_except($audit->getMetadata() , ['user_permissions']))

                                            @foreach ($audit->getModified() as $attribute => $modified)
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    @lang('article.'.$audit->event.'.modified.'.$attribute, $modified)
                                                </li>
                                            </ul>
                                            @endforeach
                                        </li>
                                    @endforeach
                                </ul>
                             </div>
                           </div>
                     </div>    
                @empty
                <p>@lang('article.unavailable_audits')</p>
                @endforelse
            </div>
        </div>

     </div><!-- container -->

</div><!-- slim-mainpanel -->

@include('tenant.common._footer')

@endsection

@section('xtra_scripts')
@endsection