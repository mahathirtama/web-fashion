<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Menu</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('reports*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-pie-chart"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('pos*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                    <i class="bi bi-cart-plus"></i>
                    POS
                </a>
            </li>
            @if(session('email') !== 'kasir@example.com')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('inventory*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                    <i class="bi bi-boxes"></i>
                    Inventory
                </a>
            </li>
             @endif

            
            {{-- LOGIC: Cek session email --}}
            @if(session('email') !== 'kasir@example.com')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <i class="bi bi-receipt"></i>
                    Invoices
                </a>
            </li>
            @endif

        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Account</span>
        </h6>
        <ul class="nav flex-column mb-2">
            
            {{-- LOGIC: Cek session email --}}
            @if(session('email') !== 'kasir@example.com')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-person-gear"></i>
                    User Management
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}">
                    <i class="bi bi-box-arrow-right"></i>
                    Sign out
                </a>
            </li>
        </ul>
    </div>
</nav>