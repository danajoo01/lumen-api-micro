<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Banner;
use App\Http\Controllers\Controller;
use App\Utilities\ResponseUtility;


class BannerController extends Controller
{
	
     /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */	 
    public function banner_home(Request $req)
    {
          //validate incoming request 
        $this->validate($req, [
            //"limit"=>"required|numeric|max:100|min:2",
            //"page"=>"required|numeric|min:1",
            "is_featured"=>"boolean",
            "is_published"=>"boolean",
            "sort_by"=>"in:created_at,id",
            "sort_mode"=>"in:asc,desc",
        ]);

         if ($req->has("sort_by") && $req->has("sort_mode")){
             $paginator = Banner::orderBy($req->sort_by,$req->sort_mode);
         }else{
             $paginator = Banner::orderBy("id","asc");
         }
         if ($req->has("is_featured")){
             $paginator->where(["featured"=>intval($req->is_featured)]);
         }
         if ($req->has("is_published")){
             $paginator->where(["published"=>intval($req->is_published)]);
         }
         $paginator = $paginator->paginate($req->limit);
         $total = $paginator->total();
         $data = array_filter($paginator->items(),function ($v){
            $v->banner = env("ASSET_URL").('images/all/'. $v->banner);
             return $v;
         });
         return ResponseUtility::makeResponse($data,200,true,$total);
     }
}