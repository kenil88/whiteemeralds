@php
    $colorMode ??= 'light';
    $showUserMenu ??= false;
    $announcements = apply_filters('announcement_display_html', null);
    $currencies = collect();
    $hasCurrencies = false;
    if (is_plugin_active('ecommerce')) {
        $currencies = get_all_currencies();
        $hasCurrencies = $currencies->count() > 1;
    }
@endphp

<div
    @class(['p-relative z-index-11', 'tp-header-top-border' => $hasCurrencies || $announcements, 'tp-header-top-2' => $colorMode === 'light', 'tp-header-top black-bg' => $colorMode !== 'light'])
    style="background-color: {{ theme_option('header_top_background_color', $headerTopBackgroundColor) }}; color: {{ $headerTopTextColor }}"
>
    <div @class([$headerTopClass ?? null])>
        <div class="d-flex flex-wrap align-items-center justify-content-between">
            
            <div class="position-relative added_left" style="">
                @php
                if(get_application_currency_id() == 4){
            @endphp
                {!! $announcements !!}
                  @php
                }
            @endphp
            </div>
          
            <div>
                <div @class(['tp-header-top-right d-flex align-items-center justify-content-end', 'tp-header-top-black' => $colorMode === 'light'])>
                    <div class="tp-header-top-menu d-none d-lg-flex align-items-center justify-content-end">
                        {{-- {!! Theme::partial('language-switcher', ['type' => 'desktop']) !!}
                        @if ($hasCurrencies)
                            <div class="tp-header-top-menu-item tp-header-currency">
                                <span class="tp-header-currency-toggle" id="tp-header-currency-toggle">
                                    {{ get_application_currency()->title }}
                                    <x-core::icon name="ti ti-chevron-down" />
                                </span>
                                {!! Theme::partial('currency-switcher') !!}
                            </div>
                        @endif --}}
                        
                         

                        @if ($showUserMenu && is_plugin_active('ecommerce'))
                            @auth('customer')
                                <div class="tp-header-top-menu-item tp-header-setting">
                                    <span class="tp-header-setting-toggle" id="tp-header-setting-toggle">
                                        {{ auth('customer')->user()->name }}
                                        <x-core::icon name="ti ti-chevron-down" />
                                    </span>
                                    <ul>
                                        <li>
                                            <a href="{{ route('customer.overview') }}">{{ __('My Profile') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('customer.orders') }}">{{ __('Orders') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('customer.logout') }}">{{ __('Logout') }}</a>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <div class="tp-header-top-menu-item tp-header-setting">
                                    <a href="{{ route('customer.login') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 1a5.53 5.53 0 0 0-4.467 2.248.5.5 0 0 0 .416.752h8.102a.5.5 0 0 0 .416-.752A5.53 5.53 0 0 0 8 9z"/>
                                        </svg>
                                    {{ __('Login') }}</a>
                                </div>
                                {{-- <div class="tp-header-top-menu-item tp-header-setting">
                                    <a href="{{ route('customer.register') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 1a5.53 5.53 0 0 0-4.467 2.248.5.5 0 0 0 .416.752h8.102a.5.5 0 0 0 .416-.752A5.53 5.53 0 0 0 8 9z"/>
                                        </svg>
                                        {{ __('Register') }}
                                    </a>
                                </div> --}}
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
