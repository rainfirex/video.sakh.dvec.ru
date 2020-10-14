<?php


namespace App\Http\Controllers;

use App\Http\Modules\SergFunc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewMathController extends Controller
{
    public function index(Request $request) {
        if (!$request->hasFile('file')){
            return response()->json([
                'success' => false,
                'message' => 'Нет файла для обработки.'
            ]);
        }

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $upload_folder = 'public/files';
        Storage::putFileAs($upload_folder, $file, $filename);

        $isInterval    = ($request->input('isInterval') === 'true') ? true : false;
        $startDateTime = date_create_from_format('Y-m-d h:i:s', date('Y-m-d h:i:s', strtotime($request->input('startInterval'))));
        $endDateTime   = date_create_from_format('Y-m-d h:i:s', date('Y-m-d h:i:s', strtotime($request->input('endInterval'))));
        //date": "2020-08-11 08:08:22.000000

        ini_set('memory_limit', '2024m');

        // Массив с дынными
        $data = SergFunc::readXLS($filename);
        $frontEndArray = SergFunc::run($data, $startDateTime, $endDateTime);
        $linkXls = asset(Storage::url('result.xls'));
        $countResult = count($frontEndArray);
        $countXLSRows = count($data);

        return response()->json([
            'success'     => true,
            'countXLS'    => $countXLSRows,
            'countResult' => $countResult,
            '$linkXls'    => $linkXls,
            'resultArray' => $frontEndArray,
            '$isInterval' => $isInterval,
            '$startInterval' => $startDateTime,
            '$endInterval'   => $endDateTime
        ]);
    }
}