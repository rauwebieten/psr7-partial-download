# PSR-7 Partial download

Partial downloads with PSR-7 libraries.

- Resuming downloads
- Audio streaming

Based on code by pomle
https://github.com/pomle/php-serveFilePartial

## Installation

```
composer require rauwebieten/psr7-partial-download
```

## Usage

```php
// in your controller class / callback

$partialDownload = new Psr7PartialDownload();
$response = $partialDownload->sendFile($request,$response,'my-song.mp3','audio/mpeg');
return $response;

```

see the example folder for details