<?php

namespace RauweBieten\SlimPartialDownload;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Psr7PartialDownload
{
    public function sendFile(ServerRequestInterface $request, ResponseInterface $response, $filePath, $fileName, $contentType = 'application/octet-stream')
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        if (!is_readable($filePath)) {
            throw new \Exception("File not readable: $filePath");
        }

        // remove headers hat might unnecessarily clutter up the output
        $response = $response->withoutHeader('Cache-Control');
        $response = $response->withoutHeader('Pragma');

        // default action is to send the entire file
        $byteOffset = 0;
        $byteLength = $fileSize = filesize($filePath);

        $response = $response->withHeader('Accept-Ranges', 'bytes');
        $response = $response->withHeader('Content-Disposition', "attachment; filename=$fileName");

        $server = $request->getServerParams();

        if (isset($server['HTTP_RANGE']) && preg_match('%bytes=(\d+)-(\d+)?%i', $server['HTTP_RANGE'], $match)) {
            $byteOffset = (int)$match[1];

            if (isset($match[2])) {
                $finishBytes = (int)$match[2];
                $byteLength = $finishBytes+1;
            } else {
                $finishBytes = $fileSize - 1;
            }
            $response = $response->withStatus(206, 'Partial Content');
            $response = $response->withHeader('Content-Range', "bytes {$byteOffset}-{$finishBytes}/{$fileSize}");
        }

        $byteRange = $byteLength - $byteOffset;
        $response = $response->withHeader('Content-Langth', $byteRange);
        $response = $response->withHeader('Expires', date('D, d M Y H:i:s', time() + 60*60*24*90) . ' GMT');

        $bufferSize = 512*16;
        $bytePool = $byteRange;

        if (!$fh = fopen($filePath, 'r')) {
            throw new \Exception("Could not get filehandler for reading: $filePath");
        }

        if (fseek($fh, $byteOffset, SEEK_SET) == -1) {
            throw new \Exception("Could not seek to offset $byteOffset in file: $filePath");
        }

        while ($bytePool > 0) {
            $chunkSizeRequested = min($bufferSize, $bytePool);
            $buffer = fread($fh, $chunkSizeRequested);
            $chunkSizeActual = strlen($buffer);

            if ($chunkSizeActual == 0) {
                throw new \Exception("Chunksize became 0");
            }

            $bytePool-= $chunkSizeActual;

            $response->write($buffer);
        }

        return $response;
    }
}