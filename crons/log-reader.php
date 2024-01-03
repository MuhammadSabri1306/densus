<?php
try {

$pathTarget = $_POST['logpath'] ?? null;
$logFormat = $_POST['format'] ?? 'bunyan';
$dateTarget = $_POST['date'] ?? date('Y-m-d');
$hoursTarget = $_POST['hours'] ?? null;
$minutesTarget = $_POST['minutes'] ?? null;

$isSearching = isset($_POST['submit'], $_POST['logpath'], $_POST['date']);
$isFileNotFound = true;
$logContents = [];

$pathOptions = [
    '/node-crons/src/opnimus-alerting-port-v2/logs/cron.log',
    '/node-crons/src/opnimus-alerting-port-v2/logs/debug.log',
    '/node-crons/src/opnimus-alerting-port-v2/logs/error.log',
    '/node-crons/src/opnimus-alerting-port-v2/logs/info.log',
    '/node-crons/src/opnimus-alerting-port-v2/logs/warn.log',
];

$formatOptions = [ 'bunyan' ];

$hoursOptions = array_map(function($hour) {
    $hour = strval($hour);
    return strlen($hour) < 2 ? "0$hour" : $hour;
}, range(0, 23));

$minutesOptions = array_map(function($minute) {
    $minute = strval($minute);
    return strlen($minute) < 2 ? "0$minute" : $minute;
}, range(1, 60));

class Collection
{
    private $data;

    public function __construct(array $data)
    {
        if(!Collection::isAssocArray($val)) {
            return;
        }

        foreach($data as $key => $val) {
            if(Collection::isAssocArray($val)) {
                $val = new Collection($val);
            }
            $this->data[$key] = $val;
        }
    }

    public static function isAssocArray(mixed $data)
    {
        return is_array($data) && array_values($data) !== $data;
    }

    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function get($key = null, $defaultVal = null)
    {
        if(!$key) return $this->data;
        return $this->data[$key] ?? $defaultVal;
    }
}

class BunyanReader
{
    private $data = [];
    private $filterDate;

    public function __construct(DateTime $filterDate)
    {
        $this->filterDate = $filterDate;
    }

    public function readLine(string $line) {
        if(strlen($line) < 1) return;
        $searchDateTime = $this->filterDate->format('Y-m-d\TH:i:sp');
        if(strpos($line, "\"time\":\"$searchDateTime") !== false) {
            $data = json_decode($line, false, 512, JSON_INVALID_UTF8_IGNORE);
            if(Collection::isAssocArray($data)) {
                $data = new Collection($data);
                if($data->time) $data->time = BunyanReader::formatTimestamp($data->time);
                array_push($this->data, $data);
            }
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public static function formatTimestamp(string $timestamp, string $format = 'Y-m-d H:i:s')
    {
        $timestamp = new DateTimeImmutable($timestamp);
        return $timestamp->format($format);
    }
}

if($isSearching) {

    $filePath = __DIR__ . $pathTarget;
    if(!file_exists($filePath)) {
        $isFileNotFound = true;
        var_dump($filePath);
    } else{

        if($hoursTarget && $minutesTarget) {
            $filterDate = DateTime::createFromFormat('Y-m-d H:i', "$dateTarget $hoursTarget:$minutesTarget");
        } elseif($hoursTarget) {
            $filterDate = DateTime::createFromFormat('Y-m-d H', "$dateTarget $hoursTarget");
        } else {
            $filterDate = DateTime::createFromFormat('Y-m-d', $dateTarget);
        }

        if($logFormat == 'bunyan') {
            $reader = new BunyanReader($filterDate);
        }
        $searchDateTime = $filterDate->format('Y-m-d\TH:i:sp');

        $handle = fopen($filePath, "r");
        for($linePos = 0; fseek($handle, $linePos, SEEK_END) !== -1; $linePos--) {
            $line = trim(fgets($handle));
            if(strlen($line) > 0) {
                $reader->readLine($line);
            }
        }
        fclose($handle);

        $logContents = $reader->getData();

    }

}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Densus Log Reader</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous">
</head>
<body>
    <nav class="py-5 my-5 shadow">
        <div class="container">
            <h1 class="display-5 fw-bold text-body-emphasis text-center mb-4">Log Reader</h1>
            <form method="post">
                <div class="row g-3 align-items-end">
                    <div class="col">
                        <label for="selectPath" class="form-label">Log File Path</label>
                        <select name="logpath" class="form-select mb-3" id="selectPath" required>
                            <option>Pilih Target Path</option><?php

                            foreach($pathOptions as $path):
                                ?><option <?=( $path == $pathTarget ? 'selected' : '' )?> value="<?=$path?>">
                                    <?=$path?>
                                </option><?php
                            endforeach;

                        ?></select>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label for="inputDate" class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control mb-3" id="inputDate" value="<?=$dateTarget?>" required>
                    </div>
                    <div class="col-6 col-md-2 col-lg-1">
                        <label for="selectHours" class="form-label">Jam</label>
                        <select name="hours" class="form-select mb-3" id="selectHours">
                            <option value="">Pilih</option><?php

                            foreach($hoursOptions as $hour):
                                ?><option <?=( $hour == $hoursTarget ? 'selected' : '' )?> value="<?=$hour?>">
                                    <?=$hour?>
                                </option><?php
                            endforeach;

                        ?></select>
                    </div>
                    <div class="col-6 col-md-2 col-lg-1">
                        <label for="selectMinutes" class="form-label">Menit</label>
                        <select name="minutes" class="form-select mb-3" id="selectMinutes">
                            <option value="">Pilih</option><?php

                            foreach($minutesOptions as $minute):
                                ?><option <?=( $minute == $minutesTarget ? 'selected' : '' )?> value="<?=$minute?>">
                                    <?=$minute?>
                                </option><?php
                            endforeach;

                        ?></select>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-auto ms-auto me-5">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg px-5 gap-3">Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </nav>
    <main class="py-5"><?php

        if(!$isSearching):
            ?><div class="container">
                <h4 class="text-center text-body-secondary">Cari Log.</h4>
            </div><?php
        elseif($isFileNotFound):
            ?><div class="container">
                <h4 class="text-center text-body-secondary">File *.log tidak ditemukan.</h4>
            </div><?php
        elseif(count($logContents) < 1):
            ?><div class="container">
                <h4 class="text-center text-body-secondary">Pencarian tidak ditemukan.</h4>
            </div><?php
        else:
            ?><div class="container">
                <?php var_dump($logContents); ?>
            </div><?php
        endif;

    ?></main>
</body>
</html><?php

} catch(Throwable $err) {
    echo $err;
}