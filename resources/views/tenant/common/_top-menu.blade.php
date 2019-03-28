<div class="slim-header">
      <div class="container">
        <div class="slim-header-left">
            <?php $type = auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) ? 'admin' : 'employee'; ?>
          <h2 class="slim-logo">
              
              @if ($user->isClient())
              <a href="{{ route('tenant.warehouse.list', [$tenant->domain]) }}">{{ config('app.name') }}</a>
              @else
              <a href="{{ route("tenant.{$type}.dashboard", $tenant->domain) }}">{{ config('app.name', '') }}</a>
            @endif
          </h2>

          <div class="search-box">
            <input type="text" id="q" class="form-control" placeholder="{{ __('Search') }}"{{ $user->isClient() ? " disabled" : null }}>
            <button id="btn-search" class="btn btn-primary"{{ $user->isClient() ? " disabled" : null }}><i class="fa fa-search"></i></button>
          </div><!-- search-box -->
        </div><!-- slim-header-left -->
        <div class="slim-header-right">

            <!--
            <div class="dropdown">
                <a href="#!" class="header-notification">
                    <i class="icon ion-ios-bell-outline"></i>
                    <span class="indicator"></span>
                </a>
                <div class="dropdown-menu"></div>
            </div>
            -->

            <div class="dropdown">
                <a href="#" class="header-notification" data-toggle="dropdown">
                    <i class="icon fa fa-globe"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
              
                    <nav class="nav">
                        @foreach(localization()->getSupportedLocales() as $key => $lang)
                            @if($key == localization()->getCurrentLocale())
                                <a class="nav-link active">
                                {{$lang->native()}}
                                </a>
                            @else
                                <a class="nav-link" href="{{localization()->getLocalizedURL($key) }}">
                                {{$lang->native()}}
                                </a>
                            @endif
                        @endforeach
                    </nav>

                </div><!-- dropdown-menu -->
            </div><!-- dropdown-b -->

            <div class="dropdown dropdown-c">
                <a href="#" class="logged-user" data-toggle="dropdown">
                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="Avatar">
                    <span>{{ auth()->user()->full_name }}</span>
                    <i class="icon fa fa-angle-down"></i>
                </a>
                
                <div class="dropdown-menu dropdown-menu-right">
                    <nav class="nav">
                        @if (!$user->isClient())
                            <a href="{{ route('tenant.employee.profile.edit', $tenant->domain) }}" class="nav-link"><i class="icon fa fa-user"></i> {{ __('Profile') }} </a>
                            <a href="#" class="nav-link"><i class="icon fa fa-bolt"></i> {{ __('Actitity') }} </a>
                        @endif
                        <a href="#" onclick="document.getElementById('form-logout').submit()" class="nav-link"><i class="icon fa fa-power-off"></i> {{ __('Logout') }} </a>
                    </nav>
                </div><!-- dropdown-menu -->

            </div><!-- dropdown -->

        </div><!-- header-right -->
      </div><!-- container -->
</div>
<form id="form-logout" action="{{ route('tenant.auth.post.logout', $tenant->domain) }}" method="post" style="display: none">{!! csrf_field()  !!}</form>