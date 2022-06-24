<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Utilities\ErrorList;
use App\Models\Cart;
use App\Models\Products;
use App\Models\Users;
use App\Models\Supplier;
use App\Http\Controllers\Controller;
use App\Utilities\ResponseUtility;
use Illuminate\Support\Facades\DB;


class CartController extends Controller
{
	
     /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */	 
    public function create(Request $req){
        $messages = [];
        $info = Auth::user();
        /*if (!is_customer($info->id)){
            $messages[] = ErrorList::NOT_CUSTOMER;
            return ResponseUtility::makeResponse($messages,401);
        }*/

        $this->validate($req, [
            'product_id'  => "required|integer",
            'qty'         => 'required|integer'
        ]);
        $get_product = Products::where(['id' => $req->product_id])->first();
        $qty = $req->qty;


        if (empty($get_product)) {
            $messages[] = ErrorList::PRODUCT_CREATED_FAILED;
            return ResponseUtility::makeResponse($messages,400);
            exit();
        }

        if($get_product->current_stock == 0 || $get_product->current_stock < $qty){
            $messages = "out_of_stock";
            return ResponseUtility::makeResponse($messages,400);
        }

        DB::beginTransaction();
        if($req->has('product_id') && $req->has('qty')) {
            $shop = Supplier::where(['user_id' => $get_product->user_id])->first();
            $cart = Cart::where(['product_id' => $req->product_id, 'user_id' => $info->id])->first();
            //$city = City::where(['city_id' => $shop->city])->first();
            $payload_cart = [
                'user_id' => $info->id,
                'product_id' => $get_product->id,
                //'variation' => $get_product->name,
                'price' => $get_product->unit_price * $qty,
                //'tax'   => $get_product->tax,
                'shipping_cost' => $get_product->shipping_cost,
                'quantity' => $qty,
            ];
            // var_dump($payload_cart); die;
            $total_belanja = 0; // tampung data total_belanja
            // Jika Kosong table cart maka insert
            if(empty($cart)) {
                $createCart = Cart::create($payload_cart);
                if (!$createCart){
                    DB::rollBack();
                    $messages[] = ErrorList::PRODUCT_CREATED_FAILED;
                    return ResponseUtility::makeResponse($messages,200);
                }
                $total_belanja = $get_product->unit_price * $qty;
            }else{

                $messages[] = ErrorList::CARTS_IS_READY;
                return ResponseUtility::makeResponse($messages,200);
                // Jika cart nya itu tidak kosong, maka dia update qty nya da juga price nyaa
                /*if ($cart->product_id == $req->product_id) {
                    $qty_update = (int) $cart->quantity + $qty;
                    $payload_cart_update = [
                        'quantity' => $qty_update,
                        'price' => $get_product->unit_price * $qty_update
                    ];
                    $updateCart = Cart::where(['product_id' => $req->product_id])->update($payload_cart_update);
                    if (!$updateCart){
                        DB::rollBack();
                        $messages[] = ErrorList::PRODUCT_CREATED_FAILED;
                        return ResponseUtility::makeResponse($messages,400);
                    }
                    $total_belanja = $get_product->unit_price * $qty_update;
                }*/
            }

            DB::commit();
            return ResponseUtility::makeResponse(['id' => $get_product->id],200);
        }
    }

    public function create_bu(Request $req){
        $messages = [];
        $info = Auth::user();
        /*if (!is_customer($info->id)){
            $messages[] = ErrorList::NOT_CUSTOMER;
            return ResponseUtility::makeResponse($messages,401);
        }*/

        $this->validate($req, [
            'product_id'  => "required|integer",
            'qty'         => 'required|integer'
        ]);
        $get_product = Products::where(['id' => $req->product_id])->first();
        $qty = $req->qty;


        if (empty($get_product)) {
            $messages[] = ErrorList::PRODUCT_CREATED_FAILED;
            return ResponseUtility::makeResponse($messages,200);
            exit();
        }

        if($get_product->current_stock == 0 || $get_product->current_stock < $qty){
            $messages = "out_of_stock";
            return ResponseUtility::makeResponse($messages,200);
        }

        DB::beginTransaction();
        if($req->has('product_id') && $req->has('qty')) {
            //$shop = Shop::where(['user_id' => $get_product->user_id])->first();
            $cart = Cart::where(['product_id' => $req->product_id, 'user_id' => $info->id])->first();
            //$city = City::where(['city_id' => $shop->city])->first();
            $payload_cart = [
                'user_id' => $info->id,
                'product_id' => $get_product->id,
                //'variation' => $get_product->name,
                'price' => $get_product->unit_price * $qty,
                'shipping_cost' => $get_product->shipping_cost,
                'quantity' => $qty,
            ];
            // var_dump($payload_cart); die;
            $total_belanja = 0; // tampung data total_belanja
            // Jika Kosong table cart maka insert
            if(empty($cart)) {
                $createCart = Cart::create($payload_cart);
                if (!$createCart){
                    DB::rollBack();
                    $messages[] = ErrorList::PRODUCT_CREATED_FAILED;
                    return ResponseUtility::makeResponse($messages,200);
                }
                $total_belanja = $get_product->unit_price * $qty;
            }else{

                $messages[] = ErrorList::CARTS_IS_READY;
                return ResponseUtility::makeResponse($messages,200);
                // Jika cart nya itu tidak kosong, maka dia update qty nya da juga price nyaa
                /*if ($cart->product_id == $req->product_id) {
                    $qty_update = (int) $cart->quantity + $qty;
                    $payload_cart_update = [
                        'quantity' => $qty_update,
                        'price' => $get_product->unit_price * $qty_update
                    ];
                    $updateCart = Cart::where(['product_id' => $req->product_id])->update($payload_cart_update);
                    if (!$updateCart){
                        DB::rollBack();
                        $messages[] = ErrorList::PRODUCT_CREATED_FAILED;
                        return ResponseUtility::makeResponse($messages,400);
                    }
                    $total_belanja = $get_product->unit_price * $qty_update;
                }*/
            }

            DB::commit();
            return ResponseUtility::makeResponse(['id' => $get_product->id],200);
        }
    }

    public function count_cart()
    {
        $messages = [];
        $info = Auth::user();
        $payload_cart = Cart::where(['user_id' => $info->id])->orderBy('created_at', 'asc')->count();
        return ResponseUtility::makeResponse($payload_cart, 200);
    }

    public function read()
    {
        $messages = [];
        $info = Auth::user();
        /*if (!is_customer($info->id)){
            $messages[] = ErrorList::NOT_CUSTOMER;
            return ResponseUtility::makeResponse($messages,401);
        }*/
        $tampung_cart = array();
        $tampung_shop_id = array();
        $payload_cart = Cart::where(['user_id' => $info->id])->orderBy('created_at', 'asc')->get(); //get current product by id_user
        if(empty($payload_cart)) {
            $messages[] = ErrorList::PRODUCT_NOT_FOUND;
            return ResponseUtility::makeResponse($messages,200);
        }

        foreach ($payload_cart as $key) {
            $get_product = Products::where(['id' => $key->product_id])->first();

            if (empty($get_product)) {
                $messages[] = ErrorList::PRODUCT_NOT_FOUND;
                return ResponseUtility::makeResponse($messages,200);
            }

            $get_shop = Supplier::select(['id', 'nama_supplier'])->where('user_id', $get_product->user_id)->first();
            array_push($tampung_shop_id, $get_shop->id);
        }

        $sub_total_cart = [];
        //$sub_total_cart_after_discount = [];
        $response = array();
        foreach(array_unique($tampung_shop_id) as $key){
            $product_item = array();
            $shopItem = Supplier::select(['id', 'user_id', 'nama_supplier'])->where(['id' => $key])->first();
            //$city = City::where(['city_id' => $shopItem->city])->count() > 0 ? City::where(['city_id' => $shopItem->city])->first() : 0;
            //$city_id = $city ? $city->province_id : 0;
            //$province = Province::where('province_id', $city_id)->first();
            $id_shop = $shopItem->user_id;
            $unit_price_subtotal = 0;
            $unit_price_subtotal_after_discount = 0;
            //loop product cart
            foreach ($payload_cart as $key) {
                $get_products = Products::where(['user_id' => $id_shop, 'id' => $key->product_id])->first();
                if ($get_products) {
                    
                    //$new_grand_total = "";
                    //if($get_products->discount_type == "percent"){
                    //    $grand_discount = $key->price * $get_products->discount / 100;
                    //    $new_grand_total = $key->price - $grand_discount;
                    //}elseif($get_products->discount_type == "amount"){
                    //    $new_grand_total = $key->price - $get_products->discount;
                    //}

                    $unit_price_subtotal  = $key->price * $key->quantity;
                    $unit_price_subtotal_after_discount  = $key->price * $key->quantity;

                    $product_item[] = [
                        'id_product'   => $get_products->id,
                        'name_product' => $get_products->name,
                        'weight' => $get_products->weight,
                        'logo' => $get_products->thumbnail_img = env("ASSET_URL").('images/all/'. $get_products->thumbnail_img),
                        'stock_barang' => $get_products->current_stock,
                        'unit_price_product'=> $get_products->unit_price,
                        'unit_price_total' => $key->price * $key->quantity,
                        'unit_price_before_discount'=> $get_products->unit_price,
                        'qty'=> $key->quantity
                    ];
                    $sub_total_cart[] = $key->price;
                    //$sub_total_cart_after_discount[] = $key->price - $new_grand_total;
                }
            }

            $response[] = [
                'id_shop'   => $shopItem->id,
                'name_shop' => $shopItem->nama_supplier,
                'items'     => $product_item,
                'summary'   => [    
                    'sub_total' => $unit_price_subtotal_after_discount,
                    'unit_price_before_discount' => $unit_price_subtotal,
                ]
            ];
        }
        $sub_total = array_sum($sub_total_cart);
        //$sub_total_cart_after_discount = array_sum($sub_total_cart_after_discount);
        return ResponseUtility::makeResponse(['shop' => $response, 'summaries' => $payload_cart->count() !== 0 ? ['subtotal_shop_before_discount' => $sub_total] : array() ] ,200);
    }

    public function delete(Request $req, $id = null)
    {
        $messages = [];
        $info = Auth::user();
        /*if (!is_customer($info->id)){
            $messages[] = ErrorList::NOT_CUSTOMER;
            return ResponseUtility::makeResponse($messages,401);
        }*/

        $delete = Cart::where(['user_id' => $info->id, 'product_id' => $id]);
        if ($delete->count() === 0 ){
            $messages[] = ErrorList::PRODUCT_NOT_FOUND;
        }
        if ($delete->delete()){
            return  ResponseUtility::makeResponse(["id"=>$id],200);
        }
        return  ResponseUtility::makeResponse($messages,200);

        return ResponseUtility::makeResponse(['data' => $id],200);

    }

}