





<?php 
    class BD{
        public static $instancia=null;
        public static function crearInstancia(){
            if(!isset(self::$instancia)){
                $opciones[PDO::ATTR_ERRMODE]=PDO::ERRMODE_EXCEPTION;
                self::$instancia=new PDO("mysql:host=localhost;dbname=akemilab2","root","",$opciones);
                // echo "Conectado";
            }
            return self::$instancia;

        }
    }
?>