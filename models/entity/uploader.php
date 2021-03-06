<?php

/**
 * Class to handle file uploads
 *
 * @author Jacob Rogaishio
 *
 */
class uploader extends model 
{
	//Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $filename = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"filename");
	protected $fileType = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"fileType");
	protected $fileSize = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"fileSize");
	protected $fileDate = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"fileDate");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
    public function buildEditForm() {
        echo "<br />
			<form action='?type=uploader&action=upload' method='post' enctype='multipart/form-data'>
				<label for='file'>File:</label>
				<input type='file' name='file' id='file' /> 
				<br />
				<input type='submit' class='btn btn-success btn-large' value='Upload file' />
			</form>
			<br /><hr /><br />
        ";
    }
    
    public function displayManager($action, $parent, $child=null, $user, $auth=null) {
    	$this->buildEditForm();
    	
    	switch($action) {
    		case "delete":
	    		$result = $this->load($parent);

	    		//If the file exists, delete it
	    		if($result) {		
		    		if(file_exists("custom/uploads//" . $this->getFilename())) {
		    			unlink("custom/uploads/" . $this->getFilename());
		    		} else {
		    			echo "Could not find file on server. Removing record...<br />";
		    		}
		    		
		    		$this->delete();

		    		echo "File deleted successfully.<br /><br />";
		    		$this->log->trackChange("uploader", 'delete',$user->getId(),$user->getLoginname(), "Deleted file: " . $this->getFilename());
	    		} else {
	    			echo "Could not delete file. Reason: Could not find file in database with ID: $parent!<br /><br />";
	    		}
	    		break;
    		case "upload":
    			if(isset($_FILES["file"])) {
			    	if ($_FILES["file"]["error"] > 0) {
			    		if($_FILES["file"]["error"] == 4)
							echo "Please select a file before pressing the submit button.<br /><br />";
			    	}
			    	else {
			    		$name = $_FILES["file"]["name"];
			    		$extension = pathinfo($name, PATHINFO_EXTENSION);
			    		$whitelist = array("jpg","jpeg","gif","png","bmp","pdf","zip","rar","rtf","doc","docx","xls","xlsx");
			    		$isAllowed = in_array($extension, $whitelist);
			    		
			    		if($isAllowed) {
				    		if ( !is_dir( 'custom/uploads/' ) ) mkdir ('custom/uploads/', 0777, true);
	
				    		if (file_exists("custom/uploads/" . $name)) {
				    			$name = $this->nextName($_FILES["file"]["name"]);
				    		}
	
			    			move_uploaded_file($_FILES["file"]["tmp_name"], "custom/uploads/" . $name);
			    			echo "File uploaded successfully!<br /><br />";
		
			    			$this->setFilename($name);
							$this->setFileType($_FILES["file"]["type"]);
							$this->setFileSize($_FILES["file"]["size"]);
							$this->setFileDate(date('Y-m-d H:i:s'));
							$this->setCreated(time());
							$this->save();
	
			    			$this->log->trackChange("uploader", 'upload',$user->getId(),$user->getLoginname(), "Uploaded file: " . $name);
			    		} else {
			    			echo "Invalid file type uploaded. Only file types of (" . implode(',', $whitelist) . ") are allowed.<br /><br />";
			    		}
			    	}
    			}
		    	break;
    	}
    }
    
    /**
     * Generates a new name for a file
     * 
     * @param $name		The file name to replace
     * @param $nameType	How to generate the new name (num, hash)
     *
     */
    private function nextName($name, $nameType="num") {
    	$ret = $name;
    	$extChar = strrpos($name, ".");
    	switch($nameType) {
    		case "num":
    			$i = 1;
    			while(file_exists("custom/uploads/" . $name)) {
    				$name = substr_replace($ret, "_" . $i, $extChar, 0);
    				$i ++;
    			}
    			$ret = $name;
    			break;
    		case "hash":
    			$ext = substr($name, $extChar);

    			while(file_exists("custom/uploads/" . $name)) {
    				$name = unique_salt() . $ext;
    			}
    			$ret = $name;
    			break;
    	}
    	
    	return $ret;
    }
    
    /**
     * Display the system uploader
     *
     */
    public function displayModelList() {
    	$fileData = $this->loadArr(new uploader($this->conn, $this->log), "created:DESC");
    	echo "<div class=\"upload\">";
    
    	if(is_array($fileData)) {
    		echo "<table class='table table-bordered'>
			<tr><th>Thumb</th><th>Filename</th><th>Type</th><th>Size</th><th>Added</th><th>Link to file</th><th>Manage</th></tr>";
    			
    		foreach($fileData as $file) {    
    			//Format size to MB
    			$size = round(($file->getFileSize() / 1024 / 1024), 2);
        
    			echo "
				<tr><td>";
    			echo (strpos($file->getFileType(), "image/") !== false ? "<img src='custom/uploads/" . $file->getFilename() . "' height='40' width='40' />" : "" );
    			echo "</td>
    			<td>" . $file->getFilename() . "</td>
    			<td>" . $file->getFileType() . "</td>
    			<td>" . $file->getFileSize() . " (MB)</td>
    			<td>" . $file->getFileDate() . "</td>
    			<td><a href='" . SITE_ROOT . "custom/uploads/" . $file->getFilename() . "' target='_blank'>Link</a></td>
    			<td><form action='?type=uploader&action=delete&p=" . $file->getId() . "' method='post'><input type='submit' value='Delete'></form></td>
    			</tr>";
    		}
    		echo "</table>";
    	} else {
    				echo "No files uploaded yet...";
		}
		echo "</div>";
    }
}

?>