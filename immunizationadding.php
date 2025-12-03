<?php
session_start();
include 'db_connect.php'; // make sure this file connects to your database

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Convert empty strings to NULL
function null_if_empty($val) {
    $val = trim($val ?? '');
    return $val === '' ? NULL : $val;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Patient Info
    $first_name = null_if_empty($_POST['first_name']);
    $middle_name = null_if_empty($_POST['middle_name']);
    $last_name = null_if_empty($_POST['last_name']);
    $suffix = null_if_empty($_POST['suffix']);
    $sex = null_if_empty($_POST['sex']);
    $dob = null_if_empty($_POST['dob']);
    $contact_number = null_if_empty($_POST['contact_number']);
    
    // Address
    $province_code = null_if_empty($_POST['province']);
    $municipality_code = null_if_empty($_POST['municipality']);
    $barangay = null_if_empty($_POST['barangay']);
    
    // Vaccine Info
    $vaccine_type = $_POST['vaccine'] === 'Other' ? null_if_empty($_POST['otherVaccine']) : null_if_empty($_POST['vaccine']);
    $dose_number = null_if_empty($_POST['dose']);
    $date_administered = null_if_empty($_POST['date_administered']);
    $healthcare_provider = null_if_empty($_POST['provider']);
    $additional_notes = null_if_empty($_POST['notes']);

    // Prepare INSERT
    $stmt = $conn->prepare("INSERT INTO immunization_records 
        (first_name, middle_name, last_name, suffix, sex, dob, contact_number, province_code, municipality_code, barangay, vaccine_type, dose_number, date_administered, healthcare_provider, additional_notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssssssisss"
,
        $first_name, $middle_name, $last_name, $suffix, $sex, $dob, $contact_number,
        $province_code, $municipality_code, $barangay, $vaccine_type, $dose_number,
        $date_administered, $healthcare_provider, $additional_notes
    );

    if ($stmt->execute()) {
        $message = "Record saved successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Immunization Record | RHU Management System</title>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <link rel="stylesheet" href="immunizationadd.css" />
</head>

<body>
  <!-- Header -->
  <header class="header">
    <h1>Immunization Management System</h1>
  </header>

  <!-- Sidebar -->
  <div class="sidebar">
    <ul>
      <li><a href="homepage.php"><ion-icon name="home-outline"></ion-icon> Home</a></li>
      <li><a href="immunizationdb.php"><ion-icon name="grid-outline"></ion-icon> Dashboard</a></li>
      <li><a href="#" class="active"><ion-icon name="person-add-outline"></ion-icon> Add Patient</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <main class="main">
    <section class="content">
      <legend>📋 Immunization Record</legend>

      <?php if($message): ?>
      <p style="color:green; text-align:center;"><?= $message ?></p>
      <?php endif; ?>

      <form class="fillup_form" method="POST" action="immunizationadd.php">
        <!-- Patient Info -->
        <fieldset>
          <div class="form-grid">
            <div><label>First Name:</label><input type="text" name="first_name" required></div>
            <div><label>Middle Name:</label><input type="text" name="middle_name"></div>
            <div><label>Last Name:</label><input type="text" name="last_name" required></div>
            <div><label>Suffix:</label><input type="text" name="suffix"></div>
            <div><label>Sex:</label><input type="text" name="sex" required></div>
            <div><label>Date of Birth:</label><input type="date" name="dob" required></div>
            <div><label>Contact Number:</label><input type="tel" name="contact_number"></div>
          </div>

          <!-- Address -->
          <div><label><b>Address:</b></label>
            <div class="form-grid">
              <div>
                <select id="province" name="province" required>
                  <option value="" disabled selected hidden>Select Province</option>
                </select>
              </div>

              <div>
                <select id="municipality" name="municipality" required>
                  <option value="" disabled selected hidden>Select Municipality / City</option>
                </select>
              </div>

              <div>
                <select id="barangay" name="barangay" required>
                  <option value="" disabled selected hidden>Select Barangay</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Vaccine Info -->
          <div class="form-grid">
            <div>
              <label for="vaccine">Vaccine Type:</label>
              <select id="vaccine" name="vaccine" required>
                <option value="" disabled selected hidden>Select Vaccine</option>
                <option value="MMR">MMR (Measles, Mumps, Rubella)</option>
                <option value="DTaP">DTaP (Diphtheria, Tetanus, Pertussis)</option>
                <option value="Polio">Polio</option>
                <option value="Hepatitis B">Hepatitis B</option>
                <option value="COVID-19">COVID-19</option>
                <option value="Other">Other (specify)</option>
              </select>
              <input type="text" id="otherVaccine" name="otherVaccine" placeholder="Please specify the vaccine" style="display:none;">
            </div>

            <div><label>Dose Number:</label><input type="number" name="dose" min="1" required></div>
            <div><label>Date Administered:</label><input type="date" name="date_administered" required></div>
            <div><label>Healthcare Provider:</label><input type="text" name="provider"></div>
            <div><label>Additional Notes:</label><textarea name="notes" rows="3"></textarea></div>
          </div>

        </fieldset>

        <button type="submit" class="submit-btn">Submit Record</button>
      </form>
    </section>
  </main>

  <script>
    // JavaScript for province/municipality/barangay dropdowns
    document.addEventListener("DOMContentLoaded", () => {
      const provinceSelect = document.getElementById("province");
      const municipalitySelect = document.getElementById("municipality");
      const barangaySelect = document.getElementById("barangay");

      fetch("https://psgc.gitlab.io/api/provinces/")
        .then(res => res.json())
        .then(data => {
          data.sort((a,b)=>a.name.localeCompare(b.name));
          provinceSelect.innerHTML = '<option disabled selected>Select Province</option>';
          data.forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.code;
            opt.textContent = item.name;
            provinceSelect.appendChild(opt);
          });
        });

      provinceSelect.addEventListener("change", () => {
        municipalitySelect.disabled = false;
        barangaySelect.disabled = true;
        municipalitySelect.innerHTML = '<option disabled selected>Loading...</option>';
        barangaySelect.innerHTML = '<option disabled selected>Select municipality first</option>';
        fetch(`https://psgc.gitlab.io/api/provinces/${provinceSelect.value}/cities-municipalities/`)
          .then(res => res.json())
          .then(data => {
            data.sort((a,b)=>a.name.localeCompare(b.name));
            municipalitySelect.innerHTML = '<option disabled selected>Select Municipality / City</option>';
            data.forEach(item => {
              const opt = document.createElement("option");
              opt.value = item.code;
              opt.textContent = item.name;
              municipalitySelect.appendChild(opt);
            });
          });
      });

      municipalitySelect.addEventListener("change", () => {
        barangaySelect.disabled = false;
        barangaySelect.innerHTML = '<option disabled selected>Loading...</option>';
        fetch(`https://psgc.gitlab.io/api/cities-municipalities/${municipalitySelect.value}/barangays/`)
          .then(res => res.json())
          .then(data => {
            data.sort((a,b)=>a.name.localeCompare(b.name));
            barangaySelect.innerHTML = '<option disabled selected>Select Barangay</option>';
            data.forEach(item => {
              const opt = document.createElement("option");
              opt.value = item.name;
              opt.textContent = item.name;
              barangaySelect.appendChild(opt);
            });
          });
      });
    });

    // Show/hide Other vaccine field
    const vaccineSelect = document.getElementById("vaccine");
    const otherVaccineInput = document.getElementById("otherVaccine");
    vaccineSelect.addEventListener("change", () => {
      if (vaccineSelect.value === "Other") {
        otherVaccineInput.style.display = "block";
        otherVaccineInput.required = true;
      } else {
        otherVaccineInput.style.display = "none";
        otherVaccineInput.required = false;
        otherVaccineInput.value = "";
      }
    });
  </script>
</body>
</html>
