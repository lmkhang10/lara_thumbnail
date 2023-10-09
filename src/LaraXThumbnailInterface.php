<?php

namespace LaraX\Thumbnail;

use Illuminate\Http\UploadedFile;

interface LaraXThumbnailInterface
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
    public function upload(String $key, String $directory, string $type, array $request = [], String $setName = '', $setThumb = []);

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
    public function uploadByFile(UploadedFile $file, string $directory, string $type, string $setName = '', $setThumb = []);

    /**
     * Making thumbnail from an existed file
     *
     * @param string $filePath
     * @param string $directory
     * @param string $type
     * @param string $thumbnailFileName
     * @param numeric $width | null
     * @param numeric $height | null
     * return string
     */
    public function makeThumbnail(string $fullPath, string $directory, string $type, string $prefix, $width = null, $height = null);

    /**
     * Getting Full Path
     * @param string $path
     * @param string $directory
     * @param string $type
     * @return string
     */
    public function fullPath(String $path, $type);

    /**
     * Getting MIME
     * @param string $path
     * @param string $directory
     * @param string $type
     * @return string
     */
    public function getMime(String $path, $type);

    /**
     * Checking Existed Image
     * @param string $path
     * @param string $type
     * @return boolean
     */
    public function existed(String $path, $type);

    /**
     * Deleting Existed Image
     * @param string $path
     * @param string $type
     * @return boolean
     */
    public function delete(String $path, $type);

    /**
     * Getting URL
     * @param mixed $path
     * @param string $type
     * @return string
     */
    public function download(mixed $path, $type);

    /**
     * Getting Size
     * @param mixed $path
     * @param string $type
     * @return string
     */
    public function size(mixed $path, $type);

    /**
     * Uploading image to S3 from base64 string
     * @param string $base64_string
     * @param string $directory
     * @param string $type
     * @return string|false
     */
    public function uploadFromBase64($base64_string, $directory, $type);

    /**
     * Get Mime Type of File
     * @param string $filename
     * return string
     */
    public function mimeContentType($filename);

}
