<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Orders, OrderDetails, Users, Products, Upload, Address, Shop, Supplier, Seller ,Subdistrict};
use App\Utilities\ErrorList;
use App\Utilities\ResponseUtility;
use Illuminate\Support\Facades\{Auth, DB, Hash};
use Illuminate\Support\Str;

class HistoryOrderController extends Controller
{
    public function index(Request $req){
    	$messages     = [];
        $info         = Auth::user();
        $user_id      = $info->id;
        $payload_user = Users::where(['id' => $user_id]);
        $this->validate($req,[
            "limit"     =>"required|numeric|max:100|min:1",
            "sort_by"   =>"in:created_at,id",
            "sort_mode" =>"in:asc,desc",
        ]);
        if ($payload_user->count() === 0) {
            $messages[] = ErrorList::USER_NOT_FOUND;
            return ResponseUtility::makeResponse($messages, 404);
        }
        if ($req->has("sort_by") && $req->has("sort_mode")){
            $paginator = Orders::where(['user_id' => $user_id])->orderBy($req->sort_by,$req->sort_mode);
        }


        $paginator = $paginator->paginate($req->limit);
        $total     = $paginator->total();
        $data      = array_filter($paginator->items(),function ($v){
            $get_product_detail = OrderDetails::where(['order_id' => $v->id])->first();
            $get_product_detail_get = OrderDetails::where(['order_id' => $v->id])->get();
            $seller = Supplier::where('user_id', $get_product_detail->supplier_id)->first();
            $productItem = Products::where(['id' => $get_product_detail->product_id])->first();
            $tampung_quantity = array();
            foreach ($get_product_detail_get as $key) {
                $tampung_quantity[] =  $key->quantity;
            }
            $v->order_id = $v->id;
            $v->product_name = $productItem->name ?? "";
            $v->shops = $seller->nama_supplier;
            // $v->official_store = $seller->official_store;
            //$seller_city = $seller ? $seller->city : 0; 
            $v->alamat_supplier = $seller->alamat_supplier;
            $v->grand_total = $v->grand_total;
            //$thumbnail_img = $productItem ? $productItem->thumbnail_img : 0;
            $v->img_supplier = env("ASSET_URL").('images/all/'. $productItem->thumbnail_img);
            //$v->img_product = Upload::where(["id"=>$thumbnail_img])->count() > 0 ?assetUrl(Upload::where(["id"=>$thumbnail_img])->first()->file_name) : "";
            $v->qty = array_sum($tampung_quantity);
            $v->delivery_status = $get_product_detail->delivery_status;

            //unset($v->guest_id);
            unset($v->shipping_courier);
            unset($v->shipping_cost);
            unset($v->shipping_service);
            //unset($v->coupon_discount);
            unset($v->date);
            //unset($v->origin_city);
            // unset($v->shipping_address);
            //unset($v->govt_commission);
            unset($v->payment_type);
            //unset($v->manual_payment);
            //unset($v->manual_payment_data);
            unset($v->payment_details);
            //unset($v->coupon_discount);
            unset($v->viewed);
            unset($v->delivery_viewed);
            unset($v->payment_status_viewed);
            //unset($v->commission_calculated);
            unset($v->created_at);
            unset($v->updated_at);
            unset($v->request);
            unset($v->receipt);
            //unset($v->midtrans_order_id);
            //unset($v->manual_grand_total);
            //unset($v->manual_key);
            //unset($v->no_resi_sicepat);
            return $v;
        });
        return ResponseUtility::makeResponse($data,200,true,$total,$req->limit,ceil($total/$req->limit));
    }

    public function detailPembelian(Request $req, $id = NULL)
    {
        if ($id) {
            $info = Auth::user();
            $tampung_grand_total = array();
            $tampung_order = array();
            $tampung_shipping_const = array();
            $tmpung_detail_user = array();
            $tampung_detail_pengirim = array();
            $tampung_detail_penerima = array();
            $order_id = $id;
            $payload_order = Orders::where(['code_po' => $order_id, 'user_id' => $info->id])->first();
            if (empty($payload_order)) {
                $response[] = ErrorList::ORDER_NOT_FOUND;
                return  ResponseUtility::makeResponse($response,404);
            }else{
                $order_detail = OrderDetails::where('order_id', $payload_order->id)->get();
                $user = Users::where('id', $payload_order->user_id)->first();
                $address = json_decode($payload_order->shipping_address);
                //$subdistrict = $address ? $address->subdistrict : 0;
                //$subdistrict_name = Subdistrict::where('subdistrict_id', $subdistrict)->first()->subdistrict_name ?? null;
                //$city_id = $address ? $address->city : 0;
                //$province_id = \App\Models\City::where('city_id', $city_id)->first()->province_id ?? 0;
                //$province = \App\Models\Province::where('province_id', $province_id)->first()->province_name ?? null;
                $tampung_detail_penerima[] = [
                    'user_id' => $user ? $user->id : "",
                    'penerima' => $user ? $user->name : "",
                    "address" => $address ? $address->address : "",
                    //"city_name" => $address ? $address->city_name : "",
                    //"district" => $address ? $subdistrict_name : "",
                    //"province" => $province,
                    "phone" => $address ? $address->phone : "",
                    //"postal_code" => $address ? $address->postal_code : "",
                ];

                foreach ($order_detail as $key ) {
                    $seller = Supplier::where('user_id', $key->seller_id)->first();
                    $product = Products::where('id', $key->product_id)->first();
                    //$reviews = \App\Models\Review::where('product_id', $key->product_id)->where('order_id', $key->order_id)->where('user_id', $info->id)->orderBy('created_at', 'desc');
                    // var_dump($key->product_id); die;
                    $tampung_order[] = [
                        'id_product' => $product->id ?? "",
                        'id_seller' => $key->seller_id,
                        'product_name' => $product->name ?? "",
                        'unit_price' => $product->unit_price ?? "",
                        'payment_status' => $key->payment_status,
                        //'discount' => $product->discount ?? "",
                        //'discount_type' => $product->discount_type ?? "",
                        'shipping_courier' => $key->shipping_courier,
                        'shipping_service' => $key->shipping_service,
                        'delivery_status' => $key->delivery_status,
                        'quantity' => $key->quantity,
                        'no_resi'  => $key->no_resi,
                        'grand_total' => $key->price,
                        'img' => $product->thumbnail_img = env("ASSET_URL").('images/all/'. $product->thumbnail_img)
                        //'img' => Upload::where(["id"=>$product->thumbnail_img ?? 0])->count() > 0 ?get_url_file($product->thumbnail_img ?? 0):null,
                        //'ulasan' => $reviews->count() > 0 ? true : false,
                        //'rating' => $reviews->first()->rating ?? null,
                        //"message_ulasan" => $reviews->first()
                    ];
                    $tampung_detail_pengirim[] = [
                        'shops_id' => $seller ? $seller->id : "",
                        'pengirim' => $seller ? $seller->name: "",
                        'no_resi' => $payload_order ? $payload_order->no_resi_sicepat : "",
                        'shipping_courier' => $key->shipping_courier,
                        'shipping_service' => $key->shipping_service,
                        'address' => $seller ? $seller->address : "",
                        'tgl_pesanan_diterima' => $key->updated_at
                    ];
                    $tampung_grand_total[] = $key->grand_total;
                    $tampung_shipping_const[] = $key->shipping_cost; // jika lebih dari 1 product total ongkos kirimnya di gabungin
                }
                $shipping_const = array_sum($tampung_shipping_const);

                $type_pembayaran = '';
                if ($payload_order->payment_type === 'midtrans') {
                    $type_pembayaran = 'Pembayaran Otomatis';
                }elseif ($payload_order->payment_type === 'manual_payment') {
                    $type_pembayaran = 'Pembayaran Manual';
                }else{
                    $type_pembayaran = 'Cash On Delivery (COD)';
                }

                $response = [
                    'id_pesanan'          => $id,
                    'order_id'            => $payload_order->id,
                    'detail_penerima'     => $tampung_detail_penerima,
                    'detail_pengiriman'   => $tampung_detail_pengirim,
                    'detail_pembayran'    => array('payment_type' => $type_pembayaran ,'grand_total' => $payload_order->grand_total, 'total_shipping_const' => $shipping_const, 'code_unique' => $payload_order->manual_key, 'tgl_pembayaran' => $payload_order->created_at, 'coupon_discount' => $payload_order->coupon_discount),
                    'product_items'       => $tampung_order,
                ];
                return ResponseUtility::makeResponse($response,200);
            }

        }else{
            $response[] = 'order_id_not_found';
            return  ResponseUtility::makeResponse($response,404);
        }
    }
}
