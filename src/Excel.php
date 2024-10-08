<?php

namespace Lee\pdf;

use Exception;

class Excel
{
    //分隔符
    protected $separator = "\t";
    protected $inputPath;
    protected $outPath;
    protected $command;
    protected $subcommands;


    public function __construct($config)
    {
        $this->inputPath = str_replace('\\', '/', rtrim($config['inputPath'], '/'));
        $this->outPath = str_replace('\\', '/', rtrim($config['outPath'], '/'));
        $this->subcommands = $config['subcommands'];
        $this->command = $this->getCommand();
    }

    /**
     * 导出xlsx
     * @throws Exception
     */
    public function export() :bool
    {
        if(!$this->command) {
            throw new Exception("This system is not supported:" . PHP_OS, 400);
        }
        $command = sprintf("%s %s -o %s -d %s --export 2>&1", $this->command, $this->subcommands, $this->outPath, $this->inputPath);
        exec($command, $out, $status);
        if($status !== 0 || !empty($out[0])) {
            throw new Exception("Failed to generate xlsx file", 500);
        }

        return true;
    }




    /**
     * 导入
     * @throws Exception
     */
    public function import() :bool
    {
        if (!$this->command) {
            throw new Exception("This system is not supported:" . PHP_OS, 400);
        }
        $command = sprintf("%s %s -i %s -d %s --read 2>&1", $this->command, $this->subcommands, $this->inputPath, $this->outPath);
        exec($command, $out, $status);
        if($status !== 0 || !empty($out[0])) {
            throw new Exception("Failed to generate xlsx file", 500);
        }

        return true;
    }
    /**
     * 解析excel文件
     * @throws Exception
     */
    public function parsingFile(): bool
    {
        if(!$this->command) {
            throw new Exception("This system is not supported:" . PHP_OS, 400);
        }
        $command = sprintf("%s %s -i %s -d %s --input 2>&1", $this->command, $this->subcommands, $this->outPath, $this->inputPath);
        exec($command, $out, $status);
        if($status !== 0 || !empty($out[0])) {
            throw new Exception("Failed to generate xlsx file", 500);
        }

        return true;
    }


    /**
     * Get script
     * @return string
     */
    private function getCommand(): string
    {
        $command = "";
        switch(PHP_OS) {
            case 'Linux':
            case 'FreeBSD':
            case 'Unix':
                $command = __DIR__ . DIRECTORY_SEPARATOR . "../bin/excel_tools_for_linux";
                break;
            case 'Windows':
            case 'WIN32':
            case 'WINNT':
            case 'Darwin':
            case 'HP-UX':
            case 'IRIX64':
            case 'CYGWIN_NT':
            case 'NetBSD':
            case 'SunOS':
            case 'OpenBSD':
            default:
                break;
        }

        return str_replace('\\', '/', $command);
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

    //删除文件
    public function delFile($file) {
        $files = func_get_args();
        foreach ($files as $file) {
            if(file_exists($file)) {
                unlink($file);
            }
        }
    }

}
