<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <meta name="theme-color" content="#0070f4">
  <!-- Favicon -->
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <!-- Apple -->
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <!-- PWA -->
  <link rel="manifest" href="/site.webmanifest">
  <meta name="theme-color" content="#ffffff">
  <!-- Safari -->
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#000000">
  <!-- Microsoft -->
  <meta name="msapplication-config" content="/browserconfig.xml">

  <title>{{ $tenant->name ?? 'Cửa Hàng' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            kv: {
              blue: 'var(--theme-color, #0070f4)',
              blue_hover: 'var(--theme-color-hover, #005bb5)',
              green: '#4caf50',
              green_hover: '#3d8c40',
              bg: '#f0f2f5',
              border: '#dee2e6',
              text: '#333333',
              text_dim: '#666666'
            }
          },
          fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --theme-color: {{ $tenant->theme_color ?? '#0070f4' }};
      --theme-color-hover: {{ $tenant->theme_color ?? '#005bb5' }};
    }
    body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #333333; }
    .toast { position: fixed; top: 20px; right: 20px; z-index: 9999; transition: all .3s; transform: translateY(-50px); opacity: 0; pointer-events: none; }
    .toast.show { transform: none; opacity: 1; pointer-events: auto; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
  </style>
</head>
<body class="flex min-h-screen text-base">

<!-- Mobile Topbar -->
<!--<div class="md:hidden fixed top-0 inset-x-0 z-40 bg-[var(--theme-color)] text-white flex items-center justify-between px-4 h-[50px] shadow-md" style="background-color: var(--theme-color)">
  <div class="font-bold text-[15px] truncate">☕ {{ __('ui.customer.menu_title') }} - {{ $tenant->name ?? __('ui.customer.default_shop_name') }}</div>
</div>-->

<!-- Content Wrapper -->
<div id="offline-banner" class="hidden fixed top-0 inset-x-0 z-[99999] bg-orange-500 text-white text-center py-2 text-[13px] font-bold shadow-md flex items-center justify-center gap-2">
  <i data-feather="wifi-off" class="w-4 h-4"></i> Đang ngoại tuyến. Đơn hàng sẽ được lưu tạm trên máy.
</div>

<div class="@yield('container_class', 'mx-auto max-w-5xl') flex-1 flex flex-col min-w-0">
  <main class="flex-1 @yield('main_class', 'p-3 md:p-5') mt-[50px] md:mt-0">
    @yield('content')
  </main>
</div>

<!-- Global Toast -->
<div class="toast" id="toast">
  <div class="bg-gray-800 text-white font-medium text-[14px] px-5 py-3 rounded shadow-lg flex items-center gap-2" id="toast-msg"></div>
</div>

<script>
  feather.replace(); // Initialize icons
  function showToast(msg, type='success') {
    const t = document.getElementById('toast');
    const m = document.getElementById('toast-msg');
    let icon = type === 'error' ? '<i data-feather="alert-circle" class="w-5 h-5 text-red-400"></i>' : '<i data-feather="check-circle" class="w-5 h-5 text-green-400"></i>';
    m.innerHTML = icon + ' <span>' + msg + '</span>';
    feather.replace();
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
  }
  function fmtMoney(n) { return new Intl.NumberFormat('{{ app()->getLocale() == 'vi' ? 'vi-VN' : 'en-US' }}').format(n); }
</script>
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').then(reg => {
        console.log('PWA Service Worker registered');
      }).catch(err => {
        console.log('PWA SW registration failed: ', err);
      });
    });
  }

  // Offline handler
  window.addEventListener('online', () => {
      document.getElementById('offline-banner').classList.add('hidden');
  });
  window.addEventListener('offline', () => {
      document.getElementById('offline-banner').classList.remove('hidden');
  });
  if(!navigator.onLine) {
      document.getElementById('offline-banner').classList.remove('hidden');
  }
</script>
@stack('scripts')
</body>
</html>
