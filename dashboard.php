<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodForensics - Personal Dashboard</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:"Courier New", monospace;color:#00ff66;}
        body{background:black;height:100vh;display:flex;flex-direction:column;}
        .topbar{display:flex;justify-content:space-between;align-items:center;padding:15px 40px;border-bottom:2px solid #00ff66;}
        .logo{font-size:40px;font-weight:bold;letter-spacing:3px;}
        .top-links button{padding:8px 15px;background:black;border:1px solid #00ff66;cursor:pointer;}
        .top-links button:hover{background:#00ff66;color:black;}
        .container{flex:1;display:flex;overflow:hidden;}
        .sidebar{width:200px;border-right:2px solid #00ff66;padding:15px;}
        .menu-item{margin:12px 0;cursor:pointer;font-size:14px;}
        .menu-item:hover{text-shadow:0 0 8px #00ff66;}
        .main{flex:1;padding:15px;overflow:auto;}
        .box{border:1px dashed #00ff66;padding:10px;margin-bottom:15px;font-size:13px;}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:15px;}
        .gauge-container{display:flex;flex-direction:column;align-items:center;}
        .gauge{width:100px;height:100px;}
        .score-text{font-size:16px;margin-top:5px;}
        .preview{width:100%;max-height:120px;object-fit:cover;border:1px solid #00ff66;}
        .bar{height:8px;background:#002200;margin:4px 0;}
        .fill{height:100%;background:#00ff66;transition: width 0.5s ease-in-out;}
        table{width:100%;border-collapse:collapse;font-size:12px;}
        th,td{border-bottom:1px dashed #00ff66;padding:4px;text-align: left;}
        
        .chatbot {
            position:fixed;
            bottom:15px;
            right:15px;
            width:300px;
            border:1px solid #00ff66;
            background:black; 
            z-index: 100;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .chat-header{padding:6px;border-bottom:1px solid #00ff66;font-size:12px; font-weight: bold;}
        .chat-controls {
            float: right;
            cursor: pointer;
            letter-spacing: 8px;
            user-select: none;
        }
        .chat-controls span:hover {
            color: white;
        }
        .chat-body{height:180px;overflow:auto;padding:6px;font-size:11px;}
        .chat-input{display:flex;}
        .chat-input input{flex:1;background:black;border:none;border-top:1px solid #00ff66;padding:6px;font-size:11px;outline:none;}
        .chat-input button{background:black;border-left:1px solid #00ff66;border-top:1px solid #00ff66;cursor:pointer;font-size:11px;padding: 0 10px;}
        
        .chatbot.minimized .chat-body, 
        .chatbot.minimized .chat-input {
            display: none;
        }
        .chatbot.maximized {
            width: 60vw;
            height: 70vh;
            bottom: 15vh;
            right: 20vw;
            box-shadow: 0 0 20px #00ff66;
        }
        .chatbot.maximized .chat-body {
            flex: 1; 
            height: auto;
            font-size: 14px; 
        }

        /* FIX: Mobile Responsiveness for maximized chatbot */
        @media (max-width: 768px) {
            .chatbot.maximized {
                width: 90vw;
                right: 5vw;
                bottom: 5vh;
            }
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="logo">FOODFORENSICS</div>
    <div class="top-links">
        <button onclick="location.href='profile.php'">Profile</button>
        <button onclick="location.href='home.html'">Home</button>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <div class="menu-item" onclick="location.href='dashboard.php'">> Personal Dashboard</div>
        <div class="menu-item" onclick="location.href='analyze.html'">> Analyze</div>
        <div class="menu-item" onclick="location.href='history.php'">> History</div>
        <div class="menu-item" onclick="location.href='settings.php'">> Settings</div>
    </div>

    <div class="main">
        <div class="grid">
            <div class="box">
                <h4 style="text-align:center;">Risk Score</h4>
                <div class="gauge-container">
                    <svg class="gauge" viewBox="0 0 36 36">
                        <path stroke="#003300" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path id="gaugeFill" stroke="#00ff66" stroke-width="3" fill="none" stroke-dasharray="0,100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <div class="score-text" id="riskScore">0 / 10</div>
                </div>
            </div>
            <div class="box">
                <h4>Product Scan Preview</h4>
                <img id="productImg" src="https://dummyimage.com/400x200/000/00ff66&text=Loading..." class="preview">
            </div>
        </div>

        <div class="box">
            <h4>Nutrition Breakdown (%)</h4>
            Calories <div class="bar"><div id="calBar" class="fill" style="width:0%"></div></div>
            Sugar <div class="bar"><div id="sugarBar" class="fill" style="width:0%"></div></div>
            Sodium <div class="bar"><div id="sodiumBar" class="fill" style="width:0%"></div></div>
            Fat <div class="bar"><div id="fatBar" class="fill" style="width:0%"></div></div>
        </div>

        <div class="box">
            <h4>Ingredient Risk Analysis</h4>
            <table id="ingredientTable">
                <thead><tr><th>Ingredient</th><th>Category</th><th>Risk</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="box">
            <h4>AI Health Explanation</h4>
            <p id="aiText">Analyzing ingredients...</p>
        </div>
    </div>
</div>

<div class="chatbot" id="chatbotUI">
    <div class="chat-header">
        AI Assistant
        <span class="chat-controls">
            <span onclick="toggleMinimize()" title="Minimize">_</span>
            <span onclick="toggleMaximize()" title="Maximize">□</span>
        </span>
    </div>
    <div class="chat-body" id="chatBody">> System initialized. Context loaded. Awaiting queries.</div>
    <div class="chat-input">
        <input id="chatInput" placeholder="Ask about the product, your health, or anything else..." onkeypress="if(event.key === 'Enter') sendChat()">
        <button onclick="sendChat()">Send</button>
    </div>
</div>

<script>
let isMinimized = false;
let isMaximized = false;

function toggleMinimize() {
    const bot = document.getElementById('chatbotUI');
    isMinimized = !isMinimized;
    
    if (isMinimized) {
        bot.classList.add('minimized');
        bot.classList.remove('maximized');
        isMaximized = false; 
    } else {
        bot.classList.remove('minimized');
    }
}

function toggleMaximize() {
    const bot = document.getElementById('chatbotUI');
    isMaximized = !isMaximized;
    
    if (isMaximized) {
        bot.classList.add('maximized');
        bot.classList.remove('minimized');
        isMinimized = false; 
        
        let chat = document.getElementById("chatBody");
        chat.scrollTop = chat.scrollHeight; 
    } else {
        bot.classList.remove('maximized');
    }
}

async function loadDashboard() {
    try {
        const res = await fetch("personal_dashboard_data.php");
        const d = await res.json();

        // FIX: Display High Demand error distinctly without breaking the UI structure
        if (d.error) {
            document.getElementById("aiText").innerHTML = `<span style="color:red; border: 1px solid red; padding: 5px; display: inline-block;">⚠️ ${d.error}</span>`;
            document.getElementById("productImg").src = "https://dummyimage.com/400x200/000/ff0000&text=Error";
            return;
        }

        let score = Math.min((d.risk_score || 0), 10) * 10;
        document.getElementById("riskScore").innerText = d.risk_score + " / 10";
        document.getElementById("gaugeFill").setAttribute("stroke-dasharray", score + ",100");
        
        if (d.image) document.getElementById("productImg").src = d.image;

        document.getElementById("calBar").style.width = (d.nutrition.calories || 0) + "%";
        document.getElementById("sugarBar").style.width = (d.nutrition.sugar || 0) + "%";
        document.getElementById("sodiumBar").style.width = (d.nutrition.sodium || 0) + "%";
        document.getElementById("fatBar").style.width = (d.nutrition.fat || 0) + "%";

        let tbody = document.querySelector("#ingredientTable tbody");
        tbody.innerHTML = "";
        if (d.ingredients_list && d.ingredients_list.length > 0) {
            d.ingredients_list.forEach(i => {
                let row = tbody.insertRow();
                row.insertCell(0).innerText = i.name || 'Unknown';
                row.insertCell(1).innerText = i.category || 'Unknown';
                row.insertCell(2).innerText = i.risk || 'Unknown';
            });
        } else {
            let row = tbody.insertRow();
            row.insertCell(0).innerText = "No data extracted";
            row.insertCell(1).innerText = "-";
            row.insertCell(2).innerText = "-";
        }

        let warningList = d.warnings && d.warnings.length > 0 ? d.warnings.map(w => `• ${w}`).join("<br>") : "None detected.";
        document.getElementById("aiText").innerHTML = `
            <b>Risk Level:</b> ${d.risk_level}<br><br>
            ${d.explanation}<br><br>
            <b style="color:yellow;">Warnings:</b><br>${warningList}
        `;

    } catch (e) {
        document.getElementById("aiText").innerHTML = `<span style="color:red;">Failed to connect to the dashboard server.</span>`;
    }
}

async function sendChat() {
    let input = document.getElementById("chatInput");
    let chat = document.getElementById("chatBody");
    let msg = input.value.trim();
    if (!msg) return;

    chat.innerHTML += `<br><br><span style="color:#fff;">USER></span> ${msg}`;
    input.value = "";
    chat.scrollTop = chat.scrollHeight;

    let loadingId = "load-" + Date.now();
    chat.innerHTML += `<br><span id="${loadingId}">AI> Processing request...</span>`;
    chat.scrollTop = chat.scrollHeight;

    try {
        let res = await fetch("chat_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message: msg })
        });
        let data = await res.json();
        
        document.getElementById(loadingId).innerHTML = `AI> ${data.reply}`;
    } catch (e) {
        document.getElementById(loadingId).innerHTML = `<span style="color:red;">AI> ERR: Connection to neural network lost.</span>`;
    }
    chat.scrollTop = chat.scrollHeight;
}

window.onload = loadDashboard;
</script>
</body>
</html>