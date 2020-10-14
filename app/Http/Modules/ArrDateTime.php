<?php
namespace App\Http\Modules;

use App\Http\Modules\DocumentExcel;
use Illuminate\Support\Facades\Storage;
class ArrDateTime
{
    /**
     * Чтение xls
     * @param $filename
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public static function readXLS($filename): array {
        require_once(app_path() . '/Http/Modules/PHPExcel/PHPExcel.php');
        $data = [];

        $path = storage_path('app/public/files/' . $filename);

        $type = \PHPExcel_IOFactory::identify($path);
        $objReader = \PHPExcel_IOFactory::createReader($type);
        $objPHPExcel = $objReader->load($path);
        $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();

        foreach ($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            foreach ($cellIterator as $cell) {
                $data[$row->getRowIndex()][$cell->getColumn()] = $cell->getCalculatedValue();
            }
        }

        return $data;
    }

    public static function saveXLS($resultArray) {

        $doc = DocumentExcel::Init();
        $doc->setActiveSheet(0)->setTitle('Экспорт');
        $doc->writeTextCell('A1', 'Учет времени')->mergeCells('A1:B1')
            ->alignCell('center')->setBorder()->setFontSize(17);

        $doc->writeTextCell('A2','Интервал')->setFontSize(15)->alignCell('center');
        $doc->writeTextCell('B2','Пауза перед следующей записью')->setFontSize(15)->alignCell('center');
//        $doc->setBorder('thin','A2:J2');
        $i = 3;

        foreach ($resultArray as $item) {
            $doc->writeTextCell('A' . $i, $item['a']);
            $doc->writeTextCell('B' . $i, $item['b']);
            $i++;
        }

        $doc->setAutoSize(['A', 'B']);

        $path = Storage::disk('public')->path('result.xls');
        $doc->save($path);

        return asset(Storage::url('result.xls'));
    }

    /**
     * Расчет времени
     * @param \DateTime $dateStart
     * @param \DateTime $dateEnd
     * @param \DateTime $dateStartNext
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @param array $frontEndArray
     * @return array
     */
    public static function diffDateTime(\DateTime $dateStart,\DateTime $dateEnd, \DateTime $dateStartNext, int &$days, int &$hours, int &$minutes, int &$seconds, array &$frontEndArray, \DateTime &$commonDatetime): array {
        $result = $dateEnd->diff($dateStart);
        $commonDatetime->add($result);


        $resultStopTime = null;
        $stopTimeText = '';

        $days += $result->d;
        $hours += $result->h;
        $minutes += $result->i;
        $seconds += $result->s;

        if (isset($dateStartNext)) {
            $resultStopTime = $dateEnd->diff($dateStartNext);
            $stopTimeText = sprintf("%s дней, %s:%s:%s", $resultStopTime->d, $resultStopTime->h, $resultStopTime->i, $resultStopTime->s);
        }

        $frontEndArray[] = [
            'dataS'    => $dateStart->format('d-m-Y h:i:s'),
            'dataE'    => $dateEnd->format('d-m-Y h:i:s'),
            'days'     => $result->d,
            'hours'    => $result->h,
            'minutes'  => $result->i,
            'seconds'  => $result->s,
            'stopDays' => $resultStopTime->d,
            'stopHours'=> $resultStopTime->h,
            'stopMinutes' => $resultStopTime->i,
            'stopSeconds' => $resultStopTime->s
        ];


        $workTimeText = sprintf("(С %s - ПО %s) = %s дней, %s:%s:%s", $dateStart->format('d-m-Y h:i:s'),  $dateEnd->format('d-m-Y h:i:s'), $result->d, $result->h, $result->i, $result->s);
        return ['a' => $workTimeText, 'b' => $stopTimeText];
    }

    /**
     * Форматирование и сбор в 1н массив: $productionArray
     * @param array $rows
     * @param array $productionArray
     * @param array $badArray
     */
    public static function formatArray(array $rows, array &$productionArray, array &$badArray) {
        $productionArray = [];
        $badArray = [];
        foreach ($rows as $row) {
            $test = explode(',', $row);
            if (is_array($test) && !empty($test[0]) && !empty($test[1])) {
                $start = trim(str_replace('С:', '', $test[0]));
                $end = trim(str_replace('ПО:', '', $test[1]));
                $productionArray[] = ['start' => $start, 'end' => $end];
            } else {
                $badArray[] = $row;
            }
        }
    }

    /**
     * Массив всех возможных элементов из колонки
     * @param $data
     * @param string $columnName
     * @return array
     */
    public static function getColumnData($data, $columnName = 'F'): array {
        $rows = [];
        foreach ($data as $column) {
            //Если в колонки ; тогда там несколько интервалов дат
            if (preg_match('/;/', $column[$columnName])) {
                //Разбиваем данные колонки на еще один массив
                $columnArray = explode(';', $column[$columnName]);
                // Перебор массива и занесение интервалов дат в общий массив
                foreach ($columnArray as $r) {
                    $rows[] = $r;
                }
            } else
                //Если в колонки нет символа ';', то это один интервал даты
                $rows[] = $column[$columnName];
        }
        return $rows;
    }

    /**
     * Sergey function
     * @param $filename
     */
    public static function test1($filename) {
        require_once(app_path() . '/Http/Modules/SimpleXLSX.php');
        $path = storage_path('app/public/files/' . $filename);
        ini_set('memory_limit', '2024m');
        if ( $xlsx = \SimpleXLSX::parse($path) ) {

            $i = 0;
            foreach ($xlsx->rows() as $elt) {
                if ($i == 0) {
                    $arr[]= $elt[8];
                }
                else
                {
                    $arr[]= $elt[8];
                }

                $i++;
            }

        } else {
            echo \SimpleXLSX::parseError();
        }

//        dd(count($arr));

        $c=0;
        $i = 0;

        foreach ($arr as $key => $value)
        {
//            Пример  $value строка
//            """
// С: 02.08.2020 21:36:55, ПО: 05.08.2020 08:38:21;
// С: 10.08.2020 08:06:14, ПО: 10.08.2020 17:41:20;
// С: 11.08.2020 04:26:14, ПО: 11.08.2020 04:56:43;
// С: 11.08.2020 05:27:22, ПО: 25.08.2020 10:03:11;
// С: 26.08.2020 00:49:03, ПО: 27.08.2020 06:48:32;
// С: 27.08.2020 19:51:22, ПО: 08.09.2020 12:50:30
//"""
            $h=$m=$s=$sN=0;
            $e = array();
            $value=  str_replace("\r\n","",$value);
            $value = str_replace("С: ","",$value);
            $value = str_replace("ПО: ","",$value);
            $value = str_replace(".","-",$value);
            $value = str_replace('"',"",$value);
            $value = str_replace("_x000D_","",$value);
            $myOutB = explode(";",$value);
//            if ($c==2)dd($myOutB);

//          $myOutB  =  [
//  0 => " 02-08-2020 21:36:55, 05-08-2020 08:38:21"
//  1 => """10-08-2020 08:06:14, 10-08-2020 17:41:20//    """
//  2 => """11-08-2020 04:26:14, 11-08-2020 04:56:43//    """
//  3 => """11-08-2020 05:27:22, 25-08-2020 10:03:11//    """
//  4 => """26-08-2020 00:49:03, 27-08-2020 06:48:32//    """
//  5 => """27-08-2020 19:51:22, 08-09-2020 12:50:30//    """
//]

            foreach ($myOutB as $key =>$value)
            {
                $myOutC = explode(",", $value);

                //!!!!!!!!!!!!
//                if ($c == 2)dd($myOutC);

                foreach ($myOutC as $k=>$value){ // $value = arra c -po

//                    if ($c == 2)  dd($value);

                    $value = trim($value);
                    if (isset($value)){
                        $a = trim($value);
                    }
                    else $a=0;

                    if (isset($e[0]) & ($a!="")){
                        $d3 = strtotime(trim(end($e)));
                        $d4 = strtotime(trim($a));
                    } else $d3=$d4=0;

                    if (isset($myOutC[1])){
                        $b = trim($myOutC[1]); //END
                    }
                    else $b=0;
                    $d1=strtotime(trim(($a)));
                    $d2=strtotime(trim(($b)));
                    $seconds = abs($d2 - $d1);
                    $secondsN = abs($d3 - $d4);
                    $s += $seconds;
                    $sN += $secondsN;
                    $e[]=$b;
                }
                $i++;
            }

//            if ($c == 2)  dd($s, $sN);

            $hours = floor($s/3600);
            $minutes = floor($minutes = ($s/3600 - $hours)*60);
            $seconds = ceil(($minutes - floor($minutes))*60);
            echo $c++."   ".($hours).":".floor($minutes).":".$seconds." --> ";

            $hoursN = floor($sN/3600);
            $minutesN = floor($minutesN = ($sN/3600 - $hoursN)*60);
            $secondsN = ceil(($minutesN - floor($minutesN))*60);
            echo ($hoursN).":".floor($minutesN).":".$secondsN."\r\n";
        }
//        var_dump($i);exit;
    }

    public static function sergeyfunc(array $productionArray) {

        $c=0;

        $i = 0;

        $arr_pos = [];

        foreach ($productionArray as $item) {
            //!!!!!!!!!!!!!!!!!!!!!!!!!!
            // if ($c == 0)dd($item);

            $s=0;
            $sN=0;

            foreach ($item as $k => $value) { // $value = arra c -po
                $value = str_replace(".","-", $value);

//                if ($c == 0)dd($value);
//                dd(isset($arr_pos[0]) && ($value!=""));

                if (isset($arr_pos[0]) && ($value!="")){
                    $d3 = strtotime(trim(end($arr_pos)));
                    $d4 = strtotime(trim($value));
                } else $d3=$d4=0;

                $item_po = trim(str_replace(".","-", $item['end']));


                $d1 = strtotime(trim(($value)));
                $d2 = strtotime(trim(($item_po)));

                $seconds  = abs($d2 - $d1);
                $secondsN = abs($d3 - $d4);

                $s += $seconds;
                $sN += $secondsN;
                $arr_pos[] = $item_po;
            }

//            if ($c == 4) dd($arr_pos, $i);
//            if ($c == 0)dd($s,$sN );

            $i++;

            $hours = floor($s/3600);
            $minutes = floor($minutes = ($s/3600 - $hours)*60);
            $seconds = ceil(($minutes - floor($minutes))*60);
            echo $c++."   ".($hours).":".floor($minutes).":".$seconds." --> ";

            $hoursN = floor($sN/3600);
            $minutesN = floor($minutesN = ($sN/3600 - $hoursN)*60);
            $secondsN = ceil(($minutesN - floor($minutesN))*60);
            echo ($hoursN).":".floor($minutesN).":".$secondsN."\r\n";
        }
    }

}