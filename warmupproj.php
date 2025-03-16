<?php
// Database connection details (UPDATE THESE VALUES)
$servername = "nsc5531.encs.concordia.ca";
$username = "nsc55314";
$password = "RpMKHa25";
$database = "nsc55314";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MYVC Query Interface</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; }
        textarea { width: 100%; height: 100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .query-buttons button { margin: 5px; padding: 10px; }
    </style>
    <script>
        function setQuery(query) {
            document.getElementById("query").value = query;
        }
    </script>
</head>
<body>
    <h2>Run SQL Queries on MYVC Database</h2>
    
    <h3>Select a Query:</h3>
    <div class="query-buttons">
        <button onclick="setQuery(`SELECT * FROM Location ORDER BY Province, City;`)">1. List all locations</button>
        <button onclick="setQuery(`SELECT FamilyMembers.FirstName, FamilyMembers.LastName, COUNT(ClubMembers.MemberId) AS ActiveMembers 
                                   FROM FamilyMembers 
                                   JOIN ClubMembers ON FamilyMembers.FamilyMemberId = ClubMembers.FamilyMemberId 
                                   WHERE ClubMembers.Status = 'active'
                                   GROUP BY FamilyMembers.FamilyMemberId;`)">2. Active members per family</button>
        <button onclick="setQuery(`SELECT Personnel.FirstName, Personnel.LastName, Personnel.BirthDate, Personnel.SocialSecurityNumber,
                                   Personnel.MedicareCardNumber, Personnel.Phone, Personnel.Address, Personnel.City, 
                                   Personnel.Province, Personnel.PostalCode, Personnel.Email, Role.RoleName, Personnel.Mandate
                                   FROM Personnel
                                   JOIN PersonnelAssignment ON Personnel.PersonnelId = PersonnelAssignment.PersonnelId
                                   JOIN Role ON PersonnelAssignment.RoleId = Role.RoleId
                                   WHERE PersonnelAssignment.LocationId = 1 AND PersonnelAssignment.EndDate IS NULL;`)">3. Personnel at Location</button>
        <button onclick="setQuery(`SELECT ClubMembers.MemberId, ClubMembers.FirstName, ClubMembers.LastName, TIMESTAMPDIFF(YEAR, ClubMembers.BirthDate, CURDATE()) AS Age, 
                                   ClubMembers.City, ClubMembers.Province, ClubMembers.Status, Location.Name AS Location
                                   FROM ClubMembers
                                   JOIN Location ON ClubMembers.LocationId = Location.LocationId
                                   ORDER BY Location.Name, Age;`)">4. All registered club members</button>
        <button onclick="setQuery(`SELECT ClubMembers.MemberId, ClubMembers.FirstName, ClubMembers.LastName, ClubMembers.BirthDate, 
                                   ClubMembers.SSN, ClubMembers.MedicareCardNumber, ClubMembers.Phone, ClubMembers.Address, ClubMembers.City, 
                                   ClubMembers.Province, ClubMembers.PostalCode, ClubMemberFamilyRelationships.RelationshipType
                                   FROM ClubMembers
                                   JOIN ClubMemberFamilyRelationships ON ClubMembers.MemberId = ClubMemberFamilyRelationships.ClubMemberId
                                   WHERE ClubMemberFamilyRelationships.FamilyMemberId = 1;`)">5. Members associated with family</button>
        <button onclick="setQuery(`SELECT DISTINCT FamilyMembers.FirstName, FamilyMembers.LastName, FamilyMembers.Phone
                                   FROM FamilyMembers
                                   JOIN ClubMemberFamilyRelationships ON FamilyMembers.FamilyMemberId = ClubMemberFamilyRelationships.FamilyMemberId
                                   JOIN ClubMembers ON ClubMemberFamilyRelationships.ClubMemberId = ClubMembers.MemberId
                                   JOIN Personnel ON FamilyMembers.FamilyMemberId = Personnel.PersonnelId
                                   WHERE ClubMembers.Status = 'active' AND Personnel.CurrentLocationAssignment = ClubMembers.LocationId;`)">6. Family members who are also personnel</button>
        <button onclick="setQuery(`SELECT Payments.PaymentDate, Payments.Amount, MembershipFees.Year
                                   FROM Payments
                                   JOIN MembershipFees ON Payments.MembershipFeeId = MembershipFees.FeeId
                                   WHERE Payments.MemberId = 1
                                   ORDER BY Payments.PaymentDate;`)">7. Payment details of a member</button>
        <button onclick="setQuery(`SELECT SUM(CASE WHEN MembershipFees.Year = 2024 THEN MembershipFees.AmountPaid ELSE 0 END) AS TotalFeesPaid,
                                   SUM(CASE WHEN MembershipFees.Year = 2024 AND MembershipFees.ExcessAmountDonated > 0 THEN MembershipFees.ExcessAmountDonated ELSE 0 END) AS TotalDonations
                                   FROM MembershipFees;`)">8. Total fees and donations in 2024</button>
    </div>

    <br>
    <h3>Or enter your own query:</h3>
    <form method="POST">
        <textarea id="query" name="query" required></textarea><br><br>
        <input type="submit" value="Run Query">
    </form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = $_POST["query"];

    // Prevent destructive queries (optional security feature)
    $restricted_keywords = ["DROP", "DELETE", "TRUNCATE", "ALTER"];
    foreach ($restricted_keywords as $word) {
        if (stripos($sql, $word) !== false) {
            die("<p style='color:red;'>Restricted SQL command detected!</p>");
        }
    }

    $result = $conn->query($sql);
    
    if ($result) {
        if ($result->num_rows > 0) {
            echo "<h3>Query Results:</h3>";
            echo "<table><tr>";
            
            // Get column names
            while ($field = $result->fetch_field()) {
                echo "<th>" . htmlspecialchars($field->name) . "</th>";
            }
            echo "</tr>";
            
            // Fetch rows
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $col) {
                    echo "<td>" . htmlspecialchars($col) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No results found.</p>";
        }
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}

// Close connection
$conn->close();
?>
</body>
</html>
