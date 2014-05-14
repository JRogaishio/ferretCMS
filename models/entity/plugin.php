<?php

/**
 * Class to handle plug-ins
 *
 * @author Jacob Rogaishio
 * 
 */
class plugin extends model
{
	// Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $path = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"path");
	protected $filename = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"filename");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	//Non-Persistant Properties
	protected $name = null;
	
	//Getters
	public function getName() {return $this->name;}
	
	//Setters
	public function setName($val) {$this->name = $val;}
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['path'])) $this->setPath(clean($this->conn, $params['path']));
		if(isset($params['file'])) $this->setFilename(clean($this->conn, $params['file']));
		$this->name = substr($this->getFilename(), 0, strpos($this->getFilename(), ".php"));
	}
	
	/**
	 * Loads the plugin object members based off the plugin id in the database
	 * 
	 * @param $pluginId	The plugin to be loaded
	 */
	public function loadRecord($p=null, $c=null) {		
		if(isset($p) && $p != null) {
			$this->load($p);
			
			//Set a field to use by the logger
			$this->logField = $this->getName();
			
			$this->name = substr($this->getFilename(), 0, strpos($this->getFilename(), ".php"));
		}
	}
	
	/**
	 * Builds the admin editor form to add / update plugins
	 * 
	 * @param $pluginId	The plugin to be edited
	 */
	public function buildEditForm($pluginId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($pluginId);

		echo "<div id='main_content'>";
		echo '
			<form action="admin.php?type=plugin&action=' . (($this->getId() == null) ? "insert" : "update") . '&p=' . $this->getId() . '" method="post">

			<label for="path" title="This is the name in plugins">Plugin folder name:</label><br />
			<input name="path" id="path" type="text" maxlength="150" value="' . $this->getPath() . '" />
			<div class="clear"></div>

			<label for="file" title="This is the name of the plugin php file">Plugin filename:</label><br />
			<input name="file" id="file" type="text" maxlength="150" value="' . $this->getFilename() . '" />
			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($pluginId) || $pluginId == null) ? "Create" : "Update") . ' This Plugin!" /><br /><br />
			' . ((isset($pluginId) && $pluginId != null) ? '<a href="admin.php?type=plugin&action=delete&p=' . $this->getId() . '"" class="deleteBtn">Delete This Plugin!</a><br /><br />' : '') . '
			</form>
		';
		echo "</div>";
	}
	
	/**
	 * Display the list of all plugins
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=plugin&action=read">Plugin List</a><br /><br />';
	
		$sql = "SELECT * FROM " . $this->table . " ORDER BY created DESC";
		$result = $this->conn->query($sql);
	
		if ($result !== false && mysqli_num_rows($result) > 0 ) {
			while($row = mysqli_fetch_assoc($result) ) {
	
				$file = stripslashes($row['filename']);
				$path = stripslashes($row['path']);
				$name = substr($file, 0, strpos($file, ".php"));
				if($name == null)
					$name = "ERROR WITH PLUGIN FILE NAME";
	
				echo "
				<div class=\"plugin\">
					<h2>
					<a href=\"admin.php?type=plugin&action=update&p=".$row['id']."\" title=\"Edit / Manage this plugin\" alt=\"Edit / Manage this plugin\" class=\"cms_pageEditLink\" >$name</a>
						</h2>
						<p>" . PLUGIN_PATH . "/" . $path . "/" . $file . "</p>
				</div>";
	
			}
			} else {
			echo "
			<p>
				No plugins found!
			</p>";
		}
	
	}
	
	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";
	
		return $ret;
	}
	
}

?>
