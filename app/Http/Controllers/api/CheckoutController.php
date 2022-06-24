<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Utilities\ErrorList;
use App\Models\Cart;
use App\Models\Products;
use App\Models\Orders;
use App\Models\OrderDetails;
use App\Models\Users;
use App\Models\Supplier;
use App\Http\Controllers\Controller;
use App\Utilities\ResponseUtility;
use Illuminate\Support\Facades\DB;


class CheckoutController extends Controller
{
	
     /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */	 
    public function order_create(Request $req)
    {
        $messages = [];
        $info = Auth::user();
        if (!is_customer($info->id)){
            $messages[] = ErrorList::NOT_CUSTOMER;
            return ResponseUtility::makeResponse($messages,200);
        }
        $id = $info->id;
        $this->validate($req, [
            "product_id" => "required|integer",
            "payment_type" => "required|string",
            "payment_status" => "required|string",
            "grand_total" => "required|string",
            //"coupon_discount" => "required|string",
        ]);

        $check_shipping = Users::where(['id' => $req->user_id, "id" => $id])->first();
        $productItem = Products::where(['id' => $req->product_id])->first();
        if (empty($check_shipping)) {
            $messages[] = ErrorList::ADDRESS_NOT_FOUND;
            return ResponseUtility::makeResponse($messages,200);
        }

        if (empty($productItem)) {
            $messages[] = ErrorList::PRODUCT_NOT_FOUND;
            return ResponseUtility::makeResponse($messages,200);
        }

        if (!empty($productItem)) {
            $shipping = $req->shipping_cost;
            $shippingAddress = array('name' => $info->name, 'email' => $info->email, "address" => $check_shipping->address, "phone" => $check_shipping->phone, "checkout_type" => "logged");
            DB::beginTransaction();
            $order = Orders::create([
                'user_id' => $id,
                'shipping_address' => json_encode($shippingAddress),
                'shipping_cost' => $req->shipping_cost,
                'payment_type' => $req->payment_type,
                'payment_status' => $req->payment_status,
                'grand_total' => $req->grand_total + $shipping,
                'code_po' => date('Ymd-his'),
                'date' => strtotime('now')
            ]);
            if (!$order) {
                DB::rollBack();
                $messages[] = ErrorList::SHOPPING_CREATED_FAILED;
                return ResponseUtility::makeResponse($messages,200);
            }
            
            $cartItem = Cart::where(['product_id' => $req->product_id, 'user_id' => $id])->first();
            if (empty($cartItem)) {
                $messages[] = ErrorList::CARTS_NOT_FOUND;
                return ResponseUtility::makeResponse($messages,200);
            }

            $order_detail = OrderDetails::create([
                'order_id' =>  $order['id'],
                'supplier_id' => $productItem->user_id,
                'product_id' => $productItem->id,
                'variation' => $cartItem->variation,
                'price' => $cartItem->price * $cartItem->quantity,
                'shipping_cost' => $req->shipping_cost,
                'quantity' => $cartItem->quantity,
                'delivery_status' => 'pending',
                'payment_status' => $req->payment_status
            ]);

            /*$productItem->update([
                'num_of_sale' => DB::raw('num_of_sale + ' . $cartItem->quantity)
            ]);*/

            if (empty($order_detail)) {
                DB::rollBack();
                $messages[] = ErrorList::SHOPPING_CREATED_FAILED;
                return ResponseUtility::makeResponse($messages,200);
            }

            DB::commit();
            return ResponseUtility::makeResponse(['data' => true],200);
        }
        
    }

    public function order_list(Request $req)
    {
        $messages = [];
        $info = Auth::user();
        if (!is_customer($info->id)){
            $messages[] = ErrorList::NOT_CUSTOMER;
            return ResponseUtility::makeResponse($messages,200);
        }
        $id = $info->id;
        $this->validate($req,[
           "limit"=>"required|numeric|max:100|min:2",
           "sort_by"=>"in:created_at,id",
           "sort_mode"=>"in:asc,desc",
        ]);

        if ($req->has("sort_by") && $req->has("sort_mode")){
            $paginator = Orders::where(['user_id' => $id])->orderBy($req->sort_by,$req->sort_mode);
        }else{
            $paginator = Orders::orderBy("id","asc");
        }
        
        $paginator = $paginator->paginate($req->limit);
        $total = $paginator->total();
        $data = array_filter($paginator->items(),function ($v){
            $order = OrderDetails::where(['order_id' => $v->id])->first();
            $v->order = $order;

            unset($v->user_id);
            unset($v->govt_commission);
            // unset($v->order->tax);
            // unset($v->order->product_referral_code);
            // unset($v->order->pickup_point_id);
            return $v;
        });
        return ResponseUtility::makeResponse($data,200,true,$total,$req->limit,ceil($total/$req->limit));

    }

}