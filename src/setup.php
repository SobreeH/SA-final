<?php
// ================================
// setup_db.php
// Generates full database, tables, and dummy data
// ================================

$servername = "mariadb";
$username = "root";
$password = "root";
$database = "livestockdb";

// ----------------------
// Connect without DB first
// ----------------------
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) !== TRUE) die("Error creating database: " . $conn->error);

// Connect to the database
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// ================================
// Table creation
// ================================
$tables = [];

// Farmer
$tables['Farmer'] = "
CREATE TABLE IF NOT EXISTS Farmer (
    farmer_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    contact VARCHAR(32),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Veterinarian
$tables['Veterinarian'] = "
CREATE TABLE IF NOT EXISTS Veterinarian (
    vet_id INT AUTO_INCREMENT PRIMARY KEY,
    vet_name VARCHAR(100) NOT NULL,
    email VARCHAR(191) UNIQUE,
    password VARCHAR(255) NOT NULL,
    institution VARCHAR(150),
    contact VARCHAR(32),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Customer
$tables['Customer'] = "
CREATE TABLE IF NOT EXISTS Customer (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    contact VARCHAR(32),
    email VARCHAR(191),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Livestock (with gender)
$tables['Livestock'] = "
CREATE TABLE IF NOT EXISTS Livestock (
    livestock_id INT AUTO_INCREMENT PRIMARY KEY,
    tag_number VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('cow','chicken','goat') NOT NULL,
    breed VARCHAR(100),
    gender ENUM('male','female') DEFAULT 'female',
    weight DECIMAL(10,2) DEFAULT NULL,
    status ENUM('available','sold') NOT NULL DEFAULT 'available',
    date_added DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    image VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Supply
$tables['Supply'] = "
CREATE TABLE IF NOT EXISTS Supply (
    supply_id INT AUTO_INCREMENT PRIMARY KEY,
    supply_name VARCHAR(100) NOT NULL,
    category ENUM('feed','medicine') NOT NULL,
    description TEXT,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES Farmer(farmer_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Sales
$tables['Sales'] = "
CREATE TABLE IF NOT EXISTS Sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    livestock_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'THB',
    date_purchase DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE SET NULL,
    FOREIGN KEY (livestock_id) REFERENCES Livestock(livestock_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Health_Records
$tables['Health_Records'] = "
CREATE TABLE IF NOT EXISTS Health_Records (
    health_id INT AUTO_INCREMENT PRIMARY KEY,
    livestock_id INT NOT NULL,
    vet_id INT NULL,
    treatment_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    treatment TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES Livestock(livestock_id) ON DELETE RESTRICT,
    FOREIGN KEY (vet_id) REFERENCES Veterinarian(vet_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Breeding_Records
$tables['Breeding_Records'] = "
CREATE TABLE IF NOT EXISTS Breeding_Records (
    breeding_id INT AUTO_INCREMENT PRIMARY KEY,
    livestock_id INT NOT NULL,
    vet_id INT NULL,
    date_inseminated DATE,
    pregnancy_result ENUM('pregnant','not_pregnant','unknown') NOT NULL DEFAULT 'unknown',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES Livestock(livestock_id) ON DELETE RESTRICT,
    FOREIGN KEY (vet_id) REFERENCES Veterinarian(vet_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Execute table creation
foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$name' created successfully.<br>";
    } else {
        echo "Error creating table '$name': " . $conn->error . "<br>";
    }
}

// ================================
// Insert Dummy Data
// ================================

// Farmers
for($i=1;$i<=10;$i++){
    $conn->query("INSERT INTO Farmer (username,password,name,contact) 
        VALUES ('farmer$i','1234','Farmer $i','08123456".str_pad($i,2,'0',STR_PAD_LEFT)."')");
}

// Veterinarians
for($i=1;$i<=10;$i++){
    $conn->query("INSERT INTO Veterinarian (vet_name,email,password,institution,contact) 
        VALUES ('Dr. Vet$i','vet$i@example.com','abcd','Clinic $i','0823456".str_pad($i,2,'0',STR_PAD_LEFT)."')");
}

// Customers
for($i=1;$i<=10;$i++){
    $conn->query("INSERT INTO Customer (customer_name,contact,email) 
        VALUES ('Customer $i','0834567".str_pad($i,3,'0',STR_PAD_LEFT)."','customer$i@example.com')");
}

// Function to generate TAG number
function generateTag($type,$num){
    $prefix = ['cow'=>'M','chicken'=>'C','goat'=>'G'][$type];
    return "TAG$prefix".str_pad($num,3,'0',STR_PAD_LEFT);
}

// Livestock dummy data with gender
$livestock_data = [
    ['cow','Angus','male',500.5,'available','https://placehold.co/600x400?text=Cow'],
    ['cow','Holstein','female',610.8,'available','https://placehold.co/600x400?text=Cow'],
    ['cow','Brahman','male',520.1,'available','https://placehold.co/600x400?text=Cow'],
    ['cow','Charolais','female',705.25,'sold','https://placehold.co/600x400?text=Cow'],
    ['cow','Angus','male',580,'available','https://placehold.co/600x400?text=Cow'],
    ['chicken','Leghorn','female',2.1,'available','https://placehold.co/600x400?text=Chicken'],
    ['chicken','Rhode Island Red','male',2.45,'available','https://placehold.co/600x400?text=Chicken'],
    ['chicken','Plymouth Rock','female',2.8,'sold','https://placehold.co/600x400?text=Chicken'],
    ['goat','Boer','male',52.3,'available','https://placehold.co/600x400?text=Goat'],
    ['goat','Kiko','female',48.75,'sold','https://placehold.co/600x400?text=Goat']
];

$index_cow=1; $index_chicken=1; $index_goat=1;
foreach($livestock_data as $l){
    list($type,$breed,$gender,$weight,$status,$image) = $l;
    switch($type){
        case 'cow': $tag=generateTag($type,$index_cow++); break;
        case 'chicken': $tag=generateTag($type,$index_chicken++); break;
        case 'goat': $tag=generateTag($type,$index_goat++); break;
    }
    $stmt=$conn->prepare("INSERT IGNORE INTO Livestock (tag_number,type,breed,gender,weight,status,image) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssdsss",$tag,$type,$breed,$gender,$weight,$status,$image);
    $stmt->execute();
}

// Supplies dummy data (10 entries)
for($i=1;$i<=10;$i++){
    $conn->query("INSERT INTO Supply (supply_name,category,description,quantity,unit,updated_by) 
        VALUES ('Supply $i','feed','Description $i',100,'kg',".($i%10+1).")");
}

// Sales (POS-style)
for($i=1;$i<=10;$i++){
    $customer = ($i%2==0)?$i:"NULL"; // some walk-in sales
    $conn->query("INSERT INTO Sales (customer_id,livestock_id,price) VALUES ($customer,$i,".(1000+$i).")");
}

// Health Records
for($i=1;$i<=10;$i++){
    $conn->query("INSERT INTO Health_Records (livestock_id,vet_id,treatment_date,treatment) 
        VALUES ($i,".($i%10+1).",'2025-10-01','Routine check $i')");
}

// Breeding Records
for($i=1;$i<=10;$i++){
    $conn->query("INSERT INTO Breeding_Records (livestock_id,vet_id,date_inseminated,pregnancy_result) 
        VALUES ($i,".($i%10+1).",'2025-10-01','unknown')");
}

echo "<br>Dummy data inserted successfully!";
echo "<script>setTimeout(() => { window.location.href = 'main.php'; }, 1500);</script>";

$conn->close();
?>
