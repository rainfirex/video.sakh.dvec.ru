<?php namespace App\Http\Modules {

    use Illuminate\Support\Facades\Storage;
    use App\Http\Modules\DocumentExcel;

    class SergFunc
    {
        /**
         * Чтение файла
         * @param string $filename
         * @return array
         */
        public static function readXLS(string $filename): array {
            require_once(app_path() . '/Http/Modules/SimpleXLSX.php');
            $path = storage_path('app/public/files/' . $filename);

            $data = [];

            if ( $xlsx = \SimpleXLSX::parse($path) ) {

                $i = 0;
                foreach ($xlsx->rows() as $elt) {
                    if ($i == 0) {
                        // $elt[8] - столбец с нужными данными
                        $data[]= $elt;
                    }
                    else {
                        $data[]= $elt;
                    }
                    $i++;
                }
            } else {
                echo \SimpleXLSX::parseError();
            }

            return $data;
        }

        public static function run(array $data,\DateTime $startDate, \DateTime $endDate): array {
            $start = $startDate->getTimestamp();
            $end = $endDate->getTimestamp();

            $c=0;
            $resultCSV = [];
            $resultFront = [];
            $resultForXLS = [];
            foreach ($data as $items) {
                $h=$m=$s=$sN=0;
                $e = array();
                $item =  str_replace("\r\n","",$items[8]);
                $item = str_replace("С: ","",$item);
                $item = str_replace("ПО: ","",$item);
                $item = str_replace(".","-",$item);
                $item = str_replace('"',"",$item);
                $item = str_replace("_x000D_","",$item);
                $myOutB = explode(";",$item);

                $tmpResultDateTime = null;
                $f = null;

                foreach ($myOutB as $arrayInterval) {

                    $tmpResultDateTime = new \DateTime('00:00');
                    $f = clone $tmpResultDateTime;

                    $myOutC = explode(",", $arrayInterval);

                    foreach ($myOutC as $k => $v){

                        $tmpValue = trim($v);
                        $a = (isset($tmpValue))? $tmpValue : 0;

                        if (isset($e[0]) & ($a!="")){
                            $d3 = strtotime(trim(end($e)));
                            $d4 = strtotime(trim($a));
                            if ($d3<$start) { $d3=$start;}
                            if ($d4>$end) { $d2 = $end;}
                            $myN = self::dataCheck($start,$end,$d3,$d4);
                            if ($myN == false) break;
                        } else $d3=$d4=0;

                        if (isset($myOutC[1])){
                            $b = trim($myOutC[1]);
                        }
                        else $b=0;
                        $d1=strtotime(trim(($a)));
                        $d2=strtotime(trim(($b)));
                        if ($d1<$start) { $d1=$start; }
                        if ($d2>$end) { $d2 = $end; }

                        $myS = self::dataCheck($start, $end, $d1, $d2);

                        if ($myS == false) break;

                        $seconds = abs($d2 - $d1);
                        $secondsN = abs($d3 - $d4);
                        $s += $seconds;
                        $sN += $secondsN;
                        $e[] = $b;

                        $startDateTime = date_create_from_format('Y-m-d h:i:s', date('Y-m-d h:i:s', $start));
                        $endDateTime = date_create_from_format('Y-m-d h:i:s', date('Y-m-d h:i:s', $end));

                        $interval = $endDateTime->diff($startDateTime);
                        $tmpResultDateTime->add($interval);
                    }
                }

                $hours = floor($s/3600);
                $minutes = floor($minutes = ($s/3600 - $hours)*60);
                $seconds = ceil(($minutes - floor($minutes))*60);
                //$c++.";".($hours).":".floor($minutes).":".$seconds.";";

                $hoursN = floor($sN/3600);
                $minutesN = floor($minutesN = ($sN/3600 - $hoursN)*60);
                $secondsN = ceil(($minutesN - floor($minutesN))*60);
                //echo ($hoursN).":".floor($minutesN).":".$secondsN."\r\n";


                $regEx = '/\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}/im';
                preg_match_all($regEx, $items[8], $matches);

                $countMatches = count($matches[0]);
                if ($countMatches >= 1) {

                    $strTime = ($hours).":".floor($minutes).":".$seconds.";".($hoursN).":".floor($minutesN).":".$secondsN;

                    $resultDateInterval = $tmpResultDateTime->diff($f);

                    $resultCSV[]   = $c++.";".$strTime;

                    $resultFront[] = [
                        'ip'         => $items[0],
                        'address'    => $items[1],
                        'sourceMin' => str_replace(',', ' -', str_replace('\n', '', implode(';', array_slice($myOutB, 0, 6)))).(count($myOutB) > 6 ? ' .....Слишком много элементов.....' : ''),
                        'sourceMax' => str_replace(',', ' -', str_replace('\n', '', implode(';', $myOutB))),
                        'timeCommon' => ($hours). ":".floor($minutes). ":".$seconds,
                        'timeStop'   => ($hoursN).":".floor($minutesN).":".$secondsN,
                        'timeResultString' => $strTime
                    ];
                }
                $resultForXLS[] = [
                    'IP_Records'    => $items[0],
                    'short_name'    => $items[1],
                    'name_canal'    => $items[2],
                    'ident_canal'   => $items[3],
                    'status_trans'  => $items[4],
                    'deep_archive'  => $items[5],
                    'status_save_archive' => $items[6],
                    'data_start_archive'  => $items[7],
                    'periods_save'  => $items[8],
                    'isCanalMB'     => $items[9],
                    'timeSave'      => ($hours). ":".floor($minutes). ":".$seconds,
                    'timeNotSave'   => ($hoursN).":".floor($minutesN).":".$secondsN
                ];

            }

            self::saveXLS($resultForXLS);
//            self::myGiStoFile($resultCSV);

            return $resultFront;
        }

        private static function dataCheck($start, $end, $data1, $data2){
            if ($end >= $data1) {
                if ($data1 >= $start) {

                } else {return false;}

                if ($data1>$data2) {
                    return false;
                } else { return true;}
            }
        }

        private static function myGiStoFile(array $arrayResult) {
            $path = Storage::disk('public')->path('result.xls');

            $fp = fopen($path, 'w+');

            foreach ($arrayResult as $key => $value){
                fwrite($fp,$value."\r\n");
            }
            fclose($fp);
        }

        public static function saveXLS($array) {

            $doc = DocumentExcel::Init();
            $doc->setActiveSheet(0)->setTitle('Экспорт');
//            $doc->writeTextCell('A1', 'Учет времени')->mergeCells('A1:B1')
//                ->alignCell('center')->setBorder()->setFontSize(17);

//            $doc->writeTextCell('A2','Интервал')->setFontSize(15)->alignCell('center');
//            $doc->writeTextCell('B2','Пауза перед следующей записью')->setFontSize(15)->alignCell('center');
            $doc->setBorder('thin','A1:L1');

            $i = 1;

            foreach ($array as $item) {
                $doc->writeTextCell('A' . $i, $item['IP_Records']);
                $doc->writeTextCell('B' . $i, $item['short_name']);
                $doc->writeTextCell('C' . $i, $item['name_canal']);
                $doc->writeTextCell('D' . $i, $item['ident_canal']);
                $doc->writeTextCell('E' . $i, $item['status_trans']);
                $doc->writeTextCell('F' . $i, $item['deep_archive']);
                $doc->writeTextCell('G' . $i, $item['status_save_archive']);
                $doc->writeTextCell('H' . $i, $item['data_start_archive']);
                $doc->writeTextCell('I' . $i, $item['periods_save']);
                $doc->writeTextCell('J' . $i, $item['isCanalMB']);
                $doc->writeTextCell('K' . $i, $item['timeSave']);
                $doc->writeTextCell('L' . $i, $item['timeNotSave']);
                $i++;
            }

            $doc->setAutoSize(['A', 'B', 'K', 'L']);

            $path = Storage::disk('public')->path('result.xls');
            $doc->save($path);
        }
    }
}


