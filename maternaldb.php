<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

include 'db_connect.php';

// Fetch summary rows for dashboard
// We select a "date_recorded" fallback using COALESCE for safer display if your table doesn't have a created_at
$sql = "SELECT 
            id,
            maiden_family_name,
            maiden_first_name,
            maiden_middle_name,
            age,
            COALESCE(created_at, delivery_date, lmp, dob) AS date_recorded
        FROM maternal_records
        ORDER BY id DESC";
$result = $conn->query($sql);
$total_patients = $result ? $result->num_rows : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Maternal Dashboard | RHU Management System</title>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

  <style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background-color: #e9f8f4;
  min-height: 100vh;
}

/* HEADER */
.header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 76px;
  background-color: #1e8c72;
  color: white;
  display: flex;
  align-items: center;
  padding: 0 40px;
  z-index: 1000;
}

/* SIDEBAR */
.sidebar {
  position: fixed;
  top: 70px;
  left: 0;
  width: 240px;
  height: calc(100% - 70px);
  background-color: #57cba7;
  color: white;
  padding: 25px 20px;
  overflow-y: auto;
}

.sidebar ul {
  list-style: none;
}

.sidebar ul li a {
  display: block;
  color: #fff;
  text-decoration: none;
  margin-top: 10px;
  padding: 10px 15px;
  border-radius: 8px;
  transition: all .6s ease;
}

.sidebar ul li a:hover,
.sidebar ul li a.active {
  background-color: #b2f2dc;
  color: #1e8c72;
  font-weight: 600;
  transform: scale(1.05);
}
main {
  margin-left: 260px;
  padding: 100px 40px 40px; /* space for fixed header */
  background-color: #e9f8f4;
  min-height: 100vh;
}

.content {
  background: #fff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.content h2 {
  text-align: center;
  color: #2e7267;
  margin-bottom: 25px;
  font-weight: 600;
}

/* ===== SEARCH + COUNTER ===== */
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.search-box {
  display: flex;
  align-items: center;
  background-color: #f0fbf8;
  border: 1px solid #b5e3da;
  border-radius: 25px;
  padding: 8px 15px;
  width: 50%;
}

.search-box ion-icon {
  color: #3f8d82;
  font-size: 18px;
  margin-right: 8px;
}

.search-box input {
  border: none;
  outline: none;
  background: transparent;
  flex: 1;
  font-size: 14px;
}

.total {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 500;
  color: #2e7267;
}

.total .count {
  background: #e9f8f4;
  border: 1px solid #a4dcd2;
  padding: 5px 15px;
  border-radius: 8px;
  font-weight: 600;
}

/* ===== TABLE ===== */
.table-container {
  border: 2px solid #3f8d82;
  border-radius: 10px;
  overflow: hidden;
  margin-top: 15px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  font-size: 15px;
  border-bottom: 1px solid #e0efeb;
}

th {
  background-color: #c5f4e5;
  color: #2e7267;
  font-weight: 600;
}
th:nth-child(1),
td:nth-child(1) { width: 8%; }  /* File No. */
th:nth-child(2),
td:nth-child(2) { width: 45%; }  /* Name */
th:nth-child(3),
td:nth-child(3) { width: 12%; }  /* Age */
th:nth-child(4),
td:nth-child(4) { width: 20%; }  /* Date */
th:nth-child(5),
td:nth-child(5) { width: 15%; }  /* Details */

tr:nth-child(even) {
  background-color: #f9fdfc;
}

.view-btn {
  background-color: #5eb5a9;
  color: white;
  border: none;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-btn:hover {
  background-color: #3f8d82;
}

/* modal styles (kept here for single-file CSS) */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
.modal-content {
  width: 95%;
  max-width: 950px;
  max-height: 90vh;
  overflow: auto;
  background: #fff;
  border-radius: 12px;
  padding: 20px 24px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  position: relative;
}
.modal-content h2 {
  margin: 0 0 12px;
  text-align: center;
  font-size: 1.25rem;
}
.modal-close {
  position: absolute;
  top: 12px;
  right: 12px;
  background: transparent;
  border: none;
  font-size: 28px;
  cursor: pointer;
}
.modal-body { padding: 8px 0 20px; }
.modal-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
.modal-grid input, .modal-grid textarea {
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #d0d0d0;
}
.checkbox-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0,1fr));
  gap: 8px;
  margin-top: 8px;
}
@media (max-width: 860px) {
  .modal-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 520px) {
  .modal-grid { grid-template-columns: 1fr; }
  .checkbox-grid { grid-template-columns: repeat(2, 1fr); }
}
.modal-footer { display:flex; justify-content:center; gap:8px; padding:12px 0 6px; }
.btn { padding: 8px 14px; border-radius: 8px; border: none; cursor: pointer; }

/* ===== SCROLLBAR ===== */
.sidebar::-webkit-scrollbar {
  width: 6px;
}
.sidebar::-webkit-scrollbar-thumb {
  background-color: #2f756a;
  border-radius: 3px;
}
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <h1>Maternal Health Management</h1>
  </header>

  <!-- Sidebar -->
  <div class="sidebar">
    <ul>
      <li><a href="homepage.html"><ion-icon name="home-outline"></ion-icon> Home</a></li>
      <li><a href="#" class="active"><ion-icon name="grid-outline"></ion-icon> Dashboard</a></li>
      <li><a href="maternaladd.php"><ion-icon name="person-add-outline"></ion-icon> Add Patient</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <main class="main">
    <section class="content">
      <h2>LIST OF MATERNAL PATIENTS</h2>

      <div class="top-bar">
        <div class="search-box">
          <ion-icon name="search-outline"></ion-icon>
          <input id="tableSearch" type="text" placeholder="Search by name or file no." />
        </div>
        <div class="total">
          <span>Total of Patient:</span>
          <div class="count"><?php echo $total_patients; ?></div>
        </div>
      </div>

      <div class="table-container">
        <table id="patientsTable">
          <thead>
            <tr>
              <th>File No.</th>
              <th>Name</th>
              <th>Age</th>
              <th>Date Recorded</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $id = (int)$row['id'];
                    $name = trim($row['maiden_family_name'] . ', ' . $row['maiden_first_name'] . ' ' . $row['maiden_middle_name']);
                    $age = htmlspecialchars($row['age']);
                    $date = htmlspecialchars($row['date_recorded']);
                    echo "<tr>";
                    echo "<td>" . $id . "</td>";
                    echo "<td>" . htmlspecialchars($name) . "</td>";
                    echo "<td>" . $age . "</td>";
                    echo "<td>" . $date . "</td>";
                    echo "<td><button class='view-btn' data-id='" . $id . "'>View</button></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No records found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- VIEW MODAL -->
  <div id="maternalModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <button class="modal-close" id="modalClose" aria-label="Close">&times;</button>
      <h2 id="modalTitle">Maternal Patient Details</h2>

      <div class="modal-body">
        <form class="modal-form" autocomplete="off">
          <!-- SECTION 1 -->
          <fieldset>
            <legend>1. Mother’s Identification & Vitals</legend>
            <div class="modal-grid">
              <div><label>Family Number</label><input id="family_number" readonly></div>
              <div><label>NHTS/4Ps Status</label><input id="nhts_status" readonly></div>
              <div><label>Code No.</label><input id="code_no" readonly></div>
              <div><label>PhilHealth No.</label><input id="philhealth_no" readonly></div>
              <div><label>Civil Status</label><input id="civil_status" readonly></div>
              <div><label>Contact No.</label><input id="contact_no" readonly></div>
              <div><label>Name of Clinic</label><input id="clinic_name" readonly></div>
              <div><label>Maiden Family Name</label><input id="maiden_family_name" readonly></div>
              <div><label>Maiden First Name</label><input id="maiden_first_name" readonly></div>
              <div><label>Maiden Middle Name</label><input id="maiden_middle_name" readonly></div>
              <div><label>Age</label><input id="age" readonly></div>
              <div><label>Address (Barangay/City)</label><input id="address" readonly></div>
              <div><label>Date of Birth</label><input id="dob" readonly></div>
              <div><label>Occupation</label><input id="occupation" readonly></div>
              <div><label>Height (m)</label><input id="height" readonly></div>
              <div><label>BMI</label><input id="bmi" readonly></div>
              <div><label>MUAC (cm)</label><input id="muac" readonly></div>
            </div>
          </fieldset>

          <!-- SECTION 2 -->
          <fieldset>
            <legend>2. Husband’s Information</legend>
            <div class="modal-grid">
              <div><label>Family Name</label><input id="husband_family" readonly></div>
              <div><label>First Name</label><input id="husband_first" readonly></div>
              <div><label>Middle Name</label><input id="husband_middle" readonly></div>
              <div><label>Occupation</label><input id="husband_occupation" readonly></div>
            </div>
          </fieldset>

          <!-- SECTION 3 -->
          <fieldset>
            <legend>3. Obstetrical History & Complications</legend>
            <div class="modal-grid">
              <div><label>No. of Children Alive</label><input id="children_alive" readonly></div>
              <div><label>Living Children</label><input id="living_children" readonly></div>
              <div><label>No. of Abortion</label><input id="abortions" readonly></div>
              <div><label>No. of Stillbirth</label><input id="stillbirth_count" readonly></div>
            </div>

            <div class="checkbox-grid">
              <label><input type="checkbox" id="complication_hemorrhage" disabled> Hemorrhage</label>
              <label><input type="checkbox" id="complication_toxemia" disabled> Toxemia</label>
              <label><input type="checkbox" id="complication_placenta_previa" disabled> Placenta Previa</label>
              <label><input type="checkbox" id="complication_sepsis" disabled> Sepsis</label>
              <label><input type="checkbox" id="complication_hypertension" disabled> Hypertension</label>
            </div>
          </fieldset>

          <!-- SECTION 4 -->
          <fieldset>
            <legend>4. Current Signs and Symptoms</legend>
            <div class="checkbox-grid">
              <label><input type="checkbox" id="symptom_nausea" disabled> Nausea</label>
              <label><input type="checkbox" id="symptom_vomiting" disabled> Vomiting</label>
              <label><input type="checkbox" id="symptom_headache" disabled> Headache</label>
              <label><input type="checkbox" id="symptom_dizziness" disabled> Dizziness</label>
              <label><input type="checkbox" id="symptom_leucorrhea" disabled> Leucorrhea</label>
              <label><input type="checkbox" id="symptom_edema" disabled> Edema</label>
              <label><input type="checkbox" id="symptom_cramps" disabled> Cramps</label>
              <label><input type="checkbox" id="symptom_bleeding" disabled> Bleeding</label>
              <label><input type="checkbox" id="symptom_pruritis" disabled> Pruritis</label>
              <label><input type="checkbox" id="symptom_blurring" disabled> Blurring</label>
            </div>
          </fieldset>

          <!-- SECTION 5 -->
          <fieldset>
            <legend>5. Dietary Pattern, Preparation & Delivery Info</legend>
            <div class="modal-grid">
              <div><label>Food Regularly Taken</label><input id="food_regular" readonly></div>
              <div><label>Food Avoided</label><input id="food_avoided" readonly></div>
              <div><label>Ante/Post-partum</label><input id="preg_stage" readonly></div>
              <div><label>Prepared for Breastfeeding?</label><input id="prepared_bf" readonly></div>
              <div><label>Reason if not prepared</label><input id="not_prepared_reason" readonly></div>
              <div><label>Date of Delivery</label><input id="delivery_date" readonly></div>
              <div><label>Type of Delivery</label><input id="delivery_type" readonly></div>
              <div><label>Place of Delivery</label><input id="delivery_place" readonly></div>
              <div><label>Attended By</label><input id="attended_by" readonly></div>
              <div><label>Designation</label><input id="designation" readonly></div>
              <div style="grid-column: span 3;"><label>Delivery Address</label><input id="delivery_address" readonly></div>
            </div>
          </fieldset>

          <!-- SECTION 6 -->
          <fieldset>
            <legend>6. Ante-partum (GTPAL) & Lab Data</legend>
            <div class="modal-grid">
              <div><label>LMP</label><input id="lmp" readonly></div>
              <div><label>EDC</label><input id="edc" readonly></div>
              <div><label>Risk Code</label><input id="risk_code" readonly></div>
              <div><label>Deworming</label><input id="deworming" readonly></div>
              <div><label>Gravida</label><input id="gravida" readonly></div>
              <div><label>Term</label><input id="term" readonly></div>
              <div><label>Para</label><input id="para" readonly></div>
              <div><label>Abortion</label><input id="abortion" readonly></div>
            </div>
          </fieldset>
        </form>
      </div>

      <div class="modal-footer">
        <button id="modalCloseBtn" class="btn">Close</button>
      </div>
    </div>
  </div>

<script>
// Simple client-side search
document.getElementById('tableSearch').addEventListener('input', function(e){
  const q = e.target.value.toLowerCase();
  const rows = document.querySelectorAll('#patientsTable tbody tr');
  rows.forEach(r => {
    const text = r.innerText.toLowerCase();
    r.style.display = text.indexOf(q) === -1 ? 'none' : '';
  });
});

// Modal + fetch logic
(function(){
  function $id(id){ return document.getElementById(id); }
  function setVal(id, v){
    const el = $id(id);
    if (!el) return;
    el.value = v === null || v === undefined ? '' : v;
  }
  function setCheckbox(id, val){
    const el = $id(id);
    if (!el) return;
    el.checked = !!Number(val);
  }

  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      if (!id) return;
      fetch('fetch_maternal.php?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(r => {
          if (!r.ok) throw new Error('Record not found');
          return r.json();
        })
        .then(data => {
          // scalar mapping
          const map = [
            'family_number','nhts_status','code_no','philhealth_no','civil_status','contact_no','clinic_name',
            'maiden_family_name','maiden_first_name','maiden_middle_name','age','address','dob','occupation',
            'height','bmi','muac','husband_family','husband_first','husband_middle','husband_occupation',
            'children_alive','living_children','abortions','stillbirth_count',
            'food_regular','food_avoided','preg_stage','prepared_bf','not_prepared_reason',
            'delivery_date','delivery_type','delivery_place','attended_by','designation','delivery_address',
            'lmp','edc','risk_code','deworming','gravida','term','para','abortion'
          ];
          map.forEach(k => setVal(k, data[k]));

          const checkboxFields = [
            'complication_hemorrhage','complication_toxemia','complication_placenta_previa','complication_sepsis','complication_hypertension',
            'symptom_nausea','symptom_vomiting','symptom_headache','symptom_dizziness','symptom_leucorrhea','symptom_edema','symptom_cramps',
            'symptom_bleeding','symptom_pruritis','symptom_blurring'
          ];
          checkboxFields.forEach(k => setCheckbox(k, data[k]));

          const modal = $id('maternalModal');
          modal.style.display = 'flex';
          modal.setAttribute('aria-hidden','false');
          document.body.style.overflow = 'hidden';
        })
        .catch(err => alert('Unable to load record: ' + err.message));
    });
  });

  const closeEls = [ $id('modalClose'), $id('modalCloseBtn') ];
  closeEls.forEach(el => {
    if (!el) return;
    el.addEventListener('click', () => {
      const modal = $id('maternalModal');
      modal.style.display = 'none';
      modal.setAttribute('aria-hidden','true');
      document.body.style.overflow = '';
    });
  });

  document.getElementById('maternalModal').addEventListener('click', function(e){
    if (e.target === this) {
      this.style.display = 'none';
      this.setAttribute('aria-hidden','true');
      document.body.style.overflow = '';
    }
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      const modal = document.getElementById('maternalModal');
      if (modal.style.display === 'flex') {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden','true');
        document.body.style.overflow = '';
      }
    }
  });
})();
</script>

</body>
</html>
<?php $conn->close(); ?>
