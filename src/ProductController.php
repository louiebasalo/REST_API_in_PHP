<?php

class ProductController {

    public function __construct(private ProductGateway $gateway)
    {

    }
    public function processRequest(string $method, ?string $id) : void
    {
        if($id){
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id) : void 
    {
        $product = $this->gateway->get($id);

        if (!$product){
            http_response_code(404);
            echo json_encode(["message" => "Product not found."]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($product);
                break;
            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data);
                if (!empty($errors)){
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $rows = $this->gateway->update($product, $data);

                http_response_code(201);

                echo json_encode([
                    "message" => "Product $id updated",
                    "rows" => $rows
                ]);

                break;

            case "DELETE":
                $row = $this->gateway->delete($id);
                http_response_code(204);
                echo json_encode([
                    "message" => "Product {$id} is deleted.",
                    "rows" => $row
                ]);

                break;
            default:
                http_response_code(405);
                header("Allow: GET, POST, PATCH, DELETE");

        }
    }

    private function processCollectionRequest(string $method) : void 
    {
        switch($method){
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $errors = $this->getValidationErrors($data);
                if (!empty($errors)){
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data);

                http_response_code(201);

                echo json_encode([
                    "message" => "Product Created",
                    "id" => $id
                ]);

                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
                
        }
    }

    private function getValidationErrors(array $data) : array
    {
        $errors = [];

        if (empty($data["name"])) $errors[] = "name is required";
        
        if (array_key_exists("size", $data)){
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === FALSE){
                $errors[] = "size must be an integer"; 
            }
        }

        return $errors;
    }

}