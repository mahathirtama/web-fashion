<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Menu</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                {{-- 
                  Kita asumsikan route dashboard adalah 'dashboard.index' dan URL-nya '/' atau '/dashboard'.
                  Request::is('/') akan aktif untuk root.
                  Request::is('dashboard*') akan aktif untuk /dashboard, /dashboard/stats, dll.
                --}}
                <li class="nav-item">
                <a class="nav-link {{ Request::is('reports*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-pie-chart"></i>
                    Dashboard
                </a>
            </li>
            </li>
            <li class="nav-item">
                {{-- Request::is('pos*') akan aktif untuk /pos, /pos/create, dll. --}}
                <a class="nav-link {{ Request::is('pos*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                    <i class="bi bi-cart-plus"></i>
                    POS
                </a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link {{ Request::is('purchasing*') ? 'active' : '' }}" href="{{ route('purchasing.index') }}">
                    <i class="bi bi-truck"></i>
                    Purchasing
                </a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link {{ Request::is('inventory*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                    <i class="bi bi-boxes"></i>
                    Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <i class="bi bi-receipt"></i>
                    Invoices
                </a>
            </li>
           
        
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Account</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-person-gear"></i>
                    User Management
                </a>
            </li>
            <li class="nav-item">
                {{-- Anda perlu membuat route untuk logout --}}
                <a class="nav-link" href="#">
                    <i class="bi bi-box-arrow-right"></i>
                    Sign out
                </a>
            </li>
        </ul>
    </div>
</nav>