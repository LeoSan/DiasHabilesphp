<?php
/**
 * Created by PhpStorm.
 * User: admin.sau
 * Date: 04/04/2016
 * Time: 06:11 PM
 */
error_reporting(0);
include ("lib/scrypt.php");

//Instancias de las Variables

$titulo2 = "Recordatorio de atencion de inconformidad por 15 Dias Habiles; Soy Usuario";
$dia2 = 15;//15
$estatus2 = "8";
$arrgDiasFeriado = get_dias_feriados();

$titulo3 = "Recordatorio de atencion de inconformidad por 48 Horas, Soy Usuario";
$dia3 = 2;//2
$estatus3 = "1, 2, 3, 4, 6, 9";

// minute
echo "llamada  metodo 2<br><br>";
/*Inicio de la funcion*/
proceso_general($estatus2, $dia2, $titulo2, $arrgDiasFeriado);

echo "llamada  metodo 3<br><br>";
/*Inicio de la funcion*/
proceso_general($estatus3, $dia3, $titulo3, $arrgDiasFeriado);

/*Función: Permite enviar correos Masiva
//Parametros: @Estatus(int): el PK del estatus del reclamo
//Parametros: @dia(int): la diferencia de fechas para que envie el correo
//Parametros: @titulo(string): Titilo del Mensaje del Corre (Subject)
*/
function proceso_general($estatus, $dia, $titulo, $arrgDiasFeriado){

    //Consulta de la BD de los datos del proveedor

    $rows_sql  = ("SELECT  recla.fecha_registro, recla.folio,  recla.id_proveedor, pro.email
	FROM reclamaciones AS recla
	INNER JOIN proveedor_usuarios AS pro ON pro.id_proveedor = recla.id_proveedor
	WHERE recla.id_estatus IN (".$estatus.") AND YEAR(recla.fecha_registro) = YEAR(GETDATE())    GROUP BY  recla.id_estatus, recla.id_servicio, recla.folio, recla.fecha_registro, recla.id_proveedor, pro.email");

    $rows  = sql_connect($rows_sql);
    $numrows = sql_numrows($rows);

    for ($n = 1; $n < $numrows+1; $n++){

         $folio              = sql_result($rows,$n,"folio");
         $correo_proveedor   = sql_result($rows,$n,"email");
         $fecha_registro     = sql_result($rows,$n,"fecha_registro");
         $fecha_registro2     = date("Y-m-d",$fecha_registro->getTimestamp());
        $mensaje_completo = utf8_encode("Estimado(a) usuario(a),<br><br>Te informamos que la inconformidad con folio ".$folio." ha cumplido ".$dia."
                                dias habiles de que se realizo el primer contacto con el usuario de servicio
                                y no se le ha proporcionado una solucion, por lo que te pedimos ingresar
                                al sistema para dar la respuesta a la inconformidad.<br><br>
                                Ante cualquier duda o aclaracion ponemos a tu disposicion
                                el mail atencion@ift.org.mx o llama al (0155) 5015-4000 Ext. 4349 o 4173.
                                <br><br>Saludos cordiales,<br>Atencion Ciudadana, IFT.
                                <br><br>Este correo electronico ha sido generado de manera automatica, favor de no responder.");
        //Funcion de envio de correo Simple
         $comparaFecha = dias_habiles($fecha_registro2, $dia, $arrgDiasFeriado);

        if ($comparaFecha['cal_fecha'] == date('Y-m-d')){
           // send_mail_single($correo_proveedor,$titulo,utf8_encode($mensaje_completo));
            echo "******************** Envio Exitoso ".$folio." ******** Fecha Habil ".$comparaFecha['cal_fecha']."*********** Subject ***************** ".utf8_encode($titulo)."**********************<br><br>";
            $cadena = "[*** Envio Exitoso ".$folio."  **Fecha Reclamo->".$fecha_registro2." ***Fecha Habil->".$comparaFecha['cal_fecha']."** Subject *** ".utf8_encode($titulo)."*** ******* SQL-> ".$rows_sql."***]\n\n\n";
            $tipo = "EXITO";
            write_log($cadena,$tipo);
        }else{
            echo "******************** NO coincide ->  ****** Fecha Habil ".$comparaFecha['cal_fecha']."************* Subject ***************** ".utf8_encode($titulo)."**********************<br><br>";
            $cadena = "[*** NO coincide  -> **Fecha Reclamo->".$fecha_registro2."  **Fecha Habil ".$comparaFecha['cal_fecha']."*** Subject *** ".utf8_encode($titulo)."********** SQL-> ".$rows_sql."***]\n\n\n";
            $tipo = "NULL";
            write_log($cadena,$tipo);
        }



    }//fin del for

}//fin del proceso general

/**
 * Descripcion: Permite escribir un  log sobre los eventos del envio de correo
 * @param string $cadena texto a escribir en el log
 * @param string $tipo texto que indica el tipo de mensaje. Los valores normales son Info, Error,
 *                                       Warn Debug, Critical
 */
function write_log($cadena,$tipo)
{
    $arch = fopen(realpath( '.' )."/logs/log_".date("Y-m-d").".txt", "a+");

    fwrite($arch, "[".date("Y-m-d H:i:s.u")." ".$_SERVER['REMOTE_ADDR']." ".
        $_SERVER['HTTP_X_FORWARDED_FOR']." - $tipo ] ".$cadena."\n");
    fclose($arch);
}
/**
 * Descripcion: Permite obtener la fecha exacta del Día habil del reclamo, No cuenta los fines de semana y dias Feriados
 * @param string $fecha -> Fecha del Reclamo
 * @param int $cantidadDias -> cantidad de dias que se desea calcular, Ejemplo 15 dias habiles, 2 dias habiles
 */
function dias_habiles($fecha, $cantidadDias, $arrgDiasFeriado){

    $cantidadDiasValidos = 0;
    $cantidadDiasFeriados = 0;
    $fechaSalida = "";

    while(true){

        if(is_dia_valido($fecha, $arrgDiasFeriado)){
            $cantidadDiasValidos++;

            if($cantidadDiasValidos == $cantidadDias){
                $fecha;
                $cantidadDiasValidos;
                $cantidadDiasFeriados;

                $arrFecha = array("cal_fecha"=>$fecha, "cantidad_dias_validos"=>$cantidadDiasValidos, "cantidad_dias_feriados"=>$cantidadDiasFeriados);
                return $arrFecha;
                break;
            }else{
                $fecha = date_create($fecha);
                $fecha = date_add($fecha, date_interval_create_from_date_string('1 days'));
            }
        }else{
            $cantidadDiasFeriados++;
            $fecha = date_create($fecha);
            $fecha = date_add($fecha, date_interval_create_from_date_string('1 days'));
        }

        $fecha =  date_format($fecha, 'Y-m-d');
    }


}//fin de la funcion
/**
 * Descripcion: Permite determinar si la fecha es valida o no esta funcion complementa a la de dias habiles
 * @param string $fecha -> Fecha del Reclamo
 * @param arrays $arrgDiasFeriado -> es un arreglo con contiene las fechas feriadas extraida de la BD
 */
function is_dia_valido($fecha, $arrgDiasFeriado){

    $weekday = date("w", strtotime($fecha));

    if($weekday == 0 || $weekday == 6){
        return false;
    }else{
        $feriados  = $arrgDiasFeriado;

        $fecha = explode("-", $fecha);
        $fecha = $fecha[2]."-".$fecha[1]; // deberia devolver DIA-MES

        if(in_array($fecha, $feriados)){
            return false;
        }
        else{
            return true;
        }
    }
}//fin de la funcion
/**
 * Descripcion: Extrae de la BD las fechas feriadas del pais, Complemento para calcular los dias habiles
 */
function get_dias_feriados(){

    $sql_script  = ("SELECT a.fecha, SUBSTRING( CONVERT ( char, a.fecha) , 6, 6 ) as corta FROM ift_web.dbo.cat_dias_habiles  as a");
    $rows  = sql_connect($sql_script);
    $numrows = sql_numrows($rows);

    for ($n = 1; $n <= $numrows; $n++) {
        $folio = sql_result($rows, $n, "corta");
        $feriados[$n] = $folio;
    }
    return $feriados;
}//fin de la funcion

?>
