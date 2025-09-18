<?php

use App\Models\Setting;

if (!function_exists('save_my_settings')) {
    function save_my_settings($key, $value)
    {
        $obj = Setting::where('key', $key)->first();
        if (!$obj) {
            $obj = new Setting();
            $obj->key = $key;
        }
        $obj->value = $value;
        $obj->save();
        cache_put($key, $value);
    }
}

if (!function_exists('get_default_settings')) {
    function get_default_settings($key, $default = '')
    {
        $setting = cache_get($key);
        if (!empty($setting)) {
            return $setting;
        }
        $setting = Setting::where('key', $key)->pluck('value', 'key')->toArray();
        return $setting[$key] ?? $default;
    }
}

if (!function_exists('cache_get')) {
    function cache_get($key, $default = '')
    {
        return  cache()->get($key) ?? $default;
    }
}

if (!function_exists('cache_put')) {
    function cache_put($key, $value = '')
    {
        return  cache()->put($key, $value);
    }
}
