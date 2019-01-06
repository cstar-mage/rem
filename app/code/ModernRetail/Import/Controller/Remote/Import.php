<?php
namespace ModernRetail\Import\Controller\Remote;

class Import extends \ModernRetail\Import\Controller\RemoteAbstract
{

    public function execute()
    {
        $bucket = date("m-d-Y");

        if(isset($_FILES['mr_import_file']['name']) && $_FILES['mr_import_file']['name'] != '') {
            $localFile = str_replace(" ","_",$_FILES['mr_import_file']['name'] );
            /* Starting upload */
            $uploader = new \Magento\Framework\File\Uploader('mr_import_file');

            // Any extention would work
            $uploader->setAllowedExtensions(array('xml'));
            $uploader->setAllowRenameFiles(false);

            // Set the file upload mode
            // false -> get the file directly in the specified folder
            // true -> get the file in the product like folders
            //    (file.jpg will go in something like /media/f/i/file.jpg)
            $uploader->setFilesDispersion(false);

            // We set media as the upload dir
            @mkdir($this->helper->getPath().DS.$bucket);
            $path = $this->helper->getPath().DS.$bucket;
            $uploader->save($path, $localFile);
            @unlink($this->helper->getPath().DS.$bucket.DS.$localFile.".done");

            /**
             * Simulate job started from ROY
             */
           // $this->monitorHelper->sendJob(["job_id"=>$localFile,"state"=>"started","log"=>"Job was sent to integrator"]);

            /**
             * Notify we received job
             */

            /**
             * Temporary send started code 
             */
            $this->monitorHelper->sendJob(["job_id"=>$localFile,"state"=>"started","log"=>"Middleware sent job"]);
            $this->monitorHelper->sendJob(["job_id"=>$localFile,"state"=>"received","log"=>"Magento received job"]);


            $this->eventManager->dispatch("integrator_run_file",array("bucket"=>$bucket,"file"=>$localFile,"debug"=>false,'reindex'=>false));

            die("EXECUTED");
        }
    }
}