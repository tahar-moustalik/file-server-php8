<?php


namespace App;

interface FileHandlerInterface
{
    public function upload(array $file, string $destination): array;
    public function download(string $filePath): array;
    public function delete(string $filePath): array;
    public function move(string $filePath, string $destination): array;
    public function getMetadata(string $filePath): array;
    public function downloadDirectory(string $directoryPath, ?string $zipFileName = null): array;
}
