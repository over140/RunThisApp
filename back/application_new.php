<!doctype html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Run This App | Add Application</title>
	<link href="../css/style-0001.css" media="screen" type="text/css" rel="stylesheet">
</head>
<body>

	<div id="header">
		<h2><a href="../">Run This App</a></h2>
		<ul class="menu">
			<li class="invitations"><a href="invitation_list.php">Invitations</a></li>
			<li class="testers"><a href="tester_list.php">Testers</a></li>
        	<li class="applications active">Applications</li>
		</ul>
		
		<ul class="login">
			<li>Hi, Guest</li>
			<li><a href="register.php">Register</a></li>	
			<li><a href="login.php">Log In</a></li>
		</ul>
	</div>
	
	<div id="content" class="box">
		<div class="boxtop"></div>
		<div class="column first">
			<div class="boxheader">			
				<h2>Add New Application</h2>
			</div>
			<div>
				
				<?php
				
				require_once __DIR__ . '/../lib/cfpropertylist/CFPropertyList.php';
				
				function getInfoPlistPath($appName) {
				
					// We get the Info.plist of the app if it exists else <app_name>-Info.plist
					$plistPath = __DIR__ . '/../app/' . $appName . '/Payload/' . $appName;
					if ( file_exists($plistPath . '.app/Info.plist') ) {
							$plistFilePath = $plistPath . '.app/Info.plist';
					} else {
							$plistFilePath = $plistPath . '.app/' . appName . '-Info.plist';
					}
					
					return $plistFilePath;
				}
				
				function unzipApplication($upload_path, $filename) {
					
					$zip = new ZipArchive;
					$res = $zip->open($upload_path . $filename);
					if ($res === TRUE) {
						
						//TODO this needs to be verify for exotic names
						$appName = substr($filename, 0, strpos($filename,'.'));
					    $zip->extractTo('../app/' . $appName . '/');
					    $zip->close();
					     
					    return true;
					    
					} else {
					    return false;
					}
				}
				
				function readAppInfoAndSaveThem($plistfile, $application) {
				
					$plistValue = file_get_contents($plistfile);
					if (empty($plistValue)) {
						die("Unable to read application plist file.<br />");
				  	}
				    
				  	$plist = new CFPropertyList();
				  	$plist->parse( $plistValue, CFPropertyList::FORMAT_AUTO);
				  	$plistData = $plist->toArray();
					
					echo 'App display name: ' . $plistData['CFBundleDisplayName'] . '<br />'; //TODO if '${PRODUCT_NAME}' use <app_ame>
					echo 'Icon file: ' . $plistData['CFBundleIconFile'] . '<br />'; //TODO if empty use 'Icon.png'	
				  	echo 'Bundle identifier: ' . $plistData['CFBundleIdentifier'] . '<br />'; //TODO if contains('${PRODUCT_NAME:rfc1034identifier}') replace by <app_ame>
				  	echo 'Version: ' . $plistData['CFBundleVersion'] . '<br />';
				  	
				  	//TODO write these values to he object '$application' and save them in database
				}
				
				// If we have uploaded a file we process it else we ask for it
				if ( !empty($_FILES['app_file']) ) {
				
					if ($_FILES["app_file"]["error"] > 0) {
					  	if ($_FILES["app_file"]["error"] == 1) {
					  	 	echo 'Error: The uploaded file exceeds the upload_max_filesize directive in php.ini.';
					  	} else if ($_FILES["app_file"]["error"] == 4) {
					  	 	echo 'Error: No file was uploaded.';
					  	} else {
					  		echo "Error code: " . $_FILES["app_file"]["error"] . "<br />";
					  	}
					} else {
						
						//echo "Upload: " . $_FILES["app_file"]["name"] . "<br />";
						echo "Type: " . $_FILES["app_file"]["type"] . "<br />";
						echo "Size: " . ($_FILES["app_file"]["size"] / 1024) . " Kb<br />";
						//echo "Temp file: " . $_FILES["app_file"]["tmp_name"] . " Kb<br />";
					  
						if ( $_FILES["app_file"]["type"] == 'application/x-itunes-ipa' ) {
					  		
					 		// Configuration - Your Options
					   		$allowed_filetypes = array('.jpg','.png','.ipa'); // Types of file allowed.
					 		$max_filesize = 20971520; // Maximum filesize in BYTES (currently 20MB).
					 		$upload_path = '../app/'; // The place the files will be uploaded to (currently a 'files' directory).
					 		
							$filename = $_FILES['app_file']['name']; // Get the name of the file (including file extension).
							//TODO verify to grab the last '.' to get only extension!!! and not version & extension
					 		$ext = substr($filename, strpos($filename,'.'), strlen($filename)-1); // Get the extension from the filename.
					  		
					    	// Check if the filetype is allowed, if not DIE and inform the user.
					 		if(!in_array($ext,$allowed_filetypes))
					  			die('The file you attempted to upload is not allowed: ' . $ext);
							
							// Now check the filesize, if it is too large then DIE and inform the user.
							if(filesize($_FILES['app_file']['tmp_name']) > $max_filesize)
								die('The file you attempted to upload is too large.');
							
					  		// Check if we can upload to the specified path, if not DIE and inform the user.
					 		if(!is_writable($upload_path))
					    		die('You cannot upload to the specified directory, please CHMOD it to 777.');
							
							// Upload the file to your specified path.
							if(move_uploaded_file($_FILES['app_file']['tmp_name'],$upload_path . $filename)) {
					 			echo 'Your file upload was successful at: ' . $upload_path . $filename . '<br />';
					   			
					   			$zip = new ZipArchive;
					  			$res = $zip->open($upload_path . $filename);
					   			if (unzipApplication($upload_path, $filename)) {
					     			echo 'Unzip ok.<br>';
					    			
					      			//TODO this needs to be verify for exotic names
					    			$appName = substr($filename, 0, strpos($filename,'.'));
					   				
					     			// We get the Info.plist of the app if it exists else <app_name>-Info.plist
					     			$plistfile = getInfoPlistPath($appName);
					      			//echo 'Plist file: ' . $plistfile . '<br />';
					    			
					    			//TODO create an application object and set basic data here
					    			$application = null;
					    			
					    			readAppInfoAndSaveThem($plistfile, $application);
									
								} else {
					   				echo 'Unzip failed.<br />';
					   			}
					       	       
							} else {
								echo 'There was an error during the file upload.  Please try again.<br />'; // It failed
							}
							
						} else {
							echo 'Error: This file is not an IPA file.<br />';
						}
					}
				
				} else {
				
				?>
				
				<form name="application_new.php" method="post" enctype="multipart/form-data"  action="">
					<label for="file">Select a file:</label><input type="file" name="app_file">
					<input name="Submit" type="submit" value="Upload application">	
				</form>
				
				<?php } ?>
				
			</div>
		</div>
		
		<div class="column last">
			<div class="boxheader">
				<h2>Deployment steps</h2>
			</div>
			<div class="function">
				<h6>Send Invitations</h6>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
			</div>
			<hr>
			<div class="function">
				<h6>Tester get registered</h6>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
			</div>
			<hr>
			<div class="function">
				<h6>They install your app Over-The-Air</h6>				
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
			</div>
		</div>	
		<div class="boxbottom"></div>
	</div>

</body>
</html>