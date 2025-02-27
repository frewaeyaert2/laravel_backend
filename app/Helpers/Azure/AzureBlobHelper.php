<?php

namespace App\Helpers\Azure;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Exception;

class AzureBlobHelper
{
    private $accountName;
    private $accessKey;
    private $container;
    private $blobUrl;

    public function __construct() {
        $this->accountName = env('AZURE_STORAGE_ACCOUNT_NAME');
        $this->accessKey = env('AZURE_STORAGE_ACCESS_KEY');
        $this->container = env('AZURE_STORAGE_CONTAINER');
        $this->blobUrl = env('AZURE_STORAGE_BLOB_URL');
    }




    protected $accountName = 'straccqpigeons';
    protected $accountKey = 'lKetgoP/tiGfQviD949U9xv/JmP9fRTzZ7rJRygroQgqZdE54e8dZiUwU3qd1rVx52vBT/dRiGLJ+ASt8yi0QQ==';
    protected $container = 'imgs-q-pigeons';
    protected $baseUrl = 'https://straccqpigeons.blob.core.windows.net';
    public function uploadImageToAzure($file, $folder = 'uploads')
    {


        

        if (!$this->accountName || !$this->accountKey || !$this->container) {
            throw new Exception('Azure Storage configuration is missing. Please check your .env file.');
        }

        // Create a Blob client
        $connectionString = "DefaultEndpointsProtocol=https;AccountName={$this->accountName};AccountKey={$this->accountKey};EndpointSuffix=core.windows.net";
        $blobClient = BlobRestProxy::createBlobService($connectionString);

        try {
            // Generate a unique file name
            $fileName = $folder . '/' . uniqid() . '_' . $file->getClientOriginalName();

            // Set the content type
            $options = new CreateBlockBlobOptions();
            $options->setContentType($file->getMimeType());

            // Upload the file to Azure Blob Storage
            $blobClient->createBlockBlob($this->container, $fileName, file_get_contents($file->getRealPath()), $options);

            // Return the URL of the uploaded file
            $url = $this->baseUrl . '/' . $this->container . '/' . $fileName;
            \Log::info('Uploaded file URL: ' . $url);
            return $url;
        } catch (ServiceException $e) {
            // Log the error
            \Log::error('Azure Blob Storage Upload Error: ' . $e->getMessage());
            return false;
        }
    }
        /**
     * Delete an image from Azure Blob Storage.
     *
     * @param string $fileUrl
     * @return bool True on success, false on failure.
     */
    public function deleteImageFromAzure($fileUrl)
    {
        // Extract the file name from the URL
        $fileName = str_replace($this->baseUrl . '/' . $this->container . '/', '', $fileUrl);

        // Create a Blob client
        $connectionString = "DefaultEndpointsProtocol=https;AccountName={$this->accountName};AccountKey={$this->accountKey};EndpointSuffix=core.windows.net";
        $blobClient = BlobRestProxy::createBlobService($connectionString);

        try {
            // Delete the file from Azure Blob Storage
            $blobClient->deleteBlob($this->container, $fileName);
            \Log::info('Deleted file URL: ' . $fileUrl);
            return true;
        } catch (ServiceException $e) {
            // Log the error
            \Log::error('Azure Blob Storage Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an image on Azure Blob Storage by deleting the old one and uploading a new one.
     *
     * @param string $oldFileUrl
     * @param \Illuminate\Http\UploadedFile $newFile
     * @param string $folder
     * @return string|bool The URL of the uploaded file or false on failure.
     */
    public function updateImageOnAzure($oldFileUrl, $newFile, $folder = 'uploads')
    {
        // Delete the old image
        $this->deleteImageFromAzure($oldFileUrl);

        // Upload the new image
        return $this->uploadImageToAzure($newFile, $folder);
    }
}