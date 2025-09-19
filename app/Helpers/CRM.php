<?php

namespace App\Helpers;

use App\Models\CrmToken;
use Illuminate\Support\Facades\Cache;
use App\Helpers\gCache;

class CRM
{

    protected static $base_url = 'https://services.leadconnectorhq.com/';
    protected static $version = '2021-07-28';
    protected static $crm = CrmToken::class;
    public static $lang_com = 'Company';
    public static $lang_loc = 'Location';

    protected static $userType = ['Company' => 'company_id', 'Location' => 'location_id'];
    public static $scopes = "companies.readonly";

    protected static $no_token = 'No Token';
    protected static $no_record = 'No Data';

    public static function getDefault($key, $def = '')
    {
        $def = get_default_settings($key);
        return $def;
    }

    public static function getCrmToken($where = [])
    {
        $key = '';
        if (isset($where['location_id'])) {
            $key = 'loc_' . $where['location_id'];
        } elseif (isset($where['company_id'])) {
            $key = 'comp_' . $where['company_id'];
        } else {
            $key = json_encode($where);
        }
        // dd(static::$crm::where($where)->first(), $where);
        return gCache::remember($key, 3 * 60, function () use ($where) {
            return static::$crm::where($where)->first();
        });
    }

    public static function saveCrmToken($code, $company_id, $loc = null)
    {
        $where = []; //'user_id' => $company_id
        $type = $code->userType;
        if ($type == self::$lang_loc) {
            $where['location_id'] = $code->locationId ?? '';
            $where['user_type'] = self::$lang_loc;
        }
        $cmpid = $code->companyId ?? "";
        if (!empty($cmpid)) {
            if ($type == self::$lang_com) {
                $where['user_type'] = self::$lang_com;
            }
            $where['company_id'] = $cmpid;
        }
        $already = true;
        if (!$loc) {
            $already = false;
            $loc = self::getCrmToken($where);
            if (!$loc) {
                $loc = new static::$crm();
                $loc->location_id = $code->locationId ?? '';
                $loc->user_type = $type;
                $loc->company_id = $cmpid;
                $loc->user_id = $company_id;
                $loc->crm_user_id = $code->userId ?? '';
            }
        }
        $loc->expires_in = $code->expires_in ?? 0;
        $loc->access_token = $code->access_token;
        $loc->refresh_token = $code->refresh_token;
        $loc->save();

        self::rememberToken($loc);

        if ($already) {
            $loc->refresh();
        }
        return $loc;
    }



    // public static function saveCrmToken($code, $company_id, $loc = null)
    // {
    //     $where = [];//'user_id' => $company_id
    //     $type = $code->userType;
    //     if ($type == self::$lang_loc) {
    //         $where['location_id'] = $code->locationId ?? '';
    //     }
    //     $cmpid = $code->companyId ?? "";
    //     if (!empty($cmpid)) {
    //         $where['company_id'] = $cmpid;
    //     }
    //     $already = true;
    //     if (!$loc) {
    //         $already = false;
    //         $loc = self::getCrmToken($where);
    //         if (!$loc) {
    //             $loc = new static::$crm();
    //             $loc->location_id = $code->locationId ?? '';
    //             $loc->user_type = $type;
    //             $loc->company_id = $cmpid;
    //             $loc->user_id = $company_id;
    //             $loc->crm_user_id = $code->userId ?? '';
    //         }
    //     }
    //     $loc->expires_in = $code->expires_in ?? 0;
    //     $loc->access_token = $code->access_token;
    //     $loc->refresh_token = $code->refresh_token;
    //     $loc->save();

    //     self::rememberToken($loc);

    //     if ($already) {
    //         $loc->refresh();
    //     }
    //     return $loc;
    // }

    public static function rememberToken($token)
    {
        if ($token) {
            $key = $token->user_type == self::$lang_loc ? 'loc_' . $token->location_id : 'comp_' . $token->company_id;
            gCache::put($key, $token, 3 * 60);
        }
    }

    public static function makeCall($url, $method = 'get', $data = null, $headers = [], $json = true)
    {
        // dd($url, $method, $data, $headers, $json);
        $curl = curl_init();
        $methodl = strtolower($method);
        $is_key_headers = array_is_list($headers);
        if (!$is_key_headers) {
            $headers1 = [];
            foreach ($headers as $key => $t) {
                $headers1[] = $key . ': ' . $t;
            }
            $headers = $headers1;
        }
        $isFile = strpos($url, 'upload-file') !== false;
        // \Log::info('in makeCall ' . $url . $isFile);
        $jsonheader = 'content-type: application/json';
        if (!empty($data)) {

            if (!$isFile) {
                if ((is_array($data) || is_object($data))) {
                    if ($json) {
                        $data = json_encode($data);
                    } else {
                        $data = json_decode(json_encode($data), true);
                        $data = http_build_query($data);
                    }
                }
                if ($json) {
                    $headers[] = $jsonheader;
                }
            }

            if ($methodl != 'get') {
                curl_setopt_array($curl, [CURLOPT_POSTFIELDS => $data]);
            } else {
               $url = static::$base_url . static::urlFix($url) . $data;
            }
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err != '') {
            $response = $err;
        }

        return $response;
    }

    public static function directConnect()
    {
        // for location level connectivity where auto auth is not present then change gohighlevel with leadconnectorhq for only subaccounts
        return 'https://marketplace.gohighlevel.com/oauth/chooselocation?' . self::baseConnect();
    }

    public static function baseConnect()
    {
        $callbackurl = route('crm.oauth_callback');
        $client_id = static::getDefault('crm_client_id');

        return "response_type=code&redirect_uri=" . urlencode($callbackurl) . "&client_id=" . $client_id . "&scope=" . urlencode(static::$scopes);
    }
    public static function ConnectOauth($main_id, $token, $is_company = false, $user_id = null)
    {
        $tokenx = false;

        if (!empty($token)) {
            $loc = $main_id;
            $type = $is_company ? self::$lang_com : self::$lang_loc;
            $auth_type = self::$userType[$type];
            $locurl = static::$base_url . "oauth/authorize?" . ($auth_type) . "=" . $loc . "&userType=" . $type . '&' . self::baseConnect();
            $data = [];

            if ($is_company) {
                $data = [
                    "approveAllLocations" => true,
                    "installToFutureLocations" => true
                ];
            }
            // dd($locurl);
            $red = self::makeCall($locurl, 'POST', $data, [
                'Authorization: Bearer ' . $token,
                //'Version: 2021-04-15'
            ]);
            $red = json_decode($red);

            //  \Log::info(['newLocToken',$red]);

            if ($red && property_exists($red, 'redirectUrl')) {
                $url = $red->redirectUrl;
                $parts = parse_url($url);
                parse_str($parts['query'], $query);
                $code = $query['code'] ?? '';
                list($tokenx, $token) = self::go_and_get_token($code, '', $user_id);
            }
        }
        return $tokenx;
    }

    public static function getLocationAccessTokenFirstTimeByCompany($user_id, $location_id, $retries = 0)
    {
        $companyToken =  static::$crm::where(['user_id' => 1])->first();
        $resp = null;
        if ($companyToken && !is_int($companyToken)) {

            $response = self::makeCall(static::$base_url . "oauth/locationToken", 'POST', "companyId=" . $companyToken->company_id . "&locationId=" . $location_id, [
                "Accept: application/json",
                "Authorization: Bearer " . $companyToken->access_token,
                "Content-Type: application/x-www-form-urlencoded",
                "Version: " . static::$version,
            ], false);
            $resp = json_decode($response);
            //  \Log::info(['newfetchToken',$resp]);
            if ($resp && property_exists($resp, 'access_token')) {
                $resp = self::saveCrmToken($resp, $user_id);
            } else if (self::isExpired($resp) && $retries == 0) {
                // \Log::info('Token Expired');
                list($is_refresh, $companyToken) = self::getRefreshToken(1, $companyToken, true);
                if ($is_refresh) {
                    return self::getLocationAccessTokenFirstTimeByCompany($user_id, $location_id, $retries + 1);
                }
            }
        }
        return $resp;
    }

    public static function getLocationAccessToken($user_id, $location_id, $userType = null, $token = null, $retries = 0)
    {
        // \Log::info('step 23');
        if ($userType == null) {
            $userType = self::$lang_com;
        } else {
            $userType = 'Location';
        }
        if (!$token) {
            $token = self::getCrmToken(['user_id' => $user_id, 'user_type' => $userType]);
        }
        $resp = null;
        if ($token && !is_int($token)) {

            // \Log::info('Locaion in function ='. $location_id);
            $response = self::makeCall(static::$base_url . "oauth/locationToken", 'POST', "companyId=" . $token->company_id . "&locationId=" . $location_id, [
                "Accept: application/json",
                "Authorization: Bearer " . $token->access_token,
                "Content-Type: application/x-www-form-urlencoded",
                "Version: " . static::$version,
            ], false);
            $resp = json_decode($response);
            //  \Log::info(['newfetchToken',$resp]);
            if ($resp && property_exists($resp, 'access_token')) {
                $resp = self::saveCrmToken($resp, $user_id);
            } else if (self::isExpired($resp) && $retries == 0) {
                list($is_refresh, $token) = self::getRefreshToken($user_id, $token, true);
                if ($is_refresh) {
                    return self::getLocationAccessToken($user_id, $location_id, $token, $retries + 1);
                }
            }
        }
        return $resp;
    }

    public static function go_and_get_token($code, $type = "", $company_id = null, $loc = null)
    {

        // if ($type == 'reconnect') {
        //     $oldtype = $type;
        //     $type = '';
        // } else if (!empty($type)) {
        //     $type = '1';
        //     $oldtype = $type;
        // }

        $status = false;
        $error = [$status, 'Unable to update'];
        if (is_string($code)) {
            $code = self::crm_token($code, $type);
            //$type = $oldtype ?? $type;
            $code = json_decode($code);
        }
        if ($code) {

            if (!$company_id) {
                //return $error;
            }
            if (property_exists($code, 'access_token')) {
                return [true, self::saveCrmToken($code, $company_id, $loc)];
            }

            if (property_exists($code, 'error_description')) {
                if (strpos($code->error_description, 'refresh token is invalid') !== false) {
                    try {

                        if ($loc) {
                            // $loc->delete();
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
                $error = [$status, $code->error_description];
            }
        }
        return $error;
    }

    public static function urlFix($url)
    {
        return (strpos($url, '?') !== false) ? '&' : '?';
    }

    public static function getRefreshToken($company_id, $location, $is_company = false)
    {

        $loc_time = 30;
        $type = $is_company ? self::$lang_com : self::$lang_loc;
        $user = !$is_company ? ($location->location_id ?? $company_id) : $company_id;
        $loc_block = cache()->lock($type . '_token_refresh' . $user, $loc_time);
        $is_refresh = false;
        $code = $location->refresh_token;
        try {
            list($is_refresh, $location) = $loc_block->block($loc_time, function () use ($code, $company_id, $location) {
                try {
                    $location->urefresh();
                    if ($code != $location->refresh_token) {
                        return [true, $location];
                    }
                    return self::go_and_get_token($code, '1', $company_id, $location);
                } catch (\Throwable $th) {
                    //throw $th;
                }
                return [false, null];
            });
        } catch (\Exception $e) {
        }
        return [$is_refresh, $location];
    }

    public static function agencyV2($company_id, $urlmain = '', $method = 'get', $data = '', $headers = [], $json = false, $token = null, $retries = 0)
    {
        if (!$company_id) {
            return self::$no_record;
        }
        $url = $urlmain;
        if ($token) {
            $company = $token;
        } else {
            $rec['company_id'] = $company_id;
            $company = self::getCrmToken($rec);
            // $company = self::getCrmToken($company_id);
        }
        $access_token = $company->access_token ?? null;
        if (!$access_token || empty($access_token)) {
            return self::$no_token;
        }
        $main_url = static::$base_url;
        $headers['Version'] = static::$version;
        //$companyId = $location->company_id;
        //$methodl = strtolower($method);
        $headers['Authorization'] = 'Bearer ' . $access_token;
        $url1 = $main_url . $url;
        // dd($url1, $method, $data, $headers, $json);
        $cd = self::makeCall($url1, $method, $data, $headers, $json);
        $bd = json_decode($cd);
        // dd($bd );
        // \Log::info(['newfetch',$bd]);
        if (self::isExpired($bd) && $retries == 0) {
            list($is_refresh, $token) = self::getRefreshToken($company_id, $company, true);
            if ($is_refresh) {
                return self::agencyV2($company_id, $url, $method, $data, $headers, $json, $token, $retries + 1);
            }
        }
        return $bd;
    }
    public static function getAgencyToken($company_id, $key = 'company_id')
    {
        return static::getCrmToken([$key => $company_id, 'user_type' => self::$lang_com]);
    }
    public static function getLocationToken($company_id, $location = '')
    {
        $data = ['user_type' => self::$lang_loc];
        if ($company_id) {
            // $data['user_id']=$company_id;
        }
        if ($location != '') {
            $data['location_id'] = $location;
        }

        $tokenData = static::getCrmToken($data);

        if (!$tokenData) {
            $tokenData = CRM::getLocationAccessToken($company_id, $location);
        }

        return $tokenData;
    }
    public static function connectLocation($company_id, $location, $companyToken = null)
    {
        $token = null;
        if (!$companyToken) {
            $companyToken = static::getAgencyToken($company_id);
        }

        if ($companyToken) {
            $token = static::getLocationAccessToken($company_id, $location, 'Location', $companyToken);
        }
        return $token;
    }

    public static function crmV2Loc($company_id = null, $location_id = null, $urlmain = '', $method = 'get', $data = '', $token = '', $json = true)
    {
        if (!$company_id) {
            return self::$no_record;
        }
        if (!$token) {
            $token = static::getLocationToken($company_id, $location_id);
        }

        if (!$token) {
            $token = static::connectLocation($company_id, $location_id);
        }

        if (empty($token) || is_null($token)) {
            return self::$no_token;
        }
        return self::crmV2($company_id, $urlmain, $method, $data, [], $json, $location_id, $token);
    }

    public static function isExpired($bd)
    {
        $status = false;
        try {
            $error = $bd->error ?? "";
            if (!is_string($error)) {
                $error = '';
            }
            $message = $bd->message ?? $bd->error_description ?? "";
            if (!is_string($message)) {
                $message = '';
            }
            $status = (strtolower($error) == 'unauthorized' && stripos(($error), 'authclass') === false) || (isset($message) && strtolower($message) == 'invalid jwt');
        } catch (\Exception $e) {
        }

        return $status;
    }

    public static function crmV2($company_id, $urlmain = '', $method = 'get', $data = '', $headers = [], $json = false, $location_id = '', $location = null, $retries = 0)
    {
        $url = $urlmain;
        if (!$company_id) {
            return self::$no_record;
        }
        if (!$location) {
            $location = self::getLocationToken($company_id, $location_id);
            // dd($location);
            if (!$location) {
                return self::$no_record;
            }
        }

        $main_url = static::$base_url;
        $headers['Version'] = static::$version;
        $access_token = $location->access_token ?? null;

        if (!$access_token) {
            return self::$no_token;
        }
        $location_id = $location->location_id ?? '';
        $company_id = $location->company_id ?? '';
        $methodl = strtolower($method);
        if ((strpos($url, 'templates') !== false || strpos($url, 'tags') !== false || strpos($url, 'custom') !== false || strpos($url, 'tasks/search') !== false) && strpos($url, 'locations/') === false) {
            if (strpos($url, 'custom-fields') !== false) {
                $url = str_replace('-fields', 'Fields', $url);
            }

            if (strpos($url, 'custom-values') !== false) {
                $url = str_replace('-values', 'Values', $url);
            }
            if (strpos($url, 'contacts') === false) {
                $url = 'locations/' . $location_id . '/' . $url;
            }
        } else if ($methodl == 'get') {
            $urlap = self::urlFix($url);
            if (strpos($url, 'location_id=') === false && strpos($url, 'locationId=') === false && strpos($url, 'locations/') === false) {

                if (strpos($url, 'opportunities/search') !== false) {
                    $url .= $urlap . 'location_id=' . $location_id;
                } else {
                    $isinnot = true;
                    $uri = ['users', 'opportunities', 'conversations', 'links', 'opportunities', 'notes', 'appointments', 'tasks', 'free-slots'];
                    foreach ($uri as $k) {
                        if (strpos($url, $k) != false) {
                            $isinnot = false;
                        }
                    }
                    if ($isinnot) {
                        $url .= $urlap . 'locationId=' . $location_id;
                    }
                }
            }
        }

        if (strpos($url, 'contacts') !== false) {
            if (strpos($url, 'q=') !== false) {
                $url = str_replace('q=', 'query=', $url);
            }
            if (strpos($url, 'lookup') !== false) {
                $url = str_replace('lookup', 'search/duplicate', $url);
                if (strpos($url, 'phone=') !== false) {
                    $url = str_replace('phone=', 'number=', $url);
                }
            }
        }
        $lastsl = '/';
        $sep = '?';
        $slash = explode($sep, $url);
        if (strpos($url, 'customFields') === false) {
            if (count($slash) > 1) {
                $urlpart = $slash[0];
                $lastindex = substr($urlpart, -1);
                if ($lastindex != $lastsl) {
                    $urlpart .= $lastsl;
                }
                $url = $urlpart . $sep . $slash[1];
            } else {
                $lastindex = substr($url, -1);
                if ($lastindex != $lastsl) {
                    $url .= $lastsl;
                    $urlmain .= $lastsl;
                }
            }
        }
        $headers['Authorization'] = 'Bearer ' . $access_token;
        if ($json) {
            // $headers['Content-Type'] = "application/json";
        }
        $url1 = $main_url . $url;
        // $usertype = $location->user_type;
        $isFile = strpos($url1, 'upload-file') !== false;
        // dd($isFile);
        $dat = '';
        if (!empty($data)) {
            if ($isFile) {
                $headers['Content-Type'] = 'multipart/form-data';
                $dat = $data;
            } else {
                if (!is_string($data)) {
                    $dat = json_encode($data);
                } else {
                    $dat = $data;
                }
                try {
                    $dat = json_decode($dat) ?? null;
                } catch (\Exception $e) {
                    $dat = (object) $data;
                }
                if (property_exists($dat, 'company_id')) {
                    unset($dat->company_id);
                }
                if (property_exists($dat, 'customField')) {
                    $dat->customFields = $dat->customField;
                    unset($dat->customField);
                }

                if ($methodl == 'post') {
                    $uri = ['businesses', 'calendars', 'contacts', 'conversations', 'links', 'opportunities', 'contacts/bulk/business'];
                    $matching = str_replace('/', '', $urlmain);
                    foreach ($uri as $k) {
                        if ($matching == $k) {
                            if (!property_exists($dat, 'locationId')) {
                                $dat->locationId = $location_id;
                            }
                        }
                    }
                }
                if ($methodl == 'put' && strpos($url, 'contacts') !== false) {
                    if (property_exists($dat, 'locationId')) {
                        unset($dat->locationId);
                    }
                    if (property_exists($dat, 'gender')) {
                        unset($dat->gender);
                    }
                }
            }
        }

        if (strpos($url1, 'status') !== false) {
        }
        // $url = 'https://services.leadconnectorhq.com/medias/upload-file';
        // dd($url1, $method, $dat, $headers, $json);
        $cd = self::makeCall($url1, $method, $dat, $headers, $json);

        // return $cd;
        // dd($cd);
        $bd = json_decode($cd);
        if (self::isExpired($bd) && $retries == 0) {
            list($is_refresh, $location1) = self::getRefreshToken($company_id, $location, false);

            // \Log::info(['1',$location1,$is_refresh]);
            if (!$is_refresh && $location) {
                $cmpid = $location->company_id ?? $company_id;
                $getAgency = static::getAgencyToken($cmpid);

                if ($getAgency) {

                    $location1 = static::connectLocation($cmpid, $location->location_id, $getAgency);

                    if ($location && $location1) {
                        $is_refresh = true;
                    }
                }
            }



            if ($is_refresh) {
                try {
                    if ($location1 && $location && $is_refresh) {
                        gCache::put('token' . $location_id, $location1, 10 * 60);
                    }
                } catch (\Exception $e) {
                }
                return self::crmV2($company_id, $url, $method, $data, $headers, $json, $location_id, $location1, $retries + 1);
            }

            // if (self::ConnectOauth($company)) {
            //     return self::crmV2($company_id, $urlmain, $method, $data, $headers, $json,$location_id,null,$retries+1);
            // }

        }
        return $bd;
    }

    public static function crm_token($code = '', $method = '')
    {
        $md = empty($method) ? 'code' : 'refresh_token';
        if (empty($code)) {
            return $md . ' is required';
        }
        $url = static::$base_url . 'oauth/token';
        $data = [];

        $data['client_id'] = static::getDefault('crm_client_id');
        $data['client_secret'] = static::getDefault('crm_client_secret');
        $data[$md] = $code;
        $data['grant_type'] = empty($method) ? 'authorization_code' : 'refresh_token';
        $headers = ['content-type: application/x-www-form-urlencoded'];
        return self::makeCall($url, 'POST', $data, $headers, false);
    }

    public static function checkNull($pr)
    {
        return is_null($pr) || empty($pr);
    }

    public static function getCompany($user)
    {
        $token = $user->crmauth ?? null;
        $status = false;
        $type = '';
        $detail = '';
        $message = "Connect to agency first";
        $load_more = false;
        if ($token) {
            $type = $token->user_type;

            $query = 'companies/' . $token->company_id;

            if ($type !== self::$lang_com) {
                return ["", ""];
                return response()->json(['status' => $status, 'message' => $message, 'type' => $type, 'detail' => $detail, 'loadMore' => $load_more]);
            } else {
                $detail = self::agencyV2($user->id, $query, 'get', '', [], false, $token);
            }
            try {
                if ($detail && property_exists($detail, 'company')) {
                    return [$detail->company->name, $detail->company->id];
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        return ['', ''];
    }

    public static function getLocationCustomFields($locationId, $token = null)
    {
        if (!$locationId) {
            return [];
        }

        $token = $token ?? self::getLocationToken($locationId);
        $finalCustomFields = [];

        try {
            $endPoint = "locations/$locationId/customFields?model=contact";
            $response = self::crmV2($token->user_id, $endPoint, 'GET', '', [], false, $locationId, $token);

            if ($response && property_exists($response, 'customFields')) {
                $customFields = $response->customFields ?? null;

                $finalCustomFields = collect($customFields)->pluck('name', 'id')->toArray();
            }
        } catch (\Exception $e) {
        }

        return $finalCustomFields;
    }
    public static function evp_bytes_to_key($password, $salt)
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

    public static function decryptSSO($ssoToken)
    {
        $ciphertext = base64_decode($ssoToken);

        if (substr($ciphertext, 0, 8) !== "Salted__") {
            return null; //response()->json(['status' => false]);
        }
        $salt = substr($ciphertext, 8, 8);
        $ciphertext = substr($ciphertext, 16);
        $ssoKey= env('SSO_KEY', null);;
        list($key, $iv) = self::evp_bytes_to_key($ssoKey, $salt);
        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $decrypted_data = null;
        if ($decrypted === false) {
            return null;
        } else {

            $decrypted_data = json_decode($decrypted);
        }
        return $decrypted_data;
    }

}
