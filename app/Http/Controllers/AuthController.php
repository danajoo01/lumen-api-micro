<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Utilities\ResponseUtility;
use App\Utilities\ErrorList;
use  App\Models\Users;

class AuthController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        
        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        try {
            $users = new Users([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'phone' => $request->phone,
                'level' => 'customer',
                'password' => Hash::make($request->password)
            ]);
            $users->save();

            //return successful response
            return response()->json(['users' => $users, 'message' => 'Berhasil! Silahkan Login'], 201);

        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' =>$e, 'User Registration Failed!'], 200);
        }

    }
	
     /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */	 
    public function login(Request $request)
    {
          //validate incoming request 
        $this->validate($request, [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['phone', 'password']);

        if (! $token = Auth::setTTL(10080)->attempt($credentials)) {			
            return response()->json(['message' => 'Unauthorized'], 200);
        }
        return $this->respondWithToken($token);
    }
	
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'Successfully signed out']);
    }
     /**
     * Get user details.
     *
     * @param  Request  $request
     * @return Response
     */	 	

    public function getProfile()
    {
        $messages = [];
        $info = Auth::user();
        /*if (!is_customer($info->id)){
            $messages[] = ErrorList::NOT_CUSTOMER;
            return ResponseUtility::makeResponse($messages,401);
        }*/

        $user_id = $info->id;
        $payload_user = Users::where(['id' => $user_id]);
        if ($payload_user->count() === 0) {
            $messages[] = ErrorList::USER_NOT_FOUND;
        }

        $data = [];
        if ($payload_user->first()) {
            $result = $payload_user->first();
            $data['avatar_profile'] = env("ASSET_URL").('images/all/'. $result->gambar);
            //$data['avatar_profile'] = Upload::where(["id"=>$result->avatar])->count() > 0 ?get_url_file($result->avatar) : "";
            $data["user"] = $result;
            return  ResponseUtility::makeResponse($data,200);

        }
        return ResponseUtility::makeResponse($messages,200);

    }

    public function updateProfile()
    {
        $messages = [];
        $info = Auth::user();
        $user_id = $info->id;
        $validate = Validator::make($this->req->only('phone_number', 'name','address'), [
            // 'phone_number' => ['regex:/^(\+62|62)8[1-9][0-9]{6,9}$/'],
            // 'phone_number' => ['regex:/^(\+62)8[1-9][0-9]{6,9}$/'],
            'name' => 'string|min:2|max:100',
        ], ['regex' => ':attribute pastikan nomor telepon harus berformat 08xxx'], ['phone_number' => 'Nomor Telepon']);
        // check if validator fail
        if ($validate->fails()) {
            return response()->json($validate->errors(),200);
        }

        $payload_user = Users::where(['id' => $user_id]);
        if ($payload_user->count() === 0) {
            $messages[] = ErrorList::USER_NOT_FOUND;
            return ResponseUtility::makeResponse($messages,200);
        }
        
        $payload_user = $payload_user->first();
        if ($this->req->has('password_new')) {
            $this->validate($this->req, [
                'password_new' => 'min:6|required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'min:6'
            ]);
            $phone_number = phone_number_update($this->req->phone_number); 
            $check_phone_number = Users::select('phone')->where('phone', $phone_number)->first();
            $phone_number_now = "";
            if ($check_phone_number) {
                $phone_number_now = $check_phone_number->phone;
            }else{
                $payload_user->update([
                    'phone' => $phone_number,
                ]);
            }
            $payload_user->update([
                'name' => $this->req->name,
                'address' => $this->req->address,
                'password' => Hash::make($this->req->password_new)
            ]);

            $messages[] = ErrorList::SUCCESS;
            return ResponseUtility::makeResponse($messages,200);
        }else{
            $phone_number = phone_number_update($this->req->phone_number); 
            $check_phone_number = Users::select('phone')->where('phone', $phone_number)->first();
            $phone_number_now = "";
            if ($check_phone_number) {
                $phone_number_now = $check_phone_number->phone;
            }else{
                $payload_user->update([
                    'phone' => $phone_number,
                ]);
            }
            $payload_user->update([
                'name' => $this->req->name,
                'address' => $this->req->address,
            ]);

            $messages[] = ErrorList::SUCCESS;
            return ResponseUtility::makeResponse($messages,200);
        }
    }
    public function me()
    {
        return response()->json(auth()->user());
    }
}