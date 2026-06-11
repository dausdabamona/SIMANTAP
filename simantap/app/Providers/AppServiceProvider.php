<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // @activeRoute('route.name') — tambahkan class 'active' jika route cocok
        Blade::directive('activeRoute', function ($expression) {
            return "<?php echo (request()->routeIs({$expression})) ? 'active' : ''; ?>";
        });

        // @money(value) — format rupiah
        Blade::directive('money', function ($expression) {
            return "<?php echo 'Rp ' . number_format({$expression}, 0, ',', '.'); ?>";
        });

        // @bulan(num) — nama bulan Indonesia
        Blade::directive('bulan', function ($expression) {
            return "<?php echo \\App\\Helpers\\DateHelper::namaBulan({$expression}); ?>";
        });
    }
}
