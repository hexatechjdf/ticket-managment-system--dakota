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
        return view('admin.setting.index');
    }

    public function save(Request $request)
    {
        // dd($request->all());
        foreach ($request->except('_token') as $key => $value) {
            // if ($request->hasFile($key)) {
            //     $value = uploadFile($value, 'Upload/Settings', $key . time());
            // }
            save_my_settings($key, $value);
        }

        return redirect()->back()->with('success', 'Saved');
    }
}
