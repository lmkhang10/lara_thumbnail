<?php

namespace LaraX\Thumbnail;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class LaraXThumbnail implements LaraXThumbnailInterface
{
    /**
     * Uploading Image
     * @param string $key
     * @param string $directory
     * @param string $type
     * @param array $request
     * @param string $setName
     * @param array $setThumb
     * @return string
     */
    public function upload(String $key, String $directory, $type, array $request = [], $setName = '', $setThumb = []) {
        $url = '';
        $imagePath = '';
        $file = null;
        if (empty($request)) {
            $request = request();
            $file = $request->file($key);
        } else {
            // specific request is put
            $file = $request[$key];
        }

        if ($file) {
            $extension  = $file->getClientOriginalExtension(); //This is to get the extension of the image file just uploaded
            $image_name = !empty($setName) ? random_int(100000, 999999) . '_' . $setName : time() .'_' . random_int(100000, 999999) . '.' . $extension;
            $imagePath = $file->storeAs(
                $directory,
                $image_name,
                $type
            );

            // generating thumbnail
            if ($setThumb) {
                $thumbnailFileName = 'thumbnail_' . $image_name;
                $this->_thumbnail($file, $thumbnailFileName, $setThumb['width'], $setThumb['height']);
            }

            // get full url
            $url = $this->download($imagePath, $type);
        }

        return $url;
    }

    /**
     * Upload a file to the specified directory.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $type
     * @param string $setName
     * @param array $setThumb
     * @return string|null
     */
    public function uploadByFile(UploadedFile $file, string $directory, string $type, string $setName = '', $setThumb = [])
    {
        $extension = $file->getClientOriginalExtension();
        $image_name = !empty($setName) ? random_int(100000, 999999) . '_' . $setName : time() .'_' . random_int(100000, 999999) . '.' . $extension;

        $path = Storage::disk($type)->putFileAs($directory, $file, $image_name);

        // generating thumbnail
        if (!empty($path) && (!empty($setThumb['width']) || !empty($setThumb['height']))) {
            $thumbnailFileName = config('blog.THUMBNAIL.PREFIX', 'thumbnail_') . $image_name;
            $thumbnailPath = $this->_thumbnail($file, $thumbnailFileName, $setThumb['width'], $setThumb['height']);
            $mimeType = $this->mimeContentType($thumbnailFileName);

            $thumbnailFile = new UploadedFile(
                $thumbnailPath,
                $thumbnailFileName,
                $mimeType
            );

            if (!empty($thumbnailPath)) {
                Storage::disk($type)->putFileAs($directory, $thumbnailFile, $thumbnailFileName);

                // unlink after uploading thumbnail to S3
                unlink($thumbnailPath);
            }
        }

        // get full url
        $url = $this->download($path, $type);

        return $url;
    }

    /**
     * Generating thumbnail
     * @param string $fullPath
     * @param string $type
     * @param string $prefix
     * @param numeric $width | null
     * @param numeric $height | null
     * return string
     */
    public function makeThumbnail(string $fullPath, string $type, string $prefix, $width = null, $height = null) {
        $url = '';
        try {

            // generating thumbnail
            if (!empty($fullPath) && (!empty($width) || !empty($height))) {
                // Use pathinfo() to get the file name and extension
                $pathInfo = pathinfo($fullPath);

                $fileName = $pathInfo['filename'];
                $extension = $pathInfo['extension'];

                $image_name = $fileName  . '.' . $extension;
                $thumbnailFileName = $prefix . $image_name;
                $mimeType = $this->mimeContentType($thumbnailFileName);

                $uploadedFile = new UploadedFile(
                    $fullPath,
                    $fileName,
                    $mimeType
                );

                $url = $this->_thumbnail($uploadedFile, $thumbnailFileName, $width, $height);
            }
        } catch (\Throwable $th) {}

        return $url;
    }

    /**
     * Getting Full Path
     * @param string $path
     * @param string $directory
     * @param string $type
     * @return string
     */
    public function fullPath(String $path, $type) {
        if (empty($path)) {
            return '';
        }

        return Storage::disk($type)->path($path);
    }

    /**
     * Getting MIME
     * @param string $path
     * @param string $directory
     * @param string $type
     * @return string
     */
    public function getMime(String $path, $type) {
        if (empty($path)) {
            return '';
        }

        return Storage::disk($type)->mimeType($path);
    }

    /**
     * Checking Existed Image
     * @param string $path
     * @param string $directory
     * @param string $type
     * @return boolean
     */
    public function existed(String $path, $type) {
        if (empty($path)) {
            return false;
        }

        return Storage::disk($type)->exists($path) ? true : false;
    }

    /**
     * Deleting Existed Image
     * @param string $path
     * @param string $directory
     * @param string $type
     * @return boolean
     */
    public function delete(String $path, $type) {
        if (empty($path)) {
            return false;
        }

        if ($this->existed($path, $type)) {
            Storage::disk($type)->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Getting URL
     * @param string $path
     * @param string $type
     * @return string
     */
    public function download(string $path, $type) {
        $url = '';
        if (empty($path)) {
            return '';
        }

        if ($this->existed($path, $type)) {
            $url = Storage::disk($type)->url($path);
        }
        return $url;
    }

    /**
     * Getting Size
     * @param mixed $path
     * @param string $type
     * @return string
     */
    public function size(mixed $path, $type) {
        $size = 0;
        if (empty($path)) {
            return '';
        }

        if ($this->existed($path, $type)) {
            $size = Storage::disk($type)->size($path);
        }
        return $size;
    }

    /**
     * Uploading image to S3 from base64 string
     * @param string $base64_string
     * @param string $directory
     * @param string $type
     * @return string|false
     */
    public function uploadFromBase64($base64_string, $directory, $type)
    {
        if (empty($base64_string)) {
            return false;
        }

        // split the string on commas
        $data = explode( ',', $base64_string );

        if (empty($data[0]) || empty($data[1])) {
            return $base64_string; // this is a url
        }

        $image_info = getimagesize($base64_string);
        $extension = (!empty($image_info["mime"]) ? explode('/', $image_info["mime"] )[1]: "");
        $mimeType = !empty($image_info["mime"]) ? $image_info["mime"] : "";

        if (empty($extension) || empty($mimeType)) {
            return false;
        }

        // initializing
        $fileName = random_int(100000, 999999). '_' . time() . '.' . $extension;
        $filePath = 'tmp/' . $directory . '/' . $fileName;
        $fullFilePath = storage_path('app/' . $filePath);

        // putting file from url
        Storage::disk(config('filesystems.local'))->put($filePath, base64_decode($data[1]));
        $file = new UploadedFile(
            $fullFilePath,
            $fileName,
            $mimeType
        );

        // uploading
        $url = $this->uploadByFile($file, $directory, $type);

        // unlinking temp file
        unlink($fullFilePath);

        return $url ?? '';
    }

    /**
     * Get Mime Type of File
     * @param string $filename
     * return string
     */
    public function mimeContentType($filename) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        $parseFileName = explode('.',$filename);
        $ext = strtolower(array_pop($parseFileName));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            try {
                $finfo = finfo_open(FILEINFO_MIME);
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);
                return $mimetype;
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                \Log::error($e->getTraceAsString());
                return false;
            }
        }
        else {
            return 'application/octet-stream';
        }
    }

    /**
     * Generating thumbnail
     * @param string $path
     * @param string $thumbnailFileName
     * @param numeric $width | null
     * @param numeric $height | null
     * return string
     */
    private function _thumbnail($file, $thumbnailFileName, $width = null, $height = null) {
        $thumbnailPath = '';
        if (env('GENERATE_THUMBNAIL', false) === true) {
            try {
                $path = $file->getPathname();

                $image = Image::make($path);

                // Generate a thumbnail file name with the "thumb_" prefix and the original extension
                $thumbnailPath = dirname($path) . '/' . $thumbnailFileName;

                // Resize the image to a width or height while maintaining the aspect ratio
                $image = $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });

                // Save the thumbnail with the "thumb_" prefix and the original file extension
                // Save the thumbnail in the same folder as the original image
                $image->save($thumbnailPath);

            } catch (\Throwable $th) {
                dump($th->getMessage());
            }
        }

        return $thumbnailPath;
    }
}
