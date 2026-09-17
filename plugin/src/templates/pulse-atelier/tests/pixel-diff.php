<?php
/** php -d extension=gd pixel-diff.php reference.png actual.png [difference.png] */
declare(strict_types=1);
$reference = imagecreatefrompng($argv[1]);
$actual = imagecreatefrompng($argv[2]);
$width = imagesx($reference); $height = imagesy($reference);
if ($width !== imagesx($actual) || $height !== imagesy($actual)) { throw new RuntimeException('Screenshot dimensions differ.'); }
$diff = isset($argv[3]) ? imagecreatetruecolor($width, $height) : null;
$different = 0; $totalError = 0; $maxError = 0; $rows = [];
for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $a = imagecolorat($reference, $x, $y); $b = imagecolorat($actual, $x, $y);
        if ($a === $b) { continue; }
        $different++; $rows[$y] = ($rows[$y] ?? 0) + 1;
        foreach ([0, 8, 16] as $shift) {
            $error = abs((($a >> $shift) & 255) - (($b >> $shift) & 255));
            $totalError += $error; $maxError = max($maxError, $error);
        }
        if ($diff) { imagesetpixel($diff, $x, $y, 0xff00ff); }
    }
}
if ($diff) { imagepng($diff, $argv[3]); }
echo json_encode(['width'=>$width,'height'=>$height,'differentPixels'=>$different,'differentPercent'=>100*$different/($width*$height),'meanChannelError'=>$totalError/($width*$height*3),'maxChannelError'=>$maxError,'firstDifferentRow'=>array_key_first($rows),'lastDifferentRow'=>array_key_last($rows)], JSON_PRETTY_PRINT) . PHP_EOL;
