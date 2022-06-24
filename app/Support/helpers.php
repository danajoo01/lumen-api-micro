<?php

use App\Models\BusinessSetting;
use App\Models\Seller;
use App\Models\Translation;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Storage as Storage;

if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
if (!function_exists('assetUrl')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function assetUrl($path = '')
    {
        return env("ASSET_URL").$path;
    }
}
if (!function_exists('get_setting')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function get_setting($key)
    {
        $find = \App\Models\BusinessSetting::where(["type"=>$key]);
        if ($find->count() > 0){
            return $find->first()->value;
        }
        return null;
    }
}
function forgetCachedTranslations(){
    Cache::forget('translations');
}
function translate($key, $lang = null){
    if($lang == null){
        $lang = App::getLocale();
    }
    $translation_def = Translation::where('lang', env('DEFAULT_LANGUAGE', 'en'))->where('lang_key', $key)->first();
    if($translation_def == null){
        $translation_def = new Translation;
        $translation_def->lang = env('DEFAULT_LANGUAGE', 'en');
        $translation_def->lang_key = $key;
        $translation_def->lang_value = $key;
        $translation_def->save();
        forgetCachedTranslations();
    }
    $translation_locale = Translation::where('lang_key', $key)->where('lang', $lang)->first();
    if($translation_locale != null){
        return $translation_locale->lang_value;
    } else {
        return $translation_def->lang_value;
    }
}

function phone_number($value){       
    $number = '';
    $regex = substr($value, 0, 2);
    
    if($regex === '08'){
        $number .= '+62'.substr($value, 1);
    }else if($regex === '62'){
        $number .= '+'.$value;
    }else{
        $number = '+62'.$value;
    }   
    return $number;
}

function phone_number_update($value){       
    $number = '';
    $regex = substr($value, 0, 2);
    
    if($regex === '08'){
        $number .= '+62'.substr($value, 1);
    }else if($regex === '62'){
        $number .= '+'.$value;
    }else{
        $number = $value;
    }   
    return $number;
}

function is_customer($id){
    $have_custumer = \App\Models\Users::where(["id" => $id])->count() > 0;
    $have_flag = \App\Models\Users::where(["id" => $id,"level" => \App\Models\Users::CUSTOMER])->count() > 0;
    return $have_custumer && $have_flag;
}


function removeSpecialChar($str){

    # Using preg_replace() function to replace the word
    $res = preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$str);

    # Returning the result
    return $res;
}

function get_url_file($id)
{
    $upload = Upload::find($id);
    if(!$upload){
        return null;
    }
    $url = null;
    if (strpos($upload->file_name,"linode")) {
        $file = Storage::disk("linode")->temporaryUrl($upload->file_name, $exp = Carbon::now()->addMinutes(10));
        if ($file) {
            $url = $file;
        }else{
            $url = null;
        }
    }else{
        $url = assetUrl($upload->file_name);
    }

    return  $url;
}

if (! function_exists('static_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function static_asset($path, $secure = null)
    {
        return app('url')->asset(''.$path, $secure);
    }
}

function verified_sellers_id() {
    return Seller::where('verification_status', 1)->get()->pluck('user_id')->toArray();
    //return App\Seller::where('verification_status', 1)->where('seller_package_id', '!=', null)->get()->pluck('user_id')->toArray();
}

function filter_products($products) {
    $verified_sellers = verified_sellers_id();
    if(BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1){
        return $products->where('published', '1')->orderBy('created_at', 'desc')->where(function($p) use ($verified_sellers){
            $p->where('added_by', 'admin')->orWhere(function($q) use ($verified_sellers){
                $q->whereIn('user_id', $verified_sellers);
            });
        });
    }
    else{
        return $products->where('published', '1')->where('added_by', 'admin');
    }
}
