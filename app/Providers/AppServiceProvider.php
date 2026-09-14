<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use App\Helpers\FirestoreHelper;
use App\Models\AdminUserRegion;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        setcookie('XSRF-TOKEN-AK', bin2hex(env('FIREBASE_APIKEY')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-AD', bin2hex(env('FIREBASE_AUTH_DOMAIN')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-DU', bin2hex(env('FIREBASE_DATABASE_URL')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-PI', bin2hex(env('FIREBASE_PROJECT_ID')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-SB', bin2hex(env('FIREBASE_STORAGE_BUCKET')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-MS', bin2hex(env('FIREBASE_MESSAAGING_SENDER_ID')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-AI', bin2hex(env('FIREBASE_APP_ID')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-MI', bin2hex(env('FIREBASE_MEASUREMENT_ID')), time() + 3600, "/"); 

        $countries_data = [];
        $get_countries_json = file_get_contents(public_path('countriesdata.json'));
        if($get_countries_json != ''){
            $countries_data = json_decode($get_countries_json);
        }
        view()->composer('*', function($view) use($countries_data) {
            $view->with('countries_data', $countries_data);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $openai_settings = FirestoreHelper::getDocument('settings/openai_settings');
        if (!empty($openai_settings)) {
            if (!empty($openai_settings['api_key'])) {
                Config::set('openai.api_key', $openai_settings['api_key']);
            }
            if (!empty($openai_settings['organization'])) {
                Config::set('openai.organization', $openai_settings['organization']);
            }
        }

        view()->composer('*', function ($view) use ($openai_settings) {
            $view->with('openai_settings', $openai_settings);
        });

        view()->composer('*', function ($view) {
            $view->with('admin_user_regions', $this->allowedRegions());
            $view->with('admin_active_region', $this->activeRegion());
        });
    }

    /**
     * The region ids the logged in admin is allowed to work in, held for the
     * life of the request. The composer runs for every view, partials included,
     * so the lookup is resolved once instead of on every render.
     *
     * @var array|null
     */
    private $allowed_regions = null;

    /**
     * The region ids the logged in admin is allowed to work in. An empty array
     * means the admin is unbound and may work in every region.
     *
     * @return array
     */
    private function allowedRegions()
    {
        if ($this->allowed_regions !== null) {
            return $this->allowed_regions;
        }

        if (!Auth::check()) {
            $this->allowed_regions = [];
            return $this->allowed_regions;
        }

        $user = Auth::user();
        if (AdminUserRegion::isSuperAdmin($user->id)) {
            $this->allowed_regions = [];
            return $this->allowed_regions;
        }

        $this->allowed_regions = AdminUserRegion::regionsOfUser($user->id);
        return $this->allowed_regions;
    }

    /**
     * The region the admin is currently working in. The value held in the
     * cookie is only honoured when the admin is actually allowed to use it, so
     * editing the cookie by hand cannot widen the admin's access.
     *
     * @return string
     */
    private function activeRegion()
    {
        $selected = isset($_COOKIE['region_id']) ? $_COOKIE['region_id'] : '';
        $allowed = $this->allowedRegions();

        if (empty($allowed)) {
            return $selected;
        }

        if ($selected != '' && in_array($selected, $allowed)) {
            return $selected;
        }

        return $allowed[0];
    }
}
