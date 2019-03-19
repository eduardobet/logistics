<div class="slim-navbar" style="background-color: #1b84e7">
      <div class="container">
        <ul class="nav">
          
          @if (!$user->isClient())
          <li class="nav-item with-sub {{ active(['tenant.client.create', 'tenant.client.list','tenant.client.edit']) }}">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-person-outline"></i>
              <span>{{ __('Clients') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                @can('create-client')
                  <li><a href="{{ route('tenant.client.create', $tenant->domain) }}">{{ __('New client') }}</a></li>
                @endcan

                @can('show-client')
                  <li><a href="{{ route('tenant.client.list', $tenant->domain) }}">{{ __('Client list') }}</a></li>
                @endcan
              </ul>
            </div><!-- dropdown-menu -->
          </li>
          @endif

          <li class="nav-item with-sub {{ active(['tenant.warehouse.create', 'tenant.warehouse.list','tenant.warehouse.edit', 'tenant.warehouse.cargo-entry.create', 'tenant.warehouse.cargo-entry.edit', 'tenant.warehouse.cargo-entry.list', 'tenant.misidentified-package.index',]) }}">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-box-outline"></i>
              <span>{{ __('Warehouse' )}}</span>
            </a>
            <div class="sub-item">
              <ul>
                @can('create-warehouse')
                  <li><a href="{{ route('tenant.warehouse.create', $tenant->domain) }}">{{ __('New warehouse') }}</a></li>
                @endcan

                @can('show-warehouse')  
                  <li><a href="{{ route('tenant.warehouse.list', $tenant->domain) }}">{{ __('Warehouse list') }}</a></li>
                @endcan

                @can('create-warehouse')
                <li><a href="{{ route('tenant.warehouse.cargo-entry.create', $tenant->domain) }}">{{ __('Register Cargo entry') }}</a></li>
                <li><a href="{{ route('tenant.warehouse.cargo-entry.list', $tenant->domain) }}">{{ __('Cargo entries') }}</a></li>
                <li><a  href="{{ route('tenant.misidentified-package.create', $tenant->domain) }}">{{ __('Create misidentified package') }}</a></li>
                <li><a  href="{{ route('tenant.misidentified-package.index', $tenant->domain) }}">{{ __('Misidentified packages') }}</a></li>
                @endcan
              </ul>
            </div><!-- dropdown-menu -->
          </li>

          <li class="nav-item with-sub {{ active(['tenant.invoice.list','tenant.invoice.create', 'tenant.invoice.edit']) }}">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-calculator-outline"></i>
              <span>{{ __('Invoices') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                @can('create-invoice')
                  <li><a href="{{ route('tenant.invoice.create', [$tenant->domain, 'branch_id' => $branch->id, ]) }}">{{ __('New invoice') }}</a></li>
                @endcan
                
                @can('show-invoice')
                <li><a href="{{ route('tenant.invoice.list', $tenant->domain) }}">{{ __('Invoices list') }}</a></li>
                @endcan
              </ul>
            </div><!-- dropdown-menu -->
          </li>

          @if (!$user->isClient())
          <li class="nav-item with-sub {{ active(['tenant.payment.*', 'tenant.income.*']) }}">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-paper-outline"></i>
              <span>{{ __('Accounting') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                
                @can('show-payment')
                <li><a href="{{ route('tenant.payment.list', [$tenant->domain, 'branch_id' => $branch->id, ])  }}">{{ __('Payments') }}</a></li>
                <li><a href="{{ route('tenant.income.list', [$tenant->domain, 'branch_id' => $branch->id, ])  }}">{{ __('Incomes') }}</a></li>
                @endcan
                <!--<li><a href="#">{{ __('New expense') }}</a></li>
                <li><a href="#">{{ __('Expense list') }}</a></li>
                <li><a href="#">{{ __('Petty cash') }}</a></li>-->
              </ul>
            </div><!-- dropdown-menu -->
          </li>
          @endif

          @if (!$user->isClient())
          <li class="nav-item with-sub {{ active(['tenant.admin.branch.list','tenant.admin.branch.create', 'tenant.admin.branch.edit', 'tenant.admin.employee.list','tenant.admin.employee.create', 'tenant.admin.employee.edit', 'tenant.admin.position.list','tenant.admin.position.create', 'tenant.admin.position.edit', 'tenant.admin.company.edit']) }}">
            <a class="nav-link" href="#">
              <i class="icon ion-ios-gear-outline"></i>
              <span>{{ __('System') }}</span>
            </a>
            <div class="sub-item">
              <ul>
                @if ($user->isSuperAdmin())
                <li><a href="{{ route('tenant.admin.company.edit', $tenant->domain) }}">{{ __('Configuration') }}</a></li>
                <li><a href="{{ route('tenant.admin.branch.list', $tenant->domain) }}">{{ __('Branches') }}</a></li>
                <li><a href="{{ route('tenant.admin.position.list', $tenant->domain) }}">{{ __('Positions') }}</a></li>
                <li><a href="{{ route('tenant.admin.employee.list', $tenant->domain) }}">{{ __('Employees') }}</a></li>
                @endif

                @can('show-mailer')
                  <li><a href="{{ route('tenant.mailer.list', $tenant->domain) }}">{{ __('Mailers') }}</a></li>
                @endcan
              </ul>
            </div><!-- dropdown-menu -->
          </li>
          @endif

        </ul>
      </div><!-- container -->
</div>