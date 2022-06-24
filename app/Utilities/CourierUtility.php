<?php


namespace App\Utilities;


use App\Models\Order;

class CourierUtility
{
    public static function pickupSiCepat($order_code, $shipper_name, $shipper_phone, $shipper_email, $shipper_addr, $origin_code, $receiver_name, $receiver_phone, $receiver_email, $receiver_addr, $receiver_subdistrict,$receiver_city,$receiver_province, $receiver_zip, $destination_code, $qty, $weight, $parcel_category, $parcel_content, $servicetype, $insurance, $order_date, $item_name, $cod_value, $sendstarttime, $sendendtime, $goodsvalue, $shipper_city, $shipper_province, $shipper_subdistrict, $order_details,$shipping_cost)
    {


        # $development = true; // TRUE => Development Mode;;  FALSE => Production Mode

        /*
        if ($development) {
            $key = 'F5F21D17D11349E6B9F5899CB968D436';
        } else {
            // $key = 'e840b4adbdf4b9411e66b82651fab122';
        }
        # */

        # $cod_value = ($cod_status == 'Yes')? $goodsvalue + $shipping_cost : 0;


        #development
        $key = 'F5F21D17D11349E6B9F5899CB968D436';
        $url_endpoint = 'http://pickup.sicepat.com:8087/api/partner/requestpickuppackage';


//        #production
//        $key = 'e840b4adbdf4b9411e66b82651fab122';
//        $url_endpoint = 'https://pickup.sicepat.com/api/partner/requestpickuppackage';
//        #

        //untuk receipt number
        $prefix = "00156215";
        do {
            $new = str_pad(rand(1,9999),4,'0',STR_PAD_LEFT);
            $new = strval($new);
            $receipt_number = $prefix.$new;
        } while (Order::where("no_resi_sicepat",$receipt_number)->exists());

        // body order
        $datas = [
            "auth_key" => $key,
            "reference_number" => $order_code,
            "pickup_request_date" => $order_date,
            "pickup_merchant_code" => "",
            "pickup_merchant_name" => $shipper_name,
            "sicepat_account_code" =>"",
            "pickup_address" => $shipper_addr,
            "pickup_city" => $shipper_city,
            "pickup_merchant_phone" => $shipper_phone,
            "pickup_method" => "PICKUP",
            "pickup_merchant_email" => $shipper_email,
            "notes" => "",
            "PackageList" => [
                [
                    # still use 10 dummy receipt number
                    "receipt_number" => $receipt_number,
                    "origin_code" => $origin_code,
                    "delivery_type" => $servicetype,
                    "parcel_category" => $parcel_category,
                    "parcel_content" => $parcel_content,
                    "parcel_qty" => $qty,
                    "parcel_uom" => "Pcs",
                    "parcel_value" => $goodsvalue,
                    "cod_value" => $cod_value,
                    "insurance_value" => 0,
                    "total_weight" => $weight,
                    "parcel_length" => 0,
                    "parcel_width" => 0,
                    "parcel_height" => 0,
                    # "shipper_code" => null,
                    "shipper_name" => $shipper_name,
                    "shipper_address" => $shipper_addr,
                    "shipper_province" => $shipper_province,
                    "shipper_city" => $shipper_city,
                    "shipper_district" => $shipper_subdistrict,
                    "shipper_zip" => $receiver_zip,
                    "shipper_phone" => $shipper_phone,
                    "shipper_longitude" => null,
                    "shipper_latitude" => null,
                    "recipient_title" => 'Mr./Mrs./Miss.',
                    "recipient_name" => $receiver_name,
                    "recipient_address" => $receiver_addr,
                    "recipient_province" => $receiver_province,
                    "recipient_city" => $receiver_city,
                    "recipient_district" => $receiver_subdistrict,
                    "recipient_zip" => $receiver_zip,
                    "recipient_phone" => $receiver_phone,
                    "recipient_email" => $receiver_email,
                    "recipient_longitude" => null,
                    "recipient_latitude" => null,
                    "destination_code" => $destination_code,
                    "notes" => ""
                ]
            ]
        ];


        # dd(json_encode($datas));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url_endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($datas),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        # dd(json_decode($response));

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $response =  json_decode($response);
            /*$data = [
                "message"=>$response->error_message,
                "courier_order_id"=>$response->request_number,
                "datas"=>[
                    "receipt_number"=>$response->datas[0]->receipt_number
                ],
            ];*/
            $data = $response->datas[0]->receipt_number;
            return $data;
        }
    }


    public static function pickupJNT($orderid, $shipper_name, $shipper_contact, $shipper_phone, $shipper_addr, $origin_code, $receiver_name, $receiver_phone, $receiver_addr, $receiver_zip, $destination_code, $receiver_area, $qty, $weight, $goodsdesc, $servicetype, $insurance, $orderdate, $item_name, $cod_value, $sendstarttime, $sendendtime, $expresstype, $goodsvalue)
    {
    # development
    #$key = 'AKe62df84bJ3d8e4b1hea2R45j11klsb';
    #$username = 'HOBIKOE';
    #$api_key = 'BYWSRI';
    # $cod_value = ($cod_status)? $cod : 0;
    #$url_endpoint = 'http://test-jk.jet.co.id/jandt_ecommerce/api/onlineOrder.action';
    # */

    # production

    //masih pakai key development
    $key = env("JNT_PICKUP_KEY_TEST");
    $api_key =env("JNT_PICKUP_API_KEY_TEST");
    $url_endpoint= env("JNT_PICKUP_ENDPOINT_TEST");

    // $key = 'AKe62df84bJ3d8e4b1hea2R45j11klsb';
    $username = 'HOBIKOE';
    #

    $data = array (
              'username'=>$username,
              'api_key'=>$api_key,
              'orderid'=>$orderid,
              'shipper_name'=>$shipper_name,
              'shipper_contact'=>$shipper_contact,
              'shipper_phone'=>$shipper_phone,
              'shipper_addr'=>$shipper_addr,
              'origin_code'=>$origin_code,
              'receiver_name'=>$receiver_name,
              'receiver_phone'=>$receiver_phone,
              'receiver_addr'=>$receiver_addr,
              'receiver_zip'=>$receiver_zip,
              'destination_code'=>$destination_code,
              'receiver_area'=>$receiver_area,
              'qty'=>$qty,
              'weight'=>$weight,
              'goodsdesc'=>$goodsdesc,
              'servicetype'=>$servicetype,
              'insurance'=>$insurance,
              'orderdate'=>$orderdate,
              'item_name'=>$item_name,
              'cod'=>$cod_value,
              'sendstarttime'=>$sendstarttime,
              'sendendtime'=>$sendendtime,
              'expresstype'=>$expresstype,
              'goodsvalue'=>$goodsvalue,
            );

    $data_json = json_encode(array('detail'=>array($data)));
    $data_sign = base64_encode(md5($data_json.$key));

    /*
    return response()->json([
      'data_json' => $data_json
    ]);
    # */

    # $data_sign = base64_encode('c459d203586b75a9111898ec79479b8f');
    # $data_sign = 'YzQ1OWQyMDM1ODZiNzVhOTExMTg5OGVjNzk0NzliOGY=';

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url_endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "data_param=$data_json&data_sign=$data_sign",
      CURLOPT_HTTPHEADER => array(
        "content-type: application/x-www-form-urlencoded"
      ),
    ));

    $response = curl_exec($curl);
    # dd(json_decode($response));

    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      return $err;
    } else {
        /*
        return response()->json([
            'data_sign' => $data_sign,
            'response' => $response
        ]);
        # */
        $response = json_decode($response);
        /*$data = [
            "message"=>$response->desc,
            "courier_order_id"=>$response->detail[0]->orderid,
            "datas"=>[]
        ];

        if (array_key_exists("existing_awbno",$response->detail[0])) {
            $data["datas"] = [
                "receipt_number"=>$response->detail[0]->existing_awbno
            ];
        }else{
            $data["datas"] = [
                "receipt_number"=>$response->detail[0]->awb_no
            ];
        }*/
        $response_detail = $response ? $response->detail[0] : [];
        $data = "";
        if (array_key_exists("existing_awbno",$response_detail)) {
            $data = $response ? $response->detail[0]->existing_awbno : null;
        }else{
            $data = $response ? $response->detail[0]->awb_no : null;
        }


        return $data;

    }
  }


    public function tracking_sicepat($resi)
    {
        $api_key = env("SICEPAT_KEY");
        $url_endpoint = env("SICEPAT_WAYBILL_ENDPOINT");

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url_endpoint."?waybill=".$resi,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 300,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            "api-key: ".$api_key
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {

            $resp = json_decode($response,true);
            if ($resp['sicepat']['status']['code'] == 400) {
                $data = [];
                return $data;
            }else{
                $result = $resp["sicepat"]["result"];
                $data = [
                    "resi"=>$result["waybill_number"],
                    "send_date"=>$result["send_date"],
                    "sender" => [
                        "name"=>$result["sender"],
                        "city"=>$result["sender_address"]
                    ],
                    "receiver"=>[
                        "name"=>$result["receiver_name"],
                        "city"=>$result["receiver_address"]
                    ],
                    "track_history"=>[]
                ];

                foreach ($result["track_history"] as $history) {
                    $dt["date_time"] = $history["date_time"];
                    $dt["status"] = $history["status"];
                    if ($history["status"] == "DELIVERED") {
                        $dt["desc"] = $history["receiver_name"];
                    }else{
                        $dt["desc"] = $history["city"];
                    }
                    array_push($data["track_history"],$dt);
                }
                return $data;
            }

            # */
        }
    }

    public function tracking_jnt($resi)
    {
        $url_endpoint = env("JNT_TRACING_ENDPOINT_PROD");

        $data_key = array(
            'awb' => $resi,
            'eccompanyid' => "HOBIKOE"
        );
        $data = json_encode($data_key);
        $username = "HOBIKOE";
        $password = env("JNT_TRACKING_PASSWORD_PROD");



        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url_endpoint,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "$data",
          CURLOPT_HTTPHEADER => array(
            "Content-Type: text/plain",
            'Authorization: Basic '. base64_encode("$username:$password")
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
          return $err;
        } else {
            $resp = json_decode($response);
            // dd($resp);
            $data =  [
                "resi"=>$resp->awb,
                "send_date"=>$resp->detail->shipped_date,
                "sender" => [
                    "name"=>$resp->detail->sender->name,
                    "city"=>$resp->detail->sender->city
                ],
                "receiver"=>[
                    "name"=>$resp->detail->receiver->name,
                    "city"=>$resp->detail->receiver->city
                ],
                "track_history"=>[]

            ];
            foreach ($resp->history as $history) {
                $dt["date_time"] = $history->date_time;
                $dt["status"] = $history->storeName;
                $dt["desc"] = $history->status;
                array_push($data["track_history"],$dt);
            }
            return $data;

        }
    }
}
