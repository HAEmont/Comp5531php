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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .header { background-color: #007BFF; color: white; padding: 15px; text-align: center; border-radius: 10px 10px 0 0; }
        textarea { width: 100%; height: 100px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; padding: 10px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        .query-buttons { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .query-buttons button { flex: 1; padding: 10px; border: none; background-color: #28a745; color: white; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        .query-buttons button:hover { background-color: #218838; }
        .run-query-btn { width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        .run-query-btn:hover { background-color: #0056b3; }
    </style>
    <script>
        function setQuery(query) {
            document.getElementById("query").value = query;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>MYVC Query Interface</h2>
        </div>

        <h4 class="mt-3">Select a Query:</h4>
        <div class="query-buttons">
            <button onclick="setQuery(`SELECT * FROM Location ORDER BY Province, City;`)">List all locations</button>
            
            <button onclick="setQuery(`SELECT FamilyMembers.FirstName, FamilyMembers.LastName, COUNT(ClubMemberFamilyRelationships.ClubMemberId) AS ActiveMembers 
                                       FROM FamilyMembers 
                                       JOIN ClubMemberFamilyRelationships ON FamilyMembers.FamilyMemberId = ClubMemberFamilyRelationships.FamilyMemberId
                                       JOIN ClubMembers ON ClubMemberFamilyRelationships.ClubMemberId = ClubMembers.MemberId 
                                       WHERE ClubMembers.Status = 'active'
                                       GROUP BY FamilyMembers.FamilyMemberId;`)">Active members per family</button>
            
            <button onclick="setQuery(`SELECT Personnel.FirstName, Personnel.LastName, Role.RoleName, Personnel.Mandate
                                       FROM Personnel
                                       JOIN PersonnelAssignment ON Personnel.PersonnelId = PersonnelAssignment.PersonnelId
                                       JOIN Role ON PersonnelAssignment.RoleId = Role.RoleId
                                       WHERE PersonnelAssignment.LocationId = 1 AND PersonnelAssignment.EndDate IS NULL;`)">Personnel at Location</button>
            
            <button onclick="setQuery(`SELECT ClubMembers.MemberId, ClubMembers.FirstName, ClubMembers.LastName, TIMESTAMPDIFF(YEAR, ClubMembers.BirthDate, CURDATE()) AS Age, 
                                       ClubMembers.City, ClubMembers.Province, ClubMembers.Status, Location.Name AS Location
                                       FROM ClubMembers
                                       JOIN Location ON ClubMembers.LocationId = Location.LocationId
                                       ORDER BY Location.Name, Age;`)">All registered club members</button>
            
            <button onclick="setQuery(`SELECT ClubMembers.MemberId, ClubMembers.FirstName, ClubMembers.LastName, ClubMembers.BirthDate, ClubMembers.SSN, ClubMembers.MedicareCardNumber, 
                                       ClubMembers.Phone, ClubMembers.Address, ClubMembers.City, ClubMembers.Province, ClubMembers.PostalCode, ClubMemberFamilyRelationships.RelationshipType
                                       FROM ClubMembers
                                       JOIN ClubMemberFamilyRelationships ON ClubMembers.MemberId = ClubMemberFamilyRelationships.ClubMemberId
                                       WHERE ClubMemberFamilyRelationships.FamilyMemberId = 1;`)">Members associated with family</button>

            <button onclick="setQuery(`SELECT DISTINCT FamilyMembers.FirstName, FamilyMembers.LastName, FamilyMembers.Phone
                                       FROM FamilyMembers
                                       JOIN ClubMemberFamilyRelationships ON FamilyMembers.FamilyMemberId = ClubMemberFamilyRelationships.FamilyMemberId
                                       JOIN ClubMembers ON ClubMemberFamilyRelationships.ClubMemberId = ClubMembers.MemberId
                                       JOIN Personnel ON FamilyMembers.FamilyMemberId = Personnel.PersonnelId
                                       WHERE ClubMembers.Status = 'active' AND Personnel.CurrentLocationAssignment = ClubMembers.LocationId;`)">Family members who are also personnel</button>

            <button onclick="setQuery(`SELECT Payments.PaymentDate, Payments.Amount, MembershipFees.Year
                                       FROM Payments
                                       JOIN MembershipFees ON Payments.MembershipFeeId = MembershipFees.FeeId
                                       WHERE MembershipFees.MemberId = 1
                                       ORDER BY Payments.PaymentDate;`)">Payment details of a member</button>

            <button onclick="setQuery(`SELECT SUM(MembershipFees.AmountPaid) AS TotalFeesPaid,
                                       SUM(MembershipFees.ExcessAmountDonated) AS TotalDonations
                                       FROM MembershipFees WHERE MembershipFees.Year = 2024;`)">Total fees and donations in 2024</button>
            
            <button onclick="setQuery(`SELECT 'Location' AS TableName, COUNT(*) AS RowCount FROM Location
UNION
SELECT 'Personnel', COUNT(*) FROM Personnel
UNION
SELECT 'PersonnelAssignment', COUNT(*) FROM PersonnelAssignment
UNION
SELECT 'Role', COUNT(*) FROM Role
UNION
SELECT 'FamilyMembers', COUNT(*) FROM FamilyMembers
UNION
SELECT 'ClubMembers', COUNT(*) FROM ClubMembers
UNION
SELECT 'ClubMemberFamilyRelationships', COUNT(*) FROM ClubMemberFamilyRelationships
UNION
SELECT 'Teams', COUNT(*) FROM Teams
UNION
SELECT 'MembershipFees', COUNT(*) FROM MembershipFees
UNION
SELECT 'Payments', COUNT(*) FROM Payments;`)">Count Rows in All Tables</button>
        </div>

        <h4>Or enter your own query:</h4>
        <form method="POST">
            <textarea id="query" name="query" required></textarea><br><br>
            <input type="submit" class="run-query-btn" value="Run Query">
        </form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = $_POST["query"];

    $result = $conn->query($sql);
    
    if ($result) {
        if ($result->num_rows > 0) {
            echo "<h3 class='mt-4'>Query Results:</h3>";
            echo "<table class='table table-bordered table-striped'><tr>";
            
            while ($field = $result->fetch_field()) {
                echo "<th>" . htmlspecialchars($field->name) . "</th>";
            }
            echo "</tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $col) {
                    echo "<td>" . htmlspecialchars($col) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='text-muted'>No results found.</p>";
        }
    } else {
        echo "<p class='text-danger'>Error: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
    </div>
</body>
</html>
