<?php
namespace Lee\pdf;
use Exception;

class Pdf
{
    private $tempPath;
    private $outPath;
    private $script;

    public function __construct($config)
    {
        $this->tempPath = str_replace('\\', '/', rtrim($config['tempPath'], '/') . DIRECTORY_SEPARATOR);
        $this->outPath = str_replace('\\', '/', rtrim($config['outPath'], '/') . DIRECTORY_SEPARATOR);
        $this->mkdir($this->tempPath, $this->outPath);
        $this->script = $this->getScript();
        $this->dirPermits($this->script); //检测&修改脚本执行权限

    }

    /**
     * split pdf file
     * @throws Exception
     */
    public function split($pdfFile): array
    {

        if(!$this->script) {
            throw new Exception("This system is not supported:" . PHP_OS, 400);
        }
        $command = sprintf("%s parse --pdf %s --outPath %s --tempPath %s 2>&1", $this->script, $pdfFile, $this->outPath, $this->tempPath);
        exec($command, $out, $status);
        if($status !== 0 || $out[0] != "ok") {
            throw new Exception("pdf split failure", 500);
        }
        $temp_list = $this->sort($this->outPath);
        $base64Contents = [];
        foreach ($temp_list as $file) {
            if ($file != ".." && $file != ".") {
                $file = $this->outPath . $file;
                $base64Contents[] = $this->base64FileContent($file);
                unlink($file);//删除临时文件
            }
        }

        return $base64Contents;
    }

    /**
     * Notes:按照拆分的顺序进行重新遍历数据
     * User: LiDong
     * Date: 2023/8/23
     * Time: 11:12
     * @param $outPath
     * @return array
     */
    private function sort($outPath): array
    {
        $temp_list = scandir($outPath);
        $newList = [];
        foreach ($temp_list as &$value) {
            if($value == ".." || $value == ".") {
                continue;
            }
            $keyStr = substr($value, strrpos($value, '_') + 1);
            $key = explode('.', $keyStr);
            $newList[--$key[0]] = $value;
        }
        ksort($newList);
        return $newList;
    }

    /**
     * Convert the file to base64
     * @param $file
     * @return string
     */
    private function base64FileContent($file): string
    {
        return base64_encode(file_get_contents($file));
    }


    /**
     * Check script permissions
     * @param $script
     */
    private function dirPermits($script) {
        $permission = fileperms($script);
        if($permission != '0777') {
            chmod($script, 0777);
        }
    }
    /**
     * Get script
     * @return string
     */
    private function getScript(): string
    {
        $script = "";
        switch(PHP_OS) {
            case 'Linux':
            case 'FreeBSD':
            case 'Unix':
                $script = __DIR__ . DIRECTORY_SEPARATOR . "../bin/pdf_tools_for_linux";
                break;
            case 'Windows':
            case 'WIN32':
            case 'WINNT':
                $script = __DIR__ . DIRECTORY_SEPARATOR . "../bin/pdf_tools_for_win.exe";
                break;
            case 'Darwin':
                $script = __DIR__ . DIRECTORY_SEPARATOR . "../bin/pdf_tools_for_mac";
                break;
            case 'HP-UX':
            case 'IRIX64':
            case 'CYGWIN_NT':
            case 'NetBSD':
            case 'SunOS':
            case 'OpenBSD':
            default:
                break;
        }

        return str_replace('\\', '/', $script);
    }


    /**
     * Detect if the folder exists and create it
     */
    private function mkdir() {
        $dirs = func_get_args();
        foreach ($dirs as $dir) {
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);chmod($dir, 0777);
            }
        }
    }
}
