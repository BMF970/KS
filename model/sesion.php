<?php
class MySQLConnection
{
    private $id;
    private $PASSWORD;
    private $USERNAME;
    private $DATABASE;
    private $PORT;
    public $Connection;
    public function __construct()
    {
        $this->IP       = null;
        $this->PASSWORD = null;
        $this->USERNAME = null;
        $this->DATABASE = null;
        $this->PORT     = 3306;
    }
    public function setIP($IP) { $this->IP = $IP; }
    public function setDatabase($database) { $this->DATABASE = $database; }
    public function setUsername($username) { $this->USERNAME = $username; }
    public function setPassword($password) { $this->PASSWORD = $password; }
    public function connect()
    {
        $this->Connection = new mysqli
        (
            $this->IP,
            $this->USERNAME,
            $this->PASSWORD,
            $this->DATABASE,
            $this->PORT
        );
        // WE CHEACK THE CONNECTION STATUS
        if ($this->Connection->connect_error)
        {
            echo  $this->Connection->connect_error;
            return false;
        }
        else
        {
            return true;
        }
    }
    public function execute($SQL, $BIND_PARAMS )
    {
        if ($STMT = $this->Connection->prepare($SQL))
        {
            $tmp = array();
            if (is_array($BIND_PARAMS) && $BIND_PARAMS != null)
            {
                foreach($BIND_PARAMS as $key => $value)
                    $tmp[$key] = &$BIND_PARAMS[$key];
                call_user_func_array(array($STMT, 'bind_param'), $tmp);
            }
            if ($STMT->execute())
            {
                $ResultSet = $STMT->get_result();
                return $ResultSet;
            }
            else
            {
                echo "Error en execute()";
            }
            $STMT->close();
        }
        else
        {
            echo "Fallo al preparar la consulta.";
        }
    }
}

class Session
{
      public function setid( $id )
    {
        $this->id = $id;
    }
    public function getid()
    {
        return $this->id;
    }
    private $mysql;
    public function __construct()
    {
        session_start();
        $this->mysql = new MySQLConnection();
        $this->mysql->setIP("127.0.0.1");
        $this->mysql->setDatabase("legacy");
        $this->mysql->setUsername("root");
        $this->mysql->setPassword("");
        $this->mysql->connect();
    }
    function login($correo, $password)
    {
        $SQL = "SELECT * FROM usuarios WHERE correo LIKE ? AND contrasena LIKE ?";
        $PARAMS = array("ss", $correo, $password);

        $resultset = $this->mysql->execute($SQL, $PARAMS);

        if ($resultset->num_rows == 1) {
            $row = $resultset->fetch_assoc();
            $_SESSION['id'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['correo'] = $row['correo'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['contrasena'] = $row['contrasena'];
        } else {
            return false;
        }
        $resultset->close();
        return true;
    }

    function actualizar($nombre, $usuario, $correo, $contrasena)
    {
        $id = $_SESSION['id'];
        $codigo = "UPDATE usuarios SET nombre=?, correo=?, usuario=?, contrasena=? WHERE id=?";
        
        $stmt = $this->mysql->Connection->prepare($codigo);
        $stmt->bind_param("ssssi", $nombre, $correo, $usuario, $contrasena, $id);
        
        if ($stmt->execute()) {
            echo "Perfil actualizado correctamente.";
        } else {
            echo "Error al actualizar el perfil: " . $stmt->error;
        }
        $stmt->close();
    }

    function logout()
    {
        session_destroy();
    }

    function isLoggedIn()
    {
        return isset($_SESSION['id']);
    }
    public function getIDUser()
    {
        return $_SESSION['id'];
    }
    public function getUser()
    {
        return $_SESSION['usuario'];
    }
    
}
class SessionAdmin extends Session{

    public function __construct()
    {
        session_start();
        $this->mysql = new MySQLConnection();
        $this->mysql->setIP("127.0.0.1");
        $this->mysql->setDatabase("legacy");
        $this->mysql->setUsername("root");
        $this->mysql->setPassword("");
        $this->mysql->connect();
    }
    function loginA($usuario, $password)
    {
        $SQL = "SELECT * FROM administrador WHERE usuario = ? AND contrasena = ?";
        $PARAMS = array("ss", $usuario, $password);

        $resultset = $this->mysql->execute($SQL, $PARAMS);

        if ($resultset->num_rows == 1) {
            $row = $resultset->fetch_assoc();
            $_SESSION['id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['contrasena'] = $row['contrasena'];
        } else {
            return false;
        }
        $resultset->close();
        return true;
    }
    
    public function getUser()
    {
        return $_SESSION['usuario'];
    }
}
?>
