<?php
namespace App\Utilities;
use App\Models\User;
use App\Utilities\ResponseUtility;

class FcmUtility{


    /**
     * Write code on Method
     *  @param array user_id
     *
     */
    public function send_notification($user_id,$title,$body){
        $firebaseToken = User::whereIn("id",$user_id)->whereNotNull('fcm_token')->pluck('fcm_token');

        $SERVER_API_KEY = env("FCM_SERVER_KEY");

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        $response = json_decode($response);
        return $response;
    }

    public function send_notif_expo($token, $title, $body, $action = null, $value = null, $is_seller = null)
    {
        $curl = curl_init();
        $data = [
            "to" => $token,
            "title" => $title,
            "body" => $body,
            "data" => [
                "action" => $action,
                "value" => $value,
                "is_seller" => $is_seller
            ]
        ];
        // echo json_encode($data); die;

        curl_setopt_array($curl, [
          CURLOPT_URL => "https://exp.host/--/api/v2/push/send",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Accept-encoding: gzip, deflate",
            "Content-Type: application/json"
          ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return false;
        } else {
          return $response;
        }
    }

    public function check_balance_ppob(){


        $username   = env("MOBILEPULSA_USERNAME");
        $apiKey   = env("MOBILEPULSA_APIKEY");
        $signature  = md5($username.$apiKey.'bl');

        $json = json_encode([
            "commands"=>"balance",
            "username"=>$username,
            "sign"=>$signature,
        ]);

        $url = env("MOBILEPULSA_PREPAID");

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        curl_close($ch);
        $parse_data = json_decode($data);
        $resp = $parse_data->data;
        return $resp;
    }

    public function top_up_prabayar($ref_id, $hp, $product_code)
    {
        $username   = env("MOBILEPULSA_USERNAME");
        $apiKey   = env("MOBILEPULSA_APIKEY");
        $code = $product_code; //
        $signature  = md5($username.$apiKey.$ref_id);
        $hp = $hp;

        $json = json_encode([
            "commands"=>"topup",
            "username"=>$username,
            "ref_id"=>$ref_id,
            "hp"=>$hp,
            "pulsa_code"=> $code,
            "sign"=>$signature,
        ]);
        
        $url = env("MOBILEPULSA_PREPAID");

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = json_decode(curl_exec($ch));
        return $data;
        // $data = collect($data);

    }

}


?>
