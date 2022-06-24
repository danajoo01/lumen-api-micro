<?php

namespace App\Utilities;

use App\Models\ApiUpload;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Storage;

class StorageUtility
{
    public static function read_file($id,$url_only=true)
    {
        $finding = ApiUpload::find($id);
        $payload = null;
        if ($finding){
            $file = Storage::disk("linode")->temporaryUrl($finding->full_path, $exp = Carbon::now()->addMinutes(10));
            if ($file){
                if ($url_only){
                    $payload = $file;
                }else{
                    $payload = [
                        "url"=>$file,
                        "exp"=>$exp
                    ];
                }
            }
        }
        return  $payload;
    }

    public static function get_file($id)
    {
        $finding = Upload::find($id);
        $payload = null;
        if ($finding){
            $file = Storage::disk("linode")->temporaryUrl($finding->file_name, $exp = Carbon::now()->addMinutes(10));
            if ($file){
                $payload = $file;
            }else{
                $payload = [];
            }
        }
        return  $payload;
    }
}
