<!-- PHP = SAVE DATA FIELDS -->
<?php
session_start();

//$ID = $_SESSION["UserID"];
//$EM = $_SESSION["Email"];
//$FN = $_SESSION["FName"];


$servername = " ";
$username = " ";
$password = " ";
$dbname = " ";

// Create connection
//$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
//if ($conn->connect_error) {
    //die("Connection failed: " . $conn->connect_error);
//}



// Insert Data
if (isset($_POST['submit'])) {

    //$UserID = $_POST['UserID'];
    $UserID = $_SESSION["UserID"];
    $ModelName = $_POST['ModelName'];



    // UPLOAD IMAGES
    // check if there are uploaded files
    if (isset($_FILES['files'])) {
        // Get the user input for the subfolder name
        $subfolderName = $ID . "_" . $_POST['ModelName'];
    
        // configure upload directory
        $upload_dir = '/home/confocal/public_html/UserImages/';
        $subfolder_dir = $upload_dir . $subfolderName . '/';
    
        if (!file_exists($subfolder_dir)) {
            // Create the subfolder if it doesn't exist
            mkdir($subfolder_dir, 0777, true);
        }
    
        // process each uploaded file
        $errors = array();
        $successes = array();
        $max_allowed_files = 100; // Set the maximum allowed files to 100
        $total_files = count($_FILES['files']['name']);
        
        if ($total_files > $max_allowed_files) {
            http_response_code(400);
            echo '<script>alert("Error! You can upload a maximum of ' . $max_allowed_files . ' images.");</script>';
            exit();
        }
        
        for ($index = 0; $index < $total_files; $index++) {
            $filename = $_FILES['files']['name'][$index];
            $target_file = $subfolder_dir . basename($filename);
            $upload_ok = true;
    
            // check file type and size
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if ($file_type != 'jpg' && $file_type != 'jpeg' && $file_type != 'png' && $file_type != 'gif') {
                $errors[] = 'File type not allowed: ' . $filename;
                $upload_ok = false;
            } else if ($_FILES['files']['size'][$index] > 5000000) {
                $errors[] = 'File too large: ' . $filename;
                $upload_ok = false;
            }
    
            // attempt to move uploaded file
            if ($upload_ok) {
                if (move_uploaded_file($_FILES['files']['tmp_name'][$index], $target_file)) {
                    $successes[] = 'File uploaded: ' . $filename;
                } else {
                    $errors[] = 'Error uploading file: ' . $filename;
                }
            }
        }
    
        // send response to client
        if (count($errors) > 0) {
            http_response_code(400);
            //echo implode("\n", $errors);
            echo '<script>alert("Error!");</script>';
        } else {
            http_response_code(200);
            //echo implode("\n", $successes);
    
            $Smsg = $total_files . ' images were successfully uploaded!';
            echo '<script>alert("' . 'Model Created! ' . $Smsg . '")</script>';
        }
    } else {
        http_response_code(400);
        echo '<script>alert("Error, No files provided!");</script>';
    }


    
    
    
    
    
    
    
    
    // CREATE JSON FILE
    // Specify the directory path and file name
    $dir_path = '/home/confocal/public_html/UserBuildScripts/';
    $file_name = $ID . "_" . $_POST['ModelName'];
    
    $Refrance = 'https://davidjoiner.net/~confocal/UserImages/';
    $baseURL = $Refrance . $file_name . '/';
    
    
    // Generate JSON data
    $data = array(
        'baseURL' => $baseURL,
        'numImgs' => $total_files
    );
    
    $json = json_encode($data, JSON_UNESCAPED_SLASHES);
    
    // Generate the full file path
    $file_path = $dir_path . $file_name . '.json';
    
    // Use the 'w' flag to create a new file and write to it
    file_put_contents($file_path, $json, LOCK_EX);
    
    // Set file permissions to 777
    chmod($file_path, 0777);
    
    
    // Check if file was created
    if (file_exists($file_path)) {
        //echo '<script>alert("File Created!");</script>';
    } 
    else {
        echo '<script>alert("Error, File NOT Created!");</script>';
    }

    
    $jsonPull = 'https://davidjoiner.net/~confocal/UserBuildScripts/';
    $jsonLink = $jsonPull . $file_name;
    
    
    
    
    
    
    
    
    
    // UPLOAD TXT FILE
    if (isset($_FILES['txtFile'])) {
        $txtFilename = $_FILES['txtFile']['name'];
        $txtTempPath = $_FILES['txtFile']['tmp_name'];
    
        // Get the user input for the subfolder name
        $subfolderName = $ID . "_XYZ_" . $_POST['ModelName'];
    
        // Configure upload directory for TXT files
        $txtUploadDir = '/home/confocal/public_html/UserXYZdata/';
        $txtTargetFile = $txtUploadDir . $subfolderName . '/' . $txtFilename;
    
        // Check if the directory exists, if not, create it
        if (!file_exists($txtUploadDir . $subfolderName)) {
            mkdir($txtUploadDir . $subfolderName, 0777, true);
        }
    
        // Move the uploaded TXT file to the destination
        if (move_uploaded_file($txtTempPath, $txtTargetFile)) {
            echo '<script>alert("TXT File uploaded: ' . $txtFilename . '");</script>';
        } else {
            echo '<script>alert("Error uploading TXT file: ' . $txtFilename . '");</script>';
        }
        
        $xyzPull = 'https://davidjoiner.net/~confocal/UserXYZdata/';
        $xyzLink = $xyzPull . $subfolderName . '/' . $txtFilename;
    }
        

    
    

    
    
    
    //Insert info into DB
   //$sql = "INSERT INTO ConfocalData (UserID, ModelName, NumImg, ImgLink, XYZLink, JsonLink, CreationDateTime) VALUES ('$UserID', '$ModelName', '$total_files', '$baseURL', '$xyzLink', '$jsonLink', '" . date('Y-m-d H:i:s') . "')";
   
   
    // Check if "Run Analysis" checkbox is selected
    $runAnalysis = isset($_POST['runAnalysis']) ? 1 : 0;
    
    // Build SQL query based on the "Run Analysis" checkbox
    if ($runAnalysis) {
        // SQL query when "Run Analysis" is selected
        $sql = "INSERT INTO ConfocalData (UserID, ModelName, NumImg, ImgLink, XYZLink, JsonLink, CreationDateTime) VALUES ('$UserID', '$ModelName', '$total_files', '$baseURL', '$xyzLink', '$jsonLink', '" . date('Y-m-d H:i:s') . "')";
    } 
                
    else {
        // SQL query when "Run Analysis" is not selected
        $sql = "INSERT INTO ConfocalData (UserID, ModelName, NumImg, ImgLink, JsonLink, CreationDateTime) VALUES ('$UserID', '$ModelName', '$total_files', '$baseURL', '$jsonLink', '" . date('Y-m-d H:i:s') . "')";
    }


   
   
   
    if ($conn->query($sql) === TRUE) {
      //echo '<script>alert("Record successfully added!"); window.location.href = "https://davidjoiner.net/~confocal/myModels.php";</script>';
    } 
    else {
      echo '<script>alert("Error Writing to Data Base.");</script>';
    }
}


//$conn->close();
?> 










<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
 
  <title>Create Model</title>
  
 
 
 
 
 
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const dropZone = document.getElementById("drop-zone");
    const fileInput = document.getElementById("files");
    const gallery = document.getElementById("gallery");
    const maxRows = 2; // Maximum number of rows to display

    dropZone.addEventListener("dragover", (e) => {
      e.preventDefault();
      dropZone.classList.add("highlight");
    });

    dropZone.addEventListener("dragleave", () => {
      dropZone.classList.remove("highlight");
    });

    dropZone.addEventListener("drop", (e) => {
      e.preventDefault();
      dropZone.classList.remove("highlight");
      const files = e.dataTransfer.files;
      fileInput.files = files;
      updateGallery(files);
    });

    fileInput.addEventListener("change", (e) => {
      updateGallery(fileInput.files);
    });

    function updateGallery(files) {
      gallery.innerHTML = "";
      const numCols = 5; // Number of columns to display
      const numRows = Math.min(maxRows, Math.ceil(files.length / numCols));
      const thumbnailSize = 150; // Adjust this value as needed
      gallery.style.height = `${numRows * thumbnailSize}px`;

      for (const file of files) {
        const imgContainer = document.createElement("div");
        imgContainer.classList.add("image-container");
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        imgContainer.appendChild(img);
        gallery.appendChild(imgContainer);
      }
    }
  });
</script>

 
 
 
 
 
 
 
 
 
  <style>
    /* Main Body */
    html, body {
      margin: 0;
      padding: 0;
      font-family: Ariel;
      min-height: 100vh; /* Set minimum height to the viewport height */
      position: relative; /* Set relative position to allow footer absolute positioning */
      overflow-x: hidden;
    }
    
   ul { 
    text-decoration: underline;
   }
   
   .center {
      display: block;
      margin-left: auto;
      margin-right: auto;
      width: 50%;
    } 
  
   
   
   
   
   
  /*Image and Text*/
  * {
    box-sizing: border-box;
  }
  
  .column {
    float: left;
    width: 50%;
    padding: 5px;
  }
  
  body {
    font-family: "Times New Roman", Times, serif;
  }
  .note {
    width: 500px;
    margin: 50px auto;
    font-size: 1.1em;
    color: #333;
    text-align: justify;
  }
  #drop-zone {
    border: 2px dashed #ccc;
    border-radius: 20px;
    width: 600px;
    margin: 50px auto;
    padding: 20px;
  }
  #drop-zone.highlight {
    border-color: blue;
  }
  p {
    margin-top: 0;
  }
  .my-form {
    margin-bottom: 10px;
  }
  
  
  
  
  #gallery {
    margin-top: 10px;
    overflow-y: auto; /* Add vertical scrollbar when needed */
    max-height: 320px; /* Adjust this value as needed */
  }

  .image-container {
    position: relative;
    display: inline-block;
    margin-right: 8px;
    margin-bottom: 8px;
  }

  .image-container img {
    width: 150px;
  }

  
  
  
  #gallery img {
    width: 150px;
    margin-bottom: 8px;
    margin-right: 8px;
    vertical-align: middle;
  }
  .button {
    display: inline-block;
    padding: 10px;
    background: #ccc;
    cursor: pointer;
    border-radius: 5px;
    border: 1px solid #ccc;
  }
  .button:hover {
    background: #ddd;
  }
  .submit-btn {
    display: inline-block;
    padding: 10px;
    background: #ccc;
    cursor: pointer;
    border-radius: 5px;
    border: 1px solid #ccc;
  }
  #fileElem {
    display: none;
  }
   
  .image-container {
    position: relative;
    display: inline-block;
    margin-right: 8px;
    margin-bottom: 8px;
  }

  .image-container img {
    width: 150px;
  }


   
   
   /* Navbar */
    .navbar {
      display: flex;
      flex-wrap: wrap;
      background-color: black;
      color: #fff;
      border-style: solid;
      border-color: blue;
      font-family: Ariel;
    }
    
    .navbar a {
      color: #fff;
      text-decoration: none;
      padding: 12px 20px;
      display: block;
      text-align: center;
      flex-grow: 1;
      transition: background-color 0.3s ease-in-out;
    }
    
   
   
   
   
   /* Navbar Hover */
    .navbar a:hover {
      background-color: #555;
    }
    
    .navbar a:last-child {
      border-right: none;
    }
    



    /* Drop Down*/
    .dropdown {
      float: left;
      overflow: hidden;
    }
    
    .dropdown .dropbtn {
      font-size: 16px;  
      border: none;
      outline: none;
      color: white;
      padding: 14px 16px;
      background-color: inherit;
      font-family: inherit;
      margin: 0;
    }
    
    .navbar a:hover, .dropdown:hover .dropbtn {
      background-color: blue;
    }
    
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #f9f9f9;
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
    }
    
    .dropdown-content a {
      float: none;
      color: black;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      text-align: left;
    }
    
    .dropdown-content a:hover {
      background-color: #ddd;
    }
    
    .dropdown:hover .dropdown-content {
      display: block;
    }


   
   
   
   /* Footer */
    .footer {
      background-color: black;
      color: #fff;
      padding: 10px;
      text-align: center;
      font-size: 14px;
      position: absolute; /* Set absolute position */
      bottom: 0; /* Set to bottom of the screen */
      width: 100%; /* Set width to 100% */
    }
    
    .footer a {
      color: #fff;
      text-decoration: none;
      margin: 0 10px;
      transition: opacity 0.3s ease-in-out;
    }
    
    .footer a:hover {
      opacity: 0.7;
    }
   
   
   
   
   
   /* Social Media Icons */
  .fa {
    padding: 10px;
    font-size: 15px;
    width: 15px;
    text-align: center;
    text-decoration: none;
    margin: 2px 1px;
    border-radius: 50%;
    box-sizing: revert;
  }
  
  .fa:hover {
      opacity: 0.7;
  }
  
  
  
  .fa-facebook {
    background: #393939;
    color: white;
  }
  
  .fa-twitter {
    background: #393939;
    color: white;
  }
  
  .fa-linkedin {
    background: #393939;
    color: white;
  }
  
  
  
  
  
  /* Increase the size of the checkbox */
  #Analyze {
    transform: scale(1.5); /* You can adjust the scale factor as needed */
    margin-right: 10px; /* Add some spacing between the label and the checkbox */
  }




  </style>
</head>





   
    <?php 
      if ($_SESSION['UserID']){ ?>
          <body style="background-color:white">
          <div class="navbar">
            <a href="https://davidjoiner.net/~confocal/index.php">&emsp; &emsp; &ensp;  Home &emsp; &emsp; &ensp; </a>
            
            <a href="https://davidjoiner.net/~confocal/download.php">&emsp; &emsp; &ensp;  Download &emsp; &emsp; &ensp; </a>
            
            
            <div class="dropdown">
              <button class="dropbtn">&emsp; &emsp; &ensp;  Models <font size= 1px> &#9660 </font>  &emsp; &emsp; &ensp;
               
              </button>
              
              <div class="dropdown-content">
                <a href="https://davidjoiner.net/~confocal/createModel.php"> Create Model </a>
                <a href="https://davidjoiner.net/~confocal/myModels.php">Model Library </a>
                <a href="https://davidjoiner.net/~confocal/Auth.php">Generate VR Room </a>
              </div>
            </div> 
            
            
            <a href="https://davidjoiner.net/~confocal/documentation.php">&emsp; &emsp; &ensp;  Documentation &emsp; &emsp; &ensp; </a>
            
            <a href="https://davidjoiner.net/~confocal/contactUs.php">&emsp; &emsp; &ensp;  Contact us &emsp; &emsp; &ensp; </a>
        
          </div>
      
      
      
      
      <?php } else { ?>
          <body style="background-color:white">
          <div class="navbar">
            <a href="https://davidjoiner.net/~confocal/index.php">&emsp; &emsp; &ensp;  Home &emsp; &emsp; &ensp; </a>
            
            
            <a href="https://davidjoiner.net/~confocal/contactUs.php">&emsp; &emsp; &ensp;  Contact us &emsp; &emsp; &ensp; </a>
            
            <a href="https://davidjoiner.net/~confocal/login.php">&emsp; &emsp; &ensp;  Login &emsp; &emsp; &ensp; </a>
            
          </div>
      <?php }
      ?>   
   






<body>

 <h1><center><ul>Create a New Model</ul></center></h1>
   <br>
    
        <p><font color="black"><center>Please fill out the following feilds to create a new model. Based off your input a configuration file will be 
          genarate, which can then be ran on the lattest <br />verson of the Cell Viewer application. If you do not have the lattest version of the modeling 
          software, please <a href="https://davidjoiner.net/~confocal/download.php">click here.</a> </center></font></p>
        <br>
        
        
        
        <center>
          <?php
           $color = "blue";
           echo "<p style='color: $color;'>Welcome, $FN </p>";
          ?>
          <br>
        
        

        
        
        
          <!-- DATA FEILDS -->
          <form method="post" action="" id="form-id" enctype="multipart/form-data">
          
            <label for="ModelName">Model Name:</label><br>
            <input type="text" id="ModelName" name="ModelName"><br><br>
            <br>
            
            <input type="hidden" id="ImgLink" name="ImgLink">
            
            
            
            
          <!-- Drag and Drop Form -->
          <div id="drop-zone">
            <form action="" class="my-form" method="POST" enctype="multipart/form-data">
              <center>
                <p>Drag and Drop or Select Files to Upload</p><br>
                <input type="file" id="files" name="files[]" accept=".jpg, .jpeg, .png, .gif" multiple="multiple" max_file_uploads="100"><br>
                <div id="gallery"></div><br>
              </center>
          </div>
              
          
          
              
          <!-- Add this input field to the form -->
          <label for="runAnalysis">Run Analysis</label>
          <input type="checkbox" name="runAnalysis" id="runAnalysis">
          <br><br>
      
          <!-- Show the file upload option only if "Run Analysis" is selected -->
          <div id="uploadOption" style="display:none;">
              <label for="txtFile">Upload cell location Data:</label><br>
              <input type="file" id="txtFile" name="txtFile" accept=".txt"><br><br>
          </div>
      
          <script>
              // JavaScript to toggle the display of the upload option based on the checkbox state
              document.getElementById('runAnalysis').addEventListener('change', function() {
                  document.getElementById('uploadOption').style.display = this.checked ? 'block' : 'none';
              });
          </script>

              
              
            <br><br>
              
            <button type="submit" name="submit" id="submit-btn" class="button" style="font-family: 'Times New Roman',Times, serif;">Create Model</button></center>
            </form>
    
          </form>
          
          
          <br><br><br>
        </center>

        
        
        <br><br><br><br><br><br>
</body>
 
 
 
 
  <footer class="footer">
    <p>&copy; Copyright 2023, Property of Kean University.</p>
    
    <a href="#" class="fa fa-facebook"></a>
    <a href="#" class="fa fa-twitter"></a>
    <a href="#" class="fa fa-linkedin"></a>
  </footer>
  

</html>
