# pdf
pdf处理


example:
```phpt
$demo = new Pdf([
    'outPath' => "app/pdf/temp", //存放拆分出的文件夹
    'tempPath' => "app/pdf/config", //要拆分的pdf文件存放目录以及脚本config目录
]);
$demoPdf = "test.pdf";
try {
    $list = $demo->split($demoPdf);
    
} catch (Exception $e) {
    // 抛出异常
}
```
