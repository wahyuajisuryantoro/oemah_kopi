<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
            <span class="app-brand-logo demo">
               <img src="{{ asset('logo-50.png') }}" class="brand-logo">
            </span>
            <span class="app-brand-text demo menu-text fw-semibold ms-2">Oemah Kopi</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M8.47365 11.7183C8.11707 12.0749 8.11707 12.6531 8.47365 13.0097L12.071 16.607C12.4615 16.9975 12.4615 17.6305 12.071 18.021C11.6805 18.4115 11.0475 18.4115 10.657 18.021L5.83009 13.1941C5.37164 12.7356 5.37164 11.9924 5.83009 11.5339L10.657 6.707C11.0475 6.31653 11.6805 6.31653 12.071 6.707C12.4615 7.09747 12.4615 7.73053 12.071 8.121L8.47365 11.7183Z"
                    fill-opacity="0.9" />
                <path
                    d="M14.3584 11.8336C14.0654 12.1266 14.0654 12.6014 14.3584 12.8944L18.071 16.607C18.4615 16.9975 18.4615 17.6305 18.071 18.021C17.6805 18.4115 17.0475 18.4115 16.657 18.021L11.6819 13.0459C11.3053 12.6693 11.3053 12.0587 11.6819 11.6821L16.657 6.707C17.0475 6.31653 17.6805 6.31653 18.071 6.707C18.4615 7.09747 18.4615 7.73053 18.071 8.121L14.3584 11.8336Z"
                    fill-opacity="0.4" />
            </svg>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @auth
            @if (auth()->user()->role === 'kasir')
                <!-- Menu untuk Kasir -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text">Menu</span>
                </li>
                <li class="menu-item">
                    <a href="{{ route('kasir.dashboard') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-calculator-line"></i>
                        <div>Daftar Menu</div>
                    </a>
                </li>

                <li class="menu-header mt-3">
                    <span class="menu-header-text">Manajemen Operasional</span>
                </li>
                <li class="menu-item">
                    <a href="{{ route('list-pesanan.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                        <div>Pesanan</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('meja.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-table-line"></i>
                        <div>Manajemen Meja</div>
                    </a>
                </li>
            @elseif(auth()->user()->role === 'dapur')
                <!-- Menu untuk Dapur -->
                <li class="menu-item">
                    <a href="{{ route('dapur.dashboard') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-dashboard-line"></i>
                        <div>Dashboard Dapur</div>
                    </a>
                </li>
            @endif
        @endauth
    </ul>
</aside>
