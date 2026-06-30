<?php
include "db.php";
$msg = "";
$msgClass = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if(empty($email) || empty($password)){
        $msg = "PLEASE FILL ALL FIELDS";
        $msgClass = "error";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s",$email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $msg = "EMAIL ALREADY REGISTERED";
            $msgClass = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO users(email,password) VALUES(?,?)");
            $stmt->bind_param("ss",$email,$password);

            if($stmt->execute()){
                $msg = "ACCOUNT CREATED SUCCESSFULLY";
                $msgClass = "success";
            } else {
                $msg = "REGISTRATION FAILED";
                $msgClass = "error";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodForensics – Registration</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Courier New",monospace;color:#00ff66;}
body{background:black;}
.navbar{display:flex;justify-content:space-between;align-items:center;padding:15px 40px;border-bottom:2px solid #00ff66;}
.logo{font-size:40px;font-weight:bold;letter-spacing:3px;}
.main{height:85vh;display:flex;justify-content:center;align-items:center;}
.container{width:500px;border:2px dashed #00ff66;padding:35px 30px;display:flex;flex-direction:column;}
.header{text-align:center;margin-bottom:25px;font-size:20px;}
.input-group{margin-bottom:18px;}
label{display:block;margin-bottom:6px;}
input{width:100%;padding:8px;background:black;border:1px solid #00ff66;outline:none;}
input:focus{box-shadow:0 0 8px #00ff66;}
.password-wrapper{position:relative;}
.password-wrapper input{padding-right:60px;}
.toggle-eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:12px;}
.requirements{border:1px dashed #00ff66;padding:10px;margin-top:8px;font-size:12px;}
.invalid{color:red;}
.valid{color:#00ff66;}
.btn{margin-top:18px;width:100%;padding:10px;background:black;border:1px solid #00ff66;cursor:pointer;}
.btn:hover{background:#00ff66;color:black;}
.disabled{opacity:.4;cursor:not-allowed;}
.status{margin-top:10px;font-size:12px;}
.error{color:red;}
.success{color:#00ff66;}
.links{margin-top:12px;font-size:12px;}
.links a{text-decoration:none;color:#00ff66;}
.links a:hover{text-shadow:0 0 8px #00ff66;}
.left-link{text-align:left;margin-top:15px;}
.center-link{text-align:center;}
</style>
</head>

<body>

<div class="navbar">
<div class="logo">FOODFORENSICS</div>
</div>

<div class="main">
<div class="container">

<div class="header">Create Account</div>

<form method="POST">

<div class="input-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="input-group">
<label>Password</label>
<div class="password-wrapper">
<input type="password" name="password" id="password" required>
<span class="toggle-eye" onclick="togglePassword('password',this)">Show</span>
</div>

<div class="requirements">
<div id="length" class="invalid">[ ] Minimum 8 Characters</div>
<div id="upper" class="invalid">[ ] At Least One Uppercase Letter</div>
<div id="number" class="invalid">[ ] At Least One Number</div>
<div id="special" class="invalid">[ ] At Least One Special Character</div>
</div>
</div>

<div class="input-group">
<label>Confirm Password</label>
<div class="password-wrapper">
<input type="password" id="confirm" required>
<span class="toggle-eye" onclick="togglePassword('confirm',this)">Show</span>
</div>
<div id="matchMsg" class="status"></div>
</div>

<button class="btn disabled" id="registerBtn" type="submit" disabled>Register</button>

</form>

<?php if($msg!=""): ?>
<div class="status <?php echo $msgClass; ?>">
<?php echo $msg; ?>
</div>
<?php endif; ?>

<?php if($msgClass=="success"): ?>
<script>
setTimeout(function(){
    window.location.href = "login.php";
}, 2000);
</script>
<?php endif; ?>

<div class="links center-link">
Already Registered? <a href="login.php">Login</a>
</div>

<div class="links left-link">
<a href="home.html">← Back to Home</a>
</div>

</div>
</div>

<script>
const password=document.getElementById("password");
const confirm=document.getElementById("confirm");
const registerBtn=document.getElementById("registerBtn");

function togglePassword(id,el){
const field=document.getElementById(id);
if(field.type==="password"){field.type="text";el.textContent="Hide";}
else{field.type="password";el.textContent="Show";}
}

function setReq(id,ok){
const el=document.getElementById(id);
el.className=ok?"valid":"invalid";
el.innerHTML=ok?el.innerHTML.replace("[ ]","[✔]"):el.innerHTML.replace("[✔]","[ ]");
}

function validate(){
const p=password.value;
const c=confirm.value;
const length=p.length>=8;
const upper=/[A-Z]/.test(p);
const number=/[0-9]/.test(p);
const special=/[^A-Za-z0-9]/.test(p);

setReq("length",length);
setReq("upper",upper);
setReq("number",number);
setReq("special",special);

const msg=document.getElementById("matchMsg");
if(c && p!==c){msg.textContent="Passwords do not match";msg.style.color="red";}
else if(c){msg.textContent="Passwords match";msg.style.color="#00ff66";}
else{msg.textContent="";}

if(length && upper && number && special && p===c){
registerBtn.disabled=false;
registerBtn.classList.remove("disabled");
}else{
registerBtn.disabled=true;
registerBtn.classList.add("disabled");
}
}

password.addEventListener("input",validate);
confirm.addEventListener("input",validate);
</script>

</body>
</html>