<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\ImageManipulation;
use App\Http\Requests\ResizeImageRequest;
use GuzzleHttp\Psr7\UploadedFile;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function byAlbum(Album $album) {

    }

    public function resize(ResizeImageRequest $request)
    {
        $all = $request->all();

        // @var UploadedFile/String $image
        $image = $all['image'];
        unset($all['image']);
        $data = [
            'type' => ImageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => null
        ];

        if (isset($all['album_id'])) {
            // TODO

            $data['album_id'] = $all['album_id'];
        }

        // Create an Image Directory
        $dir = 'images'.Str::random().'/';
        $absolutePath = public_path($dir);
        File::makeDirectory($absolutePath);

        if ($image instanceof UploadedFile) {
            $data['name'] = $image->getClientOriginalName();
            // test.jpg -> test-resized.jpg
            $filename = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $originalPath = $absolutePath . $data['name'];

            $image->move($absolutePath, $data['name']);
        } else {
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);

            copy($image, $originalPath);
        }
        $data['path'] = $dir . $data['name'];

        $w = $all['w'];
        $h = $all['h'] ?? false;

        list($width, $height) = $this->getImageWidthAndHeight($w, $h, $originalPath);

        echo '<pre>';
        var_dump($width, $height);
        echo '</pre>';
        exit;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $imageManipulation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $imageManipulation)
    {
        //
    }

    protected function getImageWidthAndHeight($w, $h, string $originalPath) {
        // 1000px - 50% = 500px;
        $image = Image::make($originalPath);
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        if(str_ends_with($w, '%')) {
            $ratioW = (float)str_replace('%', '', $w);
            $ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;

            $newWidth = $originalWidth * $ratioW / 100;
            $newHeight = $originalHeight * $ratioH / 100;
        } else {
            $newWidth = (float)$w;

            /**
             * $originalWidth - $newWidth
             * $orginalHeight - $newHeight
             * ---------------------------------
             * $newHeight = $originalHeight * $newWidth / $originalWidth
             */
            $newHeight = $h ? (float)$h : $originalHeight * $newWidth / $originalWidth;
            

        }

        return [$newWidth, $newHeight];

    }
}
