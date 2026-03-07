<?php
session_start();

/* ---------------- DATABASE CONFIG ---------------- */
$host = "localhost";      // Replace with your DB host
$user = "root";           // Replace with your DB username
$pass = "";               // Replace with your DB password
$db   = "belay_portfolio";// Replace with your DB name

$conn = new mysqli($host,$user,$pass,$db);
if ($conn->connect_error) die("Database connection failed: ".$conn->connect_error);

/* ---------------- SQL TABLE CREATION (RUN ONCE) ---------------- */
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    file_name VARCHAR(255),
    file_path VARCHAR(255),
    file_type ENUM('cv','pdf','video') DEFAULT 'pdf',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS chat_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    question TEXT,
    answer TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

/* ---------------- CREATE DEFAULT ADMIN ---------------- */
$adminExists = $conn->query("SELECT * FROM users WHERE username='admin'")->num_rows;
if($adminExists==0){
    $hash = password_hash("admin123", PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username,password,role) VALUES ('admin','$hash','admin')");
}

/* ---------------- HANDLE CONTACT FORM ---------------- */
$msg = "";
if(isset($_POST["send"])){
    $n = $conn->real_escape_string($_POST["name"]);
    $e = $conn->real_escape_string($_POST["email"]);
    $m = $conn->real_escape_string($_POST["message"]);
    $conn->query("INSERT INTO messages (name,email,message) VALUES ('$n','$e','$m')");
    $msg = "Message sent successfully!";
}

/* ---------------- HANDLE FILE UPLOAD ---------------- */
if(isset($_FILES['file'])){
    $user_id = $_SESSION['user_id'] ?? 0;
    $file_name = $_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Determine type
    if(in_array($ext,['pdf'])) $file_type = 'pdf';
    elseif(in_array($ext,['mp4','mov'])) $file_type = 'video';
    else $file_type = 'cv';

    $target_dir = "uploads/$file_type/";
    if(!is_dir($target_dir)) mkdir($target_dir,0777,true);
    $target_file = $target_dir . basename($file_name);

    if(move_uploaded_file($tmp_name,$target_file)){
        $conn->query("INSERT INTO uploads (user_id,file_name,file_path,file_type) VALUES ('$user_id','$file_name','$target_file','$file_type')");
        $msg = "$file_name uploaded successfully!";
    }
}

/* ---------------- HANDLE LOGIN ---------------- */
$err = "";
if(isset($_POST["login"])){
    $u = $conn->real_escape_string($_POST["username"]);
    $p = $_POST["password"];
    $res = $conn->query("SELECT * FROM users WHERE username='$u'");
    $row = $res->fetch_assoc();
    if($row && password_verify($p,$row["password"])){
        $_SESSION["user_id"] = $row['id'];
        $_SESSION["username"] = $row['username'];
        $_SESSION["role"] = $row['role'];
    } else {
        $err = "Wrong login credentials!";
    }
}

/* ---------------- HANDLE LOGOUT ---------------- */
if(isset($_GET["logout"])){
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ---------------- HANDLE DELETE ---------------- */
if(isset($_GET['delete']) && isset($_SESSION["role"]) && $_SESSION["role"]=='admin'){
    $id = intval($_GET['delete']);
    $type = $_GET['type'] ?? 'message';
    if($type=='message'){
        $conn->query("DELETE FROM messages WHERE id=$id");
    } elseif($type=='upload'){
        $file = $conn->query("SELECT file_path FROM uploads WHERE id=$id")->fetch_assoc()['file_path'];
        if(file_exists($file)) unlink($file);
        $conn->query("DELETE FROM uploads WHERE id=$id");
    }
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Belay Kassanew Portfolio</title>
<style>
body{font-family:Arial;background:#f3f7f3;margin:0;padding:0;}
header{background:linear-gradient(135deg,#2E8B57,#3CB371);color:white;text-align:center;padding:50px 20px;}
nav{background:#2E8B57;text-align:center;padding:10px;}
nav a{color:white;text-decoration:none;padding:10px 15px;display:inline-block;}
nav a:hover{background:#3CB371;border-radius:5px;}
.box{background:#fff;padding:20px;margin:20px auto;border-radius:10px;width:80%;}
button{background:#2E8B57;color:#fff;padding:10px 15px;border:none;cursor:pointer;border-radius:5px;}
input,textarea{width:100%;padding:8px;margin:5px 0;border-radius:5px;border:1px solid #ccc;}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;text-align:left;border:1px solid #ccc;}
th{background:#2E8B57;color:white;}
</style>
</head>
<body>

<header>
<h1>Belay Kassanew</h1>
<p>Computer Science & Public Health Student | Educator | Researcher</p>
</header>

<nav>
<a href="#contact">Contact</a>
<a href="#upload">Upload</a>
<a href="#chatbot">AI Chatbot</a>
<?php if(isset($_SESSION["role"])) echo '<a href="?logout=1">Logout</a>'; ?>
</nav>

<!-- CONTACT FORM -->
<div class="box" id="contact">
<h2>Contact Me</h2>
<?php if($msg) echo "<p style='color:green;'>$msg</p>"; ?>
<form method="POST">
<input name="name" placeholder="Name" required><br>
<input name="email" type="email" placeholder="Email" required><br>
<textarea name="message" placeholder="Message" required></textarea><br>
<button name="send">Send Message</button>
</form>
</div>

<!-- FILE UPLOAD -->
<?php if(isset($_SESSION['user_id'])) { ?>
<div class="box" id="upload">
<h2>Upload CV / PDF / Video</h2>
<form method="POST" enctype="multipart/form-data">
<input type="file" name="file" required><br>
<button type="submit">Upload</button>
</form>
</div>
<?php } else { ?>
<div class="box">
<h2>Login to Upload</h2>
<form method="POST">
<input name="username" placeholder="Username" required><br>
<input type="password" name="password" placeholder="Password" required><br>
<button name="login">Login</button>
</form>
<?php if($err) echo "<p style='color:red;'>$err</p>"; ?>
</div>
<?php } ?>

<!-- ADMIN DASHBOARD -->
<?php if(isset($_SESSION["role"]) && $_SESSION["role"]=='admin') { 
$messages = $conn->query("SELECT * FROM messages ORDER BY id DESC");
$uploads = $conn->query("SELECT * FROM uploads ORDER BY uploaded_at DESC");
?>
<div class="box">
<h2>Admin Dashboard</h2>

<h3>Messages</h3>
<table>
<tr><th>Name</th><th>Email</th><th>Message</th><th>Action</th></tr>
<?php while($m=$messages->fetch_assoc()){ ?>
<tr>
<td><?=$m['name']?></td>
<td><?=$m['email']?></td>
<td><?=$m['message']?></td>
<td><a href="?delete=<?=$m['id']?>&type=message" style="color:red;">Delete</a></td>
</tr>
<?php } ?>
</table>

<h3>Uploads</h3>
<table>
<tr><th>User ID</th><th>File Name</th><th>Type</th><th>Action</th></tr>
<?php while($u=$uploads->fetch_assoc()){ ?>
<tr>
<td><?=$u['user_id']?></td>
<td><?=$u['file_name']?></td>
<td><?=$u['file_type']?></td>
<td><a href="?delete=<?=$u['id']?>&type=upload" style="color:red;">Delete</a></td>
</tr>
<?php } ?>
</table>
</div>
<?php } ?>

<!-- AI CHATBOT -->
<div class="box" id="chatbot">
<h2>BelayBot AI</h2>
<input id="q" placeholder="Ask something">
<button onclick="ask()">Ask</button>
<p id="a"></p>
</div>

<script>
function ask(){
    let q = document.getElementById("q").value.toLowerCase();
    let a = "";
    if(q.includes("hi") || q.includes("hello")) a = "Hello! I am BelayBot, your assistant.";
    else if(q.includes("project")) a = "You can add IoT, AI, or web projects here!";
    else a = "BelayBot: I am still learning. Ask about your projects!";
    document.getElementById("a").innerText = a;
}
</script>

</body>
</html>
