<?php

namespace App\Http\Controllers;

use App\Exports\TemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{

    public function success(
        mixed $data,
        ?int $status = 200,
        ?array $headers = [])
    {


        if((is_object($data) && property_exists($data, 'data')) || (is_array($data) && isset($data['data']))){
           $data = collect($data)->toArray();
            return response()->json([
                'status' => 'success',
                ...$data
            ], $status, $headers);
        }
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], $status, $headers);
    }

    public function Ok(string $message, ?int $status = 200, ?array $headers = [])
    {
        return response()->json([
            'status' => 'success',
            'message' => $message
        ], $status, $headers);
    }



    public function notFound(mixed $message, array $headers = [])
    {
        return response()->json([
            'status' => 'failed',
            'message' => $message
        ], Response::HTTP_NOT_FOUND, $headers);
    }

    public function error(mixed $message, ?int $status, array $headers = [])
    {
        return response()->json([
            'status' => 'failed',
            'error' => [
                'message' => $message
            ]
        ], $status, $headers);
    }



}