<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodForensics - Profile</title>

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

/* ===== PROFILE FORM ===== */

.container{
    display:flex;
    justify-content:center;
    align-items:center;
    height:85vh;
}

.profile-box{
    width:450px;
    border:1px dashed #00ff66;
    padding:30px;
}

.profile-box h2{
    text-align:center;
    margin-bottom:20px;
}

/* INPUT */

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:5px;
}

input, textarea{
    width:100%;
    padding:8px;
    background:black;
    border:1px solid #00ff66;
    outline:none;
}

/* ✨ GLOW EFFECT */
input:focus, textarea:focus{
    box-shadow: 0 0 12px #00ff66;
    border: 1px solid #00ff66;
}

textarea{
    resize:none;
    height:80px;
}

/* BUTTON */

button{
    width:100%;
    padding:10px;
    border:1px solid #00ff66;
    background:black;
    cursor:pointer;
}

button:hover{
    background:#00ff66;
    color:black;
}

.message{
    margin-top:15px;
    text-align:center;
}

</style>
</head>

<body>

<!-- NAVBAR -->

<div class="navbar">
    <div class="logo">FOODFORENSICS </div>

    <div class="nav-links">
        <button onclick="location.href='dashboard.php'">Dashboard</button>
    </div>
</div>

<!-- PROFILE FORM -->

<div class="container">

    <div class="profile-box">

        <h2>User Health Profile</h2>

        <div class="form-group">
            <label>Name</label>
            <input type="text" id="name">
        </div>

        <div class="form-group">
            <label>Age</label>
            <input type="number" id="age">
        </div>

        <div class="form-group">
            <label>Height (cm)</label>
            <input type="number" id="height">
        </div>

        <div class="form-group">
            <label>Weight (kg)</label>
            <input type="number" id="weight">
        </div>

        <div class="form-group">
            <label>Diseases / Medical Conditions</label>
            <textarea id="disease" placeholder="Diabetes, BP, Allergy etc..."></textarea>
        </div>

        <button onclick="submitProfile()">Submit Profile</button>

        <div class="message" id="msg"></div>

    </div>

</div>

<script>
async function submitProfile(){

    const name = document.getElementById("name").value.trim();
    const age = document.getElementById("age").value.trim();
    const height = document.getElementById("height").value.trim();
    const weight = document.getElementById("weight").value.trim();
    const disease = document.getElementById("disease").value.trim();

    if(name=="" || age=="" || height=="" || weight==""){
        msg.innerText="Please fill all required fields.";
        return;
    }

    msg.innerText="Saving profile...";

    const fd = new FormData();
    fd.append("name", name);
    fd.append("age", age);
    fd.append("height", height);
    fd.append("weight", weight);
    fd.append("disease", disease);

    try{
        const res = await fetch("save_profile.php",{
            method:"POST",
            body:fd
        });

        const text = await res.text();

        if(text.trim()==="success")
            msg.innerText="Profile saved successfully ✔";
        else
            msg.innerText="Save failed ❌";

    }catch{
        msg.innerText="Server error ❌";
    }
}
</script>

</body>
</html>