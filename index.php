<?php


use ksr\classes\FTPConnector;

include __DIR__ . '/autoload.php';


 function serialRenaimer(array $ftp) {
     $log = [];
     $ftp_stream = $ftp['ftp_stream'];
     $opts = $ftp['opts'];
     $fullPath = "ftp://{$opts['login']}:{$opts['password']}@{$opts['host']}/{$opts['path']}";
     $serials = ftp_nlist($ftp_stream, '');
     foreach ($serials as $serial) {
         if (is_dir($fullPath . '/' . $serial) && !strpos($serial, '(R)')) {
             //заходим в папку сериала
             ftp_chdir($ftp_stream, $serial);
             //получаем список сезонов или серий
             $seasons = ftp_nlist($ftp_stream, '');
             $tag = seasonsRenaimer($ftp_stream, $fullPath, $seasons, $serial);
             if ($tag !== 2)
                 ftp_cdup($ftp_stream);
             if ($tag !== -1)
                 ftp_rename($ftp_stream, $serial, $serial . ' (R)');
         }
     }
     return logger($log);
 }

 function seasonsRenaimer($ftp_stream, $fullPath, $seasons, $serial) {
     // проверяем файлы перед нами или папки
     if (is_file($fullPath . '/' . $serial . '/' . $seasons[1])) {
         if (preg_match('/[^\d](\d{1,2})[^\d]/', $serial, $seasonNum)) {
             $seasonNum = $seasonNum[1];
         } else
             $seasonNum = '01';
         if (strlen($seasonNum) == 1)
             $seasonNum = '0' . $seasonNum;
         tvSeriesRenamer($ftp_stream, $fullPath . '/' . $serial, $serial, $seasonNum, TRUE);
         return 2;
     }
     foreach ($seasons as $season) {
         $seasonFullPath = $fullPath . '/' . $serial . '/' . $season;
         //если это сезон
         if (is_dir($seasonFullPath)) {
             preg_match('/\d{1,2}/', $season, $seasonNum);
             if (mb_strlen($seasonNum[0]) == 1)
                 $seasonNum[0] = '0' . $seasonNum[0];
             $newName = 'Season ' . trim($seasonNum[0]);
             $result = tvSeriesRenamer($ftp_stream, $seasonFullPath, $season, $seasonNum[0]);
             if ($result) {
                 $log[$serial][] = "\t$season => $newName\r\n";
                 $log[$serial][] = $result;
                 $log[$serial][] = '------------------------------------------' . "\r\n";
                 if ($season !== $newName)
                     ftp_rename($ftp_stream, $season, $newName);
             }else {
                 return -1;
             }
         }
     }
     return 1;
 }

 function tvSeriesRenamer($ftp_stream, $seasonFullPath, $seasonName, $seasonNum, $oneSeasonSerial = FALSE) {
     $output = '';
     if (!$oneSeasonSerial)
         ftp_chdir($ftp_stream, $seasonName);
     if (is_dir($seasonFullPath)) {
         $series = ftp_nlist($ftp_stream, '');
     }
     sort($series);
     foreach ($series as $ep) {
         if (is_file($oldEpFullPath = $seasonFullPath . '/' . $ep)) {
             //пропускаем недокачанные серии
             if (($extension = pathinfo($ep)['extension']) == 'part') {
                 ftp_cdup($ftp_stream);
                 return FALSE;
             }
             if (preg_match('/\d{1,2}[-|_]\d{1,2}/', $ep, $match)) {
                 $epNewName = epRenamer(str_replace('_', '-', $match[0]), $seasonNum, $extension);
             } else if (preg_match('/s(\d{1,2})e(\d{1,2})/i', $ep, $match)) {
                 $epNewName = epRenamer($match[2], $seasonNum, $extension);
             } else if (preg_match('/(\d{1,2})[^\d]/', $ep, $match)) {
                 $epNewName = epRenamer($match[1], $seasonNum, $extension);
             }else{
                 continue;
             }
             if ($ep !== $epNewName) {
                 ftp_rename($ftp_stream, $ep, $epNewName);
                 $output .= "\t\t{$ep} => {$epNewName}\r\n";
             }
         }
     }
     ftp_cdup($ftp_stream);
     return TRUE;
 }

 function epRenamer($epNum, $seasonNum, $extension) {
     $securityExt = ['avi', 'mkv', 'mov', 'wma', 'mp4', 'flv', 'm4v', 'ts', 'srt', 'ssa', 'ass'];
     if (!in_array($extension, $securityExt)){
         return;
     }
     if (strlen($epNum) == 1)
         $epNum = '0' . $epNum;
     return "s{$seasonNum}e{$epNum}.{$extension}";
 }

 function logger(array $log) {
     if (empty($log))
         return FALSE;
     $output = '============================' . "\r\n";
     $output .= date('H:i:s d-m-Y') . "\r\n";
     $output .= '------------------------------------------' . "\r\n";
     foreach ($log as $l)
         $output .= (is_array($l)) ? implode(PHP_EOL, $l) : $l;
     $output .= '============================' . "\r\n";
     return $output;
 }

 try {

//     $opts = json_decode(file_get_contents('security/creds.json'), TRUE);
//     if (!$opts)
//         throw new Exception('Не могу получить настройки для инициализации скрипта');
//     $prepareFtpStream = ftpStreamPreparer($opts);

//     $log = serialRenaimer($prepareFtpStream);
     $log = serialRenaimer(FTPConnector::init()->getOpts());
//     if ($log)
     file_put_contents('log.txt', date('H:i:s d-m-Y') . PHP_EOL, FILE_APPEND);
 } catch (Exception $ex) {
     echo $ex->getMessage();
 }