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
        $stmt = $conn->prepare("SELECT password FROM users WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows == 0){
            $msg = "USER NOT REGISTERED";
            $msgClass = "error";
        } else {
            $stmt->bind_result($dbpass);
            $stmt->fetch();

            if($password === $dbpass){
                $msg = "LOGIN SUCCESSFUL";
                $msgClass = "success";
            } else {
                $msg = "INVALID PASSWORD";
                $msgClass = "error";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodForensics – Login</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:"Courier New",monospace;color:#00ff66;}
body{background:black;}

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

.main{
height:85vh;
display:flex;
justify-content:center;
align-items:center;
}

.container{
width:500px;
border:2px dashed #00ff66;
padding:50px 40px;
display:flex;
flex-direction:column;
}

.header{
text-align:center;
margin-bottom:35px;
font-size:22px;
}

.input-group{
margin-bottom:25px;
}

label{
display:block;
margin-bottom:8px;
}

input{
width:100%;
padding:10px;
background:black;
border:1px solid #00ff66;
outline:none;
}

input:focus{
box-shadow:0 0 10px #00ff66;
}

.password-wrapper{
position:relative;
}

.password-wrapper input{
padding-right:60px;
}

.toggle-eye{
position:absolute;
right:10px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
font-size:12px;
}

.btn{
margin-top:20px;
width:100%;
padding:12px;
background:black;
border:1px solid #00ff66;
cursor:pointer;
}

.btn:hover{
background:#00ff66;
color:black;
}

.status{
margin-top:12px;
font-size:13px;
text-align:center;
}

.error{color:red;}
.success{color:#00ff66;}

.extra-links{
text-align:center;
margin-top:15px;
font-size:13px;
}

.extra-links a{
text-decoration:none;
color:#00ff66;
}

.links{
margin-top:20px;
text-align:left;
font-size:13px;
}

.links a{
text-decoration:none;
color:#00ff66;
}
</style>
</head>

<body>

<div class="navbar">
<div class="logo">FOODFORENSICS</div>
</div>

<div class="main">
<div class="container">

<div class="header">Login</div>

<form method="POST">

<div class="input-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="input-group">
<label>Password</label>
<div class="password-wrapper">
<input type="password" name="password" id="password" required>
<span class="toggle-eye" onclick="togglePassword()">Show</span>
</div>
</div>

<button class="btn" type="submit">Login</button>

</form>

<?php if($msg!=""): ?>
<div class="status <?php echo $msgClass; ?>">
<?php echo $msg; ?>
</div>
<?php endif; ?>

<?php if($msgClass=="success"): ?>
<script>
setTimeout(function(){
    window.location.href="profile.php";
},2500);
</script>
<?php endif; ?>

<div class="extra-links">
Don't have an account? <a href="register.php">Register</a>
</div>

<div class="links">
<a href="home.html">← Back to Home</a>
</div>

</div>
</div>

<script>
function togglePassword(){
const field=document.getElementById("password");
const eye=document.querySelector(".toggle-eye");
if(field.type==="password"){field.type="text";eye.textContent="Hide";}
else{field.type="password";eye.textContent="Show";}
}
</script>

</body>
</html>