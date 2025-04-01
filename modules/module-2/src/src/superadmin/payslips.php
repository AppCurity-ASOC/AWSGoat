<?php

include_once '../config.inc';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: superadmin-index.php");
    exit;
}
if($_SESSION['isadmin'] == 0 || $_SESSION['isadmin'] == 1){
    header("Location: ../logout.php");
    exit;
}

$stmt = $conn->prepare("SELECT * from users_info where id =(SELECT id from users where username = ?);");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$userid = $_SESSION['id'];



if (isset($_POST['submit'])) {
    $fname = htmlspecialchars(trim($_POST['inputfirstname']), ENT_QUOTES, 'UTF-8');
    $lname = htmlspecialchars(trim($_POST['inputlastname']), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(trim($_POST['inputphone']), ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['inputEmail'], FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars(trim($_POST['inputAddress']), ENT_QUOTES, 'UTF-8');
    $ssn = htmlspecialchars(trim($_POST['inputssn']), ENT_QUOTES, 'UTF-8');
    $bank = htmlspecialchars(trim($_POST['inputbank']), ENT_QUOTES, 'UTF-8');
    $npass = $_POST['inputnewPassword'];
    $cpass = $_POST['inputcnfPassword'];
    $uid = filter_var($_POST['uid'], FILTER_VALIDATE_INT);

    if (!$uid) {
        $_SESSION['errorMsg'] = "Invalid user ID";
        header('Location: superadmin-index.php');
        exit;
    }

    if ((!empty($fname)) && (!empty($lname)) && (!empty($email)) && (!empty($address)) && (!empty($ssn))) {
        $upq = $conn->prepare("UPDATE `users_info` SET `first_name` = ?, `last_name` = ?, `phone` = ?, `email` = ?, `address` = ?, `ssn` = ?, `bank_account` = ? WHERE id = ?");
        $upq->bind_param("sssssssi", $fname, $lname, $phone, $email, $address, $ssn, $bank, $uid);
        $upload1 = $upq->execute();
        
        $upq2 = $conn->prepare("UPDATE `users` SET `email` = ? where id = ?");
        $upq2->bind_param("si", $email, $uid);
        $upload2 = $upq2->execute();

        if ((!empty($npass)) && (!empty($cpass))) {
            if (($npass == $cpass)) {
                // Use password_hash instead of md5
                $pass = password_hash($cpass, PASSWORD_DEFAULT);
                $upq3 = $conn->prepare("UPDATE `users` SET `password` = ? where id = ?");
                $upq3->bind_param("si", $pass, $uid);
                $upload3 = $upq3->execute();
                header('Location: ../logout.php');
                exit;
            }
            else{
                echo "<script>alert('Confirm Password is Wrong!')</script>";
            }
        }

        header('Location: superadmin-index.php');
        exit;
    } else {
        $_SESSION['errorMsg'] = "Only Phone & Bank details can be blank!";
        header('Location: superadmin-index.php');
        exit;
    }
} else if (isset($_REQUEST['request'])) {
    if( isset($_FILES['file']) && $_FILES['file']['name'] != "" ) {
        // Validate file type and size
        $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $temp = explode('.', $_FILES['file']['name']);
        $extension = strtolower(end($temp));
        
        if (!in_array($extension, $allowed_extensions)) {
            die("Invalid file type. Only PDF, DOC, DOCX, JPG, JPEG, PNG are allowed.");
        }
        
        if ($_FILES['file']['size'] > 5000000) { // 5MB limit
            die("File is too large. Maximum size is 5MB.");
        }
        
        $currentDirectory = getcwd();
        $uploadDirectory = "/documents/payslips/" ;

        $salt = bin2hex(random_bytes(8));
        $fileName = $salt . "_" . time() . "." . $extension;
       
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] .  $uploadDirectory .  basename($fileName);
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
            die("Could not copy file!");
        }
        
        $remname = filter_var($_POST['remname'], FILTER_VALIDATE_INT);
        $date = htmlspecialchars(trim($_POST['date']), ENT_QUOTES, 'UTF-8');
        $filepath = "../" . $uploadDirectory .  basename($fileName);

        if ((!empty($remname)) && (!empty($date)) && (!empty($filepath)) ) {
            $queryreminsert = $conn->prepare("INSERT INTO `payslips` (`id`,`date`, `file`) VALUES(?, ?, ?)");
            $queryreminsert->bind_param("iss", $remname, $date, $filepath);
            $upload5 = $queryreminsert->execute();
        }
        else{
            header('Location: payslips.php');
            exit;
        } 

        header('Location: payslips.php');
        exit;
    }
    else {
        die("No file specified!");
    }
}




?>





<!DOCTYPE html>
<html lang="en">

<head>
    <title>AWS GOAT V2 - Payslips</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../images/AWScloud.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    
    <link rel="stylesheet" href="../CSS/styles.css">
</head>

<body>

    <!-- Navbar & Menus -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-main-head">
            <div class="container-fluid navbar-bundle">
                <div class="nav-flex-container">
                    <a class="navbar-brand" href="#"><img src="../images/AWScloud.png" height="40" width="60"> &nbsp; <img src="../images/logo.png" height="25" width="120"></a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">

                    <ul class="navbar-nav">

                        <li class="nav-item">
                            <div class="dropdown notify">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-repeat fa-xl"></i></a>
                                <div class="dropdown-menu dropdown-menu-right " aria-labelledby="navbarDropdown">
                                    <h6 class="dropdown-header">Organizations</h6>
                                    <?php
                                    $sql = "SELECT * from organizations where organization_id != 0;";
                                    $organizationresult = mysqli_query($conn, $sql);

                                    while ($organizationrow = $organizationresult->fetch_assoc()) {
                                        $org_name = htmlspecialchars($organizationrow["organization"], ENT_QUOTES, 'UTF-8');
                                        echo "<a class='dropdown-item' href='http://" . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8') . "/login.php?organization=" . urlencode($org_name) . "'>" . $org_name . "</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </li>


                        <li class="nav-item">
                            <div class="dropdown profile">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img id="profileimg" src="../images/pic.png" height="30" width="30">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#myModal"><i class="fa fa-user"></i> Profile</a>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#settingsModal"><i class="fa fa-gear"></i> Settings</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="../logout.php"><i class="fa fa-arrow-right-from-bracket"></i> Logout</a>
                                </div>
                            </div>
                        </li>
                    </ul>



                </div>
            </div>
        </nav>

        <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
            <div class="navlinks" style="width: 260px;">
                <ul>
                    <li><a href="./superadmin-index.php" type="button" data-page="homepage" class="navlink"><b></b><b></b><i class="fa fa-home fa-xl"></i><span class="title">Home</span></a></li>
                    <li><a href="#" type="button" data-page="payslippage" class="navlink active"><b></b><b></b><i class="fa fa-file-invoice-dollar fa-xl"></i><span class="title">Payslips</span></a></li>
                    <li><a href="./leave-application.php" type="button" data-page="leaveapplicationpage" class="navlink"><b></b><b></b><i class="fa fa-calendar fa-xl"></i></i><span class="title">Leave Applications</span></a></li>
                    <li><a href="./reimbursment.php" type="button" data-page="reimbursmentpage" class="navlink"><b></b><b></b><i class="fa fa-money-check-dollar fa-xl"></i><span class="title">Reimbursements</span></a></li>
                    <li><a href="./user-settings.php" type="button" data-page="usersettingspage" class="navlink"><b></b><b></b><i class="fa-solid fa-user-gear"></i><span class="title">User Settings</span></a></li>
                </ul>
            </div>
        </nav>

        <footer>
            <div class="waves">
                <div class="wave" id="wave1"></div>
                <div class="wave" id="wave2"></div>
                <div class="wave" id="wave3"></div>
                <div class="wave" id="wave4"></div>
            </div>
        </footer>

    </header>


    <div class="profilewrapper">
        <?php
        $stmt = $conn->prepare("SELECT * from users_info where id =(SELECT id from users where username = ?);");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
        <div class="modal fade" id="myModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="profile_info_text">Profile</h1>
                    </div>
                    <div class="modal-body">
                        <?php if ($result->num_rows > 0) {
                            $row = mysqli_fetch_assoc($result); ?>
                            <div class="card">

                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-6">Name</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="col-6">Email</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="col-6">Address</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="col-6">Social Security Number</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['ssn'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="col-6">Phone</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        <dt class="col-6">Bank Account Number</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['bank_account'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        <?php } else { ?>
                            User not found.
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn " data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="settingswrapper">
        <?php
        $stmt = $conn->prepare("SELECT * from users_info where id =(SELECT id from users where username = ?);");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();

        ?>
        <div class="modal fade" id="settingsModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="profile_info_text">Settings</h1>
                    </div>
                    <div class="modal-body">

                        <?php if ($result->num_rows > 0) {
                            $row = mysqli_fetch_assoc($result); ?>
                            <form method="POST" action="#">
                                <input type='hidden' name='uid' value="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-group row">
                                    <label for="inputfirstname" class="col-sm-4 col-form-label">First Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8'); ?>" id="inputfirstname" name="inputfirstname" placeholder="First Name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputlastname" class="col-sm-4 col-form-label">Last Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8'); ?>" id="inputlastname" name="inputlastname" placeholder="Last Name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputphone" class="col-sm-4 col-form-label">Phone</label>
                                    <div class="col-sm-8">
                                        <input type="tel" class="form-control" value="<?php echo htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?>" id="inputphone" name="inputphone" placeholder="Phone Number">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail" class="col-sm-4 col-form-label">Email</label>
                                    <div class="col-sm-8">
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" id="inputEmail" name="inputEmail" placeholder="Email">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Address</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?>" id="inputAddress" name="inputAddress" placeholder="Address">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputssn" class="col-sm-4 col-form-label">SSN</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['ssn'], ENT_QUOTES, 'UTF-8'); ?>" id="inputssn" name="inputssn" placeholder="SSN">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputbank" class="col-sm-4 col-form-label">Account Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['bank_account'], ENT_QUOTES, 'UTF-8'); ?>" id="inputbank" name="inputbank" placeholder="SSN">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputnewPassword" class="col-sm-4 col-form-label">New Password</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" id="inputnewPassword" name="inputnewPassword" placeholder="New Password">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputcnfPassword" class="col-sm-4 col-form-label">Confirm New Password</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" id="inputcnfPassword" name="inputcnfPassword" placeholder="Confirm Password">
                                    </div>
                                </div>

                                <div class="form-group text-right">
                                    <div class="col-sm-12 ">
                                        <input type="submit" name="submit" class="btn btn-success" value="Update">
                                    </div>
                                </div>
                            </form>
                        <?php } else { ?>
                            User not found.
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn " data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payslips -->
    <section id="reimbursmentpage" style="margin-left:10px;">
        <div class="reimbursmentwrapper">
            <h4 class="titletext"> Upload Payslips</h4>
            <div class="container" style="margin-left:-10px;">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#uploadpayslip">Upload Payslips</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="uploadpayslip">
                        <div class="row border g-0  shadow-sm">
                            <div class="col p-4">
                                <form method="POST" action="#" enctype="multipart/form-data">
                                    <div class="myform">
                                        <div class="form-row align-items-center">
                                            <div class="col-auto my-1">
                                                <label for="remtype" class="mr-sm-2">Name</label>
                                                <select class="custom-select mr-sm-4" id="remname" name="remname">
                                                    <option selected>Choose</option>
                                                    <?php
                                                        $stmt = $conn->prepare("select id, username from `users` where organization_id = ? AND isadmin = 1;");
                                                        $stmt->bind_param("i", $_SESSION['organization_id']);
                                                        $stmt->execute();
                                                        $remresult = $stmt->get_result();
            
                                                        if (!$remresult) {
                                                            die("Invalid Query: ");
                                                        }

                                                        while ($remrow = $remresult->fetch_assoc()) {
                                                        echo "<option value=" . htmlspecialchars($remrow["id"], ENT_QUOTES, 'UTF-8') . ">" . htmlspecialchars($remrow["username"], ENT_QUOTES, 'UTF-8') . " </option>";
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-auto my-1">
                                                <label for="filedon" class="mr-sm-4">Date</label>
                                                <input type="date" class="form-control mr-sm-4" name="date" id="date" placeholder="Date">
                                            </div>
                                            <div class="col-auto my-1">
                                                <label for="file" class="mr-sm-4">File</label>
                                                <input type="file" class="form-control mr-sm-4" name="file" id="file">
                                            </div>

                                        </div>
                                        <div class="form-row align-items-center">
                                            <div class="form-group col-6">
                                                <div class="mr-sm-8">
                                                    <input type="submit" name="request" value="Upload" class="btn btn-primary">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="viewpayslips">
                        <div class="row border g-0  shadow-sm">
                            <div class="col p-4">
                                <div class="table-responsive">
                                    <table class="table bg-white">
                                        <thead class="text-light">
                                            <tr id="tableheadingrem">
                                                <th>Date</th>
                                                <th>Payslip</th>
                                                <th>File</th>
                                            </tr>
                                        </thead>
                                        <tbody class="tablebodyrows">
                                            <?php
                                            $stmt = $conn->prepare("select * from `payslips` where id=? ORDER BY date DESC LIMIT 4;");
                                            $stmt->bind_param("i", $userid);
                                            $stmt->execute();
                                            $remresult = $stmt->get_result();

                                            if (!$remresult) {
                                                die("Invalid Query: ");
                                            }

                                            while ($remrow = $remresult->fetch_assoc()) {
                                                $date1=date_create($remrow["date"]);
                                                echo "<tr>
                                                    <td>" . date_format($date1,"Y F") . "</td>
                                                    <td>" . htmlspecialchars($remrow['payslip_id'], ENT_QUOTES, 'UTF-8') . "</td>
                                                    <td><a href=" . htmlspecialchars($remrow["file"], ENT_QUOTES, 'UTF-8') . " target='_blank'>
                                                    <button class='btn btn-primary' type='button'>View File</button></a></td>                
                                                </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/ums/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
</body>

</html>
