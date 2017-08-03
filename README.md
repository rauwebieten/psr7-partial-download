# PSR-7 Partial download

Partial downloads. Why?

- Resuming downloads
- Audio streaming

## Example

```php
// in your controller class / callback

$partialDownload = new Psr7PartialDownload();
$response = $partialDownload->sendFile($request,$response,'my-song.mp3','audio/mpeg');
return $response;

```

see the example folder for details