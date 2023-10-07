<?php

class ProductGateway
{
    private PDO $conn;
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM product";
        $stmt = $this->conn->query($sql);

        $data = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $row["is_available"] = (bool) $row["is_available"]; //this line is to make the fetched is_avalable value into a boolean literal (instead 1 or 2, it will become true or false)
            $data[] = $row;
        }

        return $data;
    
    }

    public function create(array $data) : string
    {
        $sql = "INSERT INTO product (name, size, is_available)
                VALUES (:name, :size, :is_available)";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
        $stmt->bindValue(":size", $data["size"], PDO::PARAM_INT);
        $stmt->bindValue(":is_available", (bool) ($data["is_available"] ?? false), PDO::PARAM_BOOL);

        $stmt->execute();

        return $this->conn->lastInsertId();


    }

}