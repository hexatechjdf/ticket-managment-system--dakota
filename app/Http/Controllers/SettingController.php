<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return;
        return view('admin.setting.index');
    }

    public function save(Request $request)
    {
        // dd($request->all());
        foreach ($request->except(['_token', 'user_password']) as $key => $value) {
            save_my_settings($key, $value);
        }


        $password = $request->user_password;
        if ($password) {
            $authUser = auth()->user();
            $authUser->password = \Hash::make($password);
            $authUser->save();
        }

        return redirect()->back()->with('success', 'Saved');
    }
}
