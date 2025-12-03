<?php
session_start();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Block access if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; // your DB connection

// Fetch single patient for modal
if (isset($_GET['get_patient_id'])) {
    $id = intval($_GET['get_patient_id']);
    $res = mysqli_query($conn, "SELECT * FROM maternal_records WHERE id=$id LIMIT 1");
    if ($row = mysqli_fetch_assoc($res)) {
        echo json_encode($row);
    } else {
        echo json_encode(['error'=>'Patient not found']);
    }
    exit;
}

// Handle update in the same file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_patient'])) {
    $id = intval($_POST['id']);
    $fields = [
        'family_number','nhts_status','code_no','philhealth_no','civil_status','contact_no',
        'clinic_name','maiden_family_name','maiden_first_name','maiden_middle_name','age',
        'dob','address','occupation','height','bmi','muac',
        'husband_family','husband_first','husband_middle','husband_occupation',
        'children_alive','living_children','abortions','stillbirth_count',
        'complication_hemorrhage','complication_toxemia','complication_placenta_previa',
        'complication_sepsis','complication_hypertension',
        'symptom_nausea','symptom_vomiting','symptom_headache','symptom_dizziness',
        'symptom_leucorrhea','symptom_edema','symptom_cramps','symptom_bleeding',
        'symptom_pruritis','symptom_blurring',
        'food_regular','food_avoided','preg_stage','prepared_bf','not_prepared_reason',
        'delivery_date','delivery_type','delivery_place','attended_by','designation','delivery_address',
        'lmp','edc','risk_code','deworming','gravida','term','para','abortion'
    ];
    $setArr = [];
    foreach($fields as $f){
        $value = isset($_POST[$f]) ? mysqli_real_escape_string($conn, $_POST[$f]) : '';
        $setArr[] = "$f='$value'";
    }
    $query = "UPDATE maternal_records SET ".implode(',', $setArr)." WHERE id=$id";
    echo mysqli_query($conn, $query) ? "success" : "error";
    exit;
}

// Fetch all patients
$query = "SELECT * FROM maternal_records ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$total_patients = mysqli_num_rows($result);
$patients = [];
while ($row = mysqli_fetch_assoc($result)) { $patients[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Maternal Dashboard | RHU Management System</title>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<link rel="stylesheet" href="maternaldb.css">
<style>
.modal {display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:2000;}
.modal-content {background:#fff; border-radius:12px; padding:30px; width:90%; max-width:1000px; max-height:90%; overflow-y:auto; position:relative;}
.close-btn {position:absolute; top:15px; right:15px; background:#e74c3c; color:#fff; border:none; padding:5px 10px; border-radius:8px; cursor:pointer;}
.view-record-section {border:1px solid #b5e3da; border-radius:8px; padding:20px; margin-bottom:20px;}
.view-grid {display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px 30px; margin-top:10px;}
.view-grid1 {display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:8px;}
.field-item {margin-bottom:5px;}
.field-label {font-size:13px; font-weight:500; color:#668b83;}
.field-value input, .field-value select {width:100%; padding:5px; font-size:14px;}
</style>
</head>
<body>

<header class="header"><h1>Maternal Management System</h1></header>
<div class="sidebar">
<ul>
<li><a href="homepage.php"><ion-icon name="home-outline"></ion-icon> Home</a></li>
<li><a href="#" class="active"><ion-icon name="grid-outline"></ion-icon> Dashboard</a></li>
<li><a href="maternaladd.php"><ion-icon name="person-add-outline"></ion-icon> Add Patient</a></li>
</ul>
</div>

<main class="main">
<section class="content">
<h2>LIST OF PATIENT</h2>
<div class="top-bar">
    <div class="search-box"><ion-icon name="search-outline"></ion-icon><input type="text" placeholder="Search patient..." id="searchInput"></div>
    <div class="total"><span>Total Patients:</span><div class="count"><?php echo $total_patients; ?></div></div>
</div>

<div class="table-container">
<table id="patientTable">
<thead>
<tr><th>File No.</th><th>Name (Last, First M.)</th><th>Age</th><th>Date</th><th>Details</th></tr>
</thead>
<tbody>
<?php foreach($patients as $row):
    $middleInitial = $row['maiden_middle_name'] ? strtoupper(substr($row['maiden_middle_name'],0,1)) . "." : "";
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['maiden_family_name'] . ", " . $row['maiden_first_name'] . " " . $middleInitial; ?></td>
<td><?php echo $row['age']; ?></td>
<td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
<td><button class="view-btn" onclick="openModal(<?php echo $row['id']; ?>)">View / Edit</button></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- Modal -->
<div class="modal" id="patientModal">
  <div class="modal-content" id="modalContent">
    <button class="close-btn" onclick="closeModal()">Close</button>
    <div id="patientData"></div>
    <button id="save-btn" style="margin-top:15px; padding:8px 15px; border:none; border-radius:6px; background:#3498db; color:#fff; cursor:pointer;"><ion-icon name="save-outline"></ion-icon> Save Changes</button>
    <button id="download-pdf" style="margin-top:10px; padding:8px 15px; border:none; border-radius:6px; background:#1e8c72; color:#fff; cursor:pointer;"><ion-icon name="download-outline"></ion-icon> Download PDF</button>
  </div>
</div>

</section>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function openModal(id){
    fetch(`?get_patient_id=${id}`)
    .then(res => res.json())
    .then(data => {
        if(data.error){ alert(data.error); return; }

        const modal = document.getElementById('patientModal');
        const container = document.getElementById('patientData');

        // Build modal HTML with all 6 sections
        container.innerHTML = `

<div class="view-record-section"><legend>1. Mother’s Identification & Vitals</legend>
<div class="view-grid">
${['family_number','nhts_status','code_no','philhealth_no','civil_status','contact_no','clinic_name','maiden_family_name','maiden_first_name','maiden_middle_name','age','dob','address','occupation','height','bmi','muac'].map(f=>{
    let val = data[f]||'';
    let type = (f=='dob')?'date':(f=='age' || f=='height' || f=='bmi' || f=='muac')?'number':'text';
    return `<div class="field-item"><div class="field-label">${f.replace(/_/g,' ')}:</div><input id="${f}" type="${type}" value="${val}"></div>`;
}).join('')}
</div></div>

<div class="view-record-section"><legend>2. Husband’s Information</legend>
<div class="view-grid">
${['husband_family','husband_first','husband_middle','husband_occupation'].map(f=>{
    let val = data[f]||'';
    return `<div class="field-item"><div class="field-label">${f.replace(/_/g,' ')}:</div><input id="${f}" type="text" value="${val}"></div>`;
}).join('')}
</div></div>

<div class="view-record-section"><legend>3. Obstetrical History & Complications</legend>
<div class="view-grid">
${['children_alive','living_children','abortions','stillbirth_count'].map(f=>{
    let val = data[f]||'';
    return `<div class="field-item"><div class="field-label">${f.replace(/_/g,' ')}:</div><input id="${f}" type="number" value="${val}"></div>`;
}).join('')}
</div>
<div class="view-grid1">
${['complication_hemorrhage','complication_toxemia','complication_placenta_previa','complication_sepsis','complication_hypertension'].map(f=>{
    let checked = data[f]==1?'checked':'';
    return `<label><input type="checkbox" id="${f}" ${checked}> ${f.replace('complication_','').replace(/_/g,' ')}</label>`;
}).join('')}
</div></div>

<div class="view-record-section"><legend>4. Current Signs & Symptoms</legend>
<div class="view-grid1">
${['symptom_nausea','symptom_vomiting','symptom_headache','symptom_dizziness','symptom_leucorrhea','symptom_edema','symptom_cramps','symptom_bleeding','symptom_pruritis','symptom_blurring'].map(f=>{
    let checked = data[f]==1?'checked':'';
    return `<label><input type="checkbox" id="${f}" ${checked}> ${f.replace('symptom_','').replace(/_/g,' ')}</label>`;
}).join('')}
</div></div>

<div class="view-record-section"><legend>5. Dietary & Delivery Info</legend>
<div class="view-grid">
${['food_regular','food_avoided','preg_stage','prepared_bf','not_prepared_reason','delivery_date','delivery_type','delivery_place','attended_by','designation','delivery_address'].map(f=>{
    let val = data[f]||'';
    let type = (f=='delivery_date')?'date':'text';
    return `<div class="field-item"><div class="field-label">${f.replace(/_/g,' ')}:</div><input id="${f}" type="${type}" value="${val}"></div>`;
}).join('')}
</div></div>

<div class="view-record-section"><legend>6. Ante-partum (GTPAL) & Lab Data</legend>
<div class="view-grid">
${['lmp','edc','risk_code','deworming','gravida','term','para','abortion'].map(f=>{
    let val = data[f]||'';
    let type = (f=='lmp' || f=='edc')?'date':'text';
    return `<div class="field-item"><div class="field-label">${f.replace(/_/g,' ')}:</div><input id="${f}" type="${type}" value="${val}"></div>`;
}).join('')}
</div></div>

        `;

        modal.style.display = 'flex';

        // Save button
        const saveBtn = document.getElementById('save-btn');
        saveBtn.onclick = () => {
            const formData = new FormData();
            formData.append('update_patient', true);
            formData.append('id', id);

            ['family_number','nhts_status','code_no','philhealth_no','civil_status','contact_no','clinic_name','maiden_family_name','maiden_first_name','maiden_middle_name','age','dob','address','occupation','height','bmi','muac',
            'husband_family','husband_first','husband_middle','husband_occupation',
            'children_alive','living_children','abortions','stillbirth_count',
            'complication_hemorrhage','complication_toxemia','complication_placenta_previa','complication_sepsis','complication_hypertension',
            'symptom_nausea','symptom_vomiting','symptom_headache','symptom_dizziness','symptom_leucorrhea','symptom_edema','symptom_cramps','symptom_bleeding','symptom_pruritis','symptom_blurring',
            'food_regular','food_avoided','preg_stage','prepared_bf','not_prepared_reason','delivery_date','delivery_type','delivery_place','attended_by','designation','delivery_address',
            'lmp','edc','risk_code','deworming','gravida','term','para','abortion'].forEach(f=>{
                const el = document.getElementById(f);
                if(el) formData.append(f, el.type=='checkbox'? (el.checked?1:0) : el.value);
            });

            fetch('', {method:'POST', body: formData})
            .then(res=>res.text())
            .then(resp=>{
                if(resp.trim()=='success'){
                    alert('Patient updated successfully!');
                    const rows = document.querySelectorAll('#patientTable tbody tr');
                    rows.forEach(row=>{
                        if(row.cells[0].innerText==id){
                            row.cells[1].innerText = `${document.getElementById('maiden_family_name').value}, ${document.getElementById('maiden_first_name').value} ${document.getElementById('maiden_middle_name').value?document.getElementById('maiden_middle_name').value.charAt(0)+'.':''}`;
                            row.cells[2].innerText = document.getElementById('age').value;
                            row.cells[3].innerText = document.getElementById('dob').value;
                        }
                    });
                    closeModal();
                }else alert('Error updating patient.');
            });
        };

        // PDF button
        const pdfBtn = document.getElementById('download-pdf');
        pdfBtn.onclick = () => {
            const patientName = `${data.maiden_family_name}_${data.maiden_first_name}`.replace(/\s+/g,'_');
            pdfBtn.style.display='none';
            html2pdf().set({margin:0.5, filename:`${patientName}_maternal_record.pdf`, image:{type:'jpeg',quality:0.98}, html2canvas:{scale:2}, jsPDF:{unit:'in',format:'letter',orientation:'portrait'}}).from(container).save().then(()=>{pdfBtn.style.display='block';});
        };
    });
}

function closeModal(){document.getElementById('patientModal').style.display='none';}

document.getElementById('searchInput').addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#patientTable tbody tr').forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(filter)?'':'none';
    });
});
</script>
</body>
</html>
