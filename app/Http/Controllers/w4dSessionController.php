<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests;

class w4dSessionController extends Controller
{
    public function GetSessionState()
    {
		if (!Auth::check())
		{
			$response["status"] = "guest";
			return response()->json($response);
		}
		
		$response["logged_in"] = "content";
		$response["strName"] = Auth::user()->name;
		
		return response()->json($response);
	}
}
