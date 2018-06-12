<div class="slim-navbar">
      <div class="container">
        <ul class="nav">

          <li class="nav-item with-sub {{ active(['tenant.admin.dashboard', 'tenant.client.create', 'tenant.client.list','tenant.client.edit']) }}">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-person-outline"></i>
              <span>{{ __('Clients') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                <li><a href="{{ route('tenant.client.create') }}">{{ __('New client') }}</a></li>
                <li><a href="#!">{{ __('Search client') }}</a></li>
                <li><a href="#!">{{ __('Client list') }}</a></li>
              </ul>
            </div><!-- dropdown-menu -->
          </li>

          <li class="nav-item with-sub">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-box-outline"></i>
              <span>{{ __('Warehouse' )}}</span>
            </a>
            <div class="sub-item">
              <ul>
                <li><a href="#">{{ __('New warehouse') }}</a></li>
                <li><a href="#">{{ __('Search warehouse') }}</a></li>
                <li><a href="#">{{ __('Bill package') }}</a></li>
                <li><a href="#">{{ __('Order package') }}</a></li>
              </ul>
            </div><!-- dropdown-menu -->
          </li>

          <li class="nav-item with-sub">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-calculator-outline"></i>
              <span>{{ __('Invoices') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                <li><a href="#">{{ __('New invoice') }}</a></li>
                <li><a href="#">{{ __('Search invoice') }}</a></li>
                <li><a href="#">{{ __('New payment') }}</a></li>
                <li><a href="#">{{ __('Pending invoices') }}</a></li>
              </ul>
            </div><!-- dropdown-menu -->
          </li>

          <li class="nav-item with-sub">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-paper-outline"></i>
              <span>{{ __('Accounting') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                <li><a href="#">{{ __('Accounting') }}</a></li>
                <li><a href="#">{{ __('New expense') }}</a></li>
                <li><a href="#">{{ __('Expense list') }}</a></li>
                <li><a href="#">{{ __('Petty cash') }}</a></li>
              </ul>
            </div><!-- dropdown-menu -->
          </li>

          <li class="nav-item with-sub {{ active(['tenant.admin.branch.list','tenant.admin.branch.create', 'tenant.admin.branch.edit', 'tenant.admin.employee.list','tenant.admin.employee.create', 'tenant.admin.employee.edit']) }}">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-gear-outline"></i>
              <span>{{ __('System') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                <li><a href="#">{{ __('Configuration') }}</a></li>
                <li><a href="{{ route('tenant.admin.branch.list') }}">{{ __('Branches') }}</a></li>
                <li><a href="{{ route('tenant.admin.employee.list') }}">{{ __('Users') }}</a></li>
                <li><a href="#">{{ __('Activity') }}</a></li>
              </ul>
            </div><!-- dropdown-menu -->
          </li>

        </ul>
      </div><!-- container -->
</div>