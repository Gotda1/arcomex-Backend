<ul class="navbar-nav ml-auto">
    <li class="nav-item">
        <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ route('home') }}">Nosotros</a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="#">Servicios</a>
    </li>

    @if(Auth::id())
        <li class="dropdown-submenu nav-item position-relative">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown"> 
                <img src="{{ asset('assets/images/ICONOdePERFIL.png') }}" alt="">
                <span class="nav-label">{{ $user->razon_social }}</span><span class="caret"></span>
            </a>
            <ul class="dropdown-menu border-0">
                <li>
                    <a class="nav-link {{ Request::is('profile') ? 'active' : '' }}" href="{{ route('dashboard') }}">Regresar a mi Perfil</a>
                </li>
                <li>
                    <a class="nav-link {{ Request::is('digital') ? 'active' : '' }}" href="{{ route('digital') }}">Adopción  digital</a>
                </li>
                <li>
                    <a class="nav-link {{ Request::is('financial') ? 'active' : '' }}" href="{{ route('financial') }}">Viabilidad financiera</a>
                </li>
                <li>
                    <a class="nav-link {{ Request::is('doLogout') ? 'active' : '' }}" href="{{ route('doLogout') }}">Cerrar Sesión</a>
                </li>
            </ul>
        </li>
    @else
        <li class="nav-item">
            <a class="nav-link {{ Request::is('login') ? 'active' : '' }}" href="{{ route('login') }}">Iniciar Sesión</a>
        </li>
    @endif
</ul>