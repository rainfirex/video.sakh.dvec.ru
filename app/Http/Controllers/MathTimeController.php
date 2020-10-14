<?php

namespace App\Http\Controllers;

use App\Http\Modules\ArrDateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MathTimeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $upload_folder = 'public/files';
            Storage::putFileAs($upload_folder, $file, $filename);

            $productionArray = []; // Массив всех отформатированных элементов
            $resultArray     = []; // Массив с результатом форматирования Для экспорта в xls
            $frontEndArray   = []; // Для frontend
            $badArray        = [];

            $isInterval    = ($request->input('isInterval') === 'true') ? true : false;
            $startInterval = date_create_from_format('Y-m-d h:i:s', date('Y-m-d h:i:s', strtotime($request->input('startInterval'))));
            $endInterval   = date_create_from_format('Y-m-d h:i:s', date('Y-m-d h:i:s', strtotime($request->input('endInterval'))));
             //date": "2020-08-11 08:08:22.000000

            ini_set('memory_limit', '2024m');

            /// Посчитаная информация
            $days    = 0;
            $hours   = 0;
            $minutes = 0;
            $seconds = 0;
            $isFormatFull = true;
            ///

            $data = ArrDateTime::readXLS($filename);

            $rows = ArrDateTime::getColumnData($data, 'I');

            $countRowXLS = 'Количество строк из xls файла: ' . (count($data) - 1);

            $data = []; // Убиваем все что прочитали с файла

            // Sergey function
//            ArrDateTime::test1($filename);exit;

            try {
                // Форматирование и сбор в 1н массив : $productionArray "С" => "ПО"
                ArrDateTime::formatArray($rows, $productionArray, $badArray);

                ArrDateTime::sergeyfunc($productionArray);exit;

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Цикл форматирование. ' . $e->getMessage(),
                    'line'    => 'Line in code: ' . $e->getLine()
                ]);
            }

            $rows = []; // Убиваем все что сформировано


            try {
                //Общая дата
                $commonDatetime = new \DateTime('00-00-00');
                $fulltime = clone $commonDatetime;

                for ($i = 0; $i < count($productionArray); $i++) {
                  if(!isset($productionArray[$i])){ return; }// Если массив отсутствует, то выход из цикла
                    $s = $productionArray[$i]['start'];
                    $sNext = isset($productionArray[$i+1]['start']) ? $productionArray[$i+1]['start'] : null;
                    $d = $productionArray[$i]['end'];
                    $dateStart = new \DateTime($s);
                    $dateStartNext = (!empty(new \DateTime($sNext))) ? new \DateTime($sNext) : null;
                    $dateEnd = new \DateTime($d);

                    if ( $isInterval ) {
                        $isFormatFull = false;
                        if ($dateStart > $startInterval &&  $dateStart <= $endInterval) {//&& $dateEnd < $endInterval
                            $resultArray[] = ArrDateTime::diffDateTime($dateStart, $dateEnd, $dateStartNext, $days, $hours, $minutes, $seconds, $frontEndArray, $commonDatetime);
                        }
                    } else {
                        $isFormatFull = true;
                        $resultArray[] = ArrDateTime::diffDateTime($dateStart, $dateEnd, $dateStartNext, $days, $hours, $minutes, $seconds, $frontEndArray, $commonDatetime);
                    }
                }


                $allDateTime = $fulltime->diff($commonDatetime)->format("%Y-%M-%d %H:%I:%S");
                // new \DateTime($allDateTime)
                $time = strtotime($allDateTime); ;  // you have 1299446702 in time

                $year = $time/31556926 % 12;  // to get year
                $week = $time / 604800 % 52;  // to get weeks
                        //1299446702
                $hour = $time / 3600 % 24;    // to get hours
//                $hour = $time / 360 % 24;    // to get hours
                $minute = $time / 60 % 60;    // to get minutes
                $second = $time % 60;
                $test = sprintf("год %s, недель %s, часов %s, минут %s, секунд %s", $year, $week, $hour, $minute, $second);

            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Цикл вычисление времени. ' . $e->getMessage(),
                    'line' => $e->getLine()
                ]);
            }

            $countRowProd = 'Количество строк после форматирования: ' . (count($productionArray) - 1);


            $linkXls = ArrDateTime::saveXLS($resultArray);

            return response()->json([
                'success'        => true,
                'countXLS'       => $countRowXLS,
                'countProd'      => $countRowProd,
                'badArray'       => $badArray,
                'resultArray'    => $frontEndArray,
                'result'         => ['days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds],
                '$isInterval'    => $isInterval,
                '$startInterval' => $startInterval,
                '$endInterval'   => $endInterval,
                'isFormatFull'   => $isFormatFull,
                '$linkXls'       => $linkXls,
                'test'           => $test
            ]);
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }
}
