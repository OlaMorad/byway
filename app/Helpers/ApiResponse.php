<?php

namespace App\Helpers;
use Illuminate\Http\JsonResponse;
class ApiResponse 
{
    static function sendResponse($code = 200 , $msg = null , $data = []){
        $response = [
            'status' => $code,
            'message' => $msg,
            'data' => $data
        ];
        return response()->json($response, $code);
    }
}