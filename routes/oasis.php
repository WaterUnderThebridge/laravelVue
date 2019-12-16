<?php

use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Service\OSS;


Route::group(['prefix' => 'api'], function () {
    Route::get('/attachments', function () {
        //Storage::put('/attachment/download/baidu.jpg', file_get_contents("https://www.baidu.com/img/bd_logo1.png?where=super"));
        $files = Storage::allFiles('/attachment/download/');
        return response()->json(new JsonResponse($files));

    });

    Route::get('/oss', function () {
        $files = OSS::oss_list('/attachment/download/');
        return response()->json(new JsonResponse($files));

    });

    Route::get('/preparation_files/{gymcode?}', function ($gymcode = '500004') {
        //?表示可选，但必须有默认值
        $dir = "/preparationFiles/{$gymcode}";
        $files = Storage::allFiles($dir);
        return response()->json(new JsonResponse($files));
    });
});
?>
