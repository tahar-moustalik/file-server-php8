<?php


namespace App;

class LocalFileHandler implements FileHandlerInterface {


    private Config $config;
    const  mimeAutorized=array('text/csv'
    ,'application/msword'
    ,'application/vnd.ms-excel'
    ,'application/vnd.ms-powerpoint'
    ,'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ,'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ,'application/vnd.oasis.opendocument.presentation'
    ,'application/vnd.oasis.opendocument.spreadsheet'
    ,'application/vnd.oasis.opendocument.text'
    ,'image/png'
    ,'image/bmp'
    ,'image/jpeg'
    ,'application/pdf'
    ,'application/vnd.rar'
    ,'application/rtf'
    ,'application/zip'
    ,'application/x-zip-compressed'
    ,'application/x-7z-compressed');

    public function __construct()
    {

        $this->config = new Config();
    }


    public function upload(array $files, string $destination) :array {
        try {

        $fileStorageBaseDir = $this->config->get('FILE_STORAGE_DIR');

        if(count($files) === 0) return ['status' => 0 , 'message' => 'no files passed','statusCode' => 400];

        // validate files , if one is invalid reject    
        foreach($files as $file ) {
        
            $resValidate = $this->validateFile($file);
            if($resValidate['status'] === 0) return $resValidate;
        }


        // upload files 

        $success = [];
        $errors = [];

        $destinationPath = $fileStorageBaseDir . '/ '. date('Y/m/d') . '/' . $destination . '/';

        if(!is_dir($destinationPath)) {
            mkdir($destinationPath,0777,true);
        }
        
        
        foreach($files as $key => $file) {
            $fileName = preg_replace("/[^a-zA-Z0-9.]/", "", basename($file['name']));

            $storageFileName = sha1(time()) . '_' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destinationPath . $storageFileName)) {
                $success[] = [
                    "fileKey" => $storageFileName,
                    "filePath" => $destinationPath
                ];
            } else {
                $errors[] = $storageFileName;
            }
        }


        return ['status' => 1 , 'succes' => $success , 'errors' => $errors,'statusCode' => 200];

    } catch(\Throwable $t) {
        return ['status' => 0 , 'message' => "error uploading files" . $t->getMessage(),'statusCode' => 500];
    }




    }


    public function download(string $filePath): array {
        $fileStorageBaseDir = $this->config->get('FILE_STORAGE_DIR');

        $filePath =  dirname(__DIR__) .'/public/'. $fileStorageBaseDir . '/' . $filePath;

    

        if(!\file_exists($filePath) ||!is_file($filePath)) {

            return ['status' => 0 , 'message' => 'File not found','statusCode' => 404];

        
        }


          // Get file name and size
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);
        $fileMimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $fileMimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Read the file and send it to the user
        readfile($filePath);
        exit;


    }



    public function delete(string $filePath): array {

        try 
        {
            $fileStorageBaseDir = $this->config->get('FILE_STORAGE_DIR');

            $filePath =  dirname(__DIR__) .'/public/'. $fileStorageBaseDir . '/' . $filePath;


            if(!\file_exists($filePath) || !is_file($filePath)) {

                return ['status' => 0 , 'message' => 'File not found','statusCode' => 404];

            }


            $res = unlink($filePath);

            return ['status' => 1 , 'message' => $res ? 'File successfully deleted': 'Error deleting file', 'statusCode' => $res ? 200 : 500];

        } catch(\Throwable $t) {
            
            return ['status' => 0 , 'message' =>'Error deleting file', 'statusCode' => 500];

        }

    }


    public function move(string $filePath, string $destination): array {

        $fileStorageBaseDir = $this->config->get('FILE_STORAGE_DIR');

        $filePath =   $fileStorageBaseDir . '/' . $filePath;
        $destinationPath = $fileStorageBaseDir . '/' . $destination;



        if(!\file_exists($filePath) || !is_file($filePath)) {

            return ['status' => 0 , 'message' => 'File not found','statusCode' => 404];

        }



        if(!is_dir($destinationPath)) {

            return ['status' => 0 , 'message' => 'Destination directory not found','statusCode' => 404];

        
        }
        $fileName = basename($filePath);


        $fullDestinationPath = rtrim($destinationPath, '/') . '/' . $fileName;


        if(rename($filePath,$fullDestinationPath)) {

            return ['status' => 1, 'message' => 'file successfully moved', 'statusCode' => 200];

        }

        else {

            
            return ['status' => 0, 'message' => 'error moving file', 'statusCode' => 500];

        }

    }



    public function getMetadata(string $filePath): array {
    $fileStorageBaseDir = $this->config->get('FILE_STORAGE_DIR');
    $fullPath = $fileStorageBaseDir . '/' . $filePath;

    if (!file_exists($fullPath) || !is_file($fullPath)) {
        return ['status' => 0 , 'message' => 'File not found','statusCode' => 404];

    }

    return [
        'filename'      => basename($fullPath),
        'size'          => filesize($fullPath),  // Size in bytes
        'mime_type'     => mime_content_type($fullPath),  // Get MIME type
        'extension'     => pathinfo($fullPath, PATHINFO_EXTENSION),  // File extension
        'last_modified' => date("Y-m-d H:i:s", filemtime($fullPath)), // Last modified time
        'is_readable'   => is_readable($fullPath),
        'is_writable'   => is_writable($fullPath),
        'statusCode' => 200
    ];
    }



    public function downloadDirectory(string $directoryPath, ?string $zipFileName = null): array {

        $fileStorageBaseDir = $this->config->get('FILE_STORAGE_DIR');

        $fullDirectoryPath = $fileStorageBaseDir . '/' . $directoryPath;

        if(!is_dir($fullDirectoryPath)) {

            return ['status' => 0 , 'message' => 'Directory not found','statusCode' => 404];

        }

        if(!$zipFileName) {
            $zipFileName = str_replace('/','_',$fullDirectoryPath) .  sha1(time()). '.zip';
        }

        
        $zip = new \ZipArchive; 
   
        if($zip -> open($$zipFileName, \ZipArchive::CREATE ) === TRUE) { 
      
        // Store the path into the variable 
        $dir = opendir($fullDirectoryPath); 
       
        while($file = readdir($dir)) { 
            if(is_file($pathdir.$file)) { 
                $zip -> addFile($fullDirectoryPath.$file, $file); 
            } 
        } 
        $zip ->close(); 

        } 


    }


    private function validateFile($file)
    {

        try {
        
        $maxFileStorage = $this->config->get('MAX_FILE_SIZE');
        $filePath = $file['tmp_name'];
        $fileSize = \filesize($filePath);
        $fileInfo = \finfo_open(FILEINFO_MIME_TYPE);
        $fileType = \finfo_file($fileInfo,$filePath);

        if($fileSize === 0) return ['status' => 0 , 'message' => 'File is empty', 'statusCode' => 500];

        if($fileSize > $maxFileStorage) return ['status' => 0 , 'message' => 'File is too large','statusCode' => 500];

        if(!\in_array($fileType,self::mimeAutorized)) return ['status' => 0 , 'message' => 'File type is not allowed','statusCode' => 500];


        return ['status' => 1, 'message' => 'file is valid'];
        } catch (\Throwable $t)
            {
                return  ['status' => 0 , 'message' => $t->getLine(),'statusCode' => 500];
            }

    }
    
}