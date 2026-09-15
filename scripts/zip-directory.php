<?php
declare(strict_types=1);
[$script,$source,$target]=$argv;
$source=realpath($source);
if(!$source || !is_dir($source) || !class_exists('ZipArchive')) {fwrite(STDERR,"ZIP extension and source directory required\n");exit(1);}
$zip=new ZipArchive();if($zip->open($target,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true){exit(1);}
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source,FilesystemIterator::SKIP_DOTS)) as $file){if($file->isFile()&&!$file->isLink()){$zip->addFile($file->getPathname(),str_replace('\\','/',substr($file->getPathname(),strlen($source)+1)));}}
$zip->close();
