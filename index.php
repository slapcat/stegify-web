<html>
<head>
<title>STEGIFY WEB - A WEB APPLICATION FOR STEGIFY IMAGE ENCODING</title>
<style>
h1 {
	padding: 5px;
	border: 5px solid white;
	margin: 0 30%;
}
body {
	text-align: center;
	background-color: dodgerblue;
	color: white;
	font-family: Arial, Helvetica, sans-serif;
	margin-left: 10%;
	margin-right: 10%;
	padding-top: 10px;
}
select,input {
	padding: 5px;
	border: 5px solid white;
	background-color: white;
	color: #555;
}
#submit {
	font-weight: bold;
}
input:hover,select:hover {
	border: 5px solid #555;
	background-color: #555;
	color: white;
}
.links {
	font-size: 20px;
	font-weight: bold;
}
a {
	color: white;
}
a:hover {
	font-style: italic;
	color: white;
}
a:visited {
	color: white;
}
</style>
<script src="jquery-3.4.1.min.js"></script>
<script>
$(document).ready(function(){
    $('#code').on('change', function() {
      if ( this.value == 'decode')
      {
        $(".encfile").hide();
      }
      else
      {
        $(".encfile").show();
      }
    });
});
</script>
</head>
<body>
	<h1><a href="https://nabasny.com/stegify" style="text-decoration:none">STEGIFY WEB</a></h1>
	<h2><i>A web-based application of <a href="https://github.com/DimitarPetrov/stegify" target="_blank">Stegify image encoding</a> developed by Dimitar Petrov.<br />Hide information and files inside of images.</i></h2>
<br />
<form name="input" action="index.php" method="POST" enctype="multipart/form-data">
	<h3>STEP 1: SELECT ENCODE / DECODE</h3>
	  <select id="code" name="code">
	    <option value="encode">Encode</option>
	    <option value="decode">Decode</option>
	  </select>
<br /><br />
	<h3>STEP 2: UPLOAD IMAGES</h3>
	Order matters! Files will not decode correctly if the encoded images are not uploaded in the same order that they were encoded in.<br />
<br />
	Accepted Formats: <tt><b><font color="#555">JPG, JPEG, PNG</font></tt></b><br />
<br />
	<font color="#FFA5F0">Before encoding, remove spaces from filenames!</font><br />
<br />
	<input type="file" name="file[]" id="1pic"><br />
	<input type="file" name="file[]" id="2"><br />
	<input type="file" name="file[]" id="3"><br />
<br />
<div id="encode" class="encfile">
	<h3>STEP 3: ADD HIDDEN FILE</h3>
	<input type="file" name="hidden" id="hidden"><br />
</div>
<br />
<br />
	<input name="submit" id="submit" type="submit" value="STEGIFY" />
</form>
<br /><br />
<?php

function rm_dir($target) {
    if(is_dir($target)){
        $allfiles = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

        foreach( $allfiles as $file ){
            rm_dir( $file );
        }

        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );
    }
}

if(isset($_POST['submit'])){

	// set variables
	$method = $_POST["code"];
	$carriers = "";
	$result = "";

	// create working directory
	$hash = bin2hex(random_bytes(8));
	$oldmask = umask(0);
	mkdir($hash, 0777);
	umask($oldmask);

	// count number of files to be uploaded (always 3 in this implementation)
	$countfiles = count($_FILES['file']['name']);

	if ($method == "encode") {

 	// loop through files
 	for($i=0;$i<$countfiles;$i++){
		$filename = $_FILES['file']['name'][$i];

		// upload carriers, check file types, and record filenames
		if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $hash . '/' . $filename)) {
			$imageFileType = strtolower(pathinfo($hash . '/' . $filename,PATHINFO_EXTENSION));

				if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
					echo "Sorry, you must use a JPG, JPEG, or PNG file!";
					rm_dir($hash . '/');
					exit();
				}

			$carriers = $carriers . $hash . '/' . $filename . " ";
			$result = $result . $hash . '/encoded-' . $filename . " ";
		}
	}

	// upload data to be hidden
	move_uploaded_file($_FILES["hidden"]["tmp_name"], $hash . "/hidden");

	// trim trailing space in filenames
	$carriers = substr_replace($carriers ,"", -1);
	$result = substr_replace($result ,"", -1);

	// encode
	$cmd = './stegify encode --carriers "' . $carriers . '" -d ' . $hash . '/hidden -r "' . $result . '"';
	$output = shell_exec($cmd);
		if ($output != "") { echo 'Error! Could not encode file. Try another image.'; exit(); }

	// display link to encoded files
	$files = preg_split('/ +/', $result);
	echo '<div class="links">';
	for($i=0;$i<4;$i++) {
		if ($files[$i] != "") {
			echo '<a href="' . $files[$i] . '" download>Download Encoded Image ' . $i . "</a><br />";
		} else {
			echo '<br /><i>Images will be available for 24 hours only.</i></div>';
			exit();
		}
	}
	} elseif ($method = "decode") {

	// loop through files
 	for($i=0;$i<$countfiles;$i++){
		$filename = $_FILES['file']['name'][$i];

		// upload carriers, check file types, and record filenames
		if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $hash . '/' . $filename)) {
			$imageFileType = strtolower(pathinfo($hash . '/' . $filename,PATHINFO_EXTENSION));

				if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
					echo "Sorry, you must use a JPG, JPEG, or PNG file!";
					rm_dir($hash . '/');
					exit();
				}

			$carriers = $carriers . $hash . '/' . $filename . " ";
		}
	}

	// trim trailing space in filenames
	$carriers = substr_replace($carriers ,"", -1);

	// decode
	$cmd = './stegify decode --carriers "' . $carriers . '" -r ' . $hash . '/data';
	$output = shell_exec($cmd);
		if ($output != "") { echo 'Error! Could not decode file. Did you put the images in the right order?'; exit(); }

	// link to decoded file
	echo '<div class="links"><a href="' . $hash . '/data" download>Download Decoded File</a><br /><br /><i>File exists for 30 seconds. After that, it is removed from the server.</i></div>';
	}
}
?>
</body>
</html>
