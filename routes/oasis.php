<?php

use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Storage;

Route::get('/oss/list_attachment', function () {
    //Storage::put('/attachment/download/baidu.jpg', file_get_contents("https://www.baidu.com/img/bd_logo1.png?where=super"));
    $files=Storage::allFiles('/attachment/download/');
    return response()->json(new JsonResponse($files));

});

Route::get('/oss/list_preparation_files/{gymcode?}', function ($gymcode='500004') {
    //?表示可选，但必须有默认值
    $dir="/preparationFiles/{$gymcode}";
    $files=Storage::allFiles($dir);
    Log::info("list_preparation_files success");
    return response()->json(new JsonResponse($files));

});

?>
