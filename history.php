<?php
// Include your database connection
include __DIR__ . "/db.php"; 

// Fetch all products from newest to oldest
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FoodForensics - Scan History</title>

<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:"Courier New", monospace; color:#00ff66; }
body{ background:black; }

/* ===== NAVBAR ===== */
.navbar{ display:flex; justify-content:space-between; align-items:center; padding:15px 40px; border-bottom:2px solid #00ff66; }
.logo{ font-size:40px; font-weight:bold; letter-spacing:3px; }
.nav-links button{ padding:8px 15px; background:black; border:1px solid #00ff66; cursor:pointer; }
.nav-links button:hover{ background:#00ff66; color:black; }

/* ===== MAIN ===== */
.main{ height:85vh; display:flex; justify-content:center; align-items:flex-start; padding-top: 40px; overflow-y: auto;}

/* ===== CONTAINER ===== */
.container{ width:1100px; border:2px dashed #00ff66; padding:25px; margin-bottom: 40px; }

/* HEADER */
.header{ text-align:center; margin-bottom:20px; font-size:22px; }

/* SEARCH */
.search{ margin-bottom:15px; }
.search input{ padding:6px; background:black; border:1px solid #00ff66; width:220px; outline: none; }

/* TABLE */
table{ width:100%; border-collapse:collapse; margin-top:10px; font-size:14px; }
th, td{ border-bottom:1px dashed #00ff66; padding:10px 8px; text-align:left; }

/* RISK COLORS */
.low{ color:#00ff66; font-weight: bold; }
.moderate{ color:orange; font-weight: bold; }
.high{ color:red; font-weight: bold; }
.pending { color: #888; }

/* BUTTONS */
button.action-btn{ padding:5px 10px; border:1px solid #00ff66; background:black; cursor:pointer; margin-right:5px; }
button.action-btn:hover{ background:#00ff66; color:black; }
button.del-btn:hover { background: red; color: white; border-color: red;}
</style>
</head>

<body>

<div class="navbar">
    <div class="logo">FOODFORENSICS</div>
    <div class="nav-links">
        <button onclick="location.href='dashboard.php'">Dashboard</button>
    </div>
</div>

<div class="main">
    <div class="container">
        <div class="header">Scan History</div>

        <div class="search">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search product name...">
        </div>

        <table id="historyTable">
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Risk Level</th>
                <th>Risk Score</th>
                <th>Action</th>
            </tr>

            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    
                    // Parse the saved AI data if it exists
                    $aiData = !empty($row['ai_analysis']) ? json_decode($row['ai_analysis'], true) : null;
                    
                    // Extract data from the JSON or default database columns
                    $productName = $aiData['product_name'] ?? ($row['product_name'] ?? 'Unknown');
                    $barcode = $row['barcode'] ?? 'Manual Entry';
                    $riskScore = $aiData['risk_score'] ?? 'N/A';
                    $riskLevel = $aiData['risk_level'] ?? 'Pending/Failed';

                    // Determine CSS class based on Risk Score (1-10 scale)
                    $colorClass = 'pending';
                    if (is_numeric($riskScore)) {
                        if ($riskScore <= 3) $colorClass = 'low';
                        elseif ($riskScore <= 6) $colorClass = 'moderate';
                        else $colorClass = 'high';
                    }

                    echo "<tr id='row-{$row['id']}'>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>" . htmlspecialchars($productName) . "</td>";
                    echo "<td>" . htmlspecialchars($barcode) . "</td>";
                    echo "<td class='{$colorClass}'>{$riskLevel}</td>";
                    echo "<td class='{$colorClass}'>{$riskScore} / 10</td>";
                    echo "<td>
                            <button class='action-btn del-btn' onclick='deleteRecord({$row['id']})'>Delete</button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No scan history found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

<script>
// Live Search Filtering
function searchTable() {
    let input = document.getElementById("searchInput").value.toUpperCase();
    let table = document.getElementById("historyTable");
    let tr = table.getElementsByTagName("tr");
    
    for (let i = 1; i < tr.length; i++) { // Skip header row
        let td = tr[i].getElementsByTagName("td")[1]; // Target Product Name column
        if (td) {
            let txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(input) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

// Delete Record Functionality
async function deleteRecord(id) {
    if(!confirm("Are you sure you want to delete this scan from history?")) return;
    
    try {
        let res = await fetch("delete_history.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id })
        });
        
        let data = await res.json();
        if(data.success) {
            // Remove the row from the table smoothly
            document.getElementById('row-' + id).style.display = 'none';
        } else {
            alert("Failed to delete record.");
        }
    } catch (e) {
        alert("Error connecting to server.");
    }
}
</script>

</body>
</html>