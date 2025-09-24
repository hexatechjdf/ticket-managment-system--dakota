<?php

namespace App\Http\Controllers;

use App\Helpers\CRM;
use App\Http\Controllers\Controller;
use App\Models\CrmToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use stdClass;

class CRMConnectionController extends Controller
{
    public function crmCallback(Request $request)
    {
        Artisan::call('optimize:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        // dd($request->all());
        $code = $request->code ?? null;
        $user_id = $request->state ?? 1;
        if ($code) {

            $code = CRM::crm_token($code, '');

            $code = json_decode($code);
            $companyId = $code->companyId;
            $user_type = $code->userType ?? null;

            $main = route('agency.index');
            $agencyConnected = route('agencyconnected');

            if ($user_type) {
                $token = $user->token ?? null;
                list($connected, $con) = CRM::go_and_get_token($code, '', $user_id, $token);
                if ($connected) {
                    $companyData = CRM::agencyV2($companyId, 'companies/' . $companyId);
                   // dd($companyData);
                    if ($companyData && property_exists($companyData, 'company')) {
                        $crmToken = CrmToken::where('company_id', $companyId)->first();
                        $companyData = $companyData->company;
                        $detail = new stdClass;
                        $detail->name = $companyData->name;
                        $detail->email = $companyData->email;
                        $detail->timezone = $companyData->timezone;
                        $detail->phone = $companyData->phone;
                        $detail = json_encode($detail);
                        $crmToken->meta = $detail;

                        $userEmail = $companyId."@agencysupport.com";
                        $user = User::where('email',$userEmail )->first();
                        if(!$user){
                            $user  = new User();
                            $user->name=$companyData->name;
                            $user->email=$userEmail;

                            $user->password= \Hash::make($companyId.'333***');
                            $user->save();

                        }
                        $crmToken->user_id = $user->id;
                        $crmToken->save();
                    }
                    // $this->authService->getCompany(auth()->user());
                    if(!auth()->user()){
                       return redirect($agencyConnected)->with('success', 'Connected Successfully');

                    }
                    return redirect($main)->with('success', 'Connected Successfully');
                }
                return redirect($main)->with('error', json_encode($code));
            }
            return response()->json(['message' => 'Not allowed to connect']);
        }
    }
    
    public function agencyconnected(){
        return view('user.agencyconnected');
    }

    private function evp_bytes_to_key($password, $salt)
    {
        $key = '';
        $iv = '';
        $derived_bytes = '';
        $previous = '';

        // Concatenate MD5 results until we generate enough key material (32 bytes key + 16 bytes IV = 48 bytes)
        while (strlen($derived_bytes) < 48) {
            $previous = md5($previous . $password . $salt, true);
            $derived_bytes .= $previous;
        }

        // Split the derived bytes into the key (first 32 bytes) and IV (next 16 bytes)
        $key = substr($derived_bytes, 0, 32);
        $iv = substr($derived_bytes, 32, 16);

        return [
            $key,
            $iv
        ];
    }

    public function decryptSSO(Request $request)
    {
        try {

            $ssoKey = env('SSO_KEY');; // Save in the db settings

            if (!$ssoKey) {
                return response()->json(['status' => false, 'message' => 'SSO key is not configured.']);
            }
            $ciphertext = base64_decode($request->ssoToken);

            if (substr($ciphertext, 0, 8) !== "Salted__") {
                return response()->json(['status' => false]);
            }
            $salt = substr($ciphertext, 8, 8);
            $ciphertext = substr($ciphertext, 16);
            list($key, $iv) = self::evp_bytes_to_key($ssoKey, $salt);
            $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

            if ($decrypted === false) {
                return response()->json(['status' => false]);
            } else {

                $decrypted_data = json_decode($decrypted, true);
                \Log::info($decrypted_data);
                $comapnyId = $decrypted_data['companyId'];
                $userId = CrmToken::where('companyId', $comapnyId)->value('user_id');
                if ($userId) {
                    $user = User::where('id', $userId)->first();
                    if ($user) {
                        dd('fffff');
                    }

                    // if ($user) {
                    //     Auth::login($user);
                    // }
                    // if (Auth::check()) {
                    //     return response()->json(['status' => true, 'user' => Auth::user()]);
                    // }
                    // return response()->json(['status' => false, 'message' => 'Auth session initialization failed.']);
                }
            }
        } catch (Exception $e) {
            Log::error('SSO Decryption Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred while processing your request.']);
        }
    }
}
