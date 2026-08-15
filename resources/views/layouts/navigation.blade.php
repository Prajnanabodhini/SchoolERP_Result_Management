@php
$menus = include app_path('Helpers/MenuConfig.php');
@endphp

@if(auth()->check())

<nav style="background:#1E3A8A;padding:8px 12px;">

<style>

.menu-wrapper{
    display:flex;
    align-items:center;
    width:100%;
}

/* .mobile-menu-btn{
    display:block;
} */

.menu-bar{
    display:flex;
    align-items:center;
    gap:6px;
}

.menu-btn{
    background:#2563EB;
    color:white !important;
    padding:8px 12px;
    border-radius:4px;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    border:none;
    cursor:pointer;

    display:flex;
    align-items:center;
    justify-content:center;
}

.menu-btn:hover{
    background:#1D4ED8;
}

.dropdown{
    position:relative;
}

.dropdown-content{
    display:none;
    position:absolute;
    top:100%;
    left:0;
    min-width:250px;
    background:white;
    border:1px solid #D1D5DB;
    border-radius:4px;
    box-shadow:0 4px 12px rgba(0,0,0,.15);
    z-index:9999;
}

.dropdown-content a{
    display:block;
    padding:8px 12px;
    color:#111827;
    text-decoration:none;
    border-bottom:1px solid #E5E7EB;
    font-size:12px;
}

.dropdown-content a:last-child{
    border-bottom:none;
}

.dropdown-content a:hover{
    background:#FEF3C7;
}

.show-menu{
    display:block !important;
}

.user-panel{
    margin-left:auto;
    display:flex;
    align-items:center;
    gap:6px;
}

.user-btn{
    padding:8px 12px;
    border-radius:4px;
    color:white;
    text-decoration:none;
    font-size:12px;
    font-weight:600;
}

.profile-btn{
    background:#16A34A;
}

.logout-btn{
    background:#DC2626;
    border:none;
    cursor:pointer;
}

/* MOBILE */

@media(max-width:768px){

    .mobile-menu-btn{
        display:block;
        width:100%;
        background:#2563EB;
        color:white;
        border:none;
        padding:10px;
        border-radius:4px;
        font-size:16px;
        font-weight:bold;
        margin-bottom:8px;
    }

    .menu-wrapper{
        flex-direction:column;
        align-items:stretch;
    }

    .menu-bar{
        display:none;
        flex-direction:column;
        width:100%;
        gap:4px;
    }

    .menu-bar.show{
        display:flex !important;
    }

    .dropdown{
        width:100%;
    }

    .menu-btn{
        width:100%;
        justify-content:flex-start;
    }

    .dropdown-content{
        position:relative;
        width:100%;
        min-width:100%;
        box-shadow:none;
    }

    .user-panel{
        margin-left:0;
        margin-top:10px;
        flex-wrap:wrap;
    }
}

</style>

<div class="menu-wrapper">

<button
    type="button"
    class="mobile-menu-btn"
    onclick="toggleMobileMenu()">
    
</button>
<div class="menu-bar" id="mainMenu">

{{-- Dashboard --}}

@if(canView('Dashboard'))

<a href="{{ route('dashboard') }}"
   class="menu-btn">
    Dashboard
</a>

@endif

{{-- Other Menus --}}

@foreach($menus as $group => $items)

    @if($group != 'Dashboard')

        @if(canAccessAny($items))

        <div class="dropdown">

            <button
                type="button"
                class="menu-btn"
                onclick="toggleMenu('{{ \Illuminate\Support\Str::slug($group) }}')">

                {{ $group }} ▼

            </button>

            <div
                id="{{ \Illuminate\Support\Str::slug($group) }}"
                class="dropdown-content">

                @foreach($items as $item)

                    @if(canView($item['name']))

                    <a href="
                    {{
                        $item['route'] == '#'
                        ? '#'
                        : route($item['route'])
                    }}
                    ">
                        {{ $item['name'] }}
                    </a>

                    @endif

                @endforeach

            </div>

        </div>

        @endif

    @endif

@endforeach

</div>

<div class="user-panel">

<span style="color:white;font-size:13px;font-weight:bold;">
    Welcome, {{ auth()->user()->name }}
</span>

<a href="{{ route('profile.edit') }}"
   class="user-btn profile-btn">
    Profile
</a>

<form method="POST"
      action="{{ route('logout') }}"
      style="margin:0;">
    @csrf

    <button
        type="submit"
        class="user-btn logout-btn">
        Logout
    </button>
</form>

</div>

</div>

<script>

function toggleMenu(menuId)
{
    document
        .querySelectorAll('.dropdown-content')
        .forEach(function(menu){

            if(menu.id !== menuId)
            {
                menu.classList.remove('show-menu');
            }

        });

    let current =
        document.getElementById(menuId);

    if(current)
    {
        current.classList.toggle('show-menu');
    }
}

document.addEventListener(
'click',
function(event){

    if(!event.target.closest('.dropdown'))
    {
        document
            .querySelectorAll('.dropdown-content')
            .forEach(function(menu){

                menu.classList.remove('show-menu');

            });
    }

});

function toggleMobileMenu()
{
    document
        .getElementById('mainMenu')
        .classList
        .toggle('show');
}

window.addEventListener(
'resize',
function(){

    let menu =
        document.getElementById('mainMenu');

    if(window.innerWidth > 768)
    {
        menu.style.display = 'flex';
    }
    else
    {
        menu.style.display = '';
    }

});

</script>

</nav>

@endif