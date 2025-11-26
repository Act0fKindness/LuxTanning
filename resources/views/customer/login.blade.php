@extends('layouts.app')

@push('head')
  <!-- Tailwind for this page (Cloudflare-safe) -->
  <script data-cfasync="false">
    window.tailwind = {
      config: {
        corePlugins: { preflight: false },
        important: '#gl-auth',
        theme: {
          extend: {
            fontFamily: {
              sans: ['-apple-system','BlinkMacSystemFont','SF Pro Text','Inter','Segoe UI','Roboto','Helvetica Neue','Arial','Noto Sans','Apple Color Emoji','Segoe UI Emoji']
            },
            boxShadow: { 'elev': '0 10px 30px rgba(0,0,0,.08), 0 2px 8px rgba(0,0,0,.05)' },
            keyframes: { sheen: { '0%':{ transform:'translateX(-100%)' }, '100%':{ transform:'translateX(200%)' } } },
            animation: { sheen: 'sheen 2.2s cubic-bezier(.4,0,.2,1) infinite' }
          }
        }
      }
    }
  </script>
  <script data-cfasync="false" src="https://cdn.tailwindcss.com"></script>
  <style>
    .view{transition:opacity .35s ease, transform .35s ease}.view.hidden{opacity:0;transform:translateY(8px);pointer-events:none}
    .btn-press:active{transform:translateY(1px)}.spinner{width:16px;height:16px;border-radius:9999px;border:2px solid rgba(0,0,0,.15);border-top-color:#000;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
  </style>
@endpush

@section('content')
<div id="gl-auth" class="min-h-screen text-[15px] leading-6 text-zinc-900 antialiased py-24">
  <script data-cfasync="false" src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
  <script data-cfasync="false" src="https://unpkg.com/gsap@3/dist/MorphSVGPlugin.min.js"></script>
  <script data-cfasync="false">
    try{
      if (window.gsap && window.MorphSVGPlugin){
        gsap.registerPlugin(MorphSVGPlugin);
        function animateMorph(pathElem, arr, duration=4, ease="power4.inOut"){ let i=0; pathElem.setAttribute('d', arr[i]); function next(){ const n=(i+1)%arr.length; gsap.to(pathElem,{duration, morphSVG:{shape:arr[n]}, ease, onComplete:next}); i=n; } next(); }
        const a1=[
          "M0,685.556C123.586,652.166,227.099,584.947,346.866,539.734C506.844,479.34,732.596,521.188,821.815,375.31C908.54,233.511,805.833,46.852,742.237,-106.718C689.412,-234.28,589.797,-329.105,490.415,-424.947C399.438,-512.683,297.905,-578.213,188.215,-641.002C45.332,-722.791,-84.937,-861.139,-249.067,-848.243C-417.342,-835.021,-544.692,-694.932,-662.605,-574.151C-786.478,-447.265,-915.729,-310.773,-944.581,-135.81C-973.647,40.45,-898.959,214.28,-818.96,374.006C-741.185,529.292,-654.956,704.797,-492.631,766.549C-333.876,826.943,-163.976,729.859,0,685.556",
          "M0,954.214C174.817,937.127,280.944,761.534,423.128,658.399C555.637,562.282,752.411,522.898,807.255,368.661C862.204,214.128,734.091,59.102,692.653,-99.589C656.695,-237.295,651.598,-379.514,579.8,-502.4C497.034,-644.059,409.788,-813.493,251.132,-855.278C92.545,-897.044,-64.033,-787.66,-208.476,-710.004C-329.538,-644.918,-427.213,-551.905,-512.962,-444.484C-595.705,-340.828,-668.866,-229.932,-695.422,-99.987C-723.024,35.077,-699.964,170.536,-665.948,304.128C-625.646,462.408,-596.722,632.565,-479.847,746.656C-352.565,870.906,-177.029,971.517,0,954.214",
          "M0,899.507C178.5,900.4,364.607,897.496,515.565,802.235C667.06,706.635,756.364,542.3,826.961,377.66C896.293,215.97,952.482,40.405,916.035,-131.706C880.544,-299.303,741.964,-416.247,631.17,-546.912C519.098,-679.084,436.917,-884.517,264.345,-900.278C85.876,-916.577,-14.919,-684.976,-182.405,-621.215C-329.756,-565.12,-531.436,-668.792,-641.152,-555.561C-749.826,-443.405,-654.066,-251.324,-688.429,-98.981C-727.105,72.481,-888.397,217.764,-854.49,390.233C-819.597,567.717,-667.64,704.488,-512.261,797.093C-359.588,888.086,-177.73,898.618,0,899.507",
          "M0,952.011C175.531,925.174,237.497,697.991,386.189,600.923C533.223,504.937,759.989,537.516,858.053,391.86C956.484,245.659,903.628,48.111,875.519,-125.881C847.825,-297.305,825.804,-487.458,697.156,-604.089C571.356,-718.138,382.94,-719.946,213.256,-726.282C73.814,-731.489,-48.403,-652.661,-187.072,-637.107C-366.544,-616.977,-577.95,-736.292,-717.67,-621.865C-853.043,-510.998,-832.169,-292.64,-820.892,-118.026C-811.044,34.455,-716.359,159.287,-658.098,300.543C-597.073,448.501,-584.092,619.28,-470.349,731.877C-344.784,856.177,-174.654,978.713,0,952.011",
          "M0,754.416C167.133,771.997,339.22,834.338,493.757,768.301C657.121,698.492,806.158,560.994,853.406,389.738C899.497,222.676,762.48,65.431,734.876,-105.659C707.941,-272.604,803.172,-472.205,693.564,-600.977C584.052,-729.638,374.986,-677.634,211.553,-720.485C54.263,-761.725,-87.826,-869.441,-249.029,-848.116C-420.543,-825.427,-584.969,-735.643,-693.335,-600.778C-800.108,-467.895,-829.149,-290.323,-834.138,-119.931C-838.75,37.573,-792.086,188.665,-720.562,329.069C-651.389,464.858,-562.459,593.532,-431.225,670.999C-302.54,746.962,-148.613,738.783,0,754.416",
          "M0,701.376C165.491,695.24,348.597,848.466,483.995,753.111C620.881,656.708,573.934,440.58,604.144,275.903C626.823,152.278,630.499,33.524,639.009,-91.876C650.114,-255.522,762.172,-443.726,661.783,-573.438C562.788,-701.349,355.756,-644.708,195.312,-665.173C61.527,-682.238,-66.881,-700.421,-200.678,-683.447C-356.344,-663.698,-522.616,-657.225,-644.669,-558.609C-775.679,-452.756,-874.84,-295.618,-888.454,-127.74C-901.829,37.193,-779.936,174.383,-716.506,327.217C-650.304,486.732,-660.713,709.933,-507.623,789.877C-352.224,871.026,-175.191,707.871,0,701.376"
        ];
        const a2=[
          "M1920 1773.882C2081.67 1764.0839999999998 2257.335 1904.484 2394.046 1817.6309999999999 2530.64 1730.8519999999999 2504.456 1523.783 2555.429 1370.191 2600.034 1235.788 2678.326 1113.036 2674.867 971.467 2671.152 819.4390000000001 2624.4880000000003 670.012 2535.31 546.831 2441.2619999999997 416.923 2314.7219999999998 304.94000000000005 2160.49 260.966 2006.781 217.14099999999996 1836.805 237.04600000000005 1692.925 306.655 1559.43 371.23900000000003 1505.426 525.181 1399.608 629.078 1281.23 745.308 1097.422 799.075 1034.896 952.741 967.9 1117.392 989.037 1309.938 1055.48 1474.813 1123.492 1643.5819999999999 1235.571 1817.932 1407.53 1877.4189999999999 1575.633 1935.5720000000001 1742.448 1784.643 1920 1773.882",
          "M1920 1737.187C2096.513 1746.795 2255.498 1917.9 2423.862 1864.024 2593.865 1809.624 2693.339 1629.393 2766.148 1466.423 2838.581 1304.295 2882.344 1119.275 2832.766 948.764 2785.408 785.889 2646.665 667.087 2506.12 572.124 2387.918 492.25699999999995 2236.256 507.46799999999996 2103.265 455.856 1952.941 397.51800000000003 1833.911 215.96400000000006 1676.317 250.09299999999996 1520.759 283.78099999999995 1487.585 494.77200000000005 1380.3719999999998 612.409 1269.3609999999999 734.213 1061.478 790.082 1035.813 952.873 1010.191 1115.393 1187.5259999999998 1233.679 1259.069 1381.837 1328.576 1525.779 1309.413 1735.33 1450.2 1811.024 1592.708 1887.643 1758.44 1728.393 1920 1737.187",
          "M1920 2005.4070000000002C2095.561 2036.437 2274.491 1961.3519999999999 2425.127 1865.993 2576.404 1770.228 2691.665 1629.1979999999999 2769.207 1467.82 2847.812 1304.231 2897.785 1122.738 2868.313 943.653 2839.13 766.326 2743.7690000000002 601.399 2607.3630000000003 484.39599999999996 2480.21 375.33000000000004 2307.821 349.97400000000005 2142.77 321.31600000000003 1993.935 295.47299999999996 1847.098 297.90599999999995 1698.794 326.64 1537.143 357.96000000000004 1337.604 358.98699999999997 1245.1399999999999 495.231 1151.738 632.856 1261.8980000000001 817.9780000000001 1252.633 984.047 1244.455 1130.625 1143.3609999999999 1273.756 1194.018 1411.545 1245.38 1551.252 1402.711 1611.399 1517.923 1705.644 1649.9940000000001 1813.6799999999998 1751.974 1975.708 1920 2005.4070000000002",
          "M1920 1972.9850000000001C2084.466 1983.938 2223.129 1853.575 2348.005 1745.988 2460.176 1649.347 2552.308 1530.386 2597.411 1389.363 2639.49 1257.795 2595.381 1121.484 2591.9030000000002 983.395 2587.8630000000003 823.023 2652.344 652.886 2575.315 512.166 2493.923 363.475 2339.077 252.37300000000005 2173.046 218.20600000000002 2011.032 184.86599999999999 1859.058 285.33799999999997 1700.403 332.12199999999996 1541.964 378.84299999999996 1366.112 383.448 1242.21 492.69100000000003 1110.826 608.531 987.962 773.392 1001.89 947.996 1015.785 1122.19 1210.392 1215.3 1311.2350000000001 1358.0140000000001 1389.203 1468.356 1432.077 1595.301 1527.128 1691.321 1642.8319999999999 1808.205 1755.897 1962.056 1920 1972.9850000000001",
          "M1920 1815.6570000000002C2056.584 1790.328 2179.624 1739.1480000000001 2304.494 1678.2849999999999 2446.163 1609.2350000000001 2642.049 1582.724 2699.2219999999998 1435.859 2756.86 1287.801 2594.564 1143.514 2574.008 985.968 2553.036 825.227 2659.933 649.611 2578.36 509.528 2495.5389999999998 367.302 2313.556 323.399 2157.585 270.85799999999995 1996.157 216.47900000000004 1828.452 165.341 1661.84 200.789 1488.68 237.63 1305.212 317.38800000000003 1217.1190000000001 470.95000000000005 1131.155 620.8 1227.842 806.399 1215.569 978.718 1204.488 1134.302 1110.734 1280.965 1148.65 1432.2640000000001 1189.702 1596.075 1280.23 1765.022 1431.4569999999999 1840.188 1580.4859999999999 1914.262 1756.367 1846.002 1920 1815.6570000000002",
          "M1920 1814.752C2059.373 1837.417 2198.066 1773.73 2319.179 1701.134 2442.986 1626.924 2546.227 1524.238 2615.73 1397.729 2688.8959999999997 1264.5529999999999 2760.4539999999997 1111.909 2723.928 964.413 2688.005 819.3530000000001 2552.7380000000003 722.136 2428.032 639.788 2324.849 571.653 2194.9030000000002 581.581 2078.989 538.534 1950.952 490.985 1849.319 372.84400000000005 1712.744 374.149 1561.799 375.592 1391.283 423.02200000000005 1303.6680000000001 545.945 1216.325 668.485 1277.895 836.893 1275.6689999999999 987.359 1273.757 1116.626 1230.283 1253.159 1291.522 1367.016 1351.787 1479.061 1492.743 1509.772 1596.3609999999999 1583.591 1706.566 1662.103 1786.442 1793.033 1920 1814.752"
        ];
        animateMorph(document.getElementById('wPath1'), a1);
        animateMorph(document.getElementById('wPath2'), a2);
      }
    }catch(e){}
  </script>
  <div class="flex items-center justify-center px-4">
    <div class="relative w-full max-w-[420px]">
      <div aria-hidden="true" class="absolute -inset-[1px] rounded-[28px] bg-gradient-to-tr from-black/10 via-white/60 to-black/10 blur-[2px]"></div>
      <section class="relative rounded-[28px] border border-zinc-200/80 bg-white/80 backdrop-blur-xl shadow-elev">
        <header class="flex items-center gap-3 px-6 pt-6">
          <img src="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152" alt="Glint Labs" class="h-8 w-8 object-contain" style="border-radius:8px;">
          <div class="select-none">
            <p class="text-[11px] uppercase tracking-[.16em] text-zinc-500">Glint Labs</p>
            <h1 class="text-[20px] font-medium tracking-tight">Welcome</h1>
          </div>
        </header>
        <nav class="mt-5 px-3">
          <div class="grid grid-cols-2 p-1 rounded-2xl bg-zinc-100">
            <button id="tab-signin" class="tab-btn btn-press inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-900 bg-white shadow-sm" aria-selected="true">Sign in</button>
            <a id="tab-register" href="/customer/register" class="tab-btn btn-press inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-600">Create account</a>
          </div>
        </nav>
        <div class="px-6 pb-6">
          <form id="view-signin" class="view mt-6" method="POST" action="{{ route('login') }}" autocomplete="on">
            @csrf
            <div class="space-y-4">
              <div>
                <label for="email" class="block text-[13px] text-zinc-600">Email</label>
                <input id="email" name="email" type="email" required inputmode="email" placeholder="you@company.com" value="{{ old('email') }}"
                  class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 outline-none ring-0 focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
                @error('email')<div class="text-[12px] text-red-600 mt-1">{{ $message }}</div>@enderror
              </div>
              <div>
                <label for="password" class="block text-[13px] text-zinc-600">Password</label>
                <div class="mt-1 relative">
                  <input id="password" name="password" type="password" required placeholder="••••••••"
                    class="peer w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 pr-11 outline-none ring-0 focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
                  <button type="button" class="absolute inset-y-0 right-2 my-1 px-2 rounded-lg text-zinc-500 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-black/10" aria-label="Show password" data-toggle-password="password">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                </div>
                @error('password')<div class="text-[12px] text-red-600 mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-[13px] text-zinc-600 cursor-pointer select-none">
                  <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-black/10" />
                  Remember me
                </label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-[13px] text-zinc-700 hover:text-black">Forgot password?</a>
                @endif
              </div>
              <button id="btn-signin" type="submit" class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-xl bg-black px-4 py-3 text-white transition active:opacity-90">
                <span>Sign in</span>
                <span class="hidden spinner" id="spin-in"></span>
              </button>
            </div>
          </form>
        </div>
        <div class="auth-footer px-6 pb-6">
          <p class="text-[12px] text-zinc-500">Protected by reCAPTCHA and subject to Glint Labs’ <a href="/privacy-ploicy" class="underline hover:no-underline">Privacy Policy</a> & <a href="/terms" class="underline hover:no-underline">Terms</a>.</p>
        </div>
      </section>
    </div>
  </div>
  <script data-cfasync="false">
  document.querySelectorAll('[data-toggle-password]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.getAttribute('data-toggle-password');
      const input = document.getElementById(id);
      if (!input) return;
      const isPw = input.type === 'password';
      input.type = isPw ? 'text' : 'password';
      btn.setAttribute('aria-label', isPw ? 'Hide password' : 'Show password');
    });
  });
  </script>
</div>
@endsection
