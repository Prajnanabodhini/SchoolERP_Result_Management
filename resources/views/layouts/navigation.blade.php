@php
    $menus = include app_path('Helpers/MenuConfig.php');
@endphp

@if(auth()->check())

<nav class="erp-navbar">
    <div class="erp-navbar__inner">

        {{-- ============================================================
             MOBILE HAMBURGER
             ============================================================ --}}
        <button type="button"
                class="erp-navbar__hamburger"
                onclick="erpToggleMobileMenu()"
                aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        {{-- ============================================================
             LEFT: MENU ITEMS
             ============================================================ --}}
        <div class="erp-navbar__menu" id="erpMainMenu">

            {{-- Dashboard --}}
            @if(canView('Dashboard'))
                <a href="{{ route('dashboard') }}"
                   class="erp-navbar__item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    Dashboard
                </a>
            @endif

            {{-- Grouped menus --}}
            @foreach($menus as $group => $items)

                @if($group != 'Dashboard' && canAccessAny($items))

                    <div class="erp-navbar__dropdown">
                        <button type="button"
                                class="erp-navbar__item erp-navbar__item--dropdown"
                                onclick="erpToggleDropdown(event, 'dd-{{ \Illuminate\Support\Str::slug($group) }}')">
                            {{ $group }}
                            <svg class="erp-navbar__chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>

                        <div class="erp-navbar__dropdown-panel" id="dd-{{ \Illuminate\Support\Str::slug($group) }}">
                            @foreach($items as $item)
                                @if(canView($item['name']))
                                    <a href="{{ $item['route'] == '#' ? '#' : route($item['route']) }}"
                                       class="erp-navbar__dropdown-item {{ request()->routeIs($item['route']) ? 'is-active' : '' }}">
                                        {{ $item['name'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                @endif

            @endforeach

        </div>

        {{-- ============================================================
             RIGHT: USER PANEL
             ============================================================ --}}
        <div class="erp-navbar__user">

            <div class="erp-navbar__welcome">
                <span class="erp-navbar__welcome-label">Welcome</span>
                <span class="erp-navbar__welcome-name">{{ auth()->user()->name }}</span>
            </div>

            <a href="{{ route('profile.edit') }}" class="erp-navbar__btn erp-navbar__btn--profile">
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}" class="erp-navbar__logout-form">
                @csrf
                <button type="submit" class="erp-navbar__btn erp-navbar__btn--logout">
                    Logout
                </button>
            </form>

        </div>

    </div>
</nav>

<style>
/* =================================================================
   ERP NAVBAR — MODERN SINGLE ROW
   ================================================================= */

.erp-navbar {
    background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
    box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
    position: relative;
    z-index: 1000;
}

.erp-navbar__inner {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    min-height: 52px;
}

/* ---------------- HAMBURGER (mobile only) ---------------- */

.erp-navbar__hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, .1);
    border: 1px solid rgba(255, 255, 255, .2);
    border-radius: 6px;
    cursor: pointer;
    padding: 0 8px;
    flex-shrink: 0;
}
.erp-navbar__hamburger span {
    display: block;
    height: 2px;
    background: #fff;
    border-radius: 2px;
    transition: .25s ease;
}
.erp-navbar__hamburger:hover {
    background: rgba(255, 255, 255, .18);
}

/* ---------------- MENU BAR ---------------- */

.erp-navbar__menu {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: 1;
    min-width: 0;
}

.erp-navbar__item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 6px;
    background: transparent;
    color: #e0e7ff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s ease, color .15s ease;
    line-height: 1.2;
}

.erp-navbar__item:hover {
    background: rgba(255, 255, 255, .12);
    color: #ffffff;
}

.erp-navbar__item.is-active {
    background: rgba(255, 255, 255, .18);
    color: #ffffff;
}

.erp-navbar__chevron {
    transition: transform .2s ease;
    opacity: .8;
}
.erp-navbar__item--dropdown.is-open .erp-navbar__chevron {
    transform: rotate(180deg);
}

/* ---------------- DROPDOWN ---------------- */

.erp-navbar__dropdown {
    position: relative;
}

.erp-navbar__dropdown-panel {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    min-width: 240px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, .18);
    padding: 6px;
    display: none;
    opacity: 0;
    transform: translateY(-6px);
    transition: opacity .15s ease, transform .15s ease;
    max-height: 70vh;
    overflow-y: auto;
}

.erp-navbar__dropdown-panel.is-open {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.erp-navbar__dropdown-item {
    display: block;
    padding: 8px 12px;
    color: #1f2937;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    border-radius: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: background .12s ease, color .12s ease;
}

.erp-navbar__dropdown-item:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

.erp-navbar__dropdown-item.is-active {
    background: #dbeafe;
    color: #1e40af;
    font-weight: 600;
}

/* ---------------- USER PANEL ---------------- */

.erp-navbar__user {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    flex-shrink: 0;
}

.erp-navbar__welcome {
    display: flex;
    align-items: baseline;
    gap: 4px;
    color: #e0e7ff;
    font-size: 13px;
    padding: 0 6px;
    white-space: nowrap;
}
.erp-navbar__welcome-label {
    opacity: .75;
}
.erp-navbar__welcome-name {
    font-weight: 700;
    color: #ffffff;
}

.erp-navbar__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    padding: 0 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    color: #ffffff;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: filter .15s ease, transform .1s ease;
}
.erp-navbar__btn:hover {
    filter: brightness(1.1);
}
.erp-navbar__btn:active {
    transform: translateY(1px);
}

.erp-navbar__btn--profile {
    background: #16a34a;
}
.erp-navbar__btn--logout {
    background: #dc2626;
}

.erp-navbar__logout-form {
    margin: 0;
}

/* =================================================================
   RESPONSIVE — MOBILE / TABLET
   ================================================================= */

@media (max-width: 1024px) {

    .erp-navbar__welcome {
        display: none;
    }
}

@media (max-width: 900px) {

    .erp-navbar__hamburger {
        display: flex;
    }

    .erp-navbar__inner {
        flex-wrap: wrap;
    }

    .erp-navbar__menu {
        display: none;
        flex-direction: column;
        align-items: stretch;
        width: 100%;
        gap: 2px;
        order: 3;
        margin-top: 8px;
        padding: 8px 0 4px;
        border-top: 1px solid rgba(255, 255, 255, .15);
    }

    .erp-navbar__menu.is-open {
        display: flex;
    }

    .erp-navbar__item {
        width: 100%;
        justify-content: space-between;
        padding: 10px 14px;
        font-size: 14px;
    }

    .erp-navbar__dropdown {
        width: 100%;
    }

    .erp-navbar__dropdown-panel {
        position: static;
        width: 100%;
        min-width: 0;
        box-shadow: none;
        border: none;
        border-radius: 6px;
        background: rgba(255, 255, 255, .08);
        margin: 2px 0 6px;
        padding: 4px;
        display: none;
        opacity: 1;
        transform: none;
        max-height: none;
    }

    .erp-navbar__dropdown-panel.is-open {
        display: block;
    }

    .erp-navbar__dropdown-item {
        color: #e0e7ff;
        padding: 8px 12px;
        font-size: 13px;
    }

    .erp-navbar__dropdown-item:hover,
    .erp-navbar__dropdown-item.is-active {
        background: rgba(255, 255, 255, .15);
        color: #ffffff;
    }
}

@media (max-width: 600px) {

    .erp-navbar__inner {
        padding: 8px 10px;
    }

    .erp-navbar__btn {
        padding: 0 12px;
        font-size: 12px;
        height: 32px;
    }

    .erp-navbar__welcome-name {
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}
</style>

<script>
/* =================================================================
   NAVBAR — Dropdown + Mobile Menu Toggle
   ================================================================= */

function erpToggleDropdown(event, dropdownId) {
    event.preventDefault();
    event.stopPropagation();

    const target = document.getElementById(dropdownId);
    if (!target) return;

    const isCurrentlyOpen = target.classList.contains('is-open');

    /* Close ALL open dropdowns */
    document
        .querySelectorAll('.erp-navbar__dropdown-panel.is-open')
        .forEach(function (el) {
            el.classList.remove('is-open');
        });

    document
        .querySelectorAll('.erp-navbar__item--dropdown.is-open')
        .forEach(function (el) {
            el.classList.remove('is-open');
        });

    /* Toggle current */
    if (!isCurrentlyOpen) {
        target.classList.add('is-open');

        const btn = target.previousElementSibling;
        if (btn) btn.classList.add('is-open');
    }
}

function erpToggleMobileMenu() {
    const menu = document.getElementById('erpMainMenu');
    if (menu) menu.classList.toggle('is-open');
}

/* Close dropdowns on outside click */
document.addEventListener('click', function (event) {
    if (!event.target.closest('.erp-navbar__dropdown')) {
        document
            .querySelectorAll('.erp-navbar__dropdown-panel.is-open')
            .forEach(function (el) {
                el.classList.remove('is-open');
            });
        document
            .querySelectorAll('.erp-navbar__item--dropdown.is-open')
            .forEach(function (el) {
                el.classList.remove('is-open');
            });
    }
});

/* Close dropdowns on ESC */
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document
            .querySelectorAll('.erp-navbar__dropdown-panel.is-open')
            .forEach(function (el) {
                el.classList.remove('is-open');
            });
        document
            .querySelectorAll('.erp-navbar__item--dropdown.is-open')
            .forEach(function (el) {
                el.classList.remove('is-open');
            });
    }
});

/* Reset mobile menu state on resize */
let erpResizeTimer;
window.addEventListener('resize', function () {
    clearTimeout(erpResizeTimer);
    erpResizeTimer = setTimeout(function () {
        if (window.innerWidth > 900) {
            const menu = document.getElementById('erpMainMenu');
            if (menu) menu.classList.remove('is-open');
        }
    }, 150);
});
</script>

@endif