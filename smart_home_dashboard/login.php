<?php
session_start();

if(isset($_POST['start'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เริ่มต้นใช้งาน</title>
<style>
body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #2980b9, #6dd5fa);
}

.container {
    background: #ffffffee;
    padding: 60px 50px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    text-align: center;
    max-width: 400px;
    width: 90%;
    animation: fadeIn 1s ease;
}

h1 {
    font-size: 2em;
    color: #2c3e50;
    margin-bottom: 30px;
}

button {
    padding: 18px 40px;
    font-size: 18px;
    font-weight: bold;
    color: white;
    background: linear-gradient(135deg, #2980b9, #6dd5fa);
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

button:hover {
    background: linear-gradient(135deg, #1f5f85, #4fc3f7);
    transform: scale(1.05);
    box-shadow: 0 12px 25px rgba(0,0,0,0.3);
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-20px);}
    to {opacity: 1; transform: translateY(0);}
}
</style>
</head>
<body>
<div class="container">
    <h1>เริ่มต้นใช้งาน</h1>
    <form method="post">
        <button type="submit" name="start">เริ่มต้นใช้งาน</button>
    </form>
</div>
</body> 
</html>
