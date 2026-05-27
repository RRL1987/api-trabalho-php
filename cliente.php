<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, GET, DELETE");
header("Access-Control-Allow-Headers: Content-Type");


if (!file_exists("conexao.php")) {
    http_response_code(500);
    echo json_encode(array('error' => 'Arquivo conexao.php não encontrado no servidor.'), JSON_UNESCAPED_UNICODE);
    exit();
}

require_once("conexao.php");

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(array('error' => 'Falha na conexão com o banco de dados. Verifique as configurações no conexao.php.'), JSON_UNESCAPED_UNICODE);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$id = (isset($_GET['id']) && $_GET['id'] !== '') ? intval($_GET['id']) : null;
$usuarioExistente = null;

if (($method == 'GET' && $id !== null) || $method == 'PUT' || $method == 'DELETE') {
    if ($id === null) {
        http_response_code(400);
        echo json_encode(array('error' => 'O campo ID é obrigatório para esta operação.'), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $sqlCheck = "SELECT * FROM usuarios WHERE id = $id";
    $resultCheck = mysqli_query($conn, $sqlCheck);
    
    if ($resultCheck) {
        $usuarioExistente = mysqli_fetch_assoc($resultCheck);
    }

    if (!$usuarioExistente) {
        http_response_code(404);
        echo json_encode(array('error' => 'Usuário não encontrado.'), JSON_UNESCAPED_UNICODE);
        exit();
    }
}

if ($method == 'POST'){
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(!isset($data['nome'], $data['sobrenome'], $data['email'], $data['telefone']) || 
       empty($data['nome']) || empty($data['sobrenome']) || empty($data['email']) || empty($data['telefone'])) {
        
        http_response_code(400);
        echo json_encode(array('error' => 'Todos os campos são obrigatórios.'), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $nome = mysqli_real_escape_string($conn, $data['nome']);
    $sobrenome = mysqli_real_escape_string($conn, $data['sobrenome']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $telefone = mysqli_real_escape_string($conn, $data['telefone']);

    $sql = "INSERT INTO usuarios (nome, sobrenome, email, telefone) VALUES ('$nome', '$sobrenome', '$email', '$telefone')";
    $result = mysqli_query($conn, $sql);
    
    if ($result){
        $idCliente = mysqli_insert_id($conn);
        http_response_code(201);
        echo json_encode(array('message' => 'Cliente criado com sucesso.', 'id' => $idCliente), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao criar cliente: ' . mysqli_error($conn)), JSON_UNESCAPED_UNICODE);
    }
    exit();
}

if ($method == 'GET' && $id == null){
    $sql = "SELECT * FROM usuarios";
    $result = mysqli_query($conn, $sql);

    $clientes = [];
    if ($result) {
        while($cliente = mysqli_fetch_assoc($result)){
            $clientes[] = $cliente;
        }
    }
    http_response_code(200);
    echo json_encode($clientes, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($method == 'GET' && $id != null){
    http_response_code(200);
    echo json_encode($usuarioExistente, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($method == 'PUT'){
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(!isset($data['nome'], $data['sobrenome'], $data['email'], $data['telefone']) || 
       empty($data['nome']) || empty($data['sobrenome']) || empty($data['email']) || empty($data['telefone'])) {
        
        http_response_code(400);
        echo json_encode(array('error' => 'Todos os campos são obrigatórios.'), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $nome = mysqli_real_escape_string($conn, $data['nome']);
    $sobrenome = mysqli_real_escape_string($conn, $data['sobrenome']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $telefone = mysqli_real_escape_string($conn, $data['telefone']);

    $sql = "UPDATE usuarios SET nome='$nome', sobrenome='$sobrenome', email='$email', telefone='$telefone' WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        http_response_code(200);
        echo json_encode(array('message' => 'Usuário atualizado com sucesso.'), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao atualizar usuário: ' . mysqli_error($conn)), JSON_UNESCAPED_UNICODE);
    }
    exit();
}

if ($method == 'DELETE'){
    $sql = "DELETE FROM usuarios WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        http_response_code(200);
        echo json_encode(array('message' => 'Usuário deletado com sucesso.'), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao deletar usuário: ' . mysqli_error($conn)), JSON_UNESCAPED_UNICODE);
    }
    exit();
}

http_response_code(405);
echo json_encode(array('error' => 'Método não permitido.'), JSON_UNESCAPED_UNICODE);
?>
