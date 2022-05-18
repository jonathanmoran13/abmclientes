<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists("archivo.txt")) {
    //Se Leer el archivo y se almacena el contenido json en una variable
    $strJson = file_get_contents("archivo.txt");
    //Se Convierte el json en un array aClientes
    $aClientes = json_decode($strJson, true);
} else {
    //Si el archivo no existe es porque no hay clientes
    $aClientes = array();
}

//BOTON MODIFICAR

if(isset($_GET["id"])){
    $id = $_GET["id"];
}else {
    $id = "";
}


//BOTON ELIMINAR

if(isset($_GET["do"]) && $_GET["do"] == "eliminar"){
    unset($aClientes[$id]);

    //Convertir aClientes en Json
    $strJson = json_encode($aClientes);

    //Almacenar el Json en archivo
    file_put_contents("archivo.txt", $strJson);

    header("location: index.php");
}



if ($_POST) {

    $dni = $_POST["txtDni"];
    $nombre = $_POST["txtNombre"];
    $telefono = $_POST["txtTelefono"];
    $correo = $_POST["txtCorreo"];
    $imagen = "";

    if ($_FILES["archivo"]["error"] === UPLOAD_ERR_OK) {     //Para la imagen adjunta (lo hace cuando se sube un archivo)
        $nombreAleatorio = date("Ymdhmsi") . rand(1000,2000); // Creamos un nmbre aleatorio con la fecha y la función rand
        $archivo_tmp = $_FILES["archivo"]["tmp_name"]; //el apache almacena la imagen en la carpeta temporatl del sistema
        $extension = pathinfo ($_FILES["archivo"]["name"], PATHINFO_EXTENSION);
        if ($extension == "jpg" || $extension == "png" || $extension == "jpeg"){
            $imagen = "$nombreAleatorio.$extension";  //Asignamos nombre a la imagen con la extensión
            move_uploaded_file($archivo_tmp, "imagenes/$imagen");  //Movemos la imagen de la carpeta temporal a la carpeta imagenes
        }
    }

    if ($id >= 0){

        // Si no se subio una imagen y estoy editando, conservar en $imagen el nombre 
        //de la imagen anterior que esta asociada al cliente que estamos editando

        if($_FILES["archivo"]["error"] !== UPLOAD_ERR_OK){
            $imagen = $aClientes[$id]["imagen"];
        }else{
            //Si viene una imagen y hay una imagen anterior, eliminar la anterior
            if(file_exists("imagenes/". $aClientes[$id]["imagen"])){
                unlink("imagenes/". $aClientes[$id]["imagen"]);
            }
        }
        
        
        //Si se está editando hacer esto:
        $aClientes[$id] = array(
            "dni" => $dni,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "correo" => $correo,
            "imagen" => $imagen
        );
    }else{
        //Si se esta insertando un nuevo cliente
        $aClientes[] = array(
            "dni" => $dni,
            "nombre" => $nombre,
            "telefono" => $telefono,
            "correo" => $correo,
            "imagen" => $imagen
        );
    }

    //Convertir el array de clientes en Json
    $strJson = json_encode($aClientes);

    //Almacenar en un archivo.txt el Json
    file_put_contents("archivo.txt", $strJson);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Datos Personales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="css/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/fontawesome/css/fontawesome.min.css">
</head>

<body>
    <main class="container">

        <div class="row">
            <div class="col-12 py-5 text-center">
                <h1>Registro de Clientes</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-5">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="pb-3">
                        <label for="">DNI: * </label>
                        <input type="text" name="txtDni" id="txtDni" class="form-control" required value="<?php echo isset($aClientes[$id]) ? $aClientes[$id]["dni"] : "";?>">
                    </div>
                    <div class="pb-3">
                        <label for="">Nombre: * </label>
                        <input type="text" name="txtNombre" id="txtNombre" class="form-control" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["nombre"] : "" ;?>" >
                    </div>
                    <div class="pb-3">
                        <label for="">Telefono: * </label>
                        <input type="text" name="txtTelefono" id="txtTelefono" class="form-control" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["telefono"] : "" ;?>" >
                    </div>
                    <div class="pb-3">
                        <label for="">Correo: * </label>
                        <input type="text" name="txtCorreo" id="txtCorreo" class="form-control" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["correo"] : "" ;?>" >
                    </div>
                    <div class="pb-3">
                    <label for="">Archivo adjunto</label>
                            <input type="file" name="archivo" id="archivo" accept=".jpg, .jpeg, .png">
                            <small class="d-block">Archivos admitidos: .jpg, .jpeg, .png</small>
                    </div>
                    <div class="pb-3">
                        <button type="submit" class="btn btn-primary text-white">GUARDAR</button>
                        <a href="index.php" class="btn btn-danger my-2">NUEVO</a>
                    </div>
                </form>
            </div>

            <div class="col-sm-7 text-center">
                <table class="table table-hover border">
                    <tr>
                        <th>Imagen</th>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach ($aClientes as $posicion => $cliente) : ?>
                        <tr>
                            <td><img src="imagenes/<?php echo $cliente["imagen"]; ?>" class="img-thumbnail" ></td>
                            <td><?php echo $cliente["dni"]; ?></td>
                            <td><?php echo $cliente["nombre"]; ?></td>
                            <td><?php echo $cliente["correo"]; ?></td>
                            <td>
                                <a href="?id=<?php echo $posicion;?>"><i class="fa-solid fa-pen-to-square"></i></a>
                                <a href="?id=<?php echo $posicion;?>&do=eliminar"><i class="fa-solid fa-trash-can"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </main>
</body>

</html>