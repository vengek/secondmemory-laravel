<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{
    public function auth(Request $request)
    {
        $userId = $request->input('id');
        $_SESSION['userId'] = $userId;
        return $userId;
    }
}
