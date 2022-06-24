<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Utilities\ErrorList;
use App\Models\Category;
use App\Models\Products;
use App\Http\Controllers\Controller;
use App\Utilities\ResponseUtility;


class ProductsController extends Controller
{
	
     /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */	 
    public function allproducts(Request $req)
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
             $paginator = Products::orderBy($req->sort_by,$req->sort_mode);
         }else{
             $paginator = Products::orderBy("id","asc");
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
            $v->photo = env("ASSET_URL").('images/all/'. $v->photo);
            $v->thumbnail_img = env("ASSET_URL").('images/all/'. $v->thumbnail_img);
             return $v;
         });
         return ResponseUtility::makeResponse($data,200,true,$total);
     }

     public function product_details(Request $req, $id)
     {
        $product = Products::find($id);
        if($product){

            $res[] = [
                'id' => $product->id,
                'user_id' => $product->user_id,
                'category_id' => $product->category_id,
                'name' => $product->name,
                'photos' => env("ASSET_URL").('images/all/'. $product->photos),
                'description' => $product->description,
                'unit_price' => $product->unit_price,
                'published' => $product->published,
                'featured' => $product->featured,
                'current_stock' => $product->current_stock
            ];

            //$response = [
            //    'product_now' => $res,
            //];
            return ResponseUtility::makeResponse($res,200);
        }else{
            $response['success'] = false;
            $response['message'] = 'Product Not Found!';

            return response($response);

        }
      }

      public function product_category(Request $req, $category_id)
      {
         $product = Products::find($category_id);
         if($product){
 
             $res[] = [
                 'id' => $product->id,
                 'user_id' => $product->user_id,
                 'category_id' => $product->category_id,
                 'name' => $product->name,
                 'photos' => env("ASSET_URL").('images/all/'. $product->photos),
                 'description' => $product->description,
                 'unit_price' => $product->unit_price,
                 'published' => $product->published,
                 'featured' => $product->featured,
                 'current_stock' => $product->current_stock
             ];
 
             //$response = [
             //    'product_now' => $res,
             //];
             return ResponseUtility::makeResponse($res,200);
         }else{
             $response['success'] = false;
             $response['message'] = 'Product Not Found!';
 
             return response($response);
 
         }
       }
}