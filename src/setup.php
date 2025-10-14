<?php
// ================================
// setup_db.php
// Generates full database, tables, and dummy data
// ================================

$servername = "mariadb"; // Docker service name or localhost
$username = "root";
$password = "root";
$database = "livestockdb";

// ----------------------
// Connect without DB first
// ----------------------
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database '$database' created successfully.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Connect to the database
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ================================
// Table creation
// ================================
$tables = [];

// Farmer
$tables['Farmer'] = "
CREATE TABLE IF NOT EXISTS Farmer (
    farmer_id     INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    name          VARCHAR(100),
    contact       VARCHAR(32),
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Veterinarian
$tables['Veterinarian'] = "
CREATE TABLE IF NOT EXISTS Veterinarian (
    vet_id        INT AUTO_INCREMENT PRIMARY KEY,
    vet_name      VARCHAR(100) NOT NULL,
    email         VARCHAR(191) UNIQUE,
    password      VARCHAR(255) NOT NULL,
    institution   VARCHAR(150),
    contact       VARCHAR(32),
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Customer
$tables['Customer'] = "
CREATE TABLE IF NOT EXISTS Customer (
    customer_id   INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    contact       VARCHAR(32),
    email         VARCHAR(191),
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Livestock
$tables['Livestock'] = "
CREATE TABLE IF NOT EXISTS Livestock (
    livestock_id  INT AUTO_INCREMENT PRIMARY KEY,
    tag_number    VARCHAR(100) NOT NULL UNIQUE,
    type          ENUM('cow','chicken','goat') NOT NULL,
    breed         VARCHAR(100),
    weight        DECIMAL(10,2) DEFAULT NULL,
    status        ENUM('available','sold') NOT NULL DEFAULT 'available',
    date_added    DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Supply
$tables['Supply'] = "
CREATE TABLE IF NOT EXISTS Supply (
    supply_id     INT AUTO_INCREMENT PRIMARY KEY,
    supply_name   VARCHAR(100) NOT NULL,
    category      ENUM('feed','medicine') NOT NULL,
    description   TEXT,
    quantity      DECIMAL(10,2) NOT NULL DEFAULT 0,
    unit          VARCHAR(20) NOT NULL,
    reorder_level DECIMAL(10,2) NOT NULL DEFAULT 0,
    updated_by    INT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES Farmer(farmer_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Sales
$tables['Sales'] = "
CREATE TABLE IF NOT EXISTS Sales (
    sale_id        INT AUTO_INCREMENT PRIMARY KEY,
    customer_id    INT NOT NULL,
    livestock_id   INT NOT NULL,
    price          DECIMAL(10,2) NOT NULL,
    currency       CHAR(3) NOT NULL DEFAULT 'THB',
    payment_status ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
    invoice_number VARCHAR(50) NOT NULL,
    date_purchase  DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(invoice_number),
    UNIQUE(livestock_id),
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE RESTRICT,
    FOREIGN KEY (livestock_id) REFERENCES Livestock(livestock_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Health_Records
$tables['Health_Records'] = "
CREATE TABLE IF NOT EXISTS Health_Records (
    health_id      INT AUTO_INCREMENT PRIMARY KEY,
    livestock_id   INT NOT NULL,
    vet_id         INT NULL,
    treatment_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    treatment      TEXT,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (livestock_id) REFERENCES Livestock(livestock_id) ON DELETE RESTRICT,
    FOREIGN KEY (vet_id) REFERENCES Veterinarian(vet_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Breeding_Records
$tables['Breeding_Records'] = "
CREATE TABLE IF NOT EXISTS Breeding_Records (
    breeding_id        INT AUTO_INCREMENT PRIMARY KEY,
    livestock_id       INT NOT NULL,
    vet_id             INT NULL,
    date_inseminated   DATE,
    pregnancy_result   ENUM('pregnant','not_pregnant','unknown') NOT NULL DEFAULT 'unknown',
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
$conn->query("INSERT INTO Farmer (username, password, name, contact) VALUES 
    ('farmer1','1234','John Doe','0812345678'),
    ('farmer2','1234','Jane Smith','0898765432')
");

// Veterinarians
$conn->query("INSERT INTO Veterinarian (vet_name, email, password, institution, contact) VALUES
    ('Dr. Vet1','vet1@example.com','abcd','Vet Clinic A','0811111111'),
    ('Dr. Vet2','vet2@example.com','abcd','Vet Clinic B','0822222222')
");

// Customers
$conn->query("INSERT INTO Customer (customer_name, contact, email) VALUES
    ('Customer A','0833333333','customerA@example.com'),
    ('Customer B','0844444444','customerB@example.com')
");

// Livestock
$conn->query("INSERT INTO Livestock (tag_number, type, breed, weight, status) VALUES
    ('TAG001','cow','Angus',500.50,'available'),
    ('TAG002','chicken','Leghorn',2.30,'available'),
    ('TAG003','goat','Boer',45.00,'available')
");

// Supplies
$conn->query("INSERT INTO Supply (supply_name, category, description, quantity, unit, reorder_level, updated_by) VALUES
    ('Cow Feed','feed','High quality cow feed',100,'kg',20,1),
    ('Chicken Feed','feed','Layer feed',50,'kg',10,2),
    ('Deworming Medicine','medicine','General anti-parasite',20,'bottles',5,1)
");

// Sales
$conn->query("INSERT INTO Sales (customer_id, livestock_id, price, invoice_number, payment_status) VALUES
    (1,1,15000,'INV001','pending'),
    (2,2,200,'INV002','paid')
");

// Health_Records
$conn->query("INSERT INTO Health_Records (livestock_id, vet_id, treatment_date, treatment) VALUES
    (1,1,'2025-10-13','Routine check-up'),
    (3,2,'2025-10-12','Deworming')
");

// Breeding_Records
$conn->query("INSERT INTO Breeding_Records (livestock_id, vet_id, date_inseminated, pregnancy_result) VALUES
    (3,2,'2025-10-01','pregnant')
");

echo "<br>Dummy data inserted successfully!";

// Close connection
$conn->close();

?>
