<?php
// Include your database connection
include __DIR__ . "/db.php"; 

$message = "";

// 1. HANDLE FORM SUBMISSION (SAVE PROFILE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs to prevent SQL injection and map exactly to the HTML form names
    $name = $conn->real_escape_string($_POST['name']);
    $age = (int)$_POST['age'];
    $height = (int)$_POST['height_cm']; 
    $weight = (int)$_POST['weight_kg']; 
    $diseases = $conn->real_escape_string($_POST['diseases']);

    // Find the current user ID (assuming we are editing the latest profile)
    $userQ = $conn->query("SELECT id FROM user_profiles ORDER BY id DESC LIMIT 1");
    
    if ($userQ && $userQ->num_rows > 0) {
        $user = $userQ->fetch_assoc();
        $userId = $user['id'];
        
        // Update existing profile using the correct database column names (height_cm, weight_kg)
        $updateSQL = "UPDATE user_profiles SET name='$name', age='$age', height_cm='$height', weight_kg='$weight', diseases='$diseases' WHERE id=$userId";
        
        if ($conn->query($updateSQL)) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
    } else {
        // If the table is completely empty, insert a new row instead
        $insertSQL = "INSERT INTO user_profiles (name, age, height_cm, weight_kg, diseases) VALUES ('$name', $age, $height, $weight, '$diseases')";
        
        if ($conn->query($insertSQL)) {
            $message = "Profile created successfully!";
        } else {
            $message = "Error creating profile: " . $conn->error;
        }
    }
}

// 2. FETCH CURRENT DATA TO DISPLAY IN INPUTS
$userQ = $conn->query("SELECT * FROM user_profiles ORDER BY id DESC LIMIT 1");
$currentUser = $userQ->fetch_assoc();

// Fallback values if database is empty, mapping to the correct database column names
$currentName = $currentUser['name'] ?? 'User';
$currentAge = $currentUser['age'] ?? 21;
$currentHeight = $currentUser['height_cm'] ?? 175; 
$currentWeight = $currentUser['weight_kg'] ?? 70;
$currentDiseases = $currentUser['diseases'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodForensics - Settings</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Courier New", monospace;
    color:#00ff66;
}

body{
    background:black;
}

/* ===== NAVBAR ===== */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 40px;
    border-bottom:2px solid #00ff66;
}

.logo{
    font-size:40px;
    font-weight:bold;
    letter-spacing:3px;
}

.nav-links button{
    padding:8px 15px;
    background:black;
    border:1px solid #00ff66;
    cursor:pointer;
}

.nav-links button:hover{
    background:#00ff66;
    color:black;
}

/* ===== MAIN ===== */
.main{
    height:85vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* ===== CONTAINER ===== */
.container{
    width:750px;
    border:2px dashed #00ff66;
    padding:25px;
}

/* HEADER */
.header{
    text-align:center;
    margin-bottom:20px;
    font-size:22px;
}

/* INNER SPLIT LAYOUT */
.inner{
    display:flex;
}

/* LEFT OPTIONS */
.options{
    width:180px;
    border-right:1px dashed #00ff66;
    padding-right:15px;
}

.option-item{
    margin:14px 0;
    cursor:pointer;
    font-size:14px;
}

.option-item:hover{
    text-shadow:0 0 8px #00ff66;
}

/* RIGHT CONTENT */
.content{
    flex:1;
    padding-left:20px;
}

/* BOX */
.box{
    border:1px dashed #00ff66;
    padding:20px;
    font-size:14px;
}

/* INPUT */
input{
    background:black;
    border:1px solid #00ff66;
    padding:6px;
    width:220px;
    margin:6px 0;
}

input:focus {
    outline: none;
    box-shadow: 0 0 5px #00ff66;
}

/* BUTTON */
button{
    padding:6px 12px;
    border:1px solid #00ff66;
    background:black;
    cursor:pointer;
}

button:hover{
    background:#00ff66;
    color:black;
}

h3{
    margin-bottom:10px;
}

.success-msg {
    color: yellow; 
    margin-bottom: 15px;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="navbar">
    <div class="logo">FOODFORENSICS </div>
    <div class="nav-links">
        <button onclick="location.href='dashboard.php'">Dashboard</button>
    </div>
</div>

<div class="main">
    <div class="container">
        <div class="header">Settings</div>
        
        <div class="inner">
            <div class="options">
                <div class="option-item" onclick="showSection('profile')">Edit Profile</div>
                <div class="option-item" onclick="showSection('about')">About</div>
                <div class="option-item" onclick="showSection('help')">Help</div>
                <div class="option-item" onclick="logout()">Logout</div>
            </div>

            <div class="content">

                <div id="profile" class="box">
                    <h3>Edit Profile</h3>
                    
                    <?php if(!empty($message)) echo "<div class='success-msg'> > $message </div>"; ?>

                    <form method="POST" action="settings.php">
                        Name<br>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($currentName); ?>" required><br>
                        
                        Age<br>
                        <input type="number" name="age" value="<?php echo htmlspecialchars($currentAge); ?>" required><br>
                        
                        Height (cm)<br>
                        <input type="number" name="height_cm" value="<?php echo htmlspecialchars($currentHeight); ?>" required><br>
                        
                        Weight (kg)<br>
                        <input type="number" name="weight_kg" value="<?php echo htmlspecialchars($currentWeight); ?>" required><br>
                        
                        Diseases<br>
                        <input type="text" name="diseases" value="<?php echo htmlspecialchars($currentDiseases); ?>" placeholder="e.g. Diabetes, Asthma"><br><br>
                        
                        <button type="submit">Save Changes</button>
                    </form>
                </div>

                <div id="about" class="box" style="display:none;">
                    <h3>About FoodForensics</h3>
                    <p>
                    FoodForensics helps users understand nutrition and health
                    risks of packaged foods using AI-powered analysis.
                    </p>
                    <br>
                    <p>
                    It scans ingredients, nutrition labels, and additives to
                    generate risk scores and suggest healthier alternatives.
                    </p>
                </div>

                <div id="help" class="box" style="display:none;">
                    <h3>Help</h3>
                    <p>1. Go to Analyze to scan a product.</p><br>
                    <p>2. Upload label image or barcode.</p><br>
                    <p>3. AI analyzes ingredients & nutrition.</p><br>
                    <p>4. View risk score and recommendations.</p>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// If a form was submitted, ensure the profile tab stays open
function showSection(section){
    document.getElementById("profile").style.display="none";
    document.getElementById("about").style.display="none";
    document.getElementById("help").style.display="none";
    document.getElementById(section).style.display="block";
}

function logout(){
    window.location.href="login.php";
}
</script>

</body>
</html>