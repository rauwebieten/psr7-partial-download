<?php
namespace example;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RauweBieten\SlimPartialDownload\Psr7PartialDownload;

class MyController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $partialDownload = new Psr7PartialDownload();
        $response = $partialDownload->sendFile($request,$response,'my-song.mp3','audio/mpeg');
        return $response;
    }
}