<?php
include 'db_connect.php'; // your connection file

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $NHTS = isset($_POST['NHTS']) ? 1 : 0;
    $FourPs = isset($_POST['4Ps']) ? 1 : 0;
    $NonNHTS = isset($_POST['NonNHTS']) ? 1 : 0;
    $PWD = isset($_POST['PWD']) ? 1 : 0;

    $NHTS_HH_no = $_POST['NHTS_HH_no'];
    $code_no = $_POST['code_no'];
    $family_no = $_POST['family_no'];
    $NHTS_member_head = $_POST['NHTS_member_head'];
    $DOB_head = $_POST['DOB_head'];
    $relationship_to_household_head = $_POST['relationship_to_household_head'];
    $PhilHealth_member = $_POST['PhilHealth_member'] ?? 'no';
    $PhilHealth_no = $_POST['PhilHealth_no'];
    $mother_maiden_name = $_POST['mother_maiden_name'];
    $name = $_POST['name'];
    $DOB = $_POST['DOB'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $status = $_POST['status'];
    $address = $_POST['address'];
    $hx_of_allergies = $_POST['hx_of_allergies'];
    $blood_type = $_POST['blood_type'];
    $date_recorded = $_POST['date_recorded'];
    $BP = $_POST['BP'];
    $temp = $_POST['temp'];
    $weight = $_POST['weight'];
    $RR = $_POST['RR'];
    $PR = $_POST['PR'];
    $height = $_POST['height'];

    $sql = "INSERT INTO healthcheck_patients
    (NHTS, FourPs, NonNHTS, PWD, NHTS_HH_no, code_no, family_no, NHTS_member_head, DOB_head,
    relationship_to_household_head, PhilHealth_member, PhilHealth_no, mother_maiden_name, name,
    DOB, age, sex, status, address, hx_of_allergies, blood_type, date_recorded, BP, temp, weight, RR, PR, height)
    VALUES
    ($NHTS, $FourPs, $NonNHTS, $PWD, '$NHTS_HH_no', '$code_no', '$family_no', '$NHTS_member_head', '$DOB_head',
    '$relationship_to_household_head', '$PhilHealth_member', '$PhilHealth_no', '$mother_maiden_name', '$name',
    '$DOB', $age, '$sex', '$status', '$address', '$hx_of_allergies', '$blood_type', '$date_recorded', '$BP', '$temp', '$weight', $RR, $PR, '$height')";

    if ($conn->query($sql)) {
        $message = "Patient record saved successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Individual Health Record | RHU Management System</title>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<style>
/* === Your full CSS from earlier snippet === */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background-color:#e9f8f4; min-height:100vh; }
.header { position:fixed; top:0; left:0; width:100%; height:70px; background-color:#1e8c72; color:white; display:flex; align-items:center; padding:0 30px; z-index:1000; }
.sidebar { position:fixed; top:70px; left:0; width:240px; height:calc(100% - 70px); background-color:#57cba7; color:white; padding:25px 20px; }
.sidebar ul { list-style:none; }
.sidebar ul li a { display:block; color:#fff; text-decoration:none; margin-top:10px; padding:10px 15px; border-radius:8px; transition:all 0.3s ease; }
.sidebar ul li a:hover { background-color:#b2f2dc; color:#1e8c72; font-weight:600; }
.sidebar ul li a.active { background-color:#b2f2dc; color:#1e8c72; font-weight:600; transform:scale(1.1); }
.main { margin-left:260px; padding:100px 40px 50px; background-color:#e9f8f4; min-height:100vh; }
.content { background:#fff; border-radius:12px; padding:30px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
.content legend { text-align:center; color:#2e7267; margin-bottom:25px; font-weight:600; font-size:24px; }
.fillup_form fieldset { border:1px solid #b5e3da; border-radius:8px; padding:20px; margin-bottom:25px; }
.fillup_form legend { padding:0 10px; color:#1e8c72; font-weight:600; }
.form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:15px 30px; margin-top:15px; }
label { display:block; font-weight:500; color:#2e7267; margin-bottom:5px; }
input[type="text"], input[type="date"], input[type="number"], textarea { width:100%; padding:7px 10px; border:1px solid #b5e3da; border-radius:6px; font-size:14px; outline:none; background-color:#f9fdfc; }
input:focus, textarea:focus { border-color:#1e8c72; box-shadow:0 0 5px rgba(30,140,114,0.3); }
.radio-group, .checkbox-group { display:flex; flex-wrap:wrap; gap:15px; align-items:center; }
.submit-btn { display:block; margin:25px auto 0; background-color:#1e8c72; color:white; padding:12px 25px; border:none; border-radius:8px; font-size:15px; font-weight:500; cursor:pointer; transition:0.3s; }
.submit-btn:hover { background-color:#3f8d82; }
</style>
</head>
<body>
<header class="header">
  <h1>Health Check Up Management System</h1>
</header>

<div class="sidebar">
  <ul>
    <li><a href="homepage.php"><ion-icon name="home-outline"></ion-icon> Home</a></li>
    <li><a href="healthcheckdb.php"><ion-icon name="grid-outline"></ion-icon> Dashboard</a></li>
    <li><a href="#" class="active"><ion-icon name="person-add-outline"></ion-icon> Add Patient</a></li>
  </ul>
</div>

<main class="main">
  <section class="content">
    <?php if(!empty($message)) echo "<p style='color:green;font-weight:bold;'>$message</p>"; ?>
    <legend>📋 Individual Treatment Record</legend>

    <form class="fillup_form" method="POST">
      <fieldset>
        <div class="checkbox-group">
          <label><input type="checkbox" name="NHTS"> NHTS</label>
          <label><input type="checkbox" name="4Ps"> 4Ps</label>
          <label><input type="checkbox" name="NonNHTS"> Non-NHTS</label>
          <label><input type="checkbox" name="PWD"> PWD</label>
        </div>

        <div class="form-grid">
          <div><label>NHTS HH No.:</label><input type="text" name="NHTS_HH_no"></div>
          <div><label>Code No.:</label><input type="text" name="code_no"></div>
          <div><label>Family No.:</label><input type="text" name="family_no"></div>
          <div><label>NHTS Member/Head:</label><input type="text" name="NHTS_member_head"></div>
          <div><label>DOB (Head/Member):</label><input type="date" name="DOB_head"></div>
          <div><label>Relationship to Household Head:</label><input type="text" name="relationship_to_household_head"></div>
          <div>
            <label>PhilHealth Member:</label>
            <div class="radio-group">
              <label><input type="radio" name="PhilHealth_member" value="yes"> Yes</label>
              <label><input type="radio" name="PhilHealth_member" value="no"> No</label>
            </div>
          </div>
          <div><label>PhilHealth No.:</label><input type="text" name="PhilHealth_no"></div>
        </div>

        <br>
        <div>
          <label>Mother’s Maiden Name (if Married):</label>
          <input type="text" name="mother_maiden_name">
        </div>
        <br>

        <div class="form-grid">
          <div><label>Name:</label><input type="text" name="name"></div>
          <div><label>DOB:</label><input type="date" name="DOB"></div>
          <div><label>Age:</label><input type="number" name="age"></div>
          <div><label>Sex:</label><input type="text" name="sex"></div>
          <div><label>Status:</label><input type="text" name="status"></div>
        </div>

        <br>
        <div><label>Address:</label><input type="text" name="address"></div>

        <div class="form-grid">
          <div><label>Hx. of Allergies:</label><input type="text" name="hx_of_allergies"></div>
          <div><label>Blood Type:</label><input type="text" name="blood_type"></div>
          <div><label>Date:</label><input type="date" name="date_recorded"></div>
          <div><label>BP:</label><input type="text" name="BP" placeholder="mmHg"></div>
          <div><label>Temp:</label><input type="text" name="temp" placeholder="°C"></div>
          <div><label>Weight:</label><input type="text" name="weight" placeholder="kg"></div>
          <div><label>RR:</label><input type="number" name="RR" placeholder="cpm"></div>
          <div><label>PR:</label><input type="number" name="PR" placeholder="bpm"></div>
          <div><label>Height:</label><input type="text" name="height" placeholder="cm"></div>
        </div>
      </fieldset>

      <button type="submit" class="submit-btn">Save Record</button>
    </form>
  </section>
</main>
</body>
</html>
