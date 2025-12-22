<?php
session_start();

/* ---------------- DATABASE CONFIG ---------------- */
$host = "DB_HOST";      // Replace with your DB host
$user = "DB_USER";      // Replace with your DB username
$pass = "DB_PASS";      // Replace with your DB password
$db   = "DB_NAME";      // Replace with your DB name

$conn = new mysqli($host,$user,$pass,$db);
if ($conn->connect_error) die("Database connection failed");

/* ---------------- SQL TABLE CREATION (RUN ONCE) ---------------- */
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255)
)");

$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

/* ---------------- CREATE ADMIN USER IF NOT EXISTS ---------------- */
$adminExists = $conn->query("SELECT * FROM users WHERE username='admin'")->num_rows;
if($adminExists == 0){
    $hash = password_hash("admin123", PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username,password) VALUES ('admin','$hash')");
}

/* ---------------- HANDLE CONTACT FORM ---------------- */
if(isset($_POST["send"])){
    $n = $conn->real_escape_string($_POST["name"]);
    $e = $conn->real_escape_string($_POST["email"]);
    $m = $conn->real_escape_string($_POST["message"]);
    $conn->query("INSERT INTO messages (name,email,message) VALUES ('$n','$e','$m')");
    $msg = "Message sent successfully!";
}

/* ---------------- HANDLE LOGIN ---------------- */
if(isset($_POST["login"])){
    $u = $conn->real_escape_string($_POST["username"]);
    $p = $_POST["password"];
    $res = $conn->query("SELECT * FROM users WHERE username='$u'");
    $row = $res->fetch_assoc();
    if($row && password_verify($p,$row["password"])){
        $_SESSION["admin"] = true;
    } else {
        $err = "Wrong login";
    }
}

/* ---------------- HANDLE LOGOUT ---------------- */
if(isset($_GET["logout"])){
    session_destroy();
    header("Location: index.php");
}

/* ---------------- HANDLE DELETE MESSAGE ---------------- */
if(isset($_GET['delete']) && isset($_SESSION["admin"])){
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM messages WHERE id=$id");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
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
<a href="#chatbot">AI Chatbot</a>
<?php if(isset($_SESSION["admin"])) echo '<a href="?logout=1">Logout</a>'; ?>
</nav>

<!-- CONTACT FORM -->
<div class="box" id="contact">
<h2>Contact Me</h2>
<?php if(isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
<form method="POST">
<input name="name" placeholder="Name" required><br>
<input name="email" type="email" placeholder="Email" required><br>
<textarea name="message" placeholder="Message" required></textarea><br>
<button name="send">Send Message</button>
</form>
</div>

<!-- ADMIN LOGIN -->
<?php if(!isset($_SESSION["admin"])) { ?>
<div class="box">
<h2>Admin Login</h2>
<?php if(isset($err)) echo "<p style='color:red;'>$err</p>"; ?>
<form method="POST">
<input name="username" placeholder="Username" required><br>
<input type="password" name="password" placeholder="Password" required><br>
<button name="login">Login</button>
</form>
</div>
<?php } ?>

<!-- ADMIN DASHBOARD -->
<?php if(isset($_SESSION["admin"])) { ?>
<div class="box">
<h2>Admin Dashboard</h2>
<table>
<tr><th>Name</th><th>Email</th><th>Message</th><th>Action</th></tr>
<?php
$res = $conn->query("SELECT * FROM messages ORDER BY id DESC");
while($m = $res->fetch_assoc()){
    echo "<tr>";
    echo "<td>{$m['name']}</td>";
    echo "<td>{$m['email']}</td>";
    echo "<td>{$m['message']}</td>";
    echo "<td><a href='?delete={$m['id']}' style='color:red;'>Delete</a></td>";
    echo "</tr>";
}
?>
</table>
</div>
<?php } ?>

<!-- AI CHATBOT DEMO -->
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
